# HCI Architecture Overview

## Logical components
- Web application (Laravel 12 / PHP 8.2): authentication, role-based access, case worklists, clinical abstraction review, coding review, claim validation workflow, audit and reporting.
- Relational store (MySQL): normalized operational records, source references, review states, rule versions and audit events.
- AI service (FastAPI): document parsing orchestration, structured extraction, terminology retrieval and recommendation endpoints. It must not own final decisions.
- Local model runtime (Ollama): inference inside the hospital network where approved.
- Vector retrieval (Qdrant): embeddings and references for approved, versioned knowledge. Store only the minimum necessary text; apply access controls and retention rules.
- Integration Gateway: separate adapters for Khanza and GHOST, initially read-only; normalize to a canonical internal model.
- Cloud services: optional update distribution and approved metadata/telemetry. No identifiable clinical data by default.

## Offline behavior
Core patient/case review, manual abstraction, coding review, claim workflow and local audit must remain available without internet. Cloud sync is queued, retryable, idempotent and observable. Conflicts must be surfaced for human resolution; never silently overwrite clinical facts.

## Data flow
1. Source data arrives through a documented SIMRS API or controlled Excel/PDF import.
2. Validate provenance, access rights, file type, malware scanning and schema.
3. Normalize into canonical encounter/document structures while retaining source identifiers and timestamps.
4. AI may propose extracted facts and terminology candidates with evidence spans and confidence.
5. Human reviewer accepts, edits or rejects each material suggestion.
6. Coding suggestions are evaluated against approved, versioned references and rules.
7. Final diagnosis, code and claim decisions remain with authorized human roles.
8. Audit events capture actor, action, timestamp, source, version and before/after references.

## Trust boundaries
Hospital LAN/on-premise is the default trust boundary for identifiable clinical data. Cloud is a separate trust zone. Use TLS, least privilege, managed secrets, encryption at rest, network segmentation, explicit egress allowlists and documented retention. Do not expose MySQL, Qdrant or Ollama directly to the public internet.
