
# ── Initialize structured logging FIRST (before any module-level loggers) ────
from services.logging_config import configure_logging
configure_logging()

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from contextlib import asynccontextmanager
import logging

from api.routes.upload import router as upload_router
from api.routes.documents import router as documents_router
from api.routes.delete import router as delete_router
from api.routes.chat import router as chat_router

from middleware.rate_limiter import setup_rate_limiter
from middleware.security_headers import setup_security_headers
from middleware.request_logger import RequestLoggerMiddleware
from middleware.error_handler import global_exception_handler
from services.hybrid_retriever import get_pinecone_index

logger = logging.getLogger(__name__)


@asynccontextmanager
async def lifespan(app: FastAPI):
    # ── Startup: warm up NeMo Guardrails ─────────────────────────────────────
    # NeMo downloads HuggingFace model files on first use (~18s cold start).
    # Firing a dummy request at boot moves that delay to startup, not user req #1.
    try:
        logger.info("Warming up NeMo Guardrails (pre-loading HF models)...")
        from services.chat_service import agent_with_rails
        await agent_with_rails.ainvoke({"input": "hi"})
        logger.info("NeMo Guardrails warm-up complete.")
    except Exception as e:
        logger.warning(f"Guardrails warm-up failed (non-fatal): {e}")
    yield
    # ── Shutdown ──────────────────────────────────────────────────────────────


app = FastAPI(title="RAG Backend", lifespan=lifespan)

# ── Global Error Handler ─────────────────────────────────────────────────────
app.add_exception_handler(Exception, global_exception_handler)
# ── Security & Middlewares ───────────────────────────────────────────────────
setup_rate_limiter(app)
setup_security_headers(app)

# Structured JSON Logger - Tracks latency, status codes, methods
app.add_middleware(RequestLoggerMiddleware)

# Add CORS Middleware LAST so it acts as the OUTERMOST layer intercepting OPTIONS early
app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost", "http://127.0.0.1", "http://localhost:80"],
    allow_credentials=True,
    allow_methods=["GET", "POST", "DELETE", "OPTIONS"],
    allow_headers=["*"],
)

# include routes
app.include_router(upload_router, prefix="/api", tags=["Upload"])
app.include_router(documents_router, prefix="/api", tags=["Documents"])
app.include_router(delete_router, prefix="/api", tags=["Delete"])
app.include_router(chat_router, prefix="/api", tags=["Chat"])

@app.get("/")
async def root():
	return {"message": "Welcome to RAG"}

@app.get("/health")
async def health_check():
    health_status = {"status": "OK", "pinecone": "checking..."}
    try:
        # Deep health check - actually ping the Pinecone DB to ensure it's alive
        idx = get_pinecone_index()
        stats = idx.describe_index_stats()
        health_status["pinecone"] = "connected"
        health_status["total_vectors"] = stats.get("total_vector_count", 0)
    except Exception as e:
        health_status["status"] = "DEGRADED"
        health_status["pinecone"] = "disconnected"
        health_status["error"] = str(e)
    
    return health_status

if __name__ == "__main__":
	import uvicorn
	uvicorn.run(app, host="0.0.0.0", port=8000)