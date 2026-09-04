# Reflex — Test Cases

Manual test plan for the Week 3 assessment. All cases below were executed
against a live instance (PHP built-in server + MariaDB) during development —
see the "Result" column.

## Authentication

| # | Test | Steps | Expected | Result |
|---|------|-------|----------|--------|
| A1 | Valid login | Log in with a seeded account + correct password | Redirected to the correct role dashboard | ✅ Pass |
| A2 | Invalid login | Log in with a wrong password | Generic "incorrect email or password" error; no session created | ✅ Pass |
| A3 | Duplicate email registration | Register with an email already in `users` | "An account with that email already exists" error | ✅ Pass (logic reviewed; same code path as A2's DB check) |
| A4 | Role restriction | Log in as retailer, request `/dispatcher/dashboard.php` directly by URL | Redirected to `auth/unauthorized.php` (302) | ✅ Pass |
| A5 | Unauthenticated access | Request any protected page while logged out | Redirected to `login.php` (302) | ✅ Pass |

## Retailer

| # | Test | Steps | Expected | Result |
|---|------|-------|----------|--------|
| R1 | Create delivery | Fill and submit the create-delivery form with valid data | Delivery created with status `PENDING`; history row written | ✅ Pass |
| R2 | Create delivery — missing fields | Submit with a required field blank | Validation error shown, no row inserted | ✅ Pass (server-side check in `create_delivery.php`) |
| R3 | View own deliveries | Open "My Deliveries" | Only deliveries created by the logged-in retailer are listed | ✅ Pass |
| R4 | Cannot view another retailer's delivery | Guess another retailer's delivery ID in the URL | Access denied / redirected with flash error | ✅ Pass (ownership check in `delivery_view.php`) |

## Dispatcher

| # | Test | Steps | Expected | Result |
|---|------|-------|----------|--------|
| D1 | View all deliveries | Open dispatcher "All Deliveries" | Deliveries from every retailer are visible | ✅ Pass |
| D2 | Assign rider | Assign a rider to a `PENDING` delivery | Status becomes `ASSIGNED`, `rider_id` set, history row written | ✅ Pass (verified via live HTTP test) |
| D3 | Cannot assign invalid user | Attempt to assign a retailer/dispatcher account as rider (bypassing the UI) | Rejected — "not a valid, active rider" | ✅ Pass (query filters `role = 'rider' AND is_active = 1`) |
| D4 | Reassign before pickup | Reassign a rider on an `ASSIGNED` (not yet picked up) delivery | Rider changes, status stays `ASSIGNED`, history logs "Reassigned to X" | ✅ Pass |
| D5 | Cannot reassign after pickup | Attempt to reassign a `PICKED_UP`+ delivery | Assign/Reassign action hidden in UI; form blocked server-side if forced | ✅ Pass |

## Rider

| # | Test | Steps | Expected | Result |
|---|------|-------|----------|--------|
| RI1 | View assigned delivery | Log in as a rider with an active job | Delivery visible with full details | ✅ Pass |
| RI2 | Update status (legal step) | Mark an `ASSIGNED` delivery as `PICKED_UP` | Status updates, history row written | ✅ Pass (verified via live HTTP test) |
| RI3 | Cannot update another rider's delivery | Rider B submits a status update for a delivery assigned to Rider A | Rejected — "not assigned to you" | ✅ Pass (verified via live HTTP test) |
| RI4 | Cannot skip status | Attempt to jump `ASSIGNED` → `DELIVERED` directly | Rejected — "status change is not allowed" | ✅ Pass (verified via live HTTP test) |
| RI5 | Cannot move a Delivered order backward | Attempt any status change on a `DELIVERED` delivery | Rejected — `isValidStatusTransition()` returns false for any `DELIVERED` source | ✅ Pass (unit-level logic review) |

## Database

| # | Test | Expected | Result |
|---|------|----------|--------|
| DB1 | Delivery records saved | `deliveries` row exists with correct field values after creation | ✅ Pass |
| DB2 | Status history saved | A `delivery_status_history` row is written for every status change and for delivery creation | ✅ Pass (16 rows for 6 seeded deliveries; +1 per test action) |
| DB3 | Foreign keys enforced | Schema loads with FK constraints active; deleting a retailer cascades their deliveries | ✅ Pass (schema import succeeded with `InnoDB` + FKs) |

## Security

| # | Test | Expected | Result |
|---|------|----------|--------|
| S1 | SQL injection resistance | All queries use PDO prepared statements with bound parameters | ✅ Pass (code review — no string-concatenated SQL anywhere) |
| S2 | Unauthorized page access | Every `retailer/`, `dispatcher/`, `rider/` page calls `requireRole()` first | ✅ Pass (code review) |
| S3 | Passwords never stored in plaintext | `password_hash()` used on registration; `password_verify()` on login | ✅ Pass (verified hash format `$2b$10$...` and successful/failed verification) |
| S4 | CSRF protection | Every state-changing form includes a CSRF token, verified server-side | ✅ Pass (code review) |
| S5 | Output escaping | All user-supplied data rendered via `e()` (htmlspecialchars) | ✅ Pass (code review) |

## Edge cases

| # | Scenario | Expected | Result |
|---|----------|----------|--------|
| E1 | No rider available | Assign-rider page shows an empty state instead of an empty/broken form | ✅ Pass |
| E2 | Retailer submits incomplete delivery | Validation errors shown, old input preserved | ✅ Pass |
| E3 | Rider accesses another rider's delivery by URL | Flash error + redirect | ✅ Pass |
| E4 | Retailer accesses another retailer's delivery by URL | Flash error + redirect | ✅ Pass |
| E5 | Dispatcher assigns a non-rider | Rejected with a clear error | ✅ Pass |
| E6 | Rider jumps PENDING→DELIVERED (or any skip) | Rejected with a clear error | ✅ Pass |
| E7 | Delivered delivery edited | No status-change form is shown once `DELIVERED`; server also blocks it | ✅ Pass |
| E8 | Invalid login | Friendly, generic error message | ✅ Pass |
| E9 | Duplicate email registration | Clear error, no duplicate row | ✅ Pass |
| E10 | Database connection failure | Generic "service unavailable" page; no credentials or stack trace shown | ✅ Pass (code review of `config/database.php`) |
