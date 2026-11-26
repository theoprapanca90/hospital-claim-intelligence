import hmac

from fastapi import Depends, Header, HTTPException, status

from app.config import Settings, load_settings


def require_service_token(
    x_service_token: str | None = Header(default=None),
    settings: Settings = Depends(load_settings),
) -> None:
    """Service-to-service authentication (Laravel -> AI service).

    Deny by default: if the server token is not configured the service answers
    503 instead of accepting unauthenticated calls.
    """
    if not settings.token_configured:
        raise HTTPException(status.HTTP_503_SERVICE_UNAVAILABLE, "service_not_configured")

    if not x_service_token or not hmac.compare_digest(
        x_service_token.encode(), settings.service_token.encode()
    ):
        raise HTTPException(status.HTTP_401_UNAUTHORIZED, "invalid_service_token")
