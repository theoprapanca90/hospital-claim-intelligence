# Foundation Exit Criteria

The foundation phase is complete only when evidence exists for each item:

1. Repository exists with protected default branch and documented ownership.
2. Laravel 12 / PHP 8.2 application boots locally with pinned dependencies.
3. MySQL migrations run from an empty database and rollback behavior is documented.
4. FastAPI service has health/readiness endpoints and automated tests.
5. Local infrastructure can start and stop reproducibly.
6. No secrets or identifiable patient data are committed.
7. Offline test demonstrates core manual workflow remains usable without internet.
8. Audit events capture actor, action, timestamp and target reference.
9. Khanza and GHOST API discovery is based on vendor documentation and de-identified examples.
10. Human review gates are present in the workflow design.
11. Backup and restore procedure has a successful recorded test.
12. Open risks and unresolved regulatory/vendor dependencies are documented.
