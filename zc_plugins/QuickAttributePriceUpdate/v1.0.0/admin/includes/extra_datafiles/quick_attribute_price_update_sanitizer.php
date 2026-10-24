<?php
/**
 * Quick Attribute Price Update -- tells core's admin request sanitizer about
 * the two free-text search boxes on Catalog > Quick Attribute Price Update.
 *
 * Without this, strict sanitizing runs htmlspecialchars() on them, so a search
 * for "Salt & Pepper" arrives as "Salt &amp; Pepper" and finds nothing. The
 * page parses both itself: the ID box accepts only digits, commas, spaces and
 * dashes, and the keyword box is escaped for SQL and on output. The price
 * fields need no rule: the default leaves digits, points, "+", "-" and "|"
 * alone, and the page refuses anything else.
 *
 * Plugin admin datafiles load after application_bootstrap.php requires the
 * sanitizer class and before init_sanitize.php runs it (1.5.8 through 3.0.0).
 *
 * @package  QuickAttributePriceUpdate
 * @license  GNU General Public License v2.0 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

if (class_exists('AdminRequestSanitizer', false)) {
    AdminRequestSanitizer::getInstance()->addComplexSanitization([
        // The rule core gives its own search boxes.
        'qapu_ids' => ['sanitizerType' => 'WORDS_AND_SYMBOLS_REGEX', 'method' => 'get', 'pages' => ['quick_attribute_price_update']],
        'qapu_kw' => ['sanitizerType' => 'WORDS_AND_SYMBOLS_REGEX', 'method' => 'get', 'pages' => ['quick_attribute_price_update']],
    ]);
}
