# Hospital Claim Intelligence (HCI)

Status: **foundation + AI Gateway implementation branch**. This repository is not a deployed or clinically validated system.

## Architectural baseline
- Laravel 12 / PHP 8.2: application workflow, RBAC, API, audit and human review.
- MySQL 8.4: operational relational data.
- Python FastAPI: isolated AI Gateway and later clinical AI/RAG processing.
- Ollama: local model runtime; on Apple Silicon development machines, native Ollama is preferred for Metal acceleration.
- Qdrant: optional vector retrieval service, enabled with the Docker Compose `rag` profile.
- SIMRS adapters: Khanza and GHOST; read-only first and only after validating vendor/API contracts.
- On-premise first and offline-first: identifiable clinical data stays local by default.
- Human-in-the-loop: AI suggestions are not final diagnoses, codes or claim decisions.

## Repository status
- Foundation documentation and Docker Compose baseline: present on `main`.
- Laravel database/RBAC draft: Draft PR #1, not merged or runtime-tested.
- FastAPI AI Gateway: implemented on branch `feature/ai-gateway-v0.1`; requires local build and tests.
- Laravel application: not yet generated under `apps/web`.
- Qdrant RAG pipeline, clinical abstraction, ICD recommendation, BPJS rules and SIMRS adapters: not implemented.
- Security assessment, backup/restore, offline and hospital pilot tests: not performed.

## Start AI Gateway development on Apple Silicon Mac

1. Copy `.env.example` to `.env` and replace all placeholder passwords and the service token.
2. Generate a shared internal token with `openssl rand -hex 32`; place the same value in root `.env` as `HCI_SERVICE_TOKEN` and later in Laravel as `HCI_AI_SERVICE_TOKEN`.
3. Install and start Ollama natively on macOS, then pull the small development model: `ollama pull qwen2.5:1.5b`.
4. Start Docker Desktop, then run `docker compose up -d --build mysql ai`.
5. Check `http://127.0.0.1:8001/health` and `http://127.0.0.1:8001/ready`.
6. Run the FastAPI tests from `services/ai`: `python -m pip install -r requirements.txt && pytest -q`.
7. Use only synthetic, non-identifiable test content.

If `/ready` cannot reach Ollama from the AI container, check Ollama's network binding. Ollama normally listens on loopback; its official macOS guidance for exposing the server to other local processes is to set `OLLAMA_HOST` with `launchctl` and restart the app. Only do this on a trusted development machine, and restrict port 11434 with the macOS firewall; Ollama's local API does not provide authentication by default. See the [official Ollama FAQ](https://github.com/ollama/ollama/blob/main/docs/faq.mdx).

Qdrant is intentionally optional on an 8 GB Mac: start it later with `docker compose --profile rag up -d qdrant`. The optional Docker Ollama profile is intended for environments where native Ollama is not used; native Ollama is preferred on M1 for Metal acceleration.

## Important boundaries
1. Never commit patient identifiers, clinical notes, API tokens or credentials.
2. Do not send identifiable clinical data to cloud services by default.
3. Do not invent BPJS rules, tariffs or official grouper behavior.
4. Preserve source provenance, terminology version, rule effective dates, reviewer identity and audit history.
5. AI failure or internet loss must not block manual clinical abstraction and claim review.
6. Do not expose MySQL, Qdrant, Ollama or the internal AI Gateway to the public internet.
7. Human verification remains mandatory for clinical coding and claim decisions.

## Integration documentation
- Architecture: `docs/architecture/overview.md`
- Hybrid data boundary: `docs/decisions/ADR-0001-hybrid-data-boundary.md`
- Laravel-to-AI Gateway client: `docs/integration/laravel-ai-gateway.md`
- FastAPI service: `services/ai/README.md`

Do not connect production SIMRS data until authentication, hospital tenancy, authorization, audit, retention, encryption and security tests have passed.
