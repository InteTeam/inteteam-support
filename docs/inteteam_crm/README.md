# InteTeam CRM — User Manual (Admin Role)

## Overview

User-facing documentation for the InteTeam CRM Admin role. Admins have full access to everything — this is the most comprehensive manual and the first one to write. Other role manuals (Technician, Warehouse) are subsets of this.

**Status:** 42 guides written (2026-08-26). Screenshots still needed.

---

## Table of Contents

### 1. Getting Started
- [01 - Getting Started](01-getting-started.md) — Login, dashboard, navigation
- [02 - Company Setup](02-company-setup.md) — Company details, email settings, theme
- [03 - Team Members](03-team-members.md) — Invite users, roles, disable/remove
- [04 - Two-Factor Authentication](04-two-factor.md) — 2FA setup, recovery codes

### 2. Bookings
- [10 - Bookings Overview](10-bookings-overview.md) — Stages, lists, searching
- [11 - Incoming Bookings](11-bookings-incoming.md) — Processing new bookings
- [12 - Moving Through Stages](12-bookings-stages.md) — Forward, back, cancel
- [13 - Booking Detail Page](13-booking-detail.md) — Tabs, actions, history
- [14 - Stage Statuses](14-booking-statuses.md) — Custom sub-statuses
- [15 - Task Templates](15-booking-todos.md) — Reusable checklists
- [16 - Booking Forms](16-booking-forms.md) — Website embed forms

### 3. Barcode & Labels
- [20 - Scanning Barcodes](20-barcode-scanning.md) — Camera + USB scanner
- [21 - Printing Labels](21-label-printing.md) — Print button on pages
- [22 - Printer Setup](22-printer-setup.md) — Stations, printers, drivers
- [23 - Print Bridge](23-print-bridge.md) — Windows/Linux installation

### Drop-Off Partners
- [24 - Drop-Off Staff Guide](24-drop-off-staff.md) — Receiving devices, scanning labels, collection, admin setup

### 4. Inventory
- [30 - Simple Products](30-simple-products.md) — Products for storefront
- [31 - Parts Stock](31-parts-stock.md) — Parts catalog, categories, specs
- [32 - Warehouse Inventory](32-warehouse-inventory.md) — SKU tracking, stock movements

### 5. Storefront
- [40 - Storefront Setup](40-storefront-setup.md) — 6-step tutorial
- [42 - Orders & Enquiries](42-storefront-orders.md) — Customer orders, enquiries

### 6. Scheduling
- [50 - Calendar & Appointments](50-scheduler.md) — Calendar views, visits
- [51 - Scheduler Settings](51-scheduler-settings.md) — Locations, hours, holidays

### 7. Communication
- [60 - Conversations](60-conversations.md) — Unified inbox (WhatsApp, SMS, Tickets)
- [61 - WhatsApp](61-whatsapp.md) — WhatsApp messaging
- [62 - SMS](62-sms.md) — ClickSend SMS
- [62b - InteBox SMS Bridge](62b-intebox-sms.md) — SMS via your own InteBox device
- [63 - Email](63-email.md) — Email notifications
- [64 - Support Tickets](64-support-tickets.md) — Customer portal support tickets
- [65 - Voice Calling](65-voice-calling.md) — Twilio phone calls

### 8. Invoicing & Payments
- [70 - Invoicing](70-invoicing.md) — Create, send, void, credit notes
- [96 - Payments](96-payments.md) — Transaction history, payment settings

### 9. Tasks
- [80 - Tasks](80-tasks.md) — Task management, statuses, categories

### 10. CMS
- [90 - CMS Pages](90-cms-pages.md) — Website pages
- [91 - CMS Forms](91-cms-forms.md) — Form builder
- [92 - Gallery](92-gallery.md) — Image galleries
- [93 - Updates](93-updates.md) — Business updates

### 11. Reports
- [95 - Reports](95-reports.md) — Financial reports, export

### 12. Integrations
- [81 - Company Webhooks](81-company-webhooks.md) — HTTP POST on task completion
- [82 - Google Reviews](82-google-reviews.md) — Connect your Google Business Profile

---

## Guide Structure

Each guide is a standalone markdown file. Each contains:

- **What it is** (1-2 sentences)
- **How to get there** (nav path or URL)
- **Step-by-step walkthrough** (screenshots to be added)
- **Common tasks** as numbered steps
- **Troubleshooting** (if applicable)

---

## Guides to Write — Admin Role

### Section 1: Getting Started (Priority: Critical)

| # | Guide | File | Content | Source Docs |
|---|-------|------|---------|-------------|
| 1.1 | Logging In & Dashboard | `01-getting-started.md` | Login, first-time setup, dashboard overview, navigation layout | `authentication/`, `dashboard_booking_chart/` |
| 1.2 | Company Setup | `02-company-setup.md` | Company details, branding, email settings — first things to configure | `admin-settings/` |
| 1.3 | Adding Team Members | `03-team-members.md` | Create users, assign roles (admin/team), permissions | `user_management/` |
| 1.4 | Two-Factor Authentication | `04-two-factor.md` | Enable 2FA, recovery codes, enforce for team | `two-factor-authentication/` |

### Section 2: Bookings — Core Workflow (Priority: Critical)

| # | Guide | File | Content | Source Docs |
|---|-------|------|---------|-------------|
| 2.1 | Bookings Overview | `10-bookings-overview.md` | What bookings are, the 3 stages (incoming/undergoing/completed), lifecycle | `booking-workflow/` |
| 2.2 | Incoming Bookings | `11-bookings-incoming.md` | Viewing list, searching, filtering by assignee, booking detail | `booking-workflow/` |
| 2.3 | Moving Bookings Through Stages | `12-bookings-stages.md` | Move to undergoing, move to completed, move back, cancel | `booking-workflow/` |
| 2.4 | Booking Detail Page | `13-booking-detail.md` | All sections: customer info, device, costs, history, actions, print label | `booking-costs/`, `label_printing/` |
| 2.5 | Customizing Stage Statuses | `14-booking-statuses.md` | Settings -> Stage Statuses, creating custom sub-statuses | `booking_task_templates/` |
| 2.6 | Booking Todo Templates | `15-booking-todos.md` | Create reusable task checklists for bookings | `booking_task_templates/` |
| 2.7 | Booking Forms | `16-booking-forms.md` | Custom intake forms for bookings (form builder) | `cms_form_builder/`, `form_booking_workflow/` |

### Section 3: Barcode & Label Printing (Priority: High)

| # | Guide | File | Content | Source Docs |
|---|-------|------|---------|-------------|
| 3.1 | Scanning Barcodes | `20-barcode-scanning.md` | Camera scanning (phone/desktop), physical USB scanner, what happens after scan | `barcode_scanning/` |
| 3.2 | Printing Labels | `21-label-printing.md` | Print button on bookings/parts/inventory/orders, copies, barcode types | `label_printing/` |
| 3.3 | Printer Setup | `22-printer-setup.md` | Add print station, add printer, choose driver (Zebra/Brother), label options | `label_printing/` |
| 3.4 | Print Bridge Installation | `23-print-bridge.md` | Windows .exe setup, Linux setup, troubleshooting | `inteteam-print-bridge/README.md` |

### Section 4: Inventory & Warehouse (Priority: High)

| # | Guide | File | Content | Source Docs |
|---|-------|------|---------|-------------|
| 4.1 | Simple Products | `30-simple-products.md` | `/admin/inventory/products` — add/edit products, adjust stock, toggle listing | `simple-inventory/` |
| 4.2 | Parts Stock Catalog | `31-parts-stock.md` | Parts, suppliers, stock levels, purchase receipts | `parts_stock/` |
| 4.3 | Warehouse Inventory | `32-warehouse-inventory.md` | SKU management, stock tracking, warehouse locations | `warehouse-inventory-refactor/` |

### Section 5: Storefront (Priority: Medium)

| # | Guide | File | Content | Source Docs |
|---|-------|------|---------|-------------|
| 5.1 | Storefront Setup | `40-storefront-setup.md` | Settings, tutorial page, enabling storefront | `storefront_settings/` |
| 5.2 | Managing Products | `41-storefront-products.md` | Product listing, pricing, images, categories | `simple-inventory/` |
| 5.3 | Orders & Enquiries | `42-storefront-orders.md` | Order list, order detail, print label, enquiries | `storefront_checkout/` |

### Section 6: Scheduling (Priority: Medium)

| # | Guide | File | Content | Source Docs |
|---|-------|------|---------|-------------|
| 6.1 | Calendar & Appointments | `50-scheduler.md` | Calendar view, creating visits, availability | `scheduler/`, `scheduler_enhancements/` |
| 6.2 | Scheduler Settings | `51-scheduler-settings.md` | Locations, business hours, holidays, API keys | `scheduler/` |

### Section 7: Communication (Priority: Medium)

| # | Guide | File | Content | Source Docs |
|---|-------|------|---------|-------------|
| 7.1 | Conversations (Unified) | `60-conversations.md` | Unified messenger, viewing all conversations | — |
| 7.2 | WhatsApp | `61-whatsapp.md` | Setup, sending messages, templates, conversations | `whatsapp/` |
| 7.3 | SMS (ClickSend) | `62-sms.md` | Setup, sending SMS, bulk SMS, conversations | — |
| 7.4 | Email Notifications | `63-email.md` | Email settings, notification types, Resend setup | `email-notifications/` |

### Section 8: Invoicing & Payments (Priority: Medium)

| # | Guide | File | Content | Source Docs |
|---|-------|------|---------|-------------|
| 8.1 | Creating Invoices | `70-invoicing.md` | Create from booking or standalone, line items, tax | `invoicing/` |
| 8.2 | Invoice PDF & Email | `71-invoice-pdf.md` | Generate PDF, email to customer | `invoicing/` |
| 8.3 | Void & Credit Notes | `72-void-credit.md` | Void invoice, issue credit note | `invoice_void_creditnote/` |
| 8.4 | Payment Settings | `73-payment-settings.md` | Stripe, PayPal, Worldpay, Dojo, manual payments | `stripe_payments/`, `paypal_payments/`, `dojo_payments/`, `worldpay_payment/`, `manual_payments/` |

### Section 9: Tasks (Priority: Medium)

| # | Guide | File | Content | Source Docs |
|---|-------|------|---------|-------------|
| 9.1 | Task Management | `80-tasks.md` | Create tasks, assign, statuses, categories | `task_management/` |

### Section 10: CMS (Priority: Low)

| # | Guide | File | Content | Source Docs |
|---|-------|------|---------|-------------|
| 10.1 | CMS Pages | `90-cms-pages.md` | Create/edit website pages | `cms_pages/` |
| 10.2 | CMS Forms | `91-cms-forms.md` | Form builder, submissions, scheduler integration | `cms_form_builder/`, `cms_scheduler_integration/` |
| 10.3 | Gallery | `92-gallery.md` | Create galleries, upload images, navigation | `gallery/`, `cms_gallery_navigation/` |
| 10.4 | Business Updates | `93-updates.md` | Create/publish updates | `cms/` |
| 10.5 | Footer & About | `94-footer-about.md` | Edit footer, about page content | — |

### Section 11: Reports & Analytics (Priority: Low)

| # | Guide | File | Content | Source Docs |
|---|-------|------|---------|-------------|
| 11.1 | Reports | `95-reports.md` | Financial reports, export data | `reports/` |
| 11.2 | Payments Overview | `96-payments.md` | Payment reports page | — |

### Section 12: Settings (Priority: Low — admin-only, one-time setup)

| # | Guide | File | Content | Source Docs |
|---|-------|------|---------|-------------|
| 12.1 | Company Details | `97-settings-company.md` | Name, address, branding, logo | `admin-settings/` |
| 12.2 | Theme & Appearance | `98-settings-theme.md` | Custom theme, dark mode, colour preferences | `custom-theme-preferences/`, `dark-theme/` |
| 12.3 | Notifications | `99-settings-notifications.md` | Push notifications, email preferences | `push-notifications/` |
| 12.4 | Company Switching | `100-company-switcher.md` | Multi-company access, switching between companies | `company_switcher/` |
| 12.5 | Script Embeds | `101-script-embeds.md` | Custom JS/HTML embeds | `script_embeds/` |

---

## Summary

| Section | Guides | Priority | Effort |
|---------|--------|----------|--------|
| Getting Started | 4 | Critical | Small — mostly settings screens |
| Bookings | 7 | Critical | Large — core of the app, many workflows |
| Barcode & Labels | 4 | High | Medium — already well-documented in dev docs |
| Inventory | 3 | High | Medium |
| Storefront | 3 | Medium | Medium |
| Scheduling | 2 | Medium | Small |
| Communication | 4 | Medium | Medium — WhatsApp/SMS setup is complex |
| Invoicing & Payments | 4 | Medium | Medium |
| Tasks | 1 | Medium | Small |
| CMS | 5 | Low | Medium |
| Reports | 2 | Low | Small |
| Settings | 5 | Low | Small — one-time config screens |
| **Total** | **44 guides** | | |

---

## Content Already Available

These feature docs in `inteteam_crm/docs/features/` contain user stories and acceptance criteria that translate directly into manual content:

| Feature Doc | Status | Usable for Manual |
|-------------|--------|-------------------|
| `barcode_scanning/` | Complete | Yes — full UX flow documented |
| `label_printing/` | Complete | Yes — pages, drivers, setup all documented |
| `booking-costs/` | Complete | Yes |
| `booking_task_templates/` | Complete | Yes |
| `cms_scheduler_integration/` | Complete | Yes |
| `invoice_void_creditnote/` | Complete | Yes |
| `storefront_checkout/` | Complete | Yes |
| `team-two-factor-authentication/` | Complete | Yes |

The remaining ~50 feature docs are in Planning status — they have architecture notes but may not reflect current implementation. For those, the actual page components are the source of truth.

---

## Approach

1. **Write Section 1 + 2 first** (Getting Started + Bookings) — this covers onboarding and the core workflow
2. **Then Section 3** (Barcode & Labels) — new features, high support demand
3. **Then Sections 4-9** in priority order
4. **Sections 10-12 last** — lower traffic, admin-only, one-time setup
5. **Screenshots:** Each guide needs screenshots. Capture from the live production app.
6. **Review cycle:** After each section, test with a new user — can they complete the tasks from the guide alone?
