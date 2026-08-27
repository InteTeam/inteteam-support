# Google Reviews

Connect your Google Business Profile so your Google reviews can be shown on your website.

**Settings -> Google Reviews**, or navigate to `/admin/settings/google-reviews`

---

## Setup

1. Find your business's **Google Place ID** (search "Google Place ID Finder" or ask your web developer)
2. Get a **Google Places API key** from the Google Cloud Console
3. Go to **Settings -> Google Reviews**
4. Enter your **Place ID** and **API Key**
5. Optionally set a **Minimum rating to show** (e.g. only show 4 stars and above) — leave as "Show all reviews" to display everything
6. Click **Save Configuration**
7. Click **Test Connection** to verify — this pulls your current Google rating, review count, and business name to confirm it's working. The integration only becomes active once this test succeeds.

Google returns up to 5 reviews per place — this is a Google API limit, not something the CRM controls.

---

## Updating Your API Key

The API key is never shown in full after saving (only a masked version, e.g. `AIza...xyz`). To change it, type a new key into the field and save — leave it blank to keep the current one.
