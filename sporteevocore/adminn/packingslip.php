<?php
/*
  $Id: packingslip.php,v 1.7 2003/06/20 00:40:10 hpdl Exp $
  ++++++++++++++++++++++++++++++++++++++++++++++
  + Giorgio ABBATE Modification
  +  packingslip.php Modification, v 0.1 2004/10/23
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
                <td align="center" valign="top"><?php echo tep_image(DIR_WS_IMAGES . 'logo-invoice.gif', 'ProseccoRoad.com'); ?></td>
              </tr>
              <tr>
                <td align="left" valign="top" class="pageHeading-invoice"><?php echo nl2br(STORE_NAME_ADDRESS); ?></td>
              </tr>
            </table></td>
          <td>&nbsp;</td>
          <td width="350" align="right" valign="top">&nbsp; </td>
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
                <td align="center" valign="top" bgcolor="#CCCCCC">
                  <table width="100%"  border="0" cellspacing="0" cellpadding="0" class="main">
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
                      <td >&nbsp;</td>
                    </tr>
                  </table>
                </td>
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
  <?php

//MVS start
  $index = 0;
  $order_packslip_query = tep_db_query ("select vendors_id, orders_products_id, products_name, products_model, products_price, products_tax, products_quantity, final_price from " . TABLE_ORDERS_PRODUCTS . " where orders_id = '" . (int) $oID . "'");
  while ($order_packslip_data = tep_db_fetch_array( $order_packslip_query) ) {
    $packslip_products[$index] = array (
      'qty' => $order_packslip_data['products_quantity'],
      'name' => $order_packslip_data['products_name'],
      'model' => $order_packslip_data['products_model'],
      'tax' => $order_packslip_data['products_tax'],
      'price' => $order_packslip_data['products_price'],
      'final_price' => $order_packslip_data['final_price']
    );

    $subindex = 0;
    $packslip_attributes_query = tep_db_query ("select products_options, products_options_values, options_values_price, price_prefix from " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " where orders_id = '" . (int) $oID . "' and orders_products_id = '" . (int) $order_packslip_data['orders_products_id'] . "'");
    if (tep_db_num_rows ($packslip_attributes_query)) {
      while ($packslip_attributes = tep_db_fetch_array ($packslip_attributes_query) ) {
        $packslip_products[$index]['packslip_attributes'][$subindex] = array (
          'option' => $packslip_attributes['products_options'],
          'value' => $packslip_attributes['products_options_values'],
          'prefix' => $packslip_attributes['price_prefix'],
          'price' => $packslip_attributes['options_values_price']
        );

        $subindex++;
      }
    }
    $index++;
  }
?>
  <tr>
    <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr class="dataTableHeadingRow">
        <td class="dataTableHeadingContent-invoice" colspan="2"><?php echo TABLE_HEADING_PRODUCTS; ?></td>
        <td class="dataTableHeadingContent-invoice"><?php echo TABLE_HEADING_PRODUCTS_MODEL; ?></td>
      </tr>
<?php
    for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
      echo '      <tr class="dataTableRow">' . "\n" .
           '        <td class="dataTableContent" valign="top" align="right">' . $order->products[$i]['qty'] . '&nbsp;x</td>' . "\n" .
           '        <td class="dataTableContent" valign="top">' . $order->products[$i]['name'];

      if (isset($order->products[$i]['attributes']) && (sizeof($order->products[$i]['attributes']) > 0)) {
        for ($j=0, $k=sizeof($order->products[$i]['attributes']); $j<$k; $j++) {
          echo '<br><nobr><small>&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
          echo '</i></small></nobr>';
        }
      }

      echo '        </td>' . "\n" .
           '        <td class="dataTableContent" valign="top">' . $order->products[$i]['model'] . '</td>' . "\n" .
           '      </tr>' . "\n";
    }
	    echo '</td>' . "\n" . 
         '        <td class="dataTableContent" valign="top">' . $packslip_products[$i]['spacer'] . '</td>' . "\n" .
         '        <td class="dataTableContent" valign="top">' . $packslip_products[$i]['model'] . '</td>' . "\n" .
         '      </tr>' . "\n";
  }
  //MVS end
?>
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
