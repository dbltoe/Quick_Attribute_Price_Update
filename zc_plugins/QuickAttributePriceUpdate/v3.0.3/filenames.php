<?php
/**
 * Quick Attribute Price Update -- page name constant.
 *
 * Core auto-loads a plugin's root filenames.php only from Zen Cart 2.2.0, so
 * admin/includes/extra_datafiles/quick_attribute_price_update_filenames.php
 * requires this file as well (1.5.8 through 2.1). The define is guarded, so
 * loading it twice on 2.2.0+ is harmless.
 *
 * @package  QuickAttributePriceUpdate
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if (!defined('FILENAME_QUICK_ATTRIBUTE_PRICE_UPDATE')) {
    define('FILENAME_QUICK_ATTRIBUTE_PRICE_UPDATE', 'quick_attribute_price_update');
}
