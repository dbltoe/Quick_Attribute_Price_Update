<?php
/**
 * Quick Attribute Price Update -- loads the page name constant on 1.5.8
 * through 2.1, which don't read a plugin's root filenames.php. Plugin
 * extra_datafiles have been loaded on every release from 1.5.8 to 3.0.0.
 *
 * @package  QuickAttributePriceUpdate
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

require dirname(__DIR__, 3) . '/filenames.php';
