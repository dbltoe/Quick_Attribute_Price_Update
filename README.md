# Quick Attribute Price Update

Version 3.0.3 · Zen Cart 1.5.8a through 3.0.0 · PHP 7.4 through 8.5 · GPL v3 · by My Zen Cart Host (dbltoe)

Quick Attribute Price Update adds **Catalog > Quick Attribute Price Update** to your admin. Find the attribute prices you want across all your products, change them right in the table, and save every change with one button. Zen Cart's own Attributes Controller edits one attribute of one product at a time; this page shows every product that uses, say, *Size: 1 inch*, with its price, on one screen. No core files are changed, and there are no settings to set.

## What It Does

- **Find attribute rows by product.** Type one product ID, a list, or ranges: `12, 15, 40-55`. Exact matches only.
- **Find them by category**, with or without everything in its subcategories. Products linked into a category are included.
- **Find them by option name and option value**, for example every product's *Size* option, or just its *1 inch* value. The lists go by name, so a store with two options both called *Size:*, or with *1 inch* saved more than once, sees each name once and finds every matching row.
- **Narrow it down** by product name or model and by Active/Inactive, or tick *Priced Rows Only* to hide rows with no price. Sort products by sort order, ID, name or model, and show 25, 50, 100 or 200 rows per page.
- **Rows are grouped under each product**, with its base price, its *Starting at* price (the lowest price shoppers see), and links to edit the product, open it in the Attributes Controller, or change its base price in Quick Price Update when you have that plugin.
- **Change the prefix, price and one-time price in the table.** Changed rows are highlighted and counted. One *Save Changes* writes only the rows you changed.
- **Every value is checked first.** Prices take up to 4 decimal places and can't be negative (use the `-` prefix to subtract). `1,234.50` is understood; `12,50` is refused rather than misread. A row that doesn't pass is not saved, and it comes back highlighted with what you typed.
- **Two people can't overwrite each other.** If a row changed somewhere else after your page loaded, it's skipped and you're told which rows.
- **The storefront stays right.** For every product a save touches, Zen Cart's price-sort value is refreshed (so price sorting and *Starting at* prices update) and the product's last-modified date is stamped. Zen Cart's own Attributes Controller doesn't stamp that date.
- **Notes show what a row's price doesn't**: Free, Display Only, Default, Not Discounted, Not in Base Price, Price Factor, Quantity Prices and Word or Letter Pricing. A product whose own price is 0 but which isn't set to Priced by Attributes is flagged, since shoppers may see a 0 price.
- **A link in the Attributes Controller.** Its action menu gets *Quick Attribute Price Update: This Product* (or *This Category*).

## Install

1. Unzip the package. Copy the `zc_plugins/QuickAttributePriceUpdate` folder into your store's `zc_plugins/` folder, so you have `zc_plugins/QuickAttributePriceUpdate/v3.0.3/manifest.php`.
2. In your admin, go to **Modules > Plugin Manager**, select **Quick Attribute Price Update** and click **Install**.
3. Open **Catalog > Quick Attribute Price Update**.

Once it's installed, a superuser can open this guide from the **ReadMe** button in Plugin Manager.

Admins other than superusers need the page added to their admin profile (**Admins > Admin Profiles**, under Catalog). The Attributes Controller link only shows for admins who can open the page.

## Upgrade and Uninstall

To upgrade, copy the new version's folder beside the old one in `zc_plugins/QuickAttributePriceUpdate/` and click **Upgrade** in Plugin Manager. To remove it, click **Uninstall**: that takes the page off the menu. The plugin creates no tables and stores no settings, so nothing else is left behind. Prices you saved stay as you saved them.

## Good to Know

> This page changes three fields of each attribute: its prefix, its price and its one-time price. Everything else (price factors, quantity prices, word and letter prices, weights, images, the yes/no settings) stays as it is; edit those in the Attributes Controller. The notes column shows when one of them applies.

- Prices are in your default currency, the same as in the Attributes Controller.
- A blank prefix adds, just like `+`. Only `-` subtracts.
- The product's own (base) price is not on this page. Change it on the product's edit page, or in Quick Price Update.
- If you leave the page with unsaved changes, your browser asks first.
- Each save is recorded in **Tools > Admin Activity Log** with the attribute row IDs and product IDs that changed.
- A search can name up to 1,000 product IDs at a time.
- For a whole-catalog spreadsheet round trip, Database I/O Manager (DbIo) has an attributes export and import.

## Compatibility

| Item | Details |
| --- | --- |
| Zen Cart | 1.5.8a, 2.0.x, 2.1.0, 2.2.x, 2.3 and 3.0.0 |
| PHP | 7.4 through 8.5 (whatever your Zen Cart version supports) |
| Core files changed | None. The Attributes Controller link uses a notifier that exists, with the same parameters, on every version above. |
| Templates | Admin only; your storefront template doesn't matter. |

## More Documentation

The download's `docs/` folder goes further: `INSTALL.md`, `CONFIGURATION.md` (every search field, the notes and what a save does), `CUSTOMIZING.md` (wording, translating, and the notifiers for add-ons) and `COMPATIBILITY.md`.

## Companion

Quick Price Update (https://github.com/dbltoe/Quick_Price_Update) changes products' own (base) prices and quantities. The two link to each other but work alone.

## Support

Questions and problem reports go in the Quick Attribute Price Update support thread (https://www.zen-cart.com/threads/207448?page=1#post-1347750) on the Zen Cart forum, which is also linked from the plugin's Plugin Manager panel. Please include your Zen Cart and PHP versions, and any `myDEBUG-` file from your store's `logs/` folder from that moment. The source is on GitHub (https://github.com/dbltoe/Quick_Attribute_Price_Update).

## Changelog

See `CHANGELOG.md`. Version 3.0.3 moves the plugin to the GNU General Public License, version 3, and replaces readme.html with this guide, opened from the ReadMe button in Plugin Manager.

## License

Quick Attribute Price Update is free software under the GNU General Public License, version 3 (https://www.gnu.org/licenses/gpl-3.0.html); the full text is in `LICENSE`. Copyright © 2026 My Zen Cart Host (dbltoe).
