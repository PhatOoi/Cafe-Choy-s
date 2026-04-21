from __future__ import annotations

import re

try:
    from underthesea import word_tokenize as underthesea_word_tokenize
except ImportError:
    underthesea_word_tokenize = None


TOKEN_PATTERN = re.compile(r"[\wÀ-ỹ]+", re.UNICODE)


def tokenize_text(text: str) -> list[str]:
    normalized = text.lower().strip()
    if not normalized:
        return []

    if underthesea_word_tokenize is not None:
        return [token.replace(" ", "_") for token in underthesea_word_tokenize(normalized)]

    return TOKEN_PATTERN.findall(normalized)


class IntentVocabulary:
    PAD_TOKEN = "<pad>"
    UNK_TOKEN = "<unk>"

    def __init__(self) -> None:
        self.word_to_id: dict[str, int] = {
            self.PAD_TOKEN: 0,
            self.UNK_TOKEN: 1,
        }
        self.id_to_word: dict[int, str] = {index: token for token, index in self.word_to_id.items()}

    @property
    def pad_idx(self) -> int:
        return self.word_to_id[self.PAD_TOKEN]

    @property
    def unk_idx(self) -> int:
        return self.word_to_id[self.UNK_TOKEN]

    def __len__(self) -> int:
        return len(self.word_to_id)

    def add_token(self, token: str) -> int:
        if token not in self.word_to_id:
            token_id = len(self.word_to_id)
            self.word_to_id[token] = token_id
            self.id_to_word[token_id] = token
        return self.word_to_id[token]

    def build(self, texts: list[str], min_freq: int = 1) -> "IntentVocabulary":
        counts: dict[str, int] = {}
        for text in texts:
            for token in tokenize_text(text):
                counts[token] = counts.get(token, 0) + 1

        for token in sorted(token for token, count in counts.items() if count >= min_freq):
            self.add_token(token)
        return self

    def encode(self, text: str) -> list[int]:
        tokens = tokenize_text(text)
        if not tokens:
            return [self.unk_idx]
        return [self.word_to_id.get(token, self.unk_idx) for token in tokens]

    def to_dict(self) -> dict:
        return {
            "word_to_id": self.word_to_id,
        }

    @classmethod
    def from_dict(cls, payload: dict) -> "IntentVocabulary":
        vocab = cls()
        vocab.word_to_id = {token: int(index) for token, index in payload["word_to_id"].items()}
        vocab.id_to_word = {index: token for token, index in vocab.word_to_id.items()}
        return vocab
