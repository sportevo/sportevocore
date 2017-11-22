<?php
/*
  $Id: helpdesk_reopen.php,v 1.10

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/2 Francesco Rossi

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_HELPDESK_REOPEN);

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_HELPDESK_REOPEN, '', 'NONSSL'));
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
if($submit == "true") {

 $query = "UPDATE helpdesk SET problem = '$problem', status = '$status' WHERE ticket_id = $ticket_id";
 $result = tep_db_query($query);
?>
      <tr>
        <td><br><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main">
              <p align="center"><?php echo TEXT_INFORMATION; ?></p>
            </td>
          </tr>
    </table></td>
  </tr>
<tr>
<?php
} else {
$query = "SELECT * FROM helpdesk";
$result = tep_db_query($query);
while(list($ticket_id, $name, $email, $problem, $solution, $status, $date, $priority) = mysql_fetch_row($result))
  {    
    if($ticket_id == $ticket) {
      if ($username == $name) {
        $found = 1;
      }
    }
  }

  if($found != 1) {
?>
      <tr>
        <td><br><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main">
              <p align="center"><?php echo TEXT_ERROR; ?></p>
            </td>
          </tr>
    </table></td>
  </tr>
<tr>
<?php
  } else {

$query = "SELECT * FROM helpdesk WHERE ticket_id = '$ticket';";
$result = tep_db_query($query);
list($ticket_id, $name, $email, $problem, $solution, $status, $date, $priority) = mysql_fetch_row($result);

?>
      <tr>
        <td><br><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main">
				<p>
					<?php echo TEXT_DATE; ?><b><?php echo $date; ?></b><br>
					<?php echo TEXT_STATUS; ?><b><?php echo $status; ?></b><br>
					<?php echo TEXT_PRIORITY; ?><b><?php echo $priority; ?></b><br>
					<?php echo TEXT_NAME; ?><b><?php echo $name; ?></b><br>
					<?php echo TEXT_EMAIL; ?><b><?php echo $email; ?></b>
				</p>
			</td>
          </tr>
          <tr>
            <td class="main"><form method="post" action="helpdesk_reopen.php">
<input type="hidden" name="ticket_id" value="<?php echo $ticket; ?>">
<input type="hidden" name="submit" value="true">
<input type="hidden" name="status" value="REOPENED"><?php echo TEXT_REOPEN; ?>
<br>
<textarea rows="5" name="problem" cols="45"><?php echo $problem; ?></textarea><br><br>
<?php 
if($solution == '') { $solution = 'TEXT_SOLUTION';  }
?>
<b><?php echo TEXT_SOLUTION; ?>:</b><br>
<textarea rows="5" readonly name="solution" cols="45"><?php echo $solution; ?></textarea><br><br>
<input type="submit" value="Open Ticket &gt; &gt;">
</form></td>
          </tr>
        </table></td>
      </tr>
<?php
   }
}
?></center></center>
      <tr>
        <td align="right" class="main"><br><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></td>
      </tr>
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