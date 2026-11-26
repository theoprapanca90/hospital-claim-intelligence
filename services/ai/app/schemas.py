from typing import Literal
from uuid import UUID

from pydantic import BaseModel, ConfigDict, Field

MAX_DOCUMENT_CHARS = 20_000


class SuggestRequest(BaseModel):
    model_config = ConfigDict(extra="forbid")

    hospital_id: UUID
    encounter_id: UUID
    document_text: str = Field(min_length=1, max_length=MAX_DOCUMENT_CHARS)


class Suggestion(BaseModel):
    model_config = ConfigDict(extra="forbid")

    code_system: str
    code: str
    display: str
    evidence: list[str] = []


class SuggestResponse(BaseModel):
    """AI output is only ever a recommendation; a human decides.

    `status == "unavailable"` means the caller must continue the manual
    workflow. It is a normal result, not an error.
    """

    status: Literal["unavailable", "recommendation"]
    requires_human_review: Literal[True] = True
    suggestions: list[Suggestion] = []
    reason: str | None = None
