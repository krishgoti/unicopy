"""
Hybrid Retriever Service
========================
Uses PineconeHybridSearchRetriever which combines:
  - Dense (semantic) search via OpenRouter embeddings (384 dims)
  - Sparse (keyword) search via BM25

All documents are stored in the DEFAULT namespace.
Retrieval is a single query — faster and simpler.
"""

from pathlib import Path
import os
import time
import logging

from langchain_community.retrievers import PineconeHybridSearchRetriever
from pinecone import Pinecone
from pinecone_text.sparse import BM25Encoder

from services.openrouter_embeddings import OpenRouterEmbeddings
from middleware.circuit_breaker import pinecone_cb

from dotenv import load_dotenv
load_dotenv()

logger = logging.getLogger("hybrid_retriever")

HYBRID_INDEX_NAME = os.environ.get("PINECONE_INDEX")

# Path to BM25 values file fitted on corpus (optional — improves accuracy)
BM25_VALUES_PATH = Path(__file__).parent.parent / "bm25_values.json"


def _build_embeddings() -> OpenRouterEmbeddings:
    return OpenRouterEmbeddings(model="qwen/qwen3-embedding-8b", dimensions=384)

def _build_bm25() -> BM25Encoder:
    if BM25_VALUES_PATH.exists():
        return BM25Encoder().load(str(BM25_VALUES_PATH))
    # Default TF-IDF-style weights — functional without corpus fitting
    return BM25Encoder().default()

# Lazy singletons
_embeddings: OpenRouterEmbeddings | None = None
_bm25: BM25Encoder | None = None
_pc_index = None

def get_embeddings() -> OpenRouterEmbeddings:
    global _embeddings
    if _embeddings is None:
        _embeddings = _build_embeddings()
    return _embeddings

def get_bm25() -> BM25Encoder:
    global _bm25
    if _bm25 is None:
        _bm25 = _build_bm25()
    return _bm25

def get_pinecone_index():
    global _pc_index
    if _pc_index is None:
        pc = Pinecone()
        _pc_index = pc.Index(HYBRID_INDEX_NAME)
    return _pc_index



def hybrid_search(query: str) -> str:
    """
    Run hybrid (keyword + semantic) search against the default namespace.

    All documents are stored in the default namespace by PineconeHybridSearchRetriever.
    Single query = lower latency, no namespace iteration needed.

    Returns formatted context string or a fallback message.
    """
    if not pinecone_cb.can_execute():
        logger.warning(
            "Circuit breaker OPEN — skipping Pinecone query",
            extra={
                "event": "circuit_breaker_blocked",
                "data": {"state": pinecone_cb.state, "query": query[:200]},
            },
        )
        return "The knowledge base is temporarily unavailable due to connectivity issues. Please try again soon."

    try:
        logger.debug(
            f"Pinecone hybrid query starting",
            extra={"event": "pinecone_query_start", "data": {"query": query[:200]}},
        )

        t0 = time.time()

        # Single retriever on default namespace — one round trip to Pinecone
        retriever = PineconeHybridSearchRetriever(
            embeddings=get_embeddings(),
            sparse_encoder=get_bm25(),
            index=get_pinecone_index(),
            top_k=5,    # top 5 results across ALL documents in one shot
            alpha=0.5,  # 0 = pure BM25, 1 = pure dense; 0.5 = balanced
        )

        docs = retriever.invoke(query)
        latency_ms = round((time.time() - t0) * 1000, 2)

        pinecone_cb.record_success()

        if not docs:
            logger.warning(
                "No results from Pinecone hybrid search",
                extra={
                    "event": "pinecone_empty_results",
                    "data": {"query": query[:200], "latency_ms": latency_ms},
                },
            )
            return "No relevant context found via hybrid search."

        parts = []
        scores = []
        retrieved_docs_log = []

        for i, doc in enumerate(docs, start=1):
            score = doc.metadata.get("score", "N/A")
            score_str = f"{score:.4f}" if isinstance(score, float) else str(score)
            if isinstance(score, float):
                scores.append(score)
            parts.append(f"[Result {i} | Score: {score_str}]\n{doc.page_content}")

            # Build detailed log entry for each retrieved document
            retrieved_docs_log.append({
                "rank": i,
                "score": score_str,
                "source": doc.metadata.get("source", "unknown"),
                "doc_id": doc.metadata.get("doc_id", "unknown"),
                "content_preview": doc.page_content[:300],
                "content_length": len(doc.page_content),
            })

        top_score = max(scores) if scores else 0.0

        logger.info(
            f"Pinecone hybrid search complete: {len(docs)} docs, top_score={top_score:.4f}, latency={latency_ms}ms",
            extra={
                "event": "pinecone_query_complete",
                "data": {
                    "query": query[:200],
                    "num_docs": len(docs),
                    "scores": [round(s, 4) for s in scores],
                    "top_score": round(top_score, 4),
                    "latency_ms": latency_ms,
                    "retrieved_documents": retrieved_docs_log,
                },
            },
        )

        # Warn if all scores are low — likely a bad retrieval
        if scores and top_score < 0.3:
            logger.warning(
                f"All retrieval scores below 0.3 — likely irrelevant results",
                extra={
                    "event": "low_relevance_warning",
                    "data": {
                        "query": query[:200],
                        "scores": [round(s, 4) for s in scores],
                        "top_score": round(top_score, 4),
                    },
                },
            )

        return "\n\n---\n\n".join(parts)

    except Exception as e:
        pinecone_cb.record_failure()
        logger.error(
            f"Hybrid retriever error: {type(e).__name__}: {e}",
            extra={
                "event": "pinecone_error",
                "data": {"query": query[:200], "error_class": type(e).__name__, "error": str(e)},
            },
            exc_info=True,
        )
        return f"Hybrid retriever error: {str(e)}"
