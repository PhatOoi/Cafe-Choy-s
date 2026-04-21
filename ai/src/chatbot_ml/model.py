from __future__ import annotations

import math
import re
from collections import Counter

TOKEN_PATTERN = re.compile(r"[\wÀ-ỹ]+", re.UNICODE)


def tokenize(text: str) -> list[str]:
    normalized = text.lower().strip()
    words = TOKEN_PATTERN.findall(normalized)
    bigrams = [f"{words[index]}__{words[index + 1]}" for index in range(len(words) - 1)]

    compact = f" {normalized} "
    char_ngrams: list[str] = []
    for size in range(3, 6):
        for index in range(max(0, len(compact) - size + 1)):
            char_ngrams.append(f"c{size}:{compact[index:index + size]}")

    return words + bigrams + char_ngrams


class IntentNBModel:
    def __init__(self) -> None:
        self.classes_: list[str] = []
        self.class_doc_counts: dict[str, int] = {}
        self.class_token_counts: dict[str, dict[str, int]] = {}
        self.class_total_tokens: dict[str, int] = {}
        self.vocabulary: set[str] = set()

    def fit(self, texts: list[str], labels: list[str]) -> "IntentNBModel":
        self.classes_ = sorted(set(labels))
        self.class_doc_counts = Counter(labels)
        self.class_token_counts = {label: {} for label in self.classes_}
        self.class_total_tokens = {label: 0 for label in self.classes_}
        self.vocabulary = set()

        for text, label in zip(texts, labels):
            tokens = tokenize(text)
            bucket = self.class_token_counts[label]
            for token in tokens:
                bucket[token] = bucket.get(token, 0) + 1
                self.class_total_tokens[label] += 1
                self.vocabulary.add(token)
        return self

    def predict(self, texts: list[str]) -> list[str]:
        return [max(self.predict_proba(text).items(), key=lambda item: item[1])[0] for text in texts]

    def predict_proba(self, text: str) -> dict[str, float]:
        tokens = tokenize(text)
        vocab_size = max(1, len(self.vocabulary))
        total_docs = max(1, sum(self.class_doc_counts.values()))

        log_scores: dict[str, float] = {}
        for label in self.classes_:
            prior = self.class_doc_counts[label] / total_docs
            score = math.log(prior if prior > 0 else 1e-9)
            token_counts = self.class_token_counts[label]
            total_tokens = self.class_total_tokens[label]
            denominator = total_tokens + vocab_size
            for token in tokens:
                score += math.log((token_counts.get(token, 0) + 1) / denominator)
            log_scores[label] = score

        max_score = max(log_scores.values())
        exp_scores = {label: math.exp(score - max_score) for label, score in log_scores.items()}
        total_score = sum(exp_scores.values()) or 1.0
        return {label: value / total_score for label, value in exp_scores.items()}

    def to_dict(self) -> dict:
        return {
            "classes": self.classes_,
            "class_doc_counts": self.class_doc_counts,
            "class_token_counts": self.class_token_counts,
            "class_total_tokens": self.class_total_tokens,
            "vocabulary": sorted(self.vocabulary),
        }

    @classmethod
    def from_dict(cls, payload: dict) -> "IntentNBModel":
        model = cls()
        model.classes_ = list(payload["classes"])
        model.class_doc_counts = {key: int(value) for key, value in payload["class_doc_counts"].items()}
        model.class_token_counts = {
            label: {token: int(count) for token, count in counts.items()}
            for label, counts in payload["class_token_counts"].items()
        }
        model.class_total_tokens = {key: int(value) for key, value in payload["class_total_tokens"].items()}
        model.vocabulary = set(payload["vocabulary"])
        return model


def build_pipeline() -> IntentNBModel:
    return IntentNBModel()


def accuracy_score(expected: list[str], predicted: list[str]) -> float:
    if not expected:
        return 0.0
    matches = sum(1 for truth, guess in zip(expected, predicted) if truth == guess)
    return matches / len(expected)


def classification_report(expected: list[str], predicted: list[str]) -> dict:
    labels = sorted(set(expected) | set(predicted))
    report: dict[str, dict[str, float]] = {}
    for label in labels:
        tp = sum(1 for truth, guess in zip(expected, predicted) if truth == label and guess == label)
        fp = sum(1 for truth, guess in zip(expected, predicted) if truth != label and guess == label)
        fn = sum(1 for truth, guess in zip(expected, predicted) if truth == label and guess != label)
        support = sum(1 for truth in expected if truth == label)

        precision = tp / (tp + fp) if (tp + fp) else 0.0
        recall = tp / (tp + fn) if (tp + fn) else 0.0
        f1 = (2 * precision * recall / (precision + recall)) if (precision + recall) else 0.0

        report[label] = {
            "precision": precision,
            "recall": recall,
            "f1-score": f1,
            "support": support,
        }
    return report


def train_and_evaluate(
    train_texts: list[str],
    train_labels: list[str],
    test_texts: list[str],
    test_labels: list[str],
) -> tuple[IntentNBModel, dict]:
    pipeline = build_pipeline()
    pipeline.fit(train_texts, train_labels)

    predictions = pipeline.predict(test_texts)
    accuracy = accuracy_score(test_labels, predictions)
    report = classification_report(test_labels, predictions)

    metrics = {
        "accuracy": accuracy,
        "label_distribution": dict(Counter(train_labels)),
        "test_size": len(test_texts),
        "report": report,
    }
    return pipeline, metrics