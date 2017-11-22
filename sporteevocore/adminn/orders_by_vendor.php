<?php

/*
  $Id: $orders_by_vendor.php V1.0 2006/03/25 by Craig Garrison Sr www.blucollarsales.com
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
  
  $vendors_id = 0;
  if (isset ($_GET['vendors_id'])) {
    $vendors_id = (int) $_GET['vendors_id'];
  }
  
  $line_filter = 'desc';
  if (isset ($_GET['line']) && $_GET['line'] == 'asc') {
    $line_filter == $_GET['line'];
  }

  $sort_by_filter = 'orders_id';
  if (isset ($_GET['by']) && $_GET['by'] != '') {
    switch ($_GET['by']) {
      case 'date':
        $sort_by_filter = 'date_purchased';
        break;
      case 'customer':
        $sort_by_filter = 'customers_id';
        break;
      case 'status':
        $sort_by_filter = 'status';
        break;
      case 'sent':
        $sort_by_filter == 'sent';
        break;
      default:
        $sort_by_filter = 'orders_id';
        break;
    }
  }
  
  $status = '';
  if (isset ($_POST['status']) && $_POST['status'] != '') {
    $status = preg_replace("(\r\n|\n|\r)", '', $status); // Remove CR &/ LF
    $status = preg_replace("/[^A-Za-z0-9]/i", '', $status); // strip everthing except alphanumerics
  }

  $sent = '';
  if (isset ($_GET['sent']) && ($_GET['sent'] == 'yes' || $_GET['sent'] == 'no') ) {
    $sent == $_GET['sent'];
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
//  $vendors_array = array (array ('id' => '1',
//                                 'text' => 'NONE'
//                                )
//                         );
  $vendors_array = array();
  $vendors_query = tep_db_query ("select vendors_id, 
                                         vendors_name 
                                  from " . TABLE_VENDORS . " 
                                  order by vendors_name
                               ");
  while ($vendors = tep_db_fetch_array ($vendors_query) ) {
    $vendors_array[] = array ('id' => $vendors['vendors_id'],
                              'text' => $vendors['vendors_name']
                             );
  }
?>
                <td class="main" align="left"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS_VENDORS, '&vendors_id=' . $vendors_id) . '"><b>Click to reset form</a></b>';?></td>
                <td class="main" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_VENDORS) . '"><b>Go To Vendors List</a>';?><td>
              </tr>
              <tr>
                <td colspan="3"><?php echo tep_draw_separator('pixel_trans.gif','1','5'); ?></td>
              <tr>
                <td colspan="3"><?php echo tep_black_line(); ?></td>
              </tr>
              <tr>
                <td colspan="3"><?php echo tep_draw_separator('pixel_trans.gif','1','5'); ?></td>
              </tr>
              <tr>
                <td class="main" align="left"><?php echo tep_draw_form('vendors_report', FILENAME_ORDERS_VENDORS) . TABLE_HEADING_VENDOR_CHOOSE . ' '; ?><?php echo tep_draw_pull_down_menu('vendors_id', $vendors_array,'','onChange="this.form.submit()";');?></form></td>
                <td class="main" align="left"><?php echo 'Filter by email sent: <a href="' . tep_href_link(FILENAME_ORDERS_VENDORS, '&vendors_id=' . $vendors_id . '&line=' . $line_filter . '&sent=yes') . '"><b>YES</a></b>&nbsp; <a href="' . tep_href_link(FILENAME_ORDERS_VENDORS, '&vendors_id=' . $vendors_id . '&line=' . $line_filter . '&sent=no') . '"><b>NO</a></b>'; ?></td>
<?php
  if ($line_filter == 'asc') {
    if (isset ($status) ) {
?>
                <td class="main" align="right"><?php echo 'Change to <a href="' . tep_href_link (FILENAME_ORDERS_VENDORS, 'vendors_id=' . $vendors_id . '&line=desc' . '&sent=' . $sent . '&status=' . $status) . '"><b>DESCENDING</a></b> order'; ?></td>
<?php
    } else {      
?>
                <td class="main" align="right"><?php echo 'Change to <a href="' . tep_href_link (FILENAME_ORDERS_VENDORS, 'vendors_id=' . $vendors_id . '&line=desc' . '&sent=' . $sent) . '"><b>DESCENDING</a></b> order'; ?></td>
<?php
    }  

  } else {
    if (isset ($status) ) {
?>
                <td class="main" align="right"><?php echo 'Change to <a href="' . tep_href_link(FILENAME_ORDERS_VENDORS, '&vendors_id=' . $vendors_id . '&line=asc' . '&sent=' . $sent . '&status=' . $status) . '"><b>ASCENDING</a></b> order'; ?></td>
<?php
    } else {     
?>
                <td class="main" align="right"><?php echo 'Change to <a href="' . tep_href_link(FILENAME_ORDERS_VENDORS, '&vendors_id=' . $vendors_id . '&line=asc' . '&sent=' . $sent) . '"><b>ASCENDING</a></b> order'; ?></td>
<?php 
    }  
  }  
  
  $orders_statuses = array ();
  $orders_status_array = array ();
  $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int) $languages_id . "'");
  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
    $orders_statuses[] = array ('id' => $orders_status['orders_status_id'],
                                'text' => $orders_status['orders_status_name']
    );
    $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
  }
?>
                <td class="main" align="right"><?php echo tep_draw_form('status_report', FILENAME_ORDERS_VENDORS . '?&vendors_id=' . $vendors_id) . HEADING_TITLE_STATUS . ' '; echo tep_draw_pull_down_menu('status', $orders_statuses, '','onChange="this.form.submit()";');?></form></td>
              </tr>
            </table>
          </tr>
          <tr>
            <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr class="dataTableHeadingRow">
                    <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_VENDOR; ?></td>
                    <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_ORDER_ID; ?></td>
                    <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_CUSTOMERS; ?></td>
                    <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_ORDER_TOTAL; ?></td>
                    <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_DATE_PURCHASED; ?></td>
                    <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_STATUS; ?></td>
                    <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDER_SENT; ?>&nbsp;</td>
                  </tr>
<?php
  $vend_query_raw = "select vendors_name as name from " . TABLE_VENDORS . " where vendors_id = '" . $vendors_id . "'";
  $vend_query = tep_db_query($vend_query_raw);
  $vendors = tep_db_fetch_array($vend_query);
?>
                  <tr class="dataTableRow">
                    <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_VENDORS, '&vendors_id=' . $vendors_id . '&action=edit') . '" TARGET="_blank"><b>' . $vendors['name'] . '</a></b>'; ?></td>
                    <td class="dataTableContent"><?php echo ''; ?></td>
                    <td class="dataTableContent"><?php echo ''; ?></td>
                    <td class="dataTableContent"><?php echo ''; ?></td>
                    <td class="dataTableContent"><?php echo ''; ?></td>
                    <td class="dataTableContent"><?php echo ''; ?></td>
                    <td class="dataTableContent" align="right">Click To<br>Send Email</td>
                  </tr>
<?php
//  $index1 = 0;
  if ($sent == 'yes') {
    $vendors_orders_data_query = tep_db_query ("select distinct orders_id, vendor_order_sent from " . TABLE_ORDERS_SHIPPING . " where vendors_id='" . $vendors_id . "' and vendor_order_sent='yes' group by orders_id " . $line_filter . "");
  } elseif ($sent == 'no') {
    $vendors_orders_data_query = tep_db_query ("select distinct orders_id, vendor_order_sent from " . TABLE_ORDERS_SHIPPING . " where vendors_id='" . $vendors_id . "' and vendor_order_sent='no' group by orders_id " . $line_filter . "");
  } else {
    $vendors_orders_data_query = tep_db_query ("select distinct orders_id, vendor_order_sent from " . TABLE_ORDERS_SHIPPING . " where vendors_id='" . $vendors_id . "' group by orders_id " . $line_filter . "");
  }
  while ($vendors_orders_data = tep_db_fetch_array ($vendors_orders_data_query)) {
    if (isset ($status)) {
      $orders_query = tep_db_query ("select distinct o.customers_id, o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.orders_status = '" . $status . "' and o.orders_status = s.orders_status_id and s.language_id = '" . $languages_id . "' and ot.class = 'ot_total' and o.orders_id =  '" . $vendors_orders_data['orders_id'] . "' order by o." . $sort_by_filter . " ASC");
    } else {
      $orders_query = tep_db_query ("select distinct o.customers_id, o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.orders_status = s.orders_status_id and s.language_id = '" . $languages_id . "' and ot.class = 'ot_total' and o.orders_id =  '" . $vendors_orders_data['orders_id'] . "' order by o." . $sort_by_filter . " ASC");
    }
    while ($vendors_orders = tep_db_fetch_array ($orders_query) ) {
      $date_purchased = $vendors_orders['date_purchased'];

?>
                  <tr class="dataTableRow">
                    <td class="dataTableContent" align="left"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $vendors_orders_data['orders_id'] . '&action=edit') . '" TARGET="_blank"><b>View this order</b></a>'; ?></td>
                    <td class="dataTableContent" align="left"><?php echo $vendors_orders['orders_id']; ?></td>
           <!--     <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'action=new_product&pID=' . $vendors_orders_data['v_products_id']) . '" TARGET="_blank"><b>' . $vendors_products_data['products_name'] . '</a>'; ?></td>  -->
                    <td class="dataTableContent"><?php echo ' from <a href="' . tep_href_link(FILENAME_CUSTOMERS, tep_get_all_get_params(array('cID', 'action')) . 'cID=' . $vendors_orders['customers_id'] . '&action=edit') . '" TARGET="_blank"><b>' . $vendors_orders['customers_name'] . '</b></a>'; ?></td>
                    <td class="dataTableContent" align="left"><?php echo strip_tags($vendors_orders['order_total']); ?></td>
                    <td class="dataTableContent" align="left"><?php echo tep_date_short ($date_purchased); ?></td>
                    <td class="dataTableContent" align="left"><?php echo $vendors_orders['orders_status_name']; ?></td>
                    <td class="dataTableContent" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_VENDORS_EMAIL_SEND, '&vID=' . $vendors_id . '&oID=' . $vendors_orders_data['orders_id'] . '&vOS=' . $vendors_orders_data['vendor_order_sent']) . '"><b>' . $vendors_orders_data['vendor_order_sent'] . '</a></b>'; ?></td>
                  </tr>
<?php
    }
  }
?>
                </table></td>
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
<?php require_once (DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require_once (DIR_WS_INCLUDES . 'application_bottom.php'); ?>
