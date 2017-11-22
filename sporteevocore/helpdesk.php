<?php
/*
   $Id: support_track.php,v 1.3 2003/02/05 12:55:51 puddled Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/2 Puddled Computer Services
  Contributed by Puddled Computer services
  http://www.puddled.co.uk

  Author David Howarth
  Email dave@puddled.co.uk

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  
if (!tep_session_is_registered('customer_id')) {
  if (isset ($_GET['action']) or isset ($_GET['view'])){
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
} else {
	if (!$_GET['action']){
  	
  	if (!$_GET['view']){
global $customer_id; 
$customer_query = tep_db_query("select customers_email_address, customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " where customers_id =  '" . $customer_id . "'");
$customer_info = tep_db_fetch_array($customer_query);
$customer_email = $customer_info['customers_email_address'];
$customer_name = $customer_info['customers_firstname'] . ' ' . $customer_info['customers_lastname'];

//echo "name = $customer_name email=$customer_email";
//echo "<br>select customers_email_address, customers_firstname, customers_lastname from " . TABLE_CUSTOMERS . " where customers_id =  '" . $customer_id . "'";
//exit;
  	}
  }
}



if (!$_GET['action']){
  	
  	if (!$_GET['view']){
  		$_GET['action'] = 'main';
  	}
  	else{
      $_GET['action'] = 'show_tickets';
    }
  }
if ($_GET['action']) {
    switch ($_GET['action']) {
    case 'show_tickets':
    break;

    case 'edit_ticket':
          $ticket_details = tep_db_query("select * from " . TABLE_SUPPORT_TICKETS . " where ticket_id = '" . $_GET['ticket_id'] . "' and customers_id = '" . $customer_id . "'");
          $ticket = tep_db_fetch_array($ticket_details);

          $ticket_history = tep_db_query("SELECT * FROM " . TABLE_TICKET_HISTORY . " where ticket_id = '" .$_GET['ticket_id'] . "'");
          $history = tep_db_fetch_array($ticket_history);

}
}

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_SUPPORT);
  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_SUPPORT, '', 'NONSSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (getenv('HTTPS') == 'on' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
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
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top">
    	
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
       <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_account.gif', HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php   include(DIR_WS_MODULES . 'support_menu.php'); ?>	</td>
            </tr>
			

<?php
      if ($_GET['action'] == 'show_tickets') {
          include(DIR_WS_MODULES . 'support_track.php');



?>
		<tr>
			<td><?php echo tep_draw_separator('pixel_trans.gif', '20', '20'); ?></td>
		</tr>


	   
      <!-- end new insert here --//-->
    <?
} else {
?>
<tr><td><P>&nbsp;</p>
<table width=100% border="0" cellspacing="1" cellpadding="4" bgcolor="#336699"><tr bgcolor="FFFFFF"><td class="infoBoxContents" >

<?php echo TEXT_MAIN_SUPPORT; ?></p>
</td></tr></table>

</td></tr>

<?
}
 // new insert to allow editing of support tickets, once submitted

?>


        </table>
        </td>
      </tr>

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
