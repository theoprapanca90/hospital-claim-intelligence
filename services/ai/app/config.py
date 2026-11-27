from pydantic import Field
from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    app_name: str = "HCI AI Gateway"
    app_version: str = "0.1.0"
    service_token: str = Field(default="", validation_alias="HCI_SERVICE_TOKEN")
    ollama_base_url: str = Field(default="http://host.docker.internal:11434", validation_alias="OLLAMA_BASE_URL")
    ollama_model: str = Field(default="qwen2.5:1.5b", validation_alias="OLLAMA_MODEL")
    request_timeout: int = Field(default=120, validation_alias="AI_REQUEST_TIMEOUT")
    default_context_size: int = Field(default=2048, validation_alias="AI_CONTEXT_SIZE")
    model_config = SettingsConfigDict(extra="ignore")


settings = Settings()
