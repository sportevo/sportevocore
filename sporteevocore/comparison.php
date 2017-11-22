<?php
/*
  $Id: comparison.php, v1.1 20101025 kymation Exp $
  $Loc: catalog/ $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/9 Francesco Rossi

  Released under the GNU General Public License
*/

/*
 * This file displays the product comparison table from the comparison module
 *   Specific products are shown if the customer has selected one or more
 *   products, otherwise the general comparison table with all products in
 *   the category is shown.
 * 
 * $current_category_id is required to display the general comparison table
 * $comp_array is required to show the selected products
 */


  require_once ('includes/application_top.php');

  require_once (DIR_WS_FUNCTIONS . 'products_specifications.php');

  require_once (DIR_WS_LANGUAGES . $language . '/' . FILENAME_COMPARISON);
  
  if( $current_category_id == 0 ) {
    tep_redirect( tep_href_link( FILENAME_DEFAULT ) );
  } else {
    //Get the name for this category
    $title_query_raw = "
      select 
        categories_name
      from 
        " . TABLE_CATEGORIES_DESCRIPTION . "
      where 
        categories_id = '" . ( int )$current_category_id . "'
    ";
    // print $title_query_raw . "<br>\n";
    $title_query = tep_db_query( $title_query_raw );
    $title_array = tep_db_fetch_array( $title_query );
    $heading_title = sprintf( HEADING_TITLE, $title_array['categories_name'] );
  }
  
  // Set up the array of product IDs that the customer has selected (if any)
  $comp_array = array();
  if (isset ($_GET['comp']) && $_GET['comp'] != '') {
    // Decode the URL-encoded names, including arrays
    $comp_array = tep_decode_recursive ($_GET['comp']);

    // Sanitize variables to prevent hacking
    $comp_array = tep_clean_get__recursive ($comp_array);
  }

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>

<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<!-- header //-->
<?php require_once (DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require_once (DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>

<!-- body_text //-->
    <td width="100%" valign="top">
<?php 
  if (count ($comp_array) > 0 or SPECIFICATIONS_COMPARISON_LAYOUT == 'horiz') {
    require_once (DIR_WS_MODULES . 'comparison_horiz.php');
  } else {
    require_once (DIR_WS_MODULES . FILENAME_COMPARISON); 
  }
?>
    </td>
<!-- body_text_eof //-->
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- right_navigation //-->
<?php require_once (DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
    </table></td>
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require_once (DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require_once (DIR_WS_INCLUDES . 'application_bottom.php'); ?>