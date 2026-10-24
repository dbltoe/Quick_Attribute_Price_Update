# Quick Attribute Price Update

Find attribute prices across all your products by product ID, category, option name or option value, change them in one table, and save them all at once — on **Zen Cart 1.5.8a through 3.0.0** and **PHP 7.4 through 8.5**, with no core files changed.

- **Catalog > Quick Attribute Price Update**: search by product IDs (`12, 15, 40-55`), by category with or without its subcategories, by option name and option value, by product name or model, and by status; "Priced Rows Only" hides rows with no price.
- Rows are grouped under each product, with its base price and "Starting at" price, and links to the product, the Attributes Controller and Quick Price Update (when installed).
- Edit the prefix (+, - or blank), the price and the one-time price; changed rows are highlighted; one **Save Changes** writes only those rows.
- Values are checked before anything is written. A row someone else changed after the page loaded is skipped and reported, never overwritten.
- For every product touched, saving refreshes Zen Cart's price-sort value (storefront price sorting and "Starting at" prices) and stamps its last-modified date.
- Notes flag what a row's price doesn't show by itself (Free, Display Only, price factors, quantity prices and more), and products whose own price is 0 but which aren't priced by attributes.
- **Catalog > Attributes Controller** gets a link in its action menu for the product or category on screen.

## Install

1. Copy `zc_plugins/QuickAttributePriceUpdate` into your store's `zc_plugins/` folder.
2. **Modules > Plugin Manager** → **Quick Attribute Price Update** → **Install**.
3. Open **Catalog > Quick Attribute Price Update**.

See [docs/INSTALL.md](docs/INSTALL.md), [docs/CONFIGURATION.md](docs/CONFIGURATION.md), [docs/CUSTOMIZING.md](docs/CUSTOMIZING.md) and [docs/COMPATIBILITY.md](docs/COMPATIBILITY.md). The full read-me is `readme.html`.

## Companion

[Quick Price Update](https://github.com/dbltoe/Quick_Price_Update) changes products' own (base) prices and quantities. The two link to each other but work alone.

## License

GNU General Public License v2.0 — see [LICENSE](LICENSE). © 2026 My Zen Cart Host (dbltoe).
