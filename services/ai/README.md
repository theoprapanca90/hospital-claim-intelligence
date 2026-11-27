# HCI AI Gateway

FastAPI gateway for internal Laravel-to-Ollama requests.

## Endpoints
- `GET /health`: liveness check; does not expose configuration or model details.
- `GET /ready`: verifies Ollama connectivity and configured model availability.
- `POST /v1/generate`: authenticated internal inference endpoint using `X-HCI-Service-Token`.

## Local development
Install dependencies, export `HCI_SERVICE_TOKEN`, `OLLAMA_BASE_URL`, and `OLLAMA_MODEL`, then run:

```bash
cd services/ai
python -m pip install -r requirements.txt
uvicorn app.main:app --host 127.0.0.1 --port 8001
```

Run tests with `pytest -q` from this directory. Do not use identifiable patient data in development. The Gateway intentionally does not log prompts or model responses. It is an internal service and must not be exposed directly to the public internet.
