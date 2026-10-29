from langchain_community.document_loaders import PyPDFLoader
from langchain_community.retrievers import PineconeHybridSearchRetriever
from pinecone import Pinecone
from langchain_text_splitters import RecursiveCharacterTextSplitter

import hashlib
import os

from fastapi.concurrency import run_in_threadpool

from dotenv import load_dotenv
load_dotenv()


def create_doc_id(filename: str) -> str:
    """Generate a unique document ID (MD5 hash of filename)."""
    return hashlib.md5(filename.encode()).hexdigest()


splitter = RecursiveCharacterTextSplitter(chunk_size=1500, chunk_overlap=300)

INDEX_NAME = os.environ.get("PINECONE_INDEX")


async def ingest_pdf(tmp_path: str, filename: str) -> tuple[str, int]:
    """
    Loads a PDF, splits it, and stores into vector DB.
    Returns (doc_id, chunk_count).
    """
    doc_id = create_doc_id(filename)

    loader = PyPDFLoader(tmp_path)
    document = await run_in_threadpool(loader.load)

    chunks = await run_in_threadpool(splitter.split_documents, document)

    clean_chunks = []

    for c in chunks:
        text = c.page_content

        # must be string
        if not isinstance(text, str):
            continue

        # remove null characters from PDFs
        text = text.replace("\x00", " ")

        # remove broken Unicode surrogate pairs (e.g. from math symbols in PDFs)
        text = text.encode("utf-8", errors="surrogatepass").decode("utf-8", errors="ignore")

        # remove leading/trailing whitespace
        text = text.strip()

        # skip garbage chunks
        if len(text) < 10:
            continue

        c.page_content = text
        c.metadata["source"] = filename
        c.metadata["doc_id"] = doc_id

        # Ensure all metadata values are strings (fixes TextEncodeInput error)
        c.metadata = {k: str(v) for k, v in c.metadata.items()}

        clean_chunks.append(c)

    chunks = clean_chunks

    # ── Ingest into hybrid index (sparse BM25 + dense vectors) ──────────────────
    # PineconeHybridSearchRetriever.add_texts() stores text under "context" key.
    # We must NOT use PineconeVectorStore here — it stores under "text" key and
    # would cause a KeyError('context') when the hybrid retriever queries results.
    from services.hybrid_retriever import (
        get_embeddings, get_bm25, get_pinecone_index
    )

    hybrid_retriever = PineconeHybridSearchRetriever(
        embeddings=get_embeddings(),
        sparse_encoder=get_bm25(),
        index=get_pinecone_index(),
        top_k=3,
        alpha=0.5,
    )

    texts = [c.page_content for c in chunks]
    metadatas = [c.metadata for c in chunks]

    await run_in_threadpool(hybrid_retriever.add_texts, texts, metadatas=metadatas)

    return doc_id, len(chunks)


_pc = Pinecone(api_key=os.environ.get("PINECONE_API_KEY"))
_index = _pc.Index(INDEX_NAME)


async def delete_document(doc_id: str):
    """Delete all vectors belonging to a specific document by its doc_id metadata."""
    await run_in_threadpool(
        _index.delete, filter={"doc_id": {"$eq": doc_id}}
    )