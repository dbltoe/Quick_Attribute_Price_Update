<?php
/**
 * Quick Attribute Price Update -- plugin manifest.
 *
 * Zen Cart stores pluginDescription in plugin_control.description and echoes
 * it into the Plugin Manager's info box as raw HTML on every release from
 * 1.5.8 to 3.0.0, which is where the ReadMe / Forum Thread / GitHub buttons
 * go (the Social Contact Footer pattern).
 *
 * ReadMe opens README.md through the plugin's own admin page
 * (index.php?cmd=quick_attribute_price_update_readme): behind the admin login,
 * superusers only, in its own window. It is shown only to superusers; the page
 * enforces that too, because Zen Cart 1.5.8-2.1 keep the description they
 * first saw. Forum Thread and GitHub go off-site, so they open in a new
 * window with rel="noopener noreferrer" (zencart/zencart#8031).
 *
 * On 1.5.8, 2.0 and 2.1 the description and pluginId are written only by the
 * INSERT that first creates a store's plugin_control row, so the forum URL and
 * the Library ID must be right in the release a store first installs.
 *
 * @package  QuickAttributePriceUpdate
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */
$qapuReadmeUrl = 'index.php?cmd=quick_attribute_price_update_readme';
// Zen Cart 3.0.0 reads every manifest on every admin page, early (template resolver),
// before admin access is set up: zen_is_superuser() there is a fatal "Undefined constant
// SUPERUSER_PROFILE" that takes down the whole admin. Ask only once that exists.
$qapuShowReadme = true;
if (function_exists('zen_is_superuser') && defined('SUPERUSER_PROFILE')) {
    $qapuShowReadme = zen_is_superuser();
}
$qapuGithubUrl = 'https://github.com/dbltoe/Quick_Attribute_Price_Update';

// The opening-post permalink of the plugin's Zen Cart forum thread. Empty
// renders no button: a link that 404s is worse than none.
$qapuForumUrl = 'https://www.zen-cart.com/threads/207448?page=1#post-1347750';

// One row of buttons: ReadMe | Forum Thread | GitHub. There is no Settings
// button: the plugin has no settings, and its admin page is the tool itself.
$qapuButtonGap = '6px';
$qapuButton = static function ($url, $label) use ($qapuButtonGap) {
    return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer"'
        . ' class="btn btn-primary" role="button"'
        . ' style="margin:0 ' . $qapuButtonGap . ' 0 0">' . $label . '</a>';
};

$qapuLinks =
    '<div style="margin:10px 0 0;padding:0 0 0 ' . $qapuButtonGap . '">'
    . ($qapuShowReadme ? $qapuButton($qapuReadmeUrl, 'ReadMe') : '')
    . ($qapuForumUrl !== '' ? $qapuButton($qapuForumUrl, 'Forum Thread') : '')
    . ($qapuGithubUrl !== '' ? $qapuButton($qapuGithubUrl, 'GitHub') : '')
    . '</div>';

return [
    'pluginVersion' => 'v3.0.3',
    'pluginName' => 'Quick Attribute Price Update',
    'pluginDescription' =>
        'Adds Catalog > Quick Attribute Price Update: find attribute prices across products by product ID '
        . 'or ID range, by category (with or without its subcategories), by option name and option value, '
        . 'or by name or model, then change prefixes, prices and one-time prices in one table and save them '
        . 'all at once. Every product touched gets its storefront price sort refreshed. No core files are changed.'
        . $qapuLinks,
    'pluginAuthor' => 'My Zen Cart Host (dbltoe)',
    // The Plugins Library ID (the number after "vb" in the listing URL); 0 until
    // the Library assigns one.
    'pluginId' => 0,
    'zcVersions' => ['v158', 'v200', 'v210', 'v220', 'v230', 'v300'],
    'changelog' => 'changelog.txt',
    'github_repo' => $qapuGithubUrl,
    'pluginGroups' => [],
];
