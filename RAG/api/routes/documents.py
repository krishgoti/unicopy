from fastapi import APIRouter, Request, Depends
from services.document_registry import list_documents
from middleware.rate_limiter import limiter, RATE_LIMIT_DOCUMENTS
from middleware.auth import require_admin_only

router = APIRouter()


@router.get("/documents", dependencies=[Depends(require_admin_only)])
@limiter.limit(RATE_LIMIT_DOCUMENTS)
async def get_documents(request: Request):
    docs = list_documents()

    return {
        "total_documents": len(docs),
        "documents": [
            {
                "doc_id": doc["doc_id"],
                "filename": doc["filename"],
                "chunk_count": doc["chunk_count"],
                "uploaded_at": str(doc["uploaded_at"]),
            }
            for doc in docs
        ]
    }
