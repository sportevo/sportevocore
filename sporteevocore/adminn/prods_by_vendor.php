<?php
/*
  $ID: prods_by_vendor.php (for use with MVS) by Craig Garrison Sr, BluCollar Sales
  $Loc: /catalog/admin/ $
  $Mod: MVS V1.2 2009/02/28 JCK/CWG $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/6 Francesco Rossi

  Released under the GNU General Public License
*/

  require_once ('includes/application_top.php');

  require_once (DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  // Test changes 01-19-2008
  $line_filter = '';
  if (isset ($_GET['line']) && $_GET['line'] != '') {
    $line_filter = $_GET['line'];
    $line_filter = preg_replace("(\r\n|\n|\r)", '', $line_filter); // Remove CR &/ LF
    $line_filter = preg_replace("/[^a-z]/i", '', $line_filter); // strip anything we don't want
  }

  $vendors_id = 1;
  if (isset ($_GET['vendors_id']) && $_GET['vendors_id'] != '') {
    $vendors_id = (int) $_GET['vendors_id'];
  }

  $show_order = '';
  if (isset ($_GET['show_order']) && $_GET['show_order'] != '') {
    $show_order = $_GET['show_order'];
    $show_order = preg_replace("(\r\n|\n|\r)", '', $show_order); // Remove CR &/ LF
    $show_order = preg_replace("/[^a-z]/i", '', $show_order); // strip anything we don't want
  }

  switch ($line_filter) {
    case 'prod' :
      $sort_by_filter = 'pd.products_name';
      break;
    case 'vpid' :
      $sort_by_filter = 'p.vendors_prod_id';
      break;
    case 'pid' :
      $sort_by_filter = 'p.products_id';
      break;
    case 'qty' :
      $sort_by_filter = 'p.products_quantity';
      break;
    case 'vprice' :
      $sort_by_filter = 'p.vendors_product_price';
      break;
    case 'price' :
    case '' :
    default :
      $sort_by_filter = 'p.products_price';
      break;
  }

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require_once(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require_once(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
        </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
<?php

  $vendors_query = tep_db_query ("select vendors_id, 
                                         vendors_name 
                                  from " . TABLE_VENDORS . " 
                                  order by vendors_name
                                ");
  while ($vendors = tep_db_fetch_array ($vendors_query) ) {
    $vendors_array[] = array (
      'id' => $vendors['vendors_id'],
      'text' => $vendors['vendors_name']
    );
  }
?>
                <td class="main" align="left"><?php echo TABLE_HEADING_VENDOR_CHOOSE . ' '; ?><?php echo tep_draw_form ('vendors_report', FILENAME_PRODS_VENDORS) . tep_draw_pull_down_menu ('vendors_id', $vendors_array,'','onChange="this.form.submit()";');?></form></td>
                <td class="main" align="left"><?php echo '<a href="' . tep_href_link(FILENAME_VENDORS) . '"><b>Go To Vendors List</a>';?><td>
              </tr>
              <tr>
                <td class="main" align="left">
<?php

  if ($show_order == 'desc') {
    // Test code -- 3 lines
    echo 'Click for <a href="' . tep_href_link (FILENAME_PRODS_VENDORS, '&vendors_id=' . $vendors_id . '&line=' . $line_filter . '&show_order=asc') . '"><b>ascending order</b></a>';
  } else {
    echo 'Click for <a href="' . tep_href_link (FILENAME_PRODS_VENDORS, '&vendors_id=' . $vendors_id . '&line=' . $line_filter . '&show_order=desc') . '"><b>descending order</b></a>';
  }
?>
              </td>
            </tr>
        </table></td>
      </tr>
<?php

  if (isset ($vendors_id)) {

    // $vendors_id = $_POST['vendors_id'];
    $vend_query_raw = "select vendors_name as name from " . TABLE_VENDORS . " where vendors_id = '" . $vendors_id . "'";
    $vend_query = tep_db_query ($vend_query_raw);
    $vendors = tep_db_fetch_array ($vend_query);
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="1">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_VENDOR; ?></td>
                <td class="dataTableHeadingContent" align="left"><?php echo '<a href="' . tep_href_link(FILENAME_PRODS_VENDORS, 'vendors_id=' . $vendors_id . '&line=prod') . '">' . TABLE_HEADING_PRODUCTS_NAME . '</a>'; ?>&nbsp;</td>
                <td class="dataTableHeadingContent" align="left"><?php echo '<a href="' . tep_href_link(FILENAME_PRODS_VENDORS, 'vendors_id=' . $vendors_id . '&line=vpid') . '">' . TABLE_HEADING_VENDORS_PRODUCT_ID . '</a>'; ?></td>
                <td class="dataTableHeadingContent" align="left"><?php echo '<a href="' . tep_href_link(FILENAME_PRODS_VENDORS, 'vendors_id=' . $vendors_id . '&line=pid') . '">' .  TABLE_HEADING_PRODUCTS_ID . '</a>'; ?></td>
                <td class="dataTableHeadingContent" align="left"><?php echo '<a href="' . tep_href_link(FILENAME_PRODS_VENDORS, 'vendors_id=' . $vendors_id . '&line=qty') . '">' .  TABLE_HEADING_QUANTITY . '</a>'; ?></td>
                <td class="dataTableHeadingContent" align="left"><?php echo '<a href="' . tep_href_link(FILENAME_PRODS_VENDORS, 'vendors_id=' . $vendors_id . '&line=vprice') . '">' .  TABLE_HEADING_VENDOR_PRICE . '</a>'; ?></td>
                <td class="dataTableHeadingContent" align="left"><?php echo '<a href="' . tep_href_link(FILENAME_PRODS_VENDORS, 'vendors_id=' . $vendors_id . '&line=price') . '">' .  TABLE_HEADING_PRICE . '</a>'; ?></td>
              </tr>
              <tr class="dataTableRow">
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_VENDORS, '&vendors_id=' . $vendors_id . '&action=edit') . '" TARGET="_blank"><b>' . $vendors['name'] . '</a></b>'; ?></td>
                <td class="dataTableContent"><?php echo ''; ?></td>
                <td class="dataTableContent"><?php echo ''; ?></td>
                <td class="dataTableContent"><?php echo ''; ?></td>
                <td class="dataTableContent"><?php echo ''; ?></td>
                <td class="dataTableContent"><?php echo ''; ?></td>
                <td class="dataTableContent"><?php echo ''; ?></td>

<?php

    // if (isset($_GET['page']) && ($_GET['page'] > 1)) $rows = $_GET['page'] * MAX_DISPLAY_SEARCH_RESULTS - MAX_DISPLAY_SEARCH_RESULTS;
    $rows = 0;
    if ($show_order == 'desc') {
      $products_query_raw = "select p.products_id, p.vendors_id, pd.products_name, p.products_quantity , p.products_price, p.vendors_product_price, p.vendors_prod_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and p.vendors_id = " . $vendors_id . " and pd.language_id = " . $languages_id . " order by " . $sort_by_filter . " desc";
    } elseif ($show_order == 'asc') {
      $products_query_raw = "select p.products_id, p.vendors_id, pd.products_name, p.products_quantity , p.products_price, p.vendors_product_price, p.vendors_prod_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and p.vendors_id = " . $vendors_id . " and pd.language_id = " . $languages_id . " order by " . $sort_by_filter . " asc";
    } else {
      $products_query_raw = "select p.products_id, p.vendors_id, pd.products_name, p.products_quantity , p.products_price, p.vendors_product_price, p.vendors_prod_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and p.vendors_id = " . $vendors_id . " and pd.language_id = " . $languages_id . " order by " . $sort_by_filter . "";
    }
    /*  $products_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $products_query_raw, $products_query_numrows);
      Decide not to use SPLIT pages for the $vendors_id variable not being maintained.
      */

    $products_query = tep_db_query ($products_query_raw);
    while ($products = tep_db_fetch_array ($products_query)) {
      $rows++;

      if (strlen ($rows) < 2) {
        $rows = '0' . $rows;
      }
?>
              <tr class="dataTableRow">
<?php

      if ($products['vendors_prod_id'] == '') {
        $products['vendors_prod_id'] = 'None Specified';
      }
?>
                <td class="dataTableContent"><?php echo ''; ?></td>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'action=new_product&pID=' . $products['products_id']) . '" TARGET="_blank"><b>' . $products['products_name'] . '</a></b>'; ?></td>
                <td class="dataTableContent"><?php echo $products['vendors_prod_id']; ?></td>
                <td class="dataTableContent"><?php echo $products['products_id']; ?></td>
                <td class="dataTableContent" align="left"><?php echo $products['products_quantity']; ?>&nbsp;</td>
                <td class="dataTableContent"><?php echo $products['vendors_product_price']; ?></td>
                <td class="dataTableContent"><?php echo $products['products_price']; ?></td>
              </tr>
<?php

    }
  }
?>
            </table></td>
          </tr>
          <tr>
            <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText" valign="top">
<?php

  //   echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS);
?>
                </td>
                <td class="smallText" align="right">
<?php

  //   echo $products_split->display_links($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'].$vendors_id);
?>
                </td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require_once(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require_once(DIR_WS_INCLUDES . 'application_bottom.php'); ?>