"""
Input Validator Middleware (Guardrail #2)
=========================================
Validates and sanitizes all user inputs before they reach the service layer.

Protections:
  - Max question length (prevents prompt flooding)
  - Max upload file size (prevents memory exhaustion)
  - Max files per upload request
  - Filename sanitization (prevents path traversal)
  - SQL injection pattern detection (defense-in-depth on top of parameterized queries)
"""

import os
import re
from fastapi import HTTPException, UploadFile, status

from dotenv import load_dotenv
load_dotenv()


# ── Configuration ────────────────────────────────────────────────────────────
MAX_QUESTION_LENGTH  = int(os.environ.get("MAX_QUESTION_LENGTH", "5000"))
MAX_UPLOAD_SIZE_MB   = int(os.environ.get("MAX_UPLOAD_SIZE_MB", "20"))
MAX_UPLOAD_SIZE_BYTES = MAX_UPLOAD_SIZE_MB * 1024 * 1024
MAX_FILES_PER_REQUEST = int(os.environ.get("MAX_FILES_PER_REQUEST", "10"))


# ── SQL Injection Patterns ───────────────────────────────────────────────────
# Defense-in-depth: catches obvious injection attempts before they hit the DB.
# This does NOT replace parameterized queries — it's an extra safety net.
_SQL_INJECTION_PATTERNS = re.compile(
    r"""
    (?:--|;)\s*$               |   # SQL comment / statement terminator
    '\s*(?:OR|AND)\s+.*=       |   # ' OR 1=1, ' AND ''='
    UNION\s+(?:ALL\s+)?SELECT  |   # UNION SELECT
    INSERT\s+INTO              |   # INSERT INTO
    DELETE\s+FROM              |   # DELETE FROM
    DROP\s+TABLE               |   # DROP TABLE
    ALTER\s+TABLE              |   # ALTER TABLE
    EXEC(?:UTE)?\s             |   # EXEC / EXECUTE
    xp_\w+                     |   # SQL Server extended procs
    0x[0-9a-fA-F]+             |   # Hex-encoded payloads
    CHAR\s*\(\s*\d+\s*\)       |   # CHAR() encoding bypass
    CONCAT\s*\(                |   # CONCAT() abuse
    BENCHMARK\s*\(             |   # Time-based blind SQLi
    SLEEP\s*\(                 |   # Time-based blind SQLi
    LOAD_FILE\s*\(             |   # File read attack
    INTO\s+(?:OUT|DUMP)FILE    |   # File write attack
    Admin'--                   |   # Admin'--
    admin'--                       # admin'--
    """,
    re.IGNORECASE | re.VERBOSE,
)

# ── Dangerous filename characters ────────────────────────────────────────────
_UNSAFE_FILENAME_CHARS = re.compile(r'[<>:"/\\|?*\x00-\x1f]')
_PATH_TRAVERSAL = re.compile(r'(?:^|[\\/])\.\.(?:[\\/]|$)')


# ── Validators ───────────────────────────────────────────────────────────────

def validate_chat_input(question: str) -> str:
    """
    Validate and sanitize a chat question.
    Returns the cleaned question or raises HTTPException.
    """
    if not question or not question.strip():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Question cannot be empty.",
        )

    question = question.strip()

    if len(question) > MAX_QUESTION_LENGTH:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"Question too long. Maximum {MAX_QUESTION_LENGTH} characters allowed "
                   f"(received {len(question)}).",
        )

    # Check for SQL injection patterns in the question
    if _SQL_INJECTION_PATTERNS.search(question):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Input contains potentially unsafe content.",
        )

    return question


def validate_upload_files(files: list[UploadFile]) -> None:
    """
    Validate upload request: file count, file types, and filenames.
    File SIZE is checked separately per-file during streaming (see validate_file_size).
    """
    if len(files) > MAX_FILES_PER_REQUEST:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail=f"Too many files. Maximum {MAX_FILES_PER_REQUEST} files per request "
                   f"(received {len(files)}).",
        )

    for f in files:
        filename = str(f.filename or "")

        # Must be PDF
        if not filename.lower().endswith(".pdf"):
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail=f"'{filename}' is not a PDF file. Only .pdf files are allowed.",
            )

        # Block path traversal (../../etc/passwd.pdf)
        if _PATH_TRAVERSAL.search(filename):
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail=f"Invalid filename: '{filename}'. Path traversal not allowed.",
            )

        # Block dangerous characters
        if _UNSAFE_FILENAME_CHARS.search(filename):
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail=f"Invalid filename: '{filename}'. Contains unsafe characters.",
            )


async def validate_file_size(file: UploadFile) -> bytes:
    """
    Read the file in chunks and enforce the size limit.
    Returns the file content as bytes if valid.
    Raises HTTPException if file exceeds MAX_UPLOAD_SIZE_MB.
    """
    content = b""
    while chunk := await file.read(1024 * 1024):  # 1MB chunks
        content += chunk
        if len(content) > MAX_UPLOAD_SIZE_BYTES:
            raise HTTPException(
                status_code=status.HTTP_413_REQUEST_ENTITY_TOO_LARGE,
                detail=f"File '{file.filename}' exceeds the {MAX_UPLOAD_SIZE_MB}MB size limit.",
            )
    return content


def sanitize_for_db(value: str) -> str:
    """
    Extra sanitization for values going into DB queries.
    This is defense-in-depth — parameterized queries (%s) are the primary protection.
    """
    if _SQL_INJECTION_PATTERNS.search(value):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Input contains potentially unsafe content.",
        )
    return value
