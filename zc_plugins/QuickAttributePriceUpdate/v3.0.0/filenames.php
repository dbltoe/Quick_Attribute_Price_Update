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
 * @license  GNU General Public License v2.0 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */

if (!defined('FILENAME_QUICK_ATTRIBUTE_PRICE_UPDATE')) {
    define('FILENAME_QUICK_ATTRIBUTE_PRICE_UPDATE', 'quick_attribute_price_update');
}
