<?php
/*
  $Id: ec_shipping.php,v 0.4.1.0 beta 2007/02/18 07:08:00 Alex Li Exp $

  Copyright (c) 2015/7 AlexStudio
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

  $paypal_ec_check = tep_db_query("SELECT configuration_id FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_EC_STATUS' AND configuration_value = 'True'");
  $ec_enabled = (tep_db_num_rows($paypal_ec_check) ? 1 : 0);
  if ($ec_enabled) {
    require(DIR_WS_CLASSES . 'payment.php');
    $payment_modules = new payment;
    $paypal_ec->update_status();
    $ec_enabled = (($paypal_ec->enabled)? 1 : 0);
  }

  if ($ec_enabled != 1) tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
  
  if (isset($_POST['action'])) {
    if (!tep_session_is_registered('paypal_ec_method')) tep_session_register('paypal_ec_method');
    $_SESSION['paypal_ec_method'] = 'normal';
  }

  if (isset($_GET['address_error']) && tep_session_is_registered('paypal_ec_address_error')) {
    $address_error = $_SESSION['paypal_ec_address_error'];
    tep_session_unregister('paypal_ec_address_error');
    if ($_GET['address_error'] == 0) {
      $paypal_ec->ec_gen_error('', true, true);
    }
  } else $address_error = '';

// register a random ID in the session to check throughout the checkout procedure
// against alterations in the shopping cart contents
  if (!tep_session_is_registered('cartID')) tep_session_register('cartID');
  $cartID = $cart->cartID;

// if the order contains only virtual products, forward the customer to the billing page as
// a shipping address is not needed
  if ($order->content_type == 'virtual') {
    if (!tep_session_is_registered('shipping')) tep_session_register('shipping');
    $shipping = false;
    $sendto = false;

    if ($ec_enabled != 1 || MODULE_PAYMENT_PAYPAL_EC_BUTTON_INSTALLED != 'Yes' || tep_session_is_registered('paypal_ec_method')) tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }
  
  if ($order->content_type != 'virtual') {

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

// process the selected shipping method
    if ( isset($_POST['action']) && ($_POST['action'] == 'process') ) {
      if (!tep_session_is_registered('comments')) tep_session_register('comments');
      if (tep_not_null($_POST['comments'])) {
        $comments = tep_db_prepare_input($_POST['comments']);
      }

      if (!tep_session_is_registered('shipping')) tep_session_register('shipping');

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

                if (tep_session_is_registered('paypal_ec_token') && tep_not_null($_SESSION['paypal_ec_token'])) tep_redirect(tep_href_link(FILENAME_EXPRESS_CHECKOUT, '', 'SSL'));
                else tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
              }
            }
          } else {
            tep_session_unregister('shipping');
          }
        }
      } else {
        $shipping = false;

        if (tep_session_is_registered('paypal_ec_token') && tep_not_null($_SESSION['paypal_ec_token'])) tep_redirect(tep_href_link(FILENAME_EXPRESS_CHECKOUT, '', 'SSL'));
        else tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
      }    
    }

// get all available shipping quotes
    $quotes = $shipping_modules->quote();

// if no shipping method has been selected, automatically select the cheapest method.
// if the modules status was changed when none were available, to save on implementing
// a javascript force-selection method, also automatically select the cheapest shipping
// method if more than one module is now enabled
    if ( !tep_session_is_registered('shipping') || ( tep_session_is_registered('shipping') && ($shipping == false) && (tep_count_shipping_modules() > 1) ) ) $shipping = $shipping_modules->cheapest();

    if (isset($_GET['action']) && $_GET['action'] == 'ec_cancel') $paypal_ec->ec_gen_error('', true, true);
  } else { // Virtual orders loop starts here
    if (!tep_session_is_registered('paypal_ec_order_info')) $paypal_ec->ec_gen_error('', true, true);
    if (!tep_session_is_registered('comments')) tep_session_register('comments');
    if (tep_not_null($_POST['comments'])) {
      $comments = tep_db_prepare_input($_POST['comments']);
    }
    if (!tep_session_is_registered('shipping')) tep_session_register('shipping');
    if ( isset($_POST['action']) && $_POST['action'] == 'process') {
      if (tep_session_is_registered('paypal_ec_order_info')) {
        tep_redirect(tep_href_link(FILENAME_EXPRESS_CHECKOUT, '', 'SSL'));
      } else {
        if (tep_session_is_registered('payment')) tep_session_unregister('payment');
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
      }
    }
  } // Virtual orders loop ends here

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_SHIPPING);

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_EXPRESS_CHECKOUT_SHIPPING, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_EXPRESS_CHECKOUT_SHIPPING, '', 'SSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">

<script language="javascript"><!--
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

<!-- body //-->

<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
   
<!-- body_text //-->
   <div id="stscart">
<?php
  if (MODULE_PAYMENT_PAYPAL_EC_BUTTON_INSTALLED == 'Yes' && !tep_session_is_registered('paypal_ec_method') && $order->content_type != 'virtual') {
    echo tep_draw_form('checkout_address', tep_href_link(FILENAME_EXPRESS_CHECKOUT_SHIPPING, '', 'SSL')) . tep_draw_hidden_field('action', 'normal');
  } else {
    echo tep_draw_form('checkout_address', tep_href_link(FILENAME_EXPRESS_CHECKOUT_SHIPPING, '', 'SSL')) . tep_draw_hidden_field('action', 'process');
  }
?>
    
            <span class="pageHeading"><?php echo HEADING_TITLE; ?></span>
           
<?php
  if (isset($_GET['address_error']) && tep_not_null($address_error)) {
?>
      
            <div class="shipaddress"><b><?php echo tep_output_string_protected($address_error['title']); ?></b></div>
          <table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBoxNotice">
          <tr class="infoBoxNoticeContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
               
                <td class="main" width="100%" valign="top"><?php echo $address_error['message']; ?></td>
                
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
     
<?php
  }
  if ($order->content_type != 'virtual') {
    if (MODULE_PAYMENT_PAYPAL_EC_BUTTON_INSTALLED == 'Yes' && !tep_session_is_registered('paypal_ec_method') && !tep_session_is_registered('paypal_ec_token')) {
?>
      
            <div class="maincapture"><b><?php echo TABLE_HEADING_EXPRESS_CHECKOUT; ?></b></div>
          <div class="accountBox">
          <div class="accountBoxContents">
          
                <div class="maincapture" width="152"><?php echo '<a href="' . tep_href_link(FILENAME_EXPRESS_CHECKOUT, 'action=express', 'SSL') . '">' . MODULE_PAYMENT_PAYPAL_EC_BUTTON . '</a>'; ?></div>
                <div class="maincapture" valign="middle"><b><?php echo  MODULE_PAYMENT_PAYPAL_EC_BUTTON_TEXT; ?></b></div>
            
        </div><
     
<?php
    }
    if (MODULE_PAYMENT_PAYPAL_EC_BUTTON_INSTALLED != 'Yes' || tep_session_is_registered('paypal_ec_method') || tep_session_is_registered('paypal_ec_token') ) {
?>
      
      
       <div id="shipcontainer">
            <div class="shipaddress"><b><?php echo TABLE_HEADING_SHIPPING_ADDRESS; ?></b></div>
         <div class="accountBox">
          <div class="accountBoxContents">
          
                <div class="maincapture" ><?php echo TEXT_CHOOSE_SHIPPING_DESTINATION . '<br><br><a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING_ADDRESS, '', 'SSL') . '">' . tep_image_button('button_change_address.gif', IMAGE_BUTTON_CHANGE_ADDRESS) . '</a>'; ?></div>
               
                    <div class="shipaddress2" ><div id="fa"><i class="fa fa-home">&nbsp;<?php echo '<b>' . TITLE_SHIPPING_ADDRESS . '' ?></b><br></i></div></div>
                    
                  
                    <div class="maincapture"><?php echo tep_address_label($customer_id, $sendto, true, ' ', '<br>'); ?></div>
                   
<?php
      if (tep_count_shipping_modules() > 0) {
?>
      
            <div class="shipaddress2"><div id="fa"><i class="fa fa-truck"><b>&nbsp;<?php echo TABLE_HEADING_SHIPPING_METHOD; ?></b></i></div></div>
         <div class="accountBox">
          <div class="accountBoxContents">
            
<?php
        if (sizeof($quotes) > 1 && sizeof($quotes[0]) > 1) {
?>
              
                <div class="maincapture" ><?php echo TEXT_CHOOSE_SHIPPING_METHOD; ?></div>
                <div class="maincapture" ><?php echo '<b>' . TITLE_PLEASE_SELECT . '</b><br>' . tep_image(DIR_WS_IMAGES . 'arrow_east_south.gif'); ?></div>
              
<?php
        } elseif ($free_shipping == false) {
?>
             
                <div class="maincapture"><?php echo TEXT_ENTER_SHIPPING_INFORMATION; ?></div>
               <?php
        }

        if ($free_shipping == true) {
?>
             
                    <div class="maincapture"><b><?php echo FREE_SHIPPING_TITLE; ?></b>&nbsp;<?php echo $quotes[$i]['icon']; ?></div>
                   
                  <div id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, 0)">
                   
                    <div class="maincapture"><?php echo sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) . tep_draw_hidden_field('shipping', 'free_free'); ?></div>
                  
<?php
        } else {
          $radio_buttons = 0;
          for ($i=0, $n=sizeof($quotes); $i<$n; $i++) {
?>
             
                    <div class="maincaptureselected" colspan="3"><b><?php echo $quotes[$i]['module']; ?></b>&nbsp;<?php if (isset($quotes[$i]['icon']) && tep_not_null($quotes[$i]['icon'])) { echo $quotes[$i]['icon']; } ?></div>
                   
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
                    <div class="maincapture" ><?php echo tep_draw_radio_field('shipping', $quotes[$i]['id'] . '_' . $quotes[$i]['methods'][$j]['id'], $checked); ?></div>
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
    }
  } else { // Virtual orders start here
    if (MODULE_PAYMENT_PAYPAL_EC_BUTTON_INSTALLED == 'Yes' && !tep_session_is_registered('paypal_ec_method') && !tep_session_is_registered('paypal_ec_token')) {
?>
      
           <div class="maincapture"><b><?php echo TABLE_HEADING_EXPRESS_CHECKOUT; ?></b></div>
          <div class="accountBox">
          <div class="accountBoxContents">
           
                <div class="maincapture" ><?php echo '<a href="' . tep_href_link(FILENAME_EXPRESS_CHECKOUT, 'action=express', 'SSL') . '">' . MODULE_PAYMENT_PAYPAL_EC_BUTTON . '</a>'; ?></div>
                <div class="maincapture" ><b><?php echo  MODULE_PAYMENT_PAYPAL_EC_BUTTON_VIRTUAL_TEXT; ?></b></div>
              
<?php
    }
  } // Virtual orders end here
  if (MODULE_PAYMENT_PAYPAL_EC_BUTTON_INSTALLED != 'Yes' || (tep_session_is_registered('paypal_ec_method') && tep_session_is_registered('paypal_ec_token'))) {
?>
      
           <div id="commentsbox"><div id="fa"><i class="fa fa-comments"><b><?php echo TABLE_HEADING_COMMENTS; ?></b></i></div></div>
         <div class="accountBox">
          <div class="accountBoxContents">
          
                <div><?php echo tep_draw_textarea_field('comments', 'soft', '60', '5'); ?></div>
                	
              
<?php } ?></div>
      <div class="accountBox">
          <div class="accountBoxContents">
            
                <div class="maincapture">
<?php
  if (tep_session_is_registered('paypal_ec_payer_info')) echo '<b>' . TITLE_CONTINUE_CHECKOUT_PROCEDURE . '</b><br>' . TEXT_CONTINUE_REVIEW_PAYMENT;
  else if (MODULE_PAYMENT_PAYPAL_EC_BUTTON_INSTALLED == 'Yes' && !tep_session_is_registered('paypal_ec_method')) {
    if ($order->content_type != 'virtual') echo '<b>' . TITLE_EC_CONTINUE_CHECKOUT_PROCEDURE . '</b><br>' . TEXT_CONTINUE_CHECKOUT_SHIPPING;
    else echo '<b>' . TITLE_EC_CONTINUE_CHECKOUT_PROCEDURE . '</b><br>' . TEXT_CONTINUE_CHECKOUT_PROCEDURE;
  }
  else echo '<b>' . TITLE_CONTINUE_CHECKOUT_PROCEDURE . '</b><br>' . TEXT_CONTINUE_CHECKOUT_PROCEDURE;
?>
                </div>
                <div class="maincapture" ><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
               
                
                <div><?php echo tep_image(DIR_WS_IMAGES . 'checkout_bullet.gif'); ?></div>
               
            <div class="checkoutBarCurrent"><?php echo CHECKOUT_BAR_DELIVERY; ?></div>
            <div class="checkoutBarTo"><?php echo CHECKOUT_BAR_PAYMENT; ?></div>
            <div class="checkoutBarTo"><?php echo CHECKOUT_BAR_CONFIRMATION; ?></div>
            <div class="checkoutBarTo"><?php echo CHECKOUT_BAR_FINISHED; ?></div>
         
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
