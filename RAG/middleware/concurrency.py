import asyncio
from fastapi import HTTPException

# Maximum number of concurrent LLM requests allowed at the exact same time
MAX_CONCURRENT_REQS = 10

class LLMConcurrencyManager:
    """Singleton to manage a global asyncio Semaphore for LLM/RAG load-balancing"""
    _semaphore: asyncio.Semaphore | None = None

    @classmethod
    def get_semaphore(cls) -> asyncio.Semaphore:
        # Lazy initialization inside the event loop
        if cls._semaphore is None:
            cls._semaphore = asyncio.Semaphore(MAX_CONCURRENT_REQS)
        return cls._semaphore

async def acquire_llm_slot():
    """
    Tries to acquire a semaphore slot without waiting forever. 
    If the server is overloaded, instantly rejects the user with 429 to prevent stalling.
    """
    sem = LLMConcurrencyManager.get_semaphore()
    if sem.locked():
        # Optional: could wait a few seconds instead of instant rejection, but fail-fast is safer.
        raise HTTPException(
            status_code=429, 
            detail="The AI is currently processing at maximum capacity. Please try again in a few seconds."
        )
    return await sem.acquire()

def release_llm_slot():
    sem = LLMConcurrencyManager.get_semaphore()
    sem.release()
