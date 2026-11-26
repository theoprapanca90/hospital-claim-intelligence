import os
from dataclasses import dataclass

MIN_TOKEN_LENGTH = 32


@dataclass(frozen=True)
class Settings:
    service_token: str | None
    qdrant_url: str | None
    ollama_base_url: str | None
    probe_timeout: float

    @property
    def token_configured(self) -> bool:
        """Fail closed: a missing or short token means the service refuses work."""
        return self.service_token is not None and len(self.service_token) >= MIN_TOKEN_LENGTH


def load_settings() -> Settings:
    """Read settings from the environment on every call (cheap, test friendly)."""
    return Settings(
        service_token=os.environ.get("AI_SERVICE_TOKEN") or None,
        qdrant_url=os.environ.get("QDRANT_URL") or None,
        ollama_base_url=os.environ.get("OLLAMA_BASE_URL") or None,
        probe_timeout=float(os.environ.get("AI_PROBE_TIMEOUT_SECONDS", "2")),
    )
