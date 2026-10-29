import time
import uuid
import logging
from starlette.middleware.base import BaseHTTPMiddleware
from starlette.responses import Response
from fastapi import Request

from services.logging_config import set_request_id, get_request_id, JSONFormatter

# Use the centralized JSON formatter
logger = logging.getLogger("api_request_logger")


class RequestLoggerMiddleware(BaseHTTPMiddleware):
    async def dispatch(self, request: Request, call_next):
        # ── Generate and propagate request_id ────────────────────────────
        request_id = uuid.uuid4().hex[:8]
        set_request_id(request_id)

        start_time = time.time()

        # Extract safe metadata
        method = request.method
        path = request.url.path
        client_ip = request.client.host if request.client else "unknown"

        # Pass through the actual request
        try:
            response: Response = await call_next(request)
            status_code = response.status_code
        except Exception as e:
            status_code = 500
            raise e
        finally:
            process_time_ms = round((time.time() - start_time) * 1000, 2)

            # Avoid logging excessively on spammy endpoints like /health 
            # (although it's good to log the duration to ensure db hasn't lagged)
            log_data = {
                "method": method,
                "path": path,
                "status": status_code,
                "latency_ms": process_time_ms,
                "ip": client_ip,
            }

            if status_code >= 400:
                logger.error(
                    f"{method} {path} {status_code} ({process_time_ms}ms)",
                    extra={"event": "http_request", "data": log_data},
                )
            else:
                logger.info(
                    f"{method} {path} {status_code} ({process_time_ms}ms)",
                    extra={"event": "http_request", "data": log_data},
                )

        # ── Attach request_id to response for frontend debugging ─────────
        response.headers["X-Request-ID"] = request_id
        return response
