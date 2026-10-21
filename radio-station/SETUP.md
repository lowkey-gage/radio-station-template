# KITE station setup

The site is two pieces. The theme draws the pages. The KITE Station plugin stores shows, DJs, categories, and schedule times. Install both. Switching themes later keeps the records only when the plugin is active.

## Local (XAMPP)

1. Start Apache and MySQL.
2. Copy this theme to `wp-content/themes/radio-station`.
3. Copy `companion/kite-station` to `wp-content/plugins/kite-station`.
4. In wp-admin, open Plugins and activate **KITE Station**.
5. Open Appearance → Themes and activate **Radio Station**.
6. Visit the front of the site once. The first visit creates Schedule, Listen, and About, builds the Station menu, and fills an empty library.
7. Open Settings → General and set the timezone to the station’s city. The schedule page prints that timezone.
8. Open Appearance → Customize → Station identity and Where to listen. Set the stream URL if you have one. The player appears on Listen after a URL is saved.

## Pressable

Zip the theme so the zip’s first folder is `radio-station` and `style.css` is directly inside it. Upload that zip under Appearance → Themes → Add New → Upload Theme, then activate it.

Zip only the `kite-station` folder (the one that contains `kite-station.php`). Upload that zip under Plugins → Add New → Upload Plugin, then activate **KITE Station**.

Do not upload the theme zip on the Plugins screen. Do not click Update on anything named Radio Station from WordPress.org. That is a different plugin, and updating replaces this work.

After both are active, open the public site once so the About page and sample records can be added to a library that was already seeded.

## What the first visit creates

- Pages: Schedule, Listen, About.
- Menu: Station, assigned to the header.
- Categories: Music, News, Sports, Talk, plus Overnight from the original sample.
- Seven shows and four DJs, only when Shows and DJs are both empty. An existing library is extended in place: overnight sample times on Afterglow are shortened so they end the same day, and City Desk, Final Whistle, and Lena Ortiz are added if those titles are missing.
