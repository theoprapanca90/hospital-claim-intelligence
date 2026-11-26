# Laravel 12 Bootstrap Status

This branch introduces the first Laravel application entry points and an initial authenticated hospital-context route.

## Included
- Composer manifest targeting Laravel 12 and PHP 8.2.
- Laravel 12 bootstrap with API routing and middleware aliases.
- Eloquent models for Hospital, HospitalMembership, Role and Permission.
- `ResolveHospitalMembership`: requires an authenticated user and active membership for the hospital UUID in the route; inactive hospitals are denied.
- `EnsureHospitalPermission`: checks a permission slug and rejects roles whose declared scope does not match the current hospital.
- Authenticated routes: `GET /api/v1/me` and `GET /api/v1/hospitals/{hospital}/context`.

## Permission middleware usage
Register after `hospital.member`, because it reads the resolved membership from request attributes:

```php
Route::middleware(['hospital.member', 'hospital.permission:claims.read'])
    ->get('/claims', ClaimIndexController::class);
```

This is a coarse route gate only. Resource Policies and tenant-scoped database queries remain mandatory; it is not sufficient protection for patient or claim records.

## Required before runtime use
1. Generate the remaining Laravel 12 skeleton files using Composer / the official Laravel installer; this branch is not a complete Laravel distribution yet.
2. Generate and configure Sanctum, including personal access token support if API tokens are used.
3. Ensure the standard `users` migration and model exist.
4. Add resource Policies, request validation, audit-event service and tenant-scoped data access.
5. Run Composer dependency resolution, migrations and tests in PHP 8.2 + MySQL 8.4.

## Security notes
- Authentication does not imply hospital access. Every hospital route must pass membership middleware.
- Unknown or unauthorized hospital contexts return 404; missing permission returns 403.
- Role scope is checked during permission evaluation, but assignment-time validation and transactional role-management services are still required.
- Middleware is not a substitute for resource Policies and tenant-scoped repository queries.
- Do not return patient data from these routes.
- No real credentials, tokens or patient data belong in this repository.
- Do not deploy or connect to production SIMRS until the missing skeleton, Sanctum setup, Policies, tests and security review are complete.
