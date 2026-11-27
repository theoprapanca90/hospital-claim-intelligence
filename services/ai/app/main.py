import time
import uuid

import httpx
from fastapi import Depends, FastAPI, HTTPException, status

from app.config import settings
from app.schemas import GenerateRequest, GenerateResponse
from app.security import verify_service_token

app = FastAPI(title=settings.app_name, version=settings.app_version)


@app.get("/health")
async def health():
    """Liveness endpoint; does not expose configuration or secrets."""
    return {"status": "ok", "service": "hci-ai-gateway"}


@app.get("/ready")
async def ready():
    """Check Ollama connectivity and configured model availability."""
    try:
        async with httpx.AsyncClient(timeout=5) as client:
            response = await client.get(f"{settings.ollama_base_url.rstrip('/')}/api/tags")
            response.raise_for_status()
            models = response.json().get("models", [])
        wanted = settings.ollama_model
        available = any(
            item.get("name") == wanted
            or item.get("name", "").split(":")[0] == wanted.split(":")[0]
            for item in models
        )
        if not available:
            raise HTTPException(status_code=503, detail="Configured AI model is not available")
        return {"ready": True, "ollama": "connected", "model_available": True}
    except httpx.HTTPError:
        raise HTTPException(status_code=503, detail="AI model runtime unavailable") from None


@app.post("/v1/generate", response_model=GenerateResponse, dependencies=[Depends(verify_service_token)])
async def generate(request: GenerateRequest):
    request_id = str(uuid.uuid4())
    started = time.perf_counter()
    messages = []
    if request.system_prompt:
        messages.append({"role": "system", "content": request.system_prompt})
    messages.append({"role": "user", "content": request.prompt})
    payload = {
        "model": settings.ollama_model,
        "messages": messages,
        "stream": False,
        "options": {"temperature": 0.2, "num_ctx": settings.default_context_size},
    }
    try:
        async with httpx.AsyncClient(timeout=settings.request_timeout) as client:
            result = await client.post(f"{settings.ollama_base_url.rstrip('/')}/api/chat", json=payload)
            result.raise_for_status()
            data = result.json()
    except httpx.TimeoutException:
        raise HTTPException(status_code=504, detail="AI processing timeout") from None
    except httpx.HTTPError:
        raise HTTPException(status_code=502, detail="AI runtime communication error") from None
    answer = data.get("message", {}).get("content", "")
    if not answer:
        raise HTTPException(status_code=502, detail="AI returned an empty response")
    elapsed = int((time.perf_counter() - started) * 1000)
    return GenerateResponse(request_id=request_id, status="completed", model=settings.ollama_model, response=answer, processing_time_ms=elapsed)
