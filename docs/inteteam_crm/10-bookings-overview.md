# Bookings Overview

Bookings are the core of the CRM. Every repair job is a booking that moves through three stages:

```
Incoming  -->  Undergoing  -->  Completed
```

- **Incoming** — New booking, waiting to be picked up
- **Undergoing** — Being worked on by a technician
- **Completed** — Repair finished

Bookings can also be **moved back** a stage if needed (e.g. undergoing back to incoming), or **cancelled** with a reason.

---

## Finding Bookings

All booking lists are under the **Bookings** dropdown in the top navigation:

| Menu item | What it shows |
|-----------|-------------|
| **Incoming** | All new bookings waiting to be processed |
| **Undergoing** | All bookings currently being repaired |
| **Completed** | All finished bookings |
| **Settings** | Stage statuses and task templates (admin only) |

---

## The Booking List

Each booking list (Incoming, Undergoing, Completed) shows a table with:

| Column | What it shows |
|--------|-------------|
| **Booking ID** | Reference number — click to open the booking |
| **Customer** | Customer name (surname can be hidden with the privacy toggle) |
| **Date & Time** | When the booking was created |
| **Priority** | Customer priority + internal priority badges |
| **Status** | Coloured badge — click to change status inline |
| **Tasks** | Progress indicator (e.g. 3/5 = 3 of 5 tasks done) |

### Searching & Filtering

At the top of every booking list (Incoming, Undergoing, Completed):

- **Status filter** — dropdown to show only bookings with a specific status (e.g. "Waiting for parts"). Shows a coloured dot next to each status name. Select "All statuses" to clear the filter.
- **Assignee filter** — show only bookings assigned to a specific team member
- **Search box** — search by booking ID, customer name, email, or phone

Filters work together — you can combine status + assignee + search to narrow down exactly what you need. On mobile, these stack vertically below the page title.

Below the table, pagination controls include **First** and **Last** page buttons alongside Previous/Next — useful for jumping straight to the oldest or newest bookings in a long list.

### Row Actions

Click the **three-dot menu** on any booking row for quick actions:

- **History** — view the full audit trail
- **Send WhatsApp** — message the customer (if phone number available)
- **Send SMS** — text the customer
- **Move to Undergoing / Complete** — advance the booking
- **Move Back** — return to previous stage
- **Cancel Booking** — cancel with a reason
