<?php
/**
 * Quick Attribute Price Update -- Catalog > Quick Attribute Price Update.
 *
 * Find attribute rows by product ID or ID ranges, by category (with or without
 * its subcategories), by option name and option value, by product name or
 * model and by status; change prefixes, prices and one-time prices in the
 * table; save every changed row with one button.
 *
 * Reached as admin/index.php?cmd=quick_attribute_price_update, the routing Zen
 * Cart has used for plugin admin pages since v1.5.7; index.php loads the admin
 * bootstrap first, so IS_ADMIN_FLAG is always set on that path. Access is the
 * catalogQuickAttributePriceUpdate admin_pages record the installer writes.
 *
 * Saving is a POST with action=save in the URL, so core's admin CSRF check in
 * init_sessions.php applies to it. The page then redirects (post/redirect/get),
 * so a reload never saves twice.
 *
 * @package  QuickAttributePriceUpdate
 * @license  GNU General Public License v2.0 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 */

if (!defined('IS_ADMIN_FLAG')) {
    die('Illegal Access');
}

require 'includes/application_top.php';

if (!class_exists('QuickAttributePriceUpdate', false)) {
    require __DIR__ . '/includes/classes/QuickAttributePriceUpdate.php';
}

$qapuFilters = QuickAttributePriceUpdate::filtersFrom($_GET);
$qapuPage = (int)($_GET['page'] ?? 1);
$qapuAction = (string)($_GET['action'] ?? '');

// An add-on (Quick Attribute Price Update Pro) handles its own actions here, before ours.
$zco_notifier->notify('NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_ACTION', $qapuAction, $qapuFilters);

if ($qapuAction === 'save' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $qapuResult = QuickAttributePriceUpdate::save(
        is_array($_POST['qapu_prefix'] ?? null) ? $_POST['qapu_prefix'] : [],
        is_array($_POST['qapu_price'] ?? null) ? $_POST['qapu_price'] : [],
        is_array($_POST['qapu_onetime'] ?? null) ? $_POST['qapu_onetime'] : [],
        is_array($_POST['qapu_was'] ?? null) ? $_POST['qapu_was'] : []
    );
    if ($qapuResult['updated'] !== []) {
        $messageStack->add_session(sprintf(QAPU_MSG_UPDATED, count($qapuResult['updated']), count($qapuResult['products'])), 'success');
        if (function_exists('zen_record_admin_activity')) {
            zen_record_admin_activity(sprintf(QAPU_ACTIVITY_LOG, implode(', ', $qapuResult['updated']), implode(', ', $qapuResult['products'])), 'info');
        }
    }
    if ($qapuResult['invalid'] !== []) {
        $messageStack->add_session(sprintf(QAPU_MSG_INVALID, implode(', ', array_keys($qapuResult['invalid']))), 'error');
        $_SESSION['qapu_retry'] = $qapuResult['invalid'];
    }
    if ($qapuResult['conflict'] !== []) {
        $messageStack->add_session(sprintf(QAPU_MSG_CONFLICT, implode(', ', $qapuResult['conflict'])), 'warning');
    }
    if ($qapuResult['missing'] !== []) {
        $messageStack->add_session(sprintf(QAPU_MSG_MISSING, implode(', ', $qapuResult['missing'])), 'warning');
    }
    if ($qapuResult['updated'] === [] && $qapuResult['invalid'] === [] && $qapuResult['conflict'] === [] && $qapuResult['missing'] === []) {
        $messageStack->add_session(QAPU_MSG_NOTHING, 'caution');
    }
    $qapuBack = QuickAttributePriceUpdate::filterParams($qapuFilters);
    zen_redirect(zen_href_link(FILENAME_QUICK_ATTRIBUTE_PRICE_UPDATE, $qapuBack . ($qapuBack === '' ? '' : '&') . 'page=' . max(1, $qapuPage)));
}

// Rows that weren't saved last time, with what was typed, shown once.
$qapuRetry = (isset($_SESSION['qapu_retry']) && is_array($_SESSION['qapu_retry'])) ? $_SESSION['qapu_retry'] : [];
unset($_SESSION['qapu_retry']);

$qapuErrors = [];
$qapuFound = QuickAttributePriceUpdate::search($qapuFilters, $qapuPage, $qapuErrors);
foreach (array_unique($qapuErrors) as $qapuError) {
    $messageStack->add(constant($qapuError), 'error');
}

$qapuCurrency = QuickAttributePriceUpdate::currency();
$qapuParams = QuickAttributePriceUpdate::filterParams($qapuFilters);
$qapuCharset = defined('CHARSET') ? CHARSET : 'utf-8';
$qapuShowBaseLink = QuickAttributePriceUpdate::canAccessQuickPriceUpdate();

/** Escapes for HTML text and attributes. */
$qapuEsc = static function ($s) use ($qapuCharset) {
    return htmlspecialchars((string)$s, ENT_QUOTES, $qapuCharset);
};

/** This page with the current search and another page number. */
$qapuPageUrl = static function ($n) use ($qapuParams) {
    return zen_href_link(FILENAME_QUICK_ATTRIBUTE_PRICE_UPDATE, $qapuParams . ($qapuParams === '' ? '' : '&') . 'page=' . (int)$n);
};

/** A price with the store's symbol, escaped. */
$qapuMoney = static function ($amount) use ($qapuCurrency, $qapuEsc) {
    $shown = QuickAttributePriceUpdate::displayNumber((string)$amount);
    return $qapuEsc($qapuCurrency['prefix'] . $shown . ($qapuCurrency['suffix'] === '' ? '' : ' ' . $qapuCurrency['suffix']));
};

/** A badge. */
$qapuBadge = static function ($text, $style = 'default', $title = '') use ($qapuEsc) {
    return '<span class="label label-' . $style . '"' . ($title !== '' ? ' title="' . $qapuEsc($title) . '"' : '') . '>' . $qapuEsc($text) . '</span>';
};

/** A price box with the currency symbol around it. */
$qapuPriceBox = static function ($name, $value, $was, $rowId, $label, $invalid) use ($qapuCurrency, $qapuEsc) {
    $html = '<div class="input-group input-group-sm qapu-num">';
    if ($qapuCurrency['prefix'] !== '') {
        $html .= '<span class="input-group-addon" aria-hidden="true">' . $qapuEsc($qapuCurrency['prefix']) . '</span>';
    }
    $html .= '<input type="text" inputmode="decimal" class="form-control qapu-input" name="' . $name . '" value="' . $qapuEsc($value) . '"'
        . ' data-was="' . $qapuEsc($was) . '" data-row="' . $rowId . '" aria-label="' . $qapuEsc($label) . '"'
        . ($invalid ? ' aria-invalid="true" title="' . $qapuEsc(QAPU_RETRY_HELP) . '"' : '') . '>';
    if ($qapuCurrency['suffix'] !== '') {
        $html .= '<span class="input-group-addon" aria-hidden="true">' . $qapuEsc($qapuCurrency['suffix']) . '</span>';
    }
    return $html . '</div>';
};

$qapuCategoryOptions = array_merge([['id' => '', 'text' => QAPU_CATEGORY_ANY]], zen_get_category_tree());
// One entry per option NAME and, under it, one per value name; each carries every ID
// with that name, so two options both called "Size:" are searched together.
$qapuLists = QuickAttributePriceUpdate::optionLists();
$qapuOptionOptions = [];
$qapuOptionMatched = $qapuFilters['oid'] === '';
foreach ($qapuLists['options'] as $qapuOpt) {
    $qapuOptionOptions[] = ['id' => $qapuOpt['id'], 'text' => $qapuOpt['text'] !== '' ? $qapuOpt['text'] : sprintf(QAPU_OPTION_MISSING, $qapuOpt['id'])];
    $qapuOptionMatched = $qapuOptionMatched || $qapuOpt['id'] === $qapuFilters['oid'];
}
$qapuValueMatched = $qapuFilters['vid'] === '';
foreach ($qapuLists['values'] as $qapuGroup) {
    foreach ($qapuGroup['values'] as $qapuVal) {
        $qapuValueMatched = $qapuValueMatched || $qapuVal['id'] === $qapuFilters['vid'];
    }
}
$qapuStatusOptions = [
    ['id' => '', 'text' => QAPU_STATUS_ANY],
    ['id' => '1', 'text' => QAPU_STATUS_ON],
    ['id' => '0', 'text' => QAPU_STATUS_OFF],
];
$qapuSortOptions = [];
foreach (array_keys(QuickAttributePriceUpdate::SORTS) as $qapuKey) {
    $qapuSortOptions[] = ['id' => $qapuKey, 'text' => constant('QAPU_SORT_' . strtoupper($qapuKey))];
}
$qapuDirOptions = [['id' => 'asc', 'text' => QAPU_DIR_ASC], ['id' => 'desc', 'text' => QAPU_DIR_DESC]];
$qapuPerOptions = [];
foreach (QuickAttributePriceUpdate::PER_PAGE_CHOICES as $qapuN) {
    $qapuPerOptions[] = ['id' => (string)$qapuN, 'text' => (string)$qapuN];
}
$qapuPrefixChoices = ['+' => QAPU_PREFIX_PLUS, '-' => QAPU_PREFIX_MINUS, '' => QAPU_PREFIX_BLANK];
$qapuTypeText = defined('PRODUCTS_OPTIONS_TYPE_TEXT') ? (int)PRODUCTS_OPTIONS_TYPE_TEXT : 1;
$qapuTypeFile = defined('PRODUCTS_OPTIONS_TYPE_FILE') ? (int)PRODUCTS_OPTIONS_TYPE_FILE : 4;

// An add-on adds its own search fields here (complete form-group markup).
$qapuExtraFilters = '';
$zco_notifier->notify('NOTIFY_QUICK_ATTRIBUTE_PRICE_UPDATE_FILTERS_FORM', $qapuFilters, $qapuExtraFilters);
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
<head>
<?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
<style>
.qapu-page .qapu-find{display:block;background:#f7f7f7;border:1px solid #ddd;border-radius:4px;padding:12px 15px 4px;margin-bottom:15px}
.qapu-page .qapu-find legend{font-size:1.3em;border:0;margin:0 0 8px;width:auto}
.qapu-page .qapu-find .form-group{margin-right:12px;margin-bottom:10px;vertical-align:top}
.qapu-page .qapu-find .help-block{margin:2px 0 0;font-size:.95em;color:#555}
.qapu-page .qapu-table td{vertical-align:middle}
.qapu-page .qapu-table .qapu-num{width:9em}
.qapu-page .qapu-table .qapu-prefix{width:auto;min-width:8em}
.qapu-page .qapu-table tbody th.qapu-product{background:#e8eef4;color:#1d2b36;font-weight:400;padding-top:8px;padding-bottom:8px;border-top:2px solid #b8c7d6}
.qapu-page .qapu-table tbody th.qapu-product strong{font-size:1.05em}
.qapu-page .qapu-table .qapu-product-meta{display:inline-block;margin-left:10px}
.qapu-page .qapu-table .qapu-product-links{display:inline-block;margin-left:10px}
.qapu-page .qapu-table .qapu-product-links a{margin-right:10px}
.qapu-page .qapu-table tr.qapu-row-changed td{background:#fff3cd}
.qapu-page .qapu-table .qapu-changed{border-color:#8a6d3b;font-weight:700}
.qapu-page .qapu-table .label{display:inline-block;margin:1px 2px 1px 0;font-size:.9em}
.qapu-page .qapu-actions{display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin:10px 0}
.qapu-page .qapu-changed-count{font-weight:700}
</style>
</head>
<body>
<!-- header //-->
<?php require DIR_WS_INCLUDES . 'header.php'; ?>
<!-- header_eof //-->

<!-- body //-->
<main class="container-fluid qapu-page" aria-labelledby="qapuHeading">
  <h1 id="qapuHeading" class="pageHeading"><?php echo HEADING_TITLE; ?></h1>
  <p><?php echo sprintf(QAPU_INTRO, $qapuEsc($qapuCurrency['code'])); ?></p>

  <?php echo zen_draw_form('qapuFind', FILENAME_QUICK_ATTRIBUTE_PRICE_UPDATE, '', 'get', 'class="form-inline qapu-find" role="search"'); ?>
    <fieldset>
      <legend><?php echo QAPU_LEGEND_FIND; ?></legend>
      <div class="form-group">
        <label for="qapuIds"><?php echo QAPU_LABEL_IDS; ?></label><br>
        <input type="text" class="form-control" id="qapuIds" name="qapu_ids" value="<?php echo $qapuEsc($qapuFilters['ids']); ?>" size="22" inputmode="numeric" autocomplete="off" aria-describedby="qapuIdsHelp">
        <p class="help-block" id="qapuIdsHelp"><?php echo QAPU_HELP_IDS; ?></p>
      </div>
      <div class="form-group">
        <label for="qapuCid"><?php echo QAPU_LABEL_CATEGORY; ?></label><br>
        <?php echo zen_draw_pull_down_menu('qapu_cid', $qapuCategoryOptions, $qapuFilters['cid'], 'id="qapuCid" class="form-control"'); ?>
        <div class="checkbox" style="display:block;margin-top:4px;">
          <label for="qapuSub"><input type="checkbox" id="qapuSub" name="qapu_sub" value="1"<?php echo $qapuFilters['sub'] === '1' ? ' checked' : ''; ?>> <?php echo QAPU_LABEL_SUBCATEGORIES; ?></label>
        </div>
      </div>
      <div class="form-group">
        <label for="qapuOid"><?php echo QAPU_LABEL_OPTION; ?></label><br>
        <select name="qapu_oid" id="qapuOid" class="form-control">
          <option value=""><?php echo $qapuEsc(QAPU_OPTION_ANY); ?></option>
<?php if (!$qapuOptionMatched) { ?>
          <option value="<?php echo $qapuEsc($qapuFilters['oid']); ?>" selected><?php echo $qapuEsc(sprintf(QAPU_LIST_BY_ID, $qapuFilters['oid'])); ?></option>
<?php } ?>
<?php foreach ($qapuOptionOptions as $qapuOpt) { ?>
          <option value="<?php echo $qapuEsc($qapuOpt['id']); ?>"<?php echo $qapuFilters['oid'] === $qapuOpt['id'] ? ' selected' : ''; ?>><?php echo $qapuEsc($qapuOpt['text']); ?></option>
<?php } ?>
        </select>
      </div>
      <div class="form-group">
        <label for="qapuVid"><?php echo QAPU_LABEL_VALUE; ?></label><br>
        <select name="qapu_vid" id="qapuVid" class="form-control">
          <option value=""><?php echo $qapuEsc(QAPU_VALUE_ANY); ?></option>
<?php if (!$qapuValueMatched) { ?>
          <option value="<?php echo $qapuEsc($qapuFilters['vid']); ?>" selected><?php echo $qapuEsc(sprintf(QAPU_LIST_BY_ID, $qapuFilters['vid'])); ?></option>
<?php } ?>
<?php foreach ($qapuLists['values'] as $qapuGroup) { ?>
          <optgroup label="<?php echo $qapuEsc($qapuGroup['label'] !== '' ? $qapuGroup['label'] : sprintf(QAPU_OPTION_MISSING, $qapuGroup['oid'])); ?>" data-oid="<?php echo $qapuEsc($qapuGroup['oid']); ?>">
<?php     foreach ($qapuGroup['values'] as $qapuVal) { ?>
            <option value="<?php echo $qapuEsc($qapuVal['id']); ?>"<?php echo $qapuFilters['vid'] === $qapuVal['id'] ? ' selected' : ''; ?>><?php echo $qapuEsc($qapuVal['text'] !== '' ? $qapuVal['text'] : sprintf(QAPU_VALUE_MISSING, $qapuVal['id'])); ?></option>
<?php     } ?>
          </optgroup>
<?php } ?>
        </select>
      </div>
      <div class="form-group">
        <label for="qapuKw"><?php echo QAPU_LABEL_KEYWORDS; ?></label><br>
        <input type="search" class="form-control" id="qapuKw" name="qapu_kw" value="<?php echo $qapuEsc($qapuFilters['kw']); ?>" size="22">
      </div>
      <div class="form-group">
        <label for="qapuStatus"><?php echo QAPU_LABEL_STATUS; ?></label><br>
        <?php echo zen_draw_pull_down_menu('qapu_status', $qapuStatusOptions, $qapuFilters['status'], 'id="qapuStatus" class="form-control"'); ?>
        <div class="checkbox" style="display:block;margin-top:4px;">
          <label for="qapuPriced"><input type="checkbox" id="qapuPriced" name="qapu_priced" value="1"<?php echo $qapuFilters['priced'] === '1' ? ' checked' : ''; ?>> <?php echo QAPU_LABEL_PRICED; ?></label>
        </div>
      </div>
      <div class="form-group">
        <label for="qapuSort"><?php echo QAPU_LABEL_SORT; ?></label><br>
        <?php echo zen_draw_pull_down_menu('qapu_sort', $qapuSortOptions, $qapuFilters['sort'], 'id="qapuSort" class="form-control"'); ?>
      </div>
      <div class="form-group">
        <label for="qapuDir"><?php echo QAPU_LABEL_DIRECTION; ?></label><br>
        <?php echo zen_draw_pull_down_menu('qapu_dir', $qapuDirOptions, $qapuFilters['dir'], 'id="qapuDir" class="form-control"'); ?>
      </div>
      <div class="form-group">
        <label for="qapuPer"><?php echo QAPU_LABEL_PER_PAGE; ?></label><br>
        <?php echo zen_draw_pull_down_menu('qapu_per', $qapuPerOptions, (string)$qapuFilters['per'], 'id="qapuPer" class="form-control"'); ?>
      </div>
      <?php echo $qapuExtraFilters; ?>
      <div class="form-group">
        <span>&nbsp;</span><br>
        <button type="submit" class="btn btn-primary"><?php echo QAPU_BUTTON_FIND; ?></button>
        <a href="<?php echo zen_href_link(FILENAME_QUICK_ATTRIBUTE_PRICE_UPDATE); ?>" class="btn btn-default" role="button"><?php echo QAPU_BUTTON_RESET; ?></a>
      </div>
    </fieldset>
  </form>

<?php if ($qapuFound['rows'] === []) { ?>
  <p class="alert alert-info"><?php echo QAPU_NONE_FOUND; ?></p>
<?php } else { ?>
  <?php echo zen_draw_form('qapuForm', FILENAME_QUICK_ATTRIBUTE_PRICE_UPDATE, 'action=save' . ($qapuParams === '' ? '' : '&' . $qapuParams) . '&page=' . (int)$qapuFound['page'], 'post', 'id="qapuForm" autocomplete="off"'); ?>
  <div class="table-responsive">
  <table class="table table-condensed qapu-table">
    <caption><?php echo sprintf(QAPU_CAPTION, (int)$qapuFound['total'], (int)$qapuFound['page'], (int)$qapuFound['pages']); ?></caption>
    <thead>
      <tr class="dataTableHeadingRow">
        <th scope="col" class="dataTableHeadingContent"><?php echo QAPU_HEADING_OPTION; ?></th>
        <th scope="col" class="dataTableHeadingContent"><?php echo QAPU_HEADING_VALUE; ?></th>
        <th scope="col" class="dataTableHeadingContent"><?php echo QAPU_HEADING_PREFIX; ?></th>
        <th scope="col" class="dataTableHeadingContent"><?php echo QAPU_HEADING_PRICE; ?> (<?php echo $qapuEsc($qapuCurrency['code']); ?>)</th>
        <th scope="col" class="dataTableHeadingContent"><?php echo QAPU_HEADING_ONETIME; ?> (<?php echo $qapuEsc($qapuCurrency['code']); ?>)</th>
        <th scope="col" class="dataTableHeadingContent"><?php echo QAPU_HEADING_NOTES; ?></th>
      </tr>
    </thead>
<?php
    $qapuLastProduct = null;
    foreach ($qapuFound['rows'] as $qapuRow) {
        $qapuAid = (int)$qapuRow['products_attributes_id'];
        $qapuPid = (int)$qapuRow['products_id'];
        $qapuName = (string)$qapuRow['products_name'] !== '' ? (string)$qapuRow['products_name'] : QAPU_NO_NAME;

        // A new product: close the last group and print this product's header row.
        if ($qapuPid !== $qapuLastProduct) {
            if ($qapuLastProduct !== null) {
                echo "    </tbody>\n";
            }
            $qapuLastProduct = $qapuPid;
            $qapuCPath = zen_get_product_path($qapuPid);
            $qapuMeta = [];
            if ((string)$qapuRow['products_model'] !== '') {
                $qapuMeta[] = $qapuEsc(sprintf(QAPU_PRODUCT_MODEL, $qapuRow['products_model']));
            }
            $qapuMeta[] = sprintf($qapuEsc(QAPU_PRODUCT_BASE), $qapuMoney($qapuRow['products_price']));
            $qapuMeta[] = '<span title="' . $qapuEsc(QAPU_HELP_FROM) . '">' . sprintf($qapuEsc(QAPU_PRODUCT_FROM), $qapuMoney($qapuRow['products_price_sorter'])) . '</span>';
            $qapuProductBadges = [];
            if ((int)$qapuRow['products_priced_by_attribute'] === 1) {
                $qapuProductBadges[] = $qapuBadge(QAPU_NOTE_PRICED_BY_ATTRIBUTES, 'info');
            }
            if ((int)$qapuRow['products_status'] !== 1) {
                $qapuProductBadges[] = $qapuBadge(QAPU_NOTE_INACTIVE, 'default');
            }
            if (QuickAttributePriceUpdate::zeroBaseWarning($qapuRow)) {
                $qapuProductBadges[] = $qapuBadge(QAPU_NOTE_ZERO_BASE, 'warning', QAPU_HELP_ZERO_BASE);
            }
            $qapuLinks = [
                '<a href="' . zen_href_link(FILENAME_PRODUCT, 'cPath=' . $qapuCPath . '&product_type=' . (int)$qapuRow['products_type'] . '&pID=' . $qapuPid . '&action=new_product') . '">' . QAPU_LINK_EDIT_PRODUCT . '</a>',
                '<a href="' . zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $qapuPid . '&current_category_id=' . (int)$qapuRow['master_categories_id']) . '">' . QAPU_LINK_ATTRIBUTES . '</a>',
            ];
            if ($qapuShowBaseLink) {
                $qapuLinks[] = '<a href="' . zen_href_link(FILENAME_QUICK_PRICE_UPDATE, 'qpu_ids=' . $qapuPid) . '">' . QAPU_LINK_BASE_PRICE . '</a>';
            }
?>
    <tbody>
      <tr>
        <th scope="rowgroup" colspan="6" class="qapu-product" id="qapuProduct<?php echo $qapuPid; ?>">
          <strong><?php echo $qapuEsc(sprintf(QAPU_PRODUCT_LINE, $qapuPid, $qapuName)); ?></strong>
          <?php echo implode(' ', $qapuProductBadges); ?>
          <span class="qapu-product-meta"><?php echo implode(' &middot; ', $qapuMeta); ?></span>
          <span class="qapu-product-links"><?php echo implode(' ', $qapuLinks); ?></span>
        </th>
      </tr>
<?php
        }

        $qapuOptionName = $qapuRow['products_options_name'] !== null && (string)$qapuRow['products_options_name'] !== ''
            ? (string)$qapuRow['products_options_name'] : sprintf(QAPU_OPTION_MISSING, (int)$qapuRow['options_id']);
        $qapuType = (int)$qapuRow['products_options_type'];
        if ($qapuRow['products_options_values_name'] !== null && (int)$qapuRow['options_values_id'] > 0) {
            $qapuValueName = (string)$qapuRow['products_options_values_name'];
        } elseif ($qapuType === $qapuTypeText) {
            $qapuValueName = QAPU_VALUE_TEXT;
        } elseif ($qapuType === $qapuTypeFile) {
            $qapuValueName = QAPU_VALUE_FILE;
        } else {
            $qapuValueName = sprintf(QAPU_VALUE_MISSING, (int)$qapuRow['options_values_id']);
        }
        $qapuDescribe = sprintf(QAPU_ROW_DESCRIPTION, $qapuOptionName, $qapuValueName, $qapuName);

        $qapuWasPrefix = QuickAttributePriceUpdate::canonicalPrefix($qapuRow['price_prefix']);
        $qapuWasPrefix = $qapuWasPrefix === null ? '' : $qapuWasPrefix;
        $qapuWasPrice = (string)QuickAttributePriceUpdate::canonicalNumber($qapuRow['options_values_price'], true);
        $qapuWasOnetime = (string)QuickAttributePriceUpdate::canonicalNumber($qapuRow['attributes_price_onetime'], true);
        $qapuShowPrice = QuickAttributePriceUpdate::displayNumber($qapuWasPrice);
        $qapuShowOnetime = QuickAttributePriceUpdate::displayNumber($qapuWasOnetime);

        $qapuRetryRow = $qapuRetry[$qapuAid] ?? null;
        // What was typed arrived through core's strict sanitizer (htmlspecialchars), so it's
        // decoded once here before being escaped again for the box.
        $qapuPrefixValue = $qapuRetryRow !== null ? html_entity_decode((string)$qapuRetryRow['prefix'], ENT_QUOTES, $qapuCharset) : $qapuWasPrefix;
        $qapuPriceValue = $qapuRetryRow !== null ? html_entity_decode((string)$qapuRetryRow['price'], ENT_QUOTES, $qapuCharset) : $qapuShowPrice;
        $qapuOnetimeValue = $qapuRetryRow !== null ? html_entity_decode((string)$qapuRetryRow['onetime'], ENT_QUOTES, $qapuCharset) : $qapuShowOnetime;

        $qapuNotes = [];
        foreach (QuickAttributePriceUpdate::rowNotes($qapuRow) as $qapuNote) {
            $qapuNotes[] = $qapuBadge(constant($qapuNote), $qapuNote === 'QAPU_NOTE_MINUS' ? 'warning' : 'default');
        }
        $qapuRowId = 'qapuRow' . $qapuAid;
?>
      <tr id="<?php echo $qapuRowId; ?>"<?php echo $qapuRetryRow !== null ? ' class="danger"' : ''; ?>>
        <td><?php echo $qapuEsc($qapuOptionName); ?></td>
        <td><?php echo $qapuEsc($qapuValueName); ?></td>
        <td<?php echo $qapuRetryRow !== null ? ' class="has-error"' : ''; ?>>
          <?php echo zen_draw_hidden_field('qapu_was[' . $qapuAid . ']', QuickAttributePriceUpdate::wasValue($qapuWasPrefix, $qapuWasPrice, $qapuWasOnetime)); ?>
          <select name="qapu_prefix[<?php echo $qapuAid; ?>]" class="form-control input-sm qapu-input qapu-prefix" data-was="<?php echo $qapuEsc($qapuWasPrefix); ?>" data-row="<?php echo $qapuRowId; ?>" aria-label="<?php echo $qapuEsc(sprintf(QAPU_LABEL_PREFIX_FOR, $qapuDescribe)); ?>"<?php echo $qapuRetryRow !== null ? ' aria-invalid="true"' : ''; ?>>
<?php   foreach ($qapuPrefixChoices as $qapuChoice => $qapuChoiceText) { ?>
            <option value="<?php echo $qapuEsc($qapuChoice); ?>"<?php echo (string)$qapuChoice === $qapuPrefixValue ? ' selected' : ''; ?>><?php echo $qapuEsc($qapuChoiceText); ?></option>
<?php   } ?>
          </select>
        </td>
        <td<?php echo $qapuRetryRow !== null ? ' class="has-error"' : ''; ?>>
          <?php echo $qapuPriceBox('qapu_price[' . $qapuAid . ']', $qapuPriceValue, $qapuShowPrice, $qapuRowId, sprintf(QAPU_LABEL_PRICE_FOR, $qapuDescribe), $qapuRetryRow !== null); ?>
        </td>
        <td<?php echo $qapuRetryRow !== null ? ' class="has-error"' : ''; ?>>
          <?php echo $qapuPriceBox('qapu_onetime[' . $qapuAid . ']', $qapuOnetimeValue, $qapuShowOnetime, $qapuRowId, sprintf(QAPU_LABEL_ONETIME_FOR, $qapuDescribe), $qapuRetryRow !== null); ?>
        </td>
        <td><?php echo implode(' ', $qapuNotes); ?></td>
      </tr>
<?php
    }
?>
    </tbody>
  </table>
  </div>
  <p class="help-block"><?php echo QAPU_NOTE_HELP; ?></p>
  <div class="qapu-actions">
    <button type="submit" class="btn btn-primary"><?php echo QAPU_BUTTON_SAVE; ?></button>
    <span class="qapu-changed-count" id="qapuChanged" aria-live="polite" data-pattern="<?php echo $qapuEsc(QAPU_CHANGED_COUNT); ?>"></span>
  </div>
  </form>

<?php if ($qapuFound['pages'] > 1) { ?>
  <nav aria-label="<?php echo QAPU_PAGINATION_LABEL; ?>">
    <ul class="pagination">
<?php
        $qapuCurrent = (int)$qapuFound['page'];
        $qapuFirst = max(1, $qapuCurrent - 3);
        $qapuLast = min((int)$qapuFound['pages'], $qapuCurrent + 3);
        if ($qapuCurrent > 1) {
            echo '      <li><a href="' . $qapuPageUrl($qapuCurrent - 1) . '" rel="prev">' . QAPU_PAGE_PREVIOUS . '</a></li>' . "\n";
        }
        if ($qapuFirst > 1) {
            echo '      <li><a href="' . $qapuPageUrl(1) . '">1</a></li>' . "\n";
            if ($qapuFirst > 2) {
                echo '      <li class="disabled"><span>&hellip;</span></li>' . "\n";
            }
        }
        for ($qapuN = $qapuFirst; $qapuN <= $qapuLast; $qapuN++) {
            if ($qapuN === $qapuCurrent) {
                echo '      <li class="active"><span aria-current="page">' . $qapuN . '</span></li>' . "\n";
            } else {
                echo '      <li><a href="' . $qapuPageUrl($qapuN) . '">' . $qapuN . '</a></li>' . "\n";
            }
        }
        if ($qapuLast < (int)$qapuFound['pages']) {
            if ($qapuLast < (int)$qapuFound['pages'] - 1) {
                echo '      <li class="disabled"><span>&hellip;</span></li>' . "\n";
            }
            echo '      <li><a href="' . $qapuPageUrl($qapuFound['pages']) . '">' . (int)$qapuFound['pages'] . '</a></li>' . "\n";
        }
        if ($qapuCurrent < (int)$qapuFound['pages']) {
            echo '      <li><a href="' . $qapuPageUrl($qapuCurrent + 1) . '" rel="next">' . QAPU_PAGE_NEXT . '</a></li>' . "\n";
        }
?>
    </ul>
  </nav>
<?php } ?>
<?php } ?>
</main>
<!-- body_eof //-->

<script>
(function () {
    'use strict';

    // The Option Value list shows only the chosen option's values.
    var optionSelect = document.getElementById('qapuOid');
    var valueSelect = document.getElementById('qapuVid');
    function filterValues() {
        if (!optionSelect || !valueSelect) {
            return;
        }
        var oid = optionSelect.value;
        var groups = valueSelect.querySelectorAll('optgroup');
        for (var g = 0; g < groups.length; g++) {
            var show = oid === '' || groups[g].getAttribute('data-oid') === oid;
            groups[g].hidden = !show;
            groups[g].disabled = !show;
            if (!show && valueSelect.selectedIndex > 0 && valueSelect.options[valueSelect.selectedIndex].parentNode === groups[g]) {
                valueSelect.selectedIndex = 0;
            }
        }
    }
    if (optionSelect) {
        optionSelect.addEventListener('change', filterValues);
        filterValues();
    }
    // Choosing a value under "All Option Names" picks its option too, so the search
    // means "Size: 1 inch", not "1 inch" under any option.
    if (optionSelect && valueSelect) {
        valueSelect.addEventListener('change', function () {
            var chosen = valueSelect.options[valueSelect.selectedIndex];
            var group = chosen ? chosen.parentNode : null;
            if (group && group.tagName === 'OPTGROUP' && optionSelect.value === '') {
                optionSelect.value = group.getAttribute('data-oid');
                filterValues();
            }
        });
    }

    var form = document.getElementById('qapuForm');
    if (!form) {
        return;
    }
    var inputs = form.querySelectorAll('.qapu-input');
    var counter = document.getElementById('qapuChanged');
    var pattern = counter ? counter.getAttribute('data-pattern') : '';
    var changedRows = 0;
    var submitting = false;

    // Highlighting only: the server decides what really changed.
    function same(a, b) {
        a = String(a).replace(/,/g, '').trim();
        b = String(b).replace(/,/g, '').trim();
        if (a === b) {
            return true;
        }
        return /^-?\d*\.?\d+$/.test(a) && /^-?\d*\.?\d+$/.test(b) && parseFloat(a) === parseFloat(b);
    }

    function refresh() {
        var rows = {};
        var i;
        for (i = 0; i < inputs.length; i++) {
            var changed = !same(inputs[i].value, inputs[i].getAttribute('data-was'));
            inputs[i].classList.toggle('qapu-changed', changed);
            var rowId = inputs[i].getAttribute('data-row');
            rows[rowId] = rows[rowId] || changed;
        }
        changedRows = 0;
        for (var id in rows) {
            if (Object.prototype.hasOwnProperty.call(rows, id)) {
                var tr = document.getElementById(id);
                if (tr) {
                    tr.classList.toggle('qapu-row-changed', rows[id]);
                }
                if (rows[id]) {
                    changedRows++;
                }
            }
        }
        if (counter) {
            counter.textContent = changedRows > 0 ? pattern.replace('%s', String(changedRows)) : '';
        }
    }

    form.addEventListener('input', refresh);
    form.addEventListener('change', refresh);
    form.addEventListener('submit', function () {
        submitting = true;
    });
    window.addEventListener('beforeunload', function (e) {
        if (!submitting && changedRows > 0) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });
    refresh();
}());
</script>

<!-- footer //-->
<?php require DIR_WS_INCLUDES . 'footer.php'; ?>
<!-- footer_eof //-->
</body>
</html>
<?php require DIR_WS_INCLUDES . 'application_bottom.php'; ?>
