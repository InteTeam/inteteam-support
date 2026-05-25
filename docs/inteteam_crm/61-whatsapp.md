# WhatsApp

Send and receive WhatsApp messages to customers directly from the CRM.

**Navigate to:** `/admin/whatsapp`

---

## Setup

WhatsApp requires Meta Business API integration. To configure:

1. Go to **Settings -> Messaging tab -> WhatsApp**
2. Enter your API credentials
3. Save

Once saved, the WhatsApp options appear on booking pages immediately — you do not need to run the "Test Connection" step for the buttons to become visible.

---

## Using WhatsApp

The WhatsApp page shows a chat interface:

**Left panel — Conversation list:**
- Customer phone number and name
- Last message preview
- Unread message count
- Window status (WhatsApp has a 24-hour messaging window)
- Filter tabs: **Active** and **Archived** (with counts)

**Right panel — Messages:**
- Full conversation history
- Messages can be: text, image, audio, video, document, or template
- Each message shows status (sent, delivered, read)
- Type your reply at the bottom

---

## WhatsApp from Bookings

### View an existing conversation

If a WhatsApp conversation is already linked to a booking, open it directly:

1. Open a booking detail page
2. Click the **three-dot menu**
3. Click **View WhatsApp Chat**

This takes you straight to that customer's conversation thread.

### Send a new template message

To initiate a new WhatsApp conversation from a booking:

1. Open a booking detail page
2. Click the **three-dot menu**
3. Click **Send WhatsApp**
4. Select an approved template
5. Fill in the variables and send

This requires the customer to have a phone number on file. After sending, you are taken directly to the conversation.

**Auto-fill:** When you select a template, the CRM tries to fill in the variables automatically based on the template text. For example, if the template says "Your booking {{1}} is ready…", the booking reference is filled in for you. A row of suggestion chips also appears under each variable field — click any chip to paste the booking reference, customer name, or phone number with one click.

### From the bookings list

The **Send WhatsApp** option also appears in the three-dot menu on each row of the Incoming and Undergoing booking lists. It requires the customer to have a phone number on file.
