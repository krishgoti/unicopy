import time
import logging
from fastapi import APIRouter, HTTPException, Request, Depends
from services.vectorstore import delete_document
from services.db import document_exists, unregister_document
from middleware.rate_limiter import limiter, RATE_LIMIT_DELETE
from middleware.auth import require_admin_only

logger = logging.getLogger("route.delete")

router = APIRouter()


@router.delete("/documents/{doc_id}", dependencies=[Depends(require_admin_only)])
@limiter.limit(RATE_LIMIT_DELETE)
async def remove_doc(request: Request, doc_id: str):

    logger.info(
        f"Delete request for doc_id={doc_id}",
        extra={"event": "delete_request", "data": {"doc_id": doc_id}},
    )

    t0 = time.time()

    if not document_exists(doc_id):
        logger.warning(
            f"Delete failed: doc_id={doc_id} not found",
            extra={"event": "delete_not_found", "data": {"doc_id": doc_id}},
        )
        raise HTTPException(status_code=404, detail="Document not found")

    # delete vectors from Pinecone (by doc_id metadata filter)
    await delete_document(doc_id)

    # remove record from MySQL
    unregister_document(doc_id)

    latency_ms = round((time.time() - t0) * 1000, 2)

    logger.info(
        f"Document deleted: doc_id={doc_id} in {latency_ms}ms",
        extra={
            "event": "delete_complete",
            "data": {"doc_id": doc_id, "latency_ms": latency_ms},
        },
    )

    return {"message": "Document deleted successfully"}
