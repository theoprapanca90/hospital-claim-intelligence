# ADR-0001: Hybrid deployment and clinical data boundary

Status: Proposed baseline for implementation review.

## Decision
Keep identifiable clinical data and core workflows on-premise by default. Cloud connectivity is optional and limited to explicitly approved functions. Core workflows must continue during internet outages.

## Consequences
- Local identity, authorization, operational database, AI inference and audit are required for the core workflow.
- Cloud synchronization uses an outbound queue, encryption, idempotency keys, retry policy and conflict handling.
- No cloud LLM processing of identifiable patient content unless a separately approved legal, security and governance assessment authorizes it.
- Backups must be encrypted, access-controlled, restore-tested and governed by hospital retention policy.
- Human review remains mandatory for diagnosis, coding and claim decisions.

## Open questions
- Hospital-approved hosting and network zones.
- Data controller/processor roles, retention and lawful basis.
- Key management and disaster recovery objectives (RPO/RTO).
- Whether any cloud metadata synchronization is permitted.
