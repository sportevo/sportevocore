<?php
/*
  $Id: create_account.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  // +Country-State Selector
  require(DIR_WS_FUNCTIONS . 'ajax.php');
if (isset($_POST['action']) && $_POST['action'] == 'getStates' && isset($_POST['country'])) {
	ajax_get_zones_html(tep_db_prepare_input($_POST['country']), '', true);
} else {
  // -Country-State Selector

// needs to be included earlier to set the success message in the messageStack
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CREATE_ACCOUNT);

  // BOF Pulldown date of birth 1.7 
  if(isset($_POST['dob_ind']) && isset($_POST['dob_inm']) && isset($_POST['dob_inY'])) {
  	$_POST['dob'] = $_POST['dob_ind'].'/'.$_POST['dob_inm'].'/'.$_POST['dob_inY'];
  }
  // EOF Pulldown date of birth 1.7
  
  $process = false;
  if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
    $process = true;

    if (ACCOUNT_GENDER == 'true') {
      if (isset($_POST['gender'])) {
        $gender = tep_db_prepare_input($_POST['gender']);
      } else {
        $gender = false;
      }
    }
    $firstname = tep_db_prepare_input($_POST['firstname']);
    $firstname = tep_db_prepare_input($_POST['firstname']);
    $lastname = tep_db_prepare_input($_POST['lastname']);
    if (ACCOUNT_DOB == 'true') $dob = tep_db_prepare_input($_POST['dob']);
    $email_address = tep_db_prepare_input($_POST['email_address']);
    if (ACCOUNT_COMPANY == 'true') $company = tep_db_prepare_input($_POST['company']);
    $street_address = tep_db_prepare_input($_POST['street_address']);
    if (ACCOUNT_SUBURB == 'true') $suburb = tep_db_prepare_input($_POST['suburb']);
    $postcode = tep_db_prepare_input($_POST['postcode']);
    $city = tep_db_prepare_input($_POST['city']);
    if (ACCOUNT_STATE == 'true') {
      $state = tep_db_prepare_input($_POST['state']);
      if (isset($_POST['zone_id'])) {
        $zone_id = tep_db_prepare_input($_POST['zone_id']);
      } else {
        $zone_id = false;
      }
    }
    $country = tep_db_prepare_input($_POST['country']);
    $telephone = tep_db_prepare_input($_POST['telephone']);
    $fax = tep_db_prepare_input($_POST['fax']);
    if (isset($_POST['newsletter'])) {
      $newsletter = tep_db_prepare_input($_POST['newsletter']);
    } else {
      $newsletter = false;
    }
    $password = tep_db_prepare_input($_POST['password']);
    $confirmation = tep_db_prepare_input($_POST['confirmation']);

    $error = false;

    if (ACCOUNT_GENDER == 'true') {
      if ( ($gender != 'm') && ($gender != 'f') ) {
        $error = true;

        $messageStack->add('create_account', ENTRY_GENDER_ERROR);
      }
    }

    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_FIRST_NAME_ERROR);
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_LAST_NAME_ERROR);
    }

    if (ACCOUNT_DOB == 'true') {
      if (checkdate(substr(tep_date_raw($dob), 4, 2), substr(tep_date_raw($dob), 6, 2), substr(tep_date_raw($dob), 0, 4)) == false) {
        $error = true;

        $messageStack->add('create_account', ENTRY_DATE_OF_BIRTH_ERROR);
      }
    }

    if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR);
    } elseif (tep_validate_email($email_address) == false) {
      $error = true;

      $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    } else {
      $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "'");
      $check_email = tep_db_fetch_array($check_email_query);
      if ($check_email['total'] > 0) {
        $error = true;

        $messageStack->add('create_account', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
      }
    }

    if (strlen($street_address) < ENTRY_STREET_ADDRESS_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_STREET_ADDRESS_ERROR);
    }

    if (strlen($postcode) < ENTRY_POSTCODE_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_POST_CODE_ERROR);
    }

    if (strlen($city) < ENTRY_CITY_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_CITY_ERROR);
    }

    if (is_numeric($country) == false) {
      $error = true;

      $messageStack->add('create_account', ENTRY_COUNTRY_ERROR);
    }

    if (ACCOUNT_STATE == 'true') {
      // +Country-State Selector
      if ($zone_id == 0) {
      // -Country-State Selector

        if (strlen($state) < ENTRY_STATE_MIN_LENGTH) {
          $error = true;

          $messageStack->add('create_account', ENTRY_STATE_ERROR);
        }
      }
    }

    if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_TELEPHONE_NUMBER_ERROR);
    }


    if (strlen($password) < ENTRY_PASSWORD_MIN_LENGTH) {
      $error = true;

      $messageStack->add('create_account', ENTRY_PASSWORD_ERROR);
    } elseif ($password != $confirmation) {
      $error = true;

      $messageStack->add('create_account', ENTRY_PASSWORD_ERROR_NOT_MATCHING);
    }

    if ($error == false) {
      $sql_data_array = array('customers_firstname' => $firstname,
                              'customers_lastname' => $lastname,
                              'customers_email_address' => $email_address,
                              'customers_telephone' => $telephone,
                              'customers_fax' => $fax,
                              'customers_newsletter' => $newsletter,
                              'customers_password' => tep_encrypt_password($password));

      if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
      if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw($dob);

      tep_db_perform(TABLE_CUSTOMERS, $sql_data_array);

      $customer_id = tep_db_insert_id();

      $sql_data_array = array('customers_id' => $customer_id,
                              'entry_firstname' => $firstname,
                              'entry_lastname' => $lastname,
                              'entry_street_address' => $street_address,
                              'entry_postcode' => $postcode,
                              'entry_city' => $city,
                              'entry_country_id' => $country);

      if (ACCOUNT_GENDER == 'true') $sql_data_array['entry_gender'] = $gender;
      if (ACCOUNT_COMPANY == 'true') $sql_data_array['entry_company'] = $company;
      if (ACCOUNT_SUBURB == 'true') $sql_data_array['entry_suburb'] = $suburb;
      if (ACCOUNT_STATE == 'true') {
        if ($zone_id > 0) {
          $sql_data_array['entry_zone_id'] = $zone_id;
          $sql_data_array['entry_state'] = '';
        } else {
          $sql_data_array['entry_zone_id'] = '0';
          $sql_data_array['entry_state'] = $state;
        }
      }

      tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array);

      $address_id = tep_db_insert_id();

      tep_db_query("update " . TABLE_CUSTOMERS . " set customers_default_address_id = '" . (int)$address_id . "' where customers_id = '" . (int)$customer_id . "'");

      tep_db_query("insert into " . TABLE_CUSTOMERS_INFO . " (customers_info_id, customers_info_number_of_logons, customers_info_date_account_created) values ('" . (int)$customer_id . "', '0', now())");

      if (SESSION_RECREATE == 'True') {
        tep_session_recreate();
      }

      $customer_first_name = $firstname;
      $customer_default_address_id = $address_id;
      $customer_country_id = $country;
      $customer_zone_id = $zone_id;
      tep_session_register('customer_id');
      tep_session_register('customer_first_name');
      tep_session_register('customer_default_address_id');
      tep_session_register('customer_country_id');
      tep_session_register('customer_zone_id');

// restore cart contents
      $cart->restore_contents();

// build the message content
      $name = $firstname . ' ' . $lastname;

      if (ACCOUNT_GENDER == 'true') {
         if ($gender == 'm') {
           $email_text = sprintf(EMAIL_GREET_MR, $lastname);
         } else {
           $email_text = sprintf(EMAIL_GREET_MS, $lastname);
         }
      } else {
        $email_text = sprintf(EMAIL_GREET_NONE, $firstname);
      }

      $email_text .= EMAIL_WELCOME . EMAIL_TEXT . EMAIL_CONTACT . EMAIL_WARNING;
      tep_mail($name, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

      tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
    }
  }
 // +Country-State Selector 
if (!isset($country)){$country = DEFAULT_COUNTRY;}
// -Country-State Selector

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">

<?php 
 // +Country-State Selector 
require('includes/form_check.js.php'); 
require('includes/ajax.js.php'); 
// -Country-State Selector
?>
</head> 
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->

<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    <div id="stscart">
<!-- body_text //-->        <h2><?php echo HEADING_TITLE; ?></h2>   
<hr class="hrloginhr">
<div class="pageHeading"><?php echo sprintf(TEXT_ORIGIN_LOGIN, tep_href_link(FILENAME_LOGIN, tep_get_all_get_params(), 'SSL')); ?></div>
<hr class="hrloginhr">
  <?php echo tep_draw_form('create_account', tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'), 'post', 'onSubmit="return check_form(create_account);"') . tep_draw_hidden_field('action', 'process'); ?><div id="indicator"></div>

<?php
  if ($messageStack->size('create_account') > 0) {
?>
      
        <span class="message"><?php echo $messageStack->output('create_account'); ?></span>
      
       
      
<?php
  }
?>
     <div class="createBox">
          <div class="createBoxContents">
      
            <span class="h2boxcont"><?php echo CATEGORY_PERSONAL; ?></span>
           <span class="inputRequirement" align="right"><?php echo FORM_REQUIRED_INFORMATION; ?></span>
         
<?php
  if (ACCOUNT_GENDER == 'true') {
?>
            
                <div class="maincapture">
                <span stlye="float:left; display:inlne; width:40%;"><?php echo ENTRY_GENDER; ?></span>
               
<span stlye="float:right; display:inlne; width:40%;"><?php echo tep_draw_radio_field('gender', 'm') . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . tep_draw_radio_field('gender', 'f') . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . (tep_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">' . ENTRY_GENDER_TEXT . '</span>': ''); ?></span>
          </div>   
<?php
  }
?>
</div>
                <div class="createBoxContents">
                <div class="maincapture">
                <span stlye="float:left; display:inlne; width:40%;"><?php echo ENTRY_FIRST_NAME; ?></span>
                <span stlye="float:right; display:inlne; width:40%;"> <?php echo tep_draw_input_fieldb('firstname') . '&nbsp;' . (tep_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>': ''); ?></span>
                
                </div>
             
              
                <div class="maincapture"><span stlye="float:right; display:inlne; width:40%;"><?php echo ENTRY_LAST_NAME; ?></span>
                <?php echo tep_draw_input_fieldb('lastname') . '&nbsp;' . (tep_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</span>': ''); ?>
              </div>
              </div>
<?php
  if (ACCOUNT_DOB == 'true') {
?>
              <div class="createBoxContents">
                <div class="maincapture">
               <?php echo ENTRY_DATE_OF_BIRTH; ?>
                <!--BOF Pulldown date of birth 1.7 -->
		   <div style="float:right;"><?php echo tep_draw_pull_down_date('dob_in', '', '', (isset($_POST['dob_inY'])? $_POST['dob_inY'] : 1980), false, true, 1900); 
                       		         echo '&nbsp;' . (tep_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '<span class="inputRequirement">' . ENTRY_DATE_OF_BIRTH_TEXT . '</span>': '');?></div></div>
<!--EOF Pulldown date of birth 1.7 -->
              </div>
<?php
  }
?>
               <div class="createBoxContents">
                <div class="maincapture"><?php echo ENTRY_EMAIL_ADDRESS; ?>
                <?php echo tep_draw_input_fieldb('email_address') . '&nbsp;' . (tep_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_EMAIL_ADDRESS_TEXT . '</span>': ''); ?></div>
              </div>
           
<?php
  if (ACCOUNT_COMPANY == 'true') {
?>
      </div>
    
    
    
       <div class="createBox">
         
            <div class="h2boxcont"><?php echo CATEGORY_COMPANY; ?></div>
              <div class="createBoxContents">
                <div class="maincapture"><?php echo ENTRY_COMPANY; ?>
               <?php echo tep_draw_input_fieldb('company') . '&nbsp;' . (tep_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</span>': ''); ?></div>
              </div>
            
            </div>
<?php
  }
?>
     
     
     
         <div class="createBox">
           <div class="h2boxcont"><?php echo CATEGORY_ADDRESS; ?></div>
          <div class="createBoxContents">
          
                <div class="maincapture"><?php echo ENTRY_STREET_ADDRESS; ?>
                <?php echo tep_draw_input_fieldb('street_address') . '&nbsp;' . (tep_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_STREET_ADDRESS_TEXT . '</span>': ''); ?></div>
            
<?php
  if (ACCOUNT_SUBURB == 'true') {
?>
              
                <div class="maincapture"><?php echo ENTRY_SUBURB; ?>
                <?php echo tep_draw_input_fieldb('suburb') . '&nbsp;' . (tep_not_null(ENTRY_SUBURB_TEXT) ? '<span class="inputRequirement">' . ENTRY_SUBURB_TEXT . '</span>': ''); ?></div>
             
<?php
  }
?>
            
                <div class="maincapture"><?php echo ENTRY_POST_CODE; ?>
               <?php echo tep_draw_input_fieldb('postcode') . '&nbsp;' . (tep_not_null(ENTRY_POST_CODE_TEXT) ? '<span class="inputRequirement">' . ENTRY_POST_CODE_TEXT . '</span>': ''); ?></div>
            
                <div class="maincapture"><?php echo ENTRY_CITY; ?>
               <?php echo tep_draw_input_fieldb('city') . '&nbsp;' . (tep_not_null(ENTRY_CITY_TEXT) ? '<span class="inputRequirement">' . ENTRY_CITY_TEXT . '</span>': ''); ?></div> 
              
              
            
                      <div class="maincapture" ><?php echo ENTRY_COUNTRY; ?>
                     <div style="float:right;"> <?php // +Country-State Selector ?>
                      <?php echo tep_get_country_list('country',$country,'onChange="getStates(this.value, \'states\');"') . '&nbsp;' . (tep_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COUNTRY_TEXT . '</span>': ''); ?></div>
                      <?php // -Country-State Selector ?>
                   </div>
              
<?php
  if (ACCOUNT_STATE == 'true') {
?>
                   
                      <div class="maincapture" ><?php echo ENTRY_STATE; ?>
                     <div style="float:right;"><span id="states">
                          <?php
				// +Country-State Selector
				echo ajax_get_zones_html($country,'',false);
				// -Country-State Selector
				?>
				  </span></div>
   </div>
         </div></div>
                      
                    
                    <?php
  }
?>
                    
     
     
      <div class="createBox"> <div class="h2boxcont"><?php echo CATEGORY_CONTACT; ?></div>
          <div class="createBoxContents">
     
                <div class="maincapture"><?php echo ENTRY_TELEPHONE_NUMBER; ?>
               <?php echo tep_draw_input_fieldb('telephone') . '&nbsp;' . (tep_not_null(ENTRY_TELEPHONE_NUMBER_TEXT) ? '<span class="inputRequirement">' . ENTRY_TELEPHONE_NUMBER_TEXT . '</span>': ''); ?></div>
             
                <div class="maincapture"><?php echo ENTRY_FAX_NUMBER; ?>
               <?php echo tep_draw_input_fieldb('fax') . '&nbsp;' . (tep_not_null(ENTRY_FAX_NUMBER_TEXT) ? '<span class="inputRequirement">' . ENTRY_FAX_NUMBER_TEXT . '</span>': ''); ?></div>
              </div>
           
        </div>
        
       
      <div class="createBox">
       <div class="h2boxcont"><?php echo CATEGORY_PASSWORD; ?></div>
          <div class="createBoxContents">
                <div class="maincapture"><?php echo ENTRY_PASSWORD; ?>
               <?php echo tep_draw_password_fieldb('password') . '&nbsp;' . (tep_not_null(ENTRY_PASSWORD_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_TEXT . '</span>': ''); ?></div>
             
                <div class="maincapture"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?>
                <?php echo tep_draw_password_fieldb('confirmation') . '&nbsp;' . (tep_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '</span>': ''); ?></div>
              </div>
         
    
            </div>
     <hr class="hrloginhr">
    

     <div class="createBox">
          <div class="createBoxContents">
             <span class="h2boxcont"><?php echo CATEGORY_OPTIONS; ?></span>
                <div class="maincapture"><?php echo ENTRY_NEWSLETTER; ?>
               <div id="states"><?php echo tep_draw_checkbox_field('newsletter', '1', 'checked') . '&nbsp;' . (tep_not_null(ENTRY_NEWSLETTER_TEXT) ? '<span class="inputRequirement">' . ENTRY_NEWSLETTER_TEXT . '</span>': ''); ?></div></div>
              </div>
        </div>
         <hr class="hrloginhr">
      
          <div id="buttonbox"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>     
    
               
<!-- body_text_eof //-->
   
<!-- right_navigation //-->
<?php include(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
   
<!-- body_eof //-->

<!-- footer //-->
<?php include(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</div></form>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>			
<?php
// +Country-State Selector 
}
// -Country-State Selector 
?>