# InteBox SMS Bridge

Send SMS through your own InteBox hardware device instead of (or as well as) ClickSend.

**Settings -> InteBox SMS Bridge**, or navigate to `/admin/settings/sms-bridge`

---

## What Is InteBox?

InteBox is a physical device with a SIM card that InteTeam can supply. Once paired, it becomes a second SMS provider — an alternative to [ClickSend](62-sms.md) for shops that want to send texts through their own phone number/SIM rather than pay per-message API costs.

Only one provider is active at a time per company; switching between them is instant and doesn't require reconfiguring anything else — the Conversations page and booking-level "Send SMS" actions keep working the same way regardless of which provider is active.

---

## Pairing a Device

1. Go to **Settings -> InteBox SMS Bridge**
2. Under **InteBox Devices**, type a name for the device (e.g. "Workshop Box") and click **Add Device**
3. A **token** is shown once — copy it immediately, it cannot be retrieved again
4. Enter this token into the physical InteBox device
5. The device shows a **green Wi-Fi icon** once it's online and polling; a red icon means it's offline or hasn't been set up yet

If you lose a device's token, click the **refresh icon** next to it to regenerate — the old token stops working immediately, so the device will need the new one entered before it can send again.

---

## Switching the Active Provider

Under **Active SMS Provider**, choose:

- **ClickSend** — API-based, no hardware required
- **InteBox** — routes through your paired device (only selectable once at least one device is paired; otherwise it's greyed out with "pair a device first")

Messages sent while no device is paired and active would sit unsent, so pair and confirm the device shows online before switching.

---

## Dashboard Volume Chart

Toggle **Show SMS volume chart on dashboard** to display a daily InteBox SMS count on your dashboard. This helps you self-monitor against your SIM's fair-use/data limits — useful since InteBox typically runs on a standard consumer SIM plan rather than a business SMS API.

---

## Compliance: STOP-word Opt-Out

If a customer replies **STOP**, **UNSUBSCRIBE**, or similar to any text — through either ClickSend or InteBox — the CRM automatically blocks all future SMS to that number. This is enforced in the background for UK PECR compliance and cannot be overridden by staff. The customer receives one automatic confirmation that they've been opted out.

If a customer says they're not receiving texts anymore, this is the first thing to check.
