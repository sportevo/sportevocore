<?php
/*
  $Id: create_account.php,v 1.65 2003/06/09 23:03:54 hpdl Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  
  // +Country-State Selector
  require(DIR_WS_FUNCTIONS . 'ajax.php');
  if (isset($_POST['action']) && $_POST['action'] == 'getStates' && isset($_POST['country'])) {
  	ajax_get_zones_html(tep_db_prepare_input($_POST['country']), true);
  } else {
  	// -Country-State Selector
  
  
// needs to be included earlier to set the success message in the messageStack
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CREATE_ACCOUNT);

  
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
    $lastname = tep_db_prepare_input($_POST['lastname']);
    if (ACCOUNT_DOB == 'true') $dob = tep_db_prepare_input($_POST['dob']);
    $email_address = tep_db_prepare_input($_POST['email_address']);
    // BOF Separate Pricing Per Customer, added: field for tax id number
    if (ACCOUNT_COMPANY == 'true') {
    $company = tep_db_prepare_input($_POST['company']);
    $company_tax_id = tep_db_prepare_input($_POST['company_tax_id']);
    }
// EOF Separate Pricing Per Customer, added: field for tax id number
	
	     //PIVACF start
       if (ACCOUNT_PIVA == 'true') $piva = tep_db_prepare_input($_POST['piva']);
       if (ACCOUNT_CF == 'true') $cf = tep_db_prepare_input($_POST['cf']);
    //PIVACF end

	
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

//PIVACF start
       if (ACCOUNT_PIVA == 'true'){
         if (($piva == "") && (ACCOUNT_PIVA_REQ == 'true')) {
           $error = true;
              $messageStack->add('create_account', ENTRY_PIVA_ERROR);
         } else if ((strlen($piva) < 11) && ($piva != ""))  {
        $error = true;
        $messageStack->add('create_account', ENTRY_PIVA_ERROR);
      } else if (strlen($piva) == 11) {
           if( ! ereg("^[0-9]+$", $piva) ) {
             $error = true;
             $messageStack->add('create_account', ENTRY_PIVA_ERROR);
        } else {
             $s = 0;
                for( $i = 0; $i <= 9; $i += 2 ) $s += ord($piva[$i]) - ord('0');
                for( $i = 1; $i <= 9; $i += 2 ) {
                  $c = 2*( ord($piva[$i]) - ord('0') );
                  if( $c > 9 ) $c = $c - 9;
                  $s += $c;
             }
             if( ( 10 - $s%10 )%10 != ord($piva[10]) - ord('0') ) {
            $error = true;
            $messageStack->add('create_account', ENTRY_PIVA_ERROR);
          }
           }
         }    
       }
       if (ACCOUNT_CF == 'true') {
         if (($cf == "") && (ACCOUNT_CF_REQ == 'true')) {
           $error = true;
              $messageStack->add('create_account', ENTRY_CF_ERROR);
         } else if ((strlen($cf) != 16) && ($cf != "")) {
           $error = true;
              $messageStack->add('create_account', ENTRY_CF_ERROR);
         } else if (strlen($cf) == 16) {
              $cf = strtoupper($cf);
              if( ! ereg("^[A-Z0-9]+$", $cf) ){
                $error = true;
                $messageStack->add('create_account', ENTRY_CF_ERROR);
           } else { 
                $s = 0;
                for( $i = 1; $i <= 13; $i += 2 ){
                  $c = $cf[$i];
                  if( '0' <= $c && $c <= '9' )
                       $s += ord($c) - ord('0');
                  else
                       $s += ord($c) - ord('A');
                }
                for( $i = 0; $i <= 14; $i += 2 ){
                  $c = $cf[$i];
                  switch( $c ){
                    case '0':  $s += 1;  break;
                    case '1':  $s += 0;  break;
                    case '2':  $s += 5;  break;
                    case '3':  $s += 7;  break;
                    case '4':  $s += 9;  break;
                    case '5':  $s += 13;  break;
                    case '6':  $s += 15;  break;
                    case '7':  $s += 17;  break;
                    case '8':  $s += 19;  break;
                    case '9':  $s += 21;  break;
                    case 'A':  $s += 1;  break;
                    case 'B':  $s += 0;  break;
                    case 'C':  $s += 5;  break;
                    case 'D':  $s += 7;  break;
                    case 'E':  $s += 9;  break;
                    case 'F':  $s += 13;  break;
                    case 'G':  $s += 15;  break;
                    case 'H':  $s += 17;  break;
                    case 'I':  $s += 19;  break;
                    case 'J':  $s += 21;  break;
                    case 'K':  $s += 2;  break;
                    case 'L':  $s += 4;  break;
                    case 'M':  $s += 18;  break;
                    case 'N':  $s += 20;  break;
                    case 'O':  $s += 11;  break;
                    case 'P':  $s += 3;  break;
                    case 'Q':  $s += 6;  break;
                    case 'R':  $s += 8;  break;
                    case 'S':  $s += 12;  break;
                    case 'T':  $s += 14;  break;
                    case 'U':  $s += 16;  break;
                    case 'V':  $s += 10;  break;
                    case 'W':  $s += 22;  break;
                    case 'X':  $s += 25;  break;
                    case 'Y':  $s += 24;  break;
                    case 'Z':  $s += 23;  break;
                  }
             }
             if( chr($s%26 + ord('A')) != $cf[15] ){
                  $error = true;
                  $messageStack->add('create_account', ENTRY_CF_ERROR);
                }
           }
         }
    }
       //PIVACF end
       


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
                              'customers_password' => tep_encrypt_password($password),
	 						 'fb_user_id' => $fbme['id']);

      if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
      if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw($dob);
	
// BOF Separate Pricing Per Customer
   // if you would like to have an alert in the admin section when either a company name has been entered in
   // the appropriate field or a tax id number, or both then uncomment the next line and comment the default
   // setting: only alert when a tax_id number has been given
   //    if ( (ACCOUNT_COMPANY == 'true' && tep_not_null($company) ) || (ACCOUNT_COMPANY == 'true' && tep_not_null($company_tax_id) ) ) {
	  if ( ACCOUNT_COMPANY == 'true' && tep_not_null($company_tax_id)  ) {
      $sql_data_array['customers_group_ra'] = '1';
// entry_company_tax_id moved from table address_book to table customers in version 4.2.0
      $sql_data_array['entry_company_tax_id'] = $company_tax_id; 
    }
// EOF Separate Pricing Per Customer


 // put everybody in group
 
 if ($country == 204) $sql_data_array['customers_group_id'] = '3';
 if ($country == 132) $sql_data_array['customers_group_id'] = '7';
  if ($country == 14) $sql_data_array['customers_group_id'] = '6';
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
	  
	    //PIVACF start
         if (ACCOUNT_PIVA == 'true') $sql_data_array['entry_piva'] = $piva;
         if (ACCOUNT_CF == 'true') $sql_data_array['entry_cf'] = $cf;
      //PIVACF end

	  
	  
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
	  // BOF Separate Pricing Per Customer
// register SPPC session variables for the new customer
// if there is code above that puts new customers directly into another customer group (default is retail)
// then the below code need not be changed, it uses the newly inserted customer group
      $check_customer_group_info = tep_db_query("select c.customers_group_id, cg.customers_group_show_tax, cg.customers_group_tax_exempt, cg.group_specific_taxes_exempt from " . TABLE_CUSTOMERS . " c left join " . TABLE_CUSTOMERS_GROUPS . " cg using(customers_group_id) where c.customers_id = '" . $customer_id . "'");
      $customer_group_info = tep_db_fetch_array($check_customer_group_info);
      $sppc_customer_group_id = $customer_group_info['customers_group_id'];
      $sppc_customer_group_show_tax = (int)$customer_group_info['customers_group_show_tax'];
      $sppc_customer_group_tax_exempt = (int)$customer_group_info['customers_group_tax_exempt'];
      $sppc_customer_specific_taxes_exempt = '';
      if (tep_not_null($customer_group_info['group_specific_taxes_exempt'])) {
        $sppc_customer_specific_taxes_exempt = $customer_group_info['group_specific_taxes_exempt'];
      }
// EOF Separate Pricing Per Customer


      $customer_first_name = $firstname;
      $customer_default_address_id = $address_id;
      $customer_country_id = $country;
      $customer_zone_id = $zone_id;
      tep_session_register('customer_id');
      tep_session_register('customer_first_name');
      tep_session_register('customer_default_address_id');
      tep_session_register('customer_country_id');
      tep_session_register('customer_zone_id');
// BOF Separate Pricing Per Customer
      tep_session_register('sppc_customer_group_id');
      tep_session_register('sppc_customer_group_show_tax');
      tep_session_register('sppc_customer_group_tax_exempt');
      tep_session_register('sppc_customer_specific_taxes_exempt');
// EOF Separate Pricing Per Customer

// restore cart contents
      $cart->restore_contents();

// build the message content
//---  Beginning of addition: Ultimate HTML Emails  ---//
if (EMAIL_USE_HTML == 'true') {
	require(DIR_WS_MODULES . 'UHtmlEmails/create_account_'. ULTIMATE_HTML_EMAIL_LAYOUT .'.php');
	$email_text = $html_email;
}else{
//---  End of addition: Ultimate HTML Emails  ---//
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
    //---  Beginning of addition: Ultimate HTML Emails  ---//
}

if(ULTIMATE_HTML_EMAIL_DEVELOPMENT_MODE === 'true'){
	//Save the contents of the generated html email to the harddrive in .htm file. This can be practical when developing a new layout.
	$TheFileName = 'Last_mail_from_create_account.php.htm';
	$TheFileHandle = fopen($TheFileName, 'w') or die("can't open error log file");
	fwrite($TheFileHandle, $email_text);
	fclose($TheFileHandle);
}
//---  End of addition: Ultimate HTML Emails  ---//

	
	  // ###### Added CCGV Contribution #########
  if (NEW_SIGNUP_GIFT_VOUCHER_AMOUNT > 0) {
    $coupon_code = create_coupon_code();
    $insert_query = tep_db_query("insert into " . TABLE_COUPONS . " (coupon_code, coupon_type, coupon_amount, date_created) values ('" . $coupon_code . "', 'G', '" . NEW_SIGNUP_GIFT_VOUCHER_AMOUNT . "', now())");
    $insert_id = tep_db_insert_id($insert_query);
    $insert_query = tep_db_query("insert into " . TABLE_COUPON_EMAIL_TRACK . " (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) values ('" . $insert_id ."', '0', 'Admin', '" . $email_address . "', now() )");

    $email_text .= sprintf(EMAIL_GV_INCENTIVE_HEADER, $currencies->format(NEW_SIGNUP_GIFT_VOUCHER_AMOUNT)) . "\n\n" .
                   sprintf(EMAIL_GV_REDEEM, $coupon_code) . "\n\n" .
                   EMAIL_GV_LINK . tep_href_link(FILENAME_GV_REDEEM, 'gv_no=' . $coupon_code,'NONSSL', false) .
                   "\n\n";
  }
  if (NEW_SIGNUP_DISCOUNT_COUPON != '') {
		$coupon_code = NEW_SIGNUP_DISCOUNT_COUPON;
    $coupon_query = tep_db_query("select * from " . TABLE_COUPONS . " where coupon_code = '" . $coupon_code . "'");
    $coupon = tep_db_fetch_array($coupon_query);
		$coupon_id = $coupon['coupon_id'];		
    $coupon_desc_query = tep_db_query("select * from " . TABLE_COUPONS_DESCRIPTION . " where coupon_id = '" . $coupon_id . "' and language_id = '" . (int)$languages_id . "'");
    $coupon_desc = tep_db_fetch_array($coupon_desc_query);
    $insert_query = tep_db_query("insert into " . TABLE_COUPON_EMAIL_TRACK . " (coupon_id, customer_id_sent, sent_firstname, emailed_to, date_sent) values ('" . $coupon_id ."', '0', 'Admin', '" . $email_address . "', now() )");
    $email_text .= EMAIL_COUPON_INCENTIVE_HEADER .  "\n" .
                   sprintf("%s", $coupon_desc['coupon_description']) ."\n\n" .
                   sprintf(EMAIL_COUPON_REDEEM, $coupon['coupon_code']) . "\n\n" .
                   "\n\n";

  }
//    $email_text .= EMAIL_TEXT . EMAIL_CONTACT . EMAIL_WARNING;
// ###### End Added CCGV Contribution #########
	  tep_mail($name, $email_address, EMAIL_SUBJECT, $email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
// BOF Separate Pricing Per Customer: alert shop owner of account created by a company
// if you would like to have an email when either a company name has been entered in
// the appropriate field or a tax id number, or both then uncomment the next line and comment the default
// setting: only email when a tax_id number has been given
//    if ( (ACCOUNT_COMPANY == 'true' && tep_not_null($company) ) || (ACCOUNT_COMPANY == 'true' && tep_not_null($company_tax_id) ) ) {
      if ( ACCOUNT_COMPANY == 'true' && tep_not_null($company_tax_id) ) {
      $alert_email_text = "Please note that " . $firstname . " " . $lastname . " of the company: " . $company . " has created an account.";
      tep_mail(STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS, 'Company account created', $alert_email_text, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
      }
// EOF Separate Pricing Per Customer: alert shop owner of account created by a company
// Create account in vtiger CRM 

 //  require_once(DIR_WS_INCLUDES . 'vtiger/vtwsclib/Vtiger/WSClient.php'); 
   //     $vtiger_ws_client = new Vtiger_WSClient($vtiger_url);
   //     $login = $vtiger_ws_client->doLogin($vtiger_username, $vtiger_accesskey);
  //      if(!$login) {
  //          $lasterr = $vtiger_ws_client->lastError();
  //          die('Please contact an administrator, error:'.$lasterr['message']); 
  //      }
   //     $vt_module = 'Contacts';
   //     $vtiger_query = tep_db_query("select countries_name from countries where countries_id = '" . (int)$country . "'"); 
   //     $vtiger_array = tep_db_fetch_array($vtiger_query); 
   //     $country_name = $vtiger_array["countries_name"]; 
   //     $user_data = array(
   //         'lastname'=> $lastname,
	//		'firstname'=> $firstname,
     //       'cf_CF' => $cf,
    //        'phone'=>$telephone,
    //        'email1' => $email_address,
    //        'fax' => $fax,
    //        'ship_street' => $street_address,
    //        'ship_code' => $postcode,
    //        'ship_city' => $city,
    //        'ship_state' => $state,
    //        'ship_country' => $country_name,
            
     //       'rating'=>'Acquired',
            //'inesistente'=>'bal',
     //   );
     //   $record = $vtiger_ws_client->doCreate($vt_module,$user_data);
    //    $lasterr = $vtiger_ws_client->lastError();
     //   if ($lasterr) {
     //       die('Please contact an administrator, error:'.$lasterr['message']);
     //   }

// End of vtiger integration 


      tep_redirect(tep_href_link(FILENAME_CREATE_ACCOUNT_SUCCESS, '', 'SSL'));
    }
  }

// +Country-State Selector 

 if (!isset($country)) $country = DEFAULT_COUNTRY;
 // -Country-State Selector

 $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_CREATE_ACCOUNT,   '', 'SSL'));
 ?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">

<?php 
require(DIR_WS_INCLUDES . 'form_check.js.php'); 
require(DIR_WS_INCLUDES . 'ajax.js.php'); 
?>
</head>

<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->

<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
 
<!-- body_text //-->
  <?php echo tep_draw_form('create_account', tep_href_link(FILENAME_CREATE_ACCOUNT, '', 'SSL'), 'post', 'onSubmit="return check_form(create_account);"') . tep_draw_hidden_field('action', 'process'); ?>
<div id="indicator"><?php echo tep_image(DIR_WS_IMAGES . 'indicator.gif'); ?></div> 
            <span class="pageHeading"><?php echo HEADING_TITLE; ?></span>
          
 

    

<?php
  if ($messageStack->size('create_account') > 0) {
?>
    
<?php
  }
?><div class="accountBox">
          <div class="accountBoxContents">
      
            <span class="h2boxcont"><b><br><?php echo CATEGORY_PERSONAL; ?></b><br></span>
          
          
<?php
  if (ACCOUNT_GENDER == 'true') {
?>
             
               <div class="maincapture"><?php echo ENTRY_GENDER; ?></div> 
               <div class="maincapture"><?php echo tep_draw_radio_field('gender', 'm') . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . tep_draw_radio_field('gender', 'f') . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . (tep_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">' . ENTRY_GENDER_TEXT . '</span>': ''); ?></div>
             </div>
            </div>
              
<?php
  }
?>
              <div class="accountBoxContents">
               <div class="maincapture"><?php echo ENTRY_FIRST_NAME; ?></div>
             <div class="maincapture">   <?php  echo tep_draw_input_field('firstname', $fbme['first_name']) . '&nbsp;' . (tep_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>': ''); ?></div> 
              		 <div class="maincapture"><?php echo ENTRY_LAST_NAME; ?></div>
              		 <div class="maincapture"><span class="input2"><?php echo tep_draw_input_field('lastname', $fbme['last_name']) . '&nbsp;' . (tep_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</span></span>': ''); ?></div>
             </div>
          
<?php
  if (ACCOUNT_DOB == 'true') {
?>
              <div class="accountBoxContents">
               <div class="maincapture"><?php echo ENTRY_DATE_OF_BIRTH; ?></div>
                <div class="maincapture">
                <?php echo tep_draw_pull_down_date('dob_in', '', '', (isset($_POST['dob_inY'])? $_POST['dob_inY'] : 1980), false, true, 1900); 
                       		         echo '&nbsp;' . (tep_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '<span class="inputRequirement">' . ENTRY_DATE_OF_BIRTH_TEXT . '</span>': '');?>
                
              </div> 			  <!--PIVACF start-->
<?php  if (ACCOUNT_CF == 'true') {?>                                                
                     
               <div class="maincapture"><?php echo ENTRY_CF; ?></div> 
               
            <div class="maincapture"><?php echo tep_draw_input_field('cf') . '&nbsp;' . ((tep_not_null(ENTRY_CF_TEXT) && (ACCOUNT_CF_REQ == 'true'))? '<span class="inputRequirement">' . ENTRY_CF_TEXT . '</span>': ''); ?></div>
         <?php  }?>
<!--PIVACF end-->    
              </div>
<?php
  }
?>
          
        <span class="h2boxcont"><b><br><?php echo CATEGORY_CONTACT; ?></b><br></span>
      <div class="accountBox">
          <div class="accountBoxContents">
            
               <div class="maincapture"><?php echo ENTRY_TELEPHONE_NUMBER; ?></div>
               	<div class="maincapture"><?php echo tep_draw_input_field('telephone') . '&nbsp;' . (tep_not_null(ENTRY_TELEPHONE_NUMBER_TEXT) ? '<span class="inputRequirement">' . ENTRY_TELEPHONE_NUMBER_TEXT . '</span>': ''); ?></div>
               	<div class="maincapture"> <?php echo ENTRY_EMAIL_ADDRESS; ?></div>
               <div class="maincapture"><?php echo tep_draw_input_field('email_address', $fbme['email']) . '&nbsp;' . (tep_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_EMAIL_ADDRESS_TEXT . '</span>': ''); ?></div>
              
              </div>


			
<?php
  if (ACCOUNT_COMPANY == 'true') {
?>
      
        <span class="h2boxcont"><b><?php echo CATEGORY_PASSWORD; ?></b><br></span>
      <div class="accountBox">
          <div class="accountBoxContents">
          
               <div class="maincapture"><?php echo ENTRY_PASSWORD; ?></div>
               	<div class="maincapture"><?php echo tep_draw_password_field('password') . '&nbsp;' . (tep_not_null(ENTRY_PASSWORD_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_TEXT . '</span>': ''); ?></div>
              
               	<div class="maincapture"><?php echo ENTRY_PASSWORD_CONFIRMATION; ?></div>
                <div class="maincapture"><?php echo tep_draw_password_field('confirmation') . '&nbsp;' . (tep_not_null(ENTRY_PASSWORD_CONFIRMATION_TEXT) ? '<span class="inputRequirement">' . ENTRY_PASSWORD_CONFIRMATION_TEXT . '</span>': ''); ?></div>
           
             
        <div class="h2boxcont"><b><?php echo CATEGORY_COMPANY; ?></b><br></div>
  <div class="accountBox">
          <div class="accountBoxContents">
            
               <div class="maincapture"><?php echo ENTRY_COMPANY; ?></div> 	<!--PIVACF start-->
 <div class="maincapture"><?php echo tep_draw_input_field('company') . '&nbsp;' . (tep_not_null(ENTRY_COMPANY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TEXT . '</span>': ''); ?></div>
        
<?php  if (ACCOUNT_PIVA == 'true') { ?>
                       
               <div class="maincapture"><?php echo ENTRY_PIVA; ?> </div>
                    <div class="maincapture"><?php echo tep_draw_input_field('piva') . '&nbsp;' . ((tep_not_null(ENTRY_PIVA_TEXT) && (ACCOUNT_PIVA_REQ == 'true')) ? '<span class="inputRequirement">' . ENTRY_PIVA_TEXT . '</span>': ''); ?></div>
             
       

 </div>
             
<?php  }?>
<!--PIVACF end-->

			
			
<?php
  }
?>
    
        <span class="h2boxcont"><b><?php echo CATEGORY_ADDRESS; ?></b><br></span>
      <div class="accountBox">
          <div class="accountBoxContents">
           
               <div class="maincapture"><?php echo ENTRY_STREET_ADDRESS; ?></div>
               <div class="maincapture"><?php echo tep_draw_input_field('street_address') . '&nbsp;' . (tep_not_null(ENTRY_STREET_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_STREET_ADDRESS_TEXT . '</span>': ''); ?></div>
           <div class="maincapture"><?php echo ENTRY_POST_CODE_TEXT; ?></div>
             <div class="maincapture"><?php echo tep_draw_input_field('postcode') . '&nbsp;' . (tep_not_null(ENTRY_POST_CODE_TEXT) ? '<span class="inputRequirement">' . ENTRY_POST_CODE_TEXT . '</span>': ''); ?></div>
             
              </div>
<?php
  if (ACCOUNT_SUBURB == 'true') {
?>
            
               <div class="maincapture"><?php echo ENTRY_SUBURB; ?></div>
             <div class="maincapture"><?php echo tep_draw_input_field('suburb') . '&nbsp;' . (tep_not_null(ENTRY_SUBURB_TEXT) ? '<span class="inputRequirement">' . ENTRY_SUBURB_TEXT . '</span>': ''); ?></div>
              </div>
<?php
  }
?>
                              
              
               <div class="maincapture"><?php echo ENTRY_CITY; ?></div>
                <div class="maincapture"><?php echo tep_draw_input_field('city') . '&nbsp;' . (tep_not_null(ENTRY_CITY_TEXT) ? '<span class="inputRequirement">' . ENTRY_CITY_TEXT . '</span>': ''); ?></div>
           <div class="maincapture"><?php echo ENTRY_COUNTRY; ?></div>
            <div class="maincapture"><?php echo tep_get_country_list('country',$country,'onChange="getStates(this.value);"') . '&nbsp;' . (tep_not_null(ENTRY_COUNTRY_TEXT) ? '<span class="inputRequirement">' . ENTRY_COUNTRY_TEXT . '</span>': ''); ?>
                     </div>
         
              </div>
<?php
  if (ACCOUNT_STATE == 'true') {
?>
             
                
               <div class="maincapture"><?php echo ENTRY_STATE; ?></div>
              <div class="maincapture">
<div id="states">
                          <?php
				// +Country-State Selector
				echo ajax_get_zones_html($country,false);
				// -Country-State Selector
				?>
                        </div>
                </div>
              
<?php
  }
?>
              
        <span class="h2boxcont"><b><?php echo CATEGORY_OPTIONS; ?></b><br></span>
     <div class="accountBox">
          <div class="accountBoxContents">
            
              
               <div class="maincapture"><?php echo ENTRY_NEWSLETTER; ?></div>
               <div class="maincapture"><?php echo tep_draw_checkbox_field('newsletter', '1') . '&nbsp;' . (tep_not_null(ENTRY_NEWSLETTER_TEXT) ? '<span class="inputRequirement">' . ENTRY_NEWSLETTER_TEXT . '</span>': ''); ?></div>
              <div align="right"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></div>
   </div>
   </div>
   <?php
// +Country-State Selector 
}
// -Country-State Selector 
?>
    </form>
<!-- body_text_eof //-->
    
<!-- right_navigation //-->
<?php include(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
   
<!-- body_eof //-->

<!-- footer //-->
<?php include(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->

</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>

