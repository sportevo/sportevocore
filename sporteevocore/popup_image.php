<?php
/*
  $Id: popup_image.php,v 1.18 2003/06/05 23:26:23 hpdl Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $navigation->remove_current_page();

  //BOF UltraPics
// BOF Original
/*
  $products_query = tep_db_query("select pd.products_name, p.products_image from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id where p.products_status = '1' and p.products_id = '" . (int)$_GET['pID'] . "' and pd.language_id = '" . (int)$languages_id . "'");
*/
//EOF Original
  $products_query = tep_db_query("select pd.products_name, p.products_image, p.products_image_lrg, p.products_image_xl_1, p.products_image_xl_2, p.products_image_xl_3, p.products_image_xl_4, p.products_image_xl_5, p.products_image_xl_6 from " . TABLE_PRODUCTS . " p left join " . TABLE_PRODUCTS_DESCRIPTION . " pd on p.products_id = pd.products_id where p.products_status = '1' and p.products_id = '" . (int)$_GET['pID'] . "' and pd.language_id = '" . (int)$languages_id . "'");
//EOF UltrPics

  $products = tep_db_fetch_array($products_query);
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo $products['products_name']; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<script language="javascript"><!--
var i=0;
function resize() {
  if (navigator.appName == 'Netscape') i=40;
  if (document.images[0]) window.resizeTo(document.images[0].width +30, document.images[0].height+60-i);
  self.focus();
}
//--></script>
</head>
<body onLoad="resize();">
<!--BOF UltraPics-->
<!--BOF Original-->
<!--
<?php echo tep_image(DIR_WS_IMAGES . $products['products_image'], $products['products_name']); ?>
-->
<!--EOF Original-->
<?php
	if (($_GET['image'] ==0) && ($products['products_image_lrg'] != '')) {
		echo tep_image(DIR_WS_IMAGES . $products['products_image_lrg'], $products['products_name']);
	} elseif ($_GET['image'] ==1) {
		echo tep_image(DIR_WS_IMAGES . $products['products_image_xl_1'], $products['products_name']);
	} elseif ($_GET['image'] ==2) {
		echo tep_image(DIR_WS_IMAGES . $products['products_image_xl_2'], $products['products_name']);
	} elseif ($_GET['image'] ==3) {
		echo tep_image(DIR_WS_IMAGES . $products['products_image_xl_3'], $products['products_name']);
	} elseif ($_GET['image'] ==4) {
		echo tep_image(DIR_WS_IMAGES . $products['products_image_xl_4'], $products['products_name']);
	} elseif ($_GET['image'] ==5) {
		echo tep_image(DIR_WS_IMAGES . $products['products_image_xl_5'], $products['products_name']);
	} elseif ($_GET['image'] ==6) {
		echo tep_image(DIR_WS_IMAGES . $products['products_image_xl_6'], $products['products_name']);
	} else {
		echo tep_image(DIR_WS_IMAGES . $products['products_image'], $products['products_name']);
	}
?>
<!--EOF UltraPics-->
</body>
</html>
<?php require('includes/application_bottom.php'); ?>
