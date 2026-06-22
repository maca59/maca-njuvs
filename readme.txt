=== maca Njuvs ===
Contributors: macas
Tags: news, events, calendar, information, restaurant
Requires at least: 5.9
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.31
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Publish news and events on your website with Gutenberg blocks, an iCal calendar feed, and optional sharing to Facebook and Instagram.

== Description ==

**maca Njuvs** is a standalone WordPress plugin for news and events with its own database tables and settings.

* News with scheduled publishing, banner/ticker, popup, and in-page layouts for tables and columns
* Events with recurring dates and exceptions
* Gutenberg blocks for news and events (`maca-njuvs/maca-info-news`, `maca-njuvs/maca-info-events`)
* iCal feed at `/maca-njuvs-events.ics`
* Optional publishing to Facebook and Instagram via your own Meta app

Shortcodes: `[maca_njuvs_news]`, `[maca_njuvs_events]`, `[maca_njuvs_calendar_subscribe]`

Plugin page: https://maca.se/maca-njuvs/

Source code: https://github.com/maca59/maca-njuvs

== External services ==

This plugin optionally connects to Meta (Facebook) services when a site administrator configures social media publishing. No data is sent unless that feature is enabled and used.

= Meta Graph API =

Used to publish news posts to a connected Facebook Page and Instagram account, and to list pages during setup.

Data sent when an administrator publishes (or sends a test post): post caption text, image URL, Facebook Page ID, Instagram account ID, and access tokens stored on your site after OAuth. Requests go to `https://graph.facebook.com/`.

Service provider: Meta Platforms, Inc. — [Terms of Service](https://www.facebook.com/legal/terms), [Privacy Policy](https://www.facebook.com/privacy/policy/).

= Meta OAuth =

Used when an administrator clicks "Connect Facebook & Instagram" to authorize the plugin with their Meta app. The browser is redirected to `https://www.facebook.com/` for login; authorization codes and tokens are exchanged via the Meta Graph API and stored in your WordPress database.

Data sent during connection: OAuth authorization codes, app credentials configured by the administrator, and requested permission scopes. No visitor or front-end user data is sent.

Service provider: Meta Platforms, Inc. — [Terms of Service](https://www.facebook.com/legal/terms), [Privacy Policy](https://www.facebook.com/privacy/policy/).

== Installation ==

1. In WordPress admin, go to *Plugins → Add New*, search for **maca Njuvs**, and click *Install Now* — or upload `maca-njuvs-{version}.zip` via *Upload Plugin*
2. Activate the plugin through the *Plugins* menu
3. Open **maca Njuvs** in the admin menu to add news and events

== Frequently Asked Questions ==

= Where is content stored? =

All content and settings are stored in maca Njuvs database tables and options on your WordPress site.

= How do I show news and events on pages? =

Add the **maca News** and **maca Events** blocks in the block editor, or use the shortcodes `[maca_njuvs_news]` and `[maca_njuvs_events]`.

== Screenshots ==

1. News list in the maca Njuvs admin
2. Edit a news item with scheduling and social publishing options
3. Month calendar view on the frontend (maca Events block)
4. News list on the frontend (maca News block)
5. Gutenberg blocks in the block inserter
6. Recurring event editor in admin

== Upgrade Notice ==

= 1.0.31 =
First WordPress.org release. Install or update for news, events, iCal feed, and optional Facebook/Instagram publishing.

== Changelog ==

= 1.0.31 =
* WordPress.org listing: plugin URI and readme updated for the plugin directory
* Extract news/event admin form helpers to enqueued script (media picker, recurrence fields, large-image warning)

= 1.0.30 =
* Social publish progress bar moves continuously while waiting instead of jumping between steps

= 1.0.29 =
* Warn in admin when a selected news or event image is large (500 KB+); updated translations and guides

= 1.0.28 =
* Fix "Please reduce the amount of data" when saving news: lighter excerpt editor and strip pasted data-URI images

= 1.0.26 =
* Respect "Enable maca Njuvs" setting: stop auto-enabling and showing news when disabled

= 1.0.24 =
* Standalone plugin: removed maca Menulist integration and renamed internal API to maca_njuvs_*

= 1.0.22 =
* Exclude dev tools from release ZIP
* Escape output at echo time (wp_kses_post / wp_kses)

= 1.0.21 =
* WordPress.org review: enqueue admin and frontend scripts instead of inline tags
* Document Meta external services in readme
* Remove load_plugin_textdomain() (WordPress.org auto-loads translations)

= 1.0.12 =
* In-page news layout for tables and columns
* Plugin Check compatibility fixes

= 1.0.11 =
* Import on its own admin tab
* Standalone maca Njuvs product identity
* Release ZIP for WordPress upload

= 1.0.0 =
* Initial release — news, events, and iCal with dedicated tables
