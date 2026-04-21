from __future__ import annotations

import torch
from torch.nn.utils.rnn import pad_sequence
from torch.utils.data import Dataset

from .text import IntentVocabulary


class IntentTextDataset(Dataset):
    def __init__(
        self,
        texts: list[str],
        labels: list[str],
        vocab: IntentVocabulary,
        label_to_id: dict[str, int],
    ) -> None:
        self.pad_idx = vocab.pad_idx
        self.samples: list[tuple[torch.Tensor, int]] = []
        for text, label in zip(texts, labels):
            encoded = torch.tensor(vocab.encode(text), dtype=torch.long)
            self.samples.append((encoded, label_to_id[label]))

    def __len__(self) -> int:
        return len(self.samples)

    def __getitem__(self, index: int) -> tuple[torch.Tensor, int]:
        return self.samples[index]

    def collate_fn(self, examples: list[tuple[torch.Tensor, int]]) -> dict[str, torch.Tensor]:
        examples = sorted(examples, key=lambda item: item[0].size(0), reverse=True)
        texts = [item[0] for item in examples]
        lengths = torch.tensor([item.size(0) for item in texts], dtype=torch.long)
        labels = torch.tensor([item[1] for item in examples], dtype=torch.long)
        padded = pad_sequence(texts, batch_first=False, padding_value=self.pad_idx)
        return {
            "texts": padded,
            "lengths": lengths,
            "labels": labels,
        }
