from __future__ import annotations

import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
SRC_DIR = ROOT / "ai" / "src"
if str(SRC_DIR) not in sys.path:
    sys.path.insert(0, str(SRC_DIR))

from chatbot_ml.data import dump_json, load_jsonl
from chatbot_ml.model import train_and_evaluate


DATA_DIR = ROOT / "ai" / "data" / "intents"
ARTIFACTS_DIR = ROOT / "ai" / "artifacts"


def main() -> None:
    train_path = DATA_DIR / "train_expanded.jsonl"
    test_path = DATA_DIR / "test_expanded.jsonl"
    if not train_path.exists() or not test_path.exists():
        train_path = DATA_DIR / "train.jsonl"
        test_path = DATA_DIR / "test.jsonl"

    train_rows = load_jsonl(train_path)
    test_rows = load_jsonl(test_path)

    train_texts = [row["text"] for row in train_rows]
    train_labels = [row["intent"] for row in train_rows]
    test_texts = [row["text"] for row in test_rows]
    test_labels = [row["intent"] for row in test_rows]

    model, metrics = train_and_evaluate(train_texts, train_labels, test_texts, test_labels)

    ARTIFACTS_DIR.mkdir(parents=True, exist_ok=True)
    dump_json(ARTIFACTS_DIR / "intent_model.json", model.to_dict())
    dump_json(ARTIFACTS_DIR / "intent_metrics.json", metrics)

    print(f"Accuracy: {metrics['accuracy']:.4f}")
    print(f"Training rows: {len(train_rows)} | Test rows: {len(test_rows)}")
    print(f"Model saved to: {(ARTIFACTS_DIR / 'intent_model.json').as_posix()}")


if __name__ == "__main__":
    main()