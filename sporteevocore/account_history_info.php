<?php
/*
  $Id: account_history_info.php,v 1.100 2003/06/09 23:03:52 hpdl Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot(); 
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  if (!isset($_GET['order_id']) || (isset($_GET['order_id']) && !is_numeric($_GET['order_id']))) {
    tep_redirect(tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
  }
  
  $customer_info_query = tep_db_query("select customers_id from " . TABLE_ORDERS . " where orders_id = '". (int)$_GET['order_id'] . "'");
  $customer_info = tep_db_fetch_array($customer_info_query);
  if ($customer_info['customers_id'] != $customer_id) {
    tep_redirect(tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_ACCOUNT_HISTORY_INFO);

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL'));
  $breadcrumb->add(sprintf(NAVBAR_TITLE_3, $_GET['order_id']), tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $_GET['order_id'], 'SSL'));

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order($_GET['order_id']);
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>

<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->

<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    
<!-- body_text //-->
   
            <span class="pageHeading"><?php echo HEADING_TITLE; ?></span>
            <span class="pageHeading" align="right"></span>
         
            <div class="maincapture"><b><?php echo sprintf(HEADING_ORDER_NUMBER, $_GET['order_id']) . ' <small>(' . $order->info['orders_status'] . ')</small>'; ?></b></div>
        
            <div class="smallText"><?php echo HEADING_ORDER_DATE . ' ' . tep_date_long($order->info['date_purchased']); ?></div>
            <div class="smallText" align="right"><?php echo HEADING_ORDER_TOTAL . ' ' . $order->info['total']; ?></div>
         
        <div class="accountBox">
          <div class="accountBoxContents">
<?php
  if ($order->delivery != false) {
?>
           
                <div class="main"><b><?php echo HEADING_DELIVERY_ADDRESS; ?></b></div>
              
                <div class="main"><?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>'); ?></div>
          
<?php
    if (tep_not_null($order->info['shipping_method'])) {
?>
             
                <div class="main"><b><?php echo HEADING_SHIPPING_METHOD; ?></b></div>
            
                <div class="main"><?php echo $order->info['shipping_method']; ?></div>
             
<?php
    }
?>
           
<?php
  }
?>
            <div width="<?php echo (($order->delivery != false) ? '70%' : '100%'); ?>" valign="top">
<?php
  if (sizeof($order->info['tax_groups']) > 1) {
?>
                 
                    <div class="maincapture"><b><?php echo HEADING_PRODUCTS; ?></b></div>
                    <div class="smallText" align="right"><b><?php echo HEADING_TAX; ?></b></div>
                    <div class="smallText" align="right"><b><?php echo HEADING_TOTAL; ?></b></div>
                 
<?php
  } else {
?>
                
                    <div class="main" colspan="3"><b><?php echo HEADING_PRODUCTS; ?></b></div>
                  
<?php
  }

  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    echo '          <tr>' . "\n" .
         '            <div class="main" align="right" valign="top" width="30">' . $order->products[$i]['qty'] . '&nbsp;x</div>' . "\n" .
         '            <div class="main" valign="top">' . $order->products[$i]['name'];

    if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
      for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
        echo '<br><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></small></nobr>';
      }
    }

    echo '</div>' . "\n";

    if (sizeof($order->info['tax_groups']) > 1) {
      echo '            <div class="main" valign="top" align="right">' . tep_display_tax_value($order->products[$i]['tax']) . '%</div>' . "\n";
    }

    echo '            <div class="main" align="right" valign="top">' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</div>' . "\n" .
         '          </tr>' . "\n";
  }
?>
                
        <div class="accountBox">
          <div class="accountBoxContents">
            
                <div class="main"><b><?php echo HEADING_BILLING_ADDRESS; ?></b></div>
              
                <div class="main"><?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br>'); ?></div>
              
                <div class="main"><b><?php echo HEADING_PAYMENT_METHOD; ?></b></div>
            
                <div class="main"><?php echo $order->info['payment_method']; ?></div>
             </div>
            
<?php
  for ($i=0, $n=sizeof($order->totals); $i<$n; $i++) {
    echo '              <tr>' . "\n" .
         '                <div class="main" align="right" width="100%">' . $order->totals[$i]['title'] . '</div>' . "\n" .
         '                <div class="main" align="right">' . $order->totals[$i]['text'] . '</div>' . "\n" .
         '              </tr>' . "\n";
  }
?>
          
        <div class="main"><b><?php echo HEADING_ORDER_HISTORY; ?></b></div>
    <div  class="accountBox">
          <div class="accountBoxContents">
            
<?php
  $statuses_query = tep_db_query("select os.orders_status_name, osh.date_added, osh.comments from " . TABLE_ORDERS_STATUS . " os, " . TABLE_ORDERS_STATUS_HISTORY . " osh where osh.orders_id = '" . (int)$_GET['order_id'] . "' and osh.orders_status_id = os.orders_status_id and os.language_id = '" . (int)$languages_id . "' order by osh.date_added");
  while ($statuses = tep_db_fetch_array($statuses_query)) {
    echo '              <tr>' . "\n" .
         '                <div class="main" valign="top" width="70">' . tep_date_short($statuses['date_added']) . '</div>' . "\n" .
         '                <div class="main" valign="top" width="70">' . $statuses['orders_status_name'] . '</div>' . "\n" .
         '                <div class="main" valign="top">' . (empty($statuses['comments']) ? '&nbsp;' : nl2br(tep_output_string_protected($statuses['comments']))) . '</div>' . "\n" .
         '              </tr>' . "\n";
  }
?>
          
<?php
  if (DOWNLOAD_ENABLED == 'true') include(DIR_WS_MODULES . 'downloads.php');
?>
      
                <?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY, tep_get_all_get_params(array('order_id')), 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></div>
               
            
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
    
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
