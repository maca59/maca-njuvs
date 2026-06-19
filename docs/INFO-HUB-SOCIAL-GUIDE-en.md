# Step-by-step: Connect Facebook and Instagram to maca Njuvs

**Version:** 1.0.15  
**Applies to:** **maca Njuvs** — WordPress plugin for news, events, iCal calendar, and social sharing

This guide helps you publish news and events from **maca Njuvs** to your **Facebook Page** and **Instagram Business account**. maca Njuvs does not host your Meta login centrally — you create your own app in [Meta for Developers](https://developers.facebook.com/) and connect it to your WordPress site.

---

## Before you start

1. **maca Njuvs installed and active** — the plugin should appear under *Plugins* in WordPress.
2. **Publishing enabled** — *maca Njuvs → Settings* → check *Enable maca Njuvs* and save.
3. **Facebook Page** — you must be an admin on the Page you want to publish to.
4. **Instagram Business or Creator** — the account must be **linked to the Facebook Page** (in Meta Business Suite or Instagram app under *Profile → Edit profile → Page*).
5. **HTTPS** — WordPress **Site Address** must start with `https://`.
6. **Meta Developer account** — free account at [developers.facebook.com](https://developers.facebook.com/).

> **Tip:** Open *maca Njuvs → Social media* alongside this guide. There you will see the OAuth redirect URL and enter App ID and App Secret.

---

## Overview

| Step | Where | What |
|------|-------|------|
| 1 | Meta for Developers | Create app |
| 2 | Meta app | Choose correct **use cases** (Page + Instagram) |
| 3 | Meta app | App domains, privacy policy, OAuth redirect |
| 4 | Meta app | Permissions |
| 5 | WordPress (maca Njuvs) | App ID + App Secret |
| 6 | WordPress (maca Njuvs) | Connect Facebook and select Page |
| 7 | WordPress (maca Njuvs) | Test publishing |
| 8 | WordPress (maca Njuvs) | Publish news and events |

---

## Step 1 — Create Meta app

1. Go to [developers.facebook.com/apps](https://developers.facebook.com/apps) and click **Create app**.
2. Choose **Business** as the app type if prompted.
3. Give the app a clear name, e.g. *Your company name – maca Njuvs*.
4. Select your **Business Manager** portfolio if asked.
5. Create the app and note the **App ID** (shown at the top of the app dashboard).

---

## Step 2 — Add the right use cases

This step matters — wrong use cases cause permission and OAuth errors.

1. In the app dashboard: **Use cases** → **Add use cases**.
2. Add **Manage everything on your Page**.
3. Add **Manage messaging & content on Instagram**.

**Do not** rely on generic *Facebook Login* alone — it is not enough to publish to Page and Instagram.

---

## Step 3 — App settings

### App domains

Under **App settings → Basic**:

- **App domains:** your site domain without `https://`, e.g. `{{SITE_DOMAIN}}`.
- **Privacy policy URL:** a public HTTPS privacy policy page (required by Meta). Example: `https://maca.se/policy/`
- **Website:** your site URL, e.g. `https://{{SITE_DOMAIN}}`

Save changes.

### OAuth redirect URI

1. Go to **Use cases → Facebook Login for Business** (or the Login product linked to your app).
2. Under **Settings** / **Valid OAuth Redirect URIs**, paste **exactly** the URL shown in WordPress under *maca Njuvs → Social media → OAuth redirect URI*:

```
{{OAUTH_REDIRECT_URI}}
```

3. Save. The URL must match **character for character** — no extra slash, no `http` if the site uses `https`.

---

## Step 4 — Permissions

maca Njuvs needs these permissions when you connect (Meta may show them at login):

| Permission | Purpose |
|------------|---------|
| `pages_show_list` | List Pages you administer |
| `pages_manage_posts` | Publish to the Facebook Page |
| `pages_read_engagement` | Read basic Page info |
| `instagram_basic` | Link Instagram Business account |
| `instagram_content_publish` | Publish to Instagram |
| `business_management` | Link Page and Instagram in Business Manager |

In **Development mode**, this works for app admins and testers. For production, Meta may require **App Review** and **Business Verification** — follow Meta’s checklist in the Developer Console.

> **Webhooks are not required** to publish news and events from maca Njuvs.

---

## Step 5 — Enter App ID and App Secret in WordPress

1. Go to *maca Njuvs → Social media*.
2. Under **Meta app credentials**:
   - **App ID** — from Meta Developer Console
   - **App Secret** — from *Settings → Basic* (click *Show*)
3. **Test image URL** (optional but recommended) — a public HTTPS image used for Instagram tests (Instagram always requires an image).
4. Click **Save Meta settings**.

---

## Step 6 — Connect Facebook and select Page

1. Click **Connect Facebook & Instagram**.
2. Log in with an account that is **admin** on the Facebook Page.
3. Approve the permissions Meta shows.
4. Select **which Facebook Page** to connect (if you have several).
5. Confirm — you should see the Page name and optionally `@instagram-username` under **Connection**.

If Instagram does not appear: verify the Instagram account is **Business/Creator** and **linked to that Facebook Page**.

---

## Step 7 — Test publishing

1. Fill in **Test image URL** if missing (public HTTPS image).
2. Click **Test publish** on the *Social media* tab.
3. Check that a test post appears on the Facebook Page (and Instagram if connected).
4. On errors: see **Publish log** further down on the same tab — Meta API error messages are stored there.

---

## Step 8 — Publish news and events

1. Create or edit **news** or an **event** under *maca Njuvs → News* or *maca Njuvs → Events*.
2. Set status to **Published** (or scheduled with a date that has already passed).
3. Under **Publishing** — check **Facebook** and/or **Instagram**.
4. **Instagram requires an image** on the news item or event.
5. Save — publishing runs immediately if Facebook is connected.

**Social caption text:** title plus content (or excerpt if filled in) is sent as the caption. On Instagram, text may appear below the image — tap *more* to read all of it.

**Publish again:** if a post was already published, you can check *Publish again to Facebook/Instagram* when editing the news item.

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| *Invalid OAuth Redirect URI* | Compare the URL in Meta with the exact value in *maca Njuvs → Social media* (step 3). |
| *Invalid Scopes* | Check use cases in step 2 — add Page + Instagram. |
| Redirect to wp-admin / blank page | Update maca Njuvs to the latest version (OAuth uses the REST URL above). |
| Instagram missing after connect | Link Instagram Business to the Facebook Page in Meta Business Suite. |
| Image only, no text | Fill in **Content** on the news item; use *Publish again* if already published. |
| *Instagram requires an image* | Upload an image on the news item or event. |
| Token expired | Connect again via *Connect Facebook & Instagram*; maca Njuvs tries to refresh the token automatically. |
| Meta connection worked on another site | maca Njuvs stores its **own** Meta connection per site — configure the app and connect again under *maca Njuvs → Social media*. |

---

## Security and privacy

- **App Secret** is stored encrypted in WordPress — do not share it publicly.
- **Page access token** is stored encrypted on your server.
- maca.se does **not** host your OAuth — all traffic goes between your site and Meta.
- maca Njuvs stores social settings in its own tables (`wp_maca_njuvs_*`).
- Mention in your privacy policy that posts may be published to social media when you use this feature.

---

## Quick reference in WordPress

| Location | Purpose |
|----------|---------|
| *maca Njuvs → Settings* | Enable publishing, iCal URL, **link to this guide** |
| *maca Njuvs → Social media* | App ID/Secret, connection, test, log |
| *maca Njuvs → News / Events* | Check Facebook/Instagram when publishing |
