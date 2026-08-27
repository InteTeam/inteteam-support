# Scheduler Settings

Configure locations, drop-off points, business hours, and holidays for your scheduler.

**Settings -> Scheduler tab**

---

## Locations

Manage your physical locations (shops, branches, offices).

Each location has:

| Field | Description |
|-------|------------|
| **Name** | Location name (e.g. "Edinburgh High Street") |
| **Prefix** | Short code used in booking references |
| **Address** | Street address |
| **Postcode** | UK postcode |
| **Phone** | Contact number |
| **Email** | Contact email |
| **Active** | Toggle to show/hide on the scheduler |
| **Default** | Set one location as the default |

### Adding a Location

1. Go to **Settings -> Scheduler -> Locations**
2. Click **Add Location**
3. Fill in the details
4. Save

---

## Business Hours

Set your opening hours for each location.

1. Go to **Settings -> Scheduler -> Business Hours**
2. Select a **location** from the dropdown
3. For each day of the week:
   - Toggle **Active** to mark as a working day
   - Set **Start time** and **End time**
   - Optionally set a **Break start** and **Break end** time
4. Save

Non-working days and break times are blocked out on the calendar — no visits can be scheduled during those times.

---

## Holidays

Mark days when your location is closed.

1. Go to **Settings -> Scheduler -> Holidays**
2. Select a **location** (or set company-wide)
3. Click **Add Holiday**
4. Enter:
   - **Name** (e.g. "Christmas Day", "Bank Holiday")
   - **Date**
   - **Recurring** — toggle ON for annual holidays (repeats every year)
5. Save

Holidays appear on the calendar and block scheduling for that day.

You can create:
- **Company-wide holidays** — apply to all locations (e.g. Christmas, New Year)
- **Location-specific holidays** — only affect one location (e.g. local events)

---

## Scheduler Config (Concurrency & Buffer)

Control how many bookings can share a time slot, and add breathing room between appointments — per location.

**Settings -> Scheduler -> Scheduler Config**, or navigate to `/admin/scheduler/settings/config/{location}`

1. Select a **location**
2. Set **Max Concurrent Bookings** — how many bookings can share a single time slot. Set to **0** to temporarily close the location to new bookings without touching business hours
3. Toggle **Buffer Between Bookings** on to reserve time after each booking before the next slot opens, then set the buffer in minutes
4. Click **Save**

Leaving the buffer toggle off (or Max Concurrent Bookings unset) keeps the previous default behaviour — nothing changes for a location until you actively configure it here.

**Known gap:** the buffer only applies to the live availability check made when a booking is actually created — a slot that a buffer has just closed off can still briefly show as available in a pre-calculated slot listing until the next scheduled slot recalculation runs. The booking itself is still protected either way; this only affects what shows as available for a short window.
