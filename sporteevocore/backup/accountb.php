<?php
/*   
  $Id: account.php,v 1.61 2003/06/09 23:03:52 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_ACCOUNT);

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
<script language="javascript"><!--
function rowOverEffect(object) {
  if (object.className == 'moduleRow') object.className = 'moduleRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'moduleRowOver') object.className = 'moduleRow';
}
//--></script>
</head>

<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<div id="container">
<!-- body //-->

   
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    
<!-- body_text //-->
  
            <span class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            
<?php
  if ($messageStack->size('account') > 0) {
?>
      <?php echo $messageStack->output('account'); ?>
      
<?php
  }

  if (tep_count_customer_orders() > 0) {
?>
      
            <div class="maincapture"><b class="h2"><?php echo OVERVIEW_TITLE; ?></b></div>
            <div class="maincapture"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '"><u>' . OVERVIEW_SHOW_ALL_ORDERS . '</u></a>'; ?></div>
        <div class="accountBox">
          <div class="accountBoxContents">
            
                <div class="maincapture" ><?php echo '<b>' . OVERVIEW_PREVIOUS_ORDERS . '</b><br>' . tep_image(DIR_WS_IMAGES . 'arrow_south_east.gif'); ?></div>
               
<?php
    $orders_query = tep_db_query("select o.orders_id, o.date_purchased, o.delivery_name, o.delivery_country, o.billing_name, o.billing_country, ot.text as order_total, s.orders_status_name from " . TABLE_ORDERS . " o, " . TABLE_ORDERS_TOTAL . " ot, " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$customer_id . "' and o.orders_id = ot.orders_id and ot.class = 'ot_total' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' order by orders_id desc limit 3");
    while ($orders = tep_db_fetch_array($orders_query)) {
      if (tep_not_null($orders['delivery_name'])) {
        $order_name = $orders['delivery_name'];
        $order_country = $orders['delivery_country'];
      } else {
        $order_name = $orders['billing_name'];
        $order_country = $orders['billing_country'];
      }
?>
                  <div class="moduleRow" onMouseOver="rowOverEffect(this)" onMouseOut="rowOutEffect(this)" onClick="document.location.href='<?php echo tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders['orders_id'], 'SSL'); ?>'"></div>
                   <div class="maincapture"><?php echo tep_date_short($orders['date_purchased']); ?></div>
                    <div class="maincapture"><?php echo '#' . $orders['orders_id']; ?></td>
                    <div class="maincapture"><?php echo tep_output_string_protected($order_name) . ', ' . $order_country; ?></td>
                    <div class="maincapture"><?php echo $orders['orders_status_name']; ?></div>
                    <div class="maincapture"><?php echo $orders['order_total']; ?></div>
                    <div class="maincapture"><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders['orders_id'], 'SSL') . '">' . tep_image_button('small_view.gif', SMALL_IMAGE_BUTTON_VIEW) . '</a>'; ?></div>
                  
<?php
    }
?>
               
<?php
  }
?>
     
            <div class="maincapture"><b class="h2"><?php echo MY_ACCOUNT_TITLE; ?></b></div>
           <div class="accountBox">
          <div class="accountBoxContents">
            
                    <div class="maincapture"><?php echo tep_image(DIR_WS_IMAGES . 'arrow_green.gif') . ' <a href="' . tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL') . '">' . MY_ACCOUNT_INFORMATION . '</a>'; ?></div>
                
                    <div class="maincapture"><?php echo tep_image(DIR_WS_IMAGES . 'arrow_green.gif') . ' <a href="' . tep_href_link(FILENAME_ADDRESS_BOOK, '', 'SSL') . '">' . MY_ACCOUNT_ADDRESS_BOOK . '</a>'; ?></div>
               
                    <div class="maincapture"><?php echo tep_image(DIR_WS_IMAGES . 'arrow_green.gif') . ' <a href="' . tep_href_link(FILENAME_ACCOUNT_PASSWORD, '', 'SSL') . '">' . MY_ACCOUNT_PASSWORD . '</a>'; ?></div>
              
            <div class="maincapture"><b class="h2"><?php echo MY_ORDERS_TITLE; ?></b></div>
       <div class="accountBox">
          <div class="accountBoxContents">
            
                    <div class="maincapture"><?php echo tep_image(DIR_WS_IMAGES . 'arrow_green.gif') . ' <a href="' . tep_href_link(FILENAME_ACCOUNT_HISTORY, '', 'SSL') . '">' . MY_ORDERS_VIEW . '</a>'; ?></div>
                 
              
            <div class="maincapture"><b class="h2"><?php echo EMAIL_NOTIFICATIONS_TITLE; ?></b></div>
     <div class="accountBox">
          <div class="accountBoxContents">
           
                    <div class="maincapture"><?php echo tep_image(DIR_WS_IMAGES . 'arrow_green.gif') . ' <a href="' . tep_href_link(FILENAME_ACCOUNT_NEWSLETTERS, '', 'SSL') . '">' . EMAIL_NOTIFICATIONS_NEWSLETTERS . '</a>'; ?></div>
                 
                    <div class="maincapture"><?php echo tep_image(DIR_WS_IMAGES . 'arrow_green.gif') . ' <a href="' . tep_href_link(FILENAME_ACCOUNT_NOTIFICATIONS, '', 'SSL') . '">' . EMAIL_NOTIFICATIONS_PRODUCTS . '</a>'; ?></div>
                
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
  
<!-- body_eof //-->
</div>
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->

</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
