# Company Webhooks

Fire an HTTP POST to an external system whenever a specific task is completed on a booking.

**Settings -> Webhooks**, or navigate to `/admin/settings/webhooks`

---

## What It's For

Webhooks let you connect the CRM to other tools — for example, notifying a Slack channel, a Zapier flow, or another internal system when a particular checklist task (like "Diagnosis complete" or "Ready for collection") is ticked off on a booking.

Each webhook is tied to one **task template** — it fires when a task using that template is marked complete on any booking.

---

## Creating a Webhook

1. Click **Add**
2. Fill in:
   - **Name** — a label for your own reference (e.g. "Notify Panel on deploy")
   - **Trigger Task** — the task template that fires this webhook when completed
   - **URL** — the endpoint that receives the POST
   - **Secret** — used to sign the payload (min. 16 characters); click **Generate** for a random one
3. Toggle **Active** on
4. Save

## Managing Webhooks

Each webhook card shows its most recent delivery — a green check with the HTTP status and how long ago, or a red cross if the last delivery failed.

- **Toggle** the switch on a webhook to enable/disable it without deleting it
- **Edit** (pencil icon) to change the URL, trigger task, or secret (leave the secret field blank to keep the current one)
- **Delete** (trash icon) to remove it permanently, including its delivery history

---

## Verifying the Signature

Every request is signed with your webhook's secret so the receiving system can confirm it genuinely came from your CRM. Check with whoever built the receiving endpoint if you're not sure how the signature is validated.
