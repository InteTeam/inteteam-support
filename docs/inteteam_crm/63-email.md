# Email Notifications

The CRM sends emails to customers for booking confirmations, invoices, and notifications. You can choose between two email providers: **Resend API** or **SMTP (Mailu)**.

---

## Email Provider Choice

Each company can use one of two email providers. Only one is active at a time.

| Provider | Best for | Setup |
|----------|----------|-------|
| **Resend API** | Quick setup, no server needed | Get API key from [resend.com](https://resend.com) |
| **SMTP / Mailu** | Self-hosted, auto-configured by InteTeam Panel | Credentials filled automatically after Mailu setup |

### How to switch provider

1. Go to **Settings -> Email**
2. Click the **Resend API** or **SMTP / Mailu** tab
3. Fill in the credentials for that provider (if not already configured)
4. Click **Save Settings**

The selected tab becomes your active provider. Your previous provider's credentials are kept — you can switch back at any time without re-entering them.

### Automatic SMTP Setup (via InteTeam Panel)

If your email was provisioned via the InteTeam Panel (Setup Mailu button), **your CRM email settings are configured automatically** — no manual entry needed. The Panel pushes SMTP credentials to the CRM via a secure webhook. You'll see the SMTP tab already active with all fields filled in.

This happens automatically when the Panel admin clicks "Setup Mailu" for your company.

### If SMTP stops working

1. Go to **Settings -> Email**
2. Click the **Resend API** tab
3. Click **Save Settings** — emails immediately go via Resend again

There is no automatic fallback between providers. If one fails, switch manually.

---

## Setup

### Resend API

1. Go to **Settings -> Email** and click the **Resend API** tab
2. Enter your **API Key** from [resend.com](https://resend.com)
3. Enter your **From Domain** (the domain verified in your Resend account)
4. Click **Save Settings**

Emails will be sent from `bookings@yourdomain.com`.

### SMTP / Mailu

1. Go to **Settings -> Email** and click the **SMTP / Mailu** tab
2. Enter your SMTP details:
   - **Host** (e.g. `mail2.inteteam.co.uk`)
   - **Port** (usually 587 for TLS)
   - **Username** (your email address)
   - **Password**
   - **Encryption** (TLS recommended)
   - **From Email** (the address emails are sent from)
   - **From Name** (your company name)
3. Click **Save Settings**

If your email was set up via the InteTeam Panel, these fields are filled in automatically — no manual configuration needed.

---

## What Gets Emailed

| Trigger | What's sent |
|---------|------------|
| **Booking confirmation** | Confirmation email with booking details (configurable content) |
| **Drop-off receipt** | Confirmation to customer when item received at a drop-off point (opt-in, custom message) |
| **Invoice sent** | Invoice PDF attached |
| **Password reset** | Reset link |
| **Team invitation** | Invitation link to join your company — sent via InteTeam's own system mailer automatically if you haven't configured Resend or SMTP yet, so new team members can always be invited even before email is set up |
| **Payment confirmation** | Confirmation after successful payment |
| **Storefront order** | Order confirmation with items and total |

---

## Customising Email Content

1. Go to **Settings -> Email**
2. Scroll down to **Email Template**
3. Edit the **Message Content** text area
4. Toggle which details to include (name, email, phone, date, location, services)
5. Click **Send Test Email** to preview (sends to your logged-in email)
6. Save

These template settings apply regardless of which provider is active.

---

## Sending a Templated Email from a Booking

Separate from the automatic booking-confirmation email above, staff can send a one-off email from any booking:

1. Open a booking detail page
2. Click the **three-dot menu**, then **Send Email**
3. If your company has any email templates set up, pick one or choose **plain text**
4. Picking a template fills in known details automatically (customer name, company name, date) and asks you to fill in anything else the template needs
5. Preview the rendered email, then click **Send**

### Email Templates (booking messages)

Create reusable, company-authored email templates for the "Send Email" action above — separate from the single confirmation template under "Customising Email Content":

1. Go to **Settings -> Email Templates**
2. Click **New Template**, give it a name, subject, and body
3. Use `{{placeholder}}` tokens in the body — customer/company/date details auto-fill when sent from a booking; any other `{{...}}` becomes a field staff fill in by hand
4. Save

There's no default wording built in — templates are entirely up to you, and the backend re-validates the rendered email before sending so a template can never leave a literal `{{...}}` in a customer's inbox.

---

## Drop-Off Receipt Email

If you use drop-off points, you can send customers a confirmation email when their item is received.

1. Go to **Settings -> Email**
2. Scroll down to **Drop-Off Notifications**
3. Tick **Send confirmation email to customer on drop-off**
4. Write your custom message (e.g. "We confirm your booking", "Your item is with us")
5. Save

The email automatically includes your company name, the booking reference, and the drop-off location. Click the **?** icon next to the section title for more details.

This is off by default — enable it only if you want customers to receive this email.

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Test email not arriving | Check spam folder. Verify API key (Resend) or SMTP credentials are correct |
| "Please configure an email provider" | No credentials saved. Enter Resend API key or SMTP details and save |
| SMTP connection timeout | Check host and port are correct. Port 587 + TLS is most common |
| Emails going to spam | Check your domain has SPF, DKIM, and DMARC records configured |
