=== Traditores In-One-Year ===
Contributors: thesolverus
Tags: bible, reading plan, scripture, devotional, shortcode
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Insert a Bible reading plan into your posts or pages.

== Description ==

Traditores In-One-Year displays a table of daily Bible readings. Each day links to that day's text on [Biblia.com](https://biblia.com/) (Faithlife). The plugin does not call Biblia from your server; the visitor's browser loads Biblia only after they click a link.

Features include scroll-to-today, optional duplication of today's row at the top of the table, a visitor-selectable Bible translation (stored in an optional browser cookie), the `[tradioy]` shortcode, and a "Bible Reading Plan" block.

Reading plans are in English.

== Privacy ==

When the cookie option is enabled, the plugin may store the selected Bible version code in a first-party cookie (`trad_ioy_bible_version`) for up to one year. No data is sent to Traditores servers. Links use Biblia.com; see [Faithlife Terms](https://faithlife.com/terms) and [Faithlife Privacy](https://www.logos.com/privacy).

== Installation ==

1. Install through **Plugins → Add New** and search for "Traditores In-One-Year", or upload the plugin zip.
2. Activate the plugin.
3. Open **Settings → Traditores IOY** to choose defaults.
4. Add the block or shortcode `[tradioy]` to a post or page.

== Frequently Asked Questions ==

= Does this plugin require a Biblia account? =

No. Links open the public Biblia reader.

= How do I show only the New Testament plan? =

Use `[tradioy type="nt"]` or choose "New Testament" in the block.

== Screenshots ==

1. Settings screen
2. Reading plan on the front end

== Changelog ==

= 1.0.0 =
* Security and PHP 8.2+ compatibility overhaul
* Settings API, dynamic block, shortcode-driven asset loading
* HTTPS Biblia links, HCSB/NRSV code fixes with upgrade migration
* Modern front-end JavaScript (scrollIntoView, native cookies)
* Plugin Check and coding standards compliance

= 0.3 =
* Deprecated local jQuery copy per WordPress.org review.

= 0.2 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Major update: test reading plans and settings after upgrading. Bible codes hscb→hcsb and nsrv→nrsv migrate automatically.

== Development ==

Source: https://github.com/thesolverus/trad-ioy
