from __future__ import annotations

import torch
from torch import nn


class IntentLSTMClassifier(nn.Module):
    def __init__(
        self,
        vocab_size: int,
        embedding_dim: int,
        hidden_dim: int,
        output_dim: int,
        pad_idx: int,
        num_layers: int = 2,
        bidirectional: bool = True,
        dropout: float = 0.3,
    ) -> None:
        super().__init__()
        self.bidirectional = bidirectional
        self.embedding = nn.Embedding(vocab_size, embedding_dim, padding_idx=pad_idx)
        self.rnn = nn.LSTM(
            embedding_dim,
            hidden_dim,
            num_layers=num_layers,
            bidirectional=bidirectional,
            dropout=dropout if num_layers > 1 else 0.0,
        )
        output_features = hidden_dim * 2 if bidirectional else hidden_dim
        self.dropout = nn.Dropout(dropout)
        self.fc = nn.Linear(output_features, output_dim)

    def forward(self, texts: torch.Tensor, lengths: torch.Tensor) -> torch.Tensor:
        embedded = self.dropout(self.embedding(texts))
        packed = nn.utils.rnn.pack_padded_sequence(embedded, lengths.cpu(), enforce_sorted=True)
        _, (hidden, _) = self.rnn(packed)

        if self.bidirectional:
            hidden_state = torch.cat((hidden[-2], hidden[-1]), dim=1)
        else:
            hidden_state = hidden[-1]

        return self.fc(self.dropout(hidden_state))
