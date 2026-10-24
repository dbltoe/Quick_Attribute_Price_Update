# Compatibility

| | Supported |
|---|---|
| Zen Cart | 1.5.8a, 2.0.x, 2.1.0, 2.2.x, 2.3, 3.0.0 |
| PHP | 7.4 through 8.5, within what your Zen Cart version supports |
| Core files changed | None |
| Database | No tables or columns added; one admin-menu record |

## Why it works on every version

- **The fields it writes** (`price_prefix`, `options_values_price`, `attributes_price_onetime` in `products_attributes`) have the same types on every version above, and the price-sort refresh (`zen_update_products_price_sorter()`) is core's own, the same one the Attributes Controller calls.
- **The Attributes Controller link** uses `NOTIFY_ATTRIBUTE_CONTROLLER_ADDITIONAL_ACTIONS_DROPDOWN_UPPER` in `admin/attributes_controller.php`, which exists with the same parameters on every version above.
- **The page name** is defined in `admin/includes/extra_datafiles/` as well as in the plugin's root `filenames.php`, because Zen Cart before 2.2.0 doesn't load the root one.
- **The search boxes** are registered with core's admin request sanitizer, scoped to this page, so `&` and quotes reach the search as typed.

## Zen Cart 2.0 and later: wholesale prices

Zen Cart 2.0 added a wholesale price to each attribute (`options_values_price_w`). This page doesn't show or change it; the Attributes Controller still does.

## Zen Cart 1.5.8 (not 1.5.8a)

Plain 1.5.8 has the same notifier and fields, but testing is on 1.5.8a (the 1.5.8 maintenance line). Zen Cart recommends 1.5.8a over 1.5.8 in any case.

## Other plugins

- **Quick Price Update** (base prices and quantities): when it's installed, each product line here links to it. The two don't depend on each other.
- **Database I/O Manager (DbIo)** has a ProductsAttribsRaw export and import of every attribute field, for whole-catalog spreadsheet work. It and this page can be used on the same store.
- A plugin that replaces `admin/attributes_controller.php` with a copy lacking the notifier only loses the link there; Catalog > Quick Attribute Price Update still works.
