<?php
/*
  $Id: product_info.php,v 1.97 2003/07/01 14:34:54 hpdl Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_PRODUCT_INFO);
  
  
  // Start Products Specifications
  require_once (DIR_WS_FUNCTIONS . 'products_specifications.php');

  // Handle the output of the Ask a Question form
  $from_name = '';
  $from_email_address = '';
  $message = '';
  $error_string = '';
  if (isset($_GET['action']) && ($_GET['action'] == 'process') ) {
    $error = false;

    $to_email_address = STORE_OWNER_EMAIL_ADDRESS;
    $to_name = STORE_OWNER;
    $from_email_address = tep_db_prepare_input ($_POST['from_email_address']);
    $from_name = tep_db_prepare_input ($_POST['from_name']);
    $message = tep_db_prepare_input ($_POST['message']);

    if (empty ($from_name) ) {
      $error_string .= 'name';
      $error = true;
    }

    if (!tep_validate_email($from_email_address)) {
      if ($error == true) {
        $error_string .= '-';
      }
      $error_string .= 'email';
    }

    if ($error == false) {
      $email_subject = sprintf (TEXT_EMAIL_SUBJECT, $from_name, STORE_NAME);
      $email_body = sprintf (TEXT_EMAIL_INTRO, $to_name, $from_name, $product_info['products_name'], $product_info['products_model'], STORE_NAME) . "\n\n";

      if (tep_not_null($message)) {
        $email_body .= $message . "\n\n";
      }

      $email_body .= sprintf (TEXT_EMAIL_LINK, tep_href_link (FILENAME_PRODUCT_INFO, 'products_id=' . $_GETS['products_id']) ) . "\n\n" .
                     sprintf (TEXT_EMAIL_SIGNATURE, STORE_NAME . "\n" . HTTP_SERVER . DIR_WS_CATALOG . "\n");

      tep_mail ($to_name, $to_email_address, $email_subject, $email_body, $from_name, $from_email_address);

      $messageStack->add_session ('header', sprintf (TEXT_EMAIL_SUCCESSFUL_SENT, $product_info['products_name'], tep_output_string_protected ($to_name) ), 'success');

      tep_redirect (tep_href_link (FILENAME_PRODUCT_INFO, tep_get_all_get_params (array ('action', 'tab') ) . 'action=success&tab=ASK'));
    } else {
      tep_redirect (tep_href_link (FILENAME_PRODUCT_INFO, tep_get_all_get_params (array ('action', 'tab') ) . 'tab=ASK&error=' . $error_string));
    }
    
  } elseif (tep_session_is_registered ('customer_id') ) {
    $account_query = tep_db_query ("select customers_firstname, 
                                           customers_lastname, 
                                           customers_email_address 
                                   from " . TABLE_CUSTOMERS . " 
                                   where customers_id = '" . (int) $customer_id . "'
                                 ");
    $account = tep_db_fetch_array($account_query);

    $from_name = $account['customers_firstname'] . ' ' . $account['customers_lastname'];
    $from_email_address = $account['customers_email_address'];
  }

  // Handle errors -- missing name or invalid email. We don't check the message field.
  if (isset ($_GET['error']) && $_GET['error'] != '') {
    $error_array = explode ('-', $_GET['error']);
    for ($index=0, $end=count($error_array); $index<$end; $index++) {
      if ($error_array[$index] == 'name') {
        $messageStack->add ('ask', ERROR_FROM_NAME);
      } else {
        $messageStack->add ('ask', ERROR_FROM_ADDRESS);
      } // if ($error_array .... else ...
    } // for ($index=0
  } // if (isset
// End Products Specifications

  $product_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$_GET['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
  $product_check = tep_db_fetch_array($product_check_query);
//added by admin  showing the master for slave products
$master_query1 = tep_db_query("select products_id,products_master from " . TABLE_PRODUCTS . " where products_id =  '" . (int)$_GET['products_id'] . " '");
$results1 = tep_db_fetch_array($master_query1);
if (($results1['products_master'] != null) &&($results1['products_master'] != 0) ) {
$_GET['products_id']=(int)$results1['products_master'];
}
//end of code added by admin  showing the master for slave products
 $product_master_status_query = tep_db_query ("select products_master_status from " . TABLE_PRODUCTS . " where products_id =  '" . (int)$_GET['products_id'] . "'");
  $product_master_result = tep_db_fetch_array($product_master_status_query);
  $product_master = $product_master_result['products_master_status'];
  
  
  // BOF Separate Pricing per Customer
  if (isset($_SESSION['sppc_customer_group_id']) && $_SESSION['sppc_customer_group_id'] != '0') {
    $customer_group_id = $_SESSION['sppc_customer_group_id'];
  } else {
    $customer_group_id = '0';
  }
// EOF Separate Pricing per Customer

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>

<link rel="stylesheet" type="text/css" href="stylesheet.css">


<?php
// Start Products Specifications
  if (SPECIFICATIONS_BOX_FRAME_STYLE == 'Tabs') {
?>
  <link href="style_tabs.css" rel="stylesheet" type="text/css">
  <script language="javascript" type="text/javascript" src="includes/functions/jquery-1.3.2.min.js"></script> 
  <script language="javascript" type="text/javascript">
    $(document).ready(function(){  
      initTabs();  
    });  
  
    function initTabs() {  
      $('#tabMenu a').bind('click',function(e) {  
      e.preventDefault();  
      var thref = $(this).attr("href").replace(/#/, '');  
      $('#tabMenu a').removeClass('active');  
      $(this).addClass('active');  
      $('#tabContent div.content').removeClass('active');  
      $('#'+thref).addClass('active');  
      });  
    }  
  </script> 
<?php
  }
// End Products Specifications
?>


<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>

</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<?php /*** Begin Header Tags SEO ***/ ?>
<a name="<?php echo $header_tags_array['title']; ?>"></a>
<?php /*** End Header Tags SEO ***/ ?>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
<td width="100%" valign="top"><?php // Products Specifications echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_PRODUCT_INFO, tep_get_all_get_params(array('action')) . 'action=add_product')); ?><table border="0" width="100%" cellspacing="0" cellpadding="0">

<!-- Master Products //-->
    <td width="100%" valign="top"><?php if ($product_master['product_master_status']!= 1) {  echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_PRODUCT_INFO, tep_get_all_get_params(array('action')) . 'action=add_product')); } ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
<!-- Master Products EOF //-->
    </table></td>
<!-- body_text //-->
    <!-- Master Products //-->
<td width="100%" valign="top"><?php  echo '<form name="buy_now_" method="post" action="' . tep_href_link(basename($PHP_SELF), tep_get_all_get_params(array('action')) . 'action=add_slave', 'NONSSL') . '">'; ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
<?php
// Master Products EOF
  if ($product_check['total'] < 1) {
?>
      <tr>
        <td><?php new infoBox(array(array('text' => TEXT_PRODUCT_NOT_FOUND))); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></td>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
<?php
  } else {
 
 
  	
    //BOF UltraPics
//BOF Original
/*
    //Master Products
$product_info_query = tep_db_query("select p.products_id, pd.products_name, pd.products_description, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_master_status, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$_GET['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
//Master Products EOF
*/
//EOF Original
    $product_info_query = tep_db_query("select p.products_id, pd.products_name, pd.products_description, p.products_model, p.products_quantity, p.products_image, p.products_image_med, p.products_image_lrg, p.products_image_sm_1, p.products_image_xl_1, p.products_image_sm_2, p.products_image_xl_2, p.products_image_sm_3, p.products_image_xl_3, p.products_image_sm_4, p.products_image_xl_4, p.products_image_sm_5, p.products_image_xl_5, p.products_image_sm_6, p.products_image_xl_6, pd.products_url, p.products_price,  p.products_master_status, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.products_bundle, p. sold_in_bundle_only, p.manufacturers_id, pd.products_tab_1, pd.products_tab_2, pd.products_tab_3, pd.products_tab_4, pd.products_tab_5, pd.products_tab_6 from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$_GET['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
//EOF UltraPics
$product_info = tep_db_fetch_array($product_info_query);

    tep_db_query("update " . TABLE_PRODUCTS_DESCRIPTION . " set products_viewed = products_viewed+1 where products_id = '" . (int)$_GET['products_id'] . "' and language_id = '" . (int)$languages_id . "'");

    if ($new_price = tep_get_products_special_price($product_info['products_id'])) {
     // BOF Separate Pricing per Customer
      if ($customer_group_id > 0) { // only need to check products_groups if customer is not retail
        $scustomer_group_price_query = tep_db_query("select customers_group_price from " . TABLE_PRODUCTS_GROUPS . " where products_id = '" . (int)$_GET['products_id']. "' and customers_group_id =  '" . $customer_group_id . "'");
        if ($scustomer_group_price = tep_db_fetch_array($scustomer_group_price_query)) {
          $product_info['products_price']= $scustomer_group_price['customers_group_price'];
	      }
      } // end if ($customer_group_id > 0)
// EOF Separate Pricing per Customer
if ($products_price > 0) {
      $products_price = '<s>' . $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</s> <span class="productSpecialPrice">' . $currencies->display_price($new_price, tep_get_tax_rate($product_info['products_tax_class_id'])) . '</span>';
    } 
	}
else {
// BOF Separate Pricing per Customer
      if ($customer_group_id > 0) { // only need to check products_groups if customer is not retail
        $scustomer_group_price_query = tep_db_query("select customers_group_price from " . TABLE_PRODUCTS_GROUPS . " where products_id = '" . (int)$_GET['products_id']. "' and customers_group_id =  '" . $customer_group_id . "'");
        if ($scustomer_group_price = tep_db_fetch_array($scustomer_group_price_query)) {
        $product_info['products_price']= $scustomer_group_price['customers_group_price'];
	    }
    } // end if ($customer_group_id > 0)
// EOF Separate Pricing per Customer
      $products_price = $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id']));
    }

    if (tep_not_null($product_info['products_model'])) {
      $products_name = $product_info['products_name'] . '<br><span class="smallText">[' . $product_info['products_model'] . ']</span>';
    } else {
      $products_name = $product_info['products_name'];
    }
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <?php /*** Begin Header Tags SEO ***/ ?>
            <td valign="top"><h1><?php echo $products_name; ?></h1></td>
            <td align="right" valign="top"><h1><?php echo (($product_info['products_price'] > 0) ? $products_price : ''); ?></h1></td>
            <?php /*** End Header Tags SEO ***/ ?>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main">
<?php
    if (tep_not_null($product_info['products_image'])) {
?>
          <table border="0" cellspacing="0" cellpadding="2" align="right">
            <tr>
              <td align="center" class="smallText">
              <!--BOF UltraPics-->
<?php
	if ($product_info['products_image_med']!='') {
		$new_image = $product_info['products_image_med'];
		$image_width = MEDIUM_IMAGE_WIDTH;
		$image_height = MEDIUM_IMAGE_HEIGHT;
	} else {
		$new_image = $product_info['products_image'];
		$image_width = SMALL_IMAGE_WIDTH;
		$image_height = SMALL_IMAGE_HEIGHT;
	}

?>
<!--EOF UltraPics-->

<script language="javascript"><!--
//BOF UltraPics
//BOF Original
/*
document.write('<?php echo '<a href="javascript:popupWindow(\\\'' . tep_href_link(FILENAME_POPUP_IMAGE, 'pID=' . $product_info['products_id']) . '\\\')">' . tep_image(DIR_WS_IMAGES . $product_info['products_image'], addslashes($product_info['products_name']), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '<br>' . TEXT_CLICK_TO_ENLARGE . '</a>'; ?>');
*/
//EOF Original
document.write('<?php echo '<a href="images/' . $product_info['products_image'] . '" rel="lightbox" title="' . $product_info['products_name'] . '">' . tep_image(DIR_WS_IMAGES . $product_info['products_image'], addslashes($product_info['products_name']), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '<br>' . TEXT_CLICK_TO_ENLARGE . '</a>'; ?>');

//EOF UltraPics//--></script>
<noscript>
<!--BOF UltraPics-->
<!--BOF Original--><!--
<?php echo '<a href="' . tep_href_link(DIR_WS_IMAGES . $product_info['products_image']) . '" target="_blank">' . tep_image(DIR_WS_IMAGES . $product_info['products_image'], $product_info['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '<br>' . TEXT_CLICK_TO_ENLARGE . '</a>'; ?>
--><!--EOF Original-->
<?php echo '<a href="' . tep_href_link(DIR_WS_IMAGES . $product_info['products_image_med']) . '">' . tep_image(DIR_WS_IMAGES . $new_image . '&image=0', addslashes($product_info['products_name']), $image_width, $image_height, 'hspace="5" vspace="5"') . '<br>' . tep_image_button('image_enlarge.gif') . '</a>'; ?>
<!--EOF UltraPics--></noscript>
<br><br>
  <?php

	     ?>

              </td>
            </tr>
          </table>
<?php
    }
?>
          <p><?php echo stripslashes($product_info['products_description']); ?></p>
          <!-- BOF Bundled Products-->          
          <?php
          function display_bundle($bundle_id, $bundle_price) {
            global $languages_id, $product_info, $currencies;
          ?>
          <table border="0" width="95%" cellspacing="1" cellpadding="2" class="infoBox">
            <tr class="infoBoxContents">
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="main" colspan="5"><b>
                    <?php
                  $bundle_sum = 0;
		              echo TEXT_PRODUCTS_BY_BUNDLE . "</b></td></tr>\n";
		              $bundle_query = tep_db_query(" SELECT pd.products_name, pb.*, p.products_bundle, p.products_id, p.products_model, p.products_price, p.products_image FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON p.products_id=pd.products_id INNER JOIN " . TABLE_PRODUCTS_BUNDLES . " pb ON pb.subproduct_id=pd.products_id WHERE pb.bundle_id = " . (int)$bundle_id . " and language_id = '" . (int)$languages_id . "'");
		              while ($bundle_data = tep_db_fetch_array($bundle_query)) {
	                  echo "<tr><td class=main valign=top>" ;
	                  echo '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, ($cPath ? 'cPath=' . $cPath . '&' : '') . 'products_id=' . $bundle_data['products_id']) . '" target="_blank">' . tep_image(DIR_WS_IMAGES . $bundle_data['products_image'], $bundle_data['products_name'], intval(SMALL_IMAGE_WIDTH / 2), intval(SMALL_IMAGE_HEIGHT / 2), 'hspace="1" vspace="1"') . '</a></td>';
	                  // comment out the following line to hide the subproduct qty
	                  echo "<td class=main align=right><b>" . $bundle_data['subproduct_qty'] . "&nbsp;x&nbsp;</b></td>";
	                  echo  '<td class=main><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, ($cPath ? 'cPath=' . $cPath . '&' : '') . 'products_id=' . $bundle_data['products_id']) . '" target="_blank"><b>&nbsp;(' . $bundle_data['products_model'] . ') '  . $bundle_data['products_name'] . '</b></a>';
	                  if ($bundle_data['products_bundle'] == "yes") display_bundle($bundle_data['subproduct_id'], $bundle_data['products_price']);
	                  echo '</td>';
	                  echo '<td align=right class=main><b>&nbsp;' .  $currencies->display_price($bundle_data['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . "</b></td></tr>\n";
	                  $bundle_sum += $bundle_data['products_price']*$bundle_data['subproduct_qty'];
		              }
		              $bundle_saving = $bundle_sum - $bundle_price;
		              $bundle_sum = $currencies->display_price($bundle_sum, tep_get_tax_rate($product_info['products_tax_class_id']));
		              $bundle_saving =  $currencies->display_price($bundle_saving, tep_get_tax_rate($product_info['products_tax_class_id']));
		              // comment out the following line to hide the "saving" text
		              echo "<tr><td colspan=5 class=main><p><b>" . TEXT_RATE_COSTS . '&nbsp;' . $bundle_sum . '</b></td></tr><tr><td class=main colspan=5><font color="red"><b>' . TEXT_IT_SAVE . '&nbsp;' . $bundle_saving . "</font></b></td></tr>\n";
		            ?>
              </table></td>
            </tr>
          </table>
          <?php
          }
          if ($product_info['products_bundle'] == "yes") {
            display_bundle($_GET['products_id'], $product_info['products_price']);
          }
          if ($product_info['sold_in_bundle_only'] == "yes") {
            echo '<p class="main"><b>' . TEXT_SOLD_IN_BUNDLE . '</b></p><blockquote class="main">';
            $bquery = tep_db_query('select bundle_id from ' . TABLE_PRODUCTS_BUNDLES . ' where subproduct_id = ' . (int)$_GET['products_id']);
            while ($bid = tep_db_fetch_array($bquery)) {
              $binfo_query = tep_db_query('select p.products_model, pd.products_name from ' . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int)$bid['bundle_id'] . "' and pd.products_id = p.products_id and pd.language_id = " . (int)$languages_id);
              $binfo = tep_db_fetch_array($binfo_query);
              echo '<a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . (int)$bid['bundle_id']) . '" target="_blank">[' . $binfo['products_model'] . '] ' . $binfo['products_name'] . '</a><br />';
            }
            echo '</blockquote>';
          }
          ?>
      <!-- EOF Bundled Products-->
<?php
// Start Products Specifications 
    include_once (DIR_WS_MODULES . FILENAME_PRODUCTS_SPECIFICATIONS);
    echo tep_draw_form ('cart_quantity', tep_href_link (FILENAME_PRODUCT_INFO, tep_get_all_get_params (array ('action') ) . 'action=add_product') ); 
// End Products Specifications 
?>

<?php

// BOF SPPC Hide attributes from customer groups
    $products_attributes_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$_GET['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "' and find_in_set('".$customer_group_id."', attributes_hide_from_groups) = 0 ");
    $products_attributes = tep_db_fetch_array($products_attributes_query);
    if ($products_attributes['total'] > 0) {
?>
          <table border="0" cellspacing="0" cellpadding="2">

            <tr> 
              <td class="main" colspan="2"><?php echo TEXT_PRODUCT_OPTIONS; ?></td>
            </tr>
  <?php
// Start Products Specifications 
?>
          <table border="0" cellspacing="0" cellpadding="2" align="left" width="100%">
            <tr>
              <td>
<?php
// End Products Specifications 
?>          
<?php

      $products_options_name_query = tep_db_query("select distinct popt.products_options_id, popt.products_options_name, popt.products_options_images_enabled from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$_GET['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "' and find_in_set('".$customer_group_id."', attributes_hide_from_groups) = 0 order by popt.products_options_name");
      while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
        $products_options_array = array();
        $products_options_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix, pa.products_attributes_id from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$_GET['products_id'] . "' and pa.options_id = '" . (int)$products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$languages_id . "' and find_in_set('".$customer_group_id."', attributes_hide_from_groups) = 0");
		$list_of_prdcts_attributes_id = '';
		$products_options = array(); // makes sure this array is empty again
        while ($_products_options = tep_db_fetch_array($products_options_query)) {
		$products_options[] = $_products_options;
		$list_of_prdcts_attributes_id .= $_products_options['products_attributes_id'].",";
	}

      if (tep_not_null($list_of_prdcts_attributes_id) && $customer_group_id != '0') { 
         $select_list_of_prdcts_attributes_ids = "(" . substr($list_of_prdcts_attributes_id, 0 , -1) . ")";
	 $pag_query = tep_db_query("select products_attributes_id, options_values_price, price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES_GROUPS . " where products_attributes_id IN " . $select_list_of_prdcts_attributes_ids . " AND customers_group_id = '" . $customer_group_id . "'");
	 while ($pag_array = tep_db_fetch_array($pag_query)) {
		 $cg_attr_prices[] = $pag_array;
	 }

	 // substitute options_values_price and prefix for those for the customer group (if available)
	 if ($customer_group_id != '0' && tep_not_null($cg_attr_prices)) {
	    for ($n = 0 ; $n < count($products_options); $n++) {
		 for ($i = 0; $i < count($cg_attr_prices) ; $i++) {
			 if ($cg_attr_prices[$i]['products_attributes_id'] == $products_options[$n]['products_attributes_id']) {
				$products_options[$n]['price_prefix'] = $cg_attr_prices[$i]['price_prefix'];
				$products_options[$n]['options_values_price'] = $cg_attr_prices[$i]['options_values_price'];
			 }
		 } // end for ($i = 0; $i < count($cg_att_prices) ; $i++)
	    }
        } // end if ($customer_group_id != '0' && (tep_not_null($cg_attr_prices))
      } // end if (tep_not_null($list_of_prdcts_attributes_id) && $customer_group_id != '0')

   for ($n = 0 ; $n < count($products_options); $n++) {
          $products_options_array[] = array('id' => $products_options[$n]['products_options_values_id'], 'text' => $products_options[$n]['products_options_values_name']);
          if ($products_options[$n]['options_values_price'] != '0') {
            $products_options_array[sizeof($products_options_array)-1]['text'] .= ' (' . $products_options[$n]['price_prefix'] . $currencies->display_price($products_options[$n]['options_values_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) .') ';
          }
        }
// EOF SPPC attributes mod

        if (isset($cart->contents[$_GET['products_id']]['attributes'][$products_options_name['products_options_id']])) {
          $selected_attribute = $cart->contents[$_GET['products_id']]['attributes'][$products_options_name['products_options_id']];
        } else {
          $selected_attribute = false;
        }
?>
            <tr>
              <td class="main"><?php echo $products_options_name['products_options_name'] . ':'; ?></td>
              
              
              <?php if (OPTIONS_AS_IMAGES_ENABLED == 'false')
{ 
?>
  <td class="main"><?php echo tep_draw_pull_down_menu('id[' . $products_options_name['products_options_id'] . ']', $products_options_array, $selected_attribute); ?></td>
            </tr>
<?php 
}
else
{
if ($products_options_name['products_options_images_enabled'] == 'true'){
?>
<td class="main"><?php echo tep_draw_pull_attribute_down_menu('id[' . $products_options_name['products_options_id'] . ']', $products_options_array, $selected_attribute, 'onChange="sendRequestSearch(this.value);"'); ?></td>
<?php
}
else
{
?>
<td class="main"><?php echo tep_draw_pull_attribute_down_menu('id[' . $products_options_name['products_options_id'] . ']', $products_options_array, $selected_attribute); ?></td>
<?php
}
?>
</tr>
<?php
}
?>           
<?php
      }
?>
         <tr>
       <td></td>  <td> <?php if (OPTIONS_AS_IMAGES_ENABLED == 'true'){ ?>
<div id="zoek_resultaten"></div>
<?php 
}
else
{
}
?></td>
</tr> </table>
<?php
    }
?>
        </td>
      </tr>
      <!--BOF UltraPics-->
<?php
	if (ULTIMATE_ADDITIONAL_IMAGES == 'enable') include(DIR_WS_MODULES . 'additional_images.php');
?> 
<!--EOF UltraPics-->

      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
  //  $reviews_query = tep_db_query("select count(*) as count from " . TABLE_REVIEWS . " where products_id = '" . (int)$_GET['products_id'] . "'");
  //  $reviews = tep_db_fetch_array($reviews_query);
  //  if ($reviews['count'] > 0) {
?>
      <tr>
        <td class="main"><?php echo TEXT_CURRENT_REVIEWS . ' ' . $reviews['count']; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
    }

    if (tep_not_null($product_info['products_url'])) {
?>
      <tr>
        <td class="main"><?php echo sprintf(TEXT_MORE_INFORMATION, tep_href_link(FILENAME_REDIRECT, 'action=url&goto=' . urlencode($product_info['products_url']), 'NONSSL', true, false)); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
    }

    if ($product_info['products_date_available'] > date('Y-m-d H:i:s')) {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_DATE_AVAILABLE, tep_date_long($product_info['products_date_available'])); ?></td>
      </tr>
<?php
    } else {
?>
      
<?php
    }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
        
        
      </tr>
      <?php

$master_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_master LIKE  '%" . $_GET['products_id'] . "%'");
$results = tep_db_fetch_array($master_query);
if (($results['products_id'] != null) && ($product_info['products_master_status'] == 1)) { ?>

      <tr>
      	<td align="left" class="main">&nbsp;<?php echo TEXT_SLAVE_PRODUCTS; ?></td>
</tr>
<tr>
<td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
</tr>
<?php
}

?>
<tr>
<td><?php  include(DIR_WS_MODULES . FILENAME_MASTER_PRODUCTS); ?></td>
      	
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                <td class="main"><?php echo '<a href="' . tep_href_link(FILENAME_PRODUCT_REVIEWS, tep_get_all_get_params()) . '">' . tep_image_button('button_reviews.gif', IMAGE_BUTTON_REVIEWS) . '</a>'; ?></td>
                <!--<td class="main" align="right"><?php //echo tep_draw_hidden_field('products_id', $product_info['products_id']) . tep_image_submit('button_in_cart.gif', IMAGE_BUTTON_IN_CART); ?> -->
	<!-- Start "Hide Price if $0" edit 1 of 2 (uncomment above line if removing this contribution) -->
	<?php if ($product_master['product_master_status']!= 1) { ?> 

	
	<td class="main" align="right"><?php echo tep_draw_hidden_field('products_id', $product_info['products_id']) ;//. tep_image_submit('button_in_cart.gif', IMAGE_BUTTON_IN_CART); ?><?php if ($product_info['sold_in_bundle_only'] == "yes") {
          echo TEXT_BUNDLE_ONLY;
        } elseif ((STOCK_CHECK == 'true') && (STOCK_ALLOW_CHECKOUT != 'true') && ($qty < 1)) {
          echo tep_image_button('button_out_of_stock.gif', IMAGE_BUTTON_OUT_OF_STOCK);
        } else {  echo (($product_info['products_price'] > 0) ? tep_image_submit('button_in_cart.gif', IMAGE_BUTTON_IN_CART) : ''); } ?>
        
	<!-- End "Hide Price if $0" edit 1 of 2 -->
</td>
           <?php
}
?>      <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <!--- BEGIN Header Tags SEO Social Bookmarks -->
    <?php
// Start Products Specifications 
?>
            </td>
          </tr>
        </table></form></td>
<?php
// End Products Specifications 
?> 
      <!--- END Header Tags SEO Social Bookmarks -->
      <tr>
        <td>
        	
      <?php

$master_query = tep_db_query("select products_id from " . TABLE_PRODUCTS . " where products_master =  '" . (int)$_GET['products_id'] . "'");
$results = tep_db_fetch_array($master_query);
if ($results['products_id'] != null) { ?>

   <tr>
    <td align="left" class="main">&nbsp;<?php echo TEXT_SLAVE_PRODUCTS; ?></td>
   </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>   
   <tr>
    <td><?php include(DIR_WS_MODULES . FILENAME_MASTER_PRODUCTS); ?></td>
   </tr>
   
<?php    
  }     
?>
<!-- Master Products EOF //-->    	
<?php
    if ((USE_CACHE == 'true') && empty($SID)) {
      echo tep_cache_also_purchased(3600);
    } else {
      include(DIR_WS_MODULES . FILENAME_ALSO_PURCHASED_PRODUCTS);
    }
    include(DIR_WS_MODULES . FILENAME_PRODUCT_SETS);

  //}
?>
        </td>
      </tr>
    <?php
// Start Products Specifications 
?>
        </table></td>
<?php
// End Products Specifications 
?>  
    </table></form> <table width="794" border="0" align="center" cellpadding="0" cellspacing="0">
      <tr>
       <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
       <td class="smallText" align="center">$text_viewing&nbsp; $text_viewing_title</td>
      </tr>
      <!--- BEGIN Header Tags SEO Social Bookmarks -->
     </table>
</td>
<!-- body_text_eof //-->
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
    </table></td>
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
