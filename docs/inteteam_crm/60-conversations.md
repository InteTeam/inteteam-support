# Conversations

A unified inbox for all customer messaging — WhatsApp, SMS, and customer portal support tickets in one place.

**Conversations** in the top navigation, or navigate to `/admin/conversations`

---

## What You See

A two-panel layout:

- **Left panel** — your conversation list with customer names, last message preview, and unread badges
- **Right panel** — the selected conversation's full message thread

On mobile, you see the list first. Tap a conversation to open the message thread, then use the back button to return to the list.

---

## Filtering Conversations

At the top of the conversation list:

- **Channel tabs** — filter by All, WhatsApp, SMS, or Tickets
- **Status tabs** — switch between Active and Archived conversations (Tickets use Open/Closed instead — see [Support Tickets](64-support-tickets.md))
- **Search** — search by customer name or phone number

---

## Starting a New Conversation

Click **New WA** (WhatsApp) or **New SMS** at the top right to start a new conversation. You'll need the customer's phone number.

---

## Requirements

- **WhatsApp:** WhatsApp Business API must be configured in Settings (see [WhatsApp Setup](61-whatsapp.md))
- **SMS:** ClickSend or an [InteBox device](62b-intebox-sms.md) must be configured in Settings (see [SMS Setup](62-sms.md))
- **Tickets:** the customer portal must be enabled — tickets are always started by the customer, not staff (see [Support Tickets](64-support-tickets.md))

If none of these are configured, the Conversations page will be empty. Set up at least one channel first.
