from __future__ import annotations

import runpy
import sys
from pathlib import Path


ROOT = Path(__file__).resolve().parent


def can_use_torch() -> bool:
    try:
        import torch  # noqa: F401
    except Exception:
        return False
    return True


def print_wrapper_help() -> None:
    print("Usage: python train.py [--epochs N --batch-size N --device auto|cpu|cuda]")
    print()
    print("Behavior:")
    print("- If the current Python environment has torch, this wrapper runs the PyTorch trainer.")
    print("- Otherwise it runs the pure Python fallback trainer.")
    print()
    print("Common commands:")
    print("- python train.py")
    print("- python train.py --epochs 20 --batch-size 64 --device cuda")


def main() -> None:
    if any(arg in {"-h", "--help"} for arg in sys.argv[1:]):
        print_wrapper_help()
        return

    if can_use_torch():
        target = ROOT / "ai" / "scripts" / "train_intent_model_torch.py"
        print("Using PyTorch trainer.")
    else:
        target = ROOT / "ai" / "scripts" / "train_intent_model.py"
        print("Using pure Python trainer.")

    sys.argv = [str(target), *sys.argv[1:]]
    runpy.run_path(str(target), run_name="__main__")


if __name__ == "__main__":
    main()
