import time
import logging
from fastapi import APIRouter, UploadFile, File, HTTPException, Request, status, Depends
from typing import List, Annotated
import tempfile
import os

from services.vectorstore import ingest_pdf
from services.db import register_document
from middleware.rate_limiter import limiter, RATE_LIMIT_UPLOAD
from middleware.input_validator import validate_upload_files, validate_file_size
from middleware.auth import require_admin_only

logger = logging.getLogger("route.upload")

router = APIRouter()


@router.post("/upload", dependencies=[Depends(require_admin_only)])
@limiter.limit(RATE_LIMIT_UPLOAD)
async def upload_file(
    request: Request,
    files: Annotated[List[UploadFile], File(..., description="Multiple PDF Files to upload")]
):

    # Guardrail #2: Validate file count, types, and filenames
    validate_upload_files(files)

    logger.info(
        f"Upload request: {len(files)} file(s)",
        extra={
            "event": "upload_request",
            "data": {
                "file_count": len(files),
                "filenames": [str(f.filename) for f in files],
            },
        },
    )

    total_chunks = 0
    processed_files = []

    for f in files:

        tmp_path = None
        file_start = time.time()

        try:
            # Guardrail #2: Validate file size (20MB limit) while reading
            content = await validate_file_size(f)

            logger.info(
                f"Processing file: {f.filename} ({len(content)} bytes)",
                extra={
                    "event": "file_processing",
                    "data": {"filename": str(f.filename), "size_bytes": len(content)},
                },
            )

            # Save validated content to temp file
            with tempfile.NamedTemporaryFile(delete=False, suffix=".pdf") as tmp:
                tmp.write(content)
                tmp_path = tmp.name

            # ingest into Pinecone
            doc_id, chunk_count = await ingest_pdf(tmp_path, str(f.filename))

            # register in MySQL
            register_document(doc_id, str(f.filename), chunk_count)

            file_latency_ms = round((time.time() - file_start) * 1000, 2)

            total_chunks += chunk_count
            processed_files.append({
                "filename": f.filename,
                "doc_id": doc_id,
                "chunks": chunk_count,
            })

            logger.info(
                f"File ingested: {f.filename} → {chunk_count} chunks in {file_latency_ms}ms",
                extra={
                    "event": "file_ingested",
                    "data": {
                        "filename": str(f.filename),
                        "doc_id": doc_id,
                        "chunk_count": chunk_count,
                        "latency_ms": file_latency_ms,
                    },
                },
            )

        except HTTPException:
            raise  # Re-raise validation errors as-is

        except Exception as e:
            logger.error(
                f"File ingestion failed: {f.filename} — {type(e).__name__}: {e}",
                extra={
                    "event": "file_ingestion_error",
                    "data": {
                        "filename": str(f.filename),
                        "error_class": type(e).__name__,
                        "error": str(e),
                    },
                },
                exc_info=True,
            )
            raise HTTPException(
                status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
                detail=str(e)
            )

        finally:
            if tmp_path and os.path.exists(tmp_path):
                os.remove(tmp_path)

    logger.info(
        f"Upload complete: {len(processed_files)} files, {total_chunks} total chunks",
        extra={
            "event": "upload_complete",
            "data": {
                "files_processed": len(processed_files),
                "total_chunks": total_chunks,
            },
        },
    )

    return {
        "message": "Files successfully added to knowledge base",
        "files_processed": processed_files,
        "total_chunks_added": total_chunks
    }
