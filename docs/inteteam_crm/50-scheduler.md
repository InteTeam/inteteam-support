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
- **Reschedule** button to move the visit to a new date and time — see "Rescheduling a Visit" below
- **Cancel** button to cancel the visit — see "Cancelling a Visit" below
- **Convert to Booking** button (admin) — turns a standalone scheduler visit into a full Booking, which then appears in **Bookings -> Incoming**. Useful when a visit that was only ever booked as an appointment turns out to need the full repair workflow (tasks, notes, invoicing). The visit's reference carries over to the new booking.

---

## Rescheduling a Visit

Move a visit to a new date and time without cancelling it. The visit keeps its reference, its linked booking, its notes and its history.

1. Click the visit on the calendar, then click **Reschedule**
2. Pick the **New start**. The **New end** follows automatically so the visit keeps its length; change it only if the visit should be longer or shorter
3. Add a **Reason** (optional) — it is added to the visit's notes
4. Leave **Notify customer** ticked to tell the customer, or untick it if they already know (for example, they asked for the change on the phone)
5. Click **Move visit**

**Reschedule** is available for visits that are Booked or No Show. A Confirmed or Cancelled visit can't be moved.

### If the new time isn't available

- **The slot is full** — you'll see "Selected reschedule time is not available" under the time fields. Pick another time.
- **Outside business hours, on a closed day, or on a holiday** — an amber warning appears and **Move visit** is disabled. Admins can tick **Override availability check** to move it anyway. Other team members need to ask an admin.

The same rules apply when a customer moves their own appointment through their manage link: they can't move it to a time you're closed.

### What the customer receives

Under **Notify customer** there's a line saying exactly what will be sent, for example "Email to jo@example.com" or "SMS to 07123 456789 (no email on file)".

- The email shows the old and the new time and includes a calendar file that **moves** the existing entry in the customer's calendar rather than adding a second one.
- If you see an amber "they will NOT be notified" warning, there's no email address or text option for this customer — phone them.

---

## Cancelling a Visit

1. Click the visit on the calendar, then click **Cancel**
2. Read the warning: it tells you whether the visit is linked to a booking. Cancelling a visit never cancels or changes the booking itself
3. Leave **Notify customer** ticked to tell the customer, or untick it
4. Click **Cancel visit**

The customer gets an email (or text) saying the appointment on that date has been cancelled. A Confirmed visit can't be cancelled — use **Revert Confirmation** first if it was confirmed by mistake.

---

## Customer Notifications (moved or cancelled visits)

**Settings → Scheduler → Notifications**, or `/admin/scheduler/settings/notifications`

Choose how customers are told when a visit is moved or cancelled. Admins can change these; other team members can view them.

| Setting | Default |
|---------|---------|
| When staff move or cancel a visit — email the customer | On |
| When staff move or cancel a visit — text the customer | Off |
| When a customer moves or cancels their own visit — email the customer | On |
| When a customer moves or cancels their own visit — text the customer | Off |
| Text customers who have no email address | On |

- **Email works for every shop**, even if you haven't set up your own email provider.
- **Texts need an SMS provider** (Settings → ClickSend SMS or InteBox SMS Bridge). Without one the text options are switched off. Texts are sent from your own SMS account, so each one costs what your provider charges.
- Texts appear in **Conversations** in the customer's SMS thread, so you can see what they were told.
- A customer who has opted out of SMS is never texted.

### Checking what was sent

- The visit's history records each notice: sent (and on which channel), failed, or skipped because there was no way to reach the customer.
- If the visit is linked to a booking, the booking's **History** tab shows "visit rescheduled" or "visit cancelled".
- If an email or text fails, you get a notification in the bell so you can contact the customer another way.

---

## Overdue Visits

Visits past their scheduled time that haven't been marked as completed or cancelled are highlighted as overdue.

---

## DST (Daylight Saving Time)

The calendar shows a notification when daylight saving time changes affect upcoming appointments.
