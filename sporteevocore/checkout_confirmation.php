<?php
/*
  $Id: checkout_confirmation.php,v 1.139 2003/06/11 17:34:53 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

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

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!tep_session_is_registered('shipping')) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

  if (!tep_session_is_registered('payment')) tep_session_register('payment');
  if (isset($_POST['payment'])) $payment = $_POST['payment'];

  if (!tep_session_is_registered('comments')) tep_session_register('comments');
  if (tep_not_null($_POST['comments'])) {
    $comments = tep_db_prepare_input($_POST['comments']);
  }

//kgt - discount coupons
  if (!tep_session_is_registered('coupon')) tep_session_register('coupon');
  //this needs to be set before the order object is created, but we must process it after
  $coupon = tep_db_prepare_input($_POST['coupon']);
  //end kgt - discount coupons

// load the selected payment module
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment($payment);

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

  $payment_modules->update_status();

  if ( ( is_array($payment_modules->modules) && (sizeof($payment_modules->modules) > 1) && !is_object($$payment) ) || (is_object($$payment) && ($$payment->enabled == false)) ) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode(ERROR_NO_PAYMENT_MODULE_SELECTED), 'SSL'));
  }

  if (is_array($payment_modules->modules)) {
    $payment_modules->pre_confirmation_check();
  }

//kgt - discount coupons
  if( tep_not_null( $coupon ) && is_object( $order->coupon ) ) { //if they have entered something in the coupon field
    $order->coupon->verify_code();
    if( MODULE_ORDER_TOTAL_DISCOUNT_COUPON_DEBUG != 'true' ) {
		  if( !$order->coupon->is_errors() ) { //if we have passed all tests (no error message), make sure we still meet free shipping requirements, if any
			  if( $order->coupon->is_recalc_shipping() ) tep_redirect( tep_href_link( FILENAME_CHECKOUT_SHIPPING, 'error_message=' . urlencode( ENTRY_DISCOUNT_COUPON_SHIPPING_CALC_ERROR ), 'SSL' ) ); //redirect to the shipping page to reselect the shipping method
		  } else {
			  if( tep_session_is_registered('coupon') ) tep_session_unregister('coupon'); //remove the coupon from the session
			  tep_redirect( tep_href_link( FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode( implode( ' ', $order->coupon->get_messages() ) ), 'SSL' ) ); //redirect to the payment page
		  }
    }
	} else { //if the coupon field is empty, unregister the coupon from the session
		if( tep_session_is_registered('coupon') ) { //we had a coupon entered before, so we need to unregister it
      tep_session_unregister('coupon');
      //now check to see if we need to recalculate shipping:
      require_once( DIR_WS_CLASSES.'discount_coupon.php' );
      if( discount_coupon::is_recalc_shipping() ) tep_redirect( tep_href_link( FILENAME_CHECKOUT_SHIPPING, 'error_message=' . urlencode( ENTRY_DISCOUNT_COUPON_SHIPPING_CALC_ERROR ), 'SSL' ) ); //redirect to the shipping page to reselect the shipping method
    }
	}
	//end kgt - discount coupons

// load the selected shipping module
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping($shipping);

  require(DIR_WS_CLASSES . 'order_total.php');
  $order_total_modules = new order_total;

// Stock Check
  $any_out_of_stock = false;
  if (STOCK_CHECK == 'true') {
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {

// Yuval - Start - Fix a bug:: check stock per product attribute quantity
// Push all attributes information in an array
      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        while (list($option, $value) = each($products[$i]['attributes'])) {
          $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pa.products_attributes_id
                                      from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                      where pa.products_id = '" . $products[$i]['id'] . "'
                                       and pa.options_id = '" . $option . "'
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = '" . $value . "'
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = '" . $languages_id . "'
                                       and poval.language_id = '" . $languages_id . "'");

          $attributes_values = tep_db_fetch_array($attributes);
          $products[$i][$option]['products_options_name'] = $attributes_values['products_options_name'];
          $products[$i][$option]['options_values_id'] = $value;
          $products[$i][$option]['products_options_values_name'] = $attributes_values['products_options_values_name'];
          $products[$i][$option]['options_values_price'] = $attributes_values['options_values_price'];
          $products[$i][$option]['price_prefix'] = $attributes_values['price_prefix'];
          $products[$i][$option]['products_attributes_id'] = $attributes_values['products_attributes_id'];

          if (tep_check_stock($order->products[$i]['id'],$products[$i][$option]['products_attributes_id'], $order->products[$i]['qty'])) {
            $any_out_of_stock = true;
  				}
        }
      }
// Yuval - End - Fix a bug:: check stock per product attribute quantity
    }
    // Out of Stock
    if ( (STOCK_ALLOW_CHECKOUT != 'true') && ($any_out_of_stock == true) ) {
      tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
    }
  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_CONFIRMATION);

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2);
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">

</head>
<body> 
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->

<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
   <div id="stscart">
<!-- body_text //-->

            <h2><?php echo HEADING_TITLE; ?></h2>
      
            <div id="shipcontainer"> 
            	
          <div id="infoBoxContents">
          	
<?php
  if ($sendto != false) {
?>
          
                

           
<?php
  }
?>
             
          
<?php
  if (sizeof($order->info['tax_groups']) > 1) {
?>
                  <div class="shipaddress"><?php echo '<span class="pageHeading">' . HEADING_PRODUCTS . '</span> <a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '"><div id="orderEdit">(' . TEXT_EDIT . ')</div></a>'; ?></div>
                    <div class="smallText" align="right"><b class="h2"><?php echo HEADING_TAX; ?></b></div>
                    <div class="smallText" align="right"><b class="h2"><?php echo HEADING_TOTAL; ?></b></div>
                  
<?php
  } else {
?>
                  
                    <div id="recapheader"><?php echo '<span class="pageHeading">' . HEADING_PRODUCTS . '</span> <a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '"><div id="orderEdit">(' . TEXT_EDIT . ')</div></a>'; ?></div>
                  
<?php
  }

  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
    echo '         <div id="productListing-odd">' . "\n" .
         '            <span class="accountBoxContents"  >' . $order->products[$i]['qty'] . '&nbsp;x</span>' . "\n" .
         '            <a class="prodTextb">' . $order->products[$i]['name'];


// Yuval - Start - Fix a bug:: check stock per product attribute quantity
// Push all attributes information in an array
      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        while (list($option, $value) = each($products[$i]['attributes'])) {
          $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pa.products_attributes_id
                                      from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                      where pa.products_id = '" . $products[$i]['id'] . "'
                                       and pa.options_id = '" . $option . "'
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = '" . $value . "'
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = '" . $languages_id . "'
                                       and poval.language_id = '" . $languages_id . "'");

          $attributes_values = tep_db_fetch_array($attributes);
          $products[$i][$option]['products_options_name'] = $attributes_values['products_options_name'];
          $products[$i][$option]['options_values_id'] = $value;
          $products[$i][$option]['products_options_values_name'] = $attributes_values['products_options_values_name'];
          $products[$i][$option]['options_values_price'] = $attributes_values['options_values_price'];
          $products[$i][$option]['price_prefix'] = $attributes_values['price_prefix'];
          $products[$i][$option]['products_attributes_id'] = $attributes_values['products_attributes_id'];

          if (STOCK_CHECK == 'true') {
            echo tep_check_stock($order->products[$i]['id'],$products[$i][$option]['products_attributes_id'], $order->products[$i]['qty']);
          }
        }
      }
// Yuval - End - Fix a bug:: check stock per product attribute quantity



    if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
      for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
        echo '<br><nobr><small>&nbsp;<i class="icart"> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></small></nobr>';
      }
    }

    echo '</a>' . "\n";

    if (sizeof($order->info['tax_groups']) > 1) echo '            <span class="vattax" valign="top" align="right">' . tep_display_tax_value($order->products[$i]['tax']) . '%</span>' . "\n";

    echo '            <spand class="cartprice">' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . '</span>' . "\n" .
         '          </div>' . "\n";
  }
?>
               
       </div>     
          
      
     
      
      
       </div>
      
<?php
  if (is_array($payment_modules->modules)) {
    if ($confirmation = $payment_modules->confirmation()) {
?>
      
  
        <div id="recapheader"><div id="fa"><i class="fa fa-credit-card">&nbsp;<span class="pageHeading"><?php echo HEADING_PAYMENT_INFORMATION; ?></span></i></div></div>
     
       
    
         
          
                <div id="paymentcont"><?php echo $confirmation['title']; ?></div>
           
<?php
      for ($i=0, $n=sizeof($confirmation['fields']); $i<$n; $i++) {
?>
            
                
                <div class="maincaptureuncazzo"><?php echo $confirmation['fields'][$i]['title']; ?></div>
                
                <div class="maincaptureuncazzo"><?php echo $confirmation['fields'][$i]['field']; ?>
                
                <div id="checkoutcontainer">
                <div class="checkoutBarFrom"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '" class="checkoutBarFrom">' . CHECKOUT_BAR_DELIVERY . '</a>'; ?></div>
             <div class="checkoutBarFrom"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '" class="checkoutBarFrom">' . CHECKOUT_BAR_PAYMENT . '</a>'; ?></div>
            <div class="checkoutBarCurrent"><?php echo tep_image(DIR_WS_IMAGES . 'checkout_bullet.gif'); ?><?php echo CHECKOUT_BAR_CONFIRMATION; ?></div>
            <div class="checkoutBarTo"><?php echo CHECKOUT_BAR_FINISHED; ?></div>
                    </div>
                </div>
             
<?php
      }
?>
        
        
   
         
        
        </div>
          
               
           
        
       
             
<?php
    }
  }
?>
      
<?php
  if (tep_not_null($order->info['comments'])) {
?>
     
      <div id="methodcontainer"><?php echo '<b class="h2">' . HEADING_ORDER_COMMENTS . '</b> <a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '"><div id="orderEdit">(' . TEXT_EDIT . ')</div></a>'; ?></div>
     
      
       <div id="accountBox">
          <div id="accountBoxContents">
            
              
                <div id="methodcontainer"><?php echo nl2br(tep_output_string_protected($order->info['comments'])) . tep_draw_hidden_field('comments', $order->info['comments']); ?></div>
            
              </div>
          </div>
      
        
        
        
     
<?php
  }
?>
      
      
         </div>

         
<!-- body_text_eof //-->
     <div id="resumecontainer">
      
        	 <hr class="hrlogin">
        	
        	 <div id="recapheader"><div id="fa"><i class="fa fa-credit-card">&nbsp;<span class="pageHeading"><?php echo HEADING_BILLING_INFORMATION; ?></span></i></div></div>
     
         
                  <div id="recapheader"><?php echo '<span class="pageHeading">' . HEADING_BILLING_ADDRESS . '</span> <a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '"><div id="orderEdit">(' . TEXT_EDIT . ')</div></a>'; ?></div>
              
              
                <p class="addressbox"><?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, ' ', '<br>'); ?></p>
             
            
                 <div id="recapheader"><?php echo '<span class="pageHeading">' . HEADING_PAYMENT_METHOD . '</span> <a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL') . '"><div id="orderEdit">(' . TEXT_EDIT . ')</div></a>'; ?></div>
             
               
          
        
           
            <div class="infoBoxContents ordertotal">
             <p class="addressbox"><?php echo $order->info['payment_method']; ?></p>
              <br>
              <br>
<?php
  if (MODULE_ORDER_TOTAL_INSTALLED) {
    $order_total_modules->process();
    echo $order_total_modules->output();
  }
?>
           
             <div class="buttoncontainer">
<?php
  if (isset($$payment->form_action_url)) {
    $form_action_url = $$payment->form_action_url;
  } else {
    $form_action_url = tep_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL');
  }

  echo tep_draw_form('checkout_confirmation', $form_action_url, 'post');

  if (is_array($payment_modules->modules)) {
    echo $payment_modules->process_button();
  }

  echo tep_image_submit('button_confirm_order.gif', IMAGE_BUTTON_CONFIRM_ORDER) . '</form>' . "\n";
?>
              
           
           
           </div>
      
    <hr class="hrloginhr">
	
	     
 
        
      </div>
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
