# Booking Detail Page

Click any booking ID to open its detail page. Everything about a single booking is here, organised in tabs.

---

## Header

At the top you'll see:

- **Booking reference** (e.g. INT-001/03-2026)
- **Stage badge** — Incoming / Undergoing / Completed
- **Status dropdown** — click to change the current status
- **Print Label button** — prints a barcode label for this booking (see [Printing Labels](21-label-printing.md))
- **Action buttons** — Move to Undergoing, Complete, or three-dot menu for more options

---

## Tabs

### Details Tab

The default view with:

- **Booking Information** — date, time, location
- **Priority** — Customer priority and Internal priority (editable dropdown)
- **Assigned Team** — who's working on this booking. Add/remove team members here
- **Form Data** — if the customer submitted a booking form, their answers appear here

### Customer Tab

Customer contact details:

- Name, email, phone number
- **Edit** button to update customer information

### Visits Tab

Scheduled appointments linked to this booking:

- Each visit shows: visit number, visit type, status, date, time, location, assigned agent and description
- Click **Add Visit** to schedule a new appointment. The **Visit Type** dropdown offers your company's own visit types, or the built-in list if you haven't set any up (see [Visit Types](52-visit-types.md))
- Set the **start time** and the **end time** auto-fills using the location's default appointment length — or click a **30 / 60 / 90 / 120 min** preset button to set the duration directly. Editing the end time by hand stops the auto-fill until you change the start time, location, or a preset again
- If the slot falls outside business hours or is otherwise unavailable, you'll get a validation error with an option to override it

#### Editing a Visit

Made a mistake? Each visit that isn't cancelled has an **Edit** button. **Edit visit** lets you change:

- **Visit Type**: your company's current list, plus the visit's own type (marked "(current)"), so an old or removed type can be kept while you fix something else
- **Status**: **Booked** (not happened yet), **Confirmed** (customer arrived / visit done) or **No-show**. Setting Confirmed works the same as confirming the visit in the Scheduler
- **Assigned to**: a member of your team, or **Nobody**
- **Description**

What you can't change here:

- **Date and time**: use **Reschedule** in the Scheduler, which checks availability and can tell the customer
- **Cancelling**: use **Cancel** in the Scheduler, which can tell the customer
- **Location**

Every change is recorded in the booking's **History** tab as **Visit Edited**, with the old and new values. A cancelled visit can't be edited.

### Tasks Tab

Checklist of work to be done:

- **Tick** a task to mark it complete
- **Add** new tasks by typing and pressing Enter
- **Drag** tasks to reorder them
- **Apply Template** to add a preset group of tasks
- **Filter by department** using the buttons at the top
- **Delete** individual tasks with the X button

### Notes Tab

Internal notes visible only to your team:

- Type in the text area and submit to add a note
- **Admins** see a visibility dropdown next to the text box: **Visible to everyone** (default) or **Admins only** — non-admin team members can only add general notes and won't see this option
- Notes marked Admins only show an **"Admins only"** badge and are hidden from non-admin team members entirely
- Notes show who wrote them and when
- Newest notes appear first
- Notes include which stage the booking was in when the note was added

### Costs Tab

Financial overview for this booking — costs, charges, and totals.

### History Tab

Full audit trail showing every action taken on this booking:

- **Stage changes** (blue badge) — when the booking moved between stages
- **Status updates** (purple badge) — sub-status changes
- **Moved back** (orange badge) — includes the reason
- **Created** (green badge) — when the booking was first created

Each entry shows the timestamp and who made the change.

---

## Quick Actions (Three-Dot Menu)

The three-dot menu in the header gives you:

- **Edit Customer Details** — update name, email, phone
- **View WhatsApp Chat** — opens the linked WhatsApp conversation for this booking (appears once a conversation has been linked)
- **Send WhatsApp** — send a template message to start a new conversation (requires WhatsApp configured and customer phone number on file)
- **Send SMS** — text the customer
- **Move Back** — return to previous stage (with reason)
- **Cancel Booking** — cancel with reason
