from __future__ import annotations

import argparse
import random
import sys
from copy import deepcopy
from pathlib import Path

import torch
from torch import nn
from torch.utils.data import DataLoader


ROOT = Path(__file__).resolve().parents[2]
SRC_DIR = ROOT / "ai" / "src"
if str(SRC_DIR) not in sys.path:
    sys.path.insert(0, str(SRC_DIR))

from chatbot_ml.data import dump_json, load_jsonl
from chatbot_ml_torch.dataset import IntentTextDataset
from chatbot_ml_torch.model import IntentLSTMClassifier
from chatbot_ml_torch.text import IntentVocabulary


DATA_DIR = ROOT / "ai" / "data" / "intents"
ARTIFACTS_DIR = ROOT / "ai" / "artifacts"
SEED = 42


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Train a GPU-capable PyTorch intent classifier.")
    parser.add_argument("--epochs", type=int, default=20)
    parser.add_argument("--batch-size", type=int, default=64)
    parser.add_argument("--embedding-dim", type=int, default=128)
    parser.add_argument("--hidden-dim", type=int, default=128)
    parser.add_argument("--num-layers", type=int, default=2)
    parser.add_argument("--dropout", type=float, default=0.3)
    parser.add_argument("--lr", type=float, default=0.001)
    parser.add_argument("--valid-ratio", type=float, default=0.1)
    parser.add_argument("--device", choices=["auto", "cpu", "cuda"], default="auto")
    return parser.parse_args()


def set_seed(seed: int) -> None:
    random.seed(seed)
    torch.manual_seed(seed)
    if torch.cuda.is_available():
        torch.cuda.manual_seed_all(seed)


def choose_paths() -> tuple[Path, Path]:
    train_path = DATA_DIR / "train_expanded.jsonl"
    test_path = DATA_DIR / "test_expanded.jsonl"
    if not train_path.exists() or not test_path.exists():
        train_path = DATA_DIR / "train.jsonl"
        test_path = DATA_DIR / "test.jsonl"
    return train_path, test_path


def split_train_valid(rows: list[dict], valid_ratio: float) -> tuple[list[dict], list[dict]]:
    shuffled = rows[:]
    random.shuffle(shuffled)
    valid_size = max(1, int(len(shuffled) * valid_ratio))
    valid_rows = shuffled[:valid_size]
    train_rows = shuffled[valid_size:]
    return train_rows, valid_rows


def build_dataloader(
    rows: list[dict],
    vocab: IntentVocabulary,
    label_to_id: dict[str, int],
    batch_size: int,
    shuffle: bool,
) -> DataLoader:
    dataset = IntentTextDataset(
        texts=[row["text"] for row in rows],
        labels=[row["intent"] for row in rows],
        vocab=vocab,
        label_to_id=label_to_id,
    )
    return DataLoader(
        dataset,
        batch_size=batch_size,
        shuffle=shuffle,
        collate_fn=dataset.collate_fn,
    )


def accuracy_from_logits(logits: torch.Tensor, labels: torch.Tensor) -> float:
    predictions = logits.argmax(dim=1)
    return float((predictions == labels).float().mean().item())


def run_epoch(
    model: IntentLSTMClassifier,
    dataloader: DataLoader,
    criterion: nn.Module,
    device: torch.device,
    optimizer: torch.optim.Optimizer | None = None,
) -> tuple[float, float]:
    is_training = optimizer is not None
    if is_training:
        model.train()
    else:
        model.eval()

    total_loss = 0.0
    total_acc = 0.0
    total_batches = 0

    for batch in dataloader:
        texts = batch["texts"].to(device)
        lengths = batch["lengths"].to(device)
        labels = batch["labels"].to(device)

        if is_training:
            optimizer.zero_grad()

        with torch.set_grad_enabled(is_training):
            logits = model(texts, lengths)
            loss = criterion(logits, labels)
            acc = accuracy_from_logits(logits, labels)

            if is_training:
                loss.backward()
                torch.nn.utils.clip_grad_norm_(model.parameters(), max_norm=1.0)
                optimizer.step()

        total_loss += float(loss.item())
        total_acc += acc
        total_batches += 1

    if total_batches == 0:
        return 0.0, 0.0
    return total_loss / total_batches, total_acc / total_batches


def choose_device(requested: str) -> torch.device:
    if requested == "cuda":
        if not torch.cuda.is_available():
            raise SystemExit("CUDA was requested but torch.cuda.is_available() is False.")
        return torch.device("cuda")
    if requested == "cpu":
        return torch.device("cpu")
    return torch.device("cuda" if torch.cuda.is_available() else "cpu")


def evaluate_on_test(
    model: IntentLSTMClassifier,
    dataloader: DataLoader,
    id_to_label: dict[int, str],
    device: torch.device,
) -> dict:
    model.eval()
    predicted_labels: list[str] = []
    expected_labels: list[str] = []

    with torch.no_grad():
        for batch in dataloader:
            texts = batch["texts"].to(device)
            lengths = batch["lengths"].to(device)
            labels = batch["labels"].to(device)
            logits = model(texts, lengths)
            predictions = logits.argmax(dim=1)
            predicted_labels.extend(id_to_label[int(item)] for item in predictions.cpu().tolist())
            expected_labels.extend(id_to_label[int(item)] for item in labels.cpu().tolist())

    matches = sum(1 for expected, predicted in zip(expected_labels, predicted_labels) if expected == predicted)
    accuracy = matches / len(expected_labels) if expected_labels else 0.0

    per_label: dict[str, dict[str, float]] = {}
    labels = sorted(set(expected_labels) | set(predicted_labels))
    for label in labels:
        tp = sum(1 for expected, predicted in zip(expected_labels, predicted_labels) if expected == label and predicted == label)
        fp = sum(1 for expected, predicted in zip(expected_labels, predicted_labels) if expected != label and predicted == label)
        fn = sum(1 for expected, predicted in zip(expected_labels, predicted_labels) if expected == label and predicted != label)
        support = sum(1 for expected in expected_labels if expected == label)
        precision = tp / (tp + fp) if (tp + fp) else 0.0
        recall = tp / (tp + fn) if (tp + fn) else 0.0
        f1 = (2 * precision * recall / (precision + recall)) if (precision + recall) else 0.0
        per_label[label] = {
            "precision": precision,
            "recall": recall,
            "f1-score": f1,
            "support": support,
        }

    return {
        "accuracy": accuracy,
        "report": per_label,
    }


def main() -> None:
    args = parse_args()
    set_seed(SEED)

    train_path, test_path = choose_paths()
    train_rows = load_jsonl(train_path)
    test_rows = load_jsonl(test_path)
    fit_rows, valid_rows = split_train_valid(train_rows, args.valid_ratio)

    labels = sorted({row["intent"] for row in train_rows + test_rows})
    label_to_id = {label: index for index, label in enumerate(labels)}
    id_to_label = {index: label for label, index in label_to_id.items()}

    vocab = IntentVocabulary().build([row["text"] for row in fit_rows])
    train_loader = build_dataloader(fit_rows, vocab, label_to_id, args.batch_size, shuffle=True)
    valid_loader = build_dataloader(valid_rows, vocab, label_to_id, args.batch_size, shuffle=False)
    test_loader = build_dataloader(test_rows, vocab, label_to_id, args.batch_size, shuffle=False)

    device = choose_device(args.device)
    model = IntentLSTMClassifier(
        vocab_size=len(vocab),
        embedding_dim=args.embedding_dim,
        hidden_dim=args.hidden_dim,
        output_dim=len(labels),
        pad_idx=vocab.pad_idx,
        num_layers=args.num_layers,
        dropout=args.dropout,
    ).to(device)

    criterion = nn.CrossEntropyLoss()
    optimizer = torch.optim.Adam(model.parameters(), lr=args.lr)

    best_state = deepcopy(model.state_dict())
    best_valid_acc = -1.0
    history: list[dict[str, float | int]] = []

    for epoch in range(1, args.epochs + 1):
        train_loss, train_acc = run_epoch(model, train_loader, criterion, device, optimizer=optimizer)
        valid_loss, valid_acc = run_epoch(model, valid_loader, criterion, device)

        history.append(
            {
                "epoch": epoch,
                "train_loss": train_loss,
                "train_accuracy": train_acc,
                "valid_loss": valid_loss,
                "valid_accuracy": valid_acc,
            }
        )

        if valid_acc > best_valid_acc:
            best_valid_acc = valid_acc
            best_state = deepcopy(model.state_dict())

        print(
            f"Epoch {epoch:02d}/{args.epochs} | "
            f"train_loss={train_loss:.4f} train_acc={train_acc:.4f} | "
            f"valid_loss={valid_loss:.4f} valid_acc={valid_acc:.4f}"
        )

    model.load_state_dict(best_state)
    test_metrics = evaluate_on_test(model, test_loader, id_to_label, device)

    ARTIFACTS_DIR.mkdir(parents=True, exist_ok=True)
    torch.save(
        {
            "state_dict": model.state_dict(),
            "config": {
                "vocab_size": len(vocab),
                "embedding_dim": args.embedding_dim,
                "hidden_dim": args.hidden_dim,
                "output_dim": len(labels),
                "pad_idx": vocab.pad_idx,
                "num_layers": args.num_layers,
                "dropout": args.dropout,
                "bidirectional": True,
            },
        },
        ARTIFACTS_DIR / "intent_torch_model.pt",
    )

    metadata = {
        "labels": labels,
        "label_to_id": label_to_id,
        "vocab": vocab.to_dict(),
        "device_used": str(device),
        "train_rows": len(fit_rows),
        "valid_rows": len(valid_rows),
        "test_rows": len(test_rows),
    }
    dump_json(ARTIFACTS_DIR / "intent_torch_metadata.json", metadata)
    dump_json(
        ARTIFACTS_DIR / "intent_torch_metrics.json",
        {
            "history": history,
            "best_valid_accuracy": best_valid_acc,
            "test": test_metrics,
        },
    )

    print(f"Device: {device}")
    print(f"Best valid accuracy: {best_valid_acc:.4f}")
    print(f"Test accuracy: {test_metrics['accuracy']:.4f}")
    print(f"Model saved to: {(ARTIFACTS_DIR / 'intent_torch_model.pt').as_posix()}")


if __name__ == "__main__":
    main()
