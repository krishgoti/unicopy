"""
Custom LangChain Embeddings wrapper for Google Gemini with batch processing.

Wraps GoogleGenerativeAIEmbeddings to embed documents in small batches,
avoiding RESOURCE_EXHAUSTED quota errors on large uploads.
"""

import os
from typing import List
from langchain_google_genai import GoogleGenerativeAIEmbeddings
from langchain_core.embeddings import Embeddings


class GoogleBatchEmbeddings(Embeddings):
    """Google Gemini embeddings with batch processing to avoid quota errors."""

    def __init__(
        self,
        model: str = "models/gemini-embedding-001",
        batch_size: int = 100,
        google_api_key: str | None = None,
    ):
        self.model = model
        self.batch_size = batch_size
        self._embeddings = GoogleGenerativeAIEmbeddings(
            model=model,
            google_api_key=google_api_key or os.environ.get("GOOGLE_API_KEY"),
        )

    def embed_documents(self, texts: List[str]) -> List[List[float]]:
        """Embed a list of documents in batches.

        Batches large inputs into groups to avoid
        RESOURCE_EXHAUSTED quota errors.
        """
        all_embeddings: List[List[float]] = []

        for i in range(0, len(texts), self.batch_size):
            batch = texts[i : i + self.batch_size]
            all_embeddings.extend(self._embeddings.embed_documents(batch))

        return all_embeddings

    def embed_query(self, text: str) -> List[float]:
        """Embed a single query string."""
        return self._embeddings.embed_query(text)
