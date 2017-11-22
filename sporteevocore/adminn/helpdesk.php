<?php
/*
  $Id: helpdesk.php,v 1.0 2002/06/10

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/2 Francesco Rossi

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
?>
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
<td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
if($usern == '' || $passw == '') {
?>
<tr><td bgcolor="#ffffff">
<br><blockquote>
<font face="Verdana, Verdana, arial, sans-serif, sans-serif" size=2><p><?php echo TEXT_INFORMATION;?></p></font></blockquote>
<form method="post" action="helpdesk.php">
<input type="hidden" name="submit" value="true">
<table border=0 cellpadding=1 cellspacing=5>
<tr>
<td width=100 align="right"><font size=1 face="Verdana, arial, sans-serif"><?php echo TEXT_USERNAME;?></font></td>
<td><input type="text" size="20" maxlength="256" name="usern"></td>
</tr>
<tr>
<td align="right"><font size=1 face="Verdana, arial, sans-serif"><?php echo TEXT_PASSWORD;?></font></td>
<td><input type="password" size="20" maxlength="256" name="passw"></td>
</tr>
<tr>
<td>&nbsp;</td>
<td align="right"><input type="submit" value="Submit &gt; &gt;"></td>
</tr>
</table>
</form>
</td></tr>
<?php
} else {
$query = "SELECT * FROM helpdesk_members WHERE username = '$usern'";
$result = tep_db_query($query);
list($username, $password, $email, $signature) = mysql_fetch_row($result);

      if ($passw == $password) {
        $found = 1;
      }

  if($found != 1) {
?>
<tr><td bgcolor="#ffffff">
<br><blockquote>
<font face="verdana, arial, sans-serif" size=2>
<br><p><?php echo TEXT_ERROR_TITLE;?></p>
<p><li><?php echo TEXT_ERROR;?>
</p>
</font>
</blockquote>
</td></tr>
<?php
  } else {
?>
<tr><td bgcolor="#ffffff">
<br><blockquote>
<font face="verdana, arial, sans-serif" size=2>
<br><p><?php echo TEXT_INFORMATION2;?></p>
<table border=0 width=100% cellpadding=4 cellspacing=4>
<tr valign=top>
<td width=50%>
<font face="Verdana, Verdana, arial, sans-serif, sans-serif" size=2><B><?php echo TEXT_FEATURES;?></b><BR></font>
<form method=post action="helpdesk_viewticket.php"><input type="hidden" name="usern" value="<?php echo $usern; ?>"><input type="hidden" name="passw" value="<?php echo $passw; ?>"><input type="submit" value="<?php echo TEXT_BUTTON_OPEN;?>"></form>
<form method=post action="helpdesk_closedtickets.php"><input type="hidden" name="usern" value="<?php echo $usern; ?>"><input type="hidden" name="passw" value="<?php echo $passw; ?>"><input type="submit" value="<?php echo TEXT_BUTTON_CLOSED;?>"></form>
<form method=post action="helpdesk_search.php"><input type="hidden" name="usern" value="<?php echo $usern; ?>"><input type="hidden" name="passw" value="<?php echo $passw; ?>"><input type="submit" value="<?php echo TEXT_BUTTON_SEARCH;?>"></form>
</td>
<td>
<font face="Verdana, Verdana, arial, sans-serif, sans-serif" size=2><B><?php echo TEXT_ADMIN_FEATURES;?></b><br></font>
<form method=post action="helpdesk_edituser.php"><input type="hidden" name="usern" value="<?php echo $usern; ?>"><input type="hidden" name="passw" value="<?php echo $passw; ?>"><input type="submit" value="<?php echo TEXT_BUTTON_EDIT;?>"></form>
<?php if($usern == 'admin') { ?>
<form method=post action="helpdesk_adduser.php"><input type="hidden" name="usern" value="<?php echo $usern; ?>"><input type="hidden" name="passw" value="<?php echo $passw; ?>"><input type="submit" value="<?php echo TEXT_BUTTON_ADD;?>"></form>
<form method=post action="helpdesk_deluser.php"><input type="hidden" name="usern" value="<?php echo $usern; ?>"><input type="hidden" name="passw" value="<?php echo $passw; ?>"><input type="submit" value="<?php echo TEXT_BUTTON_DELETE;?>"></form>
<?php } ?>
</td>
</tr>
<?php
  }
}
?>
</table>
<!-- body_text_eof //-->
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
<?php require(DIR_WS_INCLUDES . 'application_bottom.php');?>