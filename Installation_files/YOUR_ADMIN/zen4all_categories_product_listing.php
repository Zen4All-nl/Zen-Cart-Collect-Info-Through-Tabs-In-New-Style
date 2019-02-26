<?php
/**
 * @package admin
 * @copyright (c) 2008-2018, Zen4All
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Author: Zen4All
 */
require('includes/application_top.php');
$languages = zen_get_languages();
require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

$product_type = (isset($_POST['products_id']) ? zen_get_products_type($_POST['products_id']) : (isset($_GET['product_type']) ? $_GET['product_type'] : 1));

$type_admin_handler = $zc_products->get_admin_handler($product_type);

$action = (isset($_GET['action']) ? $_GET['action'] : '');

if (isset($_GET['page'])) {
  $_GET['page'] = (int)$_GET['page'];
}
if (isset($_GET['product_type'])) {
  $_GET['product_type'] = (int)$_GET['product_type'];
}
if (isset($_GET['cID'])) {
  $_GET['cID'] = (int)$_GET['cID'];
}

$zco_notifier->notify('NOTIFY_BEGIN_ADMIN_CATEGORIES', $action);

if ((!isset($_SESSION['categories_products_sort_order']) && $_SESSION['categories_products_sort_order'] != '') || $_GET['reset_sort_order'] == '1') {
  $_SESSION['categories_products_sort_order'] = CATEGORIES_PRODUCTS_SORT_ORDER;
} else {
  $_SESSION['categories_products_sort_order'] = $_GET['list_order'];
}

if (zen_not_null($action)) {
  switch ($action) {
    case 'set_editor':
      // Reset will be done by init_html_editor.php. Now we simply redirect to refresh page properly.
      $action = '';
      zen_redirect(zen_href_link(FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, 'cPath=' . $_GET['cPath'] . ((isset($_GET['pID']) && !empty($_GET['pID'])) ? '&pID=' . $_GET['pID'] : '') . ((isset($_GET['page']) && !empty($_GET['page'])) ? '&page=' . $_GET['page'] : '')));
      break;
    case 'delete_product_confirm':
      $delete_linked = 'true';
      if (isset($_POST['delete_linked']) && $_POST['delete_linked'] == 'delete_linked_no') {
        $delete_linked = 'false';
      } else {
        $delete_linked = 'true';
      }
      require(DIR_WS_MODULES . 'zen4all_delete_product_confirm.php');
      break;
    case 'move_product_confirm':
      require(DIR_WS_MODULES . 'zen4all_move_product_confirm.php');
      break;
    case 'copy_product_confirm':
      require(DIR_WS_MODULES . 'zen4all_copy_product_confirm.php');
      break;
    case 'setflag_categories':
    case 'delete_category':
    case 'move_category':
    case 'delete_product':
    case 'move_product':
    case 'copy_product':
    case 'attribute_features':
    case 'attribute_features_copy_to_product':
    case 'attribute_features_copy_to_category':
      break;
    default:
      $action = $_GET['action'] = '';
      break;
  }
}

// check if the catalog image directory exists
if (is_dir(DIR_FS_CATALOG_IMAGES)) {
  if (!is_writeable(DIR_FS_CATALOG_IMAGES)) {
    $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE, 'error');
  }
} else {
  $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST, 'error');
}
$selectActions = array(
  array('id' => '', 'text' => PLEASE_SELECT),
  array('id' => 'move', 'text' => ACTION_MOVE),
  array('id' => 'move', 'text' => ACTION_DELETE),
  array('id' => 'move', 'text' => ACTION_COPY)
);
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>
    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
    </script>
    <style>
      .attributes-on {color: #000;}
      .pricemanager-on {color: #000;}
      .metatags-on {color: #000;}
      .fa-folder, .fa-folder-open {color: burlywood;}
      .folder, .folder:hover {
          text-decoration: none;
          color: #000;
      }
      .folder:hover .fa-folder:before {
          content: "\f07c";
      }
      .noWrap {
        white-space: nowrap;
      }
    </style>
    <?php
    if ($action != 'edit_category_meta_tags') { // bof: categories meta tags
      if ($editor_handler != '') {
        include ($editor_handler);
      }
    } // meta tags disable editor eof: categories meta tags
    ?>
  </head>
  <body onload="init();">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <!-- body //-->
    <div class="container-fluid">
      <h1><?php echo HEADING_TITLE; ?>&nbsp;-&nbsp;<?php echo zen_output_generated_category_path($current_category_id); ?></h1>
      <div class="col-md-4">
        <table class="table-condensed">
          <thead>
            <tr>
              <th class="smallText"><?php echo TEXT_LEGEND; ?></th>
              <th class="text-center smallText"><?php echo TEXT_LEGEND_STATUS_OFF; ?></th>
              <th class="text-center smallText"><?php echo TEXT_LEGEND_STATUS_ON; ?></th>
              <th class="text-center smallText"><?php echo TEXT_LEGEND_LINKED; ?></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td></td>
              <td class="text-center"><span class="btn btn-xs btn-danger" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>">&nbsp;</span></td>
              <td class="text-center"><span class="btn btn-xs btn-success" title="<?php echo IMAGE_ICON_STATUS_ON; ?>">&nbsp;</span></td>
              <td class="text-center"><span class="btn btn-xs btn-warning" title="<?php echo IMAGE_ICON_LINKED; ?>">&nbsp;</span></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="col-md-4">
        <div>
            <?php
            // toggle switch for editor
            echo zen_draw_form('set_editor_form', FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, '', 'get', 'class="form-horizontal"');
            echo zen_draw_label(TEXT_EDITOR_INFO, 'reset_editor', 'class="col-sm-6 col-md-4 control-label"');
            echo '<div class="col-sm-6 col-md-8">' . zen_draw_pull_down_menu('reset_editor', $editors_pulldown, $current_editor_key, 'onchange="this.form.submit();" class="form-control"') . '</div>';
            echo zen_hide_session_id();
            echo zen_draw_hidden_field('cID', $cPath);
            echo zen_draw_hidden_field('cPath', $cPath);
            echo (isset($_GET['pID']) ? zen_draw_hidden_field('pID', $_GET['pID']) : '');
            echo (isset($_GET['page']) ? zen_draw_hidden_field('page', $_GET['page']) : '');
            echo zen_draw_hidden_field('action', 'set_editor');
            echo '</form>';
            ?>
        </div>
        <?php
        if (!isset($_GET['page'])) {
          $_GET['page'] = '';
        }
        if (isset($_GET['set_display_categories_dropdown'])) {
          $_SESSION['display_categories_dropdown'] = $_GET['set_display_categories_dropdown'];
        }
        if (!isset($_SESSION['display_categories_dropdown'])) {
          $_SESSION['display_categories_dropdown'] = 0;
        }
        ?>
      </div>
      <div class="col-md-4">
        <div>
            <?php echo zen_draw_form('search', FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, '', 'get', 'class="form-horizontal"'); ?>
          <div class="col-sm-6 col-md-4 control-label">
              <?php echo zen_draw_label(HEADING_TITLE_SEARCH_DETAIL, 'search'); ?>
          </div>
          <div class="col-sm-6 col-md-8"><?php echo zen_draw_input_field('search', '', ($action == '' ? 'autofocus="autofocus"' : '') . 'class="form-control"'); ?></div>
          <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '100%', '1'); ?></div>
          <?php
          echo zen_hide_session_id();
          if (isset($_GET['search']) && zen_not_null($_GET['search'])) {
            $keywords = zen_db_input(zen_db_prepare_input($_GET['search']));
            ?>
            <div class="col-sm-6 col-md-4 control-label"><?php echo TEXT_INFO_SEARCH_DETAIL_FILTER; ?></div>
            <div class="col-sm-6 col-md-8">
                <?php echo zen_output_string_protected($_GET['search']); ?>
                <?php if (isset($_GET['search']) && zen_not_null($_GET['search'])) { ?>
                <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING); ?>" class="btn btn-default" role="button"><?php echo IMAGE_RESET; ?></a>
              <?php } ?>
            </div>
            <?php
          }
          echo '</form>';
          ?>
        </div>
        <div>
          <div class="col-sm-6 col-md-4 text-right">
              <?php echo zen_draw_label(HEADING_TITLE_GOTO, 'cPath', 'class="control-label"'); ?>
          </div>
          <div class="col-sm-6 col-md-8">
              <?php echo zen_draw_form('goto', FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, '', 'get', 'class="form-horizontal"'); ?>
              <?php echo zen_hide_session_id(); ?>
              <?php echo zen_draw_pull_down_menu('cPath', zen_get_category_tree(), $current_category_id, 'onchange="this.form.submit();" class="form-control"'); ?>
              <?php echo '</form>'; ?>
          </div>
        </div>
      </div>
      <div class="row"><?php echo zen_draw_separator('pixel_black.gif', '100%', '1px'); ?></div>
      <div class="row">
        <div class="table-responsive">
            <?php //echo zen_draw_form('listing', FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, zen_get_all_get_params(), 'post', 'class="form-horizontal"'); ?>
          <form name="listing" class="form-horizontal">
            <table class="table table-striped">
              <thead>
                <tr valign="middle">
                  <th><?php echo zen_draw_checkbox_field('', '', false, '', 'id="select_all"'); ?></th>
                  <th class="text-right noWrap">
                    <?php echo (($_GET['list_order'] == 'id-asc' || $_GET['list_order_'] == 'id-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_ID . '</span>' : TABLE_HEADING_ID); ?>&nbsp;<a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=id-asc'); ?>"><?php echo ($_GET['list_order'] == 'id-asc' ? '<i class="fa fa-caret-down fa-2x SortOrderHeader"></i>' : '<i class="fa fa-caret-down fa-2x SortOrderHeaderLink"></i>'); ?></a>&nbsp;<a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=id-desc'); ?>"><?php echo ($_GET['list_order'] == 'id-desc' ? '<i class="fa fa-caret-up fa-2x SortOrderHeader"></i>' : '<i class="fa fa-caret-up fa-2x SortOrderHeaderLink"></i>'); ?></a>
                  </th>
                  <th class="noWrap">
                    <?php echo (($_GET['list_order'] == 'name-asc' || $_GET['list_order_'] == 'name-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_CATEGORIES_PRODUCTS . '</span>' : TABLE_HEADING_CATEGORIES_PRODUCTS); ?>&nbsp;<a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=name-asc'); ?>"><?php echo ($_GET['list_order'] == 'name-asc' ? '<i class="fa fa-caret-down fa-2x SortOrderHeader"></i>' : '<i class="fa fa-caret-down fa-2x SortOrderHeaderLink"></i>'); ?></a>&nbsp;<a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=name-desc'); ?>"><?php echo ($_GET['list_order'] == 'name-desc' ? '<i class="fa fa-caret-up fa-2x SortOrderHeader"></i>' : '<i class="fa fa-caret-up fa-2x SortOrderHeaderLink"></i>'); ?></a>
                  </th>
                  <th class="hidden-sm hidden-xs noWrap">
                    <?php echo (($_GET['list_order'] == 'model-asc' || $_GET['list_order_'] == 'model-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_MODEL . '</span>' : TABLE_HEADING_MODEL); ?>&nbsp;<a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=model-asc'); ?>"><?php echo ($_GET['list_order'] == 'model-asc' ? '<i class="fa fa-caret-down fa-2x SortOrderHeader"></i>' : '<i class="fa fa-caret-down fa-2x SortOrderHeaderLink"></i>'); ?></a>&nbsp;<a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=model-desc'); ?>"><?php echo ($_GET['list_order'] == 'model-desc' ? '<i class="fa fa-caret-up fa-2x SortOrderHeader"></i>' : '<i class="fa fa-caret-up fa-2x SortOrderHeaderLink"></i>'); ?></a>
                  </th>
                  <th class="text-right hidden-sm hidden-xs noWrap">
                    <?php echo (($_GET['list_order'] == 'price-asc' || $_GET['list_order_'] == 'price-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_PRICE . '</span>' : TABLE_HEADING_PRICE); ?>&nbsp;<a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=price-asc'); ?>"><?php echo ($_GET['list_order'] == 'price-asc' ? '<i class="fa fa-caret-down fa-2x SortOrderHeader"></i>' : '<i class="fa fa-caret-down fa-2x SortOrderHeaderLink"></i>'); ?></a>&nbsp;<a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=price-desc'); ?>"><?php echo ($_GET['list_order'] == 'price-desc' ? '<i class="fa fa-caret-up fa-2x SortOrderHeader"></i>' : '<i class="fa fa-caret-up fa-2x SortOrderHeaderLink"></i>'); ?></a>
                  </th>
                  <th class="hidden-sm hidden-xs">&nbsp;</th>
                  <th class="text-right hidden-sm hidden-xs noWrap">
                    <?php echo (($_GET['list_order'] == 'quantity-asc' || $_GET['list_order_'] == 'quantity-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_QUANTITY . '</span>' : TABLE_HEADING_QUANTITY); ?>&nbsp;<a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=quantity-asc'); ?>"><?php echo ($_GET['list_order'] == 'quantity-asc' ? '<i class="fa fa-caret-down fa-2x SortOrderHeader"></i>' : '<i class="fa fa-caret-down fa-2x SortOrderHeaderLink"></i>'); ?></a>&nbsp;<a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=quantity-desc'); ?>"><?php echo ($_GET['list_order'] == 'quantity-desc' ? '<i class="fa fa-caret-up fa-2x SortOrderHeader"></i>' : '<i class="fa fa-caret-up fa-2x SortOrderHeaderLink"></i>'); ?></a>
                  </th>
                  <th class="text-right hidden-sm hidden-xs"><?php echo TABLE_HEADING_STATUS; ?></th>
                  <th class="text-right hidden-sm hidden-xs noWrap">
                    <?php echo (($_GET['list_order'] == 'sort_order-asc' || $_GET['list_order'] == 'sort_order-desc') ? '<span class="SortOrderHeader">' . TABLE_HEADING_CATEGORIES_SORT_ORDER . '</span>' : TABLE_HEADING_CATEGORIES_SORT_ORDER); ?>&nbsp;<a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=sort_order-asc'); ?>"><?php echo ($_GET['list_order'] == 'sort_order-asc' ? '<i class="fa fa-caret-down fa-2x SortOrderHeader"></i>' : '<i class="fa fa-caret-down fa-2x SortOrderHeaderLink"></i>'); ?></a>&nbsp;<a href="<?php echo zen_href_link(basename($PHP_SELF), zen_get_all_get_params(array('list_order', 'page')) . 'list_order=sort_order-desc'); ?>"><?php echo ($_GET['list_order'] == 'sort_order-desc' ? '<i class="fa fa-caret-up fa-2x SortOrderHeader"></i>' : '<i class="fa fa-caret-up fa-2x SortOrderHeaderLink"></i>'); ?></a>
                  </th>
                  <th class="text-right"><?php echo TABLE_HEADING_ACTION; ?></th>
                </tr>
              </thead>
              <tbody>
                  <?php
                  switch ($_SESSION['categories_products_sort_order']) {
                    case 'id-asc' :
                      $order_by = "c.categories_id ASC";
                      break;
                    case 'id-desc' :
                      $order_by = "c.categories_id DESC";
                      break;
                    case '1' :
                    case 'name-asc' :
                      $order_by = "cd.categories_name ASC";
                      break;
                    case 'name-desc' :
                      $order_by = "cd.categories_name DESC";
                      break;
                    case 'sort_order-desc' :
                      $order_by = "c.sort_order DESC, cd.categories_name DESC";
                      break;
                    case '0' :
                    case 'sort_order-asc' :
                    default :
                      $order_by = "c.sort_order ASC, cd.categories_name ASC";
                  }

                  $categories_count = 0;
                  $rows = 0;
                  if (isset($_GET['search'])) {
                    $search = zen_db_prepare_input($_GET['search']);

                    $categories = $db->Execute("SELECT c.categories_id, cd.categories_name, cd.categories_description, c.categories_image,
                                                       c.parent_id, c.sort_order, c.date_added, c.last_modified, c.categories_status
                                                FROM " . TABLE_CATEGORIES . " c,
                                                     " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                                WHERE c.categories_id = cd.categories_id
                                                AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                                                AND cd.categories_name LIKE '%" . zen_db_input($search) . "%'
                                                ORDER BY " . $order_by);
                  } else {
                    $categories = $db->Execute("SELECT c.categories_id, cd.categories_name, cd.categories_description, c.categories_image,
                                                       c.parent_id, c.sort_order, c.date_added, c.last_modified, c.categories_status
                                                FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
                                                WHERE c.parent_id = " . (int)$current_category_id . "
                                                AND c.categories_id = cd.categories_id
                                                AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                                                ORDER BY " . $order_by);
                  }
                  foreach ($categories as $category) {
                    $categories_count++;
                    $rows++;

// Get parent_id for subcategories if search
                    if (isset($_GET['search'])) {
                      $cPath = $category['parent_id'];
                    }

                    if ((!isset($_GET['cID']) && !isset($_GET['pID']) || (isset($_GET['cID']) && ($_GET['cID'] == $category['categories_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
                      //$category_childs = array('childs_count' => zen_childs_in_category_count($category['categories_id']));
                      //$category_products = array('products_count' => zen_products_in_category_count($category['categories_id']));
                      //$cInfo_array = array_merge($category, $category_childs, $category_products);
                      $cInfo = new objectInfo($category);
                    }
                    ?>
                  <tr>
                    <td><?php echo zen_draw_checkbox_field('selected_categories[]', $category['categories_id']); ?></td>
                    <td class="text-right"><?php echo $category['categories_id']; ?></td>
                    <td>
                      <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, zen_get_path($category['categories_id'])); ?>" class="folder">
                        <i class="fa fa-lg fa-folder"></i>&nbsp;<strong><?php echo $category['categories_name']; ?></strong></a>
                    </td>
                    <td class="text-center hidden-sm hidden-xs">&nbsp;</td>
                    <td class="text-right hidden-sm hidden-xs"><?php echo zen_get_products_sale_discount('', $category['categories_id'], true); ?></td>
                    <td class="text-center hidden-sm hidden-xs">&nbsp;</td>
                    <td class="text-right hidden-sm hidden-xs">
                        <?php
                        if (SHOW_COUNTS_ADMIN == 'false') {
                          // don't show counts
                        } else {
                          // show counts
                          $total_products = zen_get_products_to_categories($category['categories_id'], true);
                          $total_products_on = zen_get_products_to_categories($category['categories_id'], false);
                          echo $total_products_on . TEXT_PRODUCTS_STATUS_ON_OF . $total_products . TEXT_PRODUCTS_STATUS_ACTIVE;
                        }
                        ?>
                    </td>
                    <td class="text-right hidden-sm hidden-xs">
                        <?php if (SHOW_CATEGORY_PRODUCTS_LINKED_STATUS == 'true' && zen_get_products_to_categories($category['categories_id'], true, 'products_active') == 'true') { ?>
                        <span class="btn btn-xs btn-warning" title="<?php echo IMAGE_ICON_LINKED; ?>">&nbsp;</span>
                      <?php } ?>
                      <?php if ($category['categories_status'] == '1') { ?>
                        <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, 'action=setflag_categories&flag=0&cID=' . $category['categories_id'] . '&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')); ?>" class="btn btn-xs btn-success" title="<?php echo IMAGE_ICON_STATUS_ON; ?>">&nbsp;</a>
                      <?php } else { ?>
                        <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, 'action=setflag_categories&flag=1&cID=' . $category['categories_id'] . '&cPath=' . $cPath . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '') . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')); ?>" class="btn btn-xs btn-success" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>">&nbsp;</a>
                      <?php } ?>
                    </td>
                    <td class="text-right hidden-sm hidden-xs"><?php echo $category['sort_order']; ?></td>
                    <td class="text-right">
                      <div class="btn-group">
                        <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $category['categories_id'] . ((isset($_GET['search']) && !empty($_GET['search'])) ? '&search=' . $_GET['search'] : '')); ?>" title="<?php echo TEXT_LISTING_EDIT; ?>" class="btn btn-sm btn-info" role="button">
                          <i class="fa fa-pencil fa-lg" aria-hidden="true"></i>
                        </a>
                        <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, 'cPath=' . $cPath . '&cID=' . $category['categories_id'] . '&action=delete_category'); ?>" title="<?php echo TEXT_LISTING_DELETE; ?>" class="btn btn-sm btn-info" role="button">
                          <i class="fa fa-trash-o fa-lg" aria-hidden="true"></i>
                        </a>
                        <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, 'cPath=' . $cPath . '&cID=' . $category['categories_id'] . '&action=move_category'); ?>" title="<?php echo TEXT_LISTING_MOVE; ?>" class="btn btn-sm btn-info" role="button">
                          <i class="fa fa-arrows fa-lg" aria-hidden="true"></i>
                        </a>
                        <?php
// bof: categories meta tags
                        if (zen_get_category_metatags_keywords($category['categories_id'], (int)$_SESSION['languages_id']) or zen_get_category_metatags_description($category['categories_id'], (int)$_SESSION['languages_id'])) {
                          ?>
                          <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $category['categories_id'] . '&action=edit_category_meta_tags' . '&activeTab=categoryTabs4'); ?>" title="<?php echo TEXT_LISTING_EDIT_META_TAGS; ?>" class="btn btn-sm btn-info" role="button">
                            <i class="fa fa-asterisk fa-lg" aria-hidden="true"></i>
                          </a>
                        <?php } else { ?>
                          <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $category['categories_id'] . '&action=edit_category_meta_tags' . '&activeTab=categoryTabs4'); ?>" title="<?php echo TEXT_LISTING_EDIT_META_TAGS; ?>" class="btn btn-sm btn-info" role="button">
                            <i class="fa fa-asterisk fa-lg" aria-hidden="true"></i>
                          </a>
                        <?php } ?>
                      </div>
                    </td>
                  </tr>
                  <?php
                }

                switch ($_SESSION['categories_products_sort_order']) {
                  case (0):
                    $order_by = "p.products_sort_order, pd.products_name";
                    break;
                  case (1):
                    $order_by = "pd.products_name";
                    break;
                  case (2);
                    $order_by = "p.products_model";
                    break;
                  case (3);
                    $order_by = "p.products_quantity, pd.products_name";
                    break;
                  case (4);
                    $order_by = "p.products_quantity DESC, pd.products_name";
                    break;
                  case (5);
                    $order_by = "p.products_price_sorter, pd.products_name";
                    break;
                  case (6);
                    $order_by = "p.products_price_sorter DESC, pd.products_name";
                    break;
                }

                $products_count = 0;
                if (isset($_GET['search']) && !empty($_GET['search']) && $action != 'edit_category') {
                  $products_query_raw = ("SELECT p.products_type, p.products_id, pd.products_name, p.products_quantity, p.products_image, p.products_price,
                                                 p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p2c.categories_id,
                                                 p.products_model, p.products_quantity_order_min, p.products_quantity_order_units, p.products_priced_by_attribute,
                                                 p.product_is_free, p.product_is_call, p.products_quantity_mixed, p.product_is_always_free_shipping,
                                                 p.products_quantity_order_max, p.products_sort_order, p.master_categories_id
                                          FROM " . TABLE_PRODUCTS . " p,
                                               " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                                               " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                          WHERE p.products_id = pd.products_id
                                          AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                          AND (p.products_id = p2c.products_id
                                            AND p.master_categories_id = p2c.categories_id)
                                          AND (pd.products_name LIKE '%" . zen_db_input($_GET['search']) . "%'
                                            OR pd.products_description LIKE '%" . zen_db_input($_GET['search']) . "%'
                                            OR p.products_id = '" . zen_db_input($_GET['search']) . "'
                                            OR p.products_model like '%" . zen_db_input($_GET['search']) . "%')
                                          ORDER BY " . $order_by);
                } else {
                  $products_query_raw = ("SELECT p.products_type, p.products_id, pd.products_name, p.products_quantity, p.products_image, p.products_price,
                                                 p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.products_model,
                                                 p.products_quantity_order_min, p.products_quantity_order_units, p.products_priced_by_attribute, p.product_is_free,
                                                 p.product_is_call, p.products_quantity_mixed, p.product_is_always_free_shipping, p.products_quantity_order_max,
                                                 p.products_sort_order
                                          FROM " . TABLE_PRODUCTS . " p,
                                               " . TABLE_PRODUCTS_DESCRIPTION . " pd,
                                               " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c
                                          WHERE p.products_id = pd.products_id
                                          AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                                          AND p.products_id = p2c.products_id
                                          AND p2c.categories_id = " . (int)$current_category_id . "
                                          ORDER BY " . $order_by);
                }
// Split Page
// reset page when page is unknown
                if ((isset($_GET['page']) && ($_GET['page'] == '1' || $_GET['page'] == '')) && isset($_GET['pID']) && $_GET['pID'] != '') {
                  $old_page = $_GET['page'];
                  $check_page = $db->Execute($products_query_raw);
                  if ($check_page->RecordCount() > MAX_DISPLAY_RESULTS_CATEGORIES) {
                    $check_count = 1;
                    foreach ($check_page as $item) {
                      if ($item['products_id'] == $_GET['pID']) {
                        break;
                      }
                      $check_count++;
                    }
                    $_GET['page'] = round((($check_count / MAX_DISPLAY_RESULTS_CATEGORIES) + (fmod_round($check_count, MAX_DISPLAY_RESULTS_CATEGORIES) != 0 ? .5 : 0)), 0);
                    $page = $_GET['page'];
                    if ($old_page != $_GET['page']) {
//      zen_redirect(zen_href_link(FILENAME_CATEGORIES, 'cPath=' . $_GET['cPath'] . '&pID=' . $_GET['pID'] . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')));
                    }
                  } else {
                    $_GET['page'] = 1;
                  }
                }
                $products_split = new splitPageResults($_GET['page'], MAX_DISPLAY_RESULTS_CATEGORIES, $products_query_raw, $products_query_numrows);
                $products = $db->Execute($products_query_raw);
// Split Page

                foreach ($products as $product) {
                  $products_count++;
                  $rows++;

// Get categories_id for product if search
                  if (isset($_GET['search'])) {
                    $cPath = $product['categories_id'];
                  }

                  if ((!isset($_GET['pID']) && !isset($_GET['cID']) || (isset($_GET['pID']) && ($_GET['pID'] == $product['products_id']))) && !isset($pInfo) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
                    $pInfo = new objectInfo($product);
                  }

                  $type_handler = $zc_products->get_handler($product['products_type']);
                  ?>
                  <tr>
                    <td><?php echo zen_draw_checkbox_field('selected_products[]', $product['products_id']); ?></td>
                    <td class="text-right"><?php echo $product['products_id']; ?></td>
                    <td><a href="<?php echo zen_catalog_href_link($type_handler . '_info', 'cPath=' . $cPath . '&products_id=' . $product['products_id'] . '&language=' . $_SESSION['languages_code'] . '&product_type=' . $product['products_type']); ?>" target="_blank"><?php echo zen_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW); ?></a>&nbsp;<?php echo $product['products_name']; ?></td>
                    <td class="hidden-sm hidden-xs"><?php echo $product['products_model']; ?></td>
                    <td colspan="2" class="text-right hidden-sm hidden-xs"><?php echo zen_get_products_display_price($product['products_id']); ?></td>
                    <td class="text-right hidden-sm hidden-xs"><?php echo $product['products_quantity']; ?></td>
                    <td class="text-right hidden-sm hidden-xs">
                        <?php if (zen_get_product_is_linked($product['products_id']) == 'true') { ?>
                        <span class="btn btn-xs btn-warning" title="<?php echo IMAGE_ICON_LINKED; ?>">&nbsp;</span>&nbsp;
                      <?php } ?>
                      <?php if ($product['products_status'] == '1') { ?>
                        <button type="button" id="flag_<?php echo $product['products_id']; ?>" title="<?php echo IMAGE_ICON_STATUS_ON; ?>" onclick="setProductFlag('<?php echo $product['products_id']; ?>', '0')" class="btn btn-xs btn-success">&nbsp;</button>
                      <?php } else { ?>
                        <button type="button" id="flag_<?php echo $product['products_id']; ?>" title="<?php echo IMAGE_ICON_STATUS_OFF; ?>" onclick="setProductFlag('<?php echo $product['products_id']; ?>', '1')" class="btn btn-xs btn-danger">&nbsp;</button>
                      <?php } ?>
                    </td>
                    <td class="text-right hidden-sm hidden-xs"><?php echo $product['products_sort_order']; ?></td>
                    <td class="text-right">
                      <div class="btn-group">
                        <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_PRODUCT, 'cPath=' . $cPath . '&product_type=' . $product['products_type'] . '&pID=' . $product['products_id'] . '&action=new_product' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : '')); ?>" title="<?php echo TEXT_LISTING_EDIT; ?>" class="btn btn-sm btn-info" role="button">
                          <i class="fa fa-pencil fa-lg" aria-hidden="true"></i>
                        </a>
                        <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, 'cPath=' . $cPath . '&product_type=' . $product['products_type'] . '&pID=' . $product['products_id'] . '&action=delete_product'); ?>" title="<?php echo TEXT_LISTING_DELETE; ?>" class="btn btn-sm btn-info" role="button">
                          <i class="fa fa-trash-o fa-lg" aria-hidden="true"></i>
                        </a>
                        <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, 'cPath=' . $cPath . '&product_type=' . $product['products_type'] . '&pID=' . $product['products_id'] . '&action=move_product'); ?>" title="<?php echo TEXT_LISTING_MOVE; ?>" class="btn btn-sm btn-info" role="button">
                          <i class="fa fa-arrows fa-lg" aria-hidden="true"></i>
                        </a>
                        <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, 'cPath=' . $cPath . '&product_type=' . $product['products_type'] . '&pID=' . $product['products_id'] . '&action=copy_product'); ?>" title="<?php echo TEXT_LISTING_COPY; ?>" class="btn btn-sm btn-info" role="button">
                          <i class="fa fa-copy fa-lg" aria-hidden="true"></i>
                        </a>

                        <?php if (defined('FILENAME_IMAGE_HANDLER') && file_exists(DIR_FS_ADMIN . FILENAME_IMAGE_HANDLER . '.php')) { ?>
                          <a href="<?php echo zen_href_link(FILENAME_IMAGE_HANDLER, 'products_filter=' . $product['products_id'] . '&current_category_id=' . $current_category_id); ?>" title="<?php echo TEXT_LISTING_IMAGE_HANDLER; ?>" class="btn btn-sm btn-info" role="button">
                            <i class="fa fa-lg fa-image" aria-hidden="true"></i>
                          </a>
                        <?php } ?>
                        <?php if (zen_has_product_attributes($product['products_id'], 'false')) { ?>
                          <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $product['products_id'] . '&action=attribute_features' . (isset($_GET['page']) ? '&page=' . $_GET['page'] : '')); ?>" title="<?php echo TEXT_LISTING_ATTRIBUTES; ?>" class="btn btn-sm btn-info" role="button">
                            <i class="fa fa-list fa-lg attributes-on" aria-hidden="true"></i>
                          </a>
                        <?php } else { ?>
                          <a href="<?php echo zen_href_link(FILENAME_ATTRIBUTES_CONTROLLER, 'products_filter=' . $product['products_id'] . '&current_category_id=' . $current_category_id); ?>" title="<?php echo TEXT_LISTING_ATTRIBUTES; ?>" class="btn btn-sm btn-info" role="button">
                            <i class="fa fa-list fa-lg" aria-hidden="true"></i>
                          </a>
                        <?php } ?>
                        <?php if ($zc_products->get_allow_add_to_cart($product['products_id']) == 'Y') { ?>
                          <a href="<?php echo zen_href_link(FILENAME_PRODUCTS_PRICE_MANAGER, 'products_filter=' . $product['products_id'] . '&current_category_id=' . $current_category_id); ?>" title="<?php echo TEXT_LISTING_PRICE_MANAGER; ?>" class="btn btn-sm btn-info" role="button">
                            <i class="fa fa-dollar fa-lg pricemanager-on" aria-hidden="true"></i>
                          </a>
                        <?php } else { ?>
                          <a href="#" class="btn btn-sm btn-info" role="button">
                            <i class="fa fa-dollar fa-lg" aria-hidden="true"></i>
                          </a>
                          <?php
                        }
// meta tags
                        if (zen_get_metatags_keywords($product['products_id'], (int)$_SESSION['languages_id']) || zen_get_metatags_description($product['products_id'], (int)$_SESSION['languages_id'])) {
                          ?>
                          <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_PRODUCT, 'page=' . $_GET['page'] . '&product_type=' . $product['products_type'] . '&cPath=' . $cPath . '&pID=' . $product['products_id']); ?>" title="<?php echo TEXT_LISTING_EDIT_META_TAGS; ?>" class="btn btn-sm btn-info" role="button">
                            <i class="fa fa-asterisk fa-lg metatags-on" aria-hidden="true"></i>
                          </a>
                        <?php } else { ?>
                          <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_PRODUCT, 'page=' . $_GET['page'] . '&product_type=' . $product['products_type'] . '&cPath=' . $cPath . '&pID=' . $product['products_id']); ?>" title="<?php echo TEXT_LISTING_EDIT_META_TAGS; ?>" class="btn btn-sm btn-info" role="button">
                            <i class="fa fa-asterisk fa-lg" aria-hidden="true"></i>
                          </a>
                        <?php } ?>
                      </div>
                    </td>
                  </tr>
                <?php } ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="10">
                    <?php echo zen_draw_label(WITH_SELECTED, 'action_select', 'class="col-xs-2 col-sm-2 control-label"'); ?>
                    <div class="col-xs-3 col-sm-3"><?php echo zen_draw_pull_down_menu('action_select', $selectActions, '', 'class="form-control"'); ?></div>
                    <?php echo zen_draw_hidden_field('cPath', $cPath); ?>
                  </td>
                </tr>
              </tfoot>
            </table>
          </form>
        </div>
      </div>
      <?php
      $heading = [];
      $contents = [];
      switch ($action) {
        case 'delete_product':
          if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/zen4all_delete_product.php')) {
            require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/zen4all_delete_product.php');
          } else {
            require(DIR_WS_MODULES . 'zen4all_delete_product.php');
          }
          break;
        case 'move_product':
          if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/zen4all_move_product.php')) {
            require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/zen4all_move_product.php');
          } else {
            require(DIR_WS_MODULES . 'zen4all_move_product.php');
          }
          break;
        case 'copy_product':
          if (file_exists(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/zen4all_copy_product.php')) {
            require(DIR_WS_MODULES . $zc_products->get_handler($product_type) . '/zen4all_copy_product.php');
          } else {
            require(DIR_WS_MODULES . 'zen4all_copy_product.php');
          }
          break;
      }
      if ((zen_not_null($heading)) && (zen_not_null($contents))) {
        $box = new box;
        echo '<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 configurationColumnRight">';
        echo $box->infoBox($heading, $contents);
        echo '</div>';
      }
      ?>
      <?php
      $cPathBackRaw = '';
      if (sizeof($cPath_array) > 0) {
        for ($i = 0, $n = sizeof($cPath_array) - 1; $i < $n; $i++) {
          if (empty($cPathBackRaw)) {
            $cPathBackRaw .= $cPath_array[$i];
          } else {
            $cPathBackRaw .= '_' . $cPath_array[$i];
          }
        }
      }

      $cPath_back = (zen_not_null($cPathBackRaw)) ? 'cPath=' . $cPathBackRaw . '&' : '';
      ?>
      <div class="row">
        <div class="col-md-6"><?php echo TEXT_CATEGORIES . '&nbsp;' . $categories_count . '<br>' . TEXT_PRODUCTS . '&nbsp;' . $products_count; ?></div>
        <div class="col-md-6 text-right">
            <?php
            if (sizeof($cPath_array) > 0) {
              ?>
            <div class="col-sm-3">
              <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES_PRODUCT_LISTING, $cPath_back . 'cID=' . $current_category_id); ?>" class="btn btn-default" role="button"><?php echo IMAGE_BACK; ?></a>
            </div>
            <?php
          }
          if (!isset($_GET['search']) && !$zc_skip_categories) {
            ?>
            <div class="col-sm-3">
              <a href="<?php echo zen_href_link(FILENAME_ZEN4ALL_CATEGORIES, 'cPath=' . $cPath . '&action=new_category'); ?>" class="btn btn-primary" role="button"><?php echo IMAGE_NEW_CATEGORY; ?></a>
            </div>
            <?php
          }
          ?>

          <?php if ($zc_skip_products == false) { ?>
            <?php echo zen_draw_form('newproduct', FILENAME_ZEN4ALL_PRODUCT, 'cPath=' . $cPath . '&product_type=' . $product['products_type'] . '&action=new_product' . (isset($_GET['search']) ? '&search=' . $_GET['search'] : ''), 'post', 'class="form-horizontal"'); ?>
            <?php echo (empty($_GET['search']) ? '<div class="col-sm-3"><button type="submit" class="btn btn-primary">' . IMAGE_NEW_PRODUCT . '</button></div>' : ''); ?>
            <?php
            // Query product types based on the ones this category is restricted to
            $sql = "SELECT ptc.product_type_id as type_id, pt.type_name
                    FROM " . TABLE_PRODUCT_TYPES_TO_CATEGORY . " ptc,
                         " . TABLE_PRODUCT_TYPES . " pt
                    WHERE ptc.category_id = " . (int)$current_category_id . "
                    AND pt.type_id = ptc.product_type_id";
            $product_types = $db->Execute($sql);

            if ($product_types->RecordCount() == 0) {
              // There are no restricted product types so make we offer all types instead
              $sql = "SELECT * FROM " . TABLE_PRODUCT_TYPES;
              $product_types = $db->Execute($sql);
            }

            $product_restrict_types_array = [];

            foreach ($product_types as $restrict_type) {
              $product_restrict_types_array[] = [
                'id' => $restrict_type['type_id'],
                'text' => $restrict_type['type_name'],
              ];
            }
            ?>
            <?php
            echo '<div class="col-sm-6">' . zen_draw_pull_down_menu('product_type', $product_restrict_types_array, '', 'class="form-control"') . '</div>';
            echo zen_hide_session_id();
            echo zen_draw_hidden_field('cPath', $cPath);
            echo zen_draw_hidden_field('action', 'new_product');
            echo '</form>';
            ?>
            <?php
          } else {
            echo CATEGORY_HAS_SUBCATEGORIES;
            ?>
            <?php
          } // hide has cats
          ?>
        </div>
      </div>
      <div class="row text-center alert">
          <?php
          // warning if products are in top level categories
          $check_products_top_categories = $db->Execute("SELECT COUNT(*) AS products_errors
                                                             FROM " . TABLE_PRODUCTS_TO_CATEGORIES . "
                                                             WHERE categories_id = 0");
          if ($check_products_top_categories->fields['products_errors'] > 0) {
            echo WARNING_PRODUCTS_IN_TOP_INFO . $check_products_top_categories->fields['products_errors'] . '<br>';
          }
          ?>
      </div>
      <div class="row text-center">
        <?php
// Split Page
        if ($products_query_numrows > 0) {
          echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_RESULTS_CATEGORIES, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS) . '<br>' . $products_split->display_links($products_query_numrows, MAX_DISPLAY_RESULTS_CATEGORIES, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], zen_get_all_get_params(array('page', 'info', 'x', 'y', 'pID')));
        }
        ?>
      </div>
    </div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
    <?php require_once 'includes/javascript/zen4all_jscript_CategoriesProductListing.php'; ?>
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>