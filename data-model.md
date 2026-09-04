# Reflex — Data Model

## Entity-Relationship summary

```
users (1) ───< (many) deliveries [as retailer]
users (1) ───< (many) deliveries [as rider, nullable]
deliveries (1) ───< (many) delivery_status_history
users (1) ───< (many) delivery_status_history [as changed_by]
```

## Table: `users`

| Column      | Type                                      | Notes                                   |
|-------------|-------------------------------------------|------------------------------------------|
| id          | INT UNSIGNED, PK, AUTO_INCREMENT          |                                          |
| name        | VARCHAR(100)                              | Required                                |
| email       | VARCHAR(150), UNIQUE                      | Login identifier                        |
| password    | VARCHAR(255)                              | bcrypt hash via `password_hash()`       |
| role        | ENUM('retailer','dispatcher','rider')     | Drives all authorization                |
| phone       | VARCHAR(20)                               | Required                                |
| is_active   | TINYINT(1), default 1                     | Deactivated users can't log in / be assigned |
| created_at  | TIMESTAMP, default CURRENT_TIMESTAMP      |                                          |

Index: `idx_users_role (role)` — speeds up "list all active riders" queries
used on the dispatcher dashboard and assignment page.

**Design note:** a single `users` table with a `role` enum (rather than three
separate tables) keeps authentication simple — one login form, one query —
while still supporting role-specific dashboards and permissions in the
application layer.

## Table: `deliveries`

| Column               | Type                                                              | Notes |
|----------------------|--------------------------------------------------------------------|-------|
| id                   | INT UNSIGNED, PK, AUTO_INCREMENT                                    |       |
| retailer_id          | INT UNSIGNED, FK → users.id                                        | `ON DELETE CASCADE` |
| rider_id             | INT UNSIGNED, FK → users.id, NULL                                  | `ON DELETE SET NULL`; NULL = unassigned |
| customer_name        | VARCHAR(100)                                                        | Required |
| customer_phone       | VARCHAR(20)                                                         | Required, validated |
| pickup_location      | VARCHAR(255)                                                        | Required |
| delivery_location    | VARCHAR(255)                                                        | Required |
| package_description  | VARCHAR(255)                                                        | Required |
| notes                | TEXT, NULL                                                          | Optional |
| status               | ENUM('PENDING','ASSIGNED','PICKED_UP','IN_TRANSIT','DELIVERED')     | Default `PENDING` |
| created_at           | TIMESTAMP, default CURRENT_TIMESTAMP                                |       |
| updated_at           | TIMESTAMP, default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP    | Auto-bumped on any change |

Indexes: `idx_deliveries_status`, `idx_deliveries_retailer`,
`idx_deliveries_rider` — support the dashboards' filtered/aggregated queries
(counts by status, "my deliveries", "my jobs") without full table scans.

## Table: `delivery_status_history`

| Column       | Type                                                              | Notes |
|--------------|--------------------------------------------------------------------|-------|
| id           | INT UNSIGNED, PK, AUTO_INCREMENT                                    |       |
| delivery_id  | INT UNSIGNED, FK → deliveries.id                                    | `ON DELETE CASCADE` |
| status       | ENUM(...)                                                            | The status *as of* this record |
| changed_by   | INT UNSIGNED, FK → users.id                                         | Who triggered the change |
| note         | VARCHAR(255), NULL                                                   | e.g. "Assigned to Brian Otieno" |
| created_at   | TIMESTAMP, default CURRENT_TIMESTAMP                                 |       |

Index: `idx_history_delivery` — the delivery detail pages fetch a full,
ordered timeline for one delivery.

**Design note:** this table is append-only. Nothing in the application ever
`UPDATE`s or `DELETE`s a history row — it is a genuine audit trail, satisfying
RULE 6 ("every important status change must be recorded").

## Why not more normalization?

A "statuses" lookup table or a separate "assignments" table were considered
and rejected for the MVP: five fixed statuses are cleanly expressed as an
`ENUM`, and a delivery has at most one active assignment at a time, so a
nullable `rider_id` column captures that without an extra join on every page.
