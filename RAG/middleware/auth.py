
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

from fastapi import HTTPException, Security, status
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from dotenv import load_dotenv

# Load environment variables from the RAG service's .env file.
_rag_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
load_dotenv(os.path.join(_rag_dir, ".env"))
load_dotenv()

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------

# Never use a hardcoded fallback for a JWT signing secret.
JWT_SECRET_KEY = os.environ.get("JWT_SECRET_KEY")
JWT_ALGORITHM = os.environ.get("JWT_ALGORITHM", "HS256")

if not JWT_SECRET_KEY:
    raise RuntimeError(
        "JWT_SECRET_KEY environment variable is required. "
        "Set it in the RAG service environment before starting the application."
    )

# FastAPI security scheme for Swagger UI and token extraction.
security = HTTPBearer()


def verify_jwt_token(
    credentials: HTTPAuthorizationCredentials = Security(security),
) -> dict:
    """Verify a JWT token and return its decoded payload."""
    token = credentials.credentials

    try:
        payload = jwt.decode(
            token,
            JWT_SECRET_KEY,
            algorithms=[JWT_ALGORITHM],
        )
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
    Create a dependency that checks the user's role.

    Example:
        @router.get(
            "/",
            dependencies=[Depends(require_role(["admin"]))],
        )
    """

    def role_checker(
        payload: dict = Security(verify_jwt_token),
    ) -> dict:
        user_role = payload.get("role")

        if not user_role or user_role not in allowed_roles:
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Insufficient permissions.",
            )

        return payload

    return role_checker


# Pre-configured dependencies for routes.
require_user_or_admin = require_role(["user", "admin"])
require_admin_only = require_role(["admin"])
