"""
MySQL Database Service for Chatbot Document Registry
=====================================================
Connects to the 'unihelp' MySQL database to track which documents
have been uploaded to the Pinecone vector store.

Table: chatbot_documents (doc_id, filename, chunk_count, uploaded_at)
"""

import os
import time
import logging
import pymysql
from dotenv import load_dotenv

load_dotenv()

logger = logging.getLogger("db_service")

DB_HOST = os.environ.get("DB_HOST", "127.0.0.1")
DB_NAME = os.environ.get("DB_NAME", "unihelp")
DB_USER = os.environ.get("DB_USER", "root")
DB_PASS = os.environ.get("DB_PASS", "")


def _get_connection():
    try:
        t0 = time.time()
        conn = pymysql.connect(
            host=DB_HOST,
            user=DB_USER,
            password=DB_PASS,
            database=DB_NAME,
            charset="utf8mb4",
            cursorclass=pymysql.cursors.DictCursor,
            connect_timeout=5,     # Guardrail #2: fail fast if MySQL is unreachable
            read_timeout=10,       # Guardrail #2: don't hang on slow queries
        )
        latency_ms = round((time.time() - t0) * 1000, 2)
        logger.debug(
            f"MySQL connection established in {latency_ms}ms",
            extra={
                "event": "db_connect",
                "data": {"host": DB_HOST, "database": DB_NAME, "latency_ms": latency_ms},
            },
        )
        return conn
    except Exception as e:
        logger.error(
            f"MySQL connection failed: {type(e).__name__}: {e}",
            extra={
                "event": "db_connect_error",
                "data": {
                    "host": DB_HOST,
                    "database": DB_NAME,
                    "error_class": type(e).__name__,
                    "error": str(e),
                },
            },
            exc_info=True,
        )
        raise


def register_document(doc_id: str, filename: str, chunk_count: int) -> None:
    """Insert a new document record into chatbot_documents."""
    t0 = time.time()
    conn = _get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute(
                """
                INSERT INTO chatbot_documents (doc_id, filename, chunk_count)
                VALUES (%s, %s, %s)
                ON DUPLICATE KEY UPDATE
                    filename = VALUES(filename),
                    chunk_count = VALUES(chunk_count),
                    uploaded_at = CURRENT_TIMESTAMP
                """,
                (doc_id, filename, chunk_count),
            )
        conn.commit()
        latency_ms = round((time.time() - t0) * 1000, 2)
        logger.info(
            f"Document registered: {filename} (doc_id={doc_id}, chunks={chunk_count})",
            extra={
                "event": "db_register",
                "data": {
                    "doc_id": doc_id,
                    "filename": filename,
                    "chunk_count": chunk_count,
                    "latency_ms": latency_ms,
                },
            },
        )
    except Exception as e:
        latency_ms = round((time.time() - t0) * 1000, 2)
        logger.error(
            f"register_document failed: {type(e).__name__}: {e}",
            extra={
                "event": "db_register_error",
                "data": {
                    "doc_id": doc_id,
                    "filename": filename,
                    "error_class": type(e).__name__,
                    "error": str(e),
                    "latency_ms": latency_ms,
                },
            },
            exc_info=True,
        )
        raise
    finally:
        conn.close()


def unregister_document(doc_id: str) -> None:
    """Remove a document record from chatbot_documents."""
    t0 = time.time()
    conn = _get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute(
                "DELETE FROM chatbot_documents WHERE doc_id = %s",
                (doc_id,),
            )
        conn.commit()
        latency_ms = round((time.time() - t0) * 1000, 2)
        logger.info(
            f"Document unregistered: doc_id={doc_id}",
            extra={
                "event": "db_unregister",
                "data": {"doc_id": doc_id, "latency_ms": latency_ms},
            },
        )
    except Exception as e:
        latency_ms = round((time.time() - t0) * 1000, 2)
        logger.error(
            f"unregister_document failed: {type(e).__name__}: {e}",
            extra={
                "event": "db_unregister_error",
                "data": {
                    "doc_id": doc_id,
                    "error_class": type(e).__name__,
                    "error": str(e),
                    "latency_ms": latency_ms,
                },
            },
            exc_info=True,
        )
        raise
    finally:
        conn.close()


def get_all_documents() -> list[dict]:
    """Return all registered documents as a list of dicts."""
    t0 = time.time()
    conn = _get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute(
                "SELECT doc_id, filename, chunk_count, uploaded_at FROM chatbot_documents ORDER BY uploaded_at DESC"
            )
            results = cursor.fetchall()
        latency_ms = round((time.time() - t0) * 1000, 2)
        logger.info(
            f"get_all_documents: {len(results)} docs in {latency_ms}ms",
            extra={
                "event": "db_list",
                "data": {"doc_count": len(results), "latency_ms": latency_ms},
            },
        )
        return results
    except Exception as e:
        latency_ms = round((time.time() - t0) * 1000, 2)
        logger.error(
            f"get_all_documents failed: {type(e).__name__}: {e}",
            extra={
                "event": "db_list_error",
                "data": {
                    "error_class": type(e).__name__,
                    "error": str(e),
                    "latency_ms": latency_ms,
                },
            },
            exc_info=True,
        )
        raise
    finally:
        conn.close()


def document_exists(doc_id: str) -> bool:
    """Check if a document with the given doc_id exists."""
    t0 = time.time()
    conn = _get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute(
                "SELECT 1 FROM chatbot_documents WHERE doc_id = %s",
                (doc_id,),
            )
            exists = cursor.fetchone() is not None
        latency_ms = round((time.time() - t0) * 1000, 2)
        logger.debug(
            f"document_exists({doc_id}): {exists} in {latency_ms}ms",
            extra={
                "event": "db_exists",
                "data": {"doc_id": doc_id, "exists": exists, "latency_ms": latency_ms},
            },
        )
        return exists
    except Exception as e:
        latency_ms = round((time.time() - t0) * 1000, 2)
        logger.error(
            f"document_exists failed: {type(e).__name__}: {e}",
            extra={
                "event": "db_exists_error",
                "data": {
                    "doc_id": doc_id,
                    "error_class": type(e).__name__,
                    "error": str(e),
                    "latency_ms": latency_ms,
                },
            },
            exc_info=True,
        )
        raise
    finally:
        conn.close()
