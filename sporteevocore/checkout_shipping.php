<?php
/*
  $Id: checkout_shipping.php,v 1.16 2003/06/09 23:03:53 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  require('includes/classes/http_client.php');

// if the customer is not logged on, redirect them to the login page
  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// if there is nothing in the customers cart, redirect them to the shopping cart page
  if ($cart->count_contents() < 1) {
    tep_redirect(tep_href_link(FILENAME_SHOPPING_CART));
  }
// BOF PayPal Express Checkout IPN v0.4 beta
  $paypal_ec_check = tep_db_query("SELECT configuration_id FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_EC_STATUS' AND configuration_value = 'True'");
  $ec_enabled = (tep_db_num_rows($paypal_ec_check) ? 1 : 0);
  if ($ec_enabled) {
    require(DIR_WS_CLASSES . 'payment.php');
    $payment_modules = new payment;
    require(DIR_WS_CLASSES . 'order.php');
    $order = new order;
   
    $ec_enabled = (($paypal_ec->enabled)? 1 : 0);
  }

  if ($ec_enabled) tep_redirect(tep_href_link(FILENAME_EXPRESS_CHECKOUT_SHIPPING, '', 'SSL'));
// EOF PayPal Express Checkout IPN v0.4 beta

// if no shipping destination address was selected, use the customers own address as default
  if (!tep_session_is_registered('sendto')) {
    tep_session_register('sendto');
    $sendto = $customer_default_address_id;
  } else {
// verify the selected shipping address
    $check_address_query = tep_db_query("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$sendto . "'");
    $check_address = tep_db_fetch_array($check_address_query);

    if ($check_address['total'] != '1') {
      $sendto = $customer_default_address_id;
      if (tep_session_is_registered('shipping')) tep_session_unregister('shipping');
    }
  }

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

// register a random ID in the session to check throughout the checkout procedure
// against alterations in the shopping cart contents
  if (!tep_session_is_registered('cartID')) tep_session_register('cartID');
  $cartID = $cart->cartID; 

// if the order contains only virtual products, forward the customer to the billing page as
// a shipping address is not needed
  // ###### Added CCGV Contribution #########
//  if ($order->content_type == 'virtual') {
  if (($order->content_type == 'virtual') || ($order->content_type == 'virtual_weight') ) {
// ###### End Added CCGV Contribution #########
    if (!tep_session_is_registered('shipping')) tep_session_register('shipping');
    $shipping = false;
    $sendto = false;
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }

  //MVS Start
  if (SELECT_VENDOR_SHIPPING == 'true') {
  	include(DIR_WS_CLASSES . 'vendor_shipping.php');
  	$shipping_modules = new shipping;
  } else {
  	// MVS End
  
  $total_weight = $cart->show_weight();
  $total_count = $cart->count_contents();

// load all enabled shipping modules
  require(DIR_WS_CLASSES . 'shipping.php');
  $shipping_modules = new shipping;

  if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
    $pass = false;

    switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
      case 'national':
        if ($order->delivery['country_id'] == STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'international':
        if ($order->delivery['country_id'] != STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'both':
        $pass = true;
        break;
    }

    $free_shipping = false;
    if ( ($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
      $free_shipping = true;

      include(DIR_WS_LANGUAGES . $language . '/modules/order_total/ot_shipping.php');
    }
  } else {
    $free_shipping = false;
  }
 
  // MVS
  }

// process the selected shipping method
  if ( isset($_POST['action']) && ($_POST['action'] == 'process') ) {
    if (!tep_session_is_registered('comments')) tep_session_register('comments');
    if (tep_not_null($_POST['comments'])) {
      $comments = tep_db_prepare_input($_POST['comments']);
    }

    if (!tep_session_is_registered('shipping')) tep_session_register('shipping');
    
    // MVS Start
    if (SELECT_VENDOR_SHIPPING == 'true') {
    
    	$total_shipping_cost = 0;
    	$shipping_title = MULTIPLE_SHIP_METHODS_TITLE;
    	$vendor_shipping = $cart->vendor_shipping;
    	$shipping = array();
    	foreach ($vendor_shipping as $vendor_id => $vendor_data) {
    		$products_shipped = $_POST['products_' . $vendor_id];
    		$products_array = explode ("_", $products_shipped);
    
    		$shipping_data = $_POST['shipping_' . $vendor_id];
    		$shipping_array = explode ("_", $shipping_data);
    		$module = $shipping_array[0];
    		$method = $shipping_array[1];
    		$ship_tax = $shipping_array[2];
    
    		if ( is_object($$module) || ($module == 'free') ) {
    			if ($module == 'free') {
    				$quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
    				$quote[0]['methods'][0]['cost'] = '0';
    			} else {
    				$total_weight = $vendor_shipping[$vendor_id]['weight'];
    				$shipping_weight = $total_weight;
    				$cost = $vendor_shipping[$vendor_id]['cost'];
    				$total_count = $vendor_shipping[$vendor_id]['qty'];
    				$quote = $shipping_modules->quote($method, $module, $vendor_id);
    
    			}
    			if (isset($quote['error'])) {
    				tep_session_unregister('shipping');
    			} else {
    				if ( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) ) {
    					$output[$vendor_id] = array('id' => $module . '_' . $method,
    							'title' => $quote[0]['methods'][0]['title'],
    							'ship_tax' => $ship_tax,
    							'products' => $products_array,
    							'cost' => $quote[0]['methods'][0]['cost']
    					);
    					$total_ship_tax += $ship_tax;
    					$total_shipping_cost += $quote[0]['methods'][0]['cost'];
    				}//if isset
    			}//if isset
    		}//if is_object
    	}//foreach
    	if ($free_shipping == true) {
    		$shipping_title = $quote[0]['module'];
    	} elseif (count($output) <2) {
    		$shipping_title = $quote[0]['methods'][0]['title'];
    	}
    	$shipping = array('id' => $shipping,
    			'title' => $shipping_title,
    			'cost' => $total_shipping_cost,
    			'shipping_tax_total' => $total_ship_tax,
    			'vendor' => $output
    	);
    
    	tep_redirect (tep_href_link (FILENAME_CHECKOUT_PAYMENT, '', 'SSL') );
    
    } else {
    	// MVS End

    if ( (tep_count_shipping_modules() > 0) || ($free_shipping == true) ) {
      if ( (isset($_POST['shipping'])) && (strpos($_POST['shipping'], '_')) ) {
        $shipping = $_POST['shipping'];

        list($module, $method) = explode('_', $shipping);
        if ( is_object($$module) || ($shipping == 'free_free') ) {
          if ($shipping == 'free_free') {
            $quote[0]['methods'][0]['title'] = FREE_SHIPPING_TITLE;
            $quote[0]['methods'][0]['cost'] = '0';
          } else {
            $quote = $shipping_modules->quote($method, $module);
          }
          if (isset($quote['error'])) {
            tep_session_unregister('shipping');
          } else {
            if ( (isset($quote[0]['methods'][0]['title'])) && (isset($quote[0]['methods'][0]['cost'])) ) {
              $shipping = array('id' => $shipping,
                                'title' => (($free_shipping == true) ?  $quote[0]['methods'][0]['title'] : $quote[0]['module'] . ' (' . $quote[0]['methods'][0]['title'] . ')'),
                                'cost' => $quote[0]['methods'][0]['cost']);

              tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
            }
          }
        } else {
          tep_session_unregister('shipping');
        }
      }
    } else {
      $shipping = false;
                
      tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
    }    
    // MVS potrebbe essere qui
    }
    
    }


  
  
// get all available shipping quotes
  $quotes = $shipping_modules->quote();

// if no shipping method has been selected, automatically select the cheapest method.
// if the modules status was changed when none were available, to save on implementing
// a javascript force-selection method, also automatically select the cheapest shipping
// method if more than one module is now enabled
  if ( !tep_session_is_registered('shipping') || ( tep_session_is_registered('shipping') && ($shipping == false) && (tep_count_shipping_modules() > 1) ) ) $shipping = $shipping_modules->cheapest();

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_SHIPPING);

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet2.css">
<script language="javascript"><!--

<?php
		// MVS Start
		  if (SELECT_VENDOR_SHIPPING == 'true') {
		?>

		function selectRowEffect(object, buttonSelect, vendor) {

		  var test='defaultSelected_' + vendor;//set aside defaultSelected_' . $vendor_id . '
		  var el=document.getElementsByTagName('tr');//all the tr elements
		  for(var i=0;i<el.length;i++){
		    var p=el[i].id.replace(test,'').replace(/\d/g,'');//strip the $radio_buttons value
		    if(p=='_'){//the only thing left is an underscore
		      el[i].className = "moduleRow";//make the matching elements normal
		    }
		  }

		  object.className = "moduleRowSelected";//override el[i].className and highlight the clicked row

		  var field = document.getElementById('shipping_radio_' + buttonSelect + '_' + vendor);
		  if (document.getElementById) {
		    var field = document.getElementById('shipping_radio_' + buttonSelect + '_' + vendor);
		  } else {
		    var field = document.all['shipping_radio_' + buttonSelect + '_' + vendor];
		  }
		}

		<?php 
		  } else { 
		// MVS End
		?>


var selected;

function selectRowEffect(object, buttonSelect) {
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
  if (document.checkout_address.shipping[0]) {
    document.checkout_address.shipping[buttonSelect].checked=true;
  } else {
    document.checkout_address.shipping.checked=true;
  }
}


<?php
		// MVS
		  } 
		?>


function rowOverEffect(object) {
  if (object.className == 'moduleRow') object.className = 'moduleRowOver';
}

function rowOutEffect(object) {
  if (object.className == 'moduleRowOver') object.className = 'moduleRow'; 
}
//--></script>
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
   <?php echo tep_draw_form('checkout_address', tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL')) . tep_draw_hidden_field('action', 'process'); ?>
          
            <h2><?php echo TABLE_HEADING_SHIPPING_ADDRESS; ?></h2>
         
          <div class="accountBox">
          <div class="accountBoxContents">
         <div id="shipcontainer">
            <div class="shipaddress">
                <i><?php echo TEXT_CHOOSE_SHIPPING_DESTINATION . '</i><br><br>  <div id="buttonbox" ><a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL') . '">' . tep_image_button('button_change_address.gif', IMAGE_BUTTON_CHANGE_ADDRESS) . '</a>'; ?></div>
               
                    <div class="shipaddress2" ><div id="fa"><i class="fa fa-home">&nbsp;<?php echo '<span class="pageHeading">' . TITLE_SHIPPING_ADDRESS . '</span>' ; ?></i></div></div>
                   
                     <p class="addressbox"><?php echo tep_address_label($customer_id, $sendto, true, ' ', '<br>'); ?></p></div> <br>
               
<?php
//MVS
  if (tep_count_shipping_modules() > 0 || SELECT_VENDOR_SHIPPING == 'true') {
?>
      
             <div class="shipaddress2" ><div id="fa"><i class="fa fa-truck">&nbsp;<span class="pageHeading"><?php echo TABLE_HEADING_SHIPPING_METHOD; ?></span></i></div></div>
          
<?php
// MVS Start
  if (SELECT_VENDOR_SHIPPING == 'true') {
    require(DIR_WS_MODULES . 'vendor_shipping.php');
  } else {
    $quotes = $shipping_modules->quote();

  if ( defined('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
    $pass = false;

    switch (MODULE_ORDER_TOTAL_SHIPPING_DESTINATION) {
      case 'national':
        if ($order->delivery['country_id'] == STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'international':
        if ($order->delivery['country_id'] != STORE_COUNTRY) {
          $pass = true;
        }
        break;
      case 'both':
        $pass = true;
        break;
    }

    $free_shipping = false;
    if ( ($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
      $free_shipping = true;

      include(DIR_WS_LANGUAGES . $language . '/modules/order_total/ot_shipping.php');
    }
  } else {
    $free_shipping = false;
  }

// if no shipping method has been selected, automatically select the cheapest method.
// if the modules status was changed when none were available, to save on implementing
// a javascript force-selection method, also automatically select the cheapest shipping
// method if more than one module is now enabled
    if ( !tep_session_is_registered('shipping') || ( tep_session_is_registered('shipping') && ($shipping == false) && (tep_count_shipping_modules() > 1) ) ) $shipping = $shipping_modules->cheapest();
//MVS End
?>      
      
     
<?php
    if (sizeof($quotes) > 1 && sizeof($quotes[0]) > 1) {
?>
             
                <div class="maincapture" ><?php echo TEXT_CHOOSE_SHIPPING_METHOD; ?></div>
                 <?php echo '<i>' . TITLE_PLEASE_SELECT . '</i><br>'; ?></div>
          
             
<?php
    } elseif ($free_shipping == false) {
?>
              <div class="maincapture" ><?php echo TEXT_ENTER_SHIPPING_INFORMATION; ?></div>
               
<?php
    }

    if ($free_shipping == true) {
?>
             
                   <div class="maincapture"><b><?php echo FREE_SHIPPING_TITLE; ?></b>&nbsp;<?php echo $quotes[$i]['icon']; ?></div>
                   
                  <div id="defaultSelected" class="moduleRowSelected" onMouseOver="rowOverEffect(this)" onMouseOut="rowOutEffect(this)" onClick="selectRowEffect(this, 0)">
                   
                    <div class="maincapture"><?php echo sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) . tep_draw_hidden_field('shipping', 'free_free'); ?></div>
                    
<?php
    } else {
      $radio_buttons = 0;
      for ($i=0, $n=sizeof($quotes); $i<$n; $i++) {
?>
            
                    <div class="maincaptureselected"  colspan="3"><b><?php echo $quotes[$i]['module']; ?></b>&nbsp;<?php if (isset($quotes[$i]['icon']) && tep_not_null($quotes[$i]['icon'])) { echo $quotes[$i]['icon']; } ?></div>
                   
<?php
        if (isset($quotes[$i]['error'])) {
?>
                 
                    <div class="maincapture" colspan="3"><?php echo $quotes[$i]['error']; ?></div>
                  
<?php
        } else {
          for ($j=0, $n2=sizeof($quotes[$i]['methods']); $j<$n2; $j++) {
// set the radio button to be checked if it is the method chosen
            $checked = (($quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'] == $shipping['id']) ? true : false);

            if ( ($checked == true) || ($n == 1 && $n2 == 1) ) {
              echo '                  <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
            } else {
              echo '                  <tr class="moduleRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, ' . $radio_buttons . ')">' . "\n";
            }
?>
                  
                    <div class="maincapture" ><?php echo $quotes[$i]['methods'][$j]['title']; ?></div>
<?php
            if ( ($n > 1) || ($n2 > 1) ) {
?>
                    <div class="maincapture"><?php echo $currencies->format(tep_add_tax($quotes[$i]['methods'][$j]['cost'], (isset($quotes[$i]['tax']) ? $quotes[$i]['tax'] : 0))); ?></div>
                    <div class="maincapture" align="right"><?php echo tep_draw_radio_field('shipping', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'], $checked); ?></div>
<?php
            } else {
?>
                    <div class="shipprice2"><?php echo $currencies->format(tep_add_tax($quotes[$i]['methods'][$j]['cost'], $quotes[$i]['tax'])) . tep_draw_hidden_field('shipping', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id']); ?></div>
<?php
            }
?>
                   
<?php
            $radio_buttons++;
          }
        }
?>
                
                
<?php
      }
    }
?>
<?php
  }
  //MVS
  }
?>
<br><br>
      <div id="shipcontainer">
             <div id="commentsbox"><div id="fa"><i class="fa fa-comments">&nbsp;<span class="pageHeading"><?php echo TABLE_HEADING_COMMENTS; ?></span></i></div></div>
          <div class="accountBox">
          <div class="accountBoxContents">
         
                <div><?php echo tep_draw_textarea_field('comments', 'soft', '60', '5'); ?></div>
                
                </div>
              </div>  
               
      <div class="accountBox">
          <div class="centerBoxContents">
               
                <div class="maincapture"><?php echo '<b>' . TITLE_CONTINUE_CHECKOUT_PROCEDURE . '</b><br><i>' . TEXT_CONTINUE_CHECKOUT_PROCEDURE; ?></i></div>
                <hr class="hrbreaker">
               
                </div>
                 <div id="buttonbox" ><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
                
     
               
          <div id="checkoutcontainer">
            <div class="checkoutBarCurrent"><span><?php echo tep_image(DIR_WS_IMAGES . 'checkout_bullet.gif'); ?></span><?php echo CHECKOUT_BAR_DELIVERY; ?></div>
            <div class="checkoutBarTo"><?php echo CHECKOUT_BAR_PAYMENT; ?></div>
           <div class="checkoutBarTo"><?php echo CHECKOUT_BAR_CONFIRMATION; ?></div>
            <div class="checkoutBarTo"><?php echo CHECKOUT_BAR_FINISHED; ?></div>
              </div>
       </div>
       </div>
   
    </div>
   
  
   
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
<br>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
