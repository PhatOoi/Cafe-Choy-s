from __future__ import annotations

import json
import sys
from pathlib import Path

import torch


ROOT = Path(__file__).resolve().parents[2]
SRC_DIR = ROOT / "ai" / "src"
if str(SRC_DIR) not in sys.path:
    sys.path.insert(0, str(SRC_DIR))

from chatbot_ml_torch.model import IntentLSTMClassifier
from chatbot_ml_torch.text import IntentVocabulary


ARTIFACTS_DIR = ROOT / "ai" / "artifacts"


def main() -> None:
    if len(sys.argv) < 2:
        raise SystemExit("Usage: python scripts/predict_intent_torch.py \"tin nhan cua khach\"")

    model_path = ARTIFACTS_DIR / "intent_torch_model.pt"
    metadata_path = ARTIFACTS_DIR / "intent_torch_metadata.json"
    if not model_path.exists() or not metadata_path.exists():
        raise SystemExit("Torch model not found. Run python scripts/train_intent_model_torch.py first.")

    metadata = json.loads(metadata_path.read_text(encoding="utf-8"))
    vocab = IntentVocabulary.from_dict(metadata["vocab"])
    labels = metadata["labels"]

    payload = torch.load(model_path, map_location="cpu")
    model = IntentLSTMClassifier(**payload["config"])
    model.load_state_dict(payload["state_dict"])
    model.eval()

    text = " ".join(sys.argv[1:]).strip()
    encoded = torch.tensor(vocab.encode(text), dtype=torch.long).unsqueeze(1)
    lengths = torch.tensor([encoded.size(0)], dtype=torch.long)

    with torch.no_grad():
        logits = model(encoded, lengths)
        probabilities = torch.softmax(logits, dim=1)[0]

    scored = {label: round(float(probabilities[index].item()), 4) for index, label in enumerate(labels)}
    ranked = dict(sorted(scored.items(), key=lambda item: item[1], reverse=True))
    intent = max(ranked, key=ranked.get)

    print(
        json.dumps(
            {
                "text": text,
                "intent": intent,
                "probabilities": ranked,
            },
            ensure_ascii=False,
            indent=2,
        )
    )


if __name__ == "__main__":
    main()
