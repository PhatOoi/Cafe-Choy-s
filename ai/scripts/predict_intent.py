from __future__ import annotations

import json
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parents[2]
SRC_DIR = ROOT / "ai" / "src"
if str(SRC_DIR) not in sys.path:
    sys.path.insert(0, str(SRC_DIR))

from chatbot_ml.model import IntentNBModel

ARTIFACTS_DIR = ROOT / "ai" / "artifacts"


def main() -> None:
    if len(sys.argv) < 2:
        raise SystemExit("Usage: python scripts/predict_intent.py \"tin nhan cua khach\"")

    model_path = ARTIFACTS_DIR / "intent_model.json"
    if not model_path.exists():
        raise SystemExit("Model not found. Run python scripts/train_intent_model.py first.")

    model = IntentNBModel.from_dict(json.loads(model_path.read_text(encoding="utf-8")))
    text = " ".join(sys.argv[1:]).strip()

    predicted_intent = model.predict([text])[0]
    probabilities = {
        label: round(float(score), 4)
        for label, score in sorted(model.predict_proba(text).items(), key=lambda pair: pair[1], reverse=True)
    }

    payload = {
        "text": text,
        "intent": predicted_intent,
        "probabilities": probabilities,
    }
    print(json.dumps(payload, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()