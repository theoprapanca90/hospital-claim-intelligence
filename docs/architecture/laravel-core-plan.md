# Laravel Core Implementation Plan

## Target stack
Laravel 12, PHP 8.2, MySQL 8.4. Use Composer with a pinned lockfile when creating the Laravel application. The repository currently contains migrations and design artifacts, not a complete Laravel installation.

## Modules
1. Authentication and session management.
2. Hospital tenancy and user membership.
3. Role-based authorization: system administrator, hospital administrator, medical-record officer, coder, verifier, auditor and read-only reviewer.
4. Patient and encounter worklists.
5. Protected clinical-document intake.
6. Clinical abstraction and diagnosis/procedure review.
7. Claim workbench and status lifecycle.
8. Append-only audit event service.
9. SIMRS integration gateway (read-only first).

## API conventions
- Prefix: `/api/v1`.
- JSON request/response; UUIDs for internal resources.
- Require authenticated identity and hospital membership on every hospital-scoped route.
- Use Laravel Policies/Form Requests; validate hospital scope server-side.
- Return request/correlation ID; avoid sensitive payloads in logs.
- Paginate list endpoints; apply rate limits and idempotency keys to ingestion.
- Never expose storage keys or direct database identifiers to unauthorized clients.

## Initial route inventory (design only)
- `GET /api/v1/me`
- `GET /api/v1/hospitals/{hospital}/encounters`
- `GET /api/v1/encounters/{encounter}`
- `GET /api/v1/encounters/{encounter}/documents`
- `GET /api/v1/encounters/{encounter}/diagnoses`
- `POST /api/v1/encounters/{encounter}/diagnoses/{diagnosis}/review`
- `GET /api/v1/claims`
- `POST /api/v1/claims/{claim}/submit-for-review`

Do not enable external SIMRS write-back or automatic claim submission in the initial release.
