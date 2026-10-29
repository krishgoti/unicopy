import time
import logging
from fastapi import APIRouter, Request, Depends
from pydantic import BaseModel
from services.chat_service import ask_question
from middleware.rate_limiter import limiter, RATE_LIMIT_CHAT
from middleware.input_validator import validate_chat_input
from middleware.auth import require_user_or_admin
from services.logging_config import get_request_id

logger = logging.getLogger("route.chat")

router = APIRouter()

class ChatRequest(BaseModel):
    question: str
    user_context: str | None = None  # optional live context from PHP (student profile, skills, progress)
    history: list[dict] = []         # optional chat history for short-term memory

class ChatResponse(BaseModel):
    answer: str


@router.post("/chat", response_model=ChatResponse, dependencies=[Depends(require_user_or_admin)])
@limiter.limit(RATE_LIMIT_CHAT)
async def chat_endpoint(request: Request, body: ChatRequest):

    t0 = time.time()

    logger.info(
        f"Chat request received",
        extra={
            "event": "chat_request",
            "data": {
                "question_len": len(body.question),
                "has_context": body.user_context is not None,
                "history_len": len(body.history),
            },
        },
    )

    # Guardrail #2: Validate & sanitize input
    clean_question = validate_chat_input(body.question)

    answer = await ask_question(clean_question, user_context=body.user_context, history=body.history)

    latency_ms = round((time.time() - t0) * 1000, 2)

    logger.info(
        f"Chat response sent",
        extra={
            "event": "chat_response",
            "data": {
                "answer_len": len(answer),
                "total_endpoint_latency_ms": latency_ms,
            },
        },
    )

    return ChatResponse(answer=answer)
