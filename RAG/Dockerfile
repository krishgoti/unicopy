# ── Base image ──────────────────────────────────────────────
FROM python:3.13-slim AS base

# Prevent Python from writing .pyc (log, __pycache__) files and enable unbuffered output (makes print() and log appear in docker log)
ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1

# ── System dependencies ────────────────────────────────────
RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    build-essential \
    gcc \
    g++ \
    && rm -rf /var/lib/apt/lists/*

# ── Working directory ──────────────────────────────────────
WORKDIR /app

# ── Install Python dependencies ────────────────────────────
# Copy requirements first for better Docker layer caching
COPY requirements.txt .
RUN pip install --no-cache-dir --upgrade pip && \
    pip install --no-cache-dir -r requirements.txt && \
    python -c "import nltk; nltk.download('punkt_tab'); nltk.download('stopwords')"

# ── Copy application code ─────────────────────────────────
COPY . .

# ── Expose the FastAPI port ────────────────────────────────
EXPOSE 8000

# ── Health check ───────────────────────────────────────────
HEALTHCHECK --interval=90s --timeout=10s --start-period=15s --retries=3 \
    CMD python -c "import urllib.request; urllib.request.urlopen('http://localhost:8000/health')" || exit 1

# ── Run the application ───────────────────────────────────
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]
