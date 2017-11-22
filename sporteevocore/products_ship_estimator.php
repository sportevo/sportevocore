<?php
/*
  $Id: products_ship_estimator.php,v 1.2 2008/11/04 kymation Exp $
  $Loc: catalog/

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/8 Francesco Rossi

  Released under the GNU General Public License
*/

  $debug = 'no';
  require_once ('includes/application_top.php');
  require_once ('includes/classes/http_client.php');
  require_once (DIR_WS_LANGUAGES . $language . '/' . FILENAME_PRODUCTS_SHIP_ESTIMATOR);

// Get the action value and scrub it
  $action = '';
  if (isset ($_GET['action']) ) {
    $action = $_GET['action'];
  }
  $action = preg_replace ("(\r\n|\n|\r)", '', $action);  // Remove CR &/ LF
  $action = preg_replace ("/[^a-z_]/i", '', $action); // Strip anything we don't want

  //Get the products id
  $products_id = (int) $_GET['pid']; //Will be 0 if $_GET['pid'] is not set


// If the customer is logged in OR has previously entered the ship-to data,
//   AND the action has not already been set to process the quote, 
//   set the action and get the quote
  if (!tep_not_null ($action) && (tep_session_is_registered ('customer_id') || (tep_session_is_registered ('shippostcode') && tep_session_is_registered ('shipcountry') ) ) ) {
    tep_redirect (tep_href_link (FILENAME_PRODUCTS_SHIP_ESTIMATOR, 'action=process&pid=' . $products_id, 'SSL'));
  }
  
////
// Process the action. Actions are:
//   process: Get a quote
//   ship_error: The customer is not logged in and has not entered the required data
//   reset: Get a new destination to quote
//   end: Clean up and close the window
////
  switch ($action) {
    case 'process':
      //Error if coustomer is not logged in and address is not set
      if (!tep_session_is_registered ('customer_id') && (!tep_session_is_registered ('shippostcode') || !tep_session_is_registered ('shipcountry') || (!tep_session_is_registered ('shipzone') && SHIP_ESTIMATOR_USE_ZONES == 'true') ) ) {
        $error_code = '';
        if (!tep_not_null ($_POST['shippostcode']) || !tep_not_null ($_POST['shipcountry']) ) {
          if (!tep_not_null ($_POST['shippostcode']) ) $error_code .= '&error_shippostcode=1';
          if (!tep_not_null ($_POST['shipzone']) && SHIP_ESTIMATOR_USE_ZONES == 'true') $error_code .= '&error_shipzone=1';
          if (!tep_not_null ($_POST['shipcountry']) ) $error_code .= '&error_shipcountry=1';
          tep_redirect (tep_href_link (FILENAME_PRODUCTS_SHIP_ESTIMATOR, 'action=ship_error' . $error_code, 'NONSSL'));
          exit; // Don't do anything else
        } //if (!tep_not_null
      } //if (!tep_session_is_registered

      // Set the customer's default address if logged in
      if (tep_session_is_registered ('customer_id') ) {
        if (tep_session_is_registered ('customer_default_address_id') ) {
          $customer_default_address_id = $_SESSION['customer_default_address_id'];
        } else {
          $check_customer_query = tep_db_query ("select customers_default_address_id 
                                                 from " . TABLE_CUSTOMERS . " 
                                                 where customers_id = '" . (int) $_SESSION['customer_id'] . "'
                                              ");
          if (tep_db_num_rows ($check_customer_query) ) {
            $check_customer = tep_db_fetch_array ($check_customer_query);
            tep_session_register ('customer_default_address_id');
            $customer_default_address_id = $check_customer['customers_default_address_id'];
          } //if (tep_db_num_row 
        } //if (tep_session_is_registered ... else

        if (!tep_session_is_registered ('sendto') ) {
          tep_session_register ('sendto');
          $sendto = $customer_default_address_id;
        } else {
          // verify the selected shipping address
          $check_address_query = tep_db_query ("select count(*) as total from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . (int) $customer_id . "' and address_book_id = '" . (int) $sendto . "'");
          $check_address = tep_db_fetch_array ($check_address_query);
          if ($check_address['total'] != '1') {
            $sendto = $customer_default_address_id;
            if (tep_session_is_registered('shipping')) tep_session_unregister('shipping');
          } //if ($check_address['total']
        } //if (!tep_session_is_registered ... else
      } //if (tep_session_is_registered
        
      // Order class is needed by the modules for zone, postcode, country
      //   Must be AFTER the default address is set
      require_once (DIR_WS_CLASSES . 'order.php');
      $order = new order;
  
    // Set the $order variables we need if the customer is not logged in
      if (!tep_session_is_registered ('customer_id')) {
        if (!tep_session_is_registered ('shippostcode') ) {
          $shippostcode = $_POST['shippostcode'];
          tep_session_register ('shippostcode');
        } else {
          $shippostcode = $_SESSION['shippostcode'];
        }
        
        if (!tep_session_is_registered ('shipcountry') ) {
          $shipcountry = $_POST['shipcountry'];
          tep_session_register ('shipcountry');
        } else {
          $shipcountry = $_SESSION['shipcountry'];
        }
        
        if (SHIP_ESTIMATOR_USE_ZONES == 'true') {
          if (!tep_session_is_registered ('shipzone') ) {
            $shipzone = $_POST['shipzone'];
            tep_session_register ('shipzone');
          } else {
            $shipzone = $_SESSION['shipzone'];
          }
        }

        $country_info = tep_get_countries ($shipcountry, true);
        
        $shipzone_id = 0;
        if (SHIP_ESTIMATOR_USE_ZONES == 'true') {
          $zones_query = tep_db_query ("select zone_id
                                        from " . TABLE_ZONES . "
                                        where zone_name = '" . $shipzone . "'
                                          or zone_code = '" . $shipzone . "'
                                          and zone_country_id = '" . $shipcountry . "'
                                     ");
          if (tep_db_num_rows ($zones_query)) {
            $zones_info = tep_db_fetch_array ($zones_query);
            $shipzone_id = $zones_info['zone_id'];
          } //if (tep_db_num_rows
        } //if (SHIP_ESTIMATOR_USE_ZONES

        $order->delivery = array('state' => $shipzone,
                                 'postcode' => $shippostcode,
                                 'country' => array ('id' => $shipcountry, 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' => $country_info['countries_iso_code_3']),
                                 'country_id' => $shipcountry,
                                 'format_id' => tep_get_address_format_id ($shipcountry),
                                 'zone_id' => $shipzone_id
                                );
        $order->customer = array('postcode' => $shippostcode,
                                 'country' => array('id' => $shipcountry, 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' => $country_info['countries_iso_code_3']),
                                 'country_id' => $shipcountry,
                                 'format_id' => tep_get_address_format_id ($shipcountry),
                                 'zone_id' => $shipzone_id
                                 );
        $order->billing = array('postcode' => $shippostcode,
                                'country' => array('id' => $shipcountry, 'title' => $country_info['countries_name'], 'iso_code_2' => $country_info['countries_iso_code_2'], 'iso_code_3' => $country_info['countries_iso_code_3']),
                                'country_id' => $shipcountry,
                                'format_id' => tep_get_address_format_id ($shipcountry),
                                'zone_id' => $shipzone_id
                                );
        $order->info = array('total' => $cost, 
                             'currency' => $currency,
                             'shipping_cost' => $shipping['cost'],
                             'shipping_method' => $shipping['title'],
                             'shipping_tax' => $shipping['shipping_tax_total'],
                             'subtotal' => 0,
                             'tax' => $order->products[$i]['tax'] + $shipping['shipping_tax_total'],
                             'tax_groups' => array(),
                             'country_id' => $shipcountry_id,
                             'currency_value'=> $currencies->currencies[$currency]['value']);
                          
        $index = 0;
        if (DISPLAY_PRICE_WITH_TAX == 'true') {
          $order->info['total'] = $order->info['subtotal'] + $order->info['shipping_cost'];
        } else {
          $order->info['total'] = $order->info['subtotal'] + $order->info['tax'] + $order->info['shipping_cost'];
        }
      }
      
      if (SELECT_VENDOR_SHIPPING == 'true') { //MVS
        include_once (DIR_WS_CLASSES . 'vendor_shipping.php');
        $shipping_modules = new shipping;
        
        // Get the product data
        $products_query = tep_db_query("select p.products_id, 
                                               pd.products_name, 
                                               p.products_model, 
                                               p.products_quantity, 
                                               p.products_weight, 
                                               p.products_price, 
                                               p.products_tax_class_id,
                                               p.vendors_id
                                        from " . TABLE_PRODUCTS . " p, 
                                             " . TABLE_PRODUCTS_DESCRIPTION . " pd 
                                        where p.products_status = '1' 
                                          and p.products_id = '" . $products_id . "' 
                                          and pd.products_id = p.products_id 
                                          and pd.language_id = '" . (int) $languages_id . "'
                                     ");
        $products = tep_db_fetch_array ($products_query);
        // Set vendor-specific variables
        $vendors_id = $products['vendors_id'];
        $cart->vendor_shipping[$vendors_id]['weight'] = $products['products_weight'];
        $cart->vendor_shipping[$vendors_id]['cost'] = $products['products_price'];
        $cart->vendor_shipping[$vendors_id]['qty'] = 1;
        
      } else { //Non-MVS
        include_once (DIR_WS_CLASSES . 'shipping.php');
        $shipping_modules = new shipping;

        // Get the product data
        $products_query = tep_db_query("select p.products_id, 
                                               pd.products_name, 
                                               p.products_model, 
                                               p.products_quantity, 
                                               p.products_weight, 
                                               p.products_price, 
                                               p.products_tax_class_id
                                        from " . TABLE_PRODUCTS . " p, 
                                             " . TABLE_PRODUCTS_DESCRIPTION . " pd 
                                        where p.products_status = '1' 
                                          and p.products_id = '" . $products_id . "' 
                                          and pd.products_id = p.products_id 
                                          and pd.language_id = '" . (int) $languages_id . "'
                                     ");
        $products = tep_db_fetch_array ($products_query);
        $vendors_id = 0; //Non-mvs, so we don't have a vendor
      }

      //Set the variables needed by the shipping modules
      $products_id = $products['products_id'];
      $products_name = $products['products_name'];
      $products_model = $products['products_model'];
      $products_quantity = $products['products_quantity'];
      $total_weight = $products['products_weight'];
      $cost = $products['products_price'];
      $products_tax_class_id = $products['products_tax_class_id'];
      $total_count = 1;

        // Get the ship-to address    
        if (tep_session_is_registered ('customer_id') ) {
          $display_address = tep_address_format ($order->delivery['format_id'], $order->delivery, 1, ' ', '<br>'); 
        } else {
          $display_address = $shipzone . '<br>' . $shippostcode . '<br>' . tep_get_country_name ($shipcountry);
        } 

        $quotes_array = array(); //Output array to collect the data we want to display
      
        if (SELECT_VENDOR_SHIPPING == 'true') {
          $shipping_weight = $total_weight;
          $ship_tax = $shipping_tax;   //for taxes

          // Check if qualifies for free shipping
          if ( defined ('MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING') && (MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING == 'true') ) {
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

                include_once (DIR_WS_LANGUAGES . $language . '/modules/order_total/ot_shipping.php');
              }
            } else {
              $free_shipping = false;
            }

            //Get the quotes
            $quotes = $shipping_modules->quote ('', '', $vendors_id);

            $multiple_quotes = false;
            if (count ($quotes) > 1) {
              $multiple_quotes = true;
            }

            // If no free shipping, put the quotes information that we need into an array
            if ($free_shipping == false) {
              for ($quote_no=0, $n=count($quotes); $quote_no<$n; $quote_no++) {
                $icon = '';
                if (isset($quotes[$quote_no]['icon']) && tep_not_null ($quotes[$quote_no]['icon']) ) { 
                  $icon = $quotes[$quote_no]['icon'];
                }
                $quotes_array[$vendors_id]['quotes'][$quote_no]['module'] = $quotes[$quote_no]['module'];
                $quotes_array[$vendors_id]['quotes'][$quote_no]['icon'] = $icon;

                if (isset($quotes[$quote_no]['error'])) {
                  $quotes_array[$vendors_id]['quotes'][$quote_no]['error'] = $quotes[$quote_no]['error'];
                } else {
                  for ($j=0, $n2=sizeof($quotes[$quote_no]['methods']); $j<$n2; $j++) {
                    $shipping_actual_tax = $quotes[$quote_no]['tax'] / 100;
                    $shipping_tax = $shipping_actual_tax * $quotes[$quote_no]['methods'][$j]['cost'];
                    $quotes_array[$vendors_id]['quotes'][$quote_no]['methods'][$j]['title'] = $quotes[$quote_no]['methods'][$j]['title'];
                    $quotes_array[$vendors_id]['quotes'][$quote_no]['methods'][$j]['cost'] = $currencies->format($quotes[$quote_no]['methods'][$j]['cost']);
                  } //for
                } //if (isset($quotes ... else
              } //for ($quote_no=0,
            } //if ($free_shipping
          
        } else { //if (SELECT_VENDOR_SHIPPING
        // End MVS

        //Get the quotes array
        $quotes = $shipping_modules->quote();

        // Check if qualifies for free shipping
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
          } //switch

          $free_shipping = false;
          if ( ($pass == true) && ($order->info['total'] >= MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER) ) {
            $free_shipping = true;

            include_once (DIR_WS_LANGUAGES . $language . '/modules/order_total/ot_shipping.php');
          }
          
        } else {
          $free_shipping = false;
        }

        // If no free shipping, put the quotes information that we need into an array
        if ($free_shipping == false) {
          for ($quote_no=0, $n=sizeof($quotes); $quote_no<$n; $quote_no++) {
            $icon = '';
            if (isset($quotes[$quote_no]['icon']) && tep_not_null ($quotes[$quote_no]['icon']) ) { 
              $icon = $quotes[$quote_no]['icon'];
            }
            $quotes_array[$quote_no]['module'] = $quotes[$quote_no]['module'];
            $quotes_array[$quote_no]['icon'] = $icon;

            if (isset($quotes[$quote_no]['error'])) {
              $quotes_array[$quote_no]['error'] = $quotes[$quote_no]['error'];
            } else {
              for ($module_no=0, $n2=sizeof($quotes[$quote_no]['methods']); $module_no<$n2; $module_no++) {
                $shipping_actual_tax = $quotes[$quote_no]['tax'] / 100;
                $shipping_tax = $shipping_actual_tax * $quotes[$quote_no]['methods'][$module_no]['cost'];
                $quotes_array[$quote_no]['methods'][$module_no]['title'] = $quotes[$quote_no]['methods'][$module_no]['title'];
                $quotes_array[$quote_no]['methods'][$module_no]['cost'] = $currencies->format($quotes[$quote_no]['methods'][$module_no]['cost']);
              } //for
            } //if (isset($quotes ... else
          } // for ($quote_no=0,
        } //if ($free_shipping

//MVS
      } //if (SELECT_VENDOR_SHIPPING ... else
      break;
      
    case 'ship_error': //Customer did not provide needed data, so set error msg
      if (tep_not_null ($_GET['error_shippostcode']) ) {
        $messageStack->add ('ship_estimator', ERROR_SHIPPOSTCODE);
      }
      if (tep_not_null ($_GET['error_shipcountry']) ) {  //Should never happen with country dropdown
        $messageStack->add ('ship_estimator', ERROR_SHIPCOUNTRY);
      }
      break;

    case 'reset': //Remove data so that customer may select a different ship-to
      tep_session_unregister ('shippostcode');
      tep_session_unregister ('shipzone');
      tep_session_unregister ('shipcountry');
      tep_session_close();
      tep_redirect (tep_href_link (FILENAME_PRODUCTS_SHIP_ESTIMATOR, 'pid=' . $products_id, 'NONSSL') );
      break;
      
    case 'end': //Clean up and close the window
      tep_session_close();
?>
      <head>
      <script language="javascript"> <!-- 
      function CloseMe() {   
        self.close(); 
      } 
      // --> </script> 
      </head>  
      <body onload="CloseMe()"> 
      </body> 
<?php
      exit; // Don't do anything else
      break;
      
    case '':
    default:
      break;
      
  } //switch
  
  
?>

<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
  <title><?php echo TITLE; ?></title>
  <base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
  <link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>

<body>
<table width="100%" border="0" align="center" cellpadding="3" cellspacing="3">
<!-- heading //-->
  <tr>
    <td><table border="0" align="center" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td align="center" class="pageHeading"><b><?php echo HEADING_TITLE; ?></b></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator ('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
    </table></td>
  </tr>
<!-- heading_eof //-->

<?php
// Show error messages if any
  if ($messageStack->size ('ship_estimator') > 0) {
?>
<!-- error_messages //-->
  <tr>
    <td><table border="0" align="center" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td><?php echo $messageStack->output ('ship_estimator'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator ('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
    </table></td>
  </tr>
<!-- error_messages_eof //-->
<?php
  } //if ($messageStack->size
?>

<?php
// Customer is not signed in and has not selected destination, 
//   or has clicked reset, so show the form to enter ship-to
  if ($action == '' && $products_id > 0 && !tep_session_is_registered('customer_id') || $action == 'reset' || $action == 'ship_error') {
?>
<!-- ship-to_form //-->
  <tr>
    <td>
    <?php echo tep_draw_form ('shipping_dest', tep_href_link (FILENAME_PRODUCTS_SHIP_ESTIMATOR, 'action=process&pid=' . $products_id, 'SSL'), 'post') . "\n"; ?>
    <table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
      <tr class="infoBoxContents">
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan=2 class="main"><b><?php echo TITLE_SHIPPING_ADDRESS; ?></b></td>
          </tr>
          <tr>
            <td class="infoBoxContents"><?php echo ENTRY_POST_CODE; ?></td>
            <td class="infoBoxContents"><?php echo tep_draw_input_field ('shippostcode') . ' ' . (tep_not_null(ENTRY_POST_CODE) ? '<span class="inputRequirement">* Required</span>': ''); ?></td>
          </tr>
<?php
    if (SHIP_ESTIMATOR_USE_ZONES == 'true') {
?>
          <tr>
            <td class="infoBoxContents"><?php echo ENTRY_STATE; ?></td>
            <td class="infoBoxContents"><?php echo tep_draw_input_field ('shipzone') . ' <span class="inputRequirement">' . FORM_REQUIRED_INFORMATION . '</span>'; ?></td>
          </tr>
<?php
    }
?>
          <tr>
            <td class="infoBoxContents"><?php echo ENTRY_COUNTRY; ?></td>
            <td class="infoBoxContents"><?php echo tep_get_country_list ('shipcountry',STORE_COUNTRY) . ' ' . (tep_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">* Required</span>': ''); ?></td>
          </tr>
            <td class="infoBoxContents"></td>
            <td class="infoBoxContents"><?php echo tep_image_submit ('button_process_quote.gif', IMAGE_BUTTON_PROCESS_QUOTE); ?></td>
          </tr>
        </table></td>
      </tr>
    </table></form></td>
  </tr>
<!-- ship-to_form_eof //-->

<?php
// end of the shipping address form and begin the ship estimate
  } elseif ($action == 'process') {
?>
<!-- ship_quote_heading //-->
  <tr>
    <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
      <tr class="infoBoxContents">
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><b><?php echo HEADING_SHIPPING_ADDRESS; ?></b></td>
          </tr>
          <tr>
            <td class="main"><?php echo $display_address; ?></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
  </tr>
<!-- ship_quote_heading_eof //-->

<?php
// Start MVS
  if (SELECT_VENDOR_SHIPPING == 'true') {
?>
<!-- quote //-->
  <tr>
    <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
      <tr class="infoBoxContents">
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<!-- shipping_quote //-->
          <tr>
            <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
              <tr class="infoBoxContents">
                <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
      if ($multiple_quotes == true) {
?>
                  <tr>
                    <td><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                    <td class="main" width="50%" valign="top"><?php echo TEXT_AVAILABLE_SHIPPING_METHOD; ?></td>
                    <td><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                  </tr>
<?php
      } //if ($multiple_quotes
?>

<?php
      if ($free_shipping == true) {
?>
                  <tr>
                    <td><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                    <td colspan="2" width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                      <tr>
                        <td width="10"><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                        <td class="main" colspan="3"><b><?php echo FREE_SHIPPING_TITLE; ?></b> <?php echo $quotes[$quote_no]['icon']; ?></td>
                        <td width="10"><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                      </tr>
                      <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, 0)">
                        <td width="10"><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                        <td class="main" width="100%"><?php echo sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) . tep_draw_hidden_field('shipping', 'free_free'); ?></td>
                        <td width="10"><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                      </tr>
                    </table></td>
                    <td><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                  </tr>
<?php
      } else {
        if (is_array ($quotes_array[$vendors_id]['quotes']) ) {
          foreach ($quotes_array[$vendors_id]['quotes'] as $quote_data) {
?>
                  <tr>
                    <td><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                    <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                      <tr>
                        <td width="10"><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                        <td class="main" colspan="3"><b><?php echo $quote_data['module']; ?></b> <?php echo $quote_data['icon'];  ?></td>
                        <td width="10"><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                      </tr>
<?php
        if (isset($quote_data[$quote_no]['error'])) {
?>
                      <tr>
                        <td width="10"><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                        <td class="main" colspan="3"><?php echo $quote_data['error']; ?></td>
                        <td width="10"><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                      </tr>
<?php
        } else {
          for ($module_no=0, $n2=sizeof($quote_data['methods']); $module_no<$n2; $module_no++) {
?>
                      <tr>
                        <td width="10"><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                        <td class="main" width="75%"><?php echo $quote_data['methods'][$module_no]['title']; ?></td>
                        <td class="main" align="right"><?php echo $quote_data['methods'][$module_no]['cost']; ?></td>
                        <td class="main" align="right"></td>
                        <td width="10"><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                      </tr>
<?php
          } //for ($module_no=0
        } // if else
?>
                    </table></td>
                    <td><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                  </tr>
<?php
        } //foreach
      } // if
    } //if ($free_shipping ... else
?>
                </table></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><?php echo tep_draw_separator ('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
<?php
  } else { //if (SELECT_VENDOR_SHIPPING
    // End MVS
?>
<!-- quote //-->
          <tr>
            <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
              <tr class="infoBoxContents">
                <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<!-- products_quote //-->
<?php
      if ($multiple_quotes == true) {
?>
                  <tr>
                    <td><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                    <td class="main" width="50%" valign="top"><?php echo TEXT_AVAILABLE_SHIPPING_METHOD; ?></td>
                    <td><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                  </tr>
<?php
      } //if ($multiple_quotes
?>

<?php
    if ($free_shipping == true) {
?>
                  <tr>
                    <td><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                    <td colspan="2" width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                      <tr>
                        <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                        <td class="main" colspan="3"><b><?php echo FREE_SHIPPING_TITLE; ?></b> <?php echo $quotes[$quote_no]['icon']; ?></td>
                        <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                      </tr>
                      <tr id="defaultSelected" class="moduleRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectRowEffect(this, 0)">
                        <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                        <td class="main" width="100%"><?php echo sprintf(FREE_SHIPPING_DESCRIPTION, $currencies->format(MODULE_ORDER_TOTAL_SHIPPING_FREE_SHIPPING_OVER)) . tep_draw_hidden_field('shipping', 'free_free'); ?></td>
                        <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                      </tr>
                    </table></td>
                    <td><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                  </tr>
<?php
    } else { //if ($free_shipping
?>
<?php
      foreach ($quotes_array as $quote_data) {
?>
                  <tr>
                    <td><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                    <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                      <tr>
                        <td width="10"><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                        <td class="main" colspan="3"><b><?php echo $quote_data['module']; ?></b> <?php if (isset($quotes[$quote_no]['icon']) && tep_not_null($quotes[$quote_no]['icon'])) { echo $quotes[$quote_no]['icon']; } ?></td>
                        <td width="10"><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                      </tr>
<?php
        if (isset($quote_data['error'])) {
?>
                      <tr>
                        <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                        <td class="main" colspan="3"><?php echo $quote_data['error']; ?></td>
                        <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                      </tr>
<?php
        } else {
          foreach ($quote_data['methods'] as $method_data) {
?>
                      <tr>
                        <td width="10"><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                        <td class="main" width="75%"><?php echo $method_data['title']; ?></td>
                        <td class="main"><?php echo $method_data['cost']; ?></td>
                        <td width="10"><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                      </tr>
<?php
          } //foreach ($quote_data['methods']
        } //if (isset($quote_data['error'] ... else
?>
                    </table></td>
                    <td><?php echo tep_draw_separator ('pixel_trans.gif', '10', '1'); ?></td>
                  </tr>
<?php
      } //foreach ($quotes_array
    } //if ($free_shipping ... else
?>
                    </table></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><?php echo tep_draw_separator ('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
<?php
//MVS
    } //if (SELECT_VENDOR_SHIPPING ... else
// end of the shipping section
?>
<!-- shipping_quote_eof //-->
<?php
    // Stock Check
    $any_out_of_stock = false;
    if (STOCK_CHECK == 'true') {
      // for ($product_no=0, $n=sizeof($order->products); $product_no<$n; $product_no++) {
      if (tep_check_stock ($products_id, 1) ) {
        $any_out_of_stock = true;
      } //if (tep_check_stock

    // Out of Stock
      $out_of_stock = false;
      if ( (STOCK_ALLOW_CHECKOUT != 'true') && ($any_out_of_stock == true) ) {
        $out_of_stock = true;
?>
<!-- out_of_stock //-->
          <tr>
            <td colspan="2" align="center" class="main"><b><?php echo TEXT_OUT_OF_STOCK; ?></b></td>
          </tr>
<!-- out_of_stock_eof //-->
<?php
      } //if ( (STOCK_ALLOW_CHECKOUT
    } //if (STOCK_CHECK
    
  } //elseif ($action == 'process')
?>
        </table></td>
      </tr>
    </table></td>
  </tr>
<!-- quote_eof //-->
<?php
  if (!tep_session_is_registered ('customer_id') && $action == 'process' && $out_of_stock == false) {
?>
<!-- reset //-->
  <tr>
    <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
      <tr class="infoBoxContents">
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td class="main" width="100%" align="center"><?php echo TEXT_RESET_EXPLAIN; ?> </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator ('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" width="100%" align="center"><?php echo '<a href="' . tep_href_link (FILENAME_PRODUCTS_SHIP_ESTIMATOR, 'action=reset&pid=' . $products_id, 'SSL') . '">' . tep_image_button ('button_change_address.gif', IMAGE_BUTTON_RESET_FORM) . '</a>'; ?> </td>
      </tr>
        </table></td>
      </tr>
    </table></td>
  </tr>
<!-- reset_eof //-->
<?php
  } 
?>

<?php
// if the products_id is 0, give an error message
  if ($products_id < 1) {
?>
<!-- product_not_found //-->
  <tr>
    <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
      <tr class="infoBoxContents">
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
          </tr>
          <tr>
            <td colspan="2" align="center" class="main"><b><?php echo TEXT_PRODUCT_NOT_FOUND; ?></b></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
  </tr>
<!-- product_not_found_eof //-->
<?php 
  } //if ($products_id
?>

<!-- close_window //-->
  <tr>
    <td>
      <p class="smallText" align="center">
      <?php echo '<a href="' . tep_href_link (FILENAME_PRODUCTS_SHIP_ESTIMATOR, 'action=end', 'NONSSL') . '">' . tep_image_button('button_close_window.gif', IMAGE_BUTTON_CLOSE) . '</a>'; ?>
      </p>
    </td>
  </tr>
<!-- close_window_eof //-->
</table>

<?php
  if ($debug == 'yes') {
//        echo '<font color="#FFFFFF">';
        echo '<br>the action is:  ' . $action;
        echo '<br>the $customer_id is:  ' . $customer_id;
        echo '<br>the product cost:  ' . $cost;
        echo '<br>the $total_count (always 1):  ' . $total_count;
        echo '<br>the $total_weight:  ' . $total_weight;
        echo '<br>the $products_id:  ' . $products_id;
        echo '<br>the $products_name:  ' . $order->info['tax'];
        echo '<br>the $vendors_id:  ' . $vendors_id;
        echo '<br>the $shippostcode:  ' .  $shippostcode;
        echo '<br>the $country_info: <pre> ' .  $country_info . '</pre>';
        print_r ($country_info);
        echo '</pre>';
        echo '<br>$order->products array:<pre>  ';
        print_r ($order->products);
        echo '</pre>';
//        echo '</font>';
        // require_once ('show_variables.php');
  } //IF DEBUG
?>
</body>
</html>
<?php require_once (DIR_WS_INCLUDES . 'application_bottom.php'); ?>