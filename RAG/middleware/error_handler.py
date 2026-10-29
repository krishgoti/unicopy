from fastapi import Request
from fastapi.responses import JSONResponse
import logging

logger = logging.getLogger("api_error_handler")

async def global_exception_handler(request: Request, exc: Exception):
    """
    Catches any unhandled 500 Internal Server Errors bubbling up to FastAPI.
    Logs the full stack trace internally but returns a safe, sanitized JSON to the user.
    Prevents path leakage and database credential disclosure.
    """
    error_msg = str(exc)
    
    # Send detailed log to backend console
    logger.error(f"Global Exception caught on {request.method} {request.url.path}: {error_msg}", exc_info=exc)
    
    # Send sanitized payload to frontend
    return JSONResponse(
        status_code=500,
        content={"error": "An internal server error occurred.", "code": "ERR_INTERNAL"}
    )
