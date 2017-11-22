<?php
/*
  $Id: multi_invoices_countries.php,v 1.22 2003/06/29 22:50:52 hpdl Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'insert':
          $update_sql_data = array('date_added' => 'now()');
      case 'save':
        if (isset($_GET['iID'])) $association_id = tep_db_prepare_input($_GET['iID']);
          $invoice_id = $_POST['invoice_id'];
          $countries_id = $_POST['countries_id'];
          
          $sql_data_array = array('invoice_id' => $invoice_id, 
                                  'countries_id' => $countries_id,
                                  'last_modified' => 'now()');
          
          
          if ($action == 'insert') {
           $sql_data_array = array_merge($sql_data_array, $update_sql_data);
           tep_db_perform(TABLE_INVOICE_TO_COUNTRIES, $sql_data_array);
            $association_id = tep_db_insert_id();
          } elseif ($action == 'save') {
            tep_db_perform(TABLE_INVOICE_TO_COUNTRIES, $sql_data_array, 'update', "association_id = '" . (int)$association_id . "'");
          }
        

        tep_redirect(tep_href_link(FILENAME_INVOICES_COUNTRIES, 'page=' . $_GET['page'] . '&iID=' . $association_id));


        break;
      case 'deleteconfirm':
        $iID = tep_db_prepare_input($_GET['iID']);

        tep_db_query("delete from " . TABLE_INVOICE_TO_COUNTRIES . " where association_id = '" . tep_db_input($iID) . "'");

        tep_redirect(tep_href_link(FILENAME_INVOICES_COUNTRIES, 'page=' . $_GET['page']));
        break;
      case 'delete':
        $iID = tep_db_prepare_input($_GET['iID']);

        $zones_query = tep_db_query("select count(*) as count from " . TABLE_INVOICE_TO_COUNTRIES . " where invoice_id = '" . (int)$iID . "'");
        $zones = tep_db_fetch_array($zones_query);

        $remove_invoice = true;
        
        break;
    }
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
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_INVOICES; ?></td>
                <td class="dataTableHeadingContent" align="left"><?php echo TABLE_HEADING_ZONES; ?>&nbsp;</td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>                
              </tr>
<?php
  
  $invoices_query_raw = "select itg.association_id, itg.invoice_id, itg.countries_id, i.invoice_filename, i.invoice_description, g.countries_name, g.countries_iso_code_2, g.countries_iso_code_3, itg.last_modified, itg.date_added from (" . TABLE_INVOICE_TO_COUNTRIES . " as itg INNER JOIN " . TABLE_INVOICES . " as i ON itg.invoice_id = i.invoice_id) INNER JOIN " . TABLE_COUNTRIES . " AS g ON itg.countries_id = g.countries_id";
  $invoices_split = new splitPageResults($_GET['page'], MAX_DISPLAY_SEARCH_RESULTS, $invoices_query_raw, $invoices_query_numrows);
  $invoices_query = tep_db_query($invoices_query_raw);
  while ($invoices = tep_db_fetch_array($invoices_query)) {
    if ((!isset($_GET['iID']) || (isset($_GET['iID']) && ($_GET['iID'] == $invoices['association_id']))) && !isset($iInfo) && (substr($action, 0, 3) != 'new')) {
      $iInfo = new objectInfo($invoices);
    }

    if (isset($iInfo) && is_object($iInfo) && ($invoices['association_id'] == $iInfo->association_id)) {
      echo '                  <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_INVOICES_COUNTRIES, 'page=' . $_GET['page'] . '&iID=' . $iInfo->association_id . '&action=edit') . '\'">' . "\n";
    } else {
      echo '                  <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_INVOICES_COUNTRIES, 'page=' . $_GET['page'] . '&iID=' . $invoices['association_id']) . '\'">' . "\n";
    }

    
	
    echo '                <td class="dataTableContent">' . $invoices['invoice_filename'] . ' "' . substr($invoices['invoice_description'],0,20) . '" </td>' . "\n";
    echo '                <td class="dataTableContent">' . $invoices['countries_name'] .'</td>' . "\n";

?>
                <td class="dataTableContent" align="right"><?php if (isset($iInfo) && is_object($iInfo) && ($invoices['association_id'] == $iInfo->association_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_INVOICES_COUNTRIES, 'page=' . $_GET['page'] . '&iID=' . $invoices['association_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
  }
?>
              <tr>
                <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $invoices_split->display_count($invoices_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_INVOICES_ZONES); ?></td>
                    <td class="smallText" align="right"><?php echo $invoices_split->display_links($invoices_query_numrows, MAX_DISPLAY_SEARCH_RESULTS, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
<?php
  if (empty($action)) {
?>
                  <tr>
                    <td colspan="2" align="right"><?php echo '<a href="' . tep_href_link(FILENAME_INVOICES_COUNTRIES, 'page=' . $_GET['page'] . '&action=new') . '">' . tep_image_button('button_insert.gif', IMAGE_INSERT) . '</a>'; ?></td>
                  </tr>
<?php
  }
?>
                </table></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();

  switch ($action) {
    case 'new':
      
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_INVOICES_ZONES . '</b>');

      $contents = array('form' => tep_draw_form('invoice', FILENAME_INVOICES_COUNTRIES, 'page=' . $_GET['page']  . '&action=insert'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);

      $contents[] = array('text' => tep_invoices_pull_down('name="invoice_id" style="font-size:10px"') . '<i>.php</i>');
      
      $contents[] = array('text' => tep_draw_pull_down_menu('countries_id', tep_get_countries(''), '','style="font-size:10px"'));    
      //$contents[] = array('text' => tep_countries_pull_down('name="countries_id" style="font-size:10px"'));    
      
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_insert.gif', IMAGE_INSERT) . ' <a href="' . tep_href_link(FILENAME_INVOICES_COUNTRIES, 'page=' . $_GET['page']) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'edit':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_INVOICES_ZONES . '</b>');

      $contents = array('form' => tep_draw_form('invoice', FILENAME_INVOICES_COUNTRIES, 'page=' . $_GET['page'] . '&iID=' . $iInfo->association_id  . '&action=save'));
      $contents[] = array('text' => TEXT_INFO_EDIT_INTRO);

      $contents[] = array('text' => tep_invoices_pull_down('name="invoice_id" style="font-size:10px"', $iInfo->invoice_id) . '<i>.php</i>');
      
      $contents[] = array('text' => tep_draw_pull_down_menu('countries_id', tep_get_countries(TEXT_ALL_COUNTRIES), $iInfo->countries_id,'style="font-size:10px"'));    
      
      //$contents[] = array('text' => tep_countries_pull_down('name="countries_id" style="font-size:10px"', $iInfo->countries_id));    
      
      $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_update.gif', IMAGE_UPDATE) . ' <a href="' . tep_href_link(FILENAME_INVOICES_COUNTRIES, 'page=' . $_GET['page'] . '&iID=' . $iInfo->association_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    case 'delete':
      $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_INVOICES_ZONES . '</b>');

      $contents = array('form' => tep_draw_form('invoice', FILENAME_INVOICES_COUNTRIES, 'page=' . $_GET['page'] . '&iID=' . $iInfo->association_id  . '&action=deleteconfirm'));
      $contents[] = array('text' => TEXT_INFO_DELETE_INTRO);
      $contents[] = array('text' => '<br><b>' . $iInfo->invoice_filename . '->' . $iInfo->countries_name . '</b>');
      
      if ($remove_invoice) $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_INVOICES_COUNTRIES, 'page=' . $_GET['page'] . '&iID=' . $iInfo->association_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
      break;
    default:
      if (isset($iInfo) && is_object($iInfo)) {
        $heading[] = array('text' => '<b>' . $iInfo->invoice_filename . '->' . $iInfo->countries_name . '</b>');

        $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_INVOICES_COUNTRIES, 'page=' . $_GET['page'] . '&iID=' . $iInfo->association_id . '&action=edit') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_INVOICES_COUNTRIES, 'page=' . $_GET['page'] . '&iID=' . $iInfo->association_id . '&action=delete') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>');
        $contents[] = array('text' => TEXT_FILENAME . $iInfo->invoice_filename . '<i>.php</i>');
        $contents[] = array('text' => '<br>' . TEXT_DESCRIPTION . '<br>');        
        $contents[] = array('text' => $iInfo->invoice_description . '<br><br>');        
        $contents[] = array('text' => TEXT_ZONE . $iInfo->countries_name);
        $contents[] = array('text' => TEXT_DESCRIPTION . $iInfo->countries_iso_code_2 . '/' . $iInfo->countries_iso_code_3);        
        $contents[] = array('text' => '<br><br>');        
        $contents[] = array('text' => TEXT_ADDED . $iInfo->date_added);        
        $contents[] = array('text' => TEXT_MODIFIED . $iInfo->last_modified);        
        
       

      }
      break;
  }

  if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";

    $box = new box;
    echo $box->infoBox($heading, $contents);

    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
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
