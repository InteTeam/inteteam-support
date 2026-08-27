# Customizing Stage Statuses

Create custom sub-statuses for each booking stage, like "Waiting for parts" or "Customer contacted".

**Bookings -> Settings -> Stage Statuses**, or navigate to `/admin/bookings/settings/stage-statuses`

---

## What Are Statuses?

Each booking stage (Incoming, Undergoing, Completed) can have its own set of coloured sub-statuses. These appear as colour-coded badges on bookings and can be changed inline from the booking list.

For example, the **Undergoing** stage might have:
- "Diagnosing" (blue)
- "Waiting for parts" (orange)
- "Repairing" (green)
- "Ready for collection" (purple)

Every company's **Completed** stage has a built-in **Cancelled** status — this is what [cancelling a booking](12-bookings-stages.md) uses to keep cancelled jobs visible instead of deleting them.

---

## Creating a Status

1. Select the **stage** you want to add a status to
2. Click **Add Status**
3. Enter the **status name**
4. Choose a **colour** for the badge
5. Save

The new status immediately becomes available in the status dropdown on all bookings in that stage.

---

## Managing Statuses

- **Reorder** — drag and drop to change the order they appear in the dropdown
- **Edit** — change the name or colour
- **Delete** — remove a status (only if no bookings are currently using it)

---

## Using Statuses

Once created, you and your team can change a booking's status:

- **From the booking list:** Click the coloured status badge on any row -> select new status
- **From the booking detail page:** Click the status dropdown in the header

Changes take effect immediately and are recorded in the booking history.
