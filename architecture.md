# Reflex — Architecture

## 1. Overview

Reflex is a three-tier web application:

```
Browser (HTML5 / CSS3 / Bootstrap 5 / vanilla JS)
        |
        v
PHP 8+ Application (procedural pages + small shared function library)
        |
        v
MySQL 8 / MariaDB (accessed exclusively via PDO, prepared statements)
```

There is no separate API layer and no client-side framework. Every page is a
server-rendered PHP script that reads from and writes to MySQL directly. This
is a deliberate choice for a Week 3 MVP (see `trade-off-log.md`): it keeps the
system easy to reason about, easy to deploy to ordinary shared hosting, and
easy to demo end-to-end without a build step.

## 2. Responsibilities by layer

### Frontend (Browser)
- Renders server-generated HTML using Bootstrap 5 for layout/components.
- `assets/css/style.css` supplies Reflex's own design system (colors,
  status badges, the delivery progress tracker) on top of Bootstrap.
- `assets/js/app.js` provides progressive enhancement only (auto-dismissing
  alerts, confirm dialogs, phone-input filtering). The app is fully
  functional with JavaScript disabled — every action is a real `<form>` POST.

### Backend (PHP)
- **Routing**: file-based. Each `.php` file under `retailer/`, `dispatcher/`,
  `rider/` is a page + its own form handler (GET renders, POST processes).
- **Authentication & sessions**: `auth/auth.php` — PHP native sessions,
  `password_hash()` / `password_verify()`, CSRF tokens on every form.
- **Authorization**: `requireRole('retailer'|'dispatcher'|'rider')` gates
  every protected page. Ownership checks (e.g. "is this delivery mine?") are
  additionally enforced in each page's query, not just at the role level.
- **Business rules**: status-transition validation
  (`includes/functions.php::isValidStatusTransition()`) is the single source
  of truth for the delivery status flow, used by the rider status-update
  handler.
- **Data access**: PDO with prepared statements everywhere; no string-built
  SQL. `PDO::ATTR_EMULATE_PREPARES = false` so real server-side prepared
  statements are used.

### Database (MySQL/MariaDB)
- Stores all persistent state: `users`, `deliveries`, `delivery_status_history`.
- Enforces referential integrity via foreign keys (`ON DELETE CASCADE` for
  history rows tied to a delivery; `ON DELETE SET NULL` for a delivery's
  rider if that rider account is ever removed).
- `ENUM` columns constrain `role` and `status` at the schema level as a second
  line of defense behind the PHP-level validation.

## 3. Authentication flow

1. User submits email + password to `login.php`.
2. Server looks up the user by email, verifies the password with
   `password_verify()` against the stored bcrypt hash.
3. On success, `session_regenerate_id(true)` is called (mitigates session
   fixation), then `user_id`, `user_name`, `user_email`, `role` are stored in
   `$_SESSION`.
4. Every protected page starts with `requireRole($expectedRole)`, which:
   - Redirects to `login.php` if no session exists.
   - Redirects to `auth/unauthorized.php` if the session's role doesn't match
     the page's expected role — this is what stops a rider from opening a
     dispatcher dashboard, etc.

## 4. Authorization / data-ownership model

Role alone is not sufficient — a retailer must only see *their own*
deliveries, and a rider only *their own* assigned jobs. Every query in
`retailer/*.php` filters `WHERE retailer_id = :session_user_id`, and every
query in `rider/*.php` filters `WHERE rider_id = :session_user_id`. Dispatcher
pages are intentionally unfiltered (fleet-wide oversight is the dispatcher's
job) but dispatchers cannot update a delivery's status themselves — only
assign/reassign a rider, per RULE 3/RULE 4.

## 5. Delivery status management

The status flow is a strict linear state machine:

```
PENDING -> ASSIGNED -> PICKED_UP -> IN_TRANSIT -> DELIVERED
```

- Dispatchers move a delivery from `PENDING` to `ASSIGNED` (via rider
  assignment). They may reassign the rider while status is `PENDING` or
  `ASSIGNED`, but not after pickup.
- Riders move a delivery forward one step at a time via
  `rider/update_status.php`, which calls `isValidStatusTransition()`. Any
  attempt to skip a step, move backward, or change a `DELIVERED` delivery is
  rejected server-side, regardless of what the client sends.
- Every transition (including the initial `PENDING` row created at delivery
  creation, and reassignments) is written to `delivery_status_history` inside
  a database transaction alongside the `deliveries.status` update, so the two
  can never disagree.

## 6. What happens outside the application

Reflex intentionally does not model:
- Payments or invoicing.
- Real-time GPS tracking (locations are free-text fields, not coordinates).
- SMS/push notifications to customers or riders.
- Route optimization or automatic rider assignment.

These are documented as MVP trade-offs, not oversights — see
`trade-off-log.md`.
