# Calendar & Appointments

View and manage appointments for your locations.

**Scheduler** in the top navigation, or navigate to `/admin/scheduler`

---

## Calendar View

The scheduler shows a calendar with three view modes:

- **Day** — one day at a time, detailed hourly slots
- **Week** — full week overview
- **Month** — monthly overview

Switch views using the **Day / Week / Month** buttons at the top.

### Navigation

- **Previous / Next** arrows — move between days/weeks/months
- **Today** button — jump back to today
- **Keyboard shortcuts:** Left arrow (previous), Right arrow (next), T (today), Esc (close modals)

### Location Selector

If you have multiple locations, use the **location dropdown** to switch between them. Each location has its own calendar.

### Privacy Toggle

Click the **eye icon** to hide/show customer surnames on the calendar (useful when screen-sharing or when the screen is visible to other customers).

---

## Creating a Visit

1. Click on an **empty time slot** in the calendar, or click the **New Visit** button
2. Fill in:
   - Customer details
   - Date and time (pre-filled if you clicked a slot)
   - Visit type (one-time or recurring)
   - Location
   - Assigned agent
   - **Service Address** (admins only) — for mobile/collection businesses that visit the customer rather than the customer visiting a shop. Not shown or editable by non-admin team members.
   - Notes (optional)
3. Save

The visit appears on the calendar with the customer name and a status badge.

---

## Visit Statuses

| Status | Meaning |
|--------|---------|
| **Booked** | Scheduled, customer hasn't arrived/been served yet (yellow) |
| **Confirmed** | Customer arrived and was served/dropped off — click **Confirm** to set this (green) |
| **Cancelled** | Visit cancelled |
| **No Show** | Customer didn't attend |

"Confirmed" applies to any business shape — a walk-in drop-off, a collection, or a visit where staff go to the customer instead. It isn't limited to a physical shop counter.

---

## Confirming a Visit

When the customer arrives (or is served, for mobile/collection visits), mark it:

- **From the calendar** — hover over the visit and click the small checkmark that appears on the card. One click, no confirmation dialog (it's quick and reversible).
- **From the visit detail modal** — click **Confirm**.

This is available on any visit that isn't already Confirmed, Cancelled, or No Show — it doesn't matter how the visit was originally created (manually, via the online booking widget, or through the public scheduler API), it always shows up.

**Clicked Confirm by mistake?** Open the visit and click **Revert Confirmation** to put it back to Booked.

---

## Viewing a Visit

Click any visit on the calendar to see its details:

- **Visit reference** — always shown
- **Booking** — only shown if this visit has been converted into a full Booking (see below). Shows that booking's own reference and its current stage (Incoming / Undergoing / Completed), and is a link that opens the booking's page in a new tab — no need to go hunting through Bookings -> Incoming/Undergoing/Completed to find it, it opens directly regardless of which stage it's in. If there's no linked booking yet, you'll see a "Visit only — not yet a booking" badge instead.
- Customer name, email, phone
- Start and end time
- Status
- Notes
- **Edit** button to change details
- **Confirm** button — see "Confirming a Visit" above
- **Cancel** button to cancel the visit — asks for confirmation first, and tells you whether a linked booking is affected (it isn't; cancelling the visit doesn't touch the booking)
- **Convert to Booking** button (admin) — turns a standalone scheduler visit into a full Booking, which then appears in **Bookings -> Incoming**. Useful when a visit that was only ever booked as an appointment turns out to need the full repair workflow (tasks, notes, invoicing). The visit's reference carries over to the new booking.

---

## Overdue Visits

Visits past their scheduled time that haven't been marked as completed or cancelled are highlighted as overdue.

---

## DST (Daylight Saving Time)

The calendar shows a notification when daylight saving time changes affect upcoming appointments.
