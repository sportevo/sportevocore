<?php
/*
  $Id: orders.php,v 1.112 2003/06/29 22:50:52 hpdl Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi.  Theme and Icons Copyright (c) G Burton 2008 and are NOT GPL.

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
//MVS start
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
                                      where v.vendors_id=os.vendors_id 
                                        and v.vendors_id='" . $vendors_id . "' 
                                        and os.orders_id='" . (int) $oID . "' 
                                        and v.vendors_status_send='" . $status . "'
                                        and v.vendors_send_email = '1'
                                   ");
    while ($vendor_order = tep_db_fetch_array($vendor_data_query)) {
      $vendor_products[$index2] = array (
        'Vid' => $vendor_order['vendors_id'],
        'Vname' => $vendor_order['vendors_name'],
        'Vemail' => $vendor_order['vendors_email'],
        'Vcontact' => $vendor_order['vendors_contact'],
        'Vaccount' => $vendor_order['account_number'],
        'Vstreet' => $vendor_order['vendor_street'],
        'Vcity' => $vendor_order['vendor_city'],
        'Vstate' => $vendor_order['vendor_state'],
        'Vzipcode' => $vendor_order['vendors_zipcode'],
        'Vcountry' => $vendor_order['vendor_country'],
        'Vaccount' => $vendor_order['account_number'],
        'Vinstructions' => $vendor_order['vendor_add_info'],
        'Vmodule' => $vendor_order['shipping_module'],
        'Vmethod' => $vendor_order['shipping_method']
      );
      if ($debug == 'yes') {
        echo 'The vendor query: ' . $vendor_order['vendors_id'] . '<br>';
      }
      $index = 0;
      $vendor_orders_products_query = tep_db_query("select o.orders_id, o.orders_products_id, o.products_model, o.products_id, o.products_quantity, o.products_name, p.vendors_id,  p.vendors_prod_comments, p.vendors_prod_id, p.vendors_product_price from " . TABLE_ORDERS_PRODUCTS . " o, " . TABLE_PRODUCTS . " p where p.vendors_id='" . (int) $vendor_order['vendors_id'] . "' and o.products_id=p.products_id and o.orders_id='" . $oID . "' order by o.products_name");
      while ($vendor_orders_products = tep_db_fetch_array($vendor_orders_products_query)) {
        $vendor_products[$index2]['vendor_orders_products'][$index] = array (
          'Pqty' => $vendor_orders_products['products_quantity'],
          'Pname' => $vendor_orders_products['products_name'],
          'Pmodel' => $vendor_orders_products['products_model'],
          'Pprice' => $vendor_orders_products['products_price'],
          'Pvendor_name' => $vendor_orders_products['vendors_name'],
          'Pcomments' => $vendor_orders_products['vendors_prod_comments'],
          'PVprod_id' => $vendor_orders_products['vendors_prod_id'],
          'PVprod_price' => $vendor_orders_products['vendors_product_price'],
          'spacer' => '-'
        );

        if ($debug == 'yes') {
          echo 'The products query: ' . $vendor_orders_products['products_name'] . '<br>';
        }
        $subindex = 0;
        $vendor_attributes_query = tep_db_query("select products_options, products_options_values, options_values_price, price_prefix from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int) $oID . "' and orders_products_id = '" . (int) $vendor_orders_products['orders_products_id'] . "'");
        if (tep_db_num_rows($vendor_attributes_query)) {
          while ($vendor_attributes = tep_db_fetch_array($vendor_attributes_query)) {
            $vendor_products[$index2]['vendor_orders_products'][$index]['vendor_attributes'][$subindex] = array (
              'option' => $vendor_attributes['products_options'],
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
      $delivery_address_query = tep_db_query("select distinct delivery_company, delivery_name, delivery_street_address, delivery_city, delivery_state, delivery_postcode from " . TABLE_ORDERS . " where orders_id='" . $oID . "'");
      $vendor_delivery_address_list = tep_db_fetch_array($delivery_address_query);

      if ($debug == 'yes') {
        echo 'The number of vendors: ' . sizeof($vendor_products) . '<br>';
      }
      $email = '';
      for ($l = 0, $m = sizeof($vendor_products); $l < $m; $l++) {

        $vendor_country = tep_get_country_name($vendor_products[$l]['Vcountry']);
        $order_number = $oID;
        $vendors_id = $vendor_products[$l]['Vid'];
        $the_email = $vendor_products[$l]['Vemail'];
        $the_name = $vendor_products[$l]['Vname'];
        $the_contact = $vendor_products[$l]['Vcontact'];
        $email = '<b>To: ' . $the_contact . '  <br>' . $the_name . '<br>' . $the_email . '<br>' .
        $vendor_products[$l]['Vstreet'] . '<br>' .
        $vendor_products[$l]['Vcity'] . ', ' .
        $vendor_products[$l]['Vstate'] . '  ' .
        $vendor_products[$l]['Vzipcode'] . ' ' . $vendor_country . '<br>' . '<br>' . EMAIL_SEPARATOR . '<br>' . 'Special Comments or Instructions:  ' . $vendor_products[$l]['Vinstructions'] . '<br>' . '<br>' . EMAIL_SEPARATOR . '<br>' . 'From: ' . STORE_OWNER . '<br>' . STORE_NAME_ADDRESS . '<br>' . 'Accnt #: ' . $vendor_products[$l]['Vaccount'] . '<br>' . EMAIL_SEPARATOR . '<br>' . EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . '<br>' . EMAIL_SEPARATOR . '<br>' . '<br> Shipping Method: ' . $vendor_products[$l]['Vmodule'] . ' -- ' . $vendor_products[$l]['Vmethod'] . '<br>' . EMAIL_SEPARATOR . '<br>' . '<br>Dropship deliver to:<br>' .
        $vendor_delivery_address_list['delivery_company'] . '<br>' .
        $vendor_delivery_address_list['delivery_name'] . '<br>' .
        $vendor_delivery_address_list['delivery_street_address'] . '<br>' .
        $vendor_delivery_address_list['delivery_city'] . ', ' .
        $vendor_delivery_address_list['delivery_state'] . ' ' . $vendor_delivery_address_list['delivery_postcode'] . '<br><br>';
        $email = $email . '<table width="75%" border=1 cellspacing="0" cellpadding="3">
            <tr><td>Qty:</td><td>Product Name:</td><td>Item Code/Number:</td><td>Product Model:</td><td>Per Unit Price:</td><td>Item Comments: </td></tr>';
        for ($i = 0, $n = sizeof($vendor_products[$l]['vendor_orders_products']); $i < $n; $i++) {
          $product_attribs = '';
          if (isset ($vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes']) && (sizeof($vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes']) > 0)) {

            for ($j = 0, $k = sizeof($vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes']); $j < $k; $j++) {
              $product_attribs .= '&nbsp;&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes'][$j]['option'] . ': ' . $vendor_products[$l]['vendor_orders_products'][$i]['vendor_attributes'][$j]['value'] . '<br>';
            }
          }
          $email = $email . '<tr><td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['Pqty'] .
                            '</td><td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['Pname'] . '<br>&nbsp;&nbsp;<i>Option<br> ' . $product_attribs .
                            '</td><td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['PVprod_id'] .
                            '</td><td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['Pmodel'] .
                            '</td><td>&nbsp;' . $vendor_products[$l]['vendor_orders_products'][$i]['PVprod_price'] . '</td><td>' .
          $vendor_products[$l]['vendor_orders_products'][$i]['Pcomments'] . '</b></td></tr>';

        }
      }
      $email = $email . '</table><br><HR><br>';

      tep_mail ($the_name, $the_email, EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID, $email . '<br>', STORE_NAME, STORE_OWNER_EMAIL_ADDRESS);
      $vendor_order_sent = true;

      if ($debug == 'yes') {
        echo 'The $email(including headers:<br>Vendor Email Addy' . $the_email . '<br>Vendor Name' . $the_name . '<br>Vendor Contact' . $the_contact . '<br>Body--<br>' . $email . '<br>';
      }

      if ($vendor_order_sent == true) {
        tep_db_query ("update " . TABLE_ORDERS_SHIPPING . " set vendor_order_sent = 'yes' where orders_id = '" . (int) $oID . "'");
      }
    }

    return true;
  }

//MVS end



  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();
  
  // Check whether other modules are installed
  $has_create_order       = (FILENAME_CREATE_ORDER   !== 'FILENAME_CREATE_ORDER'   );
  $has_label_pdf          = (FILENAME_ORDERS_LABEL   !== 'FILENAME_ORDERS_LABEL'   );
  $has_order_editor       = (FILENAME_ORDERS_EDIT    !== 'FILENAME_ORDERS_EDIT'    ) ;
  $has_google_maps        = (FILENAME_GOOGLE_SITEMAP !== 'FILENAME_GOOGLE_SITEMAP' ) ;
  $google_map_directions  = (FILENAME_GOOGLE_MAP     !== 'FILENAME_GOOGLE_MAP'     );
  $has_pdf_invoice        = (FILENAME_PDF_INVOICE    !== 'FILENAME_PDF_INVOICE'    );

  $orders_statuses = array();
  $orders_status_array = array();
  $orders_status_query = tep_db_query("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . (int)$languages_id . "'");
  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
    $orders_statuses[] = array('id' => $orders_status['orders_status_id'],
                               'text' => $orders_status['orders_status_name']);
    $orders_status_array[$orders_status['orders_status_id']] = $orders_status['orders_status_name'];
  }
//BOF LIEBERMANN MULTIPLE INVOICES
  $invoices_descriptions = array(); //unused
  $invoices = array();  
  $invoices_query = tep_db_query("select invoice_id, invoice_filename, invoice_description from " . TABLE_INVOICES);  
  while ($invoice = tep_db_fetch_array($invoices_query)) {
    $invoices[] = array('id' => $invoice['invoice_id'],
                                'text' => $invoice['invoice_description']);
    $invoices_descriptions[] = array('id' => $invoice['invoice_id'],
                                'text' => $invoice['invoice_description']);            
  }      
  $packingslips_descriptions = array(); //unused
  $packingslips = array();  
  $packingslips_query = tep_db_query("select packingslip_id, packingslip_filename, packingslip_description from " . TABLE_PACKINGSLIPS);  
  while ($packingslip = tep_db_fetch_array($packingslips_query)) {
    $packingslips[] = array('id' => $packingslip['packingslip_id'],
                                'text' => $packingslip['packingslip_description']);
    $packingslips_descriptions[] = array('id' => $packingslip['packingslip_id'],
                                'text' => $packingslip['packingslip_description']);            
  }      
  // EOF LIEBERMANN MULTIPLE INVOICES


  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'update_order':
        $oID = tep_db_prepare_input($_GET['oID']);
        $status = tep_db_prepare_input($_POST['status']);
        $comments = tep_db_prepare_input($_POST['comments']);

        $order_updated = false;
        $check_status_query = tep_db_query("select customers_name, customers_email_address, orders_status, date_purchased from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
        $check_status = tep_db_fetch_array($check_status_query);

        if ( ($check_status['orders_status'] != $status) || tep_not_null($comments)) {
          tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . tep_db_input($status) . "', last_modified = now() where orders_id = '" . (int)$oID . "'");
//MVS start
          if (SELECT_VENDOR_EMAIL_WHEN == 'Admin' || SELECT_VENDOR_EMAIL_WHEN == 'Both') {

            if (isset ($status)) {
              $order_sent_query = tep_db_query("select vendor_order_sent, vendors_id from " . TABLE_ORDERS_SHIPPING . " where orders_id = '" . $oID . "'");
              while ($order_sent_data = tep_db_fetch_array($order_sent_query)) {
                $order_sent_ckeck = $order_sent_data['vendor_order_sent'];
                $vendors_id = $order_sent_data['vendors_id'];
                if ($order_sent_ckeck == 'no') {
                  $vendor_order_sent = false;
                  vendors_email($vendors_id, $oID, $status, $vendor_order_sent);
                } //if
              } //while
            } //isset
          } 
//MVS end
          $customer_notified = '0';
          if (isset($_POST['notify']) && ($_POST['notify'] == 'on')) {
            $notify_comments = '';
            if (isset($_POST['notify_comments']) && ($_POST['notify_comments'] == 'on')) {
              $notify_comments = sprintf(EMAIL_TEXT_COMMENTS_UPDATE, $comments) . "\n\n";
            }

            $email = STORE_NAME . "\n" . EMAIL_SEPARATOR . "\n" . EMAIL_TEXT_ORDER_NUMBER . ' ' . $oID . "\n" . EMAIL_TEXT_INVOICE_URL . ' ' . tep_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $oID, 'SSL') . "\n" . EMAIL_TEXT_DATE_ORDERED . ' ' . tep_date_long($check_status['date_purchased']) . "\n\n" . $notify_comments . sprintf(EMAIL_TEXT_STATUS_UPDATE, $orders_status_array[$status]);

            tep_mail($check_status['customers_name'], $check_status['customers_email_address'], EMAIL_TEXT_SUBJECT, $email, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

            $customer_notified = '1';
          }

          tep_db_query("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments) values ('" . (int)$oID . "', '" . tep_db_input($status) . "', now(), '" . tep_db_input($customer_notified) . "', '" . tep_db_input($comments)  . "')");

          $order_updated = true;
          
  // bof Google Maps
if ($has_google_maps) {  
if ($status == GOOGLE_MAP_ORDER_STATUS )     // wenn "Versendet"
{
        //require(DIR_WS_LANGUAGES . $language . '/report_googlemap.php');

        $oID = tep_db_prepare_input($_GET['oID']);

        $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
        $order_exists = true;
        if (!tep_db_num_rows($orders_query))
        {
                $order_exists = false;
                $messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
        }

        include(DIR_WS_CLASSES . 'order.php');
        $order = new order($oID);

        $url  = "http://maps.google.com/maps/geo?q=";
        $url .= $order->delivery['street_address'] . "," . $order->delivery['postcode'] . "," . $order->delivery['city'] . "," . $order->delivery['country'];
        $url .= "&output=csv&key=";
        $url .= GOOGLE_MAP_API_KEY;
        $url = str_replace (" ", "%20", $url);          // Leerzeichen -> %20
        $request = fopen($url,'r');
        $content = fread($request,100000);
        fclose($request);

        list($statuscode, $accuracy, $lat, $lng) = split(",", $content);


        if ($statuscode != 200)         //  errors occurred; the address was successfully parsedd.
        {
                // Versuch ohne Stra�e
                $url  = "http://maps.google.com/maps/geo?q=";
                $url .= $order->delivery['postcode'] . "," . $order->delivery['city'] . "," . $order->delivery['country'];
                $url .= "&output=csv&key=";
                $url .= GOOGLE_MAP_API_KEY;
                $url = str_replace (" ", "%20", $url);          // Leerzeichen -> %20
                $request = fopen($url,'r');
                $content = fread($request,100000);
                fclose($request);

                list($statuscode, $accuracy, $lat, $lng) = split(",", $content);
        }
        if ($statuscode == 200)         // No errors occurred; the address was successfully parsed.
        {
                $latlng_query_raw = "insert into orders_to_latlng (orders_id, lat, lng) values ('$oID','$lat','$lng')";
                $latlng_query = tep_db_query($latlng_query_raw);
        }
} // endif versendet
} // endif check voor contribution google maps
// eof Google Maps 
        }

        if ($order_updated == true) {
         $messageStack->add_session(SUCCESS_ORDER_UPDATED, 'success');
        } else {
          $messageStack->add_session(WARNING_ORDER_NOT_UPDATED, 'warning');
        }

        tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=edit'));
        break;
      case 'deleteconfirm':
        $oID = tep_db_prepare_input($_GET['oID']);

        tep_remove_order($oID, $_POST['restock']);

        tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action'))));
        break;
    }
  }

  if (($action == 'edit') && isset($_GET['oID'])) {
    $oID = tep_db_prepare_input($_GET['oID']);

    $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");
    $order_exists = true;
    if (!tep_db_num_rows($orders_query)) {
      $order_exists = false;
      $messageStack->add(sprintf(ERROR_ORDER_DOES_NOT_EXIST, $oID), 'error');
    }
  }

  include(DIR_WS_CLASSES . 'order.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE ; ?></title>
<!-- USE THIS FOR MONDSPARX_ADMIN <link rel="stylesheet" type="text/css" href="mindsparx_admin/template/<?php  echo ADMIN_TEMPLATE ?>/stylesheet.css"> -->
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css"> <!-- THIS ONE FOR STANDAARD ADMIN -->
<script language="javascript" src="includes/general.js"></script>
<script language="javascript"><!--
// LIEBERMANN MULTIPLE INVOICES
function ShowInvoice(file,formname) {
   iv=document.forms[formname].invoicename.options[document.forms[formname].invoicename.selectedIndex].value;
   window.open(file + '&oInvoiceID=' + iv,'invoice','')
return false;
}
function ShowPackingslip(file,formname) {
   iv=document.forms[formname].packingslipname.options[document.forms[formname].packingslipname.selectedIndex].value;
   window.open(file + '&oslipID=' + iv,'packingslip','')
return false;
}
//--></script>

</head>
<body>
<!-- header //-->
<?php
  require(DIR_WS_INCLUDES . 'header.php');
?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top" id="left"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="0" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top" id="main"><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
  if (($action == 'edit') && ($order_exists == true)) {
    $order = new order($oID);
//     <td class="pageHeading" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
                                

 <?php          
	   if ( CONFIG_ADMIN_SHOW_BUTTONS_PNG == 'true' ) { 
		   
$test_string2 =   '';

if ( $has_pdf_invoice ) {
   // use the pdf invoice
   if ( CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'PDF' or CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'BOTH' ) {
       $test_string2 .=  '<a href="' . tep_href_link(FILENAME_PDF_INVOICE,     'oID=' . $_GET['oID'])                   . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'invoice_large_pdf.png',     IMAGE_ORDERS_INVOICE) . '</a>                                 
                          <a href="' . tep_href_link(FILENAME_PDF_PACKINGSLIP, 'oID=' . $_GET['oID'])                   . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'packingslip_large_pdf.png', IMAGE_ORDERS_PACKINGSLIP) . '</a>  ' ;
   } 
   if ( CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'HTML' or CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'BOTH' ) {                         
      $test_string2 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_INVOICE,     'oID=' . $_GET['oID'])                   . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'invoice_large.png',     IMAGE_ORDERS_INVOICE) . '</a>                                 
                         <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID'])                   . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'packingslip_large.png', IMAGE_ORDERS_PACKINGSLIP) . '</a> ' ;          
   
   }
} else {

   $test_string2 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_INVOICE,     'oID=' . $_GET['oID'])                   . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'invoice_large.png',     IMAGE_ORDERS_INVOICE) . '</a>                                 
                      <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID'])                   . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'packingslip_large.png', IMAGE_ORDERS_PACKINGSLIP) . '</a> ' ;          
}                    
if ( $has_order_editor ) { 
   $test_string2 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_EDIT,        'oID=' . $_GET['oID'])                    . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'edit_large.png',        IMAGE_EDIT) . '</a> ' ;          
}
if ( $has_label_pdf ) { 
   $test_string2 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_LABEL,       'oID=' . $_GET['oID'])          . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'print_label_large.png', IMAGE_ORDERS_LABEL) . '</a> ' ;          
}

if ( $google_map_directions ) { 
   $test_string2 .=  '<a href="' . tep_href_link(FILENAME_GOOGLE_MAP,         'oID=' . $_GET['oID'])          . '" TARGET="_blank">' .          tep_image(DIR_WS_IMAGES . 'google_map_delivery_large.png', IMAGE_GOOGLE_DIRECTIONS) . '</a> ' ;          
}

$test_string2 .=  '<a href="' . tep_href_link(FILENAME_ORDERS,             tep_get_all_get_params(array('action'))) . '"                >' . tep_image(DIR_WS_IMAGES . 'back_large.png',        IMAGE_BACK) . '</a>';
		   
?>      
            <td class="pageHeading" align="right"><?php echo $test_string2 ; ?></td>		      
   
<?php     
	         
	   } else {
// bof order list improved 
$test_string2 =   '';

if ( $has_pdf_invoice ) {
   // use the pdf invoice
   if ( CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'PDF' or CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'BOTH' ) {
      $test_string2 .=  '<a href="' . tep_href_link(FILENAME_PDF_INVOICE,     'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_invoice_pdf.gif', IMAGE_ORDERS_INVOICE) . '</a>                                              
                         <a href="' . tep_href_link(FILENAME_PDF_PACKINGSLIP, 'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_packingslip_pdf.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a> ' ;
   }
   if ( CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'HTML' or CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'BOTH' ) {                      
      $test_string2 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_INVOICE,     'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a>                                                         
                         <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a> ' ;          
   }
} else {

    $test_string2 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_INVOICE,     'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a>                                                         
                       <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a> ' ;          
}                   
                    
if ( $has_order_editor ) { 
   $test_string2 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_EDIT,         'oID=' . $_GET['oID']) . '">'                 . tep_image_button('button_edit_orders.gif', IMAGE_EDIT) . '</a>' ;          
}
if ( $has_label_pdf ) { 
   $test_string2 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_LABEL,       'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_label.gif', IMAGE_ORDERS_LABEL) . '</a>' ;          
}

if ( $google_map_directions ) { 
   $test_string2 .=  '<a href="' . tep_href_link(FILENAME_GOOGLE_MAP,         'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_google_directions.gif', IMAGE_GOOGLE_DIRECTIONS) . '</a>' ;          
}

$test_string2 .=  '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>';
		   
?>
          <td class="pageHeading" align="right"><?php echo  $test_string2 ; ?></td>		   
<?php
        } // endif show buttons png
?>      
          </tr>       
        </table></td>
      </tr>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan="3"><?php echo tep_draw_separator(); ?></td>
          </tr>
          <tr>
            <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><b><?php echo ENTRY_CUSTOMER; ?></b></td>
                <td class="main"><?php echo tep_address_format($order->customer['format_id'], $order->customer, 1, '', '<br />'); ?></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_PIVA; ?></b></td>
                <td class="main"><?php echo $order->billing['piva']; ?></td>
              </tr>
							<tr>
                <td class="main"><b><?php echo ENTRY_CF; ?></b></td>
                <td class="main"><?php echo $order->billing['cf']; ?></td>
              </tr>			
              <tr>
                <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_TELEPHONE_NUMBER; ?></b></td>
                <td class="main"><?php echo $order->customer['telephone']; ?></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_EMAIL_ADDRESS; ?></b></td>
                <td class="main"><?php echo '<a href="mailto:' . $order->customer['email_address'] . '"><u>' . $order->customer['email_address'] . '</u></a>'; ?></td>
              </tr>
            </table></td>
            <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><b><?php echo ENTRY_SHIPPING_ADDRESS; ?></b></td>
                <td class="main"><?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br />'); ?></td>
              </tr>
            </table></td>
            <td valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main" valign="top"><b><?php echo ENTRY_BILLING_ADDRESS; ?></b></td>
                <td class="main"><?php echo tep_address_format($order->billing['format_id'], $order->billing, 1, '', '<br />'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><b><?php echo ENTRY_PAYMENT_METHOD; ?></b></td>
            <td class="main"><?php echo $order->info['payment_method']; ?></td>
          </tr>
<?php
    if (tep_not_null($order->info['cc_type']) || tep_not_null($order->info['cc_owner']) || tep_not_null($order->info['cc_number'])) {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_TYPE; ?></td>
            <td class="main"><?php echo $order->info['cc_type']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_OWNER; ?></td>
            <td class="main"><?php echo $order->info['cc_owner']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_NUMBER; ?></td>
            <td class="main"><?php echo $order->info['cc_number']; ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo ENTRY_CREDIT_CARD_EXPIRES; ?></td>
            <td class="main"><?php echo $order->info['cc_expires']; ?></td>
          </tr>
<?php
    }
?><?php //MVS start ?>
    <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
    $orders_vendors_data_query = tep_db_query("select distinct ov.orders_id, 
                                                               ov.vendors_id, 
                                                               ov.vendor_order_sent, 
                                                               v.vendors_name 
                                               from " . TABLE_ORDERS_SHIPPING . " ov, 
                                                    " . TABLE_VENDORS . " v 
                                               where v.vendors_id=ov.vendors_id 
                                                 and orders_id='" . (int) $oID . "' 
                                               group by vendors_id
                                            ");
    while ($orders_vendors_data = tep_db_fetch_array($orders_vendors_data_query)) {
      echo '<tr class="dataTableRow"><td class="dataTableContent" valign="top" align="left">Order Sent to ' . $orders_vendors_data['vendors_name'] . ':<b> ' . $orders_vendors_data['vendor_order_sent'] . '</b><br></td>';
    }
    echo '</tr>';
//MVS end
?>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
      	<?php
// MVS start
    //   echo '<br> the $order->orders_shipping_id : ' . $order->orders_shipping_id;
    if (tep_not_null($order->orders_shipping_id)) {
      require_once ('vendor_order_info.php');
    } else {
// MVS end
?>
   
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr class="dataTableHeadingRow">
            <td class="dataTableHeadingContent" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
            <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></td>
            <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></td>
          </tr>
<?php
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      echo '          <tr class="dataTableRow">' . "\n" .
           '            <td class="dataTableContent" valign="top" align="right">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
           '            <td class="dataTableContent" valign="top">' . $order->products[$i]['name'];

      if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
        for ($j = 0, $k = sizeof($order->products[$i]['attributes']); $j < $k; $j++) {
          echo '<br /><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
          if ($order->products[$i]['attributes'][$j]['price'] != '0') echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
          echo '</i></small></nobr>';
        }
      }

      echo '            </td>' . "\n" .
           '            <td class="dataTableContent" valign="top">' . $order->products[$i]['model'] . '</td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
           '            <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n";
      echo '          </tr>' . "\n";
    }
?>
          <tr>
            <td align="right" colspan="8"><table border="0" cellspacing="0" cellpadding="2">
<?php
// MVS
    }
    for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
      echo '              <tr>' . "\n" .
           '                <td align="right" class="smallText">' . $order->totals[$i]['title'] . '</td>' . "\n" .
           '                <td align="right" class="smallText">' . $order->totals[$i]['text'] . '</td>' . "\n" .
           '              </tr>' . "\n";
    }
?>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><table border="1" cellspacing="0" cellpadding="5">
          <tr>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_DATE_ADDED; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_CUSTOMER_NOTIFIED; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_STATUS; ?></b></td>
            <td class="smallText" align="center"><b><?php echo TABLE_HEADING_COMMENTS; ?></b></td>
          </tr>
<?php
    $orders_history_query = tep_db_query("select orders_status_id, date_added, customer_notified, comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . tep_db_input($oID) . "' order by date_added");
    if (tep_db_num_rows($orders_history_query)) {
      while ($orders_history = tep_db_fetch_array($orders_history_query)) {
        echo '          <tr>' . "\n" .
             '            <td class="smallText" align="center">' . tep_datetime_short($orders_history['date_added']) . '</td>' . "\n" .
             '            <td class="smallText" align="center">';
        if ($orders_history['customer_notified'] == '1') {
          echo tep_image(DIR_WS_ICONS . 'tick.gif', ICON_TICK) . "</td>\n";
        } else {
          echo tep_image(DIR_WS_ICONS . 'cross.gif', ICON_CROSS) . "</td>\n";
        }
        echo '            <td class="smallText">' . $orders_status_array[$orders_history['orders_status_id']] . '</td>' . "\n" .
             '            <td class="smallText">' . nl2br(tep_db_output($orders_history['comments'])) . '&nbsp;</td>' . "\n" .
             '          </tr>' . "\n";
      }
    } else {
        echo '          <tr>' . "\n" .
             '            <td class="smallText" colspan="5">' . TEXT_NO_ORDER_HISTORY . '</td>' . "\n" .
             '          </tr>' . "\n";
    }
?>
        </table></td>
      </tr>
      <tr>
        <td class="main"><br /><b><?php echo TABLE_HEADING_COMMENTS; ?></b></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
      <tr><?php echo tep_draw_form('status', FILENAME_ORDERS, tep_get_all_get_params(array('action')) . 'action=update_order'); ?>
        <td class="main"><?php echo tep_draw_textarea_field('comments', 'soft', '60', '5'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td><table border="0" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><b><?php echo ENTRY_STATUS; ?></b> <?php echo tep_draw_pull_down_menu('status', $orders_statuses, $order->info['orders_status']); ?></td>
              </tr>
              <tr>
                <td class="main"><b><?php echo ENTRY_NOTIFY_CUSTOMER; ?></b> <?php echo tep_draw_checkbox_field('notify', '', true); ?></td>
                <td class="main"><b><?php echo ENTRY_NOTIFY_COMMENTS; ?></b> <?php echo tep_draw_checkbox_field('notify_comments', '', true); ?></td>
              </tr>
            </table></td> 
<?php          
	   if ( CONFIG_ADMIN_SHOW_BUTTONS_PNG == 'true' ) { 
?>                  
            <td valign="top"><?php echo tep_image_submit('button_send_user_large.png', IMAGE_UPDATE); ?></td>                                   
<?php  
   	   } else {		   
?>           


                  <td valign="top"><?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?></td>   
<?php
        } // endif show buttons png 
?>
          </tr>
        </table></td>
     </tr> <!-- LIEBERMANN MULTIPLE INVOICES BOF   //-->
      <?php
        $czId = MULTIPLE_INVOICE_ID_DEFAULT;
        $country_found=false;
        $zone_found=false;
        $text = TEXT_NOTHING_FOUND;
        $cId_query = tep_db_query("SELECT C.countries_name, I.invoice_id as invoice_id FROM " . TABLE_COUNTRIES . " AS C INNER JOIN (" . TABLE_INVOICES . " AS I INNER JOIN " . TABLE_INVOICE_TO_COUNTRIES . " AS IC ON I.invoice_id = IC.invoice_id) ON C.countries_id = IC.countries_id WHERE (((C.countries_name)='" . $order->billing['country'] . "'))");                
        while ($cId_array = tep_db_fetch_array($cId_query)) {
           $cId = $cId_array['invoice_id'];              
           $country_found=true;
        }      
        
        $zId_query = tep_db_query("SELECT G.geo_zone_name, G.geo_zone_id, C.countries_name, IG.invoice_id as invoice_id FROM ((" . TABLE_ZONES_TO_GEO_ZONES . " AS ZG INNER JOIN " . TABLE_GEO_ZONES . " AS G ON ZG.geo_zone_id = G.geo_zone_id) INNER JOIN " . TABLE_COUNTRIES . " AS C ON ZG.zone_country_id = C.countries_id) INNER JOIN " . TABLE_INVOICE_TO_GEO_ZONES . " AS IG ON G.geo_zone_id = IG.geo_zone_id WHERE (((C.countries_name)='" . $order->billing['country'] . "'))");        
        while ($zId_array = tep_db_fetch_array($zId_query)) {
           $zId = $zId_array['invoice_id'];              
           $zone_found=true;           
        }      
        if (($country_found) && ($zone_found))
        {
                if (MULTIPLE_INVOICE_PRIORITY == 1) 
                {
        	  //Use zone, ignore country
        	  $czId = $zId;
        	  $text = TEXT_ZONE_FOUND;
                }
                else
        	{
                  $czId = $cId;
                  $text = TEXT_COUNTRY_FOUND;
        	}
        }	
        else if ($country_found)
        {
        	$czId = $cId;
        	$text = TEXT_COUNTRY_FOUND;
        }	
        else if ($zone_found)
        {
        	$czId = $zId;
        	$text = TEXT_ZONE_FOUND;
        }	
        //------------------
        $sczId = MULTIPLE_PACKINGSLIP_ID_DEFAULT;
        $scountry_found=false;
        $szone_found=false;
        $stext = TEXT_NOTHING_FOUND;
        
        $scId_query = tep_db_query("SELECT C.countries_name, I.packingslip_id as packingslip_id FROM " . TABLE_COUNTRIES . " AS C INNER JOIN (" . TABLE_PACKINGSLIPS . " AS I INNER JOIN " . TABLE_PACKINGSLIP_TO_COUNTRIES . " AS IC ON I.packingslip_id = IC.packingslip_id) ON C.countries_id = IC.countries_id WHERE (((C.countries_name)='" . $order->delivery['country'] . "'))");        
        while ($scId_array = tep_db_fetch_array($scId_query)) {
           $scId = $scId_array['packingslip_id'];              
           $scountry_found=true;
        }      
        
        $szId_query = tep_db_query("SELECT G.geo_zone_name, G.geo_zone_id, C.countries_name, IG.packingslip_id as packingslip_id FROM ((" . TABLE_ZONES_TO_GEO_ZONES . " AS ZG INNER JOIN " . TABLE_GEO_ZONES . " AS G ON ZG.geo_zone_id = G.geo_zone_id) INNER JOIN " . TABLE_COUNTRIES . " AS C ON ZG.zone_country_id = C.countries_id) INNER JOIN " . TABLE_PACKINGSLIP_TO_GEO_ZONES . " AS IG ON G.geo_zone_id = IG.geo_zone_id WHERE (((C.countries_name)='" . $order->delivery['country'] . "'))");        
        while ($szId_array = tep_db_fetch_array($szId_query)) {
           $szId = $szId_array['packingslip_id'];              
           $szone_found=true;           
        }      
        if (($scountry_found) && ($szone_found))
        {
                if (MULTIPLE_PACKINGSLIP_PRIORITY == 1) 
                {
        	  //Use zone, ignore country
        	  $sczId = $szId;
        	  $stext = TEXT_ZONE_FOUND;
                }
                else
        	{
                  $sczId = $scId;
                  $stext = TEXT_COUNTRY_FOUND;
        	}
        }	
        else if ($scountry_found)
        {
        	$sczId = $scId;
        	$stext = TEXT_COUNTRY_FOUND;
        }	
        else if ($szone_found)
        {
        	$sczId = $szId;
        	$stext = TEXT_ZONE_FOUND;
        }	
      ?>       
      <tr>
        <td colspan="2" align="right">
          <table border="0" cellspacing="0" cellpadding="2">
           <tr><td>
                  <?php echo '<a href="' . tep_href_link('invoice_sui.php', 'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a> <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a> <a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?>

           </td>
           </tr>
           <tr><td>
         </td></tr></table>
        </td>
      </form></tr>
      <!-- LIEBERMANN MULTIPLE INVOICES EOF   //-->

      <tr>
         <tr>
<?php          
	   if ( CONFIG_ADMIN_SHOW_BUTTONS_PNG == 'true' ) { 
		   
          $test_string3 =   '';

          if ( $has_pdf_invoice ) {
               // use the pdf invoice
               if ( CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'PDF' or CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'BOTH' ) {
                 $test_string3 .=  '<a href="' . tep_href_link(FILENAME_PDF_INVOICE,     'oID=' . $_GET['oID'])                   . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'invoice_large_pdf.png',     IMAGE_ORDERS_INVOICE) . '</a>                                 
                                    <a href="' . tep_href_link(FILENAME_PDF_PACKINGSLIP, 'oID=' . $_GET['oID'])                   . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'packingslip_large_pdf.png', IMAGE_ORDERS_PACKINGSLIP) . '</a>  ' ;
               }                                    
               if ( CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'HTML' or CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'BOTH' ) {               
                  $test_string3 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_INVOICE,     'oID=' . $_GET['oID'])                   . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'invoice_large.png',     IMAGE_ORDERS_INVOICE) . '</a>                                 
                                     <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID'])                   . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'packingslip_large.png', IMAGE_ORDERS_PACKINGSLIP) . '</a> ' ;          
               }                                     	               
          } else {

               $test_string3 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_INVOICE,     'oID=' . $_GET['oID'])                   . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'invoice_large.png',     IMAGE_ORDERS_INVOICE) . '</a>                                 
                                  <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID'])                   . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'packingslip_large.png', IMAGE_ORDERS_PACKINGSLIP) . '</a> ' ;          
          }                                  
                    
          if ( $has_order_editor ) { 
              $test_string3 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_EDIT,        'oID=' . $_GET['oID'])                    . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'edit_large.png',        IMAGE_EDIT) . '</a> ' ;          
          }
          if ( $has_label_pdf ) { 
              $test_string3 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_LABEL,       'oID=' . $_GET['oID'])          . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'print_label_large.png', IMAGE_ORDERS_LABEL) . '</a> ' ;          
          }
          if ( $google_map_directions ) { 
              $test_string3 .=  '<a href="' . tep_href_link(FILENAME_GOOGLE_MAP,         'oID=' . $_GET['oID'])          . '" TARGET="_blank">' .          tep_image(DIR_WS_IMAGES . 'google_map_delivery_large.png', IMAGE_GOOGLE_DIRECTIONS) . '</a> ' ;          
          }

          $test_string3 .=  '<a href="' . tep_href_link(FILENAME_ORDERS,             tep_get_all_get_params(array('action'))) . '"                >' . tep_image(DIR_WS_IMAGES . 'back_large.png',        IMAGE_BACK) . '</a>';
		   
?>      
            <td class="pageHeading" align="right"><?php echo $test_string3 ; ?></td>		      
   
<?php     
	         
	   } else {
          // bof order list improved 
          $test_string3 =   '';

          if ( $has_pdf_invoice ) {
               // use the pdf invoice
               if ( CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'PDF' or CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'BOTH' ) {             
                  $test_string3 .=  '<a href="' . tep_href_link(FILENAME_PDF_INVOICE,     'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_invoice_pdf.gif', IMAGE_ORDERS_INVOICE) . '</a>                                              
                                     <a href="' . tep_href_link(FILENAME_PDF_PACKINGSLIP, 'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_packingslip_pdf.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a> ' ;
               }                                     
               if ( CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'HTML' or CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'BOTH' ) {                
                  $test_string3 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_INVOICE,     'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a>                                                         
                                     <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a> ' ;          	               
               }
          } else {
               $test_string3 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_INVOICE,     'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a>                                                         
                                  <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a> ' ;          
          }                                  
                    
          if ( $has_order_editor ) { 
               $test_string3 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_EDIT,         'oID=' . $_GET['oID']) . '">'                 . tep_image_button('button_edit_orders.gif', IMAGE_EDIT) . '</a>' ;          
          }        
          if ( $has_label_pdf ) { 
               $test_string3 .=  '<a href="' . tep_href_link(FILENAME_ORDERS_LABEL,       'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_label.gif', IMAGE_ORDERS_LABEL) . '</a>' ;          
          }
          if ( $google_map_directions ) { 
               $test_string3 .=  '<a href="' . tep_href_link(FILENAME_GOOGLE_MAP,         'oID=' . $_GET['oID']) . '" TARGET="_blank">' . tep_image_button('button_google_directions.gif', IMAGE_GOOGLE_DIRECTIONS) . '</a>' ;          
          }

          $test_string3 .=  '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('action'))) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>';
		   
?>
          <td class="pageHeading" align="right"><?php echo  $test_string3 ; ?></td>		   
<?php
        } // endif show buttons png		   

?>               
          </tr>
    </tr>
<?php
  } else {
?>
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
           
            <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr><?php echo tep_draw_form('orders', FILENAME_ORDERS, '', 'get'); ?>
             <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
       <!-- bof orders list improved -->
             <?php if ($has_create_order) { ?>
                 <td class="pageHeading"><?php echo IMAGE_CREATE_ORDER . '<a href="' . tep_href_link(FILENAME_CREATE_ORDER ) . '">' . tep_image(DIR_WS_IMAGES . 'create_order.png', IMAGE_CREATE_ORDER) . '</a>'; ?></td>
             <?php } ?>                              
             
               <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>              
                <td class="smallText" align="right"><?php echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('oID', '', 'size="12"') . tep_draw_hidden_field('action', 'edit'); ?></td>
              </form></tr>
              <tr><?php echo tep_draw_form('status', FILENAME_ORDERS, '', 'get'); ?>
                <td class="smallText" align="left"><?php echo HEADING_TITLE_STATUS . ' ' . tep_draw_pull_down_menu('status', array_merge(array(array('id' => '', 'text' => TEXT_ALL_ORDERS)), $orders_statuses), '', 'onChange="this.form.submit();"'); ?></td>
                <td class="smallText" align="right"><?php echo tep_draw_form('search', FILENAME_ORDERS, '', 'get') . "\n"; ?><?php echo HEADING_TITLE_SEARCH_ORDERS_NAME . ' ' . tep_draw_input_field('search') . "\n"; ?>                
              </form></tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
<?php
/*
Function to print table headers based on current sort pattern
$name = Full name of header, usually defined in language files
$id = sort word used in URL
$current_dir = current sort direction (ASC or DESC)
*/
function print_sort( $name, $id, $default_sort ) {
   global $orderby, $sort;

   if( isset( $orderby ) && ( $orderby == $id ) ) {
      if( $sort == 'ASC' ) {
         $to_sort = 'DESC';
      } else {
         $to_sort = 'ASC';
      }
   } else {
      $to_sort = $default_sort;
   }
   $return = '<a href="' . tep_href_link(FILENAME_ORDERS, 'orderby=' . $id . '&amp;sort='. $to_sort) .
   '" class="headerLink">' . $name . '</a>';
   if( $orderby == $id ) {
      $return .= '&nbsp;<img src="images/arrow_' . ( ( $to_sort == 'DESC' ) ? 'down' : 'up' ) .
      '.png" width="10" height="13" border="0" alt="" />';
   }
   return $return;
}
?>      
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                 <td class="dataTableHeadingContent" align="left"><?php echo              TABLE_HEADING_ACTION; ?>&nbsp;</td>
                 <?php // ====> BOF: ORDERS AT-A-GLANCE <==== ?>
			     <td class="dataTableHeadingContent"><?php echo print_sort( TABLE_HEADING_ORDERNUM, 'order_numbers', 'DESC' ); ?></td>
			     <?php // ====> BEOF: ORDERS AT-A-GLANCE <==== ?>
			    <td class="dataTableHeadingContent"><?php echo                print_sort( TABLE_HEADING_CUSTOMERS,      'customers',      'DESC') ; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo  print_sort( TABLE_HEADING_ORDER_TOTAL,    'order_totals',   'ASC') ; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo print_sort( TABLE_HEADING_DATE_PURCHASED, 'date_purchased', 'ASC') ; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo  print_sort( TABLE_HEADING_STATUS,         'order_status',   'ASC' ); ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_INVOICE_NUM; ?>&nbsp;</td>

                 <?php if ( CONFIG_ADMIN_SHOW_BUTTONS_ORDERLIST == 'true' ) { ?>  
                           <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
                           
                 <?php } ?>                
              </tr>
<?php
$search = '';

// Setup column sorting
if($orderby == 'order_numbers') {
   $db_orderby = 'o.orders_id';
} elseif($orderby == 'customers') {
   $db_orderby = 'o.customers_name ' ;	
} elseif($orderby == 'order_totals') {
   $db_orderby = 'order_total';
} elseif($orderby == 'date_purchased') {
   $db_orderby = 'o.date_purchased';
} elseif($orderby == 'order_status') {
   $db_orderby = 's.orders_status_name';
} else {
   $db_orderby = 'o.orders_id';
}
if(!$sort) $sort = 'DESC';

    if (isset($_GET['search']) && tep_not_null($_GET['search'])) {
      $keywords = tep_db_input(tep_db_prepare_input($_GET['search']));
      $search = "where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' and o.customers_name like '%" . $keywords . "%' ";
    } else {
	  $search = "where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' ";  
    }
    
    if (isset($_GET['cID'])) {
	    // search on order id
      $cID = tep_db_prepare_input($_GET['cID']);
//      $orders_query_raw = "select o.orders_id, o.customers_name, o.customers_id, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$cID . "' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' . order by ". $db_orderby . " " . $sort ;
      $orders_query_raw = "select o.orders_id, o.customers_name, o.customers_id, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, o.delivery_country, o.billing_country, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.customers_id = '" . (int)$cID . "' and o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and ot.class = 'ot_total' order by orders_id DESC";

    } elseif (isset($_GET['status']) && is_numeric($_GET['status']) && ($_GET['status'] > 0)) {
	    // search on status of order
      $status = tep_db_prepare_input($_GET['status']);
      $orders_query_raw = "select o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, o.delivery_country, o.billing_country, s.orders_status_name, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s where o.orders_status = s.orders_status_id and s.language_id = '" . (int)$languages_id . "' and s.orders_status_id = '" . (int)$status . "' and ot.class = 'ot_total' order by ". $db_orderby  . " " . $sort ;
    } else {
	    // search on orders statur and customer name ( if search field is filled )
           $orders_query_raw = "select o.orders_id, o.customers_name, o.payment_method, o.date_purchased, o.last_modified, o.currency, o.currency_value, o.delivery_country, o.billing_country, s.orders_status_name, ot.text as order_total, o.customers_email_address from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id), " . TABLE_ORDERS_STATUS . " s 
            ". $search ."  order by ". $db_orderby . " " . $sort ;
    }
    $orders_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $orders_query_raw, $orders_query_numrows);
    $orders_query = tep_db_query($orders_query_raw);
    while ($orders = tep_db_fetch_array($orders_query)) {
    if ((!isset($_GET['oID']) || (isset($_GET['oID']) && ($_GET['oID'] == $orders['orders_id']))) && !isset($oInfo)) {
        $oInfo = new objectInfo($orders);
      }

      if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id)) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id']) . '\'">' . "\n";
      }
// bof order list improved 
$test_string =   '<a href="' . tep_href_link(FILENAME_ORDERS,             tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders[ 'orders_id' ] . '&action=edit'  )  . '">'                . tep_image(DIR_WS_IMAGES . 'icon_info.png',   IMAGE_ICON_INFO)          . '</a> |
                  <a href="' . tep_href_link(FILENAME_ORDERS,             tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders[ 'orders_id' ] . '&action=delete') . '">'                 . tep_image(DIR_WS_IMAGES . 'delete.png',      IMAGE_DELETE)             . '</a> |   ';

if ( $has_pdf_invoice ) {
   // use the pdf invoice
   if ( CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'PDF' or CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'BOTH' ) { 
      $test_string .=  '<a href="'  . tep_href_link(FILENAME_PDF_INVOICE,     'oID=' . $orders[ 'orders_id' ]) . '" TARGET="_blank">' .                                                          tep_image(DIR_WS_IMAGES . 'invoice_pdf.png',     IMAGE_ORDERS_INVOICE)     . '</a> | 
                        <a href="' . tep_href_link(FILENAME_PDF_PAKINGSLIP,  'oID=' . $orders[ 'orders_id' ]) . '" TARGET="_blank">' .                                                          tep_image(DIR_WS_IMAGES . 'packingslip_pdf.png',     IMAGE_ORDERS_INVOICE)     . '</a> | ' ;          
   }
   if ( CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'HTML' or CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'BOTH' ) { 
      $test_string .=  '<a href="' . tep_href_link(FILENAME_ORDERS_INVOICE,     'oID=' . $orders[ 'orders_id' ]) . '" TARGET="_blank">' .                                                                     tep_image(DIR_WS_IMAGES . 'invoice.png',     IMAGE_ORDERS_INVOICE)     . '</a> |
                       <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $orders[ 'orders_id' ]) . '" TARGET="_blank">' .                                                                     tep_image(DIR_WS_IMAGES . 'packingslip.png', IMAGE_ORDERS_PACKINGSLIP) . '</a> | ' ;             
   }
                         
} else {
   $test_string .=  '<a href="' . tep_href_link(FILENAME_ORDERS_INVOICE,     'oID=' . $orders[ 'orders_id' ]) . '" TARGET="_blank">' .                                                                     tep_image(DIR_WS_IMAGES . 'invoice.png',     IMAGE_ORDERS_INVOICE)     . '</a> |
                     <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $orders[ 'orders_id' ]) . '" TARGET="_blank">' .                                                                     tep_image(DIR_WS_IMAGES . 'packingslip.png', IMAGE_ORDERS_PACKINGSLIP) . '</a> | ' ;          
}                     
                    
if ( $has_order_editor ) { 
   $test_string .=  '<a href="' . tep_href_link(FILENAME_ORDERS_EDIT,        tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders[ 'orders_id' ]                   ) . '" TARGET="_blank">' . tep_image(DIR_WS_IMAGES . 'edit.png',        IMAGE_EDIT)               . '</a> | ' ;          
}
if ( $has_label_pdf ) { 
   $test_string .=  '<a href="' . tep_href_link(FILENAME_ORDERS_LABEL,       'oID=' . $orders[ 'orders_id' ]) . '" TARGET="_blank">' .                                                                     tep_image(DIR_WS_IMAGES . 'print_label.png', IMAGE_ORDERS_LABEL)       . '</a> | ' ;          
}

$test_string .=  '<a href="' . tep_href_link(FILENAME_MAIL,               'selected_box=tools&customer=' . $orders[ 'customers_email_address' ] ) . '">' .                                              tep_image(DIR_WS_IMAGES . 'email_send.png',  IMAGE_EMAIL)              . '</a>';

?>

   <td>
    <?php echo  $test_string ?>
   </td>  

<!-- eof order list improved --> 
<?php // ====> BOF: ORDERS AND COMMENTS AT-A-GLANCE <==== ?>
                <td class="dataTableContent" align="left"><?php echo '<b>' . $orders['orders_id'] . '</b>'; 

$products = "";
$products_query = tep_db_query("SELECT orders_products_id, products_name, products_quantity FROM " . TABLE_ORDERS_PRODUCTS . " WHERE orders_id = '" . tep_db_input($orders['orders_id']) . "' ");
while($products_rows = tep_db_fetch_array($products_query))
{
$products .= ($products_rows["products_quantity"]) . "x " . (tep_html_noquote($products_rows["products_name"])) . "<br>";
$result_attributes = tep_db_query("SELECT products_options, products_options_values FROM " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " WHERE orders_id = '" . tep_db_input($orders['orders_id']). "' AND orders_products_id = '" . $products_rows["orders_products_id"] . "' ORDER BY products_options");
while($row_attributes = tep_db_fetch_array($result_attributes))
{
$products .=" - " . (tep_html_noquote($row_attributes["products_options"])) . ": " 
. (tep_html_noquote($row_attributes["products_options_values"])) . "<br>";
}
}
   ?>	
        <img src="images/icons/comment2.gif" onMouseOver="ddrivetip('<?php echo '' . $products . ''; ?>', 'white', 300);" onMouseOut="hideddrivetip();" align="top" border="0">
	</td>
  <?php  // ====> EOF: ORDERS AT-A-GLANCE <==== ?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $orders['orders_id'] . '&action=edit') . '">' . '</a>&nbsp;' . $orders['customers_name']; 
				
				// GET COMMENT(S)
				$clean_comments = "";
				$orders_history_query = tep_db_query("SELECT comments FROM " . TABLE_ORDERS_STATUS_HISTORY . " WHERE orders_id = '" . tep_db_input($orders['orders_id']) . "' ORDER BY date_added");
				while($orders_comments = tep_db_fetch_array($orders_history_query)) {
    
   					// Append each existing comment in succession 
					if (tep_not_null($orders_comments['comments'])) { 
					$clean_comments .= tep_html_noquote($orders_comments['comments']) . '<br>';
					} //end if
				} //end while
                            
                if (tep_not_null($clean_comments)) {
                  ?>	
					
                  <img src="images/icons/comment2.gif" onMouseover="ddrivetip('<?php echo '' . $clean_comments . ''; ?>', 'white', 300)"; onMouseout="hideddrivetip()" align="top" border="0">   
              	       
                  <?php 
                } // endif
                ?></td>
<!-- eof comments at a glance -->
              
                <td class="dataTableContent" align="right"><?php echo strip_tags($orders['order_total']); ?></td>
                <td class="dataTableContent" align="center"><?php echo tep_datetime_short($orders['date_purchased']); ?></td>
                <td class="dataTableContent" align="right"><?php echo $orders['orders_status_name']; ?></td>
          <?php if ( CONFIG_ADMIN_SHOW_BUTTONS_ORDERLIST == 'true' ) { ?>                 
                <?php
                
                   $invoice_id_query = tep_db_query("select invoice_id, num_invoice from " . TABLE_INVOICE . " where orders_id = '" . $orders['orders_id'] . "'");
    $invoice_id = tep_db_fetch_array($invoice_id_query);
?>
              
                <td class="dataTableContent" align="right"><?php echo $invoice_id['num_invoice'] ?>&nbsp;</td>
                <?php ?>
                <td class="dataTableContent" align="right"><?php echo " " ?>&nbsp;</td>
                <?php ?>
                
                
                <td class="dataTableContent" align="right"><?php if (isset($oInfo) && is_object($oInfo) && ($orders['orders_id'] == $oInfo->orders_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID')) . 'oID=' . $orders['orders_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>  

          <?php } ?>       
              </tr>
<?php
    }
?>
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $orders_split->display_count($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                    <td class="smallText" align="right"><?php echo $orders_split->display_links($orders_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], tep_get_all_get_params(array('page', 'oID', 'action'))); ?></td>
                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_ORDER . '</b>');

      $contents = array('form' => tep_draw_form('orders', FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO . '<br /><br /><b>' . $cInfo->customers_firstname . ' ' . $cInfo->customers_lastname . '</b>');
      $contents[] = array('text' => '<br />' . tep_draw_checkbox_field('restock') . ' ' . TEXT_INFO_RESTOCK_PRODUCT_QUANTITY);
      $contents[] = array('align' => 'center', 'text' => '<br />' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
     //MVS start
          $orders_vendors_data_query = tep_db_query("select distinct ov.orders_id, ov.vendors_id, ov.vendor_order_sent, v.vendors_name from " . TABLE_ORDERS_SHIPPING . " ov, " . TABLE_VENDORS . " v where v.vendors_id=ov.vendors_id and orders_id='" . $oInfo->orders_id . "' group by vendors_id");
          while ($orders_vendors_data = tep_db_fetch_array($orders_vendors_data_query)) {
            $contents[] = array ('text' => VENDOR_ORDER_SENT . '<b>' . $orders_vendors_data['vendors_name'] . '</b>:<b> ' . $orders_vendors_data['vendor_order_sent'] . '</b><br>');
          }
//MVS end
	 
	  break;
    default:
      if ( CONFIG_ADMIN_SHOW_BUTTONS_ORDERLIST == 'true' ) {
	   if (isset($oInfo) && is_object($oInfo)) {
       // BOF LIEBERMANN Multiple Invoices  
        $czId = MULTIPLE_INVOICE_ID_DEFAULT;
        $country_found=false;
        $zone_found=false;
        $text = TEXT_NOTHING_FOUND;
        // replace billing_country with delivery_country if you like
        $cId_query = tep_db_query("SELECT C.countries_name, I.invoice_id as invoice_id FROM " . TABLE_COUNTRIES . " AS C INNER JOIN (" . TABLE_INVOICES . " AS I INNER JOIN " . TABLE_INVOICE_TO_COUNTRIES . " AS IC ON I.invoice_id = IC.invoice_id) ON C.countries_id = IC.countries_id WHERE (((C.countries_name)='" . $oInfo->billing_country . "'))");                
        while ($cId_array = tep_db_fetch_array($cId_query)) {
           $cId = $cId_array['invoice_id'];              
           $country_found=true;
        }      
        $zId_query = tep_db_query("SELECT G.geo_zone_name, G.geo_zone_id, C.countries_name, IG.invoice_id as invoice_id FROM ((" . TABLE_ZONES_TO_GEO_ZONES . " AS ZG INNER JOIN " . TABLE_GEO_ZONES . " AS G ON ZG.geo_zone_id = G.geo_zone_id) INNER JOIN " . TABLE_COUNTRIES . " AS C ON ZG.zone_country_id = C.countries_id) INNER JOIN " . TABLE_INVOICE_TO_GEO_ZONES . " AS IG ON G.geo_zone_id = IG.geo_zone_id WHERE (((C.countries_name)='" . $oInfo->billing_country . "'))");        
        while ($zId_array = tep_db_fetch_array($zId_query)) {
           $zId = $zId_array['invoice_id'];              
           $zone_found=true;           
        }      
        if (($country_found) && ($zone_found))
        {
                if (MULTIPLE_INVOICE_PRIORITY == 1) 
                {
        	  //Use zone, ignore country
        	  $czId = $zId;
        	  $text = TEXT_ZONE_FOUND;
                }
                else
        	{
                  $czId = $cId;
                  $text = TEXT_COUNTRY_FOUND;
        	}
        }	
        else if ($country_found)
        {
        	$czId = $cId;
        	$text = TEXT_COUNTRY_FOUND;
        }	
        else if ($zone_found)
        {
        	$czId = $zId;
        	$text = TEXT_ZONE_FOUND;
        }	
        
        $sczId = MULTIPLE_PACKINGSLIP_ID_DEFAULT;
        $scountry_found=false;
        $szone_found=false;
        $stext = TEXT_NOTHING_FOUND;
        
        $scId_query = tep_db_query("SELECT C.countries_name, I.packingslip_id as packingslip_id FROM " . TABLE_COUNTRIES . " AS C INNER JOIN (" . TABLE_PACKINGSLIPS . " AS I INNER JOIN " . TABLE_PACKINGSLIP_TO_COUNTRIES . " AS IC ON I.packingslip_id = IC.packingslip_id) ON C.countries_id = IC.countries_id WHERE (((C.countries_name)='" . $oInfo->delivery_country . "'))");        
        while ($scId_array = tep_db_fetch_array($scId_query)) {
           $scId = $scId_array['packingslip_id'];              
           $scountry_found=true;
        }      
        
        $szId_query = tep_db_query("SELECT G.geo_zone_name, G.geo_zone_id, C.countries_name, IG.packingslip_id as packingslip_id FROM ((" . TABLE_ZONES_TO_GEO_ZONES . " AS ZG INNER JOIN " . TABLE_GEO_ZONES . " AS G ON ZG.geo_zone_id = G.geo_zone_id) INNER JOIN " . TABLE_COUNTRIES . " AS C ON ZG.zone_country_id = C.countries_id) INNER JOIN " . TABLE_PACKINGSLIP_TO_GEO_ZONES . " AS IG ON G.geo_zone_id = IG.geo_zone_id WHERE (((C.countries_name)='" . $oInfo->delivery_country . "'))");        
        while ($szId_array = tep_db_fetch_array($szId_query)) {
           $szId = $szId_array['packingslip_id'];              
           $szone_found=true;           
        }      
        if (($scountry_found) && ($szone_found))
        {
                if (MULTIPLE_PACKINGSLIP_PRIORITY == 1) 
                {
        	  //Use zone, ignore country
        	  $sczId = $szId;
        	  $stext = TEXT_ZONE_FOUND;
                }
                else
        	{
                  $sczId = $scId;
                  $stext = TEXT_COUNTRY_FOUND;
        	}
        }	
        else if ($scountry_found)
        {
        	$sczId = $scId;
        	$stext = TEXT_COUNTRY_FOUND;
        }	
        else if ($szone_found)
        {
        	$sczId = $szId;
        	$stext = TEXT_ZONE_FOUND;
        }	
        //EOF LIEBERMANN MULTIPLE INVOICES
	   $heading[] = array('text' => '<b>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . tep_datetime_short($oInfo->date_purchased) . '</b>'); }
        
      
	   if ( CONFIG_ADMIN_SHOW_BUTTONS_PNG == 'true' ) {    
		    $contents = array('form' => tep_draw_form('invoices', FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=deleteconfirm'));
        
		   
		   $contents[] = array('align' => 'center', 'text' => '  <a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '">'    . tep_image(DIR_WS_IMAGES . 'details_large.png', IMAGE_DETAILS) . '</a> 
		                                                         <a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=delete') . '">'  . tep_image(DIR_WS_IMAGES . 'delete_large.png', IMAGE_DELETE) . '</a>');
           if ( $has_order_editor ) {
	         $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_ORDERS_EDIT, 'oID=' . $oInfo->orders_id ) .'" TARGET="_blank">'                                                 . tep_image(DIR_WS_IMAGES . 'edit_large.png',        IMAGE_EDIT) . '</a>');
           }  		             
           if ( $has_pdf_invoice ) {
               // use the pdf invoice
                if ( CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'PDF' or CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'BOTH' ) { 
                   $contents[] = array('align' => 'center', 'text' => '  <a href="' . tep_href_link(FILENAME_PDF_INVOICE, 'oID=' . $oInfo->orders_id)     . '" TARGET="_blank">'                                          . tep_image(DIR_WS_IMAGES . 'invoice_large_pdf.png',     IMAGE_ORDERS_INVOICE) . '</a> 
                                                                         <a href="' . tep_href_link(FILENAME_PDF_PACKINGSLIP, 'oID=' . $oInfo->orders_id) . '" TARGET="_blank">'                                          . tep_image(DIR_WS_IMAGES . 'packingslip_large_pdf.png', IMAGE_ORDERS_PACKINGSLIP) . '</a>' ) ;               
                }                                                                        
                 if ( CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'HTML' or CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'BOTH' ) { 
  	               $contents[] = array('align' => 'center', 'text' => '  <a href="' . tep_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oInfo->orders_id)     . '" TARGET="_blank">'                                          . tep_image(DIR_WS_IMAGES . 'invoice_large.png',     IMAGE_ORDERS_INVOICE) . '</a> 
                                                                         <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $oInfo->orders_id) . '" TARGET="_blank">'                                          . tep_image(DIR_WS_IMAGES . 'packingslip_large.png', IMAGE_ORDERS_PACKINGSLIP) . '</a>' ) ;                 
                 }
           } else {                                                      
               $contents[] = array('align' => 'center', 'text' => '  <a href="' . tep_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oInfo->orders_id)     . '" TARGET="_blank">'                                          . tep_image(DIR_WS_IMAGES . 'invoice_large.png',     IMAGE_ORDERS_INVOICE) . '</a> 
                                                                     <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $oInfo->orders_id) . '" TARGET="_blank">'                                          . tep_image(DIR_WS_IMAGES . 'packingslip_large.png', IMAGE_ORDERS_PACKINGSLIP) . '</a>' ) ;
			   // BOF LIEBERMANN MULTIPLE INVOICES
        $xx = '"javascript:void(0);" onclick="ShowInvoice(\'' . tep_href_link('invoice_sui.php', tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id) . '\'",\'invoices\')" ';
        $contents[] = array('align' => 'center', 'text' => '<hr>');        
        $contents[] = array('align' => 'center', 'text' => '<a href=' . $xx . '	>' . tep_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a>');
        $contents[] = array('align' => 'Center', 'text' => tep_draw_pull_down_menu('invoicename', $invoices, $czId));
        $contents[] = array('align' => 'Center', 'text' => $text);
        // test: billing country
        $contents[] = array('align' => 'left', 'text' => TEXT_BILL_TO . '<br>' . $oInfo->billing_country);
        $contents[] = array('align' => 'center', 'text' => '<hr>');        
        $xx = '"javascript:void(0);" onclick="ShowPackingslip(\'' . tep_href_link('packingslip_gen.php', 'oID=' . $oInfo->orders_id) . '\',\'invoices\')" ';
        $contents[] = array('align' => 'center', 'text' => '<a href=' . $xx . '	>' . tep_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a>');
        $contents[] = array('align' => 'Center', 'text' => tep_draw_pull_down_menu('packingslipname', $packingslips, $sczId));
        $contents[] = array('align' => 'Center', 'text' => $stext);
        $contents[] = array('align' => 'left', 'text' => TEXT_DELIVER_TO . '<br>' . $oInfo->delivery_country);
        $contents[] = array('align' => 'center', 'text' => '<hr>');        
        // EOF LIEBERMANN MULTIPLE INVOICES
           }                                                                     
                                                               
           if ($has_label_pdf) {                                                    
             $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_ORDERS_LABEL, 'oID=' . $oInfo->orders_id) . '" TARGET="_blank">'                                                . tep_image(DIR_WS_IMAGES . 'print_label_large.png', IMAGE_ORDERS_LABEL) . '</a>' );
           }
           if ($google_map_directions) { 
             $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_GOOGLE_MAP, 'oID=' .$oInfo->orders_id) . '" TARGET="_blank">'                                                . tep_image(DIR_WS_IMAGES . 'google_map_delivery_large.png', IMAGE_GOOGLE_DIRECTIONS) . '</a>' );
           }

           $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_MAIL,               'selected_box=tools&customer=' . $orders[ 'customers_email_address' ] ) . '">' .                                              tep_image(DIR_WS_IMAGES . 'email_send_large.png',  IMAGE_EMAIL)              . '</a>' ) ;
           	                      
//           if ($has_create_order) {
//	         $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_CREATE_ORDER) .'" TARGET="_blank">'                                                                             . tep_image(DIR_WS_IMAGES . 'create_order.png', IMAGE_CREATE_ORDER) . '</a>');
//           }         
     
	         
	   } else {
		   $contents[] = array('align' => 'center', 'text' => '  <a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=edit') . '">'   . tep_image_button('button_details.gif', IMAGE_DETAILS) . '</a> 
		                                                         <a href="' . tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('oID', 'action')) . 'oID=' . $oInfo->orders_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
           if ( $has_order_editor ) {
	         $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_ORDERS_EDIT, 'oID=' . $oInfo->orders_id ) .'" TARGET="_blank">'                                                . tep_image_button('button_edit_orders.gif', IMAGE_EDIT) . '</a>');
           } 
           if ( $has_pdf_invoice ) {
               // use the pdf invoice
               if ( CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'PDF' or CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'BOTH' ) {
                  $contents[] = array('align' => 'center', 'text' => '  <a href="' . tep_href_link(FILENAME_PDF_INVOICE, 'oID=' . $oInfo->orders_id)     . '" TARGET="_blank">'                                          . tep_image_button('button_invoice_pdf.gif', IMAGE_ORDERS_INVOICE) . '</a> 
                                                                        <a href="' . tep_href_link(FILENAME_PDF_PACKINGSLIP, 'oID=' . $oInfo->orders_id) . '" TARGET="_blank">'                                          . tep_image_button('button_packingslip_pdf.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a>' ) ; 
               }                                                                        
               if ( CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'HTML' or CONFIG_ADMIN_PDF_INVOICE_PACKINGSLIP == 'BOTH' ) {
                  $contents[] = array('align' => 'center', 'text' => '  <a href="' . tep_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oInfo->orders_id)     . '" TARGET="_blank">'                                          . tep_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a> 
                                                                        <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $oInfo->orders_id) . '" TARGET="_blank">'                                          . tep_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a>' ) ;	               
               }
           } else {
               $contents[] = array('align' => 'center', 'text' => '  <a href="' . tep_href_link(FILENAME_ORDERS_INVOICE, 'oID=' . $oInfo->orders_id)     . '" TARGET="_blank">'                                          . tep_image_button('button_invoice.gif', IMAGE_ORDERS_INVOICE) . '</a> 
                                                                     <a href="' . tep_href_link(FILENAME_ORDERS_PACKINGSLIP, 'oID=' . $oInfo->orders_id) . '" TARGET="_blank">'                                          . tep_image_button('button_packingslip.gif', IMAGE_ORDERS_PACKINGSLIP) . '</a>' ) ;
           }                                                                     
                                                              
           if ($has_label_pdf) {                                                    
             $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_ORDERS_LABEL, 'oID=' . $oInfo->orders_id) . '" TARGET="_blank">'                                                . tep_image_button('button_label.gif', IMAGE_ORDERS_LABEL) . '</a>' );
           }
           if ($google_map_directions) { 
             $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_GOOGLE_MAP,       'oID=' .$oInfo->orders_id) .'" TARGET="_blank">'                                        . tep_image_button('button_google_directions.gif', IMAGE_GOOGLE_DIRECTIONS) . '</a>');
           }                     
           $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_MAIL,               'customer=' .$oInfo->customers_email_address ). '" TARGET="_blank">'                    . tep_image_button('button_email.gif',  IMAGE_EMAIL)              . '</a>' ) ;

        }  // endif show alternative or standard buttons
        
        $contents[] = array('text' => '<br>' . TEXT_DATE_ORDER_CREATED . ' ' . tep_date_short($oInfo->date_purchased));

        if (tep_not_null($oInfo->last_modified)) $contents[] = array('text' => TEXT_DATE_ORDER_LAST_MODIFIED . ' ' . tep_date_short($oInfo->last_modified));
        
        $contents[] = array('text' => '<br />' . TEXT_INFO_PAYMENT_METHOD . ' '  . $oInfo->payment_method);
      } // endif show admin buttons    
     
   
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
<?php
  }
?>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
