# Visit Type Colours

Give each of your company's visit types a colour, so your team can tell at a glance in the Scheduler
which visits are drop-offs, collections, visits at the customer's address and so on.

**Bookings -> Settings -> Visit Types**, or navigate to `/admin/bookings/settings/visit-types`

Colours work with your **own** visit types. The built-in types (Initial Assessment, Follow-up and
so on) have no colour. If your company hasn't set any colours, the Scheduler looks exactly as before.

---

## Setting a Colour

Only **company admins** can set colours.

1. Go to **Bookings -> Settings -> Visit Types**
2. Click the **palette** button on a type
3. Pick a colour with the colour picker, or type a code such as `#1E90FF`
4. Click **Save colour**

**Tip:** the card's fill already shows the status (yellow = booked, green = confirmed, red =
no-show), so a green, yellow or red stripe can blend in. Blue, purple, pink or grey stand out best.

To remove a colour, click the palette button and then **No colour**.

A small coloured dot next to each type shows its colour. A dashed empty circle means "no colour".

## Where the Colour Shows

### Scheduler: coloured stripe

Each visit card gets a **coloured stripe on its left edge** in its type's colour. The card's
background still shows the **visit status** (for example yellow = booked, green = confirmed,
red = no-show), so you see both the type and the status.

Hover over a visit card to see the customer's name and the visit type (its current name, also after
a rename).

### Scheduler: legend

When coloured visits are on screen, a **legend** under the toolbar shows which colour is which
type. It lists only the types on screen, so it changes as you move between days and weeks.
With no coloured visits on screen there is no legend. The **Month** view shows only a count of
visits per day (no cards), so it has no stripes and no legend.

### Scheduler: visit type filter

When the visits on screen are of **two or more types**, a **visit type** dropdown appears in the
toolbar (next to the drop-off filter, if your company uses drop-off points). Pick a type to see only
its visits, or **All visit types** to see everything. If you move to a week where the chosen type
has no visits, the Scheduler shows all visits for that week; your choice comes back when you return
to a week that has that type.

### Booking: Visits tab

On a booking's **Visits** tab, the visit type label shows a coloured dot when its type has a colour.

## Renaming, Switching Off and Deleting

- **Renaming** a type keeps its colour, also on visits booked before the rename. The Scheduler
  (legend, filter, hover) shows the new name; the booking's Visits tab keeps the name the visit
  was booked with
- **Switching a type off** keeps its colour on visits already booked with it
- **Deleting** a type removes its colour from those visits; they keep the type name

---

## Common Questions

**I set a colour but nothing changed in the Scheduler.**
The stripe shows on visits of that type. Check that the visits you're looking at actually use the
type (their type is shown on the booking's Visits tab). Visits created with Scheduler -> New Visit
have no type, and booking-form visits use a built-in type, so neither gets a stripe.

**Can I colour the built-in types?**
No. Add your own types first (see Visit Types); colours are set on those.

**Can two types have the same colour?**
Yes, but the legend then can't tell them apart, so pick colours that are easy to tell apart.

**Does the customer see the colour?**
No. Colours are only shown to your team in the CRM.
