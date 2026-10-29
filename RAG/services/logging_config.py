"""
Centralized Logging Configuration for RAG Pipeline
=====================================================
Provides:
  - Structured JSON logging across all modules
  - request_id propagation via contextvars (async-safe)
  - File rotation: logs/rag.log (10MB, 5 backups)
  - Console (stdout) output for Docker
  - Dedicated RAGAS evaluation logger: logs/rag_eval.jsonl
    (captures query/contexts/answer triples for accuracy testing)

Usage:
    from services.logging_config import configure_logging, set_request_id, get_request_id
    configure_logging()  # call once at startup
"""

import os
import json
import logging
import logging.handlers
from contextvars import ContextVar
from datetime import datetime, timezone
from pathlib import Path

# ── Request ID propagation (async-safe) ──────────────────────────────────────
# Every log line in a request automatically inherits the request_id.
_request_id_var: ContextVar[str] = ContextVar("request_id", default="-")


def set_request_id(rid: str) -> None:
    """Set the request_id for the current async context."""
    _request_id_var.set(rid)


def get_request_id() -> str:
    """Get the request_id for the current async context."""
    return _request_id_var.get()


# ── JSON Formatter ───────────────────────────────────────────────────────────
class JSONFormatter(logging.Formatter):
    """Produces one JSON object per log line with request_id included."""

    def format(self, record: logging.LogRecord) -> str:
        log_entry = {
            "time": datetime.now(timezone.utc).isoformat(),
            "level": record.levelname,
            "request_id": get_request_id(),
            "logger": record.name,
            "message": record.getMessage(),
        }

        # Merge any extra structured fields passed via `extra={}`
        for key in ("event", "data"):
            val = getattr(record, key, None)
            if val is not None:
                log_entry[key] = val

        # Include exception info if present
        if record.exc_info and record.exc_info[0] is not None:
            log_entry["exception"] = self.formatException(record.exc_info)

        return json.dumps(log_entry, default=str, ensure_ascii=False)


# ── RAGAS Evaluation Logger ──────────────────────────────────────────────────
# Writes {query, contexts, answer, request_id, timestamp} to a separate file
# so you can directly feed it into ragas.evaluate() for accuracy testing.

_rag_eval_logger: logging.Logger | None = None


def log_rag_evaluation(
    query: str,
    contexts: list[str],
    answer: str,
    retrieval_scores: list[float] | None = None,
    latency_ms: float | None = None,
) -> None:
    """
    Log a complete RAG interaction for RAGAS evaluation.
    
    This writes to logs/rag_eval.jsonl in the exact format RAGAS expects:
      - question (str)
      - contexts (list[str])  
      - answer (str)
    Plus extra metadata for debugging.
    """
    if _rag_eval_logger is None:
        return

    eval_entry = {
        "timestamp": datetime.now(timezone.utc).isoformat(),
        "request_id": get_request_id(),
        "question": query,
        "contexts": contexts,
        "answer": answer,
    }

    if retrieval_scores is not None:
        eval_entry["retrieval_scores"] = retrieval_scores

    if latency_ms is not None:
        eval_entry["total_latency_ms"] = latency_ms

    _rag_eval_logger.info(json.dumps(eval_entry, default=str, ensure_ascii=False))


# ── Configure Logging ────────────────────────────────────────────────────────

_configured = False


def configure_logging() -> None:
    """
    Call once at application startup (before any other imports that log).
    Idempotent — safe to call multiple times.
    """
    global _configured, _rag_eval_logger

    if _configured:
        return
    _configured = True

    log_level = os.environ.get("LOG_LEVEL", "INFO").upper()
    log_dir = Path(__file__).parent.parent / "logs"
    log_dir.mkdir(exist_ok=True)

    json_formatter = JSONFormatter()

    # ── Console handler (stdout for Docker) ──────────────────────────────
    console_handler = logging.StreamHandler()
    console_handler.setFormatter(json_formatter)

    # ── File handler with rotation (10MB, 5 backups) ─────────────────────
    file_handler = logging.handlers.RotatingFileHandler(
        filename=log_dir / "rag.log",
        maxBytes=10 * 1024 * 1024,  # 10 MB
        backupCount=5,
        encoding="utf-8",
    )
    file_handler.setFormatter(json_formatter)

    # ── Apply to root logger ─────────────────────────────────────────────
    root = logging.getLogger()
    root.setLevel(getattr(logging, log_level, logging.INFO))

    # Clear existing handlers to avoid duplicates on reload
    root.handlers.clear()
    root.addHandler(console_handler)
    root.addHandler(file_handler)

    # ── Quiet noisy third-party loggers ──────────────────────────────────
    for noisy in ("httpx", "httpcore", "urllib3", "pinecone", "openai", "nemoguardrails", "langsmith"):
        logging.getLogger(noisy).setLevel(logging.WARNING)

    # ── RAGAS evaluation logger (separate file, plain JSONL) ─────────────
    _rag_eval_logger = logging.getLogger("rag_eval")
    _rag_eval_logger.setLevel(logging.INFO)
    _rag_eval_logger.propagate = False  # don't duplicate into main log

    eval_handler = logging.handlers.RotatingFileHandler(
        filename=log_dir / "rag_eval.jsonl",
        maxBytes=50 * 1024 * 1024,  # 50 MB (evaluation data can be large)
        backupCount=3,
        encoding="utf-8",
    )
    # Plain formatter — the message IS the JSON line
    eval_handler.setFormatter(logging.Formatter("%(message)s"))
    _rag_eval_logger.handlers.clear()
    _rag_eval_logger.addHandler(eval_handler)

    logging.getLogger(__name__).info(
        "Logging configured",
        extra={"event": "logging_init", "data": {"level": log_level, "log_dir": str(log_dir)}},
    )
