# Simple Products

Manage your products for the online storefront. Add items with prices, stock levels, and photos.

**Business -> Storefront -> Products**, or navigate to `/admin/inventory/products`

---

## Product List

A table showing all your products:

| Column | What it shows |
|--------|-------------|
| **Product** | Product name |
| **Price** | Sell price in GBP |
| **Stock** | Current stock quantity |
| **Listed** | Whether the item is visible in your online store |
| **Actions** | Edit and delete buttons |

Use the **search box** at the top to find products by name.

---

## Adding a Product

1. Click **+ Add Product**
2. Fill in the form:

| Field | Required | Description |
|-------|----------|------------|
| **Name** | Yes | Product name |
| **Manufacturer / Artist** | No | e.g. a brand or artist name — also available as a field in the [Label Designer](21-label-printing.md#custom-label-layouts-grid-designer) and standard label content |
| **Category** | No | Choose from your category tree |
| **Price** | Yes | Sell price in GBP |
| **Initial Quantity** | Yes | Starting stock level |
| **Description** | No | Product description |
| **List for sale** | No | Toggle ON to make visible in online store |
| **Photo 1** | No | Primary image (JPEG, PNG, WEBP, or GIF, max 5MB) |
| **Photo 2** | No | Secondary image |

3. Click **Save**

---

## Editing a Product

1. Click the **Edit** button on any product row
2. Change any fields (same as the create form)
3. To change a photo: click **Replace** and upload a new one
4. To remove a photo: click **Remove** (shows "Will be removed on save" — click Undo if you change your mind)
5. Click **Save Changes**

---

## Custom Fields

Add extra key/value data to a product beyond the standard fields (e.g. "Colour", "Material", "Voltage").

1. On the Add/Edit Product form, find **Custom Fields (optional)**
2. Click **+ Add** to add a key/value row
3. Type a field name — a dropdown suggests names already used elsewhere in your company's product catalogue, so you can stay consistent
4. Fill in the value, or leave it blank to skip that field for this product
5. Click the **remove** button on a row to delete it

When editing a product, rows for any custom field already used elsewhere in your company appear automatically (blank, ready to fill in) alongside this product's existing values. Blank rows are dropped when you save — they don't create empty fields.

Use the **up/down arrow buttons** on each custom field row to reorder them. This order is shared company-wide and saves immediately — it changes the row order on every product's Add/Edit form, not just the one you're currently editing. A newly-used field name appends to the end of the list until you manually reposition it.

Custom fields are included as extra columns when you export products, and unmapped columns during import are saved as custom fields too (see [Importing Products](#importing-products)).

---

## Stock Adjustments

To adjust stock on a product:

1. Go to the product edit page
2. Change the quantity
3. Save

For more detailed stock tracking with movement history, use the **Warehouse Inventory** (see [Warehouse Inventory](32-warehouse-inventory.md)).

---

## Deleting a Product

1. Click the **Delete** button on the product row
2. Confirm the deletion

Deleted products are removed from your storefront immediately.

---

## Import & Export Help

Click the **?** button next to Export/Import in the page header for a quick guide on both features. It explains the two import approaches and what to expect with large files.

---

## Exporting Products

Click the **Export** button at the top of the products page to download a CSV file of all your products. The CSV includes: name, MPN, category, manufacturer, description, sell price, quantity, and storefront listing status.

Use the exported file as a template for bulk imports — add more rows in the same format and re-import.

---

## Importing Products

Click the **Import** button to open the import wizard.

### Option A — Use our template (recommended for first import)

1. Create one product manually using **Add Product**
2. Click **Export** to download a CSV with the correct format
3. Add more rows to the file using the same columns
4. Click **Import** and upload your file

### Option B — Use your own spreadsheet

1. Click **Import** and upload your CSV or Excel file (max 10MB)
2. Map your columns to our fields — the system auto-suggests mappings for common column names (e.g. "Title" → Name, "Selling Price" → Sell Price)
3. Columns you don't map are saved as extra product data (specs)
4. Review the preview, then confirm

### Column mapping rules

- **Name** and **Sell Price** must be mapped (required)
- Columns mapped to "Save as product spec" are stored as extra product data
- Columns mapped to "Skip" are ignored
- Set a **default quantity** for rows without a quantity column (defaults to 1)

### Before confirming

You'll see:
- A preview of the first 10 rows
- Count of new products, duplicates, and errors
- Any new categories that will be created automatically
- For duplicates: choose to **skip** them or **update** existing products

### Processing

- Small imports (under 50 products) process instantly
- Large imports (50+) run in the background — a progress bar shows completion. Give it some time and refresh the page.
- When done, you'll see a summary: how many were created, updated, skipped, and any errors

---

## Bulk Import Tips

When importing large numbers of products (hundreds or thousands), keep these in mind:

### Before you start

1. **Check which company you're logged into.** The import assigns all products to whichever company your account is currently switched to. If you manage multiple companies, go to your company switcher and confirm you're on the right one before uploading.
2. **Check for duplicate catalogue numbers (MPN).** The system enforces unique MPN per company. If your spreadsheet has the same catalogue number more than once (e.g. multiple copies of the same vinyl record at different prices), make each MPN unique by adding a suffix: `pcs7096`, `pcs7096-2`, `pcs7096-3`, etc.

### During import

- **Batch size:** The import handles large files (1000+ rows) in the background. You'll see a progress bar — let it finish before importing the next batch.
- **Re-importing the same file** is safe. The system detects duplicates by MPN and skips them (or updates, depending on your choice). This means you can use the same CSV multiple times — it will only create the new rows.

### After import

- **Verify the count.** Check the product list and make sure the total matches what you expected.
- **Storefront cache.** If your products don't appear on the storefront immediately, wait 5 minutes for the cache to refresh, or ask your admin to clear the application cache.
- **Categories.** If your CSV contains a category that doesn't exist yet, the import creates it automatically. Check your category list afterwards to make sure the names are correct.
