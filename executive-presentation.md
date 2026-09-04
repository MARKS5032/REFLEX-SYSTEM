# Reflex — Executive Presentation Outline

5 slides. Each slide has exactly one takeaway — say it in the first
sentence, then support it.

---

## Slide 1 — Problem

**Takeaway:** Small Kenyan retailers manage deliveries through phone calls
and memory, with no visibility once a package leaves the shop.

- Retailers have no record of what was promised, to whom, or when.
- Dispatch is informal — "call Brian and see if he's free."
- Customers and retailers have zero visibility into where a delivery is.
- Nothing is written down, so disputes ("it never arrived") are
  he-said-she-said.

---

## Slide 2 — Solution

**Takeaway:** Reflex gives every delivery a single source of truth, from
creation to doorstep.

- Three roles, one shared record: Retailer creates → Dispatcher assigns →
  Rider updates → Retailer tracks.
- A strict five-step status flow (Pending → Assigned → Picked Up → In
  Transit → Delivered) that can't be skipped or faked.
- Every status change is logged with who changed it and when.

---

## Slide 3 — Architecture

**Takeaway:** A deliberately simple three-tier stack that any PHP/MySQL host
can run.

- Browser → PHP application → MySQL database. No microservices, no Node.js
  build step, no paid APIs.
- PDO + prepared statements everywhere; bcrypt password hashing;
  role-based access control on every page.
- Runs locally on XAMPP and deploys to free PHP/MySQL hosting for the demo.

*(See `architecture.md` for the full breakdown.)*

---

## Slide 4 — Trade-offs

**Takeaway:** This is an honest MVP — three trade-offs were made
deliberately, not by accident.

1. Manual rider assignment instead of automatic route optimization.
2. Free-text locations instead of live GPS/maps.
3. No SMS notifications, to avoid a paid gateway dependency.

*(Full reasoning and future plans in `trade-off-log.md`.)*

---

## Slide 5 — Roadmap

**Takeaway:** The next phase adds visibility and reach, not new roles.

- Near-term: password reset flow, geocoded locations + map view.
- Mid-term: SMS status notifications, proximity-based rider suggestions.
- Long-term: customer-facing tracking link (no login required), analytics
  dashboard for retailers (on-time rate, average delivery time per rider).
