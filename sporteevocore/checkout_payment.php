<?php
/*
  $Id: checkout_payment.php,v 1.113 2003/06/29 23:03:27 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
 
// #################### Begin Added CGV JONYO ######################
if (tep_session_is_registered('cot_gv')) tep_session_unregister('cot_gv');  //added to reset whether a gift voucher is used or not on this order
// #################### End Added CGV JONYO ######################
// MVS start 
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_PAYMENT);

//  print 'Vendor Shipping: ' . SELECT_VENDOR_SHIPPING . "<br>\n";
//  print 'Array Vendor Shipping: <pre>';
//  print_r ($shipping);
//  print '</pre>' . "<br>\n";
//  print 'Vendor Count: ' . count ($shipping['vendor']) . "<br>\n";
//  print 'Cart Vendor Count: ' . count ($cart->vendor_shipping) . "<br>\n";

//  exit;

// If a shipping method has not been selected for all vendors, redirect the customer to the shipping method selection page
  if (SELECT_VENDOR_SHIPPING == 'true') { // This test only works under MVS
    if (!is_array ($shipping['vendor']) || count ($shipping['vendor']) != count ($cart->vendor_shipping)) { // No shipping selected or not all selected
      tep_redirect (tep_href_link (FILENAME_CHECKOUT_SHIPPING, 'error_message=' . ERROR_NO_SHIPPING_SELECTED, 'SSL'));
    }
  } 
// MVS end
// if the customer is not logged on, redirect them to the login page
  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($cart->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }

// if no shipping method has been selected, redirect the customer to the shipping method selection page
  if (!tep_session_is_registered('shipping')) {
	  // BOF PayPal Express Checkout IPN v0.4.1 beta
    if (isset($_GET['payment_error']) && $_GET['payment_error'] == 'paypal_ec') {
      require(DIR_WS_CLASSES . 'payment.php');
      $payment_modules = new payment('paypal_ec');
      $error = $paypal_ec->get_error();
      if (!tep_session_is_registered('paypal_ec_address_error')) tep_session_register('paypal_ec_address_error');
      $_SESSION['paypal_ec_address_error'] = array('title' => $error['title'], 'message' => $error['error']);
      tep_redirect(tep_href_link(FILENAME_EXPRESS_CHECKOUT_SHIPPING, 'address_error=1', 'SSL'));
    }
// EOF PayPal Express Checkout IPN v0.4.1 beta
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($cart->cartID) && tep_session_is_registered('cartID')) {
    if ($cart->cartID != $cartID) {
      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }
    // if we have been here before and are coming back get rid of the credit covers variable
// #################### Added CGV ######################
	if(tep_session_is_registered('credit_covers')) tep_session_unregister('credit_covers');  // CCGV Contribution
// #################### End Added CGV ######################
// Stock Check
    $any_bundle_only = false;
  $products = $cart->get_products();
  for ($i=0, $n=sizeof($products); $i<$n; $i++) {
    if ($products[$i]['sold_in_bundle_only'] == 'yes') $any_bundle_only = true;
  }
  if ($any_bundle_only) tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  
  


	if ( (STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true') ) {
    $bundle_contents = array();
    $bundle_values = array();
    $base_product_ids_in_order = array();
    $bundle_qty_ordered = array();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if ($products[$i]['bundle'] == "yes") {
        $tmp = get_all_bundle_products($products[$i]['id']);
        $bundle_values[$products[$i]['id']] = $products[$i]['final_price'];
        $bundle_contents[$products[$i]['id']] = $tmp;
        $bundle_qty_ordered[$products[$i]['id']] =  $option[$i]['products_attributes_id'];
        foreach ($tmp as $id => $qty) {
          if (!in_array($id, $base_product_ids_in_order)) $base_product_ids_in_order[] = $id; // save unique ids
        }
      } else {
        if (!in_array($products[$i]['id'], $base_product_ids_in_order)) $base_product_ids_in_order[] = $products[$i]['id']; // save unique ids
      }
    }
    $product_on_hand = array();
    foreach ($base_product_ids_in_order as $id) {
      // get quantity on hand for every unique product contained in this order except bundles
      $product_on_hand[$id] = tep_get_products_stock($id);
    }
    if (!empty($bundle_values)) { // if bundles exist in order
      arsort($bundle_values); // sort array so bundle ids with highest value come first
      foreach ($bundle_values as $bid => $bprice) {
        $bundles_available = array();
        foreach ($bundle_contents[$bid] as $pid => $qty) {
          $bundles_available[] = intval($product_on_hand[$pid] / $qty);
        }
        $product_on_hand[$bid] = min($bundles_available); // max number of this bundle we can make with product on hand
        $deduct = min($product_on_hand[$bid], $bundle_qty_ordered[$bid]); // assume we sell as many of the bundle as possible
        foreach ($bundle_contents[$bid] as $pid => $qty) {
          // reduce product left on hand by number sold in this bundle before checking next less expensive bundle
          // also lets us know how many we have left to sell individually
          $product_on_hand[$pid] -= ($deduct * $qty);
        }
      }
    }
	
	for ($i=0, $n=sizeof($products); $i<$n; $i++) {
     
      if (tep_check_stock($products[$i]['id'], $products[$i][$option]['products_attributes_id'], $products[$i]['quantity'])) {
        tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
        break;
      }
    }
  }
// #################### Begin Added CGV JONYO ######################
// #################### THIS MOD IS OPTIONAL! ######################

// load the selected shipping module
 require(DIR_WS_CLASSES . 'shipping.php');
 $shipping_modules = new shipping($shipping);

// #################### End Added CGV JONYO ######################
// #################### THIS MOD WAS OPTIONAL! ######################

// if no billing destination address was selected, use the customers own address as default
  if (!tep_session_is_registered('billto')) {
    tep_session_register('billto');
    $billto = $customer_default_address_id;
  } else {
// verify the selected billing address
    $check_address_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$billto . "'");
    $check_address = tep_db_fetch_array($check_address_query);

    if ($check_address['total'] != '1') {
      $billto = $customer_default_address_id;
      if (tep_session_is_registered('payment')) tep_session_unregister('payment');
    }
  }

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;
// #################### Added CGV ######################
  require(DIR_WS_CLASSES . 'order_total.php');//ICW ADDED FOR CREDIT CLASS SYSTEM
  $order_total_modules = new order_total;//ICW ADDED FOR CREDIT CLASS SYSTEM
  $order_total_modules->clear_posts(); // ADDED FOR CREDIT CLASS SYSTEM by Rigadin in v5.13
// #################### End Added CGV ######################

  if (!tep_session_is_registered('comments')) tep_session_register('comments');

  $total_weight = $cart->show_weight();
  $total_count = $cart->count_contents();
  // #################### Added CGV ######################
  $total_count = $cart->count_contents_virtual(); //ICW ADDED FOR CREDIT CLASS SYSTEM
// #################### End Added CGV ######################


// load all enabled payment modules
  require(DIR_WS_CLASSES . 'payment.php');
  $payment_modules = new payment;
  
// BOF PayPal Express Checkout IPN v0.4.1 beta
  $paypal_ec_check = tep_db_query("SELECT configuration_id FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_EC_STATUS' AND configuration_value = 'True'");
  $ec_enabled = (tep_db_num_rows($paypal_ec_check) ? 1 : 0);
  if ($ec_enabled && tep_session_is_registered('paypal_ec_token')) {
    if (!tep_session_is_registered('payment')) tep_session_register('payment');
    $payment = 'paypal_ec';
  }
// EOF PayPal Express Checkout IPN v0.4.1 beta

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_PAYMENT);

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">

<script language="javascript"><!--
var selected;
<?php // #################### Added CGV ###################### ?>
var submitter = null;
function submitFunction() {
   submitter = 1;
   }
<?php // #################### End Added CGV ###################### ?>
function selectRowEffect(object, buttonSelect) {
   // #################### Begin Added CGV JONYO ######################
  if (!document.checkout_payment.payment[0].disabled){
  // #################### End Added CGV JONYO ######################
    if (!selected) {
    if (document.getElementById) {
      selected = document.getElementById('defaultSelected');
    } else {
      selected = document.all['defaultSelected'];
    }
  }

  if (selected) selected.className = 'moduleRow';
  object.className = 'moduleRowSelected';
  selected = object;

// one button is not an array
  if (document.checkout_payment.payment[0]) {
    document.checkout_payment.payment[buttonSelect].checked=true;
  } else {
    document.checkout_payment.payment.checked=true;
  }// #################### Begin Added CGV JONYO ######################
  }
  // #################### End Added CGV JONYO ######################

}

function rowOverEffect(object) {
  if (object.className == 'moduleRow') object.className = 'moduleRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'moduleRowOver') object.className = 'moduleRow';
}
<?php // #################### Begin Added CGV JONYO ###################### ?>

<?php 
if (MODULE_ORDER_TOTAL_INSTALLED)
	$temp=$order_total_modules->process();
	$temp=$temp[count($temp)-1];
	$temp=$temp['value'];

	$gv_query = tep_db_query("select amount from " . TABLE_COUPON_GV_CUSTOMER . " where customer_id = '" . $customer_id . "'");
	$gv_result = tep_db_fetch_array($gv_query);

if ($gv_result['amount']>=$temp){ $coversAll=true;

?>

function clearRadeos(){
document.checkout_payment.cot_gv.checked=!document.checkout_payment.cot_gv.checked;
for (counter = 0; counter < document.checkout_payment.payment.length; counter++)
{
// If a radio button has been selected it will return true
// (If not it will return false)
if (document.checkout_payment.cot_gv.checked){
document.checkout_payment.payment[counter].checked = false;
document.checkout_payment.payment[counter].disabled=true;
//document.checkout_payment.cot_gv.checked=false;
} else {
document.checkout_payment.payment[counter].disabled=false;
//document.checkout_payment.cot_gv.checked=true;
}
}
}<?php } else { $coversAll=false;?>
function clearRadeos(){
document.checkout_payment.cot_gv.checked=!document.checkout_payment.cot_gv.checked;
}<?php } ?><?php // #################### End Added CGV JONYO ###################### ?>

//--></script>
<?php // #################### Begin Added CGV JONYO ###################### ?>
<?php // echo $payment_modules->javascript_validation(); ?>
<?php echo $payment_modules->javascript_validation($coversAll); ?>
<?php // #################### End Added CGV JONYO ###################### ?>
</head>
 
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->

<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
     <div id="stscart">
     
<!-- body_text //-->
   <?php echo tep_draw_form('checkout_payment', tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL'), 'post', 'onsubmit="return check_form();"'); ?>
    
            <h2><?php echo HEADING_TITLE; ?></h2>
            <div id="shipcontainer"> 
<?php
  if (isset($_GET['payment_error']) && is_object(${$_GET['payment_error']}) && ($error = ${$_GET['payment_error']}->get_error())) {
?>
      
            <div class="main"><b><?php echo tep_output_string_protected($error['title']); ?></b></div>
         <div class="infoBoxNotice">
          <div class="infoBoxNoticeContents">
            
               
              <?php // BOF PayPal Express Checkout IPN v0.4 beta // ?>
<?php  if ($ec_enabled && $_GET['payment_error'] == 'paypal_ec') { ?>
                <div class="maincapture" ><?php echo $error['error']; ?></div>
<?php  } else { ?>
 
                <div class="maincapture" ><?php echo tep_output_string_protected($error['error']); ?></div>
                <?php  } ?>
<?php // EOF PayPal Express Checkout IPN v0.4 beta ?>

               </div>
         </div>
     
<?php
  }
?><?php // #################### Begin Added CGV JONYO ###################### ?>
<?php // #################### THIS MOD IS OPTIONAL! ###################### ?>
     <div id="addresscontainer">
           
            <div id="recapheader"><div id="fa"><i class="fa fa-shopping-cart">&nbsp;<span class="pageHeading"><?php echo HEADING_PRODUCTS; ?></span></i></div></div>
                   <div id="memocontainer"> 
                     <?  echo ' <a href="' . tep_href_link(FILENAME_SHOPPING_CART) . '"><div id="orderEdit">(' . TEXT_EDIT . ')</div></a>'; ?>
           
                  <?php
 //}

 for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
   echo '          <div id="productListing-odd">' . "\n" .
        '            <span class="accountBoxContents" >' . $order->products[$i]['qty'] . ' x&nbsp;</span>' . "\n" .
        '            <a class="prodTextb" >' . $order->products[$i]['name'];

   if (STOCK_CHECK == 'true') {
     echo tep_check_stock($order->products[$i]['id'], $order->products[$i]['attributes'], $order->products[$i]['qty']);
   }

   if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
     for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
       echo '<br><small> <i class="icart"> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></small>';
     }
   }

   echo '</a>' . "\n";

   if (sizeof($order->info['tax_groups']) > 1) echo '            ' . tep_display_tax_value($order->products[$i]['tax']) . '%' . "\n";

   echo '            <span  class="cartprice">' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . ' </span>' . "\n" .
        '          </div>' . "\n";
 }
 ?>
 </div>




  </div>



 	
 	
 	<?php // #################### End Added CGV JONYO ###################### ?>
<?php // #################### THIS MOD WAS OPTIONAL! ###################### ?>
    
         <?php
/* kgt - discount coupons */
	if( MODULE_ORDER_TOTAL_DISCOUNT_COUPON_STATUS == 'true' ) {
?>
      
            <div id="recapheader"><div id="fa"><i class="fa fa-credit-card">&nbsp;<span class="pageHeading"><?php echo TABLE_HEADING_COUPON; ?></span></i></div></div>
        
         
           
                <div class="maincaptureb"><?php echo ENTRY_DISCOUNT_COUPON.' '.tep_draw_input_field('coupon', '', 'size="32"'); ?></div>
           
     
     
<?php
	}
/* end kgt - discount coupons */
?> 
         
                      
            <div id="recapheader"><div id="fa"><i class="fa fa-credit-card">&nbsp;<span class="pageHeading"><?php echo TABLE_HEADING_PAYMENT_METHOD; ?></span></i></div></div>
       
          
          
<?php
  $selection = $payment_modules->selection();

  if (sizeof($selection) > 1) {
?>
            <div class="maincaptureb" >
          <p>
          <i>     <?php echo TEXT_SELECT_PAYMENT_METHOD; ?>  </i>
          </p>
              
                
<?php
  } else {
?>
               
             
                <div class="maincaptureb" >
                	<p>
          <i> <?php echo TEXT_ENTER_PAYMENT_INFORMATION; ?>
          	 </i>
          </p>
          	
          </div>
              
            
          
<?php
  }

  $radio_buttons = 0;
  for ($i=0, $n=sizeof($selection); $i<$n; $i++) {
?>
    <?php
    if (sizeof($selection) > 1) {
      echo tep_draw_radio_field('payment', $selection[$i]['id']);
    } else {
      echo tep_draw_hidden_field('payment', $selection[$i]['id']);
    }
?>   <b><?php echo $selection[$i]['module']; ?></b>
         

                
<?php
    if ( ($selection[$i]['id'] == $payment) || ($n == 1) ) {
      echo '                  <div id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')"></div>' . "\n";
    } else {
      echo '                  <div class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')"></div>' . "\n";
    }
?>
                    
                   
                    
                 
<?php
    if (isset($selection[$i]['error'])) {
?>
                  
                    <div class="maincapture" ><?php echo $selection[$i]['error']; ?></div>
                   
<?php
    } elseif (isset($selection[$i]['fields']) && is_array($selection[$i]['fields'])) {
?>
                  
                    
<?php
      for ($j=0, $n2=sizeof($selection[$i]['fields']); $j<$n2; $j++) {
?>
                     
                        <div class="maincapture"><?php echo $selection[$i]['fields'][$j]['title']; ?></div>
                       
                        <div class="maincapture"><?php echo $selection[$i]['fields'][$j]['field']; ?></div>
                        
<?php
      }
?>
                    
<?php
    }
?>
                
<?php
    $radio_buttons++;
  }
  
 // #################### Begin Added CGV JONYO ######################

if (tep_session_is_registered('customer_id')) {
if ($gv_result['amount']>0){
  echo ' ' . "\n" .
  								' <div id="memocontainer"><tr class="moduleRow" onmouseover="rowOverEffect(this)" onclick="clearRadeos()" onmouseout="rowOutEffect(this)" ></div>' . "\n" .
                             '  ' . $gv_result['text'];

  echo $order_total_modules->sub_credit_selection();
  }
}


 // #################### End Added CGV JONYO ######################

?>
 	
 </div>

<?php // #################### Added CGV ###################### 
  echo $order_total_modules->credit_selection();//ICW ADDED FOR CREDIT CLASS SYSTEM
 // #################### End Added CGV ###################### ?>

             


 <div id="shipcontainer" >  
          
            <div class="maincapture"><b><?php echo TABLE_HEADING_COMMENTS; ?></b>
            </div>
              <div class="accountBoxContents">
            
                <?php echo tep_draw_textarea_field('comments', 'soft', '60', '5'); ?>
                </div>
     </div>
     
     
       <div class="accountBox">
        
			              
      		
				</div>
				
			<div class="maincaptureb">
						<div class="centerBoxContents">

							<div class="maincapture"><?php echo '<b>' . TITLE_CONTINUE_CHECKOUT_PROCEDURE . '</b><br><i>' . TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></i>
							</div>
							<hr class="hrbreaker">
							
						</div>
		
		<div id="checkoutcontainer">	  
  <div class="checkoutBarFrom"><?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '" class="checkoutBarFrom">' . CHECKOUT_BAR_DELIVERY . '</a>'; ?></div>
            <div class="checkoutBarCurrent"><?php echo tep_image(DIR_WS_IMAGES . 'checkout_bullet.gif'); ?><?php echo CHECKOUT_BAR_PAYMENT; ?></div>
            <div class="checkoutBarTo"><?php echo CHECKOUT_BAR_CONFIRMATION; ?></div>
            <div class="checkoutBarTo"><?php echo CHECKOUT_BAR_FINISHED; ?></div>
		</div>			
				
</div>
</div>

	<div id="resumecontainer">
       
              
      <hr class="hrlogin">
          <div id="recapheader"><div id="fa"><i class="fa fa-home">&nbsp;<span class="pageHeading"><?php echo TABLE_HEADING_BILLING_ADDRESS; ?></span></i></div></div>
          <div id="infoBoxContents">
           
                    <p class="addressbox" style=" display:block; width:100%; text-align:left;"><?php echo tep_address_label($customer_id, $billto, true, ' ', '<br>'); ?></p>
                   <?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_PAYMENT_ADDRESS, '', 'SSL') . '">' . tep_image_button('button_change_address.gif', IMAGE_BUTTON_CHANGE_ADDRESS) . '</a>'; ?>
               
             </div>        
     
	<div class="infoBoxContents ordertotal">        
 <?php
 if (MODULE_ORDER_TOTAL_INSTALLED) {
   //$temp=$order_total_modules->process();
   echo $order_total_modules->output();
 }
 ?>
      
	<?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?>		
          </div>

<hr class="hrloginhr">
	


          </div>  

 </form>     
<!-- body_text_eof //-->
    
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
    
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->

<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>

</body>
</html>

