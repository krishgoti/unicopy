"""
Document Registry — backed by MySQL
=====================================
Provides document listing for the chatbot.
All data comes from the 'chatbot_documents' table in the unihelp database.
"""

from services.db import get_all_documents, document_exists


def list_documents() -> list[dict]:
    """
    Return all registered documents.

    Each dict contains: doc_id, filename, chunk_count, uploaded_at
    """
    return get_all_documents()