<?php
/*
  $Id: checkout_process.php,v 1.128 2003/05/28 18:00:29 hpdl Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/

  include('includes/application_top.php');

// if the customer is not logged on, redirect them to the login page
  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot(array('mode' => 'SSL', 'page' => FILENAME_CHECKOUT_PAYMENT));
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
  
  if (!tep_session_is_registered('sendto')) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
  }

  if ( (tep_not_null(MODULE_PAYMENT_INSTALLED)) && (!tep_session_is_registered('payment')) ) {
    tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL'));
 }

// avoid hack attempts during the checkout procedure by checking the internal cartID
  if (isset($cart->cartID) && tep_session_is_registered('cartID')) {
    if ($cart->cartID != $cartID) {
      tep_redirect(tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL'));
    }
  }

  include(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_PROCESS);

// load selected payment module
  require(DIR_WS_CLASSES . 'payment.php');
  // #################### Added CCGV ######################
  if ($credit_covers) $payment=''; //ICW added for CREDIT CLASS
// #################### End Added CGV ######################
  $payment_modules = new payment($payment);

// load the selected shipping module
 //MVS start
  if (($total_weight > 0 ) || (SELECT_VENDOR_SHIPPING == 'true') ) {
    include_once (DIR_WS_CLASSES . 'vendor_shipping.php');
  } elseif ( ($total_weight > 0 ) || (SELECT_VENDOR_SHIPPING == 'false') ) {
    include_once (DIR_WS_CLASSES . 'shipping.php');
  }
//MVS End
  $shipping_modules = new shipping($shipping);

  require(DIR_WS_CLASSES . 'order.php');
  $order = new order;

// load the before_process function from the payment modules
  $payment_modules->before_process();

  require(DIR_WS_CLASSES . 'order_total.php');
  $order_total_modules = new order_total;

  $order_totals = $order_total_modules->process();

  $sql_data_array = array('customers_id' => $customer_id,
                          'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                          'customers_company' => $order->customer['company'],
                          'customers_street_address' => $order->customer['street_address'],
                          'customers_suburb' => $order->customer['suburb'],
                          'customers_city' => $order->customer['city'],
                          'customers_postcode' => $order->customer['postcode'], 
                          'customers_state' => $order->customer['state'], 
                          'customers_country' => $order->customer['country']['title'], 
                          'customers_telephone' => $order->customer['telephone'], 
                          'customers_email_address' => $order->customer['email_address'],
                          'customers_address_format_id' => $order->customer['format_id'], 
                          'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'], 
                          'delivery_company' => $order->delivery['company'],
						  'tax_country_id' => $order->info['tax_country_id'],
							'tax_zone_id' => $order->info['tax_zone_id'],
						            //PIVACF start
                                            'billing_piva' => $order->billing['piva'],
                                            'billing_cf' => $order->billing['cf'],
                          //PIVACF end

						  
                          'delivery_street_address' => $order->delivery['street_address'], 
                          'delivery_suburb' => $order->delivery['suburb'], 
                          'delivery_city' => $order->delivery['city'], 
                          'delivery_postcode' => $order->delivery['postcode'], 
                          'delivery_state' => $order->delivery['state'], 
                          'delivery_country' => $order->delivery['country']['title'], 
                          'delivery_address_format_id' => $order->delivery['format_id'], 
                          'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'], 
                          'billing_company' => $order->billing['company'],
                          'billing_street_address' => $order->billing['street_address'], 
                          'billing_suburb' => $order->billing['suburb'], 
                          'billing_city' => $order->billing['city'], 
                          'billing_postcode' => $order->billing['postcode'], 
                          'billing_state' => $order->billing['state'], 
                          'billing_country' => $order->billing['country']['title'], 
                          'billing_address_format_id' => $order->billing['format_id'], 
                          'payment_method' => $order->info['payment_method'], 
						  'shipping_module' => $shipping['id'],
                          'cc_type' => $order->info['cc_type'], 
                          'cc_owner' => $order->info['cc_owner'], 
                          'cc_number' => $order->info['cc_number'], 
                          'cc_expires' => $order->info['cc_expires'], 
                          'date_purchased' => 'now()', 
                          'orders_status' => $order->info['order_status'], 
                          'currency' => $order->info['currency'], 
                          'currency_value' => $order->info['currency_value']);
  tep_db_perform(TABLE_ORDERS, $sql_data_array);
  $insert_id = tep_db_insert_id();
  for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
    $sql_data_array = array('orders_id' => $insert_id,
                            'title' => $order_totals[$i]['title'],
                            'text' => $order_totals[$i]['text'],
                            'value' => $order_totals[$i]['value'], 
                            'class' => $order_totals[$i]['code'], 
                            'sort_order' => $order_totals[$i]['sort_order']);
    tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);
  }

  $customer_notification = (SEND_EMAILS == 'true') ? '1' : '0';
  $sql_data_array = array('orders_id' => $insert_id, 
                          'orders_status_id' => $order->info['order_status'], 
                          'date_added' => 'now()', 
                          'customer_notified' => $customer_notification,
                          'comments' => $order->info['comments']);
  tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
  //MVS start
// Insert data into new orders_shipping table
  $shipping_array = $shipping['vendor'];
  foreach ($shipping_array as $vendors_id => $shipping_data) {
    $vendors_query = tep_db_query ("select vendors_name
                                    from " . TABLE_VENDORS . "
                                    where vendors_id = '" . (int)$vendors_id . "'"
                                  );
    $vendors_name = 'Unknown';
    if ($vendors = tep_db_fetch_array($vendors_query)) {
      $vendors_name = $vendors['vendors_name'];
    }
    $shipping_method_array = explode ('_', $shipping_data['id']);
    // Fix the shipper name where needed
    switch ($shipping_method_array[0]) {
      case 'fedex1':
        $shipping_method = 'Federal Express';
      case 'upsxml':
        $shipping_method = 'UPS';
      case 'usps':
        $shipping_method = 'USPS';
      default:
        $shipping_method = $shipping_method_array[0];
    } //switch
    
    $sql_data_array = array ('orders_id' => $insert_id,
                             'vendors_id' => $vendors_id,
                             'shipping_module' => $shipping_method,
                             'shipping_method' => $shipping_data['title'],
                             'shipping_cost' => $shipping_data['cost'],
                             'shipping_tax' =>  $shipping_data['ship_tax'],
                             'vendors_name' => $vendors_name,
                             'vendor_order_sent' => 'no'
                            );
    tep_db_perform (TABLE_ORDERS_SHIPPING, $sql_data_array);
  } //foreach ($shipping_array
//MVS End
 //kgt - discount coupons
  if( tep_session_is_registered( 'coupon' ) && is_object( $order->coupon ) ) {
	  $sql_data_array = array( 'coupons_id' => $order->coupon->coupon['coupons_id'],
                             'orders_id' => $insert_id );
	  tep_db_perform( TABLE_DISCOUNT_COUPONS_TO_ORDERS, $sql_data_array );
  }
  //end kgt - discount coupons
// initialized for the email confirmation
  $products_ordered = '';
  $subtotal = 0;
  $total_tax = 0;

  for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
// Stock Update - Joao Correia
    if (STOCK_LIMITED == 'true') {
      if (DOWNLOAD_ENABLED == 'true') {
        $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename 
                            FROM " . TABLE_PRODUCTS . " p
                            LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                             ON p.products_id=pa.products_id
                            LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                             ON pa.products_attributes_id=pad.products_attributes_id
                            WHERE p.products_id = '" . tep_get_prid($order->products[$i]['id']) . "'";
// Will work with only one option for downloadable products
// otherwise, we have to build the query dynamically with a loop
        $products_attributes = $order->products[$i]['attributes'];
        if (is_array($products_attributes)) {
          $stock_query_raw .= " AND pa.options_id = '" . $products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . $products_attributes[0]['value_id'] . "'";
        }
        $stock_query = tep_db_query($stock_query_raw);
      } else {
        $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
      }
      if (tep_db_num_rows($stock_query) > 0) {
        $stock_values = tep_db_fetch_array($stock_query);
// do not decrement quantities if products_attributes_filename exists
        if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
          $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
        } else {
          $stock_left = $stock_values['products_quantity'];
        }
        tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . $stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
        if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
          tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
        }
      }
    }

// Update products_ordered (for bestsellers list)
    tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");

    $sql_data_array = array('orders_id' => $insert_id,
          'products_id' => tep_get_prid($order->products[$i]['id']),
          'products_model' => $order->products[$i]['model'],
          'products_name' => $order->products[$i]['name'],
          'products_price' => $order->products[$i]['price'],
          'products_cost' => $order->products[$i]['cost'],
          'final_price' => $order->products[$i]['final_price'],
          'products_tax' => $order->products[$i]['tax'],
          'vendors_id' => $order->products[$i]['vendors_id'],
          'products_quantity' => $order->products[$i]['qty']);  
    tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
    $order_products_id = tep_db_insert_id();
	// #################### Added CCGV ######################
    $order_total_modules->update_credit_account($i);//ICW ADDED FOR CREDIT CLASS SYSTEM
// #################### End Added CCGV ######################

//------insert customer choosen option to order--------
    $attributes_exist = '0';
    $products_ordered_attributes = '';
    if (isset($order->products[$i]['attributes'])) {
      $attributes_exist = '1';
      for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
        if (DOWNLOAD_ENABLED == 'true') {
          $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename 
                               from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa 
                               left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                on pa.products_attributes_id=pad.products_attributes_id
                               where pa.products_id = '" . $order->products[$i]['id'] . "' 
                                and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' 
                                and pa.options_id = popt.products_options_id 
                                and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' 
                                and pa.options_values_id = poval.products_options_values_id 
                                and popt.language_id = '" . $languages_id . "' 
                                and poval.language_id = '" . $languages_id . "'";
          $attributes = tep_db_query($attributes_query);
        } else {
          		  
		  
		  // added here for attribute quantity update
//$attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
$attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pa.options_quantity, pa.products_attributes_id from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
// end added here for attribute quantity update

		  
        }
        $attributes_values = tep_db_fetch_array($attributes);
// BOF Separate Pricing Per Customer attribute_groups mod
  if (isset($_SESSION['sppc_customer_group_id']) && $_SESSION['sppc_customer_group_id'] != '0') {
	   $attributes_group_query = tep_db_query("select pag.options_values_price, pag.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " pa left join " . TABLE_PRODUCTS_ATTRIBUTES_GROUPS . " pag using(products_attributes_id) where pa.products_id = '" . tep_get_prid($order->products[$i]['id']) . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pag.customers_group_id = '" . (int)$_SESSION['sppc_customer_group_id'] . "'");
		 if ($attributes_group = tep_db_fetch_array($attributes_group_query)) {
			 $attributes_values['options_values_price'] = $attributes_group['options_values_price'];
			 $attributes_values['price_prefix'] = $attributes_group['price_prefix'];
		 }
  }
// EOF Separate Pricing Per Customer attribute_groups mod

        $sql_data_array = array('orders_id' => $insert_id, 
                                'orders_products_id' => $order_products_id, 
                                'products_options' => $attributes_values['products_options_name'],
                                'products_options_values' => $attributes_values['products_options_values_name'], 
                                'options_values_price' => $attributes_values['options_values_price'], 
								'products_options_id' => $order->products[$i]['attributes'][$j]['option_id'],
'products_options_values_id' => $order->products[$i]['attributes'][$j]['value_id'],
                                'price_prefix' => $attributes_values['price_prefix']);
		// added here for attribute quantity update
         	$new_quantity = $attributes_values['options_quantity'] - $order->products[$i]['qty'];
	      	tep_db_query("update " . TABLE_PRODUCTS_ATTRIBUTES . " set options_quantity = '" . $new_quantity . "' where products_attributes_id = '" . $attributes_values['products_attributes_id'] . "'");
        // end added here for attribute quantity update
        tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

        if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
          $sql_data_array = array('orders_id' => $insert_id, 
                                  'orders_products_id' => $order_products_id, 
                                  'orders_products_filename' => $attributes_values['products_attributes_filename'], 
                                  'download_maxdays' => $attributes_values['products_attributes_maxdays'], 
                                  'download_count' => $attributes_values['products_attributes_maxcount']);
          tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
        }
        $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
      }
    }
//------insert customer choosen option eof ----
    $total_weight += ($order->products[$i]['qty'] * $order->products[$i]['weight']);
    $total_tax += tep_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
    $total_cost += $total_products_price;

    //MVS begin
    if (SELECT_VENDOR_EMAIL_OPTION == 'false') {
      $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
    }
  }
  
  if (SELECT_VENDOR_EMAIL_OPTION == 'true') {
    $order_id = $insert_id;
    require_once (DIR_WS_INCLUDES . 'vendor_order_data.php');
  }
           
// Send order information to vendor           
  function vendors_email ($vendors_id, $oID, $status, $vendor_order_sent) {
    $vendor_order_sent = false;
    $debug = 'no';
    $vendor_order_sent = 'no';
    $index2 = 0;
    //let's get the Vendors
    $vendor_data_query = tep_db_query ("select v.vendors_id, 
                                               v.vendors_name, 
                                               v.vendors_email, 
                                               v.vendors_contact, 
                                               v.vendor_add_info, 
                                               v.vendor_street, 
                                               v.vendor_city, 
                                               v.vendor_state, 
                                               v.vendors_zipcode, 
                                               v.vendor_country, 
                                               v.account_number, 
                                               v.vendors_status_send, 
                                               v.vendors_send_email,
                                               os.shipping_module, 
                                               os.shipping_method, 
                                               os.shipping_cost, 
                                               os.shipping_tax, 
                                               os.vendor_order_sent 
                                      from " . TABLE_VENDORS . " v,  
                                           " . TABLE_ORDERS_SHIPPING . " os 
                                      where v.vendors_id = os.vendors_id 
                                        and v.vendors_id = '" . $vendors_id . "' 
                                        and os.orders_id = '" . (int) $oID . "' 
                                        and v.vendors_status_send = '" . $status . "'
                                        and v.vendors_send_email = '1'
                                    ");
    while ($vendor_order = tep_db_fetch_array ($vendor_data_query)) {
      $vendor_products[$index2] = array ('Vid' => $vendor_order['vendors_id'],
                                         'Vname' => $vendor_order['vendors_name'],
                                         'Vemail' => $vendor_order['vendors_email'],
                                         'Vcontact' => $vendor_order['vendors_contact'],
                                         'Vaccount' => $vendor_order['account_number'],
                                         'Vstreet' => $vendor_order['vendor_street'],
                                         'Vcity' => $vendor_order['vendor_city'],
                                         'Vstate' => $vendor_order['vendor_state'],
                                         'Vzipcode' => $vendor_order['vendors_zipcode'],
                                         'Vcountry' => $vendor_order['vendor_country'],
                                         'Vaccount' => $vendor_order['account_number'],                               'Vinstructions' => $vendor_order['vendor_add_info'],
                                         'Vmodule' => $vendor_order['shipping_module'],                               'Vmethod' => $vendor_order['shipping_method']);
      if ($debug == 'yes') {
        echo 'The vendor query: ' . $vendor_order['vendors_id'] . '<br>';
      }
      $index = 0;
      $vendor_orders_products_query = tep_db_query ("select o.orders_id, 
                                                            o.orders_products_id, 
                                                            o.products_model, 
                                                            o.products_id, 
                                                            o.products_quantity, 
                                                            o.products_name, 
                                                            p.vendors_id,
                                                            p.vendors_prod_comments, 
                                                            p.vendors_prod_id, 
                                                            p.vendors_product_price 
                                                   from " . TABLE_ORDERS_PRODUCTS . " o, 
                                                        " . TABLE_PRODUCTS . " p 
                                                   where p.vendors_id = '" . (int) $vendor_order['vendors_id'] . "' 
                                                     and o.products_id = p.products_id 
                                                     and o.orders_id='" . $oID . "' 
                                                   order by o.products_name
                                                 ");
      while ($vendor_orders_products = tep_db_fetch_array ($vendor_orders_products_query) ) {
        $vendor_products[$index2]['vendor_orders_products'][$index] = array(
                                      'Pqty' => $vendor_orders_products['products_quantity'],
                                      'Pname' => $vendor_orders_products['products_name'],
                                      'Pmodel' => $vendor_orders_products['products_model'],
                                      'Pprice' => $vendor_orders_products['products_price'],
                                      'Pvendor_name' => $vendor_orders_products['vendors_name'],
                                      'Pcomments' => $vendor_orders_products['vendors_prod_comments'],
                                      'PVprod_id' => $vendor_orders_products['vendors_prod_id'],
                                      'PVprod_price' => $vendor_orders_products['vendors_product_price'],
                                      'spacer' => '-');

        if ($debug == 'yes') {
          echo 'The products query: ' . $vendor_orders_products['products_name'] . '<br>';
        }
        $subindex = 0;
        $vendor_attributes_query = tep_db_query("select products_options, products_options_values, options_values_price, price_prefix from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int)$oID . "' and orders_products_id = '" . (int)$vendor_orders_products['orders_products_id'] . "'");
        if (tep_db_num_rows($vendor_attributes_query)) {
          while ($vendor_attributes = tep_db_fetch_array($vendor_attributes_query)) {
            $vendor_products[$index2]['vendor_orders_products'][$index]['vendor_attributes'][$subindex] = array ('option' => $vendor_attributes['products_options'],
                                                                           'value' => $vendor_attributes['products_options_values'],
                                                                           'prefix' => $vendor_attributes['price_prefix'],
                                                                           'price' => $vendor_attributes['options_values_price']
                                                                                                              );

            $subindex++;
          }
        }
        $index++;
      }
      $index2++;
      // let's build the email
      // Get the delivery address
      $delivery_address_query = tep_db_query("select distinct delivery_company, delivery_name, delivery_street_address, delivery_city, delivery_state, delivery_postcode from " . TABLE_ORDERS . " where orders_id='" . $oID ."'") ;
      $vendor_delivery_address_list = tep_db_fetch_array($delivery_address_query);

      if ($debug == 'yes') {
        echo 'The number of vendors: ' . sizeof($vendor_products) . '<br>';
      }
      $email='';
      for ($l=0, $m=sizeof($vendor_products); $l<$m; $l++) {
        $vendor_country = tep_get_country_name($vendor_products[$l]['Vcountry']);
        $order_number = $oID;
        $vendors_id = $vendor_products[$l]['Vid'];
        $the_email = $vendor_products[$l]['Vemail'];
        $the_name = $vendor_products[$l]['Vname'];
        $the_contact = $vendor_products[$l]['Vcontact'];
        $email = '<b>To: ' . $the_contact . '  <br>' . 
                 $the_name . '<br>' . 
                 $the_email . '<br>' .
                 $vendor_products[$l]['Vstreet'] .'<br>' .
                 $vendor_products[$l]['Vcity'] .', ' .
                 $vendor_products[$l]['Vstate'] .'  ' .
                 $vendor_products[$l]['Vzipcode'] . ' ' . 
                 $vendor_country . '<br>' . 
                 '<br>' . EMAIL_SEPARATOR . '<br>' . 
                 'Special Comments or Instructions:  ' . 
                 $vendor_products[$l]['Vinstructions'] .'<br>' . 
                 '<br>' . EMAIL_SEPARATOR . '<br>' . 
                 'From: ' . STORE_OWNER . '<br>' . 
                 STORE_NAME_ADDRESS . '<br>' . 
                 'Accnt #: ' . $vendor_products[$l]['Vaccount'] . 
                 '<br>' . EMAIL_SEPARATOR . '<br>' . 
                 EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . 
                 '<br>' .  EMAIL_SEPARATOR . '<br>' . 
                 '<br> Shipping Method: ' .  $vendor_products[$l]['Vmodule'] . ' -- '  .  $vendor_products[$l]['Vmethod'] .  
                 '<br>' .  EMAIL_SEPARATOR . '<br>' . '<br>Dropship deliver to:<br>' .
                 $vendor_delivery_address_list['delivery_company'] .'<br>' .
                 $vendor_delivery_address_list['delivery_name'] .'<br>' .
                 $vendor_delivery_address_list['delivery_street_address'] .'<br>' .
                 $vendor_delivery_address_list['delivery_city'] .', ' .
                 $vendor_delivery_address_list['delivery_state'] . ' ' . 
                 $vendor_delivery_address_list['delivery_postcode'] . '<br><br>' .
                 '<table width="75%" border=1 cellspacing="0" cellpadding="3">' . 
                 '<tr>' . 
                 '<td>Qty:</td>' . 
                 '<td>Product Name:</td>' . 
                 '<td>Item Code/Number:</td>' . 
                 '<td>Product Model:</td>' . 
                 '<td>Per Unit Price:</td>' . 
                 '<td>Item Comments: </td>' . 
                 '</tr>';

        for ($i=0, $n=sizeof($vendor_products[$l]['vendor_orders_products']); $i<$n; $i++) {
          $product_attribs = '';

          if (isset($vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes']) && (sizeof($vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes']) > 0)) {

            for ($j = 0, $k = sizeof($vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes']); $j < $k; $j++) {
              $product_attribs .= '&nbsp;&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes'][$j]['option'] . ': ' .  $vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes'][$j]['value'] . '<br>';
            }
          }
          $email .= '<tr>' . 
            '<td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['Pqty'] . '</td>' . 
            '<td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['Pname'] . '<br>&nbsp;&nbsp;<i>Option<br> ' . $product_attribs . '</i></td>' . 
            '<td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['PVprod_id'] . '</td>' . 
            '<td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['Pmodel'] . '</td>' . 
            '<td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['PVprod_price'] . '</td>' . 
            '<td><b>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['Pcomments'] . '</b></td>' . 
            '</tr>';

        } //for ($i=0, 
      } //for ($i=0, 
      $email = $email . '</table><br><HR><br>';

      if (tep_mail ($the_name, $the_email, EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID ,  $email .  '<br>', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS) ) $vendor_order_sent = 'yes';

      if ( $vendor_order_sent == 'yes') {
        tep_db_query ("update " . TABLE_ORDERS_SHIPPING . " set vendor_order_sent = 'yes' where orders_id = '" . (int) $oID . "'  and vendors_id = '" . (int) $vendors_id . "'");
      } else {
        tep_db_query ("update " . TABLE_ORDERS_SHIPPING . " set vendor_order_sent = 'no' where orders_id = '" . (int) $oID . "'  and vendors_id = '" . (int) $vendors_id . "'");
      }
      
      if ($debug == 'yes') {
        echo 'The $email(including headers:<br>Vendor Email Addy' . $the_email . '<br>Vendor Name' . $the_name . '<br>Vendor Contact' . $the_contact . '<br>Body--<br>' . $email . '<br>';
      }

    } //while ($vendor_order

    return true;
  } //function
//MVS end
// #################### Added CCGV ######################
 $order_total_modules->apply_credit();//ICW ADDED FOR CREDIT CLASS SYSTEM
// #################### End Added CCGV ######################

// lets start with the email confirmation
//---  Beginning of addition: Ultimate HTML Emails  ---//
if (EMAIL_USE_HTML == 'true') {
	require(DIR_WS_MODULES . 'UHtmlEmails/checkout_process_'. ULTIMATE_HTML_EMAIL_LAYOUT .'.php');
	$email_order = $html_email;
}else{//Send text email
//---  End of addition: Ultimate HTML Emails  ---//

  $email_order = STORE_NAME . "\n" . 
                 EMAIL_SEPARATOR . "\n" . 
                 EMAIL_TEXT_ORDER_NUMBER . ' ' . $insert_id . "\n" .
                 EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $insert_id, 'SSL', false) . "\n" .
                 EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";
  if ($order->info['comments']) {
    $email_order .= tep_db_output($order->info['comments']) . "\n\n";
  }
  $email_order .= EMAIL_TEXT_PRODUCTS . "\n" . 
                  EMAIL_SEPARATOR . "\n" . 
                  $products_ordered . 
                  EMAIL_SEPARATOR . "\n";

  for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
    $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
  }

  if ($order->content_type != 'virtual') {
    $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" . 
                    EMAIL_SEPARATOR . "\n" .
                    tep_address_label($customer_id, $sendto, 0, '', "\n") . "\n";
  }

  $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" .
                  EMAIL_SEPARATOR . "\n" .
                  tep_address_label($customer_id, $billto, 0, '', "\n") . "\n\n";
  if (is_object($$payment)) {
    $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" . 
                    EMAIL_SEPARATOR . "\n";
    $payment_class = $$payment;
    $email_order .= $payment_class->title . "\n\n";
    if ($payment_class->email_footer) { 
      $email_order .= $payment_class->email_footer . "\n\n";
    }
  }
 //---  Beginning of addition: Ultimate HTML Emails  ---//
}

if(ULTIMATE_HTML_EMAIL_DEVELOPMENT_MODE === 'true'){
	//Save the contents of the generated html email to the harddrive in .htm file. This can be practical when developing a new layout.
	$TheFileName = 'Last_mail_from_checkout_process.php.htm';
	$TheFileHandle = fopen($TheFileName, 'w') or die("can't open error log file");
	fwrite($TheFileHandle, $email_order);
	fclose($TheFileHandle);
}
//---  End of addition: Ultimate HTML Emails  ---//
 
  tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

// send emails to other people
  if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
    tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
  }

  $payment_modules->after_process();

  $cart->reset(true);

// unregister session variables used during checkout
  tep_session_unregister('sendto');
  tep_session_unregister('billto');
  tep_session_unregister('shipping');
  tep_session_unregister('payment');
  tep_session_unregister('comments');
  
    //kgt - discount coupons
  tep_session_unregister('coupon');
  //end kgt - discount coupons 
  // #################### Added CCGV ######################
   if(tep_session_is_registered('credit_covers')) tep_session_unregister('credit_covers');
  $order_total_modules->clear_posts();//ICW ADDED FOR CREDIT CLASS SYSTEM
// #################### End Added CCGV ######################

  tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>
