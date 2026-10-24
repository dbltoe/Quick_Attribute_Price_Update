<?php
/**
 * Quick Attribute Price Update -- plugin manifest.
 *
 * Zen Cart stores pluginDescription in plugin_control.description and echoes
 * it into the Plugin Manager's info box as raw HTML on every release from
 * 1.5.8 to 3.0.0, which is where the Read Me / GitHub / Forum Support Thread
 * buttons go (the Social Contact Footer pattern).
 *
 * On 1.5.8, 2.0 and 2.1 the description and pluginId are written only by the
 * INSERT that first creates a store's plugin_control row, so the forum URL and
 * the Library ID must be right in the release a store first installs.
 *
 * @package  QuickAttributePriceUpdate
 * @license  GNU General Public License v2.0 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */
$qapuPluginDir = 'zc_plugins/QuickAttributePriceUpdate/v3.0.2/';
$qapuReadmeUrl = (defined('DIR_WS_CATALOG') ? DIR_WS_CATALOG : '/') . $qapuPluginDir . 'readme.html';
$qapuGithubUrl = 'https://github.com/dbltoe/Quick_Attribute_Price_Update';

// The opening-post permalink of the plugin's Zen Cart forum thread. Empty
// renders no button: a link that 404s is worse than none.
$qapuForumUrl = 'https://www.zen-cart.com/threads/207448?page=1#post-1347750';

$qapuButtonGap = '6px';

$qapuLinks =
    '<div style="margin:10px 0 0;padding:0 0 0 ' . $qapuButtonGap . '">'
    . '<a href="' . $qapuReadmeUrl . '" target="_blank" rel="noopener noreferrer"'
    . ' class="btn btn-primary" role="button"'
    . ' style="margin:0 ' . $qapuButtonGap . ' 0 0">Read Me</a>'
    . ($qapuGithubUrl !== ''
        ? '<a href="' . $qapuGithubUrl . '" target="_blank" rel="noopener noreferrer"'
          . ' class="btn btn-primary" role="button"'
          . ' style="margin:0 ' . $qapuButtonGap . ' 0 0">GitHub</a>'
        : '')
    . ($qapuForumUrl !== ''
        ? '<a href="' . $qapuForumUrl . '" target="_blank" rel="noopener noreferrer"'
          . ' class="btn btn-primary" role="button"'
          . ' style="margin:0 ' . $qapuButtonGap . ' 0 0">Forum Support Thread</a>'
        : '')
    . '</div>';

return [
    'pluginVersion' => 'v3.0.2',
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
