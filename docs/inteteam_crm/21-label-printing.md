# Printing Labels

Print barcode labels directly from the CRM. Labels can be stuck on devices, shelves, bins, or packaging for quick scanning later.

---

## Where to Print From

The **Print Label** button appears on these pages:

| Page | What's printed on the label |
|------|---------------------------|
| **Booking detail** (`/admin/bookings/{id}`) — **Label** button | A **booking label**: QR code or barcode plus the booking reference (e.g. `1234/09-2026`). Has its own settings — see [Booking Labels](#booking-labels-qr-code-or-barcode) |
| **Storefront order** (`/admin/storefront/orders/{id}`) | Order reference |
| **Part detail** (`/inventory/parts-stock/parts/{id}`) | Part MPN (manufacturer part number) |
| **Inventory item** (`/warehouse/inventory/{id}`) | Item SKU |

---

## How to Print

If your company has **no label presets** configured, printing is still one click:

1. Open the booking, order, part, or inventory item page
2. Click the **Print Label** button (printer icon)
3. The CRM creates a print job and sends it to your default printer

**Booking labels are different:** the **Label** button on a booking prints straight away (no dialog) using the booking label settings described below.

For orders, parts and inventory items: if your company **has label presets** set up (see [Printer Setup](22-printer-setup.md)), clicking Print Label opens a **print dialog** instead:

1. Click the **Print Label** button
2. If you have more than one preset, choose the **Label Preset** from the dropdown — its dimensions (e.g. 76x51mm) are shown next to the name
3. Review the **label lines** — these are pre-filled automatically from the item's data (name, price, manufacturer, etc. depending on what fields the preset shows). Edit any line or type into an empty one before printing
4. Set the number of **copies**
5. Click **Print**

**Switching preset mid-print:** if you change the preset from the one you last used, a confirmation dialog asks "Have you loaded the correct label roll?" before applying the change — this exists to stop you accidentally printing a large label's worth of text onto a small roll (or vice versa). Your most recently used preset per company is remembered in your browser and pre-selected next time.

### Printing Multiple Copies

Set the number of copies directly in the print dialog (1-100). If you don't see a dialog (no presets configured), the default is 1 copy — ask your admin to set up a label preset if you need to choose copies per print.

---

## Batch Printing (Label Batches)

Pre-print barcode labels in bulk for drop-off points and other uses.

**Settings -> Hardware tab -> Label Batches**, or navigate to `/admin/settings/label-batches`

### Creating a Batch

1. Click **Create Batch**
2. Enter a **prefix** (e.g. PETZONE), **range** (1–100), and **year**
3. Optionally add up to 3 lines of **custom text** (max 40 characters per line) — this appears below the barcode on every label in the batch
4. Click **Create Batch**

### Printing a Batch

1. Find the batch in the list and click **Print**
2. A confirmation dialog shows the batch name, label count, and estimated print time
3. Click **Start Printing** to begin

Labels print one at a time with short pauses between each. Larger batches take longer.

### Filtering Batches

Use the **search box** to filter by prefix, or the **status dropdown** to filter by created/printed/printing/failed.

---

## What's on the Label

- A **barcode** (CODE_128 format by default) encoding the reference/SKU/MPN
- Up to **4 content fields** of your choice — barcode text, company name, product name, manufacturer/artist, category, price, or description (plus any custom "spec" fields from your product data)
- Optionally, up to **3 lines of custom text** (set per batch when creating label batches)

### Label Style

In **Settings -> Printing -> Labels tab**, choose a style:

- **Barcode label** — the barcode graphic plus your chosen fields. Choose whether the barcode sits at the **Top** or **Bottom** of the label.
- **Text only** — just the fields, no barcode graphic at all (useful for small labels where a scannable barcode isn't needed)

Next to the style choice, set **Text alignment** to **Left** or **Center** — this controls how all label text (fields and custom text) lines up on the label.

If a line of text is too long to fit the label width, it now wraps onto an extra line instead of being cut off.

### Choosing Content Fields

1. Go to **Settings -> Printing -> Labels tab**
2. In the **Label Content** card, toggle up to 4 fields on (the limit scales with your label preset's size — larger presets allow more)
3. Use the **up/down arrow buttons** on each row to reorder — the top enabled field prints closest to the barcode
4. Click **Save Label Content**

These field/style settings apply to all presets unless a specific preset has its own field configuration set separately (see [Printer Setup](22-printer-setup.md) for managing presets). Batch custom text (set during batch creation) still appears in addition to these fields.

---

## Booking Labels (QR Code or Barcode)

The label printed from a booking's page (the **Label** button) is stuck on the customer's device. It has **its own settings**, separate from product labels and label presets — changing one never changes the other.

**Where:** Settings -> Printing -> **Labels** tab -> **Booking Labels** card (admins only).

| Setting | Options |
|---|---|
| **Code** | **QR code** (scans with any phone camera — recommended) or **Barcode** (for shops using 1D barcode scanners) |
| **Label size** | **Printer's roll** (whatever roll the printer has loaded — each printer can differ, e.g. at drop-off points), **Same as label preset** (your default preset's size and calibration), or **Fixed size** (width × height in mm) |
| **Text size** | **Automatic** (the booking number as large as the label allows) or a fixed size (8–36 pt) |
| **Print the booking date** | On/off |

A **live preview** shows exactly what will print (sample booking number, today's date), and **Test print** sends one to your default printer before you save.

**Layout:** QR on the left, booking number on the right. Long references are split over lines rather than shrunk to nothing — e.g. `EDN-1234/09-2026` prints as `EDN-` / `1234/` / `09-2026`, with the number itself largest. The reference is **never cut short**.

**Defaults:** companies created after this feature start on **QR code, printer's roll**. Companies that already existed kept their previous barcode label until an admin changes the setting.

**"Doesn't fit" messages:** saving is refused if the booking number wouldn't fit at the chosen text size on any of your active printers — the message says the largest size that fits (e.g. *"The largest size that fits on Front desk Zebra (50×25 mm) is 22 pt."*). When printing, a label that can't be printed legibly is refused with a message instead of printing a cut-off or unscannable label. Common fixes: switch Text size to Automatic, use QR instead of Barcode (a barcode can't be split over lines, so long references with a location prefix don't fit on a 50 mm label as a barcode), or use a larger roll.

**Who can print a booking label:** admins/staff for any booking; technicians only for bookings assigned to them; drop-off staff only for bookings at their own drop-off point.

**What the QR contains:** a link to the booking in the CRM (no customer details). Scanning it opens the booking for whoever scans it, according to their role — see [Scanning Barcodes](20-barcode-scanning.md).

---

## Custom Label Layouts (Grid Designer)

For more control than the standard 4-field layout, each label preset can switch to a **custom grid layout** you design visually — useful for labels that need fields arranged side-by-side rather than stacked, or a mix of fixed text ("SALE", a brand name) alongside product data.

### Designing a Layout

1. Go to **Settings -> Printing -> Labels tab**
2. Find the preset you want to customise and click the **grid icon** ("Design Layout") next to it
3. Choose how many **rows** the label has, then how many **columns** each row has — rows can have different column counts (e.g. one wide row on top, two narrow columns below)
4. On the canvas, **drag a field** from the field list at the top into any cell, **type text directly** into a cell for fixed wording, or drag the **Barcode** item in to place a real, resizable barcode graphic in that cell (drag its handle to set width/height — separate from any text field showing the barcode number)
5. For each text cell, set its **font size** and **text alignment** (left/center/right). A live hint under each cell estimates how many characters will fit at that size — if your text is longer, shrink the font or make the cell bigger (fewer columns/more rows)
6. Click **Save Layout**

Once saved, that preset always prints using this custom grid — the standard 4-field list is no longer used for it.

**If this is your company's default preset:** saving a layout change asks you to confirm first, because it immediately changes what every automated and batch print produces — not just labels printed from the designer screen. (Booking labels are not affected — they have their own settings.)

**Switching back:** on the designer screen, click **Use simple list instead** to discard the custom grid and return the preset to the standard field-list style. This cannot be undone — you'd need to redesign the grid from scratch if you change your mind.

A field bound to a cell that doesn't apply to a particular item (e.g. a "spec" field a product doesn't have) simply prints blank in that cell — it never blocks or breaks the print.

---

## Requirements

Label printing requires:

1. A **print station** configured in your CRM settings (see [Printer Setup](22-printer-setup.md))
2. A **printer** added to that station (Zebra, Brother, or network printer)
3. The **Print Bridge** running on the computer connected to the printer (see [Print Bridge Installation](23-print-bridge.md))

If you don't have printing set up yet, clicking the Print Label button will show an error. Ask your admin to configure it first.
