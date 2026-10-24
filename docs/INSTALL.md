# Installing Quick Attribute Price Update

## New install

1. Unzip the package.
2. Copy `zc_plugins/QuickAttributePriceUpdate/` into your store's `zc_plugins/` folder, so you have `zc_plugins/QuickAttributePriceUpdate/v3.0.2/manifest.php`.
3. In the admin, go to **Modules > Plugin Manager**, select **Quick Attribute Price Update**, and click **Install**.
4. The page appears under **Catalog > Quick Attribute Price Update**.

No files outside `zc_plugins/` are added or changed, and no database tables are created. The installer adds one record to the admin menu (`catalogQuickAttributePriceUpdate`).

## Admin access

Superusers can use the page at once. For other admins, add **Quick Attribute Price Update** to their profile under **Admins > Admin Profiles** (it's in the Catalog group). The link in the Attributes Controller's action menu shows only for admins who can open the page.

## Upgrading

Copy the new version folder (for example `v3.0.3/`) into `zc_plugins/QuickAttributePriceUpdate/` beside the old one, then click **Upgrade** in Plugin Manager. Once it reports the new version, you can delete the old version's folder.

## Uninstalling

Click **Uninstall** in Plugin Manager. That removes the menu entry. Prices you saved stay as they are; there's nothing else to clean up. Delete `zc_plugins/QuickAttributePriceUpdate/` afterwards if you like.
