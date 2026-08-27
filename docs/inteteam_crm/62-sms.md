# SMS (ClickSend)

Send text messages to customers via ClickSend.

**Navigate to:** `/admin/sms`

ClickSend is one of two possible SMS providers — the other is your own [InteBox device](62b-intebox-sms.md), if you have one paired. Whichever is set as the **Active SMS Provider** in Settings handles all outbound texts; the Conversations and Send SMS screens work identically either way.

---

## Setup

1. Go to **Settings -> Messaging tab -> ClickSend**
2. Enter your ClickSend API username and key
3. Save

Once configured, the SMS page shows your **account balance** at the top.

---

## Sending a Single SMS

1. Go to the **Send SMS** tab
2. Enter:
   - **Phone number** — UK mobile numbers (07xxx) are automatically converted to international format (+44)
   - **Customer name** (optional)
   - **Booking reference** (optional)
   - **Message** — type your message. A counter shows characters used and SMS parts (160 chars per part)
3. Click **Send**
4. You'll see a success message with the message ID and cost

---

## Bulk SMS

1. Go to the **Bulk Send** tab
2. Enter phone numbers — one per line, or separated by commas/semicolons
3. Type your message
4. Click **Send to N Recipients**
5. A results table shows the status for each number

---

## Message History

1. Go to the **History** tab
2. Toggle between **Sent** and **Received** messages
3. Use the search box to find messages by phone number or content
4. Table shows: phone number, message, status, date, and cost (for sent messages)

---

## Sending from Bookings

You can also send SMS from a booking:

1. Open a booking detail page
2. Click the **three-dot menu**
3. Click **Send SMS**
4. If your company has any SMS templates set up, you'll first be asked to pick one or send **plain text** instead
5. Picking a template fills in known details automatically (customer name, phone, booking reference, company name, today's date) and asks you to fill in anything else the template needs
6. Preview the final message, then click **Send**

### SMS Templates

Create reusable, company-authored SMS templates so staff don't retype the same message:

1. Go to **Settings -> SMS Templates**
2. Click **New Template**, give it a name and body text
3. Use `{{placeholder}}` tokens in the body — `{{customer_name}}`, `{{customer_phone}}`, `{{booking_ref}}`, `{{company_name}}`, and `{{today_date}}` auto-fill when sent from a booking; any other `{{...}}` becomes a field staff fill in by hand
4. Save

There's no default wording built into the CRM — templates are entirely up to you.

---

## If a Customer Stops Receiving Texts

If a customer has ever replied **STOP** or **UNSUBSCRIBE** to any text from you, the CRM automatically and permanently blocks further SMS to that number — this is a legal (UK PECR) requirement and staff cannot override it. See [InteBox SMS Bridge](62b-intebox-sms.md#compliance-stop-word-opt-out) for details.
