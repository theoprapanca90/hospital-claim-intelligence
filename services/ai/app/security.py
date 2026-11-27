import secrets

from fastapi import Header, HTTPException, status

from app.config import settings


async def verify_service_token(
    x_hci_service_token: str = Header(default=""),
) -> bool:
    expected = settings.service_token
    if not expected or not secrets.compare_digest(x_hci_service_token, expected):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Unauthorized AI service request",
        )
    return True
