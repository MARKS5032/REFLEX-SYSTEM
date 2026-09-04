# Reflex — Trade-off Log

This log records real trade-offs made to ship a working MVP within the Week 3
timeframe. Each entry states the weakness plainly, why it was acceptable for
this assessment, and what would change with more time.

---

## 1. Manual rider assignment instead of automatic optimization

**Weakness:** Dispatchers manually pick a rider from a plain list (sorted
only by current active-job count). There is no route optimization, distance
calculation, or load-balancing algorithm.

**Why acceptable for the MVP:** Small retailers in the target market
typically have 1-5 riders total; a human dispatcher can make a better call
than a naive algorithm at that scale, and building real optimization would
require geocoding and a routing engine — out of scope for a Week 3 build.

**What would improve with more time:** Add geocoded pickup/delivery
coordinates, compute rider proximity, and suggest (not force) an optimal
rider, with the dispatcher still making the final call.

---

## 2. Basic session authentication instead of SSO/MFA

**Weakness:** Login is email + password with PHP native sessions. No
multi-factor authentication, no "remember me", no password reset flow, no
SSO.

**Why acceptable for the MVP:** The assessment requires demonstrating secure
*fundamentals* — hashed passwords, session management, CSRF protection,
role-based access control — which Reflex implements correctly. MFA and SSO
add real value in production but aren't necessary to prove the core
authentication model works.

**What would improve with more time:** Add a "forgot password" flow (email
token + expiry), optional TOTP-based MFA for dispatcher accounts (highest
privilege), and account lockout after repeated failed logins.

---

## 3. Free-text location fields instead of real-time GPS/maps

**Weakness:** Pickup and delivery locations are plain text
(`VARCHAR(255)`), not geocoded coordinates. There's no live map, no rider
GPS tracking, no distance/ETA calculation.

**Why acceptable for the MVP:** Small retailers currently coordinate
deliveries by phone call and memory — free-text locations are already a big
improvement, are trivial to fill in on a low-end phone, and require no paid
mapping API (a hard constraint for free-tier hosting).

**What would improve with more time:** Integrate a free-tier geocoding
service to store lat/lng alongside the text address, and show delivery
routes on a map for dispatchers and customers.

---

## 4. No SMS notifications

**Weakness:** Customers and riders are not proactively notified (SMS/push)
when a delivery's status changes; they only see it if they open the app.

**Why acceptable for the MVP:** SMS gateways for Kenya (e.g. Africa's
Talking) require paid credits and account setup that fall outside the "no
paid APIs" constraint for this assessment.

**What would improve with more time:** Add an SMS integration behind a
provider interface so it can be swapped in without touching business logic,
funded once the business is validated.

---

## 5. Single MySQL database, no caching or queueing layer

**Weakness:** All reads and writes go straight to one MySQL database. There
is no caching layer, background job queue, or read replica.

**Why acceptable for the MVP:** At the transaction volume of a small
retailer (a handful to a few dozen deliveries per day), a single database
comfortably handles the load, and simplicity keeps the system deployable on
free shared hosting.

**What would improve with more time:** Introduce a queue for notification
sending and a read replica if/when the retailer base and delivery volume
grow significantly.

---

## 6. Open self-registration for all three roles

**Weakness:** `register.php` lets anyone create a Dispatcher or Rider
account directly, with no invite code, approval step, or verification that
the person actually works for the business. In a real deployment this would
let a stranger self-assign as staff and start reassigning or "delivering"
other retailers' packages.

**Why acceptable for the MVP:** This assessment needs to demonstrate the
full three-role workflow live and repeatably to a panel — being able to
register a fresh dispatcher or rider on the spot (rather than relying only
on fixed seeded accounts, or manual `phpMyAdmin` inserts mid-presentation)
makes the demo far more convincing and flexible.

**What would improve with more time:** Restrict Dispatcher/Rider
registration behind an invite code issued by an existing dispatcher, or
build a small "Manage staff" screen inside the dispatcher dashboard so new
rider/dispatcher accounts are created by a trusted user inside the app
instead of via open public sign-up.

---

## Summary table

| # | Trade-off | Acceptable because | Future improvement |
|---|-----------|--------------------|--------------------|
| 1 | Manual rider assignment | Small fleets, human judgement > naive algorithm | Proximity-based suggestions |
| 2 | Basic auth, no MFA/SSO | Core security fundamentals are correctly implemented | Password reset + optional MFA |
| 3 | Text locations, no GPS | No paid mapping API required; simple to use | Geocoding + live map |
| 4 | No SMS notifications | Avoids paid SMS gateway dependency | Pluggable SMS provider |
| 5 | Single DB, no caching/queue | Sufficient for small-retailer transaction volume | Queue + read replica at scale |
| 6 | Open self-registration for all roles | Makes live demos flexible and repeatable | Invite codes or in-app staff management |
