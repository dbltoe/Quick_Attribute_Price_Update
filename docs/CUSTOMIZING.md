# Customizing Quick Attribute Price Update

## Wording

All text is in two array-format language files inside the plugin:

- `admin/includes/languages/english/lang.quick_attribute_price_update.php`: the page.
- `admin/includes/languages/english/extra_definitions/lang.quick_attribute_price_update_menu.php`: the menu label and the Attributes Controller links, which other admin pages need.

To translate, copy both into a folder named for your admin language (for example `languages/french/`) and translate the values.

## Notifiers for add-ons

The page raises five notifiers so another plugin (Quick Attribute Price Update Pro, or your own) can extend it without editing it:

| Notifier | Parameters | Use |
|---|---|---|
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_ACTION` | `$action` (the `action` query value), `&$filters` | Handle an action of your own before the page's; redirect when done. |
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_FILTERS_FORM` | `$filters`, `&$html` | Append form groups to the search form. Read your fields from `$_GET` yourself. |
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_SEARCH` | `$filters`, `&$extraWhere` (array) | Add SQL conditions. Each must be a complete fragment you have escaped yourself; tables are aliased `pa` (products_attributes), `p` (products) and `pd` (products_description). |
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_ROW_UPDATED` | `['products_attributes_id', 'products_id', 'prefix_old', 'prefix_new', 'price_old', 'price_new', 'onetime_old', 'onetime_new']` | Fires once per saved row, after every write and price-sort refresh (for example, to keep a change history). Prices are strings with 4 decimal places. |
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_SAVE_END` | a list of the same arrays | Fires once per save that changed anything. |

Keep any fields you add on the search form prefixed (for example `qapup_`) so they don't collide with the page's own `qapu_` fields.
