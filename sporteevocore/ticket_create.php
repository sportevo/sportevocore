<?php
/*
  $Id: ticket_create.php,v 1.5 2003/04/25 21:37:12 hook Exp $

  OSC-SupportTicketSystem
  Copyright (c) 2003 Henri Schmidhuber IN-Solution
  
  Contribution based on:

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  if (!tep_session_is_registered('customer_id') && $_GET['login']=="yes") {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }


  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_TICKET_CREATE);
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
 
  $email = tep_db_prepare_input(trim($_POST['email']));
  $name = tep_db_prepare_input($_POST['name']);
  $subject = tep_db_prepare_input($_POST['subject']);
  $enquiry = tep_db_prepare_input($_POST['enquiry']);
  $department = tep_db_prepare_input($_POST['department']);
  $priority = tep_db_prepare_input($_POST['priority']);
  $ticket_customers_orders_id = tep_db_prepare_input($_POST['ticket_customers_orders_id']);
  
  
// Customer is logged in:  
  if (tep_session_is_registered('customer_id')) {
    $customer_query = tep_db_query("select customers_firstname, customers_lastname, customers_email_address from " . TABLE_CUSTOMERS . " where customers_id = '" . $customer_id . "'");
    $customer = tep_db_fetch_array($customer_query);
  }
 
  //VISUAL VERIFY CODE start
  require(DIR_WS_FUNCTIONS . 'visual_verify_code.php');
  
  $code_query = tep_db_query("select code from visual_verify_code where oscsid = '" . $_GET['osCsid'] . "'");
  $code_array = tep_db_fetch_array($code_query);
  $code = $code_array['code'];
  
  tep_db_query("DELETE FROM " . TABLE_VISUAL_VERIFY_CODE . " WHERE oscsid='" . $vvcode_oscsid . "'"); //remove the visual verify code associated with this session to clean database and ensure new results
  
  $user_entered_code = $_POST['visual_verify_code'];
  if (!(strcasecmp($user_entered_code, $code) == 0)) {    //make the check case insensitive
  	$error = true;
  	$messageStack->add('contact', VISUAL_VERIFY_CODE_ENTRY_ERROR);
  }
  //VISUAL VERIFY CODE stop
// Form was submitted
  $error = false;
  if (isset($_GET['action']) && ($_GET['action'] == 'send')) {
  // Check Name length
    if (!tep_session_is_registered('customer_id') && isset($name) && strlen($name) < TICKET_ENTRIES_MIN_LENGTH ) {
       $error = true;
       $error_name = true;
     }
    
 // Check Subject length
    if (isset($subject) && strlen($subject) < TICKET_ENTRIES_MIN_LENGTH ) {
        $error = true;
        $error_subject = true;
      }
  // Check Message length
    if (isset($enquiry) && strlen($enquiry) < TICKET_ENTRIES_MIN_LENGTH ) {
        $error = true;
        $error_enquiry = true;
      }
  // Check Email for non logged in Customers
    if (!tep_session_is_registered('customer_id') && !tep_validate_email($email)) {
      $error = true;
      $error_email = true;
    } 
    
   
    
    if ($error == false) {
      $ticket_customers_id = '';
    // Get the customers_id
      if (tep_session_is_registered('customer_id')) {
        $ticket_customers_id = $customer_id;
      } else {
        $customerid_query = tep_db_query("select customers_id from " . TABLE_CUSTOMERS . " where customers_email_address='" . tep_db_input($email) . "'");
        if ($customerid = tep_db_fetch_array($customerid_query)) $ticket_customers_id = $customerid['customers_id'] ;
      }
      
      
      
      // generate LInkID
      $time = mktime();
      $ticket_link_id = '';
      for ($x=3;$x<10;$x++) {
        $ticket_link_id .= substr($time,$x,1) . tep_create_random_value(1, $type = 'chars');
      }
      
      $sql_data_array = array('ticket_link_id' => $ticket_link_id,
                          'ticket_customers_id' => $ticket_customers_id,
                          'ticket_customers_orders_id' => $ticket_customers_orders_id,
                          'ticket_customers_email' => $email,
                          'ticket_customers_name' => $name,
                          'ticket_subject' => $subject,
                          'ticket_status_id' => TICKET_DEFAULT_STATUS_ID,
                          'ticket_department_id' => $department,
                          'ticket_priority_id' => $priority,
                          'ticket_login_required' => TICKET_CUSTOMER_LOGIN_REQUIREMENT_DEFAULT,
                          'ticket_date_last_modified' => 'now()',
                          'ticket_date_last_customer_modified' => 'now()',
                          'ticket_date_created' => 'now()');
      tep_db_perform(TABLE_TICKET_TICKET, $sql_data_array);
      $insert_id = tep_db_insert_id();
      
      $sql_data_array = array('ticket_id' => $insert_id,
                          'ticket_status_id' => TICKET_DEFAULT_STATUS_ID,
                          'ticket_priority_id' => $priority,
                          'ticket_department_id' => $department,
                          'ticket_date_modified' => 'now()',
                          'ticket_customer_notified' => '1',
                          'ticket_edited_by' => $name,
                          'ticket_comments' => $enquiry);
      tep_db_perform(TABLE_TICKET_STATUS_HISTORY, $sql_data_array); 
    // Email  Customer doesn't get the Message cause he should use the web
      $ticket_email_subject = TICKET_EMAIL_SUBJECT . $subject;
      $ticket_email_message = TICKET_EMAIL_MESAGE_HEADER . "\n\n" . tep_href_link(FILENAME_TICKET_VIEW, 'tlid=' . $ticket_link_id, 'NONSSL',false,false) . "\n\n" . TICKET_EMAIL_TICKET_NR . " " . $ticket_link_id . "\n" . TICKET_EMAIL_MESAGE_FOOTER;
      tep_mail($name, $email, $ticket_email_subject, nl2br($ticket_email_message), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
    // send emails to other people
      if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
       $ticket_email_message = TICKET_EMAIL_MESAGE_HEADER . "\n\n" . tep_href_link(FILENAME_TICKET_VIEW, 'tlid=' . $ticket_link_id) . "\n\n" . $enquiry . TICKET_EMAIL_MESAGE_FOOTER . "\n\n" . $enquiry;
       tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, $ticket_email_subject,nl2br($ticket_email_message), STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
      }
	  
	  
	   // Create Ticket in vtiger CRM 
     // require_once(DIR_WS_INCLUDES . 'vtiger/vtwsclib/Vtiger/WSClient.php');
     // $vtiger_ws_client = new Vtiger_WSClient($vtiger_url);
     // $login = $vtiger_ws_client->doLogin($vtiger_username, $vtiger_accesskey);
     // if(!$login) {
      //    $lasterr = $vtiger_ws_client->lastError();
      //    die('Please contact an administrator, error:'.$lasterr['message']);
     // }
     // $records = $vtiger_ws_client->doQuery('SELECT id FROM Accounts WHERE email=\''.$email.'\'');
    //  if (isset($records[0]['id']) && !empty($records[0]['id']))
    //     $parent_acc = $records[0]['id'];
    //  else
    //     $parent_acc = '';
    //  $vt_module = 'HelpDesk';
    //  $user_data = array(
     //    'ticket_title' => $subject,
     //    'ticketstatus' => 'Open',
      //   'cf_653' => $parent_acc,
      //   'description' => 'Created by OSC for '.$email."\n".$enquiry,
     // );
    //  $record = $vtiger_ws_client->doCreate($vt_module,$user_data);
    //  $lasterr = $vtiger_ws_client->lastError();
    // if ($lasterr) {
    //     die('Please contact an administrator, error:'.$lasterr['message']);
    //  }
	
    //    $vt_module = 'Leads';
    //    $vtiger_query = tep_db_query("select countries_name from countries where countries_id = '" . (int)$country . "'"); 
    //    $vtiger_array = tep_db_fetch_array($vtiger_query); 
    //    $country_name = $vtiger_array["countries_name"]; 
    //    $user_data = array(
     //       'lastname'=> $name,
	//		'description' => $enquiry,
     //       'email' => $email, 
            
            //'inesistente'=>'bal',
   //     );
   //     $record = $vtiger_ws_client->doCreate($vt_module,$user_data);
   //     $lasterr = $vtiger_ws_client->lastError();
   //     if ($lasterr) {
    //        die('Please contact an administrator, error:'.$lasterr['message']);
    //    }
	  
	  
      // End of vtiger integration 
	  
      tep_redirect(tep_href_link(FILENAME_TICKET_CREATE, 'action=success&tlid=' . $ticket_link_id ));
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

</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->

<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
  
<!-- body_text //-->
   <div id="stscart">
            <h2><?php echo HEADING_TITLE; ?></h2>
     <hr class="hrloginhr">
<span class="h2boxcont">
<?php
  if (!tep_session_is_registered('customer_id')) {
     echo  sprintf(TEXT_LOGIN, tep_href_link(FILENAME_TICKET_CREATE, 'login=yes', 'SSL'), tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL')); 
  }
?>
</span>
 <hr class="hrloginhr">     

<?php
  if (isset($_GET['action']) && ($_GET['action'] == 'success')) {
?>
     <hr class="hrloginhr">       
         
            <div class="maincapture"><?php echo TEXT_SUCCESS; ?></div>
        
            <div class="maincapture"><?php echo TEXT_YOUR_TICKET_ID . ' ' . $_GET['tlid']; ?></div>
       
            <div class="maincapture"><?php echo TEXT_CHECK_YOUR_TICKET . '<br><a href="' . tep_href_link(FILENAME_TICKET_VIEW, 'tlid=' . $_GET['tlid'], 'NONSSL',false,false) . '">' . tep_href_link(FILENAME_TICKET_VIEW, 'tlid=' . $_GET['tlid'], 'NONSSL',false,false) . '</a>'; ?></div>
         
            <div id="buttonbox"><a href="<?php echo tep_href_link(FILENAME_DEFAULT); ?>"><?php echo tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></a></div>
          
<?php
  } else {
?>
    <?php echo tep_draw_form('contact_us', tep_href_link(FILENAME_TICKET_CREATE, 'action=send')); ?>
          <div class="createBox">
          <div class="createBoxContents">
            <div class="maincapture">
            <span style="float:left; display:inlne; width:40%; text-transform:uppercase;">
            <?php echo ENTRY_NAME; ?></span>
            
<?php
    if (tep_session_is_registered('customer_id')) {
      echo '<span style="float:right; display:inlne; width:70%; ">' . tep_draw_hidden_field('name',$customer['customers_firstname'] . ' ' . $customer['customers_lastname']) . $customer['customers_firstname'] . ' ' . $customer['customers_lastname'] . '</span>'; 
    } else {
      echo '<span style="float:right; display:inlne; width:70%; ">' . tep_draw_input_fieldb('name', ($error ? $name : $first_name)); if ($error_name) echo ENTRY_ERROR_NO_NAME . '</span>';
    }
?>
            </div>
            

         
         
            <div class="maincapture"><span style="float:left; display:inlne; width:40%; text-transform:uppercase;"><?php echo ENTRY_EMAIL; ?></span>
            
<?php
    if (tep_session_is_registered('customer_id')) {
      echo '<span style="float:right; display:inlne; width:70%;">' . tep_draw_hidden_field('email',$customer['customers_email_address']) . $customer['customers_email_address'] . '</span>'; 
    } else {
      echo '<span style="float:right; display:inlne;  width:70%;">' . tep_draw_input_fieldb('email', ($error ? $email : $email_address)); if ($error_email) echo ENTRY_EMAIL_ADDRESS_CHECK_ERROR . '</span>'; 
    }
?>
            </div>
        
<?php
    if (TICKET_SHOW_CUSTOMERS_SUBJECT == 'true') {   
?>
          
            <div class="maincapture"><span style="float:left; display:inlne; width:40%; text-transform:uppercase;"><?php echo ENTRY_SUBJECT; ?></span>
             <span style="float:right; display:inlne; width:70%;"><?php  echo tep_draw_input_fieldb('subject', ($error ? $subject : $subject)); if ($error_subject) echo ENTRY_ERROR_NO_SUBJECT; ?></span></div>
            
     
<?php
    }
    if (TICKET_SHOW_CUSTOMERS_ORDER_IDS == 'true' && tep_session_is_registered('customer_id')) {     
      $customers_orders_query = tep_db_query("select orders_id, date_purchased from " . TABLE_ORDERS . " where customers_id = '" . tep_db_input($customer_id) . "'");
      if (isset($_GET['ticket_order_id'])) $ticket_preselected_order_id = $_GET['ticket_order_id'];
      $orders_array[] = array('id' => '', 'text' => ' -- ' );
      while ($customers_orders = tep_db_fetch_array($customers_orders_query)) {
        $orders_array[] = array('id' => $customers_orders['orders_id'], 'text' => $customers_orders['orders_id'] . "  (" . tep_date_short($customers_orders['date_purchased']) . ")" );
      }

?>
         
</div>
            <div class="maincapture"><span style="float:left; display:inlne; width:40%; text-transform:uppercase;"><?php echo ENTRY_ORDER; ?>&nbsp;</span>
          <?php echo  tep_draw_pull_down_menu('ticket_customers_orders_id', $orders_array,$ticket_preselected_order_id); ?></div>
            



<?php
    }
    if (TICKET_CATALOG_USE_DEPARTMENT == 'true') {     
?><div class="createBox">
 <div class="createBoxContents">
            <div class="maincapture"><span style="float:left; display:inlne; width:40%; text-transform:uppercase;"><?php echo ENTRY_DEPARTMENT; ?>&nbsp;</span>
            <?php echo tep_draw_pull_down_menu('department', $ticket_departments, ($department ? $department : TICKET_DEFAULT_DEPARTMENT_ID) ); ?></div>
      
           
<?php
    } else {
      echo tep_draw_hidden_field('department', TICKET_DEFAULT_DEPARTMENT_ID);
    }
    if (TICKET_CATALOG_USE_PRIORITY == 'true') {   
?>
         
            <div class="maincapture"><span style="float:left; display:inlne; width:40%; text-transform:uppercase;"><?php echo ENTRY_PRIORITY; ?></span>
           <?php echo tep_draw_pull_down_menu('priority', $ticket_prioritys, ($priority ? $priority : TICKET_DEFAULT_PRIORITY_ID) ); ?></div>
            
        
<?php
    } else {
      echo tep_draw_hidden_field('priority', TICKET_DEFAULT_PRIORITY_ID);
    }
?>
        </div> 
        </div>
                    
            <div class="maincapture"><span style="float:left; display:inlne; width:40%; text-transform:uppercase;"><?php echo ENTRY_ENQUIRY; ?></span></div>
         
         <div class="maincapture">   <?php echo '<span style="float:right; display:inlne-block; width:70%; ">' . tep_draw_textarea_field('enquiry', 'soft', 40, 15, $enquiry) . '</span>'; ?></div><?php if ($error_enquiry) echo ENTRY_ERROR_NO_ENQUIRY; ?>
       
       
         <hr class="hrloginhr">     
        
                <div class="pageHeading"><?php echo VISUAL_VERIFY_CODE_CATEGORY; ?></div>
      
        
          
    
           </div>
        <hr class="hrloginhr"> 
       <div class="createBox">
                  <div class="createBoxContents">
                     
                               
                                <div class="main"><div class="maincapture">
                                  <?php
                                          //can replace the following loop with $visual_verify_code = substr(str_shuffle (VISUAL_VERIFY_CODE_CHARACTER_POOL), 0, rand(3,6)); if you have PHP 4.3
                                        $visual_verify_code = "";
                                        for ($i = 1; $i <= rand(3,6); $i++){
                                                  $visual_verify_code = $visual_verify_code . substr(VISUAL_VERIFY_CODE_CHARACTER_POOL, rand(0, strlen(VISUAL_VERIFY_CODE_CHARACTER_POOL)-1), 1);
                                         }
                                         $vvcode_oscsid = $_GET['osCsid'];
                                         tep_db_query("DELETE FROM " . TABLE_VISUAL_VERIFY_CODE . " WHERE oscsid='" . $vvcode_oscsid . "'");
                                         $sql_data_array = array('oscsid' => $vvcode_oscsid, 'code' => $visual_verify_code);
                                         tep_db_perform(TABLE_VISUAL_VERIFY_CODE, $sql_data_array);
                                         $visual_verify_code = "";
                                         echo('<img src="' . FILENAME_VISUAL_VERIFY_CODE_DISPLAY . '?vvc=' . $vvcode_oscsid . '"');
                                  ?>
                                
                                </div><?php echo tep_draw_input_fieldb('visual_verify_code') . '&nbsp;' . '<span class="inputRequirement">' . VISUAL_VERIFY_CODE_ENTRY_TEXT . '</span>'; ?></div>

                                
                                
                  </div>
                  </div>   
            <div id="buttonbox"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?>
                </div> 
      </div>  
<?php
  }
?>
   
<!-- body_text_eof //-->
  
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->

<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
 </form>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
