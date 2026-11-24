# Hospital Tenancy and Authorization Baseline

Status: design contract for the Laravel implementation; not yet runtime-enforced.

## Identity and tenant resolution
- Authentication establishes the user identity only. It does not grant access to any hospital.
- Every hospital-scoped request must resolve an active `hospital_memberships` row for the authenticated user and requested hospital.
- A revoked or suspended membership denies access even if a role assignment remains in the database.
- Never trust a client-supplied `hospital_id` without checking membership on the server.
- Do not use a single global "current hospital" value as the only authorization check; validate the tenant for every resource lookup.

## RBAC model
- `hospital_memberships`: link between a Laravel user and one hospital.
- `roles`: system-scoped roles (no hospital) or hospital-scoped roles.
- `permissions`: stable permission slugs such as `encounters.read`, `documents.read`, `diagnoses.review`, `claims.manage`, `audit.read`, and `members.manage`.
- `role_permissions`: permission catalogue assigned to a role.
- `membership_roles`: role assignments for one hospital membership.

A role assignment is valid only if:
1. the membership is active;
2. the role is system-scoped or belongs to the same hospital as the membership;
3. the permission is explicitly granted;
4. the action is permitted by the resource-level policy.

## Required Laravel enforcement
- Use middleware to resolve active membership for hospital routes.
- Use Policies/Gates for each resource and action; middleware alone is not sufficient.
- Scope Eloquent queries by the resolved hospital before loading a resource. Avoid fetching globally and checking tenant ownership only after serialization.
- Use Form Requests for input validation and reject tenant identifiers that conflict with the route context.
- Deny by default. Do not infer privileges from role names in controllers.
- Log membership creation, suspension, revocation, role assignment and permission changes in the audit service.

## Minimum authorization test matrix
- User A in Hospital A cannot list, read, update, download or export Hospital B resources.
- User with no membership receives 403 for hospital-scoped routes.
- Suspended/revoked membership receives 403 immediately.
- Hospital-scoped role from Hospital A cannot be assigned to membership in Hospital B.
- A role without a required permission cannot perform that action.
- Changing an identifier in a URL does not bypass tenant scoping.
- Document download endpoints re-check membership and document hospital ownership.
- Background jobs carry an explicit tenant context and cannot process cross-tenant records accidentally.

## Important database caveat
The migration creates the relational building blocks, but it does not by itself enforce that a role's `hospital_id` matches the hospital membership's `hospital_id`. Enforce this in a transaction-backed domain service and cover it with tests. Consider composite foreign keys or database triggers only after validating the chosen MySQL migration strategy.

## Operational rules
- Do not seed a universal superuser account or shared default password.
- Bootstrap the first hospital administrator through a controlled, audited setup command.
- Keep audit events append-only at the application and database-permission layers; never expose update/delete routes for audit records.
- Test against MySQL 8.4, not SQLite alone, because foreign-key and index behavior can differ.
- Do not load production patient data until tenant-isolation tests and access-control review pass.
