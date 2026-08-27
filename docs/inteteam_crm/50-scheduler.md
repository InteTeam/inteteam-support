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
| **Pending** | Scheduled, not yet confirmed |
| **Confirmed** | Customer confirmed |
| **Completed** | Visit done |
| **Cancelled** | Visit cancelled |
| **No Show** | Customer didn't attend |

---

## Viewing a Visit

Click any visit on the calendar to see its details:

- Booking reference (linked to the booking)
- Customer name, email, phone
- Start and end time
- Status
- Notes
- **Edit** button to change details
- **Delete** button to remove the visit
- **Convert to Booking** button (admin) — turns a standalone scheduler visit into a full Booking, which then appears in **Bookings -> Incoming**. Useful when a visit that was only ever booked as an appointment turns out to need the full repair workflow (tasks, notes, invoicing). The visit's reference carries over to the new booking.

---

## Overdue Visits

Visits past their scheduled time that haven't been marked as completed or cancelled are highlighted as overdue.

---

## DST (Daylight Saving Time)

The calendar shows a notification when daylight saving time changes affect upcoming appointments.
