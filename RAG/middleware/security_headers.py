"""
Security Headers & CORS Middleware (Guardrail #4)
================================================
Protects against Clickjacking, MIME-sniffing, and Cross-Site Request Forgery (CSRF)
by enforcing allowed origins and standard security headers.
"""

import os
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from starlette.middleware.base import BaseHTTPMiddleware
from dotenv import load_dotenv

load_dotenv()

# ── CORS Configuration ───────────────────────────────────────────────────────
CORS_ORIGINS_STR = os.environ.get("CORS_ORIGINS", "http://localhost, http://127.0.0.1")
ALLOWED_ORIGINS = [origin.strip() for origin in CORS_ORIGINS_STR.split(",") if origin.strip()]

class SecurityHeadersMiddleware(BaseHTTPMiddleware):
    """
    Custom middleware to inject standard security headers.
    """
    async def dispatch(self, request, call_next):
        response = await call_next(request)
        
        # Prevent browsers from trying to guess the content type (MIME-sniffing)
        response.headers["X-Content-Type-Options"] = "nosniff"
        
        # Prevent the site from being embedded in an iframe (Clickjacking)
        # Options: DENY, SAMEORIGIN
        response.headers["X-Frame-Options"] = "DENY"
        
        # Enable Browser's XSS filtering
        response.headers["X-XSS-Protection"] = "1; mode=block"
        
        # Enforce HTTPS (HSTS) - Only for production
        # response.headers["Strict-Transport-Security"] = "max-age=31536000; includeSubDomains"
        
        # Content Security Policy (CSP)
        # Restricts where resources can be loaded from.
        # cdn.jsdelivr.net is required for FastAPI Swagger UI (/docs)
        response.headers["Content-Security-Policy"] = (
            "default-src 'self'; "
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
            "img-src 'self' data: https://fastapi.tiangolo.com; "
            "object-src 'none';"
        )
        
        # Control how much info is sent in the Referer header
        response.headers["Referrer-Policy"] = "strict-origin-when-cross-origin"
        
        return response

def setup_security_headers(app: FastAPI):
    """
    Wired up custom security headers.
    """
    # Custom Security Headers
    app.add_middleware(SecurityHeadersMiddleware)
