<?php
/**
 * Quick Attribute Price Update -- installer.
 *
 * The plugin keeps no settings and no tables of its own: install registers the
 * Catalog > Quick Attribute Price Update page, uninstall removes it. Upgrade
 * re-registers it, which also repairs a page record someone deleted by hand.
 *
 * Only executeInstall(), executeUninstall() and executeUpgrade() are ever
 * called by core's ScriptedInstaller.
 *
 * @package  QuickAttributePriceUpdate
 * @license  GNU General Public License v2.0 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */

// FILENAME_QUICK_ATTRIBUTE_PRICE_UPDATE: the plugin isn't marked installed until
// this installer finishes, so its filenames aren't loaded yet on a first install.
require_once dirname(__DIR__) . '/filenames.php';

use Zencart\PluginSupport\ScriptedInstaller as ScriptedInstallBase;

class ScriptedInstaller extends ScriptedInstallBase
{
    public const ADMIN_PAGE_KEY = 'catalogQuickAttributePriceUpdate';

    protected function executeInstall()
    {
        zen_deregister_admin_pages([self::ADMIN_PAGE_KEY]);
        zen_register_admin_page(self::ADMIN_PAGE_KEY, 'BOX_CATALOG_QUICK_ATTRIBUTE_PRICE_UPDATE', 'FILENAME_QUICK_ATTRIBUTE_PRICE_UPDATE', '', 'catalog', 'Y');
        return true;
    }

    protected function executeUpgrade($oldVersion = null)
    {
        return $this->executeInstall();
    }

    protected function executeUninstall()
    {
        zen_deregister_admin_pages([self::ADMIN_PAGE_KEY]);
        return true;
    }
}
