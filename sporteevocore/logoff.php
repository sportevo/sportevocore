<?php
/*
  $Id: logoff.php 1739 2007-12-20 00:52:16Z hpdl $
  adapted for Separate Pricing Per Customer v4.2 2005/05/16

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_LOGOFF);

  $breadcrumb->add(NAVBAR_TITLE);

  tep_session_unregister('customer_id');
  tep_session_unregister('customer_default_address_id');
  tep_session_unregister('customer_first_name');
// BOF Separate Pricing Per Customer
  tep_session_unregister('sppc_customer_group_id');
  tep_session_unregister('sppc_customer_group_show_tax');
  tep_session_unregister('sppc_customer_group_tax_exempt');
  if (tep_session_is_registered('sppc_customer_specific_taxes_exempt')) { tep_session_unregister('sppc_customer_specific_taxes_exempt');
  }
// EOF Separate Pricing Per Customer
  tep_session_unregister('customer_country_id');
  tep_session_unregister('customer_zone_id');
  tep_session_unregister('comments');

  $cart->reset();
   //Facebook Connect
  include 'includes/classes/facebook.php';
  include_once "fbconnect.php";
  setcookie('fbs_'.$facebook->getAppId(), '', time()-100, '/', '.schermaontc.com');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>

<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>

<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->


<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
   
<!-- body_text //-->
    
                <span class="pageHeading" align="center"><?php echo HEADING_TITLE; ?></span>
              
                <div class="maincapture"><?php echo TEXT_MAIN; ?></div>
             <div class="accountBox">
          <div class="accountBoxContents">
           
                <div><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></div>
              
          </div>
       
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
   
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->

</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
