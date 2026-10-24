# Customizing Quick Attribute Price Update

## Wording

All text is in two array-format language files inside the plugin:

- `admin/includes/languages/english/lang.quick_attribute_price_update.php`: the page.
- `admin/includes/languages/english/extra_definitions/lang.quick_attribute_price_update_menu.php`: the menu label and the Attributes Controller links, which other admin pages need.

To translate, copy both into a folder named for your admin language (for example `languages/french/`) and translate the values.

## Notifiers for add-ons

The page raises ten notifiers so another plugin (Quick Attribute Price Update Pro, or your own) can extend it without editing it:

| Notifier | Parameters | Use |
|---|---|---|
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_ACTION` | `$action` (the `action` query value), `&$filters` | Handle an action of your own before the page's; redirect when done. |
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_FILTERS_FORM` | `$filters`, `&$html` | Append form groups to the search form. Read your fields from `$_GET` yourself. |
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_SEARCH` | `$filters`, `&$extraWhere` (array) | Add SQL conditions. Each must be a complete fragment you have escaped yourself; tables are aliased `pa` (products_attributes), `p` (products) and `pd` (products_description). |
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_ROWS` | `$filters`, `&$found` (`['rows', 'total', 'page', 'pages']`) | Add data of your own to the rows about to be shown, keyed by `$row['products_attributes_id']`: one query for the whole page. Fires after the search, before any output. |
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_PANELS` | `$filters`, `&$html`, `&$found` | Print panels between the search form and the table. They sit outside the page's forms, so a panel brings its own form. |
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_TABLE_HEADINGS` | `$filters`, `&$headings` (`['before' => [], 'after' => []]`) | Add columns: `before` the Option Name column, `after` One-Time Price. Each entry is `['text' => heading html, 'class' => css classes]`. The product lines' colspan grows to match. |
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_TABLE_CELLS` | `$row`, `&$cells` (same shape) | One row's cells for those columns: every row must get the same number in the same places. An input that should be saved with the page takes `class="... qapu-input"`, `data-was="<value as shown>"` and `data-row="qapuRow<products_attributes_id>"`, so change tracking counts it and unchanged rows are left out of the post. |
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_SAVE_RESULT` | `$filters`, `&$result` | Save your own columns after the page's save, and add to its lists: `updated`, `conflict`, `missing` (attribute row IDs), `products` (product IDs) and `invalid` (id => typed values; an entry without `prefix`/`price`/`onetime` doesn't mark the page's own boxes). The page's messages then cover both. |
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_ROW_UPDATED` | `['products_attributes_id', 'products_id', 'prefix_old', 'prefix_new', 'price_old', 'price_new', 'onetime_old', 'onetime_new']` | Fires once per saved row, after every write and price-sort refresh (for example, to keep a change history). Prices are strings with 4 decimal places. |
| `NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_SAVE_END` | a list of the same arrays | Fires once per save that changed anything. |

Keep any fields you add on the search form prefixed `qapu<letters>_` (for example `qapup_mfr`). The page keeps any such query parameter (up to 200 characters) through paging links and the redirect after saving, in `$filters['extra']`; validate it yourself. The page's own fields are `qapu_...`.
