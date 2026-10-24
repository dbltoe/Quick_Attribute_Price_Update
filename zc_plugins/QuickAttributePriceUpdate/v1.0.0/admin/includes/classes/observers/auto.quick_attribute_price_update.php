<?php
/**
 * Quick Attribute Price Update -- adds a link to Catalog > Attributes
 * Controller's action menu: the product on screen (or, with none chosen, its
 * category) opens on Catalog > Quick Attribute Price Update.
 *
 * NOTIFY_ATTRIBUTE_CONTROLLER_ADDITIONAL_ACTIONS_DROPDOWN_UPPER exists, with the
 * same parameters, on every release from 1.5.8a to 3.0.0, so no core file is
 * changed. The menu prints each ['link' => ..., 'text' => ...] as given.
 *
 * Core finds this file by its name (init_observers.php scans an installed
 * plugin's observers/ folder for auto.*.php) and constructs the class
 * camelize() derives from it. It must never be listed in an auto_loader too:
 * that includes it twice and fatals every admin page.
 *
 * @package  QuickAttributePriceUpdate
 * @license  GNU General Public License v2.0 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

// Guarded: during a Plugin Manager upgrade the old version's copy of the class
// may already be loaded from its own folder.
if (!class_exists('QuickAttributePriceUpdate', false)) {
    require dirname(__DIR__) . '/QuickAttributePriceUpdate.php';
}

class zcObserverQuickAttributePriceUpdate extends base
{
    public function __construct()
    {
        $this->attach($this, [
            'NOTIFY_ATTRIBUTE_CONTROLLER_ADDITIONAL_ACTIONS_DROPDOWN_UPPER',
        ]);
    }

    /**
     * The Attributes Controller's action menu, above Option Names / Option Values.
     */
    public function notify_attribute_controller_additional_actions_dropdown_upper(&$class, $eventID, $zc_products, &$action, &$products_filter, &$current_category_id, &$additional_actions)
    {
        if (!is_array($additional_actions) || !QuickAttributePriceUpdate::canAccess()) {
            return;
        }
        $pid = (int)$products_filter;
        $cid = (int)$current_category_id;
        if ($pid > 0) {
            $params = 'qapu_ids=' . $pid;
            $text = QAPU_MENU_PRODUCT;
        } elseif ($cid > 0) {
            $params = 'qapu_cid=' . $cid;
            $text = QAPU_MENU_CATEGORY;
        } else {
            $params = '';
            $text = BOX_CATALOG_QUICK_ATTRIBUTE_PRICE_UPDATE;
        }
        $additional_actions[] = [
            'link' => zen_href_link(FILENAME_QUICK_ATTRIBUTE_PRICE_UPDATE, $params),
            'text' => $text,
        ];
    }
}
