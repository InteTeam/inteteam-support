# Drop-Off Staff Guide

This guide is for staff working at drop-off partner locations (e.g. PetZone). You have a simplified view of the system — just what you need to receive and hand back customer devices.

---

## Logging In

1. Open the CRM URL in your browser
2. Log in with your email and password
3. You'll land on the **Drop-Off Dashboard**

You can add the app to your phone's home screen for quick access (it works as a PWA with camera scanning).

---

## Dashboard

Your dashboard shows:

| Section | What it shows |
|---------|--------------|
| **Expected today** | Number of customers expected to drop off devices |
| **Ready for collection** | Number of repaired items waiting for customers to pick up |
| **Scan & Receive** | Quick button to go to the receive page |

Below the stats, you'll see a list of today's expected customers with their name and booking reference. This list only shows customers with visits scheduled for today — not all incoming bookings.

---

## Receiving a Device (Scan & Receive)

When a customer arrives with a device, they should have a **confirmation email with a barcode**. This barcode identifies their booking without needing to ask for personal details (GDPR-friendly).

1. Go to **Receive** (from the navbar or dashboard button)
2. Ask the customer to show their confirmation email barcode
3. Scan the barcode — the system identifies their booking
4. Label the device — whichever applies:
   - **Your screen shows "Print booking label & receive"** (your drop-off point has a label printer): click it. The booking label prints and the device is received in one step — stick the label on and you're done (skip the steps below). If the label can't print, nothing is changed and a message explains why.
   - **The device already has its booking QR label:** scan that QR in the label box — it receives the booking. Scanning a *different* booking's QR is refused.
   - Otherwise, take a pre-printed label and stick it on the device
5. Scan the label using one of three methods:
   - Click the **barcode icon** next to the input field to open the camera scanner
   - Use a **USB barcode scanner** (keyboard-wedge) — it types the code and submits automatically when the input is focused
   - Type the label code manually (e.g. `PETZONE001/2026`)
4. Press **Link** — the system links the label to the booking and moves it to Undergoing
5. You'll see a green confirmation message
6. Your admin receives a notification, and if enabled, the customer receives a confirmation email

**Opening Receive from a scanned QR:** if you scan a booking QR label with your phone camera or the scanner icon, Receive opens with that customer's booking already filled in — you still confirm the name before continuing.

### If you make a mistake

Scroll down to **Unlink Label** at the bottom of the Receive page:

1. Type the label code you want to unlink
2. Click **Unlink**
3. The label is freed up for re-use

---

## Collection (Handing Back a Device)

When a customer comes to collect a repaired device:

1. Go to **Collection** from the navbar
2. Find the customer's booking in the list
3. Verify their identity using the name, email, and phone shown
4. Hand back the device

---

## What You Can See

- Customer name, email, and phone (for identity verification)
- Booking reference and device type
- Today's expected visits for your drop-off point only
- Completed items ready for collection

## Managing Your Opening Hours

You can adjust your own drop-off point's opening hours:

1. Go to **Hours** from the nav bar
2. Toggle each day on/off and set open/close times
3. Add break times if needed
4. Click **Save Changes**

Changes take effect immediately — customers won't be able to book slots outside your hours.

---

## Managing Your Holidays

Add closures so customers can't book when you're shut:

1. Go to **Holidays** from the nav bar
2. Click **Add Holiday**
3. Enter a name (e.g. "Bank Holiday Monday") and date
4. Toggle **Recurring annually** if it repeats every year
5. Click **Create**

Company-wide holidays set by the repair shop admin are shown but can't be edited by you.

---

## What You Cannot Access

- Admin settings, scheduler, invoicing
- Other drop-off points' data
- Pricing or payment information
- Editing booking details

---

## Theme

You can switch between light and dark mode from the user menu (click your name/avatar in the top right).

---

## Admin: Managing Drop-Off Schedules

Admins can set opening hours for each drop-off point:

1. Go to **Settings > Scheduler > Drop-Off Points**
2. Click the **clock icon** next to a drop-off point
3. Set opening hours for each day (same interface as location business hours)
4. Save

Drop-off staff can also manage their own hours and holidays from their dashboard (see above). These hours are used by booking forms linked to the drop-off point.

---

## Admin: Booking Forms for Drop-Off Points

Admins can create booking forms tied to a specific drop-off point:

1. Create or clone a booking form
2. Set the **drop-off point** instead of a location
3. Choose whether the scheduler shows **before** or **after** customer details
4. Embed on your website — customers book at the drop-off point's schedule

---

## Admin: Reassigning a Booking

If a customer needs to go to a different drop-off:

1. Open the booking detail page
2. Under **Drop-Off Point**, use the dropdown to select a new location
3. The booking is instantly reassigned

---

## Support

Click the chat icon in the bottom corner for AI-powered help, or contact your admin for assistance.
