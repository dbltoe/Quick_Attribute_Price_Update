<?php
/**
 * Quick Attribute Price Update -- everything the admin page and the Attributes
 * Controller link share: finding attribute rows, checking typed values, and
 * saving them.
 *
 * Written for one codebase on Zen Cart 1.5.8a through 3.0.0 and PHP 7.4
 * through 8.5: no union types, no match, no named arguments.
 *
 * @package  QuickAttributePriceUpdate
 * @license  GNU General Public License v2.0 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

class QuickAttributePriceUpdate
{
    public const VERSION = 'v3.0.0';

    /** Most product IDs one search may name, so a typo like 1-999999 can't build a huge query. */
    public const MAX_IDS = 1000;

    /**
     * Attribute rows per page. Each row posts four fields (prefix, price, one-time
     * price, original values), so 200 rows stay under PHP's default
     * max_input_vars of 1000.
     */
    public const PER_PAGE_CHOICES = [25, 50, 100, 200];
    public const PER_PAGE_DEFAULT = 50;

    /** Product order; within a product, rows follow core's option and attribute sort orders. */
    public const SORTS = [
        'sort' => 'p.products_sort_order, pd.products_name, p.products_id',
        'id' => 'p.products_id',
        'name' => 'pd.products_name, p.products_id',
        'model' => 'p.products_model, p.products_id',
    ];

    /** The three prefixes core's Attributes Controller offers. Only '-' subtracts. */
    public const PREFIXES = ['+', '-', ''];

    /** @var array|null */
    protected static $currency = null;

    /** @var bool|null */
    protected static $canAccess = null;

    // -----
    // Typed input.
    //

    /**
     * "12, 15, 40-55" => [12, 15, 40, 41, ... 55]. Spaces work as separators too.
     * Anything else in the box, a reversed range, or more than MAX_IDS IDs adds a
     * message to $errors and contributes nothing.
     *
     * @param string $raw
     * @param array $errors language-constant names, appended to
     * @return int[] unique, ascending
     */
    public static function parseIdList($raw, array &$errors)
    {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return [];
        }
        if (!preg_match('/^[0-9,\s-]+$/', $raw)) {
            $errors[] = 'QAPU_ERROR_IDS_CHARACTERS';
            return [];
        }
        $ids = [];
        foreach (preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY) as $part) {
            if (preg_match('/^(\d{1,10})$/', $part, $m)) {
                $ids[(int)$m[1]] = true;
            } elseif (preg_match('/^(\d{1,10})-(\d{1,10})$/', $part, $m)) {
                $from = (int)$m[1];
                $to = (int)$m[2];
                if ($from > $to) {
                    $errors[] = 'QAPU_ERROR_IDS_RANGE';
                    return [];
                }
                if ($to - $from >= self::MAX_IDS) {
                    $errors[] = 'QAPU_ERROR_IDS_TOO_MANY';
                    return [];
                }
                for ($i = $from; $i <= $to; $i++) {
                    $ids[$i] = true;
                }
            } else {
                $errors[] = 'QAPU_ERROR_IDS_CHARACTERS';
                return [];
            }
            if (count($ids) > self::MAX_IDS) {
                $errors[] = 'QAPU_ERROR_IDS_TOO_MANY';
                return [];
            }
        }
        unset($ids[0]);
        $ids = array_keys($ids);
        sort($ids);
        return $ids;
    }

    /**
     * A typed or stored number in one canonical form, so "12.5", "12.50" and the
     * database's "12.5000" compare equal: "12.5000". Accepts "1,234.50" (comma
     * thousands only, so "12,50" is refused rather than misread). Up to 11 digits
     * before the point and 4 after, matching the decimal(15,4) price columns.
     *
     * @param mixed $raw
     * @param bool $allowNegative
     * @return string|null null if it isn't a number the field can hold
     */
    public static function canonicalNumber($raw, $allowNegative = false)
    {
        if (!is_scalar($raw)) {
            return null;
        }
        $s = trim((string)$raw);
        if (preg_match('/^-?\d{1,3}(,\d{3})+(\.\d*)?$/', $s)) {
            $s = str_replace(',', '', $s);
        }
        if (!preg_match('/^(-?)(\d*)(?:\.(\d*))?$/', $s, $m) || ($m[2] === '' && ($m[3] ?? '') === '')) {
            return null;
        }
        $negative = $m[1] === '-';
        $int = ltrim($m[2], '0');
        $dec = rtrim($m[3] ?? '', '0');
        if (strlen($int) > 11 || strlen($dec) > 4) {
            return null;
        }
        $int = $int === '' ? '0' : $int;
        $dec = str_pad($dec, 4, '0');
        if ($int === '0' && $dec === '0000') {
            $negative = false;
        }
        if ($negative && !$allowNegative) {
            return null;
        }
        return ($negative ? '-' : '') . $int . '.' . $dec;
    }

    /**
     * A canonical number for an input box: trailing zeros trimmed, but at least
     * $minDecimals places ("12.50", "12.3456").
     *
     * @param string $canonical
     * @param int $minDecimals
     * @return string
     */
    public static function displayNumber($canonical, $minDecimals = 2)
    {
        $c = self::canonicalNumber($canonical, true);
        if ($c === null) {
            return (string)$canonical;
        }
        [$int, $dec] = explode('.', $c);
        $dec = rtrim($dec, '0');
        if (strlen($dec) < $minDecimals) {
            $dec = str_pad($dec, $minDecimals, '0');
        }
        return $dec === '' ? $int : $int . '.' . $dec;
    }

    /**
     * A typed or stored prefix: '+', '-' or '' (core treats blank like '+').
     *
     * @param mixed $raw
     * @return string|null null if it isn't one of the three
     */
    public static function canonicalPrefix($raw)
    {
        if (!is_scalar($raw)) {
            return null;
        }
        $p = trim((string)$raw);
        return in_array($p, self::PREFIXES, true) ? $p : null;
    }

    /**
     * The page's record of a row as it was loaded: "prefix|price|one-time".
     *
     * @param string $prefix
     * @param string $price canonical
     * @param string $onetime canonical
     * @return string
     */
    public static function wasValue($prefix, $price, $onetime)
    {
        return $prefix . '|' . $price . '|' . $onetime;
    }

    /**
     * The reverse of wasValue(), checked: null if it was tampered with.
     *
     * @param mixed $raw
     * @return array|null ['prefix' => ..., 'price' => canonical, 'onetime' => canonical]
     */
    public static function parseWas($raw)
    {
        if (!is_string($raw) || substr_count($raw, '|') !== 2) {
            return null;
        }
        [$prefix, $price, $onetime] = explode('|', $raw);
        $prefix = self::canonicalPrefix($prefix);
        $price = self::canonicalNumber($price, true);
        $onetime = self::canonicalNumber($onetime, true);
        if ($prefix === null || $price === null || $onetime === null) {
            return null;
        }
        return ['prefix' => $prefix, 'price' => $price, 'onetime' => $onetime];
    }

    // -----
    // The store's default currency, for the price boxes.
    //

    /**
     * Code and symbols of DEFAULT_CURRENCY, read once per request. Attribute
     * prices are stored in the default currency.
     *
     * @return array ['code' => 'USD', 'prefix' => '$', 'suffix' => '']
     */
    public static function currency()
    {
        if (self::$currency !== null) {
            return self::$currency;
        }
        global $db;
        $code = defined('DEFAULT_CURRENCY') ? strtoupper((string)DEFAULT_CURRENCY) : '';
        $left = '';
        $right = '';
        if ($code !== '') {
            $sql = $db->bindVars(
                'SELECT symbol_left, symbol_right FROM ' . TABLE_CURRENCIES . ' WHERE code = :code: LIMIT 1',
                ':code:',
                $code,
                'stringIgnoreNull'
            );
            $row = $db->Execute($sql);
            if (!$row->EOF) {
                $left = (string)$row->fields['symbol_left'];
                $right = (string)$row->fields['symbol_right'];
            }
        }
        return self::$currency = self::currencyFor($code, $left, $right, defined('CHARSET') ? CHARSET : 'utf-8');
    }

    /**
     * Pure part of currency(): symbols decoded, the right-hand one shown only
     * when there's no left-hand one.
     *
     * @param string $code
     * @param string $symbolLeft
     * @param string $symbolRight
     * @param string $charset
     * @return array
     */
    public static function currencyFor($code, $symbolLeft, $symbolRight, $charset = 'utf-8')
    {
        $left = trim(html_entity_decode((string)$symbolLeft, ENT_QUOTES, $charset));
        $right = trim(html_entity_decode((string)$symbolRight, ENT_QUOTES, $charset));
        return [
            'code' => strtoupper(trim((string)$code)),
            'prefix' => $left,
            'suffix' => $left === '' ? $right : '',
        ];
    }

    // -----
    // Who may use it.
    //

    /**
     * Whether the signed-in admin may open the page (superusers always; others
     * when their profile includes it). Read once per request.
     *
     * @return bool
     */
    public static function canAccess()
    {
        if (self::$canAccess === null) {
            self::$canAccess = zen_is_superuser() || check_page(FILENAME_QUICK_ATTRIBUTE_PRICE_UPDATE, []);
        }
        return self::$canAccess;
    }

    /**
     * Whether Quick Price Update (the base-price page) is installed and this
     * admin may open it. Its page constant is defined only while it's installed.
     *
     * @return bool
     */
    public static function canAccessQuickPriceUpdate()
    {
        return defined('FILENAME_QUICK_PRICE_UPDATE')
            && (zen_is_superuser() || check_page(FILENAME_QUICK_PRICE_UPDATE, []));
    }

    // -----
    // Finding attribute rows.
    //

    /**
     * Normalized filters from the query string.
     *
     * @param array $get
     * @return array
     */
    public static function filtersFrom(array $get)
    {
        $perPage = (int)($get['qapu_per'] ?? self::PER_PAGE_DEFAULT);
        if (!in_array($perPage, self::PER_PAGE_CHOICES, true)) {
            $perPage = self::PER_PAGE_DEFAULT;
        }
        $sort = (string)($get['qapu_sort'] ?? 'sort');
        if (!isset(self::SORTS[$sort])) {
            $sort = 'sort';
        }
        $status = (string)($get['qapu_status'] ?? '');
        if ($status !== '1' && $status !== '0') {
            $status = '';
        }
        $digits = static function ($v) {
            $v = (string)$v;
            return ($v !== '' && strlen($v) <= 10 && ctype_digit($v)) ? $v : '';
        };
        return [
            'ids' => trim((string)($get['qapu_ids'] ?? '')),
            'cid' => $digits($get['qapu_cid'] ?? ''),
            'sub' => !empty($get['qapu_sub']) ? '1' : '',
            'oid' => $digits($get['qapu_oid'] ?? ''),
            'vid' => $digits($get['qapu_vid'] ?? ''),
            'kw' => trim((string)($get['qapu_kw'] ?? '')),
            'status' => $status,
            'priced' => !empty($get['qapu_priced']) ? '1' : '',
            'sort' => $sort,
            'dir' => (($get['qapu_dir'] ?? '') === 'desc') ? 'desc' : 'asc',
            'per' => $perPage,
        ];
    }

    /**
     * The filters as query-string parameters, for links and redirects that keep
     * the same search. Empty ones are left out.
     *
     * @param array $filters
     * @return string
     */
    public static function filterParams(array $filters)
    {
        $map = [
            'qapu_ids' => $filters['ids'], 'qapu_cid' => $filters['cid'], 'qapu_sub' => $filters['sub'],
            'qapu_oid' => $filters['oid'], 'qapu_vid' => $filters['vid'],
            'qapu_kw' => $filters['kw'], 'qapu_status' => $filters['status'], 'qapu_priced' => $filters['priced'],
            'qapu_sort' => $filters['sort'] === 'sort' ? '' : $filters['sort'],
            'qapu_dir' => $filters['dir'] === 'asc' ? '' : $filters['dir'],
            'qapu_per' => (int)$filters['per'] === self::PER_PAGE_DEFAULT ? '' : (string)$filters['per'],
        ];
        $parts = [];
        foreach ($map as $k => $v) {
            if ((string)$v !== '') {
                $parts[] = $k . '=' . rawurlencode((string)$v);
            }
        }
        return implode('&', $parts);
    }

    /**
     * Category $cid and, with $withSubcategories, everything below it.
     *
     * @param int $cid
     * @param bool $withSubcategories
     * @return int[]
     */
    public static function categoryIds($cid, $withSubcategories)
    {
        $ids = [(int)$cid];
        if ($withSubcategories) {
            $sub = [];
            zen_get_subcategories($sub, (int)$cid);
            foreach ($sub as $s) {
                $ids[] = (int)$s;
            }
        }
        return array_values(array_unique($ids));
    }

    /**
     * Every option name that some product uses, for the Option Name list.
     *
     * @return array [['id' => '3', 'text' => 'Size', 'type' => 0], ...]
     */
    public static function optionNames()
    {
        global $db;
        $languageId = (int)$_SESSION['languages_id'];
        $result = $db->Execute(
            'SELECT po.products_options_id, po.products_options_name, po.products_options_type'
            . ' FROM ' . TABLE_PRODUCTS_OPTIONS . ' po'
            . ' WHERE po.language_id = ' . $languageId
            . ' AND po.products_options_id IN (SELECT DISTINCT pa.options_id FROM ' . TABLE_PRODUCTS_ATTRIBUTES . ' pa)'
            . ' ORDER BY po.products_options_sort_order, po.products_options_name, po.products_options_id'
        );
        $out = [];
        foreach ($result as $row) {
            $out[] = [
                'id' => (string)(int)$row['products_options_id'],
                'text' => (string)$row['products_options_name'],
                'type' => (int)$row['products_options_type'],
            ];
        }
        return $out;
    }

    /**
     * Every option value that some product uses, with the option it belongs to,
     * for the Option Value list (the page shows only the chosen option's values).
     * Text and file options have no values of their own, so they don't appear.
     *
     * @return array [['id' => '7', 'text' => '1 inch', 'oid' => '3'], ...]
     */
    public static function optionValues()
    {
        global $db;
        $languageId = (int)$_SESSION['languages_id'];
        $result = $db->Execute(
            'SELECT DISTINCT pa.options_id, pa.options_values_id, pov.products_options_values_name, pov.products_options_values_sort_order'
            . ' FROM ' . TABLE_PRODUCTS_ATTRIBUTES . ' pa'
            . ' INNER JOIN ' . TABLE_PRODUCTS_OPTIONS_VALUES . ' pov ON (pov.products_options_values_id = pa.options_values_id AND pov.language_id = ' . $languageId . ')'
            . ' WHERE pa.options_values_id > 0'
            . ' ORDER BY pa.options_id, pov.products_options_values_sort_order, pov.products_options_values_name, pa.options_values_id'
        );
        $out = [];
        foreach ($result as $row) {
            $out[] = [
                'id' => (string)(int)$row['options_values_id'],
                'text' => (string)$row['products_options_values_name'],
                'oid' => (string)(int)$row['options_id'],
            ];
        }
        return $out;
    }

    /**
     * One page of attribute rows matching $filters, in product order and, within
     * a product, in the order the storefront shows its options.
     *
     * @param array $filters from filtersFrom()
     * @param int $page 1-based; clamped to the last page
     * @param array $errors language-constant names, appended to
     * @return array ['rows' => [...], 'total' => int, 'page' => int, 'pages' => int]
     */
    public static function search(array $filters, $page, array &$errors)
    {
        global $db, $zco_notifier;

        $languageId = (int)$_SESSION['languages_id'];
        $where = [];

        if ($filters['ids'] !== '') {
            $ids = self::parseIdList($filters['ids'], $errors);
            if ($ids === []) {
                return ['rows' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
            }
            $where[] = 'pa.products_id IN (' . implode(',', $ids) . ')';
        }

        if ($filters['cid'] !== '') {
            $cats = self::categoryIds((int)$filters['cid'], $filters['sub'] === '1');
            $where[] = 'pa.products_id IN (SELECT p2c.products_id FROM ' . TABLE_PRODUCTS_TO_CATEGORIES
                . ' p2c WHERE p2c.categories_id IN (' . implode(',', $cats) . '))';
        }

        if ($filters['oid'] !== '') {
            $where[] = 'pa.options_id = ' . (int)$filters['oid'];
        }
        if ($filters['vid'] !== '') {
            $where[] = 'pa.options_values_id = ' . (int)$filters['vid'];
        }

        if ($filters['kw'] !== '') {
            foreach (preg_split('/\s+/', $filters['kw'], -1, PREG_SPLIT_NO_EMPTY) as $word) {
                $like = "'%" . zen_db_input(addcslashes($word, '%_\\')) . "%'";
                $where[] = '(pd.products_name LIKE ' . $like . ' OR p.products_model LIKE ' . $like . ')';
            }
        }

        if ($filters['status'] !== '') {
            $where[] = 'p.products_status = ' . (int)$filters['status'];
        }

        if ($filters['priced'] === '1') {
            $where[] = '(pa.options_values_price <> 0 OR pa.attributes_price_onetime <> 0)';
        }

        // Quick Attribute Price Update Pro (or any other add-on) adds its own
        // conditions here, as complete SQL fragments that it has escaped itself.
        $extraWhere = [];
        $zco_notifier->notify('NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_SEARCH', $filters, $extraWhere);
        if (is_array($extraWhere)) {
            foreach ($extraWhere as $w) {
                $where[] = '(' . $w . ')';
            }
        }

        $from = ' FROM ' . TABLE_PRODUCTS_ATTRIBUTES . ' pa'
            . ' INNER JOIN ' . TABLE_PRODUCTS . ' p ON (p.products_id = pa.products_id)'
            . ' LEFT JOIN ' . TABLE_PRODUCTS_DESCRIPTION . ' pd ON (pd.products_id = pa.products_id AND pd.language_id = ' . $languageId . ')';
        $joinOptions = ' LEFT JOIN ' . TABLE_PRODUCTS_OPTIONS . ' po ON (po.products_options_id = pa.options_id AND po.language_id = ' . $languageId . ')'
            . ' LEFT JOIN ' . TABLE_PRODUCTS_OPTIONS_VALUES . ' pov ON (pov.products_options_values_id = pa.options_values_id AND pov.language_id = ' . $languageId . ')';
        $whereSql = $where === [] ? '' : ' WHERE ' . implode(' AND ', $where);

        $count = $db->Execute('SELECT COUNT(*) AS total' . $from . $whereSql);
        $total = $count->EOF ? 0 : (int)$count->fields['total'];
        $perPage = (int)$filters['per'];
        $pages = max(1, (int)ceil($total / $perPage));
        $page = min(max(1, (int)$page), $pages);

        $order = self::SORTS[$filters['sort']];
        if ($filters['dir'] === 'desc') {
            $order = implode(', ', array_map(static function ($col) {
                return trim($col) . ' DESC';
            }, explode(',', $order)));
        }
        // Keep each product's rows together, then the storefront's option order.
        $order .= ', pa.products_id, po.products_options_sort_order, pa.options_id,'
            . ' pa.products_options_sort_order, pov.products_options_values_sort_order, pa.options_values_id, pa.products_attributes_id';

        $sql = 'SELECT pa.products_attributes_id, pa.products_id, pa.options_id, pa.options_values_id,'
            . ' pa.price_prefix, pa.options_values_price, pa.attributes_price_onetime,'
            . ' pa.product_attribute_is_free, pa.attributes_display_only, pa.attributes_default,'
            . ' pa.attributes_discounted, pa.attributes_price_base_included,'
            . ' pa.attributes_price_factor, pa.attributes_price_factor_onetime,'
            . ' pa.attributes_qty_prices, pa.attributes_qty_prices_onetime,'
            . ' pa.attributes_price_words, pa.attributes_price_letters,'
            . ' po.products_options_name, po.products_options_type, pov.products_options_values_name,'
            . ' p.products_type, p.products_model, p.products_price, p.products_price_sorter,'
            . ' p.products_priced_by_attribute, p.product_is_free, p.product_is_call,'
            . ' p.products_status, p.master_categories_id, pd.products_name'
            . $from . $joinOptions . $whereSql
            . ' ORDER BY ' . $order
            . ' LIMIT ' . (($page - 1) * $perPage) . ', ' . $perPage;

        $rows = [];
        $result = $db->Execute($sql);
        foreach ($result as $row) {
            $rows[] = $row;
        }

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    /**
     * The notes a row's price can't show by itself, as language-constant names.
     *
     * @param array $row a search() row
     * @return string[]
     */
    public static function rowNotes(array $row)
    {
        $notes = [];
        if ((int)$row['product_attribute_is_free'] === 1) {
            $notes[] = 'QAPU_NOTE_FREE';
        }
        if ((int)$row['attributes_display_only'] === 1) {
            $notes[] = 'QAPU_NOTE_DISPLAY_ONLY';
        }
        if ((int)$row['attributes_default'] === 1) {
            $notes[] = 'QAPU_NOTE_DEFAULT';
        }
        if ((int)$row['attributes_discounted'] === 0) {
            $notes[] = 'QAPU_NOTE_NOT_DISCOUNTED';
        }
        if ((int)$row['attributes_price_base_included'] === 0) {
            $notes[] = 'QAPU_NOTE_NOT_BASE';
        }
        if ((float)$row['attributes_price_factor'] != 0 || (float)$row['attributes_price_factor_onetime'] != 0) {
            $notes[] = 'QAPU_NOTE_FACTOR';
        }
        if (trim((string)$row['attributes_qty_prices']) !== '' || trim((string)$row['attributes_qty_prices_onetime']) !== '') {
            $notes[] = 'QAPU_NOTE_QTY_PRICES';
        }
        if ((float)$row['attributes_price_words'] != 0 || (float)$row['attributes_price_letters'] != 0) {
            $notes[] = 'QAPU_NOTE_WORDS';
        }
        if ((string)$row['price_prefix'] === '-' && (int)$row['products_priced_by_attribute'] === 1) {
            $notes[] = 'QAPU_NOTE_MINUS';
        }
        return $notes;
    }

    /**
     * Whether a product shows shoppers a $0 price it probably shouldn't: its own
     * price is 0, it isn't priced by attributes, and it isn't free or call-for-price.
     *
     * @param array $row a search() row
     * @return bool
     */
    public static function zeroBaseWarning(array $row)
    {
        return self::canonicalNumber($row['products_price'], true) === '0.0000'
            && (int)$row['products_priced_by_attribute'] === 0
            && (int)$row['product_is_free'] === 0
            && (int)$row['product_is_call'] === 0;
    }

    // -----
    // Saving.
    //

    /**
     * Writes every attribute row whose prefix, price or one-time price was
     * changed on the page.
     *
     * A row is skipped, and reported, when a value isn't one the field can hold,
     * when the row no longer exists, or when it changed in the database after the
     * page was loaded. The UPDATE itself names the loaded values in its WHERE, so
     * it can't overwrite someone else's edit, and a fresh read afterwards tells
     * which of the three happened. (Affected-row counts aren't used: on 3.0 an
     * observer of the query notifier may run queries of its own in between.)
     *
     * Then, once per product touched: products_last_modified, and the price
     * sorter that keeps the storefront's price sort and "Starting at" right.
     *
     * @param array $prefixes products_attributes_id => typed prefix
     * @param array $prices products_attributes_id => typed price
     * @param array $onetimes products_attributes_id => typed one-time price
     * @param array $was products_attributes_id => wasValue() as the page loaded it
     * @return array ['updated' => int[] attribute IDs, 'products' => int[] product IDs,
     *                'invalid' => [id => ['prefix' => raw, 'price' => raw, 'onetime' => raw]],
     *                'conflict' => int[], 'missing' => int[]]
     */
    public static function save(array $prefixes, array $prices, array $onetimes, array $was)
    {
        global $db, $zco_notifier;

        $out = ['updated' => [], 'products' => [], 'invalid' => [], 'conflict' => [], 'missing' => []];
        $changes = [];
        foreach ($was as $aid => $original) {
            $aid = (int)$aid;
            if ($aid <= 0) {
                continue;
            }
            $rawPrefix = $prefixes[$aid] ?? null;
            $rawPrice = $prices[$aid] ?? null;
            $rawOnetime = $onetimes[$aid] ?? null;
            $old = self::parseWas($original);
            $newPrefix = $rawPrefix === null ? ($old['prefix'] ?? null) : self::canonicalPrefix($rawPrefix);
            $newPrice = $rawPrice === null ? ($old['price'] ?? null) : self::canonicalNumber($rawPrice);
            $newOnetime = $rawOnetime === null ? ($old['onetime'] ?? null) : self::canonicalNumber($rawOnetime);
            if ($old === null || $newPrefix === null || $newPrice === null || $newOnetime === null) {
                $out['invalid'][$aid] = [
                    'prefix' => is_scalar($rawPrefix) ? (string)$rawPrefix : '',
                    'price' => is_scalar($rawPrice) ? (string)$rawPrice : '',
                    'onetime' => is_scalar($rawOnetime) ? (string)$rawOnetime : '',
                ];
                continue;
            }
            if ($newPrefix === $old['prefix'] && $newPrice === $old['price'] && $newOnetime === $old['onetime']) {
                continue;
            }

            $sql = 'UPDATE ' . TABLE_PRODUCTS_ATTRIBUTES
                . ' SET price_prefix = :newPrefix:, options_values_price = :newPrice:, attributes_price_onetime = :newOnetime:'
                . ' WHERE products_attributes_id = :aid:'
                . ' AND price_prefix = :oldPrefix:'
                . ' AND options_values_price = CAST(:oldPrice: AS DECIMAL(15,4))'
                . ' AND attributes_price_onetime = CAST(:oldOnetime: AS DECIMAL(15,4))';
            $sql = $db->bindVars($sql, ':newPrefix:', $newPrefix, 'stringIgnoreNull');
            $sql = $db->bindVars($sql, ':newPrice:', $newPrice, 'currency');
            $sql = $db->bindVars($sql, ':newOnetime:', $newOnetime, 'currency');
            $sql = $db->bindVars($sql, ':aid:', $aid, 'integer');
            $sql = $db->bindVars($sql, ':oldPrefix:', $old['prefix'], 'stringIgnoreNull');
            $sql = $db->bindVars($sql, ':oldPrice:', $old['price'], 'currency');
            $sql = $db->bindVars($sql, ':oldOnetime:', $old['onetime'], 'currency');
            $db->Execute($sql);

            // Fresh read: the same SELECT may already sit in core's query cache.
            $check = $db->bindVars(
                'SELECT products_id, price_prefix, options_values_price, attributes_price_onetime FROM ' . TABLE_PRODUCTS_ATTRIBUTES
                . ' WHERE products_attributes_id = :aid:',
                ':aid:',
                $aid,
                'integer'
            );
            $current = $db->Execute($check, null, false, 0, true);
            if ($current->EOF) {
                $out['missing'][] = $aid;
                continue;
            }
            if ((string)$current->fields['price_prefix'] !== $newPrefix
                || self::canonicalNumber($current->fields['options_values_price'], true) !== $newPrice
                || self::canonicalNumber($current->fields['attributes_price_onetime'], true) !== $newOnetime) {
                $out['conflict'][] = $aid;
                continue;
            }

            $pid = (int)$current->fields['products_id'];
            $out['updated'][] = $aid;
            $out['products'][$pid] = $pid;
            $changes[] = [
                'products_attributes_id' => $aid,
                'products_id' => $pid,
                'prefix_old' => $old['prefix'], 'prefix_new' => $newPrefix,
                'price_old' => $old['price'], 'price_new' => $newPrice,
                'onetime_old' => $old['onetime'], 'onetime_new' => $newOnetime,
            ];
        }

        foreach ($out['products'] as $pid) {
            $db->Execute($db->bindVars(
                'UPDATE ' . TABLE_PRODUCTS . ' SET products_last_modified = now() WHERE products_id = :pid:',
                ':pid:',
                $pid,
                'integer'
            ));
            // Keeps the storefront's price sort and "Starting at" prices right.
            zen_update_products_price_sorter($pid);
        }
        $out['products'] = array_values($out['products']);

        foreach ($changes as $change) {
            $zco_notifier->notify('NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_ROW_UPDATED', $change);
        }
        if ($changes !== []) {
            $zco_notifier->notify('NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_SAVE_END', $changes);
        }
        return $out;
    }
}
