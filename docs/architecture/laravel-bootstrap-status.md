# Laravel 12 Bootstrap Status

This branch introduces the first Laravel application entry points and an initial authenticated hospital-context route.

## Included in this branch
- Composer project manifest targeting Laravel 12 and PHP 8.2.
- Laravel 12 application bootstrap with API routing and middleware alias.
- HospitalMembership Eloquent model.
- ResolveHospitalMembership middleware: requires an authenticated user and an active membership for the hospital UUID in the route.
- Authenticated API routes:
  - `GET /api/v1/me`
  - `GET /api/v1/hospitals/{hospital}/context`

## Required before this can run
1. Generate the remaining Laravel 12 skeleton files using Composer / the official Laravel installer; this branch is not a complete Laravel distribution yet.
2. Generate and configure Sanctum according to Laravel 12 installation guidance, including personal access token support if API tokens are used.
3. Add the missing `Hospital` and `Role` models and the `Role::permissions()` relationship.
4. Ensure the standard `users` migration and model exist.
5. Run Composer dependency resolution, migrations and tests in a PHP 8.2 + MySQL 8.4 environment.

## Security notes
- Authentication does not imply hospital access. Every hospital route must pass the membership middleware.
- Unknown or unauthorized hospital contexts return 404 to reduce tenant enumeration.
- This middleware is an initial gate, not a substitute for resource Policies and tenant-scoped repository queries.
- Do not return patient data from these routes.
- No real credentials, tokens or patient data belong in this repository.
- Do not deploy this scaffold or connect it to production SIMRS until the missing models, Sanctum setup, Policies, tests and security review are complete.
