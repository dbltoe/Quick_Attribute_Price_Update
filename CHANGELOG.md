# Changelog

## v3.0.1 — 2026-10-24

- Option Name and Option Value now search by name. A store with two options both called "Size:", or with "1 inch" saved as several different option values, gets one "Size:" entry and one "1 inch" entry, and each finds every matching row. Before, each list showed the duplicates separately with nothing to tell them apart, and choosing one found only part of the rows.
- Choosing an option value while Option Name is on "All Option Names" now picks its option name too.
- Links and bookmarks with a single option or value ID still work.

## v3.0.0 — 2026-10-24

First release, numbered 3.0.0 to match its companion, Quick Price Update. For Zen Cart 1.5.8a through 3.0.0 on PHP 7.4 through 8.5. No core file is changed and no table is added.

- Catalog > Quick Attribute Price Update: find attribute rows by product ID or ID ranges, by category with or without its subcategories, by option name and option value, by product name or model, and by product status; "Priced Rows Only"; sort products and page the results.
- Rows grouped under each product, with its base price, "Starting at" price, and links to the product, the Attributes Controller and Quick Price Update (when installed).
- Change the prefix, price and one-time price in the table and save every changed row with one button. Values are checked first; a row changed elsewhere after the page loaded is skipped and reported.
- Once per product touched: last-modified date stamped and the storefront price-sort value refreshed.
- Notes for what a row's price doesn't show by itself; a flag for products whose own price is 0 but which aren't priced by attributes.
- A link in Catalog > Attributes Controller's action menu.
