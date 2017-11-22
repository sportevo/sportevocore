<?php
/*
  $Id: exportorders.php,v 1.1 April 21, 2006 Harris Ahmed $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/4 Francesco Rossi

  Use this module on your own risk. I will be updating a new one soon. This template is used to create
  the csv export for Ideal Computer Systems Accounting Software
*/

  define('FILENAME_EXPORTORDERS', 'exportorders.php');


require('includes/application_top.php'); 
require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_EXPORTORDERS);

// Check if the form is submitted
if (!$_GET['submitted'])
{
?>
<!-- header_eof //-->
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
        <!-- left_navigation //-->
        <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
        <!-- left_navigation_eof //-->
      </table></td>
    <!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
                <td class="pageHeading" align="right"></td>
              </tr>
            </table></td>
        </tr>
        <!-- first ends // -->
        <tr>
          <td><table border="0" style="font-family:tahoma;font-size:11px;" width="100%" cellspacing="2" cellpadding="2">
              <tr>
                <td><form method="GET" action="<?php echo $PHP_SELF; ?>">
                    <table border="0" style="font-family:tahoma;font-size:11px;" cellpadding="3">
                      <tr>
                        <td><?php echo INPUT_START; ?></td>
                        <td><!-- input name="start" size="5" value="<?php echo $start; ?>"> -->
                          <?php
    	                    $orders_list_query = tep_db_query("SELECT orders_id, date_purchased FROM orders ORDER BY orders_id");
   							$orders_list_array = array();
							$orders_list_array[] = array('id' => '', 'text' => '---');
   						    while ($orders_list = tep_db_fetch_array($orders_list_query)) {
   					        $orders_list_array[] = array('id' => $orders_list['orders_id'],
                                       'text' => $orders_list['orders_id']." - ".tep_date_short($orders_list['date_purchased']));
							}  

							echo '&nbsp;&nbsp;' . tep_draw_pull_down_menu('start', $orders_list_array, (isset($_GET['orders_id']) ? $_GET['orders_id'] : ''), 'size="1"') . '&nbsp;&nbsp;&nbsp;';

						?></td>
                      </tr>
                      <tr>
                        <td><?php echo INPUT_END; ?></td>
                        <td><!-- <input name="end" size="5" value="<?php echo $end; ?>"> -->
                          <?php 
						echo '&nbsp;&nbsp;' . tep_draw_pull_down_menu('end', $orders_list_array, (isset($_GET['orders_id']) ? $_GET['orders_id'] : ''), 'size="1"') . '&nbsp;&nbsp;&nbsp;';
						?></td>
                      </tr>
                      <tr>
                        <td>&nbsp;</td>
                        <td><input type="submit" value="<?php echo INPUT_VALID; ?>"></td>
                      </tr>
                    </table>
                    <input type="hidden" name="submitted" value="1">
                  </form></td>
              </tr>
              <tr>
                <td><?php echo INPUT_DESC; ?></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
            </table></td>
        </tr>
      </table></td>
  </tr>
</table>
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
<?php
}
// submitted so generate csv if the form is submitted
else
{
generatecsv($_GET['start'], $_GET['end']);
}

// generates csv file from $start order to $end order, inclusive
function generatecsv($start, $end)
{

// Patch dlan
// if both fields are empty we select all orders
if ($start=="" && $end=="") {
 $orders = tep_db_query("SELECT orders_id, date_purchased, customers_name, cc_owner, customers_company, customers_email_address, billing_street_address, billing_city, billing_state, billing_postcode, billing_country, customers_telephone, delivery_name, delivery_company, delivery_street_address, delivery_city, delivery_state, delivery_postcode, delivery_country, cc_type, cc_number, cc_expires 
FROM orders ORDER BY orders_id"); 
// if $start is empty we select all orders up to $end
} else if($start=="" && $end!="") {
 $orders = tep_db_query("SELECT orders_id, date_purchased, customers_name, cc_owner, customers_company, customers_email_address, billing_street_address, billing_city, billing_state, billing_postcode, billing_country, customers_telephone, delivery_name, delivery_company, delivery_street_address, delivery_city, delivery_state, delivery_postcode, delivery_country, cc_type, cc_number, cc_expires 
FROM orders WHERE orders_id <= $end ORDER BY orders_id"); 
// if $end is empty we select all orders from $start
} else if($start!="" && $end=="") {
 $orders = tep_db_query("SELECT orders_id, date_purchased, customers_name, cc_owner, customers_company, customers_email_address, billing_street_address, billing_city, billing_state, billing_postcode, billing_country, customers_telephone, delivery_name, delivery_company, delivery_street_address, delivery_city, delivery_state, delivery_postcode, delivery_country, cc_type, cc_number, cc_expires 
FROM orders WHERE orders_id >= $start ORDER BY orders_id");
// if both fields are filed in we select orders betwenn $start and $end
} else {
 $orders = tep_db_query("SELECT orders_id, date_purchased, customers_name, cc_owner, customers_company, customers_email_address, billing_street_address, billing_city, billing_state, billing_postcode, billing_country, customers_telephone, delivery_name, delivery_company, delivery_street_address, delivery_city, delivery_state, delivery_postcode, delivery_country, cc_type, cc_number, cc_expires 
FROM orders WHERE orders_id >= $start AND orders_id <= $end ORDER BY orders_id");
}
//patch

//$csv_output ="\n";
while ($row_orders = mysql_fetch_array($orders)) { //start one loop
 
$Orders_id = $row_orders["orders_id"];
$Date1 = $row_orders["date_purchased"];
//list($Date, $Time) = explode (' ',$Date1);
$Date = date('m/d/Y', strtotime($Date1));
$Time= date('H:i:s', strtotime($Date1));
$Name_On_Card1 = $row_orders["customers_name"]; 
$Name_On_Card = filter_text($Name_On_Card1);// order changed
list($First_Name,$Last_Name) = explode(', ',$Name_On_Card1); // order changed
$Company = filter_text($row_orders["customers_company"]);
$email = filter_text($row_orders["customers_email_address"]);
$Billing_Address_1 = filter_text($row_orders["billing_street_address"]);
$Billing_Address_2 = "";
$Billing_City = filter_text($row_orders["billing_city"]);
$Billing_State = filter_text($row_orders["billing_state"]);
$Billing_Zip = filter_text($row_orders["billing_postcode"]);
$Billing_Country = str_replace("(48 Contiguous Sta", "", $row_orders["billing_country"]);
$Billing_Phone = filter_text($row_orders["customers_telephone"]);
$ShipTo_Name1 = $row_orders["delivery_name"];
$ShipTo_Name = filter_text($ShipTo_Name1); // order changed
list($ShipTo_First_Name,$ShipTo_Last_Name) = explode(', ',$ShipTo_Name1); // order changed
$ShipTo_Company = filter_text($row_orders["delivery_company"]);
$ShipTo_Address_1 = filter_text($row_orders["delivery_street_address"]);
$ShipTo_Address_2 = "";
$ShipTo_City = filter_text($row_orders["delivery_city"]);
$ShipTo_State = filter_text($row_orders["delivery_state"]);
$ShipTo_Zip = filter_text($row_orders["delivery_postcode"]);
$ShipTo_Country = str_replace("(48 Contiguous Sta", "", $row_orders["delivery_country"]);
$ShipTo_Phone = "";
$Card_Type = $row_orders["cc_type"];
$Card_Number = $row_orders["cc_number"];
$Exp_Date = $row_orders["cc_expires"];
$Bank_Name = "";
$Gateway  = "";
$AVS_Code = "";
$Transaction_ID = "";
$Order_Special_Notes = "";
// --------------------    QUERIES 1  ------------------------------------//
//Orders_status_history for comments
 $orders_status_history = tep_db_query("select comments from orders_status_history
 where orders_id = " . $Orders_id);
 //$row_orders_status_history = tep_db_fetch_array($comments);
 while($row_orders_status_history = mysql_fetch_array($orders_status_history)) {
 // end //

$Comments = filter_text($row_orders_status_history["comments"]);

}
// --------------------    QUERIES 2  ------------------------------------//
//Orders_subtotal
$orders_subtotal = tep_db_query("select value from orders_total
where class = 'ot_subtotal' and orders_id = " . $Orders_id);
//$row_orders_subtotal = tep_db_fetch_array($orders_subtotal);
while($row_orders_subtotal = mysql_fetch_array($orders_subtotal)) {
 // end //
$Order_Subtotal = filter_text($row_orders_subtotal["value"]);
}
// --------------------    QUERIES 3  ------------------------------------//
//Orders_tax
$orders_tax = tep_db_query("select value from orders_total
where class = 'ot_tax' and orders_id = " . $Orders_id);
//$row_orders_tax = tep_db_fetch_array($orders_tax);
while($row_orders_tax = mysql_fetch_array($orders_tax)) {
 // end //
$Order_Tax = filter_text($row_orders_tax["value"]);
}
// --------------------    QUERIES 4  ------------------------------------//
//Orders_Insurance
$orders_insurance = tep_db_query("select value from orders_total
where class = 'ot_insurance' and orders_id = " . $Orders_id);
//$row_orders_insurance = tep_db_fetch_array($orders_insurance);
while($row_orders_insurance = mysql_fetch_array($orders_insurance)) {
 // end //
$Order_Insurance = filter_text($row_orders_insurance["value"]);
}
$Tax_Exempt_Message = "";
// --------------------    QUERIES 5  ------------------------------------//
//Orders_Shipping
$orders_shipping = tep_db_query("select title, value from orders_total
where class = 'ot_shipping' and orders_id = " . $Orders_id);
//$row_orders_shipping = tep_db_fetch_array($orders_shipping);
while($row_orders_shipping = mysql_fetch_array($orders_shipping)) {
 // end //
$Order_Shipping_Total = $row_orders_shipping["value"];
$Shipping_Method = filter_text($row_orders_shipping["title"]); // Shipping method from query 5
}
// --------------------    QUERIES 6  ------------------------------------//
//Orders_Residential Del Fee (Giftwrap)
$orders_residential_fee = tep_db_query("select value from orders_total
where class = 'ot_giftwrap' and orders_id = " . $Orders_id);
//$row_orders_residential_fee = tep_db_fetch_array($orders_residential_fee);
while($row_orders_residential_fee = mysql_fetch_array($orders_residential_fee)) {
 // end //
$Small_Order_Fee = $row_orders_residential_fee["value"];
}
////////////////////////////////////
$Discount_Rate = "";
$Discount_Message  = "";
$CODAmount  = "";
// --------------------    QUERIES 7  ------------------------------------//
//Orders_Total
$orders_total = tep_db_query("select value from orders_total
where class = 'ot_total' and orders_id = " . $Orders_id);
//$row_orders_total = tep_db_fetch_array($orders_total);
while($row_orders_total = mysql_fetch_array($orders_total)) {
 // end //
$Order_Grand_Total = $row_orders_total["value"];
}
// --------------------    QUERIES 8  ------------------------------------//
//Products COunt
$orders_count = tep_db_query("select count(products_quantity) as o_count from orders_products
where orders_id = " . $Orders_id);
//$row_orders_total = tep_db_fetch_array($orders_total);
while($row_orders_count = mysql_fetch_array($orders_count)) {
 // end //
$Number_of_Items = $row_orders_count[0]; // used array to show the number of items ordered
}
//
$Shipping_Weight = "";
$Coupon_Code = "";
$Order_security_msg = "";
$Order_Surcharge_Amount = "";
$Order_Surcharge_Something = "";
$Affiliate_code = "";
$Sentiment_message = "";
$Checkout_form_type = "";
$Card_CVV_value = $row_orders["cvvnumber"];
$future1  = "";
$future2 = "";
$future3 = "";
$future4 = "";
$future5 = "";
$future6 = "";
$future7 = "";
$future8 = "";
$future9 = "";
// csv settings
$CSV_SEPARATOR = ",";
$CSV_NEWLINE = "\r\n";
$csv_output .= $Orders_id . "," ;
$csv_output .= $Date . "," ;
$csv_output .= $Time . "," ;
$csv_output .= $First_Name . "," ;
$csv_output .= $Last_Name . "," ;
$csv_output .= $Name_On_Card . "," ;
$csv_output .= $Company . "," ;
$csv_output .= $email . "," ;
$csv_output .= $Billing_Address_1 . "," ;
$csv_output .= $Billing_Address_2 . "," ;
$csv_output .= $Billing_City . "," ;
$csv_output .= $Billing_State . "," ;
$csv_output .= $Billing_Zip . "," ;
$csv_output .= $Billing_Country . "," ;
$csv_output .= $Billing_Phone . "," ;
$csv_output .= $ShipTo_First_Name . "," ;
$csv_output .= $ShipTo_Last_Name . "," ;
$csv_output .= $ShipTo_Name . "," ;
$csv_output .= $ShipTo_Company . "," ;
$csv_output .= $ShipTo_Address_1 . "," ;
$csv_output .= $ShipTo_Address_2 . "," ;
$csv_output .= $ShipTo_City . "," ;
$csv_output .= $ShipTo_State . "," ;
$csv_output .= $ShipTo_Zip . "," ;
$csv_output .= $ShipTo_Country . "," ;
$csv_output .= $ShipTo_Phone . "," ;
$csv_output .= $Card_Type . "," ;
$csv_output .= $Card_Number . "," ;
$csv_output .= $Exp_Date . "," ;
$csv_output .= $Bank_Name . "," ;
$csv_output .= $Gateway . "," ;
$csv_output .= $AVS_Code . "," ;
$csv_output .= $Transaction_ID . "," ;
$csv_output .= $Order_Special_Notes . "," ;
$csv_output .= $Comments . "," ;
$csv_output .= $Order_Subtotal . "," ;
$csv_output .= $Order_Tax . "," ;
$csv_output .= $Order_Insurance . "," ;
$csv_output .= $Tax_Exempt_Message . "," ;
$csv_output .= $Order_Shipping_Total . "," ;
$csv_output .= $Small_Order_Fee . "," ;
$csv_output .= $Discount_Rate . "," ;
$csv_output .= $Discount_Message . "," ;
$csv_output .= $CODAmount . "," ;
$csv_output .= $Order_Grand_Total . "," ;
$csv_output .= $Number_of_Items . "," ;
$csv_output .= $Shipping_Method . "," ;
$csv_output .= $Shipping_Weight . "," ;
$csv_output .= $Coupon_Code . "," ;
$csv_output .= $Order_security_msg . "," ;
$csv_output .= $Order_Surcharge_Amount . "," ;
$csv_output .= $Order_Surcharge_Something . "," ;
$csv_output .= $Affiliate_code . "," ;
$csv_output .= $Sentiment_message . "," ;
$csv_output .= $Checkout_form_type . "," ;
$csv_output .= $Card_CVV_value . "," ;
$csv_output .= $future1 . "," ;
$csv_output .= $future2 . "," ;
$csv_output .= $future3 . "," ;
$csv_output .= $future4 . "," ;
$csv_output .= $future5 . "," ;
$csv_output .= $future6 . "," ;
$csv_output .= $future7 . "," ;
$csv_output .= $future8 . "," ;
$csv_output .= $future9 ;
// --------------------    QUERIES 9  ------------------------------------//
//Get list of products ordered
$orders_products = tep_db_query("select products_model, products_price, products_quantity, products_name from orders_products
where orders_id = " . $Orders_id);

// While loop to list the item

while($row_orders_products = mysql_fetch_array($orders_products)) {
$csv_output .= "," . "BEGIN_ITEM". "," ;
$csv_output .= filter_text($row_orders_products[0]) . "," ;
$csv_output .= $row_orders_products[1] . "," ;
$csv_output .= $row_orders_products[2] . "," ;
$csv_output .= filter_text($row_orders_products[3]) . "," ;
$csv_output .= "END_ITEM";

} // end while loop for products

// --------------------------------------------------------------------------//
$csv_output .= "\n";
} // while loop main first

//print
header("Content-Type: application/force-download\n");
header("Cache-Control: cache, must-revalidate");   
header("Pragma: public");
header("Content-Disposition: attachment; filename=ordersexports_" . date("Ymd") . ".csv");
 print $csv_output;
  exit;
}//function main

function filter_text($text) {
$filter_array = array(",","\r","\n","\t");
return str_replace($filter_array,"",$text);
} // function for the filter
?>
