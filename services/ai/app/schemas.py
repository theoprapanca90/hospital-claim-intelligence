from pydantic import BaseModel, Field


class GenerateRequest(BaseModel):
    prompt: str = Field(min_length=1, max_length=8000)
    system_prompt: str | None = Field(default=None, max_length=2000)


class GenerateResponse(BaseModel):
    request_id: str
    status: str
    model: str
    response: str
    processing_time_ms: int
