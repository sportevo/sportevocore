<?php
/*
  $Id: invoice.php,v 1.6 2003/06/20 00:37:30 hpdl Exp $
  ++++++++++++++++++++++++++++++++++++++++++++++
  + Giorgio ABBATE Modification
  +  invoice.php Modification, v 0.1 2004/10/23
  ++++++++++++++++++++++++++++++++++++++++++++++

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  $oID = tep_db_prepare_input($_GET['oID']);

//Professional Invoice and Packing Slip START

	//if you want your invoice starts to your last personal invoice number, set this   -START-
 $date_array = getdate();
 $old_num = '0';//last personal invoice number [cange to zero '0' if you start a new invoice series
 $last_year_invoice = '2007'; // YEAR about your last personal invoice number
 if ( $date_array[year]== $last_year_invoice)
  {
  $correction=$old_num;
	 }
     else
     {
 $correction='0';
  }
     // your last personal invoice number   -END-

	 // how many invoice in the last year  -START-
      $verify_data_query = tep_db_query("select date_invoice from " . TABLE_INVOICE . " where substring(date_invoice,7)  < '".((int) $date_array[year]) . "'");
	  $count_num_invoice=mysql_num_rows($verify_data_query);
	  // how many invoice in the last year   -END-

	  // invoice exist for this order
	  $verify_query = tep_db_query("select orders_id from " . TABLE_INVOICE . " where orders_id = '" . (int)$oID . "'");
      $verify_id = tep_db_fetch_array($verify_query);
	  $sql_data_array = array('orders_id' => $oID);
     if ($verify_id['orders_id'] == $oID)
	 {
	 }
	 // else insert date and invoice number for this order
     else
     {
	 $data_trasformed = date("d-m-Y");
	 tep_db_query("insert into " . TABLE_INVOICE . " (orders_id, date_invoice) values ('". (int)$oID . "','" .$data_trasformed ."')");
     $num_actually = mysql_insert_id();
	 $num_invoice= ((int)$num_actually - (int)$count_num_invoice + $correction);
	 tep_db_query("update " . TABLE_INVOICE . " set num_invoice = ('".$num_invoice ."') where orders_id = '" . (int)$oID . "'");
	 }
	 // -END-  invoice counting

	$invoice_id_query = tep_db_query("select invoice_id, num_invoice from " . TABLE_INVOICE . " where orders_id = '" . (int)$oID . "'");
    $invoice_id = tep_db_fetch_array($invoice_id_query);
// Professional Invoice and Packing Slip END

  $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . (int)$oID . "'");

  include(DIR_WS_CLASSES . 'order.php');
  $order = new order($oID);
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">

<!-- body_text //-->
<table border="0" width="100%" >
  <tr>
    <td><table width="100%" border="0">
        <tr>
          <td width="350">
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0"  >
              <tr>
                <td align="center" valign="top"><?php echo tep_image(DIR_WS_IMAGES . 'logo-invoice.gif', ''); ?></td>
              </tr>
              <tr>
                <td align="left" valign="top" class="pageHeading-invoice"><?php echo nl2br(STORE_NAME_ADDRESS); ?></td>
              </tr>
            </table></td>
          <td>&nbsp;</td>
          <td width="350" align="right" valign="top">
            <table width="200" border="0"  class="pageHeading-invoice2">
              <tr>
                <td><b><?php echo ENTRY_INVOICE_NUMBER; ?>&nbsp; <?php echo ENTRY_INVOICE_NUMBER_PREFIX .  ENTRY_INVOICE_NUMBER_CENTER . $invoice_id['num_invoice'] . ENTRY_INVOICE_NUMBER_SUFFIX; ?>
                  </b></td>
              </tr>
              <tr>
                <td><b><?php echo ENTRY_INVOICE_DATE; ?></b> &nbsp;
                  <?php   $data_query = tep_db_query("select date_purchased from " . orders . " where orders_id = '" . (int)$oID . "'");     $date_purchased = tep_db_fetch_array($data_query);    echo $date_purchased['date_purchased']; ?>
                </td>
              </tr>
            </table></td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
          <td><hr></td>
          <td width="100" align="center" valign="middle"  class="pageHeading"><em><b><?php echo ENTRY_HEADLINE; ?></b></em></td>
          <td width="10%"> <hr></td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0">
        <tr>
          <td width="350" align="left" valign="top">
            <table width="100%"  border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td width="11"> <img src="../images/borders/maingrey_01.gif" width="11" height="16" alt=""></td>
                <td background="../images/borders/maingrey_02.gif"> <img src="../images/borders/maingrey_02.gif" width="24" height="16" alt="" ></td>
                <td width="19"> <img src="../images/borders/maingrey_03.gif" width="19" height="16" alt=""></td>
              </tr>
              <tr>
                <td background="../images/borders/maingrey_04.gif"> <img src="../images/borders/maingrey_04.gif" width="11" height="21" alt=""></td>
                <td align="center" bgcolor="#CCCCCC"> <table width="100%"  border="0" cellspacing="0" cellpadding="0" class="main">
                    <tr>
                      <td align="left" valign="top" ><b><?php echo ENTRY_SOLD_TO; ?></b></td>
                    </tr>
                    <tr>
                      <td align="left" valign="bottom" ><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
                    </tr>
                    <tr>
                      <td ><?php echo tep_address_format($order->customer['format_id'], $order->customer, 1, '', '<br>'); ?></td>
                    </tr>
                    <tr>
                      <td >&nbsp;</td>
                    </tr>
                    <tr>
                      <td ><?php echo tep_draw_separator('pixel_trans.gif', '1', '15'); ?></td>
                    </tr>
                    <tr>
                      <td ><b><?php echo ENTRY_TELEPHONE_NUMBER; ?></b> <?php echo $order->customer['telephone']; ?></td>
                    </tr>
                    <tr>
                      <td ><b><?php echo IMAGE_EMAIL; ?>:</b> <?php echo '<a href="mailto:' . $order->customer['email_address'] . '"><u>' . $order->customer['email_address'] . '</u></a>'; ?></td>
                    </tr>
                    <tr>
                      <td ><b><?php echo ENTRY_PIVA; ?></b>&nbsp;<?php echo $order->billing['piva']; ?></td>
                    </tr>
                    <tr>
                      <td ><b><?php echo ENTRY_CF; ?></b>&nbsp;<?php echo $order->billing['cf']; ?></td>
                    </tr>
                    <tr>
                      <td >&nbsp;</td>
                    </tr>
                  </table></td>
                <td background="../images/borders/maingrey_06.gif"> <img src="../images/borders/maingrey_06.gif" width="19" height="21" alt=""></td>
              </tr>
              <tr>
                <td> <img src="../images/borders/maingrey_07.gif" width="11" height="18" alt=""></td>
                <td background="../images/borders/maingrey_08.gif"> <img src="../images/borders/maingrey_08.gif" width="24" height="18" alt=""></td>
                <td> <img src="../images/borders/maingrey_09.gif" width="19" height="18" alt=""></td>
              </tr>
            </table></td>
          <td>&nbsp;</td>
          <td width="350" align="right" valign="top">
            <table width="100%"  border="0" cellpadding="0" cellspacing="0">
              <tr>
                <td width="11"> <img src="../images/borders/mainwhite_01.gif" width="11" height="16" alt=""></td>
                <td background="../images/borders/mainwhite_02.gif"> <img src="../images/borders/mainwhite_02.gif" width="24" height="16" alt=""></td>
                <td width="19"> <img src="../images/borders/mainwhite_03.gif" width="19" height="16" alt=""></td>
              </tr>
              <tr>
                <td background="../images/borders/mainwhite_04.gif"> <img src="../images/borders/mainwhite_04.gif" width="11" height="21" alt=""></td>
                <td align="center" bgcolor="#FFFFFF"> <table width="100%"  border="0" cellpadding="0" cellspacing="0" class="main">
                    <tr>
                      <td align="left" valign="top"><b><?php echo ENTRY_SHIP_TO; ?></b></td>
                    </tr>
                    <tr>
                      <td align="left" valign="bottom"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
                    </tr>
                    <tr>
                      <td align="left" valign="bottom"><?php echo tep_address_format($order->delivery['format_id'], $order->delivery, 1, '', '<br>'); ?></td>
                    </tr>
                    <tr>
                      <td align="left" valign="bottom">&nbsp;</td>
                    </tr>
                    <tr>
                      <td align="left" valign="bottom">&nbsp;</td>
                    </tr>
                  </table></td>
                <td background="../images/borders/mainwhite_06.gif"> <img src="../images/borders/mainwhite_06.gif" width="19" height="21" alt=""></td>
              </tr>
              <tr>
                <td> <img src="../images/borders/mainwhite_07.gif" width="11" height="18" alt=""></td>
                <td background="../images/borders/mainwhite_08.gif"> <img src="../images/borders/mainwhite_08.gif" width="24" height="18" alt=""></td>
                <td> <img src="../images/borders/mainwhite_09.gif" width="19" height="18" alt=""></td>
              </tr>
            </table>
          </td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" >
        <tr>
          <td class="main-payment"><b><?php echo ENTRY_ORDER_NUMBER ?></b> <?php echo ENTRY_INVOICE_NUMBER_PREFIX .  ENTRY_INVOICE_NUMBER_CENTER . tep_db_input($oID) . ENTRY_INVOICE_NUMBER_SUFFIX; ?>
          </td>
          <td class="main-payment"><b><?php echo ENTRY_ORDER_DATE ?> </b><?php echo tep_date_short($order->info['date_purchased']); ?></td>
          <td class="main-payment"><b><?php echo ENTRY_PAYMENT_METHOD; ?></b>&nbsp;<?php echo $order->info['payment_method']; ?></td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
  	<?php 
// Start MVS
  if (tep_not_null($order->orders_shipping_id)) {  
?>
    <td><table border="1" width="100%" cellspacing="0" cellpadding="2">
      <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_PRODUCTS_VENDOR; ?></td>
        <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
        <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_VENDORS_SHIP; ?></td>
        <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_SHIPPING_METHOD; ?></td>
        <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_SHIPPING_COST; ?></td>
        <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
        <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></td>
      </tr>
<?php
    $package_num = sizeof ($order->products);
    $box_num = $l + 1;
    echo '      <tr>' . "\n" .
         '        <td class="dataTableContent" colspan=11>There will be <b>at least ' . $package_num . '</b><br>packages shipped.</td>' . "\n" .
         '      </tr>' . "\n";
    for ($l=0, $m=sizeof($order->products); $l<$m; $l++) {
      echo '      <tr class="dataTableRow">' . "\n" .
           '        <td class="dataTableContent" valign="center">Shipment Number ' . $box_num++ . '</td>' . "\n" .
           '        <td class="dataTableContent" valign="center" align="center">' . $order->products[$l]['spacer'] . '</td>' . "\n" .
           '        <td class="dataTableContent" valign="center" align="center">' . $order->products[$l]['Vmodule'] . '</td>' . "\n" .
           '        <td class="dataTableContent" valign="center" align="center">' . $order->products[$l]['Vmethod'] . '</td>' . "\n" .
           '        <td class="dataTableContent" valign="center" align="center">' . $order->products[$l]['Vcost'] . '</td>' . "\n" .
           '        <td class="dataTableContent" valign="center" align="center">' . $order->products[$l]['spacer'] . '</td>' . "\n" .
           '        <td class="dataTableContent" valign="center" align="center">ship tax<br>' . $order->products[$l]['Vship_tax'] . '</td>' . "\n" .
           '        <td class="dataTableContent" valign="center" align="center">' . $order->products[$l]['spacer'] . '</td>' . "\n" .
           '        <td class="dataTableContent" valign="center" align="center">' . $order->products[$l]['spacer'] . '</td>' . "\n" .
           '        <td class="dataTableContent" valign="center" align="center">' . $order->products[$l]['spacer'] . '</td>' . "\n" .
           '        <td class="dataTableContent" valign="center" align="center">' . $order->products[$l]['spacer'] . '</td>' . "\n" .
           '      </tr>' . "\n";
      for ($i=0, $n=sizeof($order->products[$l]['orders_products']); $i<$n; $i++) {
        echo '      <tr>' . "\n" .
             '        <td class="dataTableContent" valign="center" align="right">' . $order->products[$l]['orders_products'][$i]['qty'] . '&nbsp;x</td>' . "\n" .
             '        <td class="dataTableContent" valign="center" align="left">' . $order->products[$l]['orders_products'][$i]['name'] . "\n";

        if (isset ($order->products[$l]['orders_products'][$i]['attributes']) && (sizeof ($order->products[$l]['orders_products'][$i]['attributes']) > 0)) {
          for ($j = 0, $k = sizeof($order->products[$l]['orders_products'][$i]['attributes']); $j < $k; $j++) {
            echo '          <br><nobr><small>&nbsp;<i> - ' . $order->products[$l]['orders_products'][$i]['attributes'][$j]['option'] . ': ' . $order->products[$l]['orders_products'][$i]['attributes'][$j]['value'];
            if ($order->products[$l]['orders_products'][$i]['attributes'][$j]['price'] != '0') echo ' (' . $order->products[$l]['orders_products'][$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$l]['orders_products'][$i]['attributes'][$j]['price'] * $order->products[$l]['orders_products'][$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
            echo '</i></small></nobr>';
          } 
        }

        echo '        <td class="dataTableContent" valign="center" align="center">' . $order->products[$l]['orders_products'][$i]['spacer'] . '</td>' . "\n" .
             '        <td class="dataTableContent" valign="center" align="center">' . $order->products[$l]['orders_products'][$i]['spacer'] . '</td>' . "\n" .
             '        <td class="dataTableContent" valign="center" align="center">' . $order->products[$l]['orders_products'][$i]['spacer'] . '</td>' . "\n" .
             '        <td class="dataTableContent" valign="center" align="center">' . $order->products[$l]['orders_products'][$i]['model'] . '</td>' . "\n" .
             '        <td class="dataTableContent" align="center" valign="center">' . tep_display_tax_value($order->products[$l]['orders_products'][$i]['tax']) . '%</td>' . "\n" .
             '        <td class="dataTableContent" align="center" valign="center"><b>' . $currencies->format($order->products[$l]['orders_products'][$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
             '        <td class="dataTableContent" align="center" valign="center"><b>' . $currencies->format(tep_add_tax($order->products[$l]['orders_products'][$i]['final_price'], $order->products[$l]['orders_products'][$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
             '        <td class="dataTableContent" align="center" valign="center"><b>' . $currencies->format($order->products[$l]['orders_products'][$i]['final_price'] * $order->products[$l]['orders_products'][$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
             '        <td class="dataTableContent" align="right" valign="center"><b>' .  $currencies->format(tep_add_tax($order->products[$l]['orders_products'][$i]['final_price'], $order->products[$l]['orders_products'][$i]['tax']) * $order->products[$l]['orders_products'][$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n";
        echo '      </tr>';
      } //for ($i=0, $n=sizeof($order->products
    }
?>
          <tr>
            <td align="right" colspan="12"><table border="0" cellspacing="0" cellpadding="2">
<?php
  } else {   
// End MVS 
?>
    <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent-invoice" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
        <td class="dataTableHeadingContent-invoice"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
        <td class="dataTableHeadingContent-invoice" align="right"><?php echo TABLE_HEADING_TAX; ?></td>
        <td class="dataTableHeadingContent-invoice" align="right"><?php echo TABLE_HEADING_PRICE_EXCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent-invoice" align="right"><?php echo TABLE_HEADING_PRICE_INCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent-invoice" align="right"><?php echo TABLE_HEADING_TOTAL_EXCLUDING_TAX; ?></td>
        <td class="dataTableHeadingContent-invoice" align="right"><?php echo TABLE_HEADING_TOTAL_INCLUDING_TAX; ?></td>
      </tr>
<?php
    for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
      echo '      <tr class="dataTableRow">' . "\n" .
           '        <td class="dataTableContent" valign="top" align="right">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
           '        <td class="dataTableContent" valign="top">' . $order->products[$i]['name'];

      if (isset($order->products[$i]['attributes']) && (($k = sizeof($order->products[$i]['attributes'])) > 0)) {
        for ($j = 0; $j < $k; $j++) {
          echo '<br><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
          if ($order->products[$i]['attributes'][$j]['price'] != '0') echo ' (' . $order->products[$i]['attributes'][$j]['prefix'] . $currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . ')';
          echo '</i></small></nobr>';
        }
      }

      echo '        </td>' . "\n" .
           '        <td class="dataTableContent" valign="top">' . $order->products[$i]['model'] . '</td>' . "\n";
      echo '        <td class="dataTableContent" align="right" valign="top">' . tep_display_tax_value($order->products[$i]['tax']) . '%</td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n" .
           '        <td class="dataTableContent" align="right" valign="top"><b>' . $currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']) . '</b></td>' . "\n";
      echo '      </tr>' . "\n";
    }
?>
      <tr>
        <td align="right" colspan="8"><br> <table border="0" cellspacing="0" cellpadding="2">
<?php
  for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
    echo '          <tr>' . "\n" .
         '            <td align="right" class="smallText">' . $order->totals[$i]['title'] . '</td>' . "\n" .
         '            <td align="right" class="smallText">' . $order->totals[$i]['text'] . '</td>' . "\n" .
         '          </tr>' . "\n";
  }
?>
            </table></td>
        </tr>
      </table></td>
  </tr>
  <tr>
    <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '50'); ?></td>
  </tr>
  <tr>
    <td colspan="2" align="center" class="main"><?php echo ENTRY_FOOTER; ?></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
</table>
<!-- body_text_eof //-->

<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
