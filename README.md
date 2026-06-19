# maca Njuvs

WordPress plugin for **news and events** — Gutenberg blocks, iCal calendar feed, and optional sharing to Facebook and Instagram.

- **Source:** [github.com/maca59/maca-njuvs](https://github.com/maca59/maca-njuvs)
- **Website:** [maca.se](https://maca.se/)

## Features

- News with scheduled publishing, banner/ticker, popup, and in-page layouts
- Events with recurring dates and exceptions
- Gutenberg blocks (`maca-njuvs/maca-info-news`, `maca-njuvs/maca-info-events`)
- iCal feed at `/maca-njuvs-events.ics`
- Optional publishing to Facebook and Instagram via your own Meta app
- Shortcodes: `[maca_njuvs_news]`, `[maca_njuvs_events]`, `[maca_njuvs_calendar_subscribe]`

## Installation

1. Download a release ZIP from [Releases](https://github.com/maca59/maca-njuvs/releases) or clone this repo into `wp-content/plugins/maca-njuvs/`
2. Activate under **Plugins**
3. Open **maca Njuvs** in the admin menu to add news and events

See `readme.txt` for full documentation (WordPress.org format).

## Development

Requires PHP 7.4+, WordPress 5.9+.

```powershell
cd path\to\maca-njuvs
.\pack-plugin.ps1
```

Builds `maca-njuvs-{version}.zip` for **Plugins → Add New → Upload Plugin**.

## License

GPL-2.0-or-later — see [readme.txt](readme.txt) and plugin headers.
