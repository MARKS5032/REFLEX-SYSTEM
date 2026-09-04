# Reflex — Delivery Management System

**Simple delivery management for small retailers.**

Reflex is a real, working PHP/MySQL web application built as a Week 3 final
assessment project. It lets small Kenyan retailers create deliveries,
dispatchers assign riders, and riders track jobs through to completion — with
role-based access control, a full audit trail, and a professional dashboard
UI, end to end.

---

## 1. Project objective

Demonstrate a complete, secure, deployable delivery-management workflow:

```
Retailer creates delivery → Dispatcher assigns rider → Rider updates status → Retailer sees final result
```

## 2. Problem statement

Small retailers currently coordinate deliveries by phone call and memory.
There's no shared record of what was promised, who's carrying it, or where it
is — leading to missed handoffs, disputes, and no visibility for the
customer. Reflex replaces that with one shared, trackable record per
delivery.

## 3. Features

- Secure login/logout with hashed passwords and session-based auth
- Role-based dashboards for **Retailer**, **Dispatcher**, **Rider**
- Retailers: create deliveries, track status, view full history
- Dispatchers: fleet-wide overview, assign/reassign riders
- Riders: see assigned jobs, advance status one step at a time
- Full audit trail — every status change is logged with who and when
- Enforced business rules (ownership, valid status transitions, role checks)
- Responsive, professional UI with a visual delivery progress tracker

## 4. User roles

| Role       | Can do |
|------------|--------|
| Retailer   | Create deliveries, view their own deliveries and status history |
| Dispatcher | View all deliveries, assign/reassign riders, monitor the fleet |
| Rider      | View jobs assigned to them, advance status step by step |

## 5. Technology stack

- **Frontend:** HTML5, CSS3, vanilla JavaScript, Bootstrap 5
- **Backend:** PHP 8+, procedural style with a small shared function library
- **Database:** MySQL / MariaDB, accessed via PDO with prepared statements
- **Local dev:** XAMPP (Apache + MySQL + PHP), phpMyAdmin, VS Code
- **No Node.js, no Docker, no paid APIs, no paid services required**

## 6. Architecture

See [`docs/architecture.md`](docs/architecture.md) for the full breakdown.
Summary: Browser → PHP application → MySQL database. Every protected page
enforces both a role check (`requireRole()`) and a data-ownership check
(retailers only see their deliveries, riders only see their jobs).

## 7. Database structure

See [`docs/data-model.md`](docs/data-model.md) for full column-level detail.
Three tables: `users`, `deliveries`, `delivery_status_history`.

---

## 8. Installation instructions (local, XAMPP)

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) installed (Apache + MySQL + PHP 8+)
- A code editor (VS Code recommended)

### Steps

1. **Copy the project into htdocs**
   Copy the entire `reflex/` folder into your XAMPP `htdocs` directory, e.g.:
   ```
   C:\xampp\htdocs\reflex        (Windows)
   /Applications/XAMPP/htdocs/reflex   (macOS)
   /opt/lampp/htdocs/reflex      (Linux)
   ```

2. **Start Apache and MySQL**
   Open the XAMPP Control Panel and start both **Apache** and **MySQL**.

## 9. Database setup using phpMyAdmin

1. Open `http://localhost/phpmyadmin` in your browser.
2. Click **Import**, or open the **SQL** tab and paste the contents of
   [`database/schema.sql`](database/schema.sql), then click **Go**. This
   creates the `reflex_db` database and all three tables.
3. Repeat with [`database/seed.sql`](database/seed.sql) to load demo data
   (2 retailers, 1 dispatcher, 3 riders, 6 deliveries in varied statuses).
4. Confirm `reflex_db` now has three tables (`users`, `deliveries`,
   `delivery_status_history`) with data in each.

If your MySQL `root` user has a password, or you're using a different
username, update [`config/database.php`](config/database.php) accordingly:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'reflex_db');
define('DB_USER', 'root');
define('DB_PASS', '');   // set your password here if any
```

## 10. How to run locally

With Apache and MySQL running and the database imported, open:

```
http://localhost/reflex/index.php
```

You'll land on the Reflex homepage. Click **Log in** and use one of the demo
accounts below.

## 11. Demo credentials

All demo accounts use the password: **`Reflex@123`**
*(These are demonstration credentials only — never reuse them in a real
deployment.)*

| Role       | Email                     |
|------------|---------------------------|
| Retailer   | `retailer@reflex.test`    |
| Retailer 2 | `retailer2@reflex.test`   |
| Dispatcher | `dispatcher@reflex.test`  |
| Rider      | `rider1@reflex.test`      |
| Rider      | `rider2@reflex.test`      |
| Rider      | `rider3@reflex.test`      |

Anyone can also self-register at `/reflex/register.php` and choose their
account type (Retailer, Dispatcher, or Rider) — useful for demoing the full
system live during a presentation. In a real production deployment you'd
typically lock dispatcher/rider sign-up behind an invite code or an
admin-only "add staff" screen, since those roles can assign deliveries and
act as couriers (see [`docs/trade-off-log.md`](docs/trade-off-log.md)).

## 12. Test scenarios

Full manual test plan with results in
[`docs/test-cases.md`](docs/test-cases.md), covering authentication, each
role's permissions, database integrity, security, and edge cases. A live
demo walkthrough is in [`docs/demo-script.md`](docs/demo-script.md).

## 13. Trade-offs

Reflex makes several deliberate MVP trade-offs (manual rider assignment,
text-based locations instead of GPS, no SMS notifications, and others), each
explained with reasoning and a future-improvement plan in
[`docs/trade-off-log.md`](docs/trade-off-log.md).

## 14. Known limitations

- No password-reset flow (forgot-password).
- No real-time GPS tracking — locations are free-text fields.
- No SMS/push notifications.
- No automatic rider assignment or route optimization.
- Registration is open to all three roles with no verification step (by
  design, for demo purposes — see `docs/trade-off-log.md` for the
  production-hardening note on this).

## 15. Future improvements

See [`docs/executive-presentation.md`](docs/executive-presentation.md) for
the roadmap: password reset, geocoded locations with a live map, SMS status
notifications, proximity-based rider suggestions, and a customer-facing
tracking link that doesn't require login.

## 16. Deployment guidance for free PHP/MySQL hosting

Reflex is built to run on ordinary shared PHP/MySQL hosting (e.g.
InfinityFree, or any host offering PHP 8+ and MySQL). **Free hosting is for
demonstration purposes** — treat it as an MVP showcase, not a production
deployment (no SLA, limited resources, shared infrastructure).

### Steps

1. **Export the local database**
   In phpMyAdmin: select `reflex_db` → **Export** → format **SQL** → **Go**.
   Save the `.sql` file. (Alternatively, just reuse `database/schema.sql`
   and `database/seed.sql` directly — they're already hosting-ready.)

2. **Create a remote MySQL database**
   In your hosting control panel (e.g. cPanel), create a new MySQL database
   and a database user with full privileges on it. Note the host, database
   name, username, and password the host gives you (shared hosts often use a
   non-`localhost` DB host and a prefixed database name).

3. **Import schema and data**
   Open the remote phpMyAdmin (or your host's DB tool) and import
   `schema.sql` first, then `seed.sql` — same process as the local setup in
   section 9.

4. **Change database credentials**
   Edit `config/database.php` and replace the local values with the ones
   your host gave you:
   ```php
   define('DB_HOST', 'your-host-provided-hostname');
   define('DB_NAME', 'your_prefixed_db_name');
   define('DB_USER', 'your_prefixed_db_user');
   define('DB_PASS', 'your_db_password');
   ```

5. **Upload PHP files**
   Upload the entire `reflex/` folder to your host's public web directory
   (often `htdocs/` or `public_html/`) via FTP or the host's file manager.
   Preserve the folder structure exactly as-is.

6. **Test the live system**
   Visit your live URL (e.g. `https://yoursite.infinityfreeapp.com/reflex/`)
   and walk through the demo script in
   [`docs/demo-script.md`](docs/demo-script.md) to confirm login, delivery
   creation, assignment, and status updates all work against the remote
   database.

**Note on absolute paths:** pages link internally using absolute paths
starting with `/reflex/...` (e.g. `/reflex/login.php`). If you deploy to a
subfolder with a different name, either rename your upload folder to
`reflex`, or do a find-and-replace of `/reflex/` across the PHP files to
match your actual folder name.

---

## Project structure

```
reflex/
├── index.php               Landing page
├── login.php                Login form + handler
├── logout.php                Session destroy
├── register.php               Retailer self-registration
├── config/database.php          PDO connection + credentials
├── auth/
│   ├── auth.php                 Session, role, CSRF helpers
│   └── unauthorized.php          403 page
├── retailer/                   dashboard, create_delivery, deliveries, delivery_view
├── dispatcher/                  dashboard, deliveries, assign_rider, delivery_view
├── rider/                       dashboard, deliveries, update_status, delivery_view
├── includes/                    header, footer, navbar, alerts, functions
├── assets/css/style.css        Reflex design system
├── assets/js/app.js             Progressive-enhancement JS
├── database/schema.sql            Full normalized schema
├── database/seed.sql               Demo data
└── docs/                        architecture, data-model, trade-off-log,
                                    demo-script, test-cases, timing-log,
                                    executive-presentation
```

## Git commit plan (suggested history for collaborative development)

```
feat: create database schema - establishes core delivery data
feat: add authentication - enables secure role-based access
feat: add retailer delivery creation - allows retailers to submit deliveries
feat: add dispatcher assignment - enables rider allocation
feat: add rider status flow - tracks delivery progress
feat: add status history - provides delivery audit trail
feat: improve dashboard UI - makes workflow easier to demonstrate
test: validate role permissions - prevents unauthorized access
docs: add architecture documentation - explains system design
fix: prevent invalid status transition - protects delivery workflow
```

---

Built as a Week 3 final assessment project. Prioritizes functionality,
clarity, reliability, security, and demonstrability over enterprise scale.
