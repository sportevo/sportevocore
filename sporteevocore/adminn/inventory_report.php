<?php
//
require('includes/application_top.php');

 
define('MAX_RESULTS', '200');



$action = (isset($_GET['action']) ? $_GET['action'] : '');

if ($_GET['action'] == 'export') {
   $csv_output = TABLE_HEADING_PRODUCTS . ";" . SIZE . ";" . ";" . TABLE_HEADING_QTY_LEFT . ";" . TABLE_HEADING_COST_LEFT . ";" .TABLE_HEADING_TOTAL_COST_LEFT . ";" . TABLE_HEADING_PRICE_LEFT . ";" .TABLE_HEADING_TOTAL_PRICE_LEFT . ";" .TABLE_HEADING_MARGIN_LEFT . ";" .TABLE_HEADING_TOTAL_MARGIN_LEFT . ";" . TABLE_HEADING_STATUS_LEFT . ";" . TABLE_HEADING_ID . ";" . TABLE_HEADING_PROD_ID . ";". TABLE_HEADING_MANIF_LEFT . ";" . TABLE_HEADING_WGHT_LEFT;
  $csv_output .= "\n";
   $csv_query = tep_db_query("select * from products, products_description, products_attributes, products_options_values where products.products_id = products_description.products_id and products.products_id = products_attributes.products_id and products_description.language_id = '" . $languages_id . "' and products_options_values.language_id = '" . $languages_id . "' and products_attributes.options_values_id = products_options_values.products_options_values_id order by products_description.products_name");
      while ($csv = tep_db_fetch_array($csv_query)) {
       $csv_output .= utf8_decode($csv['products_name']) . ";" . $csv['products_options_values_name'] . ";" . ";" . $csv['options_quantity'] . ";" . str_replace(".",",",$csv['products_cost']) . ";" . str_replace(".",",",$csv['products_cost']*$csv['options_quantity']) . ";" . str_replace(".",",",$csv['products_price']) . ";" . str_replace(".",",",$csv['products_price']*$csv['options_quantity']) . ";" . str_replace(".",",",($csv['products_price']-$csv['products_cost'])/$csv['products_price']*'100') . ";" . str_replace(".",",",($csv['products_price']*$csv['options_quantity'])-($csv['products_cost']*$csv['options_quantity'])) . ";"  . $csv['products_status'] . ";" . $csv['products_id'] . ";" . $csv['products_model'] . ";" . $csv['manufacturers_name'] . ";" . $csv['products_weight'] . "\n";
      }
//salva con nome - ONTC
   $saveas = 'Stats_report';
   header("Content-Type: application/csv-tab-delimited-table");
   header("Content-Disposition: attachment; filename=$saveas.csv");
 
   print $csv_output;
   exit;
  }
 
// checking if you are activating / deactivating products
  if ($_GET['action'] == 'setflag') {
      tep_set_product_status($_GET['pID'], $_GET['flag']);
      // tep_redirect(tep_href_link(FILENAME_STATS_INVENTORY, '', 'NONSSL'));
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php');  ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); 
$sorted = $_GET['sorted'];
$orderby = $_GET['orderby'];
if ($sorted !== "ASC" and $sorted !== "DESC") $sorted = "ASC"; 
?>
<!-- left_navigation_eof //-->
        </table></td>
<!-- body_text //-->
   <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
             <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td class="smallText" align="right">
<?php
    echo tep_draw_form('search', FILENAME_CATEGORIES, '', 'get');
    echo IMAGE_SEARCH . ': ' . tep_draw_input_field('search');
    echo '</form>';
?>			
            </td>
          </tr>
        </table></td>
      </tr>
	  </table></td>
	  </tr>
      <tr>
        <td><table border="0" width="90%" cellspacing="0" cellpadding="2">
          <tr>
            <td valign="top"><table border="0" width="95%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_NUMBER; ?></td>
				<td class="dataTableHeadingContent" align="left"><?php  if (!isset($orderby) or ($orderby == "name" and $sorted == "ASC"))  $to_sort = "DESC"; else $to_sort = "ASC"; echo '<a href="' . tep_href_link(FILENAME_STATS_INVENTORY, 'orderby=name&sorted='. $to_sort) . '" class="headerLink">' . TABLE_HEADING_PRODUCTS . '</a>';  ?></td>
				<td class="dataTableHeadingContent" align="left"><?php  if (!isset($orderby) or ($orderby == "id" and $sorted == "ASC"))  $to_sort = "DESC"; else $to_sort = "ASC"; echo '<a href="' . tep_href_link(FILENAME_STATS_INVENTORY, 'orderby=id&sorted='. $to_sort) . '" class="headerLink">' .TABLE_HEADING_ID . '</a>'; ?>&nbsp;</td>
                 <td class="dataTableHeadingContent" align="left"><?php  if (!isset($orderby) or ($orderby == "stock" and $sorted == "ASC"))  $to_sort = "DESC"; else $to_sort = "ASC"; echo '<a href="' . tep_href_link(FILENAME_STATS_INVENTORY, 'orderby=stock&sorted='. $to_sort) . '" class="headerLink">' .TAGLIE . '</a>'; ?>&nbsp;</td>
				 <td class="dataTableHeadingContent" align="left"><?php  if (!isset($orderby) or ($orderby == "stock" and $sorted == "ASC"))  $to_sort = "DESC"; else $to_sort = "ASC"; echo '<a href="' . tep_href_link(FILENAME_STATS_INVENTORY, 'orderby=stock&sorted='. $to_sort) . '" class="headerLink">' .TABLE_HEADING_QTY_LEFT . '</a>'; ?>&nbsp;</td>
				<td class="dataTableHeadingContent" align="left"><?php  if (!isset($orderby) or ($orderby == "cost" and $sorted == "ASC"))  $to_sort = "DESC"; else $to_sort = "ASC"; echo '<a href="' . tep_href_link(FILENAME_STATS_INVENTORY, 'orderby=cost&sorted='. $to_sort) . '" class="headerLink">' .TABLE_HEADING_COST_LEFT . '</a>'; ?>&nbsp;</td>
				<td class="dataTableHeadingContent" align="left"><?php  if (!isset($orderby) or ($orderby == "price" and $sorted == "ASC"))  $to_sort = "DESC"; else $to_sort = "ASC"; echo '<a href="' . tep_href_link(FILENAME_STATS_INVENTORY, 'orderby=price&sorted='. $to_sort) . '" class="headerLink">' .TABLE_HEADING_PRICE_LEFT . '</a>'; ?>&nbsp;</td>
				<td class="dataTableHeadingContent" align="left"><?php  if (!isset($orderby) or ($orderby == "status" and $sorted == "ASC"))  $to_sort = "DESC"; else $to_sort = "ASC"; echo '<a href="' . tep_href_link(FILENAME_STATS_INVENTORY, 'orderby=status&sorted='. $to_sort) . '" class="headerLink">' .TABLE_HEADING_STATUS_LEFT . '</a>'; ?>&nbsp;</td>
				<td class="dataTableHeadingContent" align="left"><?php  if (!isset($orderby) or ($orderby == "status" and $sorted == "ASC"))  $to_sort = "DESC"; else $to_sort = "ASC"; echo '<a href="' . tep_href_link(FILENAME_STATS_INVENTORY, 'orderby=status&sorted='. $to_sort) . '" class="headerLink">' .TABLE_HEADING_STATUS_LEFT . '</a>'; ?>&nbsp;</td>
				<td class="dataTableHeadingContent"><?php  if (!isset($orderby) or ($orderby == "model" and $sorted == "ASC"))  $to_sort = "DESC"; else $to_sort = "ASC"; echo '<a href="' . tep_href_link(FILENAME_STATS_INVENTORY, 'orderby=model&sorted='. $to_sort) . '" class="headerLink">' .TABLE_HEADING_PROD_ID . '</a>'; ?></td>
                <td class="dataTableHeadingContent"><?php  if (!isset($orderby) or ($orderby == "manufacturers_name" and $sorted == "ASC"))  $to_sort = "DESC"; else $to_sort = "ASC"; echo '<a href="' . tep_href_link(FILENAME_STATS_INVENTORY, 'orderby=manufacturers_name&sorted='. $to_sort) . '" class="headerLink">' . TABLE_HEADING_MANIF_LEFT . '</a>';  ?></td>
                 <td class="dataTableHeadingContent" align="right"><?php  if (!isset($orderby) or ($orderby == "weight" and $sorted == "ASC"))  $to_sort = "DESC"; else $to_sort = "ASC"; echo '<a href="' . tep_href_link(FILENAME_STATS_INVENTORY, 'orderby=weight&sorted='. $to_sort) . '" class="headerLink">' .TABLE_HEADING_WGHT_LEFT . '</a>'; ?>&nbsp;</td>
              </tr>
<?php
			
 if ($_GET['page'] > 1) $rows = $_GET['page'] * MAX_RESULTS - MAX_RESULTS;
 if ($orderby == "name") {$db_orderby = "pd.products_name";}
 elseif ($orderby == "id") {$db_orderby = "p.products_id";}
  elseif ($orderby == "size") {$db_orderby = "products_options_values_name";}
 elseif ($orderby == "stock") {$db_orderby = "options_quantity";}
 elseif ($orderby == "cost") {$db_orderby = "p.products_cost";}
 elseif ($orderby == "weight") {$db_orderby = "p.products_weight";}
 elseif ($orderby == "price") {$db_orderby = "p.products_price";}
 elseif ($orderby == "status") {$db_orderby = "p.products_status";}
 elseif ($orderby == "model") {$db_orderby = "p.products_model";}
 elseif ($orderby == "manufacturers_name") {$db_orderby = "pr.manufacturers_name";}
 else {$db_orderby = "pd.products_name";}

  $products_query_raw = "select * from products, products_description, products_attributes, products_options_values where products.products_id = products_description.products_id and products.products_id = products_attributes.products_id and products_description.language_id = '" . $languages_id . "' and products_options_values.language_id = '" . $languages_id . "' and products_attributes.options_values_id = products_options_values.products_options_values_id order by products_description.products_name";
    
  $products_split = new splitPageResults($_GET['page'], MAX_RESULTS, $products_query_raw, $products_query_numrows);
  $products_query = tep_db_query($products_query_raw);
  while ($products = tep_db_fetch_array($products_query)) {
    $rows++;
	
    if (strlen($rows) < 2) {
      $rows = '0' . $rows;
    }
				$products_id = $products['products_id'];
				$last_category_query = tep_db_query("select categories_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = $products_id");
				$last_category = tep_db_fetch_array($last_category_query);
				$p_category = $last_category["categories_id"];
				do 
				{
				$p_category_array[] = $p_category;
				// Oplossing sql error
				if  ($p_category == ""){
					//Dont run query this time, it will error. Skip to next record. 
				}else{
				//end Oplossing sql error
				$last_category_query = tep_db_query("select parent_id from " . TABLE_CATEGORIES . " where categories_id = $p_category");
				$last_category = tep_db_fetch_array($last_category_query);
					$p_category = $last_category["parent_id"];
					}
			}while ($p_category);
			$cPath_array = array_reverse($p_category_array);
			
?>

<tr class="dataTableRow" onMouseOver="rowOverEffect(this)" onMouseOut="rowOutEffect(this)" onClick="document.location.href='<?php echo tep_href_link(FILENAME_CATEGORIES, tep_get_path() . '&pID=' . $products['products_id'] . '&action=new_product', 'NONSSL'); ?>'">
            <td align="left" class="dataTableContent"><?php echo $rows; ?>.</td>
            <td align="left" class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_path() . '&pID=' . $products['products_id']) . '" class="blacklink">' . $products['products_name'] . '</a>'; ?></td>
			<td align="left" class="dataTableContent"><?php echo $products['products_id']; ?></td>
            <td align="left" class="dataTableContent"><?php echo $products['products_options_values_name']; ?></td>
			<td align="left" class="dataTableContent"><?php echo $products['options_quantity']; ?></td>
			<td align="left" class="dataTableContent"><?php echo $products['products_cost']; ?></td>			
            <td align="left" class="dataTableContent"><?php echo $products['products_price']; ?></td>
			<td  class="dataTableContent" align="center">
<?php
// showing status icons
      if ($products['products_status'] == '1') {
        echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_STATS_INVENTORY, 'action=setflag&flag=0&pID=' . $products['products_id'], 'NONSSL') . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
      } else {
        echo '<a href="' . tep_href_link(FILENAME_STATS_INVENTORY, 'action=setflag&flag=1&pID=' . $products['products_id'], 'NONSSL') . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
      }
?></td>
            <td align="left" class="dataTableContent"><?php echo $products['products_status']; ?></td>
            <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_path() . '&pID=' . $products['products_id'], 'NONSSL') . '&action=new_product">' . $products['products_model'] . '</a>'; ?></td>
			<td align="left" class="dataTableContent"><?php echo $products['manufacturers_name']; ?></td>
            <td align="right" class="dataTableContent"><?php echo $products['products_weight']; ?></td>
            </tr>
<?php
  unset($cPath_array); unset($p_category_array); 
  }
?>
          </table></td>
          </tr>
		  <tr>
                <td colspan="9"><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
              </tr>
              <tr>
                <td colspan="4" align="left"><?php echo tep_draw_form('stockprice_report', FILENAME_STATS_INVENTORY, 'action=export', 'post'); ?><?php echo tep_image_submit('button_csv_export.gif', IMAGE_EXPORT); ?></form></td>
              </tr>
          <tr>
            <td colspan="3"><table border="0" width="90%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText" valign="top"><?php echo $products_split->display_count($products_query_numrows, MAX_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?></td>
                <td class="smallText" align="right"><?php echo $products_split->display_links($products_query_numrows, MAX_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page'], "orderby=" . $orderby . "&sorted=" . $sorted); ?>&nbsp;</td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
