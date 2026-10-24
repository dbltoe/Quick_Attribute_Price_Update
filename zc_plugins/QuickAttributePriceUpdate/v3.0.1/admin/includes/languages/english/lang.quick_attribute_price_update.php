<?php
/**
 * Quick Attribute Price Update -- Catalog > Quick Attribute Price Update page
 * strings (English).
 *
 * Titles, column headings and buttons are Title Case; help text and messages
 * are sentences.
 *
 * @package  QuickAttributePriceUpdate
 * @license  GNU General Public License v2.0 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */

$define = [
    'HEADING_TITLE' => 'Quick Attribute Price Update',
    'QAPU_INTRO' => 'Find attribute prices by product, category, option name or option value, change them in the table, then save them all at once. Prices are in your default currency (%s). Only the rows you change are written, and every product touched gets its storefront price sort refreshed.',

    // The search form.
    'QAPU_LEGEND_FIND' => 'Find Attribute Prices',
    'QAPU_LABEL_IDS' => 'Product IDs',
    'QAPU_HELP_IDS' => 'One or more IDs or ranges, e.g. 12, 15, 40-55',
    'QAPU_LABEL_CATEGORY' => 'Category',
    'QAPU_CATEGORY_ANY' => 'All Categories',
    'QAPU_LABEL_SUBCATEGORIES' => 'Include Subcategories',
    'QAPU_LABEL_OPTION' => 'Option Name',
    'QAPU_OPTION_ANY' => 'All Option Names',
    'QAPU_LABEL_VALUE' => 'Option Value',
    'QAPU_VALUE_ANY' => 'All Option Values',
    // A list of IDs from a link or bookmark that no single name in the lists stands for.
    'QAPU_LIST_BY_ID' => '(IDs %s)',
    'QAPU_LABEL_KEYWORDS' => 'Product Name or Model',
    'QAPU_LABEL_STATUS' => 'Product Status',
    'QAPU_STATUS_ANY' => 'Active and Inactive',
    'QAPU_STATUS_ON' => 'Active Only',
    'QAPU_STATUS_OFF' => 'Inactive Only',
    'QAPU_LABEL_PRICED' => 'Priced Rows Only',
    'QAPU_LABEL_SORT' => 'Sort Products By',
    'QAPU_SORT_SORT' => 'Sort Order',
    'QAPU_SORT_ID' => 'ID',
    'QAPU_SORT_NAME' => 'Name',
    'QAPU_SORT_MODEL' => 'Model',
    'QAPU_LABEL_DIRECTION' => 'Direction',
    'QAPU_DIR_ASC' => 'Ascending',
    'QAPU_DIR_DESC' => 'Descending',
    'QAPU_LABEL_PER_PAGE' => 'Rows Per Page',
    'QAPU_BUTTON_FIND' => 'Find Attribute Prices',
    'QAPU_BUTTON_RESET' => 'Reset',

    // The table.
    'QAPU_CAPTION' => 'Attribute rows found: %1$s. Page %2$s of %3$s.',
    'QAPU_HEADING_OPTION' => 'Option Name',
    'QAPU_HEADING_VALUE' => 'Option Value',
    'QAPU_HEADING_PREFIX' => 'Prefix',
    'QAPU_HEADING_PRICE' => 'Price',
    'QAPU_HEADING_ONETIME' => 'One-Time Price',
    'QAPU_HEADING_NOTES' => 'Notes',
    'QAPU_PRODUCT_LINE' => 'Product %1$s: %2$s',
    'QAPU_PRODUCT_MODEL' => 'Model: %s',
    'QAPU_PRODUCT_BASE' => 'Base price: %s',
    'QAPU_PRODUCT_FROM' => 'Starting at: %s',
    'QAPU_HELP_FROM' => 'The lowest price shoppers see for this product, as the storefront sorts it. It\'s refreshed whenever this page saves one of the product\'s rows.',
    'QAPU_LINK_EDIT_PRODUCT' => 'Edit Product',
    'QAPU_LINK_ATTRIBUTES' => 'Attributes Controller',
    'QAPU_LINK_BASE_PRICE' => 'Edit Base Price',
    'QAPU_NO_NAME' => '(no name)',
    'QAPU_OPTION_MISSING' => '(option ID %s)',
    'QAPU_VALUE_MISSING' => '(value ID %s)',
    'QAPU_VALUE_TEXT' => '(text field)',
    'QAPU_VALUE_FILE' => '(file upload)',
    'QAPU_PREFIX_PLUS' => '+ (Add)',
    'QAPU_PREFIX_MINUS' => '- (Subtract)',
    'QAPU_PREFIX_BLANK' => '(Blank: Adds)',
    'QAPU_LABEL_PREFIX_FOR' => 'Prefix for %s',
    'QAPU_LABEL_PRICE_FOR' => 'Price for %s',
    'QAPU_LABEL_ONETIME_FOR' => 'One-time price for %s',
    'QAPU_ROW_DESCRIPTION' => '%1$s: %2$s on %3$s',
    'QAPU_NOTE_PRICED_BY_ATTRIBUTES' => 'Priced by Attributes',
    'QAPU_NOTE_INACTIVE' => 'Inactive',
    'QAPU_NOTE_ZERO_BASE' => 'Base Price 0, Not Priced by Attributes',
    'QAPU_HELP_ZERO_BASE' => 'This product\'s own price is 0 and it isn\'t set to Priced by Attributes, so shoppers may see a price of 0 before choosing options.',
    'QAPU_NOTE_FREE' => 'Free',
    'QAPU_NOTE_DISPLAY_ONLY' => 'Display Only',
    'QAPU_NOTE_DEFAULT' => 'Default',
    'QAPU_NOTE_NOT_DISCOUNTED' => 'Not Discounted',
    'QAPU_NOTE_NOT_BASE' => 'Not in Base Price',
    'QAPU_NOTE_FACTOR' => 'Price Factor',
    'QAPU_NOTE_QTY_PRICES' => 'Quantity Prices',
    'QAPU_NOTE_WORDS' => 'Word or Letter Pricing',
    'QAPU_NOTE_MINUS' => 'Minus on Priced by Attributes',
    'QAPU_NOTE_HELP' => 'Notes name the parts of a row\'s price that this page doesn\'t change; edit those in the Attributes Controller. "Minus on Priced by Attributes": some Zen Cart versions show such a price oddly on the product page, though the cart total is right.',
    'QAPU_NONE_FOUND' => 'No attribute rows match.',
    'QAPU_BUTTON_SAVE' => 'Save Changes',
    'QAPU_CHANGED_COUNT' => 'Rows changed: %s',
    'QAPU_PAGE_PREVIOUS' => 'Previous Page',
    'QAPU_PAGE_NEXT' => 'Next Page',
    'QAPU_PAGINATION_LABEL' => 'Result Pages',
    'QAPU_RETRY_HELP' => 'Not saved: this row has a value the field can\'t hold.',

    // Search problems.
    'QAPU_ERROR_IDS_CHARACTERS' => 'Product IDs: use only numbers, commas, spaces and dashes, e.g. 12, 15, 40-55.',
    'QAPU_ERROR_IDS_RANGE' => 'Product IDs: a range must start with the smaller number, e.g. 40-55.',
    'QAPU_ERROR_IDS_TOO_MANY' => 'Product IDs: name no more than 1,000 IDs at a time.',

    // After saving.
    'QAPU_MSG_UPDATED' => 'Attribute rows updated: %1$s, on %2$s product(s).',
    'QAPU_MSG_NOTHING' => 'Nothing was changed, so nothing was saved.',
    'QAPU_MSG_INVALID' => 'Not saved, because a value isn\'t one the field can hold (prices: numbers with up to 4 decimal places, not negative; use the - prefix to subtract): attribute row ID(s) %s. They are highlighted below with what you typed.',
    'QAPU_MSG_CONFLICT' => 'Not saved, because the row changed somewhere else after this page was loaded: attribute row ID(s) %s. They now show the current values.',
    'QAPU_MSG_MISSING' => 'Not saved, because the attribute row no longer exists: attribute row ID(s) %s.',
    'QAPU_ACTIVITY_LOG' => 'Quick Attribute Price Update: updated attribute row ID(s) %1$s on product ID(s) %2$s.',
];

return $define;
