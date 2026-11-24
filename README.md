# Hospital Claim Intelligence (HCI) — Foundation

Status: **foundation scaffold only**. This repository pack is not a deployed or tested HCI system.

## Architectural baseline
- Laravel 12 / PHP 8.2: application workflow, RBAC, API, audit and human review.
- MySQL: operational relational data.
- Python FastAPI: isolated AI/processing service.
- Ollama: optional local model runtime.
- Qdrant: retrieval/vector index for approved knowledge.
- SIMRS adapters: Khanza and GHOST; start read-only and validate each vendor/API contract.
- On-premise first: identifiable clinical data stays local by default.
- Offline-first: core workflows must remain usable without cloud connectivity.
- Human-in-the-loop: AI suggestions are not final diagnoses, codes, or claim decisions.

## Important boundaries
1. Do not put patient identifiers, clinical notes, API tokens, or credentials in Git.
2. Do not send identifiable clinical data to cloud services by default.
3. Do not implement BPJS rules, tariffs, or official grouper integration from assumptions.
4. Preserve source provenance, terminology version, rule effective dates, reviewer identity, and audit history.
5. AI failure or internet loss must not block manual clinical abstraction and claim review.

## Start here
1. Read `docs/architecture/overview.md`.
2. Review `docs/decisions/ADR-0001-hybrid-data-boundary.md`.
3. Complete `docs/integration/simrs-api-discovery-template.md` using approved, de-identified examples.
4. Agree on database migrations and API contracts before implementing adapters.
5. Generate Laravel 12 in `apps/web` using Composer when the development environment is ready.
6. Run the local infrastructure with `docker compose up -d` only after reviewing resource requirements.

## Current implementation status
- Documentation scaffold: included in this pack.
- Laravel 12 runtime skeleton files: partially added (artisan, public entry point, configuration, routes, users/cache/jobs migrations and test scaffold); Composer/runtime verification pending.
- MySQL schema migrations: HCI tables plus standard users/cache/jobs migrations are present; not yet executed or verified.
- FastAPI service: minimal scaffold included; not integration-tested.
- Ollama model: not selected or evaluated.
- Qdrant collections/RAG pipeline: not implemented.
- SIMRS API adapters: not implemented.
- BPJS rules/grouper: not implemented or verified.
- Runtime verification, migration execution, security, backup, offline and pilot tests: not performed.
