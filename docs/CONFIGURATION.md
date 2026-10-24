# Using Quick Attribute Price Update

There are no settings. Everything is on the page itself.

## Finding attribute rows

Each row is one attribute of one product: one option value (for example *Size: 1 inch*) as that product offers it.

| Field | What it does |
|---|---|
| Product IDs | One or more IDs or ranges: `12`, `12, 15`, `40-55`, `12 15 40-55`. Exact matches only. Up to 1,000 IDs per search. |
| Category | Products in that category, including products linked into it from elsewhere. **Include Subcategories** adds everything below it. |
| Option Name | Only rows for that option (for example *Size*). The list holds the options some product uses, by name: two options with the same name (say, two both called *Size:*) are one entry and are searched together. |
| Option Value | Only rows for that value (for example *1 inch*), under the option name it's listed with. Values go by name too: *1 inch* saved as several option values is one entry that finds them all. Choosing an option name narrows this list to its values; choosing a value under All Option Names picks its option name for you. Text and file options have no values; pick the option name instead. |
| Product Name or Model | Every word must appear in the product's name (in the admin's language) or its model. |
| Product Status | Active and Inactive, Active Only, or Inactive Only. **Priced Rows Only** hides rows whose price and one-time price are both 0. |
| Sort Products By / Direction | Sort Order (the default, like Categories/Products), ID, Name or Model. Within a product, rows follow the order the storefront shows the options in. |
| Rows Per Page | 25, 50, 100 or 200 attribute rows. |

All the fields combine: category Pins with its subcategories, option Size, value 1 inch, for example. **Reset** clears them. The search is part of the page address, so you can bookmark one.

## The product lines

Each product's rows sit under a line with its ID and name, model, **Base price** (the product's own price), and **Starting at**: the lowest price shoppers see, as the storefront sorts it. Saving one of the product's rows refreshes that figure. The links open the product's edit page, the product in **Catalog > Attributes Controller**, and, when Quick Price Update is installed and you may use it, the product there to change its base price.

Flags on the product line:

- **Priced by Attributes**: the product's price comes from its attributes (plus its base price, which is often 0).
- **Inactive**.
- **Base Price 0, Not Priced by Attributes**: shoppers may see a price of 0 before choosing options. Usually the product should be set to Priced by Attributes on its edit page.

## Changing prices

Change the **Prefix**, **Price** or **One-Time Price**. A changed box turns bold and its row turns yellow; the count beside **Save Changes** says how many rows will be saved. Click **Save Changes** once you're done with the page; going to another page of results first will lose the changes (your browser warns you).

- **Prefix**: `+` adds the price, `-` subtracts it, and blank adds it too (that's how Zen Cart treats a blank prefix).
- **Price** and **One-Time Price**: up to 11 digits before the decimal point and 4 after; not negative. `12`, `12.5`, `12.50`, `.99`, `1,234.50`. Not `12,50` (comma as decimal point), `$12` or `12.34567`.

After saving, the page tells you how many rows and products were updated and lists any rows that weren't:

- **A value the field can't hold**: the row comes back highlighted with what you typed, so you can fix it and save again.
- **Changed somewhere else after this page was loaded**: someone changed the row meanwhile. Nothing is overwritten; the row shows the current values.
- **No longer exists**: the attribute was deleted meanwhile.

For every product with a saved row, the product's last-modified date is stamped and Zen Cart's price-sort value is refreshed, once.

## The notes column

The page changes three fields of an attribute. A note means another part of the attribute also affects what shoppers pay or see; edit those in the Attributes Controller:

- **Free**: the attribute is free when the product is.
- **Display Only**: shown but can't be chosen.
- **Default**: preselected.
- **Not Discounted**: specials and sales don't apply to this price.
- **Not in Base Price**: not counted in the product's "Starting at" price.
- **Price Factor**, **Quantity Prices**, **Word or Letter Pricing**: further pricing set on the attribute.
- **Minus on Priced by Attributes**: a `-` price on a priced-by-attributes product. Some Zen Cart versions show such a price oddly on the product page, though the cart total is right.
