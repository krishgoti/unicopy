"""
Custom LangChain Embeddings wrapper for OpenRouter API.

OpenRouter provides access to various embedding models (e.g. qwen/qwen3-embedding-8b)
through an OpenAI-compatible /v1/embeddings endpoint.
"""

import os
import time
import logging
import requests
from typing import List
from langchain_core.embeddings import Embeddings

logger = logging.getLogger("openrouter_embeddings")


class OpenRouterEmbeddings(Embeddings):
    """Custom embedding class for the OpenRouter API."""

    def __init__(
        self,
        model: str = "qwen/qwen3-embedding-8b",
        api_key: str | None = None,
        dimensions: int = 1024,
    ):
        self.model = model
        self.api_key = api_key or os.environ.get("OPENROUTER_API_KEY", "")
        self.dimensions = dimensions
        self.api_url = "https://openrouter.ai/api/v1/embeddings"

        if not self.api_key:
            raise ValueError(
                "OPENROUTER_API_KEY is required. "
                "Pass it directly or set the OPENROUTER_API_KEY environment variable."
            )

    def _call_api(self, texts: List[str]) -> List[List[float]]:
        """Make a single API call to OpenRouter embeddings endpoint."""
        logger.debug(
            f"Embedding API request: model={self.model}, num_texts={len(texts)}, dims={self.dimensions}",
            extra={
                "event": "embedding_request",
                "data": {
                    "model": self.model,
                    "num_texts": len(texts),
                    "dimensions": self.dimensions,
                    "text_lengths": [len(t) for t in texts[:10]],  # first 10 text lengths
                },
            },
        )

        t0 = time.time()

        try:
            response = requests.post(
                self.api_url,
                headers={
                    "Authorization": f"Bearer {self.api_key}",
                    "Content-Type": "application/json",
                },
                json={
                    "model": self.model,
                    "input": texts,
                    "dimensions": self.dimensions,
                },
                timeout=120,
            )

            latency_ms = round((time.time() - t0) * 1000, 2)

            if response.status_code != 200:
                logger.error(
                    f"Embedding API error: HTTP {response.status_code}",
                    extra={
                        "event": "embedding_error",
                        "data": {
                            "http_status": response.status_code,
                            "response_body": response.text[:500],
                            "latency_ms": latency_ms,
                        },
                    },
                )

            response.raise_for_status()
            data = response.json()

            # Sort by index to maintain order
            embeddings = sorted(data["data"], key=lambda x: x["index"])
            result = [[float(v) for v in item["embedding"]] for item in embeddings]

            logger.info(
                f"Embedding API complete: {len(result)} embeddings in {latency_ms}ms",
                extra={
                    "event": "embedding_complete",
                    "data": {
                        "model": self.model,
                        "num_embeddings": len(result),
                        "latency_ms": latency_ms,
                        "embedding_dims": len(result[0]) if result else 0,
                    },
                },
            )

            return result

        except requests.exceptions.Timeout:
            latency_ms = round((time.time() - t0) * 1000, 2)
            logger.error(
                f"Embedding API timeout after {latency_ms}ms",
                extra={
                    "event": "embedding_timeout",
                    "data": {"latency_ms": latency_ms, "timeout_config": 120},
                },
            )
            raise

        except requests.exceptions.RequestException as e:
            latency_ms = round((time.time() - t0) * 1000, 2)
            logger.error(
                f"Embedding API request failed: {type(e).__name__}: {e}",
                extra={
                    "event": "embedding_request_error",
                    "data": {
                        "error_class": type(e).__name__,
                        "error": str(e),
                        "latency_ms": latency_ms,
                    },
                },
                exc_info=True,
            )
            raise

    def embed_documents(self, texts: List[str]) -> List[List[float]]:
        """Embed a list of documents.

        Batches large inputs into groups of 100 to avoid
        request size limits.
        """
        BATCH_SIZE = 100
        all_embeddings: List[List[float]] = []

        total_batches = (len(texts) + BATCH_SIZE - 1) // BATCH_SIZE
        logger.info(
            f"Embedding {len(texts)} documents in {total_batches} batches",
            extra={
                "event": "embed_documents_start",
                "data": {"num_texts": len(texts), "batch_size": BATCH_SIZE, "num_batches": total_batches},
            },
        )

        for i in range(0, len(texts), BATCH_SIZE):
            batch = texts[i : i + BATCH_SIZE]
            all_embeddings.extend(self._call_api(batch))

        return all_embeddings

    def embed_query(self, text: str) -> List[float]:
        """Embed a single query string."""
        return self._call_api([text])[0]
