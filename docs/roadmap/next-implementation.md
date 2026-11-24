# Next Implementation Milestones

## Milestone 1 — Core foundation (current)
- [x] Define first-pass relational entities and migration drafts.
- [x] Define tenancy, provenance and human-review principles.
- [x] Draft hospital membership and RBAC tables.
- [x] Define tenant authorization and isolation test matrix.
- [ ] Generate Laravel 12 application and lock dependencies.
- [ ] Implement authentication, membership middleware, policies and audit service.
- [ ] Run MySQL 8.4 migration and authorization tests.

## Milestone 2 — Clinical workflow
- [ ] Patient/encounter worklists.
- [ ] Secure document intake and storage.
- [ ] Diagnosis/procedure review with evidence provenance.
- [ ] Audit event service and access audit.

## Milestone 3 — Integration
- [ ] Validate Khanza and GHOST API contracts using vendor docs.
- [ ] Implement read-only adapters and sync cursor.
- [ ] Add idempotent ingestion and reconciliation reports.

## Milestone 4 — AI and coding
- [ ] Separate AI recommendation records from accepted clinical facts.
- [ ] Add terminology catalogue and versioned references.
- [ ] Add RAG with source citations and access filtering.
- [ ] Add coding/claim rules only from verified official and hospital-approved sources.
- [ ] Human verification and full audit before any export.
