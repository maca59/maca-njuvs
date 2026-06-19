=== maca Njuvs ===
Contributors: macadevelopment
Tags: news, events, calendar, information, restaurant
Requires at least: 5.9
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.18
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

Source code: https://github.com/maca59/maca-njuvs

== Installation ==

1. Upload `maca-njuvs-{version}.zip` via *Plugins → Add New → Upload Plugin*, or copy the folder to `/wp-content/plugins/maca-njuvs/`
2. Activate the plugin through the *Plugins* menu in WordPress
3. Open **maca Njuvs** in the admin menu to add news and events

== Frequently Asked Questions ==

= Where is content stored? =

All content and settings are stored in maca Njuvs database tables and options on your WordPress site.

= How do I show news and events on pages? =

Add the **maca News** and **maca Events** blocks in the block editor, or use the shortcodes `[maca_njuvs_news]` and `[maca_njuvs_events]`.

== Changelog ==

= 1.0.12 =
* In-page news layout for tables and columns
* Plugin Check compatibility fixes

= 1.0.11 =
* Import on its own admin tab
* Standalone maca Njuvs product identity
* Release ZIP for WordPress upload

= 1.0.0 =
* Initial release — news, events, and iCal with dedicated tables
