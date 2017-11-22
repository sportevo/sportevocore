<?php
/*
  $Id: express_checkout.php,v 0.4.0.0 beta 2007/01/29 11:15:00 Alex Li Exp $

  Copyright (c) 2015/7 AlexStudio
  Released under the GNU General Public License
    
*/

  require('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($cart->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($cart->cartID) && tep_session_is_registered('cartID')) {
    if ($cart->cartID != $cartID) {
      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }

  if (!tep_session_is_registered('payment')) tep_session_register('payment');
  $_SESSION['payment'] = 'paypal_ec';

// load the selected payment module
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment;

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!tep_session_is_registered('shipping') && (!isset($_GET['action']) || $_GET['action'] != 'express')) {
    if (tep_session_is_registered('paypal_ec_token') && tep_not_null($_SESSION['paypal_ec_token'])) {
      $paypal_ec->pre_confirmation_check();
    }
    if (!tep_session_is_registered('shipping')) tep_redirect(tep_href_link(FILENAME_EXPRESS_CHECKOUT_SHIPPING, 'address_error=no_shipping', 'SSL'));
  }

// load the selected shipping module
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping($shipping);

  require(DIR_WS_CLASSES . 'order_total.php');
  $order_total_modules = new order_total;

// must load order_total befoer pre_confirmation_check() otherwise the total amount cannot be correct
  if (MODULE_ORDER_TOTAL_INSTALLED) {
    $order_total_modules->process();
  }

// Let's decide if we enable PayPal EC IPN module or not
  $paypal_ec->update_status();
  $ec_enabled = (($paypal_ec->enabled)? 1 : 0);

  if ($ec_enabled != 1) tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_PAYMENT_MODULE_SELECTED), 'SSL'));

  if ( ( is_array($$payment->modules) && (sizeof($$payment->modules) > 1) && !is_object($$payment) ) || (is_object($$payment) && ($$payment->enabled == false)) ) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_PAYMENT_MODULE_SELECTED), 'SSL'));
  }

  if (isset($_GET['action']) && $_GET['action'] == 'cancel') {
    $paypal_ec->ec_gen_error('', true, true);
    if (tep_session_is_registered('payment')) tep_session_unregister('payment');
    tep_redirect(tep_href_link(FILENAME_EXPRESS_CHECKOUT_SHIPPING, '', 'SSL'));
  }

  if (!tep_session_is_registered('comments')) tep_session_register('comments');
  if (tep_not_null($_POST['comments'])) {
    $comments = tep_db_prepare_input($_POST['comments']);
  }

// If button flow selected, use default address as shipping and billing address, then start the token initiation
  if (isset($_GET['action']) && $_GET['action'] == 'express' && !tep_session_is_registered('paypal_ec_order_info')) {
    if (!tep_session_is_registered('billto')) {
      tep_session_register('billto');
      $billto = $customer_default_address_id;
    }
    if (!tep_session_is_registered('sendto')) {
      tep_session_register('sendto');
      $sendto = $customer_default_address_id;
    }
    if (!tep_session_is_registered('payment')) {
      tep_session_register('payment');
      $payment = 'paypal_ec';
    }

    $paypal_ec->express_checkout();
  }

  $paypal_ec->pre_confirmation_check();

// Stock Check
  $any_out_of_stock = false;
  if (STOCK_CHECK == 'true') {
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      if (tep_check_stock($order->products[$i]['id'], $order->products[$i]['qty'])) {
        $any_out_of_stock = true;
      }
    }
    // Out of Stock
    if ( (STOCK_ALLOW_CHECKOUT != 'true') && ($any_out_of_stock == true) ) {
      tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
    }
  }
  
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_CONFIRMATION);

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_EXPRESS_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">

</head>
<body >
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
 
<!-- body_text //-->
   <div id="stscart">
   
             <span class="pageHeading">
<?php
  if ((isset($_GET['token']) && $_SESSION['paypal_ec_token'] == $_GET['token']) || tep_session_is_registered('paypal_ec_order_info')) echo MODULE_PAYMENT_PAYPAL_EC_TEXT_HEADING;
  else echo HEADING_TITLE; 
?>
            </span>

            
           <div id="shipcontainer"> 
    
    
    
      
          <div id="infoBoxContents">
          	<div id="addresscontainer">
          	
<?php
  if ($sendto != false) {
?>
           
            
                <div class="shipaddress2"><div id="fa"><i class="fa fa-home">&nbsp;<?php echo '<b>' . HEADING_DELIVERY_ADDRESS . '</b> <a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL') . '"><span class="orderEdit">(' . TEXT_EDIT . ')</span></a>'; ?></i></div></div>
              
              
                <div class="accountBoxContents"><?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>'); ?></div>
             
<?php
    if ($order->info['shipping_method']) {
?>
             
            </div>
             <div id="memocontainer"> 
             
                <div class="shipaddress2"><div id="fa"><i class="fa fa-truck">&nbsp;<?php echo '<b>' . HEADING_SHIPPING_METHOD . '</b> <a href="' . tep_href_link(FILENAME_EXPRESS_CHECKOUT_SHIPPING, '', 'SSL') . '"><span class="orderEdit">(' . TEXT_EDIT . ')</span></a>'; ?></i></div></div>
             
                <div class="maincaptureselected"><?php echo $order->info['shipping_method']; ?></div>
             
<?php
    }
?>
         
<?php
  }
?>
            
             
                
<?php
  if (sizeof($order->info['tax_groups']) > 1) {
?>
                 
                    <div class="shipaddress" ><?php echo '<b>' . HEADING_PRODUCTS . '</b> <a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '"><span class="orderEdit">(' . TEXT_EDIT . ')</span></a>'; ?></div>
                  
                  
<?php
  } else {
?>
               
                    <div class="mainprodslist"><?php echo '<b>' . HEADING_PRODUCTS . '</b> <a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '"><span class="orderEdit">(' . TEXT_EDIT . ')</span></a>'; ?></div>
                 
<?php
  }

  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    echo '          <div id="reciptcontainer">' . "\n" .
         '            <span class="accountBoxContents"  width="30">' . $order->products[$i]['qty'] . '&nbsp;x</span>' . "\n" .
         '            <a class="prodTextc" valign="top">' . $order->products[$i]['name'];

    if (STOCK_CHECK == 'true') {
     echo tep_check_stock($order->products[$i]['id'], $order->products[$i]['attributes'], $order->products[$i]['qty']);
    }

    if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
      for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
        echo '<br><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></small></nobr>';
      }
    }

    echo '</a>' . "\n";

    if (sizeof($order->info['tax_groups']) > 1) echo  ' <span  class="vattax"> ', HEADING_TAX . '&nbsp;' . tep_display_tax_value($order->products[$i]['tax']) . '% </span>' . "\n";

    echo '           <span  class="cartprice2"> ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . '</span>' . "\n" .
         '          </div>' . "\n";
  }
?>
                
             
     
         
     </div> 
    
     <div id="resumecontainer">
     
        <div class="shipaddress2"><div id="fa"><i class="fa fa-credit-card">&nbsp;<b><?php echo HEADING_BILLING_INFORMATION; ?></b></i></div></div>
     
    
     <div id="resumeBox">
          <div id="infoBoxContents">
            
             
                <div class="shipaddress2"><?php echo '<b>' . HEADING_BILLING_ADDRESS . '</b> <a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '"><span class="orderEdit">(' . TEXT_EDIT . ')</span></a>'; ?></div>
              
              
                <div class="accountBoxContents"><?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br>'); ?></div>
             
                <div class="shipaddress2"><?php echo '<b>' . HEADING_PAYMENT_METHOD . '</b> <a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '"><span class="orderEdit">(' . TEXT_EDIT . ')</span></a>'; ?></div>
             
                <div class="accountBoxContents"><?php echo $order->info['payment_method']; ?></div>
              
            </div>
            <div id="infoBoxContents">
           
<?php
  if (MODULE_ORDER_TOTAL_INSTALLED) {
    echo $order_total_modules->output();
  }
?>
          </div>
          
          
          
               <div id="buttoncontainer">      <?php
  $form_action_url = $paypal_ec->form_action_url;

  echo tep_draw_form('checkout_confirmation', $form_action_url, 'post');

  echo $paypal_ec->process_button();

  if (tep_session_is_registered('paypal_ec_order_info')) {
    echo tep_image_submit('button_ec_pay.gif', IMAGE_BUTTON_PAY) . '</form>' . "\n";
  } else {
    echo tep_image_submit('button_confirm_order.gif', IMAGE_BUTTON_CONFIRM_ORDER) . '</form>' . "\n";
  }
?>
         </div>
          
          
          
           </div>
          
          </div>
      
<?php
  if (is_array($payment_modules->modules)) {
    if ($confirmation = $paypal_ec->confirmation()) {
?>
      
      <div id="methodcontainer">
         <div class="shipaddress2"><div id="fa"><i class="fa fa-credit-card">&nbsp;<b><?php echo HEADING_PAYMENT_INFORMATION; ?></b></i></div></div>
      
       
      <div id="accountBox">
          <div class="accountBoxContents">
          
                <div id="paymentcont"><?php echo $confirmation['title']; ?></div>
              </div>
<?php
      for ($i=0, $n=sizeof($confirmation['fields']); $i<$n; $i++) {
?>
            
              
                <div class="maincaptureuncazzo"><b><?php echo $confirmation['fields'][$i]['title']; ?></b></div>
               
                <div class="maincaptureuncazzo"><b><?php echo $confirmation['fields'][$i]['field']; ?></b></div>
              
<?php
      }
?>
            </div>
        
      
       </div>
          </div>
        
        </div>
     
<?php
    }
  }
?>
      
<?php
  if (tep_not_null($order->info['comments'])) {
?>
     
        <div id="methodcontainer"><?php echo '<b>' . HEADING_ORDER_COMMENTS . '</b> <a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '"><span class="orderEdit">(' . TEXT_EDIT . ')</span></a>'; ?>
      
     <div id="accountBox">
          <div id="accountBoxContents">
            
              
                <div id="methodcontainer"><?php echo nl2br(tep_output_string_protected($order->info['comments'])) . tep_draw_hidden_field('comments', $order->info['comments']); ?></div>
              
            </div>
          </div>
        </div></div>
     
     
<?php
  }
?>
     
       
            
            <div class="SmallText"><b><?php if (tep_session_is_registered('paypal_ec_order_info')) echo '<a href="' . tep_href_link(FILENAME_EXPRESS_CHECKOUT, 'action=cancel', 'SSL') . '" class="SmallText">' . MODULE_PAYMENT_PAYPAL_EC_TEXT_CANCEL . '</a>'; ?></b></div>
         

            </div>
          </div>
     
      
      </div>
      
    </div></div>
<!-- body_text_eof //-->
   
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
   </div>
  
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</div>
</div>
 </div>
</div>

</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
