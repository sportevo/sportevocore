<?php
/*
  $Id: product_sets.php v3.5 2008-1-29 05:52:16 brad $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/2 Francesco Rossi

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_PRODUCT_SETS);
?>

<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script language="javascript" src="includes/javascript/product_sets.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
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
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading"></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </td>
      </tr>
	<tr>
        <td colspan=2>
		<table style="width: 100%; text-align: left; background-color: #dddddd;">
			<tr>
				<td width=15%><?php echo TABLE_HEADING_PRODUCT_SETS_ADD_CATEGORIES; ?></td>
				<td width=85% class="main"><img src="images/icons/icon_add_new.png" onclick="doCmd('sets_add_category', document.getElementById('sets_categories_name').value, 0); document.getElementById('sets_categories_name').value=''"><input type="text" id="sets_categories_name" /></td>
			</tr>
			<tr>
				<td width=15%><?php echo TABLE_HEADING_PRODUCT_SETS_CATEGORIES; ?></td>
				<td width=85% id="sets_category_result"><img src="images/icons/icon_delete.png" onclick="doCmd('sets_remove_category', document.getElementById('sets_categories_id').value, 0);"><?php echo tep_get_set_list('sets_categories_id', null, 'onchange="doCmd(\'sets_get\', this.value, 0); document.getElementById(\'sets_search_results\').innerHTML=\'\'" id="sets_categories_id"');?></td>
			</tr>
		        <tr>
        		        <td width=15%><?php echo TABLE_HEADING_PRODUCT_SETS_SEARCH; ?></td>
				<td width=85% class="main"><input type="text" onkeyup="doCmd('sets_search', document.getElementById('sets_categories_id').value, this.value);"></td>
			</tr>
		        <tr>
	        	        <td width=15%></td>
				<td width=85% class="main"><div id="sets_search_results"></div></td>
			</tr>
		        <tr>
	        	        <td width=15%><?php echo TABLE_HEADING_PRODUCT_SETS; ?></td>
				<td width=85% class="main"></td>
			</tr>
			</form>
			<tr class="main">
				<td class="main"></td>
				<td class="main"><table id="sets"></table></td>
		        </tr>
	        </table>
	</td>
      </tr>

    </table></td>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->

</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
