# maca Njuvs — user guide

**Version:** 1.0.15  
**Applies to:** **maca Njuvs** — WordPress plugin for news, events, Gutenberg blocks, iCal calendar, and optional sharing to Facebook and Instagram.

maca Njuvs lets you create and publish news and events directly in WordPress. Content appears on your website via Gutenberg blocks (or shortcodes) and can be shared to social media when a Meta app is connected.

---

## Admin tabs

| Tab | What you do here |
|-----|------------------|
| **News** | Create, edit, and manage news items |
| **Events** | Create, edit, and manage events (including recurring series) |
| **Social media** | Connect Meta app, Facebook Page, and Instagram (requires special capability) |
| **Settings** | Enable/disable the module, iCal URLs, link to social setup guide |
| **Import** | Import existing WordPress posts as news |
| **Guide** | This guide — blocks, settings, and features |

---

## News

Under *maca Njuvs → News* you create and edit news shown on your website.

### Fields

| Field | Description |
|-------|-------------|
| **Title** | Main heading — shown on the website, in the banner, and first in social post captions |
| **Excerpt** | Short summary for lists and banner. Included in social text after the title |
| **Content** | Full text. Clicking a news item opens the full content (popup or expanded view) |
| **Image** | Optional image from the media library |
| **Status** | Draft, Scheduled, Published, or Archived |
| **Publish at** | Optional date/time. A future date with status Published becomes Scheduled until then |
| **Expires at** | Optional — the item is hidden automatically after this date |
| **Publishing** | Checkboxes for website, Facebook, and Instagram |

### Statuses

- **Draft** — not shown on the website
- **Scheduled** — published automatically at the set time
- **Published** — shown on the website (when the module is enabled)
- **Archived** — hidden from the website but kept in admin

### Image tips

- Use **Select image** — do not paste images into excerpt or content fields.
- Compress large images (preferably under 500 KB). The plugin warns when an image is large; very large files can trigger *Please reduce the amount of data* when saving.

---

## Events

Under *maca Njuvs → Events* you manage upcoming and recurring events.

### Fields

| Field | Description |
|-------|-------------|
| **Title** | Event name |
| **Description** | Detailed text |
| **Location** | Where the event takes place |
| **Image** | Optional image |
| **Price** | Optional — shown on the website when set |
| **All day** | Check if the event runs all day |
| **Start / End** | Date and time |
| **Recurrence** | None, Daily, Weekly, or Monthly with interval, weekdays, and end date or occurrence count |
| **Active** | Show on website |
| **Publishing** | Website, Facebook, and Instagram |

### Exceptions in recurring series

For recurring events you can add **exceptions** when editing — cancel or reschedule a single occurrence without changing the whole series.

---

## Gutenberg blocks

Add blocks from the **maca Njuvs** category in the block editor (search for *maca News* or *maca Events*).

### maca News

Shows published news from maca Njuvs.

| Setting | Description |
|---------|-------------|
| **Layout** | List, In page (table/column), Fixed panel left/right, or Top banner |
| **Number of items** | 1–20 news items |
| **Scrolling ticker** | (Banner) Continuous horizontal scroll |
| **Show image** | (List) Show thumbnail |
| **Show date** | Show publish date |
| **Show excerpt** | Show short summary |

**Layout tips:**

- **List** — standard view with optional image
- **In page** — stays where you place the block, e.g. in tables and columns
- **Fixed panel** — fixed on the side while scrolling on desktop; on mobile it appears last on the page. Click opens the full item in a popup
- **Top banner** — band at the top. Use at most one banner block per page

### maca Events

Shows upcoming events.

| Setting | Description |
|---------|-------------|
| **View** | List or Month calendar |
| **Number of events** | 1–30 (list view) |
| **Show image** | (List view) |
| **Show location** | (List view) |
| **Week starts on Monday** | (Month calendar) |
| **Show calendar subscription** | Links to subscribe to the iCal feed |

---

## Shortcodes

If you do not use the block editor, the same content can be shown with shortcodes:

### News

```
[maca_njuvs_news limit="5" layout="list" show_image="1" show_date="1" show_excerpt="1" banner_scroll="1"]
```

| Attribute | Values | Default |
|-----------|--------|---------|
| `limit` | 1–50 | 5 |
| `layout` | `list`, `embedded`, `sidebar-left`, `sidebar-right`, `banner` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_date` | `1` / `0` | `1` |
| `show_excerpt` | `1` / `0` | `1` |
| `banner_scroll` | `1` / `0` | `1` |

### Events

```
[maca_njuvs_events limit="10" view="list" show_image="1" show_location="1" show_subscribe="1"]
```

| Attribute | Values | Default |
|-----------|--------|---------|
| `limit` | 1–50 | 10 |
| `view` | `list`, `month` | `list` |
| `show_image` | `1` / `0` | `1` |
| `show_location` | `1` / `0` | `1` |
| `show_subscribe` | `1` / `0` | `1` |

### Calendar subscription

```
[maca_njuvs_calendar_subscribe]
```

Shows links to subscribe to the event calendar in calendar apps.

---

## Settings

Under *maca Njuvs → Settings*:

| Setting | Description |
|---------|-------------|
| **Enable maca Njuvs** | Master switch — when off, no content is shown on the website or in blocks |
| **iCal feed URL** | Public feed for calendar apps: `{{ICAL_URL}}` |
| **Subscribe URL** | webcal link for Apple Calendar and others: `{{WEBCAL_URL}}` |

> **Tip:** If the iCal feed returns 404, go to *Settings → Permalinks* in WordPress and save once.

### Facebook and Instagram

Social media connection is managed under *Social media*. A separate step-by-step guide is available via the *Setup guide: Facebook & Instagram* button on the settings page.

---

## Import

Under *maca Njuvs → Import* you can copy existing WordPress posts into maca Njuvs as news.

| Option | Description |
|--------|-------------|
| **Content type** | Post or page |
| **Category** | Optional filter (posts only) |
| **Skip already imported** | Avoid duplicates |

Original posts are not deleted — import creates new news items in maca Njuvs.

---

## Other features

- **iCal calendar** — events are exported to a public feed that updates when events change
- **Scheduled publishing** — news can go live at a set time without manual action
- **Expiry dates** — news can be hidden automatically
- **Recurring events** — daily, weekly, and monthly series with exceptions
- **Social publishing** — optional sharing to Facebook Page and Instagram Business on save (requires Meta app)

---

## Quick start

1. Enable maca Njuvs under *Settings*
2. Create at least one news item or event
3. Add the **maca News** and **maca Events** blocks to a page
4. (Optional) Connect Facebook/Instagram under *Social media*
5. (Optional) Share the iCal URL so guests can subscribe to the calendar

---

## Terms of use

By using **maca Njuvs** you agree to the following terms:

1. **License** — The plugin is distributed under the GNU General Public License v2 or later (GPL v2+). You may use, modify, and distribute the plugin according to the license terms.
2. **Content responsibility** — As site owner you are responsible for all content (news, events, images, and text) you publish via the plugin, on your website, and through connected social media.
3. **Third-party services** — Features involving Facebook, Instagram, and the Meta Graph API are governed by each service's terms. You must comply with Meta platform policies and have the necessary rights to shared content.
4. **No warranty** — maca Njuvs is provided as-is without express or implied warranty. Maca Development is not liable for downtime, data loss, or damage arising from use of the plugin.
5. **Limitation of liability** — To the extent permitted by law, Maca Development is not liable for indirect damages, lost profits, or data loss resulting from the plugin or integrated services.
6. **Updates** — Features may change or be removed in future versions. We recommend backing up before updates.

## Privacy policy

maca Njuvs processes data locally on your WordPress site. As site owner you are the data controller for visitor and content data under applicable law, e.g. GDPR.

### What data is stored

| Data | Where | Purpose |
|------|-------|---------|
| News and events | WordPress database (custom tables) | Publishing on the website and in blocks |
| Image URLs and text | Same database | Display and social sharing |
| Meta App ID, tokens, etc. | WordPress options (encrypted where applicable) | Facebook/Instagram publishing |
| Social publish log | WordPress database | Troubleshooting and status in admin |
| Imported post IDs | Post metadata | Avoid duplicates on import |

### What data is shared externally

- **No data is sent to Maca Development** by default when you use the plugin.
- **The iCal feed** (`{{ICAL_URL}}`) is public — title, time, location, and description of active events can be read by anyone with the link.
- **Social publishing** sends content and images to Meta (Facebook/Instagram) according to your settings and Meta's API.

### Retention and deletion

- Data remains after uninstall unless the constant `MACA_NJUVS_UNINSTALL_DROP_DATA` is set to `true` before uninstall.
- You can delete news, events, and social media connections at any time in admin.

### Your obligations

- Inform visitors in your website **privacy policy** about the iCal feed, any tracking technologies (via other plugins), and social publishing.
- Provide a public privacy policy URL in your Meta app when using Facebook/Instagram connection (Meta requirement).

### Contact

For support and questions about the plugin: [maca.se](https://maca.se/)
