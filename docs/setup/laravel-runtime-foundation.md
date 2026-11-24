# Laravel Runtime Foundation

Status: implementation scaffold; runtime installation and verification are still pending.

## Why this step exists
The repository contains HCI-specific Laravel code, but it is not yet a complete Laravel 12 application distribution. Do not run Composer scripts in the repository until the standard Laravel skeleton has been generated and reviewed.

## Required local environment
- PHP 8.2+
- Composer 2
- MySQL 8.4
- PHP extensions required by Laravel 12 and the selected database driver
- Node.js only when frontend assets are introduced

## Safe skeleton generation procedure
1. Back up the repository branch.
2. Generate a clean Laravel 12 project in a separate temporary directory using Composer.
3. Review the generated `composer.json` and merge HCI dependencies and PSR-4 paths; do not blindly overwrite the HCI manifest.
4. Copy the standard Laravel runtime files into the repository: `artisan`, `public/index.php`, `bootstrap/providers.php`, standard `config/*`, framework routes, storage and tests scaffolding.
5. Preserve HCI-specific files under `app/Models`, `app/Http/Middleware`, `routes/api.php`, and `database/migrations`; resolve conflicts manually.
6. Install dependencies and publish/configure Sanctum using the Laravel 12-supported installation flow. Do not create a second copy of the personal access token migration if the installer already generated one.
7. Configure MySQL using the local `.env`; never commit `.env` or real credentials.
8. Run migrations against a disposable local database first.

## Sanctum implementation included in this branch
- `App\\Models\\User` uses `Laravel\\Sanctum\\HasApiTokens`.
- Migration creates `personal_access_tokens`.
- API routes remain protected by `auth:sanctum`.

The migration is intended for a setup where Sanctum's installer has not already created the same table. Check migration filenames before running migrations to prevent duplicate-table errors.

## Token policy
- Issue tokens only after the user has authenticated and passed the application's account and hospital access checks.
- Assign minimum abilities; do not issue broad wildcard abilities by default.
- Set explicit token expiration and revoke tokens when a user leaves an organization or a credential is suspected compromised.
- Never store plaintext tokens in logs, database exports, screenshots or Git.
- Sanctum token abilities are an additional restriction, not a replacement for hospital membership and resource authorization.

## Verification checklist
- [ ] Clean Laravel 12 skeleton is present.
- [ ] `composer install` completes successfully.
- [ ] Sanctum service provider/package discovery succeeds.
- [ ] Only one `personal_access_tokens` migration exists.
- [ ] MySQL 8.4 connection succeeds.
- [ ] All migrations run and roll back on a disposable database.
- [ ] Unauthenticated request to `/api/v1/me` returns 401.
- [ ] Authenticated request to `/api/v1/me` returns only the signed-in user's safe profile fields.
- [ ] No patient/claim endpoint is exposed before tenant-isolation tests exist.

No runtime or automated test result is claimed by this document.
