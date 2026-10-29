"""
Authentication Middleware (Guardrail #3)
========================================
Validates JWT tokens and enforces Role-Based Access Control (RBAC).

Roles:
  - 'admin': Can access all routes (chat, upload, delete, documents, health)
  - 'user':  Can only access the chat route
"""

import os
import jwt
from typing import Optional
from fastapi import Request, HTTPException, Security, status
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials

from dotenv import load_dotenv
load_dotenv()

# ── Configuration ────────────────────────────────────────────────────────────
# Must match the secret/algorithm used by Unicopy to sign tokens
JWT_SECRET_KEY = os.environ.get("JWT_SECRET_KEY", "super-secret-key-change-in-production")
JWT_ALGORITHM  = os.environ.get("JWT_ALGORITHM", "HS256")

# FastAPI security scheme for Swagger UI & token extraction
security = HTTPBearer()

def verify_jwt_token(credentials: HTTPAuthorizationCredentials = Security(security)) -> dict:
    """Verifies the JWT token and returns the decoded payload."""
    token = credentials.credentials
    try:
        payload = jwt.decode(token, JWT_SECRET_KEY, algorithms=[JWT_ALGORITHM])
        return payload
    except jwt.ExpiredSignatureError:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Token has expired",
            headers={"WWW-Authenticate": "Bearer"},
        )
    except jwt.InvalidTokenError:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid token",
            headers={"WWW-Authenticate": "Bearer"},
        )

def require_role(allowed_roles: list[str]):
    """
    Dependency generator for RBAC.
    Usage: @router.get("/", dependencies=[Depends(require_role(["admin"]))])
    """
    def role_checker(payload: dict = Security(verify_jwt_token)):
        user_role = payload.get("role")
        if not user_role or user_role not in allowed_roles:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail=f"Insufficient permissions. Required role: {allowed_roles}, but got: {user_role}",
            )
        return payload
    return role_checker

# Pre-configured dependencies for routes
require_user_or_admin = require_role(["user", "admin"])
require_admin_only = require_role(["admin"])
