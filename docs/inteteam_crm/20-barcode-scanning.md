# Scanning Barcodes

Scan a barcode label on a device or item and jump straight to its page in the CRM. Works with phone cameras and physical USB/Bluetooth scanners.

---

## Camera Scanning (Phone or Desktop)

1. Tap the **barcode scanner icon** in the top-right of the navigation bar (it's always visible, on every page)
2. A camera dialog opens
3. Point your camera at the barcode label
4. The system reads the barcode and searches for a match
5. **If found:** you're taken directly to the booking, order, part, or inventory item page
6. **If not found:** a "Not found" message appears — you can scan again

### Tips

- On phones, the **rear camera** is used automatically
- On desktops/laptops, any available camera works
- The app needs **camera permission** — your browser will ask the first time
- Works in the **PWA** (installed app) on both Android and iOS
- Supported barcode types: CODE_128, EAN_13, CODE_39 and **QR codes** (booking QR labels)

---

## Physical USB/Bluetooth Scanner

If you have a USB barcode scanner plugged into your computer (or paired via Bluetooth):

1. Make sure the CRM is open in your browser
2. **Don't click anything** — just scan the barcode with the physical scanner
3. The scanner types the barcode value and presses Enter automatically
4. The CRM detects this and looks up the barcode
5. You're taken to the matching page

This works on **any page** in the CRM — you don't need to open a scanner dialog first.

### How It Works

Physical barcode scanners act as keyboards — they "type" the barcode characters very fast and press Enter. The CRM listens for this pattern and triggers a lookup automatically.

**Note:** The scanner won't trigger if you're typing in a text field (search box, form input, etc.). Click somewhere outside of text fields first.

---

## What Gets Matched

When you scan a barcode, the system searches in this order (first match wins):

| Priority | What it checks | Where it takes you |
|----------|---------------|-------------------|
| 0 | Booking QR label (a link to this CRM) | Booking detail page |
| 1 | Booking reference | Booking detail page |
| 2 | Booking temp reference | Booking detail page |
| 3 | Storefront order reference | Order detail page |
| 4 | Part MPN | Part detail page |
| 5 | Inventory item SKU | Inventory item page |

If the barcode doesn't match any of these, you'll see a "No matching item found" message. QR codes that link to **other websites** never match anything. A booking QR label from a different company is treated as "not found".

### Scanning a booking QR label with your phone's own camera

Booking QR labels contain a link, so your phone's normal camera app can open them without opening the CRM first. You'll be asked to log in if you aren't, then taken to the right place for your role:

- **Admins / staff:** the booking page
- **Technicians:** the booking, if it's assigned to you — otherwise a "Not assigned to you" page
- **Drop-off staff:** the Receive page with that customer's booking ready to confirm — or a "Not waiting at your drop-off point" page if it isn't an incoming booking at your point
- **If the booking belongs to another company you're a member of:** a page asking whether to switch to that company (the CRM never switches company without asking)

---

## Team / Technician Scanning

Team members (technicians) can scan barcodes using the same camera and physical scanner methods. The behaviour differs based on assignment:

- **Assigned to the booking:** You're taken to the booking page with a **Mark as Received** button. Tapping it logs a receipt confirmation in the audit trail (no status change — your shop's custom statuses are unaffected).
- **Not assigned:** A message appears: *"You're not assigned to this booking. Contact your team lead if you think that's a mistake."*
- **Non-booking items** (orders, parts, inventory): Team members see *"You don't have access to this item."*

**Picking up from a drop-off point (Pickup page):** scan either the **pre-printed label** or the **booking QR label** on the device. The booking must be assigned to you and in the Undergoing stage; a booking QR only works for bookings that were handed in at a drop-off point.

### Mark as Received

When a technician scans a booking they're assigned to and taps **Mark as Received**, the system:

1. Creates an audit log entry: *"Received by [Name]"*
2. Shows a success toast confirmation
3. Does **not** change the booking stage or status

This gives shop owners a clear record of when technicians acknowledged each job, without interfering with custom workflows.

---

## Scan to Copy (Serial Numbers, IMEI, etc.)

A separate **clipboard icon** button sits next to the barcode scanner icon in the navigation bar. Use it to scan any barcode and copy the value to your clipboard — no database lookup, just a quick capture.

### When to Use

- Scanning a **serial number** off a device (laptop, PS5, phone, etc.)
- Capturing an **IMEI**, model number, or manufacturer barcode
- Any time you need to read a barcode and paste the value into a form field

### How to Use

1. Tap the **clipboard icon** in the top-right navigation bar
2. Point your camera at the barcode
3. The scanned value appears on screen and is **automatically copied to your clipboard**
4. Tap **Copy** again if needed, or **Scan Again** for another barcode
5. Paste the value wherever you need it (serial number field, notes, etc.)

Available for both **Admin** and **Team** roles.

### Can't Scan? Read Text Instead

If the barcode is too small, damaged, or the serial number is printed as plain text (no barcode), tap **"Can't scan? Read text instead"** while the camera is open.

The system captures the image and uses text recognition (OCR) to read printed text. Detected lines appear as tappable buttons — tap any line to copy it to your clipboard.

Works well for serial numbers, IMEI, model numbers on devices like PlayStation, laptops, phones, etc.
