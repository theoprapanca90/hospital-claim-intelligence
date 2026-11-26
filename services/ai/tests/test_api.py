import logging
import uuid

import pytest
from fastapi.testclient import TestClient

from app.main import app, get_prober

TOKEN = "t" * 40
MARKER = "SYNTHETIC-CLINICAL-MARKER-12345"


def payload(**overrides):
    body = {
        "hospital_id": str(uuid.uuid4()),
        "encounter_id": str(uuid.uuid4()),
        "document_text": MARKER,
    }
    body.update(overrides)
    return body


@pytest.fixture(autouse=True)
def clean_env(monkeypatch):
    for name in ("AI_SERVICE_TOKEN", "QDRANT_URL", "OLLAMA_BASE_URL"):
        monkeypatch.delenv(name, raising=False)
    yield
    app.dependency_overrides.clear()


@pytest.fixture
def configured(monkeypatch):
    monkeypatch.setenv("AI_SERVICE_TOKEN", TOKEN)


@pytest.fixture
def client():
    return TestClient(app)


def auth(token=TOKEN):
    return {"X-Service-Token": token}


def test_health_is_public_and_does_not_probe_dependencies(client):
    response = client.get("/health")
    assert response.status_code == 200
    assert response.json() == {"status": "ok", "service": "hci-ai"}


def test_ready_is_503_when_token_not_configured(client):
    response = client.get("/ready")
    assert response.status_code == 503
    assert response.json()["ready"] is False


def test_ready_is_503_when_token_is_too_short(client, monkeypatch):
    monkeypatch.setenv("AI_SERVICE_TOKEN", "short")
    assert client.get("/ready").status_code == 503


def test_ready_reports_optional_dependencies_as_degraded_not_failed(client, configured, monkeypatch):
    monkeypatch.setenv("QDRANT_URL", "http://qdrant.test")
    monkeypatch.setenv("OLLAMA_BASE_URL", "http://ollama.test")

    async def fake_probe(base_url, path, timeout):
        return "ok" if "qdrant" in base_url else "unavailable"

    app.dependency_overrides[get_prober] = lambda: fake_probe

    response = client.get("/ready")
    assert response.status_code == 200
    body = response.json()
    assert body["ready"] is True
    assert body["degraded"] == ["ollama"]
    assert body["dependencies"] == {"qdrant": "ok", "ollama": "unavailable"}


def test_ready_marks_unconfigured_dependencies(client, configured):
    body = client.get("/ready").json()
    assert body["dependencies"] == {"qdrant": "not_configured", "ollama": "not_configured"}
    assert body["degraded"] == ["ollama", "qdrant"]


def test_suggest_fails_closed_when_service_token_not_configured(client):
    response = client.post("/v1/abstraction/suggest", json=payload(), headers=auth())
    assert response.status_code == 503


def test_suggest_rejects_missing_and_wrong_token(client, configured):
    assert client.post("/v1/abstraction/suggest", json=payload()).status_code == 401
    wrong = client.post("/v1/abstraction/suggest", json=payload(), headers=auth("x" * 40))
    assert wrong.status_code == 401


def test_suggest_returns_unavailable_recommendation_requiring_human_review(client, configured):
    response = client.post("/v1/abstraction/suggest", json=payload(), headers=auth())
    assert response.status_code == 200
    assert response.json() == {
        "status": "unavailable",
        "requires_human_review": True,
        "suggestions": [],
        "reason": "model_not_configured",
    }


def test_validation_error_does_not_echo_rejected_input(client, configured):
    # Field yang ditolak itu sendiri berisi teks sensitif; handler bawaan
    # FastAPI akan mengulangnya di `input`.
    response = client.post(
        "/v1/abstraction/suggest",
        json=payload(patient_note=MARKER),
        headers=auth(),
    )
    assert response.status_code == 422
    assert MARKER not in response.text
    assert response.json()["detail"] == [
        {"loc": ["body", "patient_note"], "type": "extra_forbidden"}
    ]


def test_oversized_text_error_does_not_echo_the_text(client, configured):
    response = client.post(
        "/v1/abstraction/suggest",
        json=payload(document_text=MARKER + "a" * 20_001),
        headers=auth(),
    )
    assert response.status_code == 422
    assert MARKER not in response.text


def test_unknown_fields_are_rejected(client, configured):
    response = client.post(
        "/v1/abstraction/suggest", json=payload(patient_name="x"), headers=auth()
    )
    assert response.status_code == 422


@pytest.mark.parametrize("text", ["", "a" * 20_001])
def test_document_text_length_is_bounded(client, configured, text):
    response = client.post(
        "/v1/abstraction/suggest", json=payload(document_text=text), headers=auth()
    )
    assert response.status_code == 422


def test_valid_request_id_is_echoed_and_invalid_is_replaced(client):
    ok = client.get("/health", headers={"X-Request-Id": "req-12345678"})
    assert ok.headers["X-Request-Id"] == "req-12345678"

    bad = client.get("/health", headers={"X-Request-Id": "bad id\twith spaces"})
    assert bad.headers["X-Request-Id"] != "bad id\twith spaces"
    assert len(bad.headers["X-Request-Id"]) == 32


def test_logs_never_contain_request_body_or_token(client, configured, caplog):
    with caplog.at_level(logging.INFO, logger="hci.ai"):
        client.post("/v1/abstraction/suggest", json=payload(), headers=auth())

    assert "/v1/abstraction/suggest" in caplog.text
    assert MARKER not in caplog.text
    assert TOKEN not in caplog.text
