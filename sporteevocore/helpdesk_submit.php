<?php
/*
  $Id: helpdesk_login.php,v 1.10

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/2 Francesco Rossi

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_HELPDESK_LOGIN);

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_HELPDESK_LOGIN, '', 'NONSSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo $breadcrumb->trail_title(' &raquo; '); ?></title>
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
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
<tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_specials.gif', HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
if($submit != "true") {
?>
      <tr>
        <td><br><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main">
              <p align="center"><?php echo TEXT_INFORMATION; ?></p>
            </td>
          </tr>
          <tr>
            <td class="main">
              <p align="center"><?php echo tep_draw_form('account_edit', tep_href_link(FILENAME_HELPDESK_LOGIN, '', 'NONSSL'), 'post') . tep_draw_hidden_field('action', 'process'); ?><input type="hidden" name="submit" value="true">
<div align="center">
  <center>
<table border=0 cellpadding=1 cellspacing=5 width="90%">
<tr>
<td width=100 align="right"><font size=1 face="Verdana, arial, sans-serif"><b><?php echo TEXT_NAME; ?></b></font></td>
<td><input type="text" size="27" maxlength="256" name="name"></td>
</tr>
<tr>
<td align="right"><font size=1 face="Verdana, arial, sans-serif"><b><?php echo TEXT_EMAIL; ?></b></font></td>
<td><input type="text" size="27" maxlength="256" name="email"></td>
</tr>
<tr>
<td align="right"><font size=1 face="Verdana, arial, sans-serif"><b><?php echo TEXT_PROBLEM; ?></b></font></td>
<td><textarea rows="5" name="problem" cols="35"></textarea></td>
</tr>
<tr>
<td align="right"><font size=1 face="Verdana, arial, sans-serif"><b><?php echo TEXT_PRIORITY; ?></b></font></td>
<td><font size=1 face="Verdana, arial, sans-serif"><select name="priority">
<option value="High"><?php echo TEXT_HIGH; ?>
<option value="Medium" selected><?php echo TEXT_MEDIUM; ?>
<option value="Low"><?php echo TEXT_LOW; ?>
</select></font></td>
</tr>
<tr>
<td>&nbsp;</td>
<td align="right"><input type="submit" value="Submit &gt; &gt;"></td>
</tr>
</table>
  </center>
</div>
</form>
</td>
      </tr>
    </table></td>
  </tr>
<tr>
<?php
} else {
  $errorr = array();

  if($name == '') { $errorr[] = "Your name seems to be missing."; }
  if($email == '') { $errorr[] = "Please enter a valid email address."; }
  if($problem == '') { $errorr[] = "Where's your problem?"; }

 foreach ($errorr as $key => $value) { 
  if($value != '') { $noerror="1"; }
 }
 
if($noerror == '1') {
?>
      <tr>
        <td><br><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main">
              <p align="center"><?php echo TEXT_ERROR; ?></p>
            </td>
            <td class="main">
              <p align="center"><?php
foreach ($errorr as $key => $value) { 
  echo "$value";
}
?></p>
            </td>
          </tr>
          <tr>
            <td class="main">
              <p align="center"><form method="post" action="<?php echo $PHP_SELF; ?>">
<input type="hidden" name="submit" value="true">
<table border=0 cellpadding=1 cellspacing=5>
<tr>
<td width=100 align="right"><font size=1 face="Verdana, arial, sans-serif"><?php echo TEXT_NAME; ?></font></td>
<td><input type="text" size="20" maxlength="256" name="name" value="<?php echo $name; ?>"></td>
</tr>
<tr>
<td align="right"><font size=1 face="Verdana, arial, sans-serif"><?php echo TEXT_EMAIL; ?></font></td>
<td><input type="text" size="20" maxlength="256" name="email" value="<?php echo $email; ?>"></td>
</tr>
<tr>
<td align="right"><font size=1 face="Verdana, arial, sans-serif"><?php echo TEXT_PROBLEM; ?></font></td>
<td><textarea rows="5" name="problem" cols="35"><?php echo $problem; ?></textarea></td>
</tr>
<tr>
<td align="right"><font size=1 face="Verdana, arial, sans-serif"><?php echo TEXT_PRIORITY; ?></font></td>
<td><font size=1 face="Verdana, arial, sans-serif"><select name="priority">
<?php
if($priority == "High") { $c1 = " selected"; }
if($priority == "Medium") { $c2 = " selected"; }
if($priority == "Low") { $c3 = " selected"; }
?>
<option value="High"<?php echo $c1; ?><?php echo TEXT_HIGH; ?>
<option value="Medium"<?php echo $c2; ?><?php echo TEXT_MEDIUM; ?>
<option value="Low"<?php echo $c3; ?><?php echo TEXT_LOW; ?>
</select></font></td>
</tr>
<tr>
<td>&nbsp;</td>
<td align="right"><input type="submit" value="Submit &gt; &gt;"></td>
</tr>
</table>
</form>
</td>
      </tr>
    </table></td>
  </tr>
<tr>
<?php
} else {

  $curdate = date("D M d, Y h:i:s");
  $query = "INSERT INTO helpdesk VALUES ('','$name','$email','$problem','','NEW','$curdate','$priority');";
  $result = tep_db_query($query);

  $query = "SELECT ticket_id FROM helpdesk WHERE name = '$name' AND problem = '$problem';";
  $result = tep_db_query($query);
  list($ticketno) = mysql_fetch_row($result);
  
  $contents = "Dear ".$name.",\n\nYour request for support has been added to the database.\nYour ticket number is: ".$ticketno."\n\nPlease keep this email safely so that you can check on the status of your request at:\n".HTTP_SERVER."/helpdesk_status.php\n\nBest Regards,\n".STORE_NAME." Customer Service\n".SUPPORT_EMAIL_ADDRESS." ";
  $subject = "Support Ticket ".$ticketno;
  $from = 'From: "'.STORE_NAME.' Customer Service" <'.SUPPORT_EMAIL_ADDRESS.'>';
  mail($email, $subject, $contents, $from);

  $contents = "Somebody has submitted a support request! .\nThe ticket number is: ".$ticketno."\nThe Ticket Details are:\n\n".$problem."\n\nBest Regards,\n".STORE_NAME." Support System";
  $subject = "Support Ticket Recieved";
  $from = 'From: "'.STORE_NAME.' Support" <'.SUPPORT_EMAIL_ADDRESS.'>';
  mail(SUPPORT_EMAIL_ADDRESS, $subject, $contents, $from);

?>
      <tr>
        <td><br><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_SUCCESS; ?></td>
          </tr>
          <tr>
            <td class="main">
				<p>
					<?php echo TEXT_TICKET; ?><b><?php echo $ticketno; ?></b><br>
					<?php echo TEXT_NAME; ?><b><?php echo $name; ?></b><br>
					<?php echo TEXT_EMAIL; ?><b><?php echo $email; ?></b><br>
					<?php echo TEXT_PRIORITY; ?><b><?php echo $priority; ?></b><br>
					<?php echo TEXT_DESCRIPTION; ?><i><?php echo $problem; ?></i>
				</p>
			</td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_THANKYOU; ?></td>
          </tr>
        </table></td>
      </tr>
<tr>
        <td align="right" class="main"><br><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></td>
      </tr>
<?php
   }
}
?></center></center>
    </table></td>
<!-- body_text_eof //-->
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
    </table></td>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
<script language="JavaScript" src="menu.js"></script>
<script language="JavaScript" src="menu_items.js"></script>
<script language="JavaScript" src="menu_tpl.js"></script>
<script language="JavaScript">
	<!--
	new menu (MENU_ITEMS, MENU_POS, MENU_STYLES);
	//->
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>