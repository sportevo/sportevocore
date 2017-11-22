<?php
/*
  $Id: ticket_view.php,v 1.6 2003/07/13 20:22:02 hook Exp $

  OSC-SupportTicketSystem
  Copyright (c) 2015/3 Henri Schmidhuber IN-Solution
  
  Contribution based on:

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/
  require('includes/application_top.php');
 if (!tep_session_is_registered('customer_id') && $_GET['login']=="yes") {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_TICKET_VIEW);
  $ticket_departments = array();
  $ticket_department_array = array();
  $ticket_department_query = tep_db_query("select ticket_department_id, ticket_department_name from " . TABLE_TICKET_DEPARTMENT . " where ticket_language_id = '" . $languages_id . "'");
  while ($ticket_department = tep_db_fetch_array($ticket_department_query)) {
    $ticket_departments[] = array('id' => $ticket_department['ticket_department_id'],
                               'text' => $ticket_department['ticket_department_name']);
    $ticket_department_array[$ticket_department['ticket_department_id']] = $ticket_department['ticket_department_name'];
  }

  $ticket_prioritys = array();
  $ticket_priority_array = array();
  $ticket_priority_query = tep_db_query("select ticket_priority_id, ticket_priority_name from " . TABLE_TICKET_PRIORITY . " where ticket_language_id = '" . $languages_id . "'");
  while ($ticket_priority = tep_db_fetch_array($ticket_priority_query)) {
    $ticket_prioritys[] = array('id' => $ticket_priority['ticket_priority_id'],
                               'text' => $ticket_priority['ticket_priority_name']);
    $ticket_priority_array[$ticket_priority['ticket_priority_id']] = $ticket_priority['ticket_priority_name'];
  }

  $ticket_statuses = array();
  $ticket_status_array = array();
  $ticket_status_query = tep_db_query("select ticket_status_id, ticket_status_name from " . TABLE_TICKET_STATUS . " where ticket_language_id = '" . $languages_id . "'");
  while ($ticket_status = tep_db_fetch_array($ticket_status_query)) {
    $ticket_statuses[] = array('id' => $ticket_status['ticket_status_id'],
                               'text' => $ticket_status['ticket_status_name']);
    $ticket_status_array[$ticket_status['ticket_status_id']] = $ticket_status['ticket_status_name'];
  }
  
  $enquiry = tep_db_prepare_input($_POST['enquiry']);
  $status = tep_db_prepare_input($_POST['status']);
  $priority = tep_db_prepare_input($_POST['priority']);
  $department = tep_db_prepare_input($_POST['department']);
  if (isset($_POST['tlid'])) $tlid =  tep_db_prepare_input($_POST['tlid']);
  if (isset($_GET['tlid'])) $tlid =  tep_db_prepare_input($_GET['tlid']);
  if (strlen($tlid) < 10) unset($tlid);
// Form was submitted
  $error = false;
  if (isset($_GET['action']) && ($_GET['action'] == 'send') && isset($tlid) ) {
  // Check Message length
    if (isset($enquiry) && strlen($enquiry) < TICKET_ENTRIES_MIN_LENGTH ) {
        $error = true;
        $_GET['error_message']=TICKET_WARNING_ENQUIRY_TOO_SHORT;
    }
    if ($error == false) {
      $ticket_id_query = tep_db_query("select ticket_id, �ticket_customers_name� from " . TABLE_TICKET_TICKET . " where ticket_link_id = '" . tep_db_input($tlid) . "'");
      $ticket_id = tep_db_fetch_array($ticket_id_query);
      if ($ticket_id['ticket_id']) {
        if (TICKET_ALLOW_CUSTOMER_TO_CHANGE_STATUS == 'false' && TICKET_CUSTOMER_REPLY_STATUS_ID > 0 ) $status = TICKET_CUSTOMER_REPLY_STATUS_ID;
        $sql_data_array = array('ticket_id' => $ticket_id['ticket_id'],
                          'ticket_status_id' => $status,
                          'ticket_priority_id' => $priority,
                          'ticket_department_id' => $department,
                          'ticket_date_modified' => 'now()',
                          'ticket_customer_notified' => '0',
                          'ticket_edited_by' => $ticket_id['ticket_customers_name'],
                          'ticket_comments' => $enquiry);
        tep_db_perform(TABLE_TICKET_STATUS_HISTORY, $sql_data_array);         
        $sql_data_array = array('ticket_status_id' => $status,
                          'ticket_priority_id' => $priority,
                          'ticket_department_id' => $department,
                          'ticket_date_last_modified' => 'now()',
                          'ticket_date_last_customer_modified' => 'now()');       
        tep_db_perform(TABLE_TICKET_TICKET, $sql_data_array, 'update', 'ticket_id = \'' . $ticket_id['ticket_id'] . '\'');        
        $_GET['info_message']=TICKET_MESSAGE_UPDATED;
        
      }
    }  
  }
  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_TICKET_CREATE));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
<?php echo TICKET_STYLESHEET; ?>
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
            <td class="pageHeading" align="right"><?php echo tep_image(DIR_WS_IMAGES . 'table_background_contact_us.gif', HEADING_TITLE, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
// Show spezific Ticket  
  if (!isset($tlid)) {
?>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <?php echo tep_draw_form('ticket_view', tep_href_link(FILENAME_TICKET_VIEW, 'action=send'), 'get') . "\n"; ?>
          <tr>
            <td class="main" align="left"><?php echo TEXT_VIEW_TICKET_NR; ?>&nbsp;</td>
            <td class="main" align="left"><?php echo tep_draw_input_field('tlid'); ?></td>
            <td><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE);  ?></td>
          </tr></form>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '5'); ?></td>
      </tr>
<?php
    if (tep_session_is_registered('customer_id')) {
      $customers_tickets_raw = "select * from " . TABLE_TICKET_TICKET . " where ticket_customers_id = '" . tep_db_prepare_input($customer_id) . "' order by ticket_date_last_modified desc";
      $customers_tickets_split = new splitPageResults($customers_tickets_raw, MAX_DISPLAY_SEARCH_RESULTS);
      if ($customers_tickets_split->number_of_rows > 0 ) {
?>
      <tr>
        <td><table width="100%" border="0" cellspacing="0" cellpadding="5">
          <tr>
            <td class="infoBoxHeading" align="left"><?php echo TABLE_HEADING_NR; ?></td>
<?php 
      if (TICKET_SHOW_CUSTOMERS_SUBJECT == 'true') { echo '            <td class="infoBoxHeading" align="left"><' . TABLE_HEADING_SUBJECT . '></td>'; }
      if (TICKET_CATALOG_USE_STATUS == 'true') {     echo '            <td class="infoBoxHeading">' . TABLE_HEADING_STATUS . '</td>'; }
      if (TICKET_CATALOG_USE_DEPARTMENT == 'true') { echo '            <td class="infoBoxHeading">' . TABLE_HEADING_DEPARTMENT . '</td>'; }
      if (TICKET_CATALOG_USE_PRIORITY == 'true') {   echo '            <td class="infoBoxHeading">' . TABLE_HEADING_PRIORITY . '</td>'; }
?>
           <td class="infoBoxHeading" align="right"><?php echo TABLE_HEADING_CREATED; ?></td>
            <td class="infoBoxHeading" align="right"><?php echo TABLE_HEADING_LAST_MODIFIED; ?></td>
          </tr>              
<?php
        $customers_tickets_query = tep_db_query ($customers_tickets_split->sql_query);
        $number_of_tickets = 0;
        while ($customers_tickets = tep_db_fetch_array($customers_tickets_query)) {
          $number_of_tickets++;
          if (($number_of_tickets / 2) == floor($number_of_tickets / 2)) {
            echo '         <tr class="productListing-even">' . "\n";
          } else {
           echo '          <tr class="productListing-odd">' . "\n";
          }
?>
            <td class="smallText" align="left"><?php echo '<a href="' . tep_href_link(FILENAME_TICKET_VIEW, 'tlid=' . $customers_tickets['ticket_link_id']) . '">' . $customers_tickets['ticket_link_id'] . '</a>'; ?></td>
<?php
          if (TICKET_SHOW_CUSTOMERS_SUBJECT == 'true') { echo '            <td class="smallText" align="left"><a href="' . tep_href_link(FILENAME_TICKET_VIEW, 'tlid=' . $customers_tickets['ticket_link_id']) . '">' . $customers_tickets['ticket_subject'] . '</a></td>'; }
          if (TICKET_CATALOG_USE_STATUS == 'true') {     echo '            <td class="smallText">' . $ticket_status_array[$customers_tickets['ticket_status_id']] . '</td>'; }
          if (TICKET_CATALOG_USE_DEPARTMENT == 'true') { echo '            <td class="smallText">' . $ticket_department_array[$customers_tickets['ticket_department_id']] . '</td>'; }
          if (TICKET_CATALOG_USE_PRIORITY == 'true') {   echo '            <td class="smallText">' . $ticket_priority_array[$customers_tickets['ticket_priority_id']] . '</td>'; }

?>
            <td class="smallText" align="right"><?php echo tep_date_short($customers_tickets['ticket_date_created']); ?></td>
            <td class="smallText" align="right"><?php echo tep_date_short($customers_tickets['ticket_date_last_modified']); ?></td>
          </tr>
<?php
        }
?>
<?php 
  if ($customers_tickets_split->number_of_rows > 0) {
?>
          <tr>
            <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="smallText"><?php echo $customers_tickets_split->display_count(TEXT_DISPLAY_NUMBER_OF_TICKETS); ?></td>
                <td class="smallText" align="right"><?php echo TEXT_RESULT_PAGE; ?> <?php echo $customers_tickets_split->display_links(MAX_DISPLAY_PAGE_LINKS, tep_get_all_get_params(array('page', 'info', 'x', 'y'))); ?></td>
              </tr>
            </table></td>
          </tr>
          <tr>
<?php
  }
?>
        </table></td>
      </tr>
<?php
      }
    }
  }
  if (isset($tlid)) {
      $ticket_query = tep_db_query("select * from " . TABLE_TICKET_TICKET . " where ticket_link_id�= '" . tep_db_input($tlid) . "'");
      $ticket = tep_db_fetch_array($ticket_query);
    // Check if Customer is allowed to view ticket:
      if ($ticket['ticket_customers_id'] > 1 && $ticket['ticket_login_required']=='1' && !tep_session_is_registered('customer_id') ) {
          // Customer must be logged in to view ticket:
?>
      <tr>
        <td align="center"><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo sprintf(TEXT_VIEW_TICKET_LOGIN, tep_href_link(FILENAME_TICKET_VIEW, 'login=yes&tlid=' . $tlid, 'SSL'), tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL')); ?></td>
          </tr>
        </table></td>
      </tr>      
<?php  
      } else {
      // Customer is allowed to view ticket
        $ticket_status_query = tep_db_query("select * from " . TABLE_TICKET_STATUS_HISTORY . " where ticket_id = '". tep_db_input($ticket['ticket_id']) . "'");
      
?>
      <tr>
        <td><table class="ticket" width="100%" border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td colspan=2 class="ticketInfoBoxHeading" align="left"><b><?php echo $ticket['ticket_subject']; ?></b></td>
          </tr> 
          <tr>
            <td class="ticketSmallText" colspan=2 align="left">
<?php 
        echo TEXT_OPENED . ' ' . tep_date_short($ticket['ticket_date_created']) . ' ' . TEXT_TICKET_BY . ' ' . $ticket['ticket_customers_name'] . "<br>";
        echo TEXT_TICKET_NR . '&nbsp;' . $ticket['ticket_link_id'];
        if ($ticket['ticket_customers_orders_id'] > 0) echo '<br>' . TEXT_CUSTOMERS_ORDERS_ID . '&nbsp;' . $ticket['ticket_customers_orders_id'];
?>
            </td>
          </tr>  
<?php     
        while ($ticket_status = tep_db_fetch_array($ticket_status_query)) {
?>
          <tr >
            <td class="ticketSmallText" width="15%">
<?php
          echo '<b>' . $ticket_status['ticket_edited_by'] . '</b><br></br>';
          echo TEXT_DATE . '&nbsp;' .  tep_date_short($ticket_status['ticket_date_modified']) . '<br>';
          if (TICKET_CATALOG_USE_STATUS == 'true') {      echo TEXT_STATUS . '&nbsp;' .  $ticket_status_array[$ticket_status['ticket_status_id']] . '<br>'; }
          if (TICKET_CATALOG_USE_DEPARTMENT == 'true') {  echo TEXT_DEPARTMENT . '&nbsp;' .  $ticket_department_array[$ticket_status['ticket_department_id']] . '<br>'; }
          if (TICKET_CATALOG_USE_PRIORITY == 'true') {    echo TEXT_PRIORITY . '&nbsp;' .  $ticket_priority_array[$ticket_status['ticket_priority_id']] . '<br>'; }
          $ticket_last_used_status = $ticket_status['ticket_status_id'];
          $ticket_last_used_department = $ticket_status['ticket_department_id'];
          $ticket_last_used_priority = $ticket_status['ticket_priority_id'];
?>
            </td>
            <td align=left class="ticketSmallText"><?php echo nl2br($ticket_status['ticket_comments']); ?></td>
          </tr>  

<?php
        }
        echo tep_draw_form('ticket_view', tep_href_link(FILENAME_TICKET_VIEW, 'action=send')); 
        echo tep_draw_hidden_field('tlid',$tlid);
?>
          <tr>
            <td class="ticketSmallText" valign="top">
<?php 
        echo TEXT_COMMENT . '<br><br><br>';
        if (TICKET_CATALOG_USE_STATUS == 'true' && TICKET_ALLOW_CUSTOMER_TO_CHANGE_STATUS == 'true') {
          echo TEXT_STATUS . '&nbsp;' . tep_draw_pull_down_menu('status', $ticket_statuses, ($ticket_last_used_status ? $ticket_last_used_status : TICKET_DEFAULT_STATUS_ID) ) . "<br><br>";
        } else {
           echo tep_draw_hidden_field('status', ($ticket_last_used_status ? $ticket_last_used_status : TICKET_DEFAULT_STATUS_ID) );
        }
        if (TICKET_CATALOG_USE_DEPARTMENT == 'true' && TICKET_ALLOW_CUSTOMER_TO_CHANGE_DEPARTMENT == 'true') {
          echo TEXT_DEPARTMENT . '&nbsp;' . tep_draw_pull_down_menu('department', $ticket_departments, ($ticket_last_used_department ? $ticket_last_used_department : TICKET_DEFAULT_DEPARTMENT_ID) ) . "<br><br>";
        } else {
           echo tep_draw_hidden_field('department', ($ticket_last_used_department ? $ticket_last_used_department : TICKET_DEFAULT_DEPARTMENT_ID) );
        }
        if (TICKET_CATALOG_USE_PRIORITY == 'true' && TICKET_ALLOW_CUSTOMER_TO_CHANGE_PRIORITY == 'true') {
          echo TEXT_PRIORITY . '&nbsp;' . tep_draw_pull_down_menu('priority', $ticket_prioritys, ($ticket_last_used_priority ? $ticket_last_used_priority : TICKET_DEFAULT_PRIORITY_ID) ) . "<br><br>";
        } else {
          echo tep_draw_hidden_field('priority', ($ticket_last_used_priority ? $ticket_last_used_priority : TICKET_DEFAULT_PRIORITY_ID) );
        }
?>
            </td>
            <td  class="ticketSmallText" ><?php echo tep_draw_textarea_field('enquiry', 'soft', 50, 15,'','',false); ?></td>
          </tr>
          <tr>
            <td colspan=2 class="main" align="center"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></td>
          </tr>
          </form>
        </table></td>
      </tr> 
<?php
    }
  }
?>      
 
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
