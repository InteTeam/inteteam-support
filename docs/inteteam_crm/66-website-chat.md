# Website Chat

A chat button for your own website. A visitor presses it and gets a small panel with shortcuts: book a visit, see their bookings, and your phone number and opening hours.

**Settings -> Website Chat**, or navigate to `/admin/settings/chat-widgets`

Only company admins can open this screen.

---

## What It Is

The button sits in a corner of your website. Pressing it opens a panel that greets the visitor and offers up to three buttons:

| Button | What it does | Shown when |
|---|---|---|
| **Book a visit** | Opens your booking page | You filled in "Booking page" |
| **My bookings** | Opens your customer account page | You filled in "Customer account page" |
| **Phone and opening hours** | Shows your phone number and opening hours inside the panel | Always |

Visitors cannot type a message in the chat yet. It does not collect a name, email address or any other detail from them, and it sets no cookie.

---

## Creating a Widget

A widget is one chat button for one website. Create one for each website you want the chat on.

1. Go to **Settings -> Website Chat** and click **New widget**
2. Fill in the fields below
3. Click **Save**

| Field | What to enter |
|---|---|
| **Name** | Anything that helps you tell your widgets apart, e.g. "Main website". Only you see it |
| **Websites that may show this chat** | The website's address, one per line, up to 10. Enter `example.com`, not a full page address |
| **Booking page** | Where "Book a visit" leads. Must start with `https://` and be on one of the websites above, or on the CRM itself. Leave empty for no button |
| **Customer account page** | Where "My bookings" leads. Must start with `https://`. Leave empty for no button |
| **Phone and opening hours from** | The location whose phone number and opening hours the panel shows |
| **The chat is on / off** | Off hides the button on the website without deleting anything |

**Add the address both with and without `www`.** `example.com` and `www.example.com` count as two different websites. If your site answers on both and you add only one, the button appears on one and not the other.

The phone number and opening hours are not typed in here. They come from the location you picked, so they always match what the rest of the CRM uses. To change them, see [Scheduler Settings](51-scheduler-settings.md).

---

## Changing the Look

The **Look** section of the form sets:

- Six colours: main colour, text on the main colour, background, text, the visitor's messages, text in those messages
- The text on the button
- How rounded the corners are (0 to 24)
- Font: Standard, Serif, Rounded or Typewriter
- Light, dark, or follow the visitor's device
- Button position: bottom right or bottom left

The preview next to the form changes as you type. Nothing changes on your website until you save.

---

## Putting It on Your Website

Each saved widget shows one line of code with a **Copy** button.

1. Click **Copy**
2. Paste the line into your website just before `</body>`, on every page that should show the chat
3. Open your website. The button appears in the corner you chose

If you use a website builder, look for a setting called "custom code", "footer code" or "embed code". If someone else looks after your website, send them the line.

You paste the line once. Later changes to the look, the buttons or the on/off switch take effect on the website by themselves.

---

## Switching It Off or Deleting It

- **Off:** edit the widget and switch the chat off. The button disappears from the website. The line of code can stay where it is; switching the chat back on brings the button back.
- **Delete:** the button disappears straight away and the line of code stops working for good. A new widget gets a new line, which has to be pasted into the website again. Deleting cannot be undone.

If you only want a break, switch it off rather than deleting it.

---

## Troubleshooting

**The button does not appear on my website**

- Check the widget is **On**
- Check the website's address is in "Websites that may show this chat", spelled exactly as it shows in the browser's address bar, with or without `www`
- Check the line of code is on that page. It has to be on every page that should show the button
- Check the website is opened with `https://`

The chat is built to show nothing at all when something is not right, rather than a broken button. So a missing button almost always means one of the points above.

**"Book a visit" or "My bookings" is missing from the panel**

The field for that button is empty. Edit the widget and fill in "Booking page" or "Customer account page".

**The opening hours or phone number are wrong**

They come from the location chosen on the widget. Check the right location is chosen, then correct the hours or phone number on that location.

**The booking page address is refused when I save**

It must start with `https://` and be on one of the websites listed on the widget, or on the CRM itself.
