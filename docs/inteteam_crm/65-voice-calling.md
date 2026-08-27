# Voice Calling (Twilio)

Take and make phone calls from inside the CRM using your business phone number.

**Settings -> Telephony**, or navigate to `/admin/settings/telephony`

---

## Setup

Voice calling runs on your own Twilio account.

1. Go to **Settings -> Telephony**
2. Enter your Twilio **Account SID**, **Auth Token**, **API Key SID**, **API Key Secret**, **TwiML App SID**, and business **phone number**
   - The API Key/Secret is a separate, scoped credential used only to mint browser call tokens — never enter your main Account Auth Token here for that purpose
3. Save
4. Click **Test Connection** to verify your credentials against Twilio
5. Toggle **Telephony is on** to activate — calls only ring to agents once this is on

---

## Assigning Agents

Only assigned agents can receive or place calls.

1. Go to **Settings -> Telephony -> Manage agents**
2. Use the dropdown to add a team member as an agent
3. Each agent can toggle their own **Available / Unavailable** switch without being unassigned — useful for lunch breaks or when someone's away from their desk
4. Click the trash icon to unassign someone entirely

Only available, assigned agents ring when a call comes in.

---

## Using the Phone

Once you're an assigned agent, a **phone bar** appears docked at the bottom of every page in the CRM.

- **Idle** — shows "Phone ready"
- **Incoming call** — shows the caller's number with Answer/Reject actions
- **On a call** — shows mute and hang-up buttons
- **Outgoing** — shows "Calling…" while it connects

Click the bar (or the chevron) to expand it for the full dial pad, mute, and hang-up controls.

### Making an Outbound Call

1. Expand the phone bar
2. Enter the number in the dialer
3. Click to call

### During a Call

While connected to a number, the expanded panel automatically shows any **bookings linked to that phone number** — so you can pull up the customer's job without leaving the call.

### Calling From Another Window

If you have the CRM open in two tabs/windows, the phone only takes calls in one at a time. The other shows "This phone is connected in another window" — close it there to bring the line back.

---

## Requirements

- A Twilio account with a purchased phone number
- At least one team member assigned and available as an agent
- Telephony toggled on in Settings
