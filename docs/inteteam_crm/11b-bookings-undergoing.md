# Undergoing Bookings

**Bookings -> Undergoing** or navigate to `/admin/bookings/undergoing`

This is where active repair jobs live — jobs that have been accepted and are being worked on.

---

## What You See

A table of all bookings in the **Undergoing** stage. Use the filters to narrow down:

- **Filter by status** — show only bookings with a specific sub-status (e.g. "Waiting for parts", "In repair")
- **Filter by team member** — show only bookings assigned to a specific person
- **Search** — search by booking ID, name, email, or phone

Each row shows the booking ID, customer, date, priority, status, and task progress.

---

## Actions on Each Booking

Click the **three-dot menu** on any row to:

- **Complete** — move the booking to the Completed stage
- **Move Back** — return to Incoming (requires a reason)
- **Cancel** — cancel the booking (requires a reason)
- **Send WhatsApp** — open a WhatsApp message composer (only visible if WhatsApp is configured and the customer has a phone number)
- **Send SMS** — open an SMS composer (only visible if ClickSend is configured)
- **View History** — see the full audit log of changes

---

## Exporting Bookings

You can export the undergoing bookings list to a file for reporting, accounting, or sharing with your team.

### How to Export

1. Apply any filters you want (status, assignee, search) — the export only includes bookings matching your current filters
2. Click the **Export** button (top right of the bookings card)
3. Choose your format:
   - **Export CSV** — opens in Excel, Google Sheets, or any spreadsheet app
   - **Export Excel (XLSX)** — native Excel format with proper column types

The file downloads immediately. No page reload required.

### What's in the Export

| Column | Description |
|--------|-------------|
| Reference | Booking ID (or temp reference if not yet assigned) |
| Customer Name | Customer's full name |
| Email | Customer's email address |
| Phone | Customer's phone number |
| Status | Current sub-status within Undergoing |
| Location | Branch or location the booking is assigned to |
| Assignees | Team members assigned to the job |
| First Visit Date | Date and time of the earliest scheduled visit |
| Created At | When the booking was originally created |

### Tips

- **Filter before exporting** — if you only want this week's bookings for one team member, set those filters first, then export
- **CSV works in Google Sheets** — just open Google Sheets, File → Import, and select the CSV
- **Large exports** — there's no row limit; the export streams all matching records efficiently

---

## Moving Bookings Forward

When a repair is complete:

1. Click the **three-dot menu** → **Complete**
2. The booking moves immediately to Completed

Or use the booking detail page → **Complete** button at the top.

---

## Moving Bookings Back

If a job needs to go back to Incoming (e.g. wrong diagnosis, waiting for customer decision):

1. Click the **three-dot menu** → **Move Back**
2. Enter a **reason** (required — this is recorded in the history)
3. Click **Move Back**

The reason appears in the booking audit log so your team knows what happened.
