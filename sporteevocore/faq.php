<?php
/*
  $Id: faq.php v.0.1 26.05.2002 
  povered by Adgrafics-Ukraine http://adgrafics.net 
  victor@zolochevsky.com

  The Exchange Project - Community Made Shopping!
  http://www.theexchangeproject.org 

  Copyright (c) 2015/0,2001 The Exchange Project

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_FAQ);
  
  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_FAQ, '', 'NONSSL'));
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
          <td class="main"><? include(DIR_WS_MODULES . 'support_menu.php'); ?></td>
        </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><br><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main">

<table border="0" width="100%" cellspacing="1" cellpadding="3" bgcolor="#336699"><tr bgcolor="#FFFFFF"><td  class="main">
<ol>
<?php 

//mysql_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD) or die("Unable to connect to SQL server");


while ($faq=faq_toc()) {
?>
<li><?php echo $faq[toc];?></li>

<?php }
?>
</ol>
</td></tr></table>
<hr size="1" color="#336699">
<!-- answers -->
<?php while ($faq=read_faq()) {
new infoBox(array(array('text' => '<b><a name=' . $faq[faq_id] .'>'.$faq[question].'</a></b><br><br>'.$faq[answer])));
echo "&nbsp;\n";
}
?>
<!-- end answers -->


</td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td align="right" class="main"><br><?php
        if (tep_session_is_registered('customer_id')) {
             echo '<a href="' . tep_href_link(FILENAME_SUPPORT_TRACK, '', 'NONSSL') . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a></td>';
        } else {
            echo '<a href="' . tep_href_link(FILENAME_DEFAULT, '', 'NONSSL') . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a></td>';
        }
        ?>
      </tr>
    </table></td>
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
