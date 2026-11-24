# SIMRS API Discovery Template

Complete separately for Khanza and GHOST. Do not paste secrets or identifiable patient data.

| Item | Khanza | GHOST |
|---|---|---|
| API documentation/version | Unknown | Unknown |
| Base URL/environment | Unknown | Unknown |
| Authentication method | Unknown | Unknown |
| Patient lookup endpoint | Unknown | Unknown |
| Encounter/visit endpoint | Unknown | Unknown |
| Diagnosis endpoint | Unknown | Unknown |
| Procedure endpoint | Unknown | Unknown |
| Clinical document endpoint | Unknown | Unknown |
| Pagination/rate limits | Unknown | Unknown |
| Updated-at / change cursor | Unknown | Unknown |
| Webhook/event support | Unknown | Unknown |
| Sandbox/test account | Unknown | Unknown |
| Read-only access confirmed | Unknown | Unknown |

## Required sample artifacts
- OpenAPI/Swagger file or vendor API manual.
- De-identified request/response examples.
- Error response examples.
- Authentication flow description with credentials removed.
- API version and compatibility policy.

## Acceptance checks
- Read-only calls work in a non-production environment.
- Patient/encounter linkage is deterministic and auditable.
- Pagination and incremental updates are understood.
- Duplicate delivery is safe (idempotent).
- Timeout, retry and source outage behavior are tested.
- No write-back until a separate authorization and safety review is approved.
