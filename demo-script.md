# Reflex — Demo Script

Target length: **4-6 minutes**. Practice against `timing-log.md` before the
real presentation.

Use the seeded demo accounts (password for all: `Reflex@123`):

| Role       | Email                     |
|------------|---------------------------|
| Retailer   | retailer@reflex.test      |
| Dispatcher | dispatcher@reflex.test    |
| Rider      | rider1@reflex.test        |

Open three browser windows (or one regular + one incognito + one different
browser) so you can switch between roles without repeated logout/login.

---

## 1. Retailer logs in
- Go to `/reflex/login.php`.
- Log in as `retailer@reflex.test`.
- **Say:** "This is Grace, a small retailer. She lands straight on her
  dashboard showing her delivery stats."

## 2. Retailer creates a delivery
- Click **New Delivery**.
- Fill in:
  - Customer: `Faith Njoroge`
  - Phone: `0798123456`
  - Pickup: `Reflex Store, Kisumu`
  - Delivery: `Milimani Estate`
  - Package: `Baby care products`
  - Notes: `Fragile, handle with care.`
- Submit. **Say:** "The delivery is created as Pending, and Grace can already
  see it in her tracker."

## 3. Dispatcher sees the delivery
- Switch to the dispatcher window, log in as `dispatcher@reflex.test`.
- **Say:** "Alice, the dispatcher, sees this new request under 'Needs
  attention' on her dashboard — it's unassigned."

## 4. Dispatcher assigns a rider
- Click **Assign rider** on the new delivery.
- Pick a rider (e.g. Brian Otieno) from the list — **point out** the
  active-job count shown next to each rider.
- Confirm assignment. **Say:** "The status automatically moves from Pending
  to Assigned, and it's logged in the delivery's history."

## 5. Rider logs in
- Switch to the rider window, log in as `rider1@reflex.test`.
- **Say:** "Brian only ever sees deliveries assigned to him — nothing else."

## 6. Rider views the assigned delivery
- Open the new job from the dashboard.
- **Say:** "He can see the customer, pickup and delivery locations, and the
  package details — everything he needs, nothing he doesn't."

## 7. Rider marks Picked Up
- Click **Mark as Picked Up**, confirm.
- **Say:** "One tap moves it forward exactly one step. If he tried to jump
  straight to Delivered, the system would reject it — I can show that."
  *(Optional: demonstrate the rejection by editing the form in devtools, or
  simply state that the server enforces this regardless of what the client
  sends.)*

## 8. Rider marks In Transit
- Click **Mark as In Transit**, confirm.

## 9. Rider marks Delivered
- Click **Mark as Delivered**, confirm.
- **Say:** "That's the final step — the system won't allow any further
  status change on a Delivered order."

## 10. Retailer views final Delivered status
- Switch back to the retailer window, refresh the delivery view (or the
  dashboard).
- **Say:** "And Grace sees the full journey — every step, who made the
  change, and when — without a single phone call."

---

## Closing line
"That's the complete loop: retailer creates, dispatcher assigns, rider
delivers, retailer tracks — with role-based access and a full audit trail at
every step."
