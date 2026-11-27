from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    app_name: str = "HCI AI Gateway"
    app_version: str = "0.1.0"
    service_token: str = ""
    ollama_base_url: str = "http://host.docker.internal:11434"
    ollama_model: str = "qwen2.5:1.5b"
    request_timeout: int = 120
    default_context_size: int = 2048

    model_config = SettingsConfigDict(extra="ignore")


settings = Settings()
