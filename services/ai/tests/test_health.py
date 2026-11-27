from fastapi.testclient import TestClient

from app.main import app

client = TestClient(app)


def test_health_endpoint():
    response = client.get("/health")
    assert response.status_code == 200
    assert response.json()["status"] == "ok"


def test_generate_rejects_missing_internal_token():
    response = client.post(
        "/v1/generate",
        json={"prompt": "health check only"},
    )
    assert response.status_code == 401
