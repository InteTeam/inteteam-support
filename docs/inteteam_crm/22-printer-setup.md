# Printer Setup

Configure your label printers in the CRM so staff can print barcodes.

**Settings -> Hardware tab -> Printing**, or navigate to `/admin/settings/printing`

The Printing settings page has four tabs: **My Desk** (only shown if you have a station assigned to you), **Stations & Printers**, **Labels**, and **Calibration**.

---

## Step 1: Add a Print Station

A station represents one location where a printer is connected (e.g. "Main Counter", "Back Room").

1. Go to **Settings -> Hardware -> Printing -> Stations & Printers**
2. Enter a name (e.g. "D&J Records - Main Counter")
3. Optionally, **assign the station to a specific team member** using the dropdown next to the name field — this makes it show up under that person's **My Desk** tab. Leave as "Unassigned (shared)" for a station multiple people use.
4. Click **Add Station**
5. A **token** is shown (starts with `st_`) in a modal — **copy it before closing the modal**, it's shown only once and cannot be retrieved later

This token is used by the Print Bridge software to authenticate. Keep it safe. If it's lost, click the **refresh icon** on the station card to regenerate it — the old token stops working immediately.

The station will show a **red Wi-Fi icon** (offline) until the Print Bridge connects, and a **green icon** once connected. The "Last seen" timestamp shows when it last checked in.

---

## Step 2: Add a Printer

1. On your station, click **Add Printer**
2. Fill in the details:

| Field | What to enter |
|-------|-------------|
| **Name** | A friendly name (e.g. "Zebra ZD230", "Brother P900W") |
| **Driver** | See table below |
| **Connection** | `lan` (network) or `usb` (direct USB cable) |
| **Address** | Printer's IP and port (e.g. `192.168.1.100:9100`), USB path (Linux: `/dev/usb/lp0`), or Windows printer share name (e.g. `ZDesigner ZD230-203dpi ZPL`) |
| **Media Type** | Only shown if you have **no label presets** set up — the raw label size code (e.g. `62` or `50x25`). Once you create a preset, dimensions come from the preset instead and this field disappears. |
| **Margin (mm)** | Unprintable edge margin, default 4mm. Increase this if labels are printing shifted or if text is getting clipped near the edges. |

To set a printer as the **default** for its station, click the **star icon** next to it in the printer list (not a toggle in the add/edit form).

### Choosing a Driver

| Driver | Use for | Notes |
|--------|---------|-------|
| **zebra_zpl** | Zebra printers (ZD230, ZD420, etc.) | Generates ZPL commands. Best for Zebra. Works via LAN or USB (Windows shared printer). |
| **zing_raster** | Zing / RONGTA thermal label printers | Converts images to ZPL raster graphics. Works via USB or LAN. |
| **brother_ql** | Brother P-touch (P900W, P750W) | Direct raster protocol. Works via USB or LAN. |
| **cups** | Any printer configured in CUPS (Linux) | Recommended for Brother on Linux — most reliable. |
| **raw_tcp** | Any network printer on port 9100 | Generic. Sends data as-is. |

### Finding Your Printer Address

**Network printers (LAN):**
- **Zebra:** Check the printer's display menu, or print a network config label
- **Brother:** Press and hold the Wi-Fi button to print network info
- **Router:** Check your router's DHCP client list for the printer's name

**USB printers on Windows:**
- Open PowerShell and run: `Get-Printer | Select-Object Name`
- Use the exact name shown (e.g. `ZDesigner ZD230-203dpi ZPL`)
- The printer must be **shared** — go to Printer properties > Sharing > tick "Share this printer"

**USB printers on Linux:**
- Run: `lpstat -p` to see CUPS printer names
- Or check USB device path: `ls /dev/usb/lp*`

3. Click **Save**

---

## Step 3: Install the Print Bridge

The Print Bridge is a small program that runs on the computer connected to the printer. It polls the CRM for print jobs and sends them to the printer.

See [Print Bridge Installation](23-print-bridge.md) for setup instructions.

---

## Step 4: Verify

1. The station should show a **green Wi-Fi icon** (online) once the bridge is running
2. Go to any booking page and click **Print Label**
3. Click **Job History** (top of the Printing settings page) to see the job status
4. Your printer should print the label

---

## Monitoring

### Station Status

- **Green Wi-Fi icon** — bridge is connected and polling
- **Red Wi-Fi icon** — bridge is offline (not running or can't reach CRM)
- **Last seen** timestamp shows when the bridge last checked in

### Job History

Click **View Jobs** to see all print jobs:

- **Pending** — waiting for bridge to pick up
- **Sent** — bridge received it, sending to printer
- **Completed** — printed successfully
- **Failed** — something went wrong (error message shown)
- **Cancelled** — cancelled by admin

You can cancel pending jobs from this page.

---

## My Desk & Default Printers

Every team member can have their **own default printer**, so print jobs go to the printer at their own desk rather than a shared company default.

- **Settings -> Printing -> Stations & Printers -> Default Printers card** — an admin can set any team member's default printer from a dropdown, or leave it as "Company default"
- **My Desk tab** — only appears for you if a station is assigned to you specifically. It shows just your own station(s) so you're not scrolling through every printer in the shop.
- When you add a new printer, a prompt asks **"Set as your default printer?"** — useful the first time you set up your own desk

### Per-Station Default

Within a station that has multiple printers, click the **star icon** next to a printer to make it that station's default (shown with a filled star badge).

---

## Label Presets & Sizes

If you print on more than one label size (e.g. small 50x25mm stock labels and larger 76x51mm product labels), set up **presets** instead of editing printer settings every time you switch rolls.

**Settings -> Printing -> Labels tab -> Label Sizes card**

1. Click **Add Preset**
2. Enter a **name** (e.g. "Large 76x51"), **width** and **height** in mm
3. Optionally set a **Max rows override** if you want to force a specific number of content lines instead of the auto-calculated limit — only do this if you understand the label won't auto-adjust to fit
4. Toggle **Set as default** if this should be the pre-selected preset when printing
5. Click **Create Preset**

Once you have at least one preset, every Print Label button switches from one-click printing to the [print dialog](21-label-printing.md) with a preset picker. You can't delete your last remaining preset — at least one must always exist.

Each preset can also be given its own content fields, separate from your company-wide Label Content settings — this shows as "Custom fields (N enabled)" on the preset card instead of "Using company defaults".

---

## ZPL Calibration

For Zebra printers, fine-tune how text and barcodes fit on the label if the defaults don't look right.

**Settings -> Printing -> Calibration tab**

1. Select the **Label Size** (preset) you want to calibrate
2. Adjust:
   - **Font Size** — ZPL text height in dots (18 small, 30 medium, 40 large — leave blank for auto)
   - **Barcode Height** — in dots; lower values leave more room for text
   - **Characters per line** — override if text is getting cut off or leaving too much blank space
3. Click **Print Test Label** to send a real test print without needing to open a booking
4. Click **Save Calibration**

Each field shows the auto-calculated default next to it, so you know what you're overriding and by how much.

**Doesn't apply to Grid Designer presets.** If the selected preset uses a [Custom Label Layout](21-label-printing.md#custom-label-layouts-grid-designer), the Font Size / Barcode Height / Characters-per-line fields above are inert — the Calibration tab shows a warning and disables them. Grid layouts set font size per cell instead: open the preset's **Design Layout** screen, select the cell, and adjust its font size there.

---

## Windows USB Quick Setup Checklist

For setting up a Zebra USB printer on a new Windows laptop:

1. [ ] Plug in the Zebra printer via USB — Windows should auto-install the driver
2. [ ] Open PowerShell, run `Get-Printer` to confirm the printer name
3. [ ] Right-click the printer in Settings > Printers > Properties > Sharing > **Share this printer**
4. [ ] In CRM: create a **new station** (Settings > Hardware > Printing > Add Station)
5. [ ] Copy the station **token** (shown once)
6. [ ] Add a **printer** to the station: Driver = Zebra ZPL, Connection = USB, Address = the exact printer name from step 2
7. [ ] Copy `inteteam-print-bridge.exe` to the laptop (e.g. `C:\PrintBridge\`)
8. [ ] Test from PowerShell:
   ```powershell
   .\inteteam-print-bridge.exe start --api-url https://crm.bookrepaironline.co.uk --token st_YOUR_TOKEN --interval 10000
   ```
9. [ ] Send a test print from the CRM — label should print
10. [ ] Set up **auto-start** (see below)

### Auto-Start on Windows Boot

So the bridge runs automatically when the laptop turns on:

> **Important:** Do NOT put the full command in a Windows shortcut target — it has a 260-character limit and will silently fail. Use a `.bat` file instead.

1. Create a file called `start-bridge.bat` in the bridge folder (e.g. `C:\inteteam_crm_print_bridge\start-bridge.bat`) with this content:
   ```bat
   @echo off
   cd /d "C:\inteteam_crm_print_bridge"
   inteteam-print-bridge.exe start --api-url https://crm.bookrepaironline.co.uk --token st_YOUR_TOKEN --interval 20000
   ```
   **The command must be on a single line** — do not split it across multiple lines.

2. Press `Win + R`, type `shell:startup`, press Enter
3. Copy `start-bridge.bat` into the Startup folder

That's it — Windows runs everything in the Startup folder on login.

### Verify Auto-Start

After reboot:
- The CRM station should show a **green dot** (online)
- Send a test print — it should work without manually starting anything
- If the green dot doesn't appear, open the `.bat` file manually to check for errors
