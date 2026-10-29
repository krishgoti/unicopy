"""
Rate Limiter Middleware (Guardrail #1)
=====================================
Uses slowapi to enforce per-IP, per-route rate limits.

Defaults (configurable via .env):
  - Chat:      30 requests/minute
  - Upload:    10 requests/minute
  - Documents: 60 requests/minute
  - Delete:    10 requests/minute
  - Global:   200 requests/minute (shared across all routes per IP)
"""

import os

from slowapi import Limiter
from slowapi.util import get_remote_address
from slowapi.errors import RateLimitExceeded
from slowapi.middleware import SlowAPIMiddleware

from fastapi import Request
from fastapi.responses import JSONResponse

from dotenv import load_dotenv
load_dotenv()

# ── Rate limit values from env (with sensible defaults) ──────────────────────
RATE_LIMIT_CHAT      = os.environ.get("RATE_LIMIT_CHAT",      "30/minute")
RATE_LIMIT_UPLOAD    = os.environ.get("RATE_LIMIT_UPLOAD",     "10/minute")
RATE_LIMIT_DOCUMENTS = os.environ.get("RATE_LIMIT_DOCUMENTS",  "60/minute")
RATE_LIMIT_DELETE    = os.environ.get("RATE_LIMIT_DELETE",      "10/minute")


# ── Limiter instance ─────────────────────────────────────────────────────────
# key_func = get_remote_address → rate-limits per client IP
# application_limits → shared budget across ALL routes for a single IP
limiter = Limiter(
    key_func=get_remote_address,
    application_limits=["200/minute"],
    default_limits=["60/minute"],
)


# ── Custom 429 response ─────────────────────────────────────────────────────
async def rate_limit_exceeded_handler(request: Request, exc: RateLimitExceeded):
    """Return a clean JSON 429 instead of the default plaintext response."""
    return JSONResponse(
        status_code=429,
        content={
            "error": "rate_limit_exceeded",
            "message": f"Too many requests. Limit: {exc.detail}",
            "retry_after": request.headers.get("Retry-After", "unknown"),
        },
    )


def setup_rate_limiter(app):
    """
    Call this in main.py to wire up rate limiting.

    Usage:
        from middleware.rate_limiter import setup_rate_limiter
        setup_rate_limiter(app)
    """
    app.state.limiter = limiter
    app.add_exception_handler(RateLimitExceeded, rate_limit_exceeded_handler)
    app.add_middleware(SlowAPIMiddleware)
