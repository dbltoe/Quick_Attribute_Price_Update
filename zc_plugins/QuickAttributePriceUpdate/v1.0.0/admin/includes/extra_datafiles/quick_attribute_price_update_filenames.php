<?php
/**
 * Quick Attribute Price Update -- loads the page name constant on 1.5.8
 * through 2.1, which don't read a plugin's root filenames.php. Plugin
 * extra_datafiles have been loaded on every release from 1.5.8 to 3.0.0.
 *
 * @package  QuickAttributePriceUpdate
 * @license  GNU General Public License v2.0 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

require dirname(__DIR__, 3) . '/filenames.php';
