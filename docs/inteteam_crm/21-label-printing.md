# Printing Labels

Print barcode labels directly from the CRM. Labels can be stuck on devices, shelves, bins, or packaging for quick scanning later.

---

## Where to Print From

The **Print Label** button appears on these pages:

| Page | What's printed on the label |
|------|---------------------------|
| **Booking detail** (`/admin/bookings/{id}`) | Booking reference (e.g. INT-001/03-2026) |
| **Storefront order** (`/admin/storefront/orders/{id}`) | Order reference |
| **Part detail** (`/inventory/parts-stock/parts/{id}`) | Part MPN (manufacturer part number) |
| **Inventory item** (`/warehouse/inventory/{id}`) | Item SKU |

---

## How to Print

If your company has **no label presets** configured, printing is still one click:

1. Open the booking, order, part, or inventory item page
2. Click the **Print Label** button (printer icon)
3. The CRM creates a print job and sends it to your default printer

If your company **has label presets** set up (see [Printer Setup](22-printer-setup.md)), clicking Print Label opens a **print dialog** instead:

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

### Choosing Content Fields

1. Go to **Settings -> Printing -> Labels tab**
2. In the **Label Content** card, toggle up to 4 fields on (the limit scales with your label preset's size — larger presets allow more)
3. Use the **up/down arrow buttons** on each row to reorder — the top enabled field prints closest to the barcode
4. Click **Save Label Content**

These field/style settings apply to all presets unless a specific preset has its own field configuration set separately (see [Printer Setup](22-printer-setup.md) for managing presets). Batch custom text (set during batch creation) still appears in addition to these fields.

---

## Requirements

Label printing requires:

1. A **print station** configured in your CRM settings (see [Printer Setup](22-printer-setup.md))
2. A **printer** added to that station (Zebra, Brother, or network printer)
3. The **Print Bridge** running on the computer connected to the printer (see [Print Bridge Installation](23-print-bridge.md))

If you don't have printing set up yet, clicking the Print Label button will show an error. Ask your admin to configure it first.
