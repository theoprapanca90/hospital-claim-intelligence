import logging
import re
import time
import uuid
from collections.abc import Awaitable, Callable

import httpx
from fastapi import Depends, FastAPI, Request
from fastapi.exceptions import RequestValidationError
from fastapi.responses import JSONResponse

from app.config import Settings, load_settings
from app.schemas import SuggestRequest, SuggestResponse
from app.security import require_service_token

logger = logging.getLogger("hci.ai")

REQUEST_ID_PATTERN = re.compile(r"^[A-Za-z0-9._-]{8,64}$")

app = FastAPI(title="HCI AI Service", version="0.2.0")

Prober = Callable[[str | None, str, float], Awaitable[str]]


@app.middleware("http")
async def request_context(request: Request, call_next):
    incoming = request.headers.get("x-request-id", "")
    request_id = incoming if REQUEST_ID_PATTERN.match(incoming) else uuid.uuid4().hex
    started = time.perf_counter()

    response = await call_next(request)

    response.headers["X-Request-Id"] = request_id
    # Log metadata only. Never log bodies, query strings or headers.
    logger.info(
        "request_id=%s method=%s path=%s status=%s duration_ms=%d",
        request_id,
        request.method,
        request.url.path,
        response.status_code,
        (time.perf_counter() - started) * 1000,
    )
    return response


@app.exception_handler(RequestValidationError)
async def validation_error_handler(_: Request, exc: RequestValidationError) -> JSONResponse:
    # The default handler echoes the rejected input, which may be clinical text.
    errors = [{"loc": list(e["loc"]), "type": e["type"]} for e in exc.errors()]
    return JSONResponse(status_code=422, content={"detail": errors})


async def probe_http(base_url: str | None, path: str, timeout: float) -> str:
    if not base_url:
        return "not_configured"
    try:
        async with httpx.AsyncClient(timeout=timeout) as client:
            response = await client.get(base_url.rstrip("/") + path)
    except httpx.HTTPError:
        return "unavailable"
    return "ok" if response.status_code < 500 else "unavailable"


def get_prober() -> Prober:
    return probe_http


@app.get("/health")
def health() -> dict[str, str]:
    """Liveness only. Does not touch dependencies."""
    return {"status": "ok", "service": "hci-ai"}


@app.get("/ready")
async def ready(
    settings: Settings = Depends(load_settings),
    probe: Prober = Depends(get_prober),
) -> JSONResponse:
    """The service is ready when it can authenticate callers.

    Qdrant and Ollama are optional: HCI must keep working manually when AI
    components are down, so they only mark the service as degraded.
    """
    dependencies = {
        "qdrant": await probe(settings.qdrant_url, "/healthz", settings.probe_timeout),
        "ollama": await probe(settings.ollama_base_url, "/api/tags", settings.probe_timeout),
    }
    degraded = sorted(name for name, state in dependencies.items() if state != "ok")
    body = {
        "ready": settings.token_configured,
        "degraded": degraded,
        "dependencies": dependencies,
    }
    return JSONResponse(status_code=200 if settings.token_configured else 503, content=body)


@app.post(
    "/v1/abstraction/suggest",
    response_model=SuggestResponse,
    dependencies=[Depends(require_service_token)],
)
def suggest(payload: SuggestRequest) -> SuggestResponse:
    """Contract placeholder. No model is selected or evaluated yet.

    Returns "unavailable" so the caller continues the manual workflow.
    `payload.document_text` is intentionally never logged or persisted here.
    """
    return SuggestResponse(status="unavailable", reason="model_not_configured")
