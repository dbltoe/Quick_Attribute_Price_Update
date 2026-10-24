# Changelog

## v1.0.0 — 2026-10-24

First release, for Zen Cart 1.5.8a through 3.0.0 on PHP 7.4 through 8.5. No core file is changed and no table is added.

- Catalog > Quick Attribute Price Update: find attribute rows by product ID or ID ranges, by category with or without its subcategories, by option name and option value, by product name or model, and by product status; "Priced Rows Only"; sort products and page the results.
- Rows grouped under each product, with its base price, "Starting at" price, and links to the product, the Attributes Controller and Quick Price Update (when installed).
- Change the prefix, price and one-time price in the table and save every changed row with one button. Values are checked first; a row changed elsewhere after the page loaded is skipped and reported.
- Once per product touched: last-modified date stamped and the storefront price-sort value refreshed.
- Notes for what a row's price doesn't show by itself; a flag for products whose own price is 0 but which aren't priced by attributes.
- A link in Catalog > Attributes Controller's action menu.
