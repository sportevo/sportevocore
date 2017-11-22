<?php
/*
  $Id: create_order.php,v 1 2003/08/17 23:21:34 frankl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/




//http://localhost:8888/catalog/admin/create_order.php?Customer=2



  require('includes/application_top.php');

  // #### Get Available Customers

  $query = tep_db_query("select a.customers_id, a.customers_firstname, a.customers_lastname, b.entry_company, b.entry_city, b.entry_cf, b.entry_piva, c.zone_code from " . TABLE_CUSTOMERS . " AS a, " . TABLE_ADDRESS_BOOK . " AS b LEFT JOIN " . TABLE_ZONES . " as c ON (b.entry_zone_id = c.zone_id) WHERE a.customers_default_address_id = b.address_book_id  ORDER BY entry_company,customers_lastname");
  $result = $query;



  if (tep_db_num_rows($result) > 0){
    // Query Successful
    $SelectCustomerBox = "<select name='Customer'><option value=''>" . TEXT_SELECT_CUST . "</option>\n";

    while($db_Row = tep_db_fetch_array($result)){ 

      $SelectCustomerBox .= "<option value='" . $db_Row['customers_id'] . "'";

      if(isSet($_GET['Customer']) and $db_Row['customers_id']==$_GET['Customer']){

        $SelectCustomerBox .= " SELECTED ";
        $SelectCustomerBox .= ">" . (empty($db_Row['entry_company']) ? "": strtoupper($db_Row['entry_company']) . " - " ) . $db_Row['customers_lastname'] . " , " . $db_Row['customers_firstname'] . " - " . $db_Row['entry_city'] . ", " . $db_Row['zone_code'] . "</option>\n";
      }else{

        $SelectCustomerBox .= ">" . (empty($db_Row['entry_company']) ? "": strtoupper($db_Row['entry_company']) . " - " ) . $db_Row['customers_lastname'] . " , " . $db_Row['customers_firstname'] . " - " . $db_Row['entry_city'] . ", " . $db_Row['zone_code'] . "</option>\n";


      }
    }

    $SelectCustomerBox .= "</select>\n";

          

	$query = tep_db_query("select code, value from " . TABLE_CURRENCIES . " ORDER BY code");
	$result = $query;
	
	if (tep_db_num_rows($result) > 0){
	  // Query Successful
	  $SelectCurrencyBox = "<select name='Currency'><option value=''>" . TEXT_SELECT_CURRENCY . "</option>\n";
	  while($db_Row = tep_db_fetch_array($result)){ 
	    $SelectCurrencyBox .= "<option value='" . $db_Row["code"] . " , " . $db_Row["value"] . "'";

	    if ($db_Row["code"] == DEFAULT_CURRENCY){

	      $SelectCurrencyBox .= " SELECTED ";

	    }

	    $SelectCurrencyBox .= ">" . $db_Row["code"] . "</option>\n";
	  }
	  $SelectCurrencyBox .= "</select>\n";
	}

    

	if(isSet($_GET['Customer'])){
 	  $account_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . $_GET['Customer'] . "'");
 	  $account = tep_db_fetch_array($account_query);
 	  $customer = $account['customers_id'];
 	  $address_query = tep_db_query("select * from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . $_GET['Customer'] . "'");
 	  $address = tep_db_fetch_array($address_query);
	}elseif (isSet($_GET['Customer_nr'])){
 	  $account_query = tep_db_query("select * from " . TABLE_CUSTOMERS . " where customers_id = '" . $_GET['Customer_nr'] . "'");
 	  $account = tep_db_fetch_array($account_query);
 	  $customer = $account['customers_id'];
 	  $address_query = tep_db_query("select * from " . TABLE_ADDRESS_BOOK . " where customers_id = '" . $_GET['Customer_nr'] . "'");
 	  $address = tep_db_fetch_array($address_query);
	}

      

    require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CREATE_ORDER_PROCESS);

  // #### Generate Page
?>

  
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
    <title><?php echo HEADING_TITLE ?></title>
    <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
    <?php require('includes/form_check.js.php'); ?>
  </head>

  <body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
  <!-- header //-->
  <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
  <!-- header_eof //-->		
	
  <!-- body //-->
  <table border="0" width="100%" cellspacing="2" cellpadding="2">
    <tr>
      <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2" class="columnLeft">
        <!-- left_navigation //-->
        <?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
        <!-- left_navigation_eof //-->
  </table></td>
  <!-- body_text //-->

  <td valign="top">
    <table border='0' bgcolor='#7c6bce' width='100%'>
      <tr>
        <td class=main><font color='#ffffff'><b><?php echo TEXT_STEP_1 ?></b></td>
      </tr>
    </table>
    <table border='0' cellpadding='7'><tr><td class="main" valign="top">

      <?php
	    print "<form action='$PHP_SELF' method='GET'>\n";
	    print "<table border='0'>\n";
	    print "<tr>\n";
	    print "<td><br>$SelectCustomerBox</td>\n";
	    print "<td valign='bottom'><input type='submit' value=\"" . BUTTON_SUBMIT . "\"></td>\n";
	    print "</tr>\n";
	    print "</table>\n";
	    print "</form>\n";


  

	    print "<form action='$PHP_SELF' method='GET'>\n";
	    print "<table border='0'>\n";
	    print "<tr>\n";
	    print "<td><font class=main><b><br>" . TEXT_OR_BY . "</b></font><br><br><input type=text name='Customer_nr'></td>\n";
	    print "<td valign='bottom'><input type='submit' value=\"" . BUTTON_SUBMIT . "\"></td>\n";
	    print "</tr>\n";
	    print "</table>\n";
	    print "</form>\n";
      ?>	
    <tr>
      <td width="100%" valign="top"><?php echo tep_draw_form('create_order', FILENAME_CREATE_ORDER_PROCESS, '', 'post', '', '') . tep_draw_hidden_field('customers_id', $account->customers_id); ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
    </tr>

    <tr>
      <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
        <tr>
          <td class="pageHeading"><?php echo HEADING_CREATE; ?></td>
        </tr>
      </table></td>
    </tr>
    <tr>
      <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
    </tr>
    <tr>
      <td>
        <?php
          //onSubmit="return check_form();"
          require(DIR_WS_MODULES . 'create_order_details.php');
        ?>
      </td>
    </tr>
    <tr>
      <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
    </tr>
    <tr>
      <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
        <tr>
          <td class="main"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></td>
          <td class="main" align="right"><?php echo tep_image_submit('button_confirm.gif', IMAGE_BUTTON_CONFIRM); ?></td>
        </tr>
      </table></td>
    </tr>
  </table></form></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php 
require(DIR_WS_INCLUDES . 'application_bottom.php'); 
}
?>