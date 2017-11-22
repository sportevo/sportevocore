<?php
/*
  $Id: exportcustomers.php,v 1.1 April 21, 2006 Harris Ahmed $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/4 Francesco Rossi

  Use this module on your own risk. I will be updating a new one soon. This template is used to create the csv customer card export for MYOB Accounting Australia v18
  
  Written by the fantabulous Shroom
*/

  define('FILENAME_EXPORTCUSTOMERS', 'exportcustomers.php');


require('includes/application_top.php'); 
require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_EXPORTCUSTOMERS);

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
 $orders = tep_db_query("SELECT orders_id, date_purchased, customers_name, cc_owner, customers_company, customers_email_address, billing_street_address, billing_city, billing_state, billing_postcode, billing_country, customers_telephone, payment_method
FROM orders ORDER BY orders_id"); 
// if $start is empty we select all orders up to $end
} else if($start=="" && $end!="") {
 $orders = tep_db_query("SELECT orders_id, date_purchased, customers_name, cc_owner, customers_company, customers_email_address, billing_street_address, billing_city, billing_state, billing_postcode, billing_country, customers_telephone, payment_method
FROM orders WHERE orders_id <= $end ORDER BY orders_id"); 
// if $end is empty we select all orders from $start
} else if($start!="" && $end=="") {
 $orders = tep_db_query("SELECT orders_id, date_purchased, customers_name, cc_owner, customers_company, customers_email_address, billing_street_address, billing_city, billing_state, billing_postcode, billing_country, customers_telephone, payment_method
FROM orders WHERE orders_id >= $start ORDER BY orders_id");
// if both fields are filed in we select orders betwenn $start and $end
} else {
 $orders = tep_db_query("SELECT orders_id, date_purchased, customers_name, cc_owner, customers_company, customers_email_address, billing_street_address, billing_city, billing_state, billing_postcode, billing_country, customers_telephone, payment_method
FROM orders WHERE orders_id >= $start AND orders_id <= $end ORDER BY orders_id");
}
//patch

//$csv_output ="\n";
while ($row_orders = mysql_fetch_array($orders)) { //start one loop
$Company = filter_text($row_orders["customers_company"]);
$customer_name = $row_orders["customers_name"];
list($First_Name, $Last_Name) = split('[ ]', $customer_name);
$CardID = "*None";
$CardStatus = "N";
$Billing_Address_1 = filter_text($row_orders["billing_street_address"]);
$Billing_Address_2 = "";
$Billing_City = filter_text($row_orders["billing_city"]);
$Billing_State = filter_text($row_orders["billing_state"]);
$Billing_Zip = filter_text($row_orders["billing_postcode"]);
$Billing_Country = str_replace("(48 Contiguous Sta", "", $row_orders["billing_country"]);
$Billing_Phone = filter_text($row_orders["customers_telephone"]);
$email = filter_text($row_orders["customers_email_address"]);
$Identifiers = "W";
$CustomList1 = "Retail_EndUser";			
$Terms = "1";
$Discount_Days = "0";
$Balance = "0";
$Discount = "0";
$Monthly_Charge = "0";
$Tax_Code = "GST";
$Credit_Limit = "0";
$Tax_Id = "";
$Volume_Discount = "0";
$Sales_Layout = "I";
$Payment_Method = "";
$Salesperson = "Website";
$Salesperson_Card_ID = "WEB";
$Comment = "We appreciate your business.";
$Shipping = "";
$Printed_Form = "Pre-Printed Invoice";
$Freight_tax = "GST";
$Use_Cust_Tax_Code = "N";
$Invoice_Delivery = "A";


// csv settings
$CSV_SEPARATOR = ",";
$CSV_NEWLINE = "\r\n";
$csv_output .= $Last_Name . "," ;
$csv_output .= $First_Name . "," ;
$csv_output .= $CardID . "," ;
$csv_output .= $CardStatus . "," ;
$csv_output .= $Billing_Address_1 . "," ;
$csv_output .= $Billing_Address_2 . "," ;
$csv_output .= $Billing_Address_3 . "," ;
$csv_output .= $Billing_Address_4 . "," ;
$csv_output .= $Billing_City . "," ;
$csv_output .= $Billing_State . "," ;
$csv_output .= $Billing_Zip . "," ;
$csv_output .= $Billing_Country . "," ;
$csv_output .= $Billing_Phone . "," ;
$csv_output .= $Billing_Phone2 . "," ;
$csv_output .= $Billing_Phone3 . "," ;
$csv_output .= $Billing_Fax . "," ;
$csv_output .= $email . "," ;
$csv_output .= $website . "," ;
$csv_output .= $contactname . "," ;
$csv_output .= $salutation . "," ;
$csv_output .= $B2Address_1 . "," ;
$csv_output .= $B2Address_2 . "," ;
$csv_output .= $B2Address_3 . "," ;
$csv_output .= $B2Address_4 . "," ;
$csv_output .= $B2City . "," ;
$csv_output .= $B2State . "," ;
$csv_output .= $B2Zip . "," ;
$csv_output .= $B2Country . "," ;
$csv_output .= $B2Phone . "," ;
$csv_output .= $B2Phone2 . "," ;
$csv_output .= $B2Phone3 . "," ;
$csv_output .= $B2Fax . "," ;
$csv_output .= $B2email . "," ;
$csv_output .= $B2website . "," ;
$csv_output .= $B2contactname . "," ;
$csv_output .= $B2salutation . "," ;
$csv_output .= $B3Address_1 . "," ;
$csv_output .= $B3Address_2 . "," ;
$csv_output .= $B3Address_3 . "," ;
$csv_output .= $B3Address_4 . "," ;
$csv_output .= $B3City . "," ;
$csv_output .= $B3State . "," ;
$csv_output .= $B3Zip . "," ;
$csv_output .= $B3Country . "," ;
$csv_output .= $B3Phone . "," ;
$csv_output .= $B3Phone2 . "," ;
$csv_output .= $B3Phone3 . "," ;
$csv_output .= $B3Fax . "," ;
$csv_output .= $B3email . "," ;
$csv_output .= $B3website . "," ;
$csv_output .= $B3contactname . "," ;
$csv_output .= $B3salutation . "," ;
$csv_output .= $B4Address_1 . "," ;
$csv_output .= $B4Address_2 . "," ;
$csv_output .= $B4Address_3 . "," ;
$csv_output .= $B4Address_4 . "," ;
$csv_output .= $B4City . "," ;
$csv_output .= $B4State . "," ;
$csv_output .= $B4Zip . "," ;
$csv_output .= $B4Country . "," ;
$csv_output .= $B4Phone . "," ;
$csv_output .= $B4Phone2 . "," ;
$csv_output .= $BBPhone3 . "," ;
$csv_output .= $B4Fax . "," ;
$csv_output .= $B4email . "," ;
$csv_output .= $B4website . "," ;
$csv_output .= $B4contactname . "," ;
$csv_output .= $B4salutation . "," ;
$csv_output .= $B5Address_1 . "," ;
$csv_output .= $B5Address_2 . "," ;
$csv_output .= $B5Address_3 . "," ;
$csv_output .= $B5Address_4 . "," ;
$csv_output .= $B5City . "," ;
$csv_output .= $B5State . "," ;
$csv_output .= $B5Zip . "," ;
$csv_output .= $B5Country . "," ;
$csv_output .= $B5Phone . "," ;
$csv_output .= $B5Phone2 . "," ;
$csv_output .= $B5Phone3 . "," ;
$csv_output .= $B5Fax . "," ;
$csv_output .= $B5email . "," ;
$csv_output .= $B5website . "," ;
$csv_output .= $B5contactname . "," ;
$csv_output .= $B5salutation . "," ;
$csv_output .= $Picture . "," ;
$csv_output .= $Notes . "," ;
$csv_output .= $Identifiers . "," ;
$csv_output .= $CustomList1 . "," ;
$csv_output .= $CustomList2 . "," ;
$csv_output .= $CustomList3 . "," ;
$csv_output .= $CustomField1 . "," ;
$csv_output .= $CustomField2 . "," ;
$csv_output .= $CustomField3 . "," ;
$csv_output .= $Terms . "," ;
$csv_output .= $Discount_Days . "," ;
$csv_output .= $Balance . "," ;
$csv_output .= $Discount . "," ;
$csv_output .= $Monthly_Charge . "," ;
$csv_output .= $Tax_Code . "," ;
$csv_output .= $Credit_Limit . "," ;
$csv_output .= $Tax_Id . "," ;
$csv_output .= $Volume_Discount . "," ;
$csv_output .= $Sales_Layout . "," ;
$csv_output .= $Payment_Method . "," ;
$csv_output .= $Payment_Notes . "," ;
$csv_output .= $Card_Name . "," ;
$csv_output .= $Card_Number . "," ;
$csv_output .= $Card_Expiry . "," ;
$csv_output .= $BSB . "," ;
$csv_output .= $Account_Number . "," ;
$csv_output .= $Account_Name . "," ;
$csv_output .= $ABN . "," ;
$csv_output .= $ABN_Branch . "," ;
$csv_output .= $Account . "," ;
$csv_output .= $Salesperson . "," ;
$csv_output .= $Salesperson_Card_ID . "," ;
$csv_output .= $Comment . "," ;
$csv_output .= $Shipping . "," ;
$csv_output .= $Printed_Form . "," ;
$csv_output .= $Freight_tax . "," ;
$csv_output .= $Use_Cust_Tax_Code . "," ;
$csv_output .= $Receipt_Memo . "," ;
$csv_output .= $Invoice_Delivery . "," ;
$csv_output .= $Record_ID . "," ;
// --------------------------------------------------------------------------//
$csv_output .= "\r";
} // while loop main first

//print
header("Content-Type: application/force-download\n");
header("Cache-Control: cache, must-revalidate");   
header("Pragma: public");
header("Content-Disposition: attachment; filename=customerexports_" . date("Ymd") . ".txt");
 print $csv_output;
  exit;
}//function main

function filter_text($text) {
$filter_array = array(",","\r","\n","\t");
return str_replace($filter_array,"",$text);
} // function for the filter
?>
