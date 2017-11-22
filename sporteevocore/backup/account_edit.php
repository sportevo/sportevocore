<?php
/*
  $Id: account_edit.php,v 1.65 2003/06/09 23:03:52 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  if (!tep_session_is_registered('customer_id')) {
    $navigation->set_snapshot();
    tep_redirect(tep_href_link(FILENAME_LOGIN, '', 'SSL'));
  }

// needs to be included earlier to set the success message in the messageStack
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_ACCOUNT_EDIT);

  if (isset($_POST['action']) && ($_POST['action'] == 'process')) {
    if (ACCOUNT_GENDER == 'true') $gender = tep_db_prepare_input($_POST['gender']);
    $firstname = tep_db_prepare_input($_POST['firstname']);
    $lastname = tep_db_prepare_input($_POST['lastname']);
    if (ACCOUNT_DOB == 'true') $dob = tep_db_prepare_input($_POST['dob']);
    $email_address = tep_db_prepare_input($_POST['email_address']);
    $telephone = tep_db_prepare_input($_POST['telephone']);
    $fax = tep_db_prepare_input($_POST['fax']);

       //PIVACF start
       if (ACCOUNT_CF == 'true') $cf = tep_db_prepare_input($_POST['cf']);
    //PIVACF end

   
    $error = false;

    if (ACCOUNT_GENDER == 'true') {
      if ( ($gender != 'm') && ($gender != 'f') ) {
        $error = true;

        $messageStack->add('account_edit', ENTRY_GENDER_ERROR);
      }
    }

 //PIVACF start
       if (ACCOUNT_CF == 'true') {
         if (($cf == "") && (ACCOUNT_CF_REQ == 'true')) {
           $error = true;
              $messageStack->add('account_edit', ENTRY_CF_ERROR);
         } else if ((strlen($cf) != 16) && ($cf != "")) {
           $error = true;
              $messageStack->add('account_edit', ENTRY_CF_ERROR);
         } else if (strlen($cf) == 16) {
              $cf = strtoupper($cf);
              if( ! ereg("^[A-Z0-9]+$", $cf) ){
                $error = true;
                $messageStack->add('account_edit', ENTRY_CF_ERROR);
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
                  $messageStack->add('account_edit', ENTRY_CF_ERROR);
                }
           }
         }
    }
       //PIVACF end


    if (strlen($firstname) < ENTRY_FIRST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_FIRST_NAME_ERROR);
    }

    if (strlen($lastname) < ENTRY_LAST_NAME_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_LAST_NAME_ERROR);
    }

    if (ACCOUNT_DOB == 'true') {
      if (!checkdate(substr(tep_date_raw($dob), 4, 2), substr(tep_date_raw($dob), 6, 2), substr(tep_date_raw($dob), 0, 4))) {
        $error = true;

        $messageStack->add('account_edit', ENTRY_DATE_OF_BIRTH_ERROR);
      }
    }

    if (strlen($email_address) < ENTRY_EMAIL_ADDRESS_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_ERROR);
    }

    if (!tep_validate_email($email_address)) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_CHECK_ERROR);
    }

    $check_email_query = tep_db_query("select count(*) as total from " . TABLE_CUSTOMERS . " where customers_email_address = '" . tep_db_input($email_address) . "' and customers_id != '" . (int)$customer_id . "'");
    $check_email = tep_db_fetch_array($check_email_query);
    if ($check_email['total'] > 0) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_EMAIL_ADDRESS_ERROR_EXISTS);
    }

    if (strlen($telephone) < ENTRY_TELEPHONE_MIN_LENGTH) {
      $error = true;

      $messageStack->add('account_edit', ENTRY_TELEPHONE_NUMBER_ERROR);
    }

    if ($error == false) {
	// BOF Separate Pricing Per Customer
// don't rely on the input field for company_tax_id being there and filled in, the customer might have edited
// his local copy of the page to include that, so we will check for an entry ourselves
    $check_entry_company_tax_id_query = tep_db_query("select entry_company_tax_id from " . TABLE_CUSTOMERS . " where customers_id = '" . (int)$customer_id . "'");
    $check_entry_company_tax_id = tep_db_fetch_array($check_entry_company_tax_id_query);
// EOF Separate Pricing Per Customer


      $sql_data_array = array('customers_firstname' => $firstname,
                              'customers_lastname' => $lastname,
                              'customers_email_address' => $email_address,
                              'customers_telephone' => $telephone,
                              'customers_fax' => $fax);

      if (ACCOUNT_GENDER == 'true') $sql_data_array['customers_gender'] = $gender;
      if (ACCOUNT_DOB == 'true') $sql_data_array['customers_dob'] = tep_date_raw($dob);
// BOF Separate Pricing Per Customer
    if (ACCOUNT_COMPANY == 'true') {
      if (isset($_POST['company_tax_id']) && tep_not_null($_POST['company_tax_id']) && !tep_not_null($check_entry_company_tax_id['entry_company_tax_id'])) {
        $sql_data_array['entry_company_tax_id'] = tep_db_prepare_input($_POST['company_tax_id']);
      }
    }
// EOF Separate Pricing Per Customer

      tep_db_perform(TABLE_CUSTOMERS, $sql_data_array, 'update', "customers_id = '" . (int)$customer_id . "'");

      tep_db_query("update " . TABLE_CUSTOMERS_INFO . " set customers_info_date_account_last_modified = now() where customers_info_id = '" . (int)$customer_id . "'");

      $sql_data_array = array('entry_firstname' => $firstname,
                              'entry_lastname' => $lastname);

         //PIVACF start
         if (ACCOUNT_CF == 'true') $sql_data_array['entry_cf'] = $cf;
         $sql_data_array['entry_gender'] = $gender;
      //PIVACF end


      tep_db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "customers_id = '" . (int)$customer_id . "' and address_book_id = '" . (int)$customer_default_address_id . "'");

// reset the session variables
      $customer_first_name = $firstname;

      $messageStack->add_session('account', SUCCESS_ACCOUNT_UPDATED, 'success');

      tep_redirect(tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
    }
  }

  //PIVACF start
  $account_query = tep_db_query("select c.customers_gender, c.customers_firstname, c.customers_lastname, c.customers_dob, c.customers_email_address, c.customers_telephone, c.customers_fax , a.entry_cf, entry_company_tax_id from " . TABLE_CUSTOMERS . " c left join " . TABLE_ADDRESS_BOOK . " a on c.customers_default_address_id = a.address_book_id where a.customers_id = c.customers_id and c.customers_id = '" . (int)$customer_id . "'");
  //PIVACF end
  


 
  $account = tep_db_fetch_array($account_query);

  $breadcrumb->add(NAVBAR_TITLE_1, tep_href_link(FILENAME_ACCOUNT, '', 'SSL'));
  $breadcrumb->add(NAVBAR_TITLE_2, tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
<?php require('includes/form_check.js.php'); ?>
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
    <td width="100%" valign="top"><?php echo tep_draw_form('account_edit', tep_href_link(FILENAME_ACCOUNT_EDIT, '', 'SSL'), 'post', 'onSubmit="return check_form(account_edit);"') . tep_draw_hidden_field('action', 'process'); ?><table border="0" width="100%" cellspacing="0" cellpadding="0">
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
<?php
  if ($messageStack->size('account_edit') > 0) {
?>
      <tr>
        <td><?php echo $messageStack->output('account_edit'); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
  }
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
          <tr>
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="main"><b><?php echo MY_ACCOUNT_TITLE; ?></b></td>
                <td class="inputRequirement" align="right"><?php echo FORM_REQUIRED_INFORMATION; ?></td>
              </tr>
            </table></td>
          </tr>
          <tr>
            <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
              <tr class="infoBoxContents">
                <td><table border="0" cellspacing="2" cellpadding="2">
<?php
  if (ACCOUNT_GENDER == 'true') {
    if (isset($gender)) {
      $male = ($gender == 'm') ? true : false;
    } else {
      $male = ($account['customers_gender'] == 'm') ? true : false;
    }
    $female = !$male;
?>
                  <tr>
                    <td class="main"><?php echo ENTRY_GENDER; ?></td>
                    <td class="main"><?php echo tep_draw_radio_field('gender', 'm', $male) . '&nbsp;&nbsp;' . MALE . '&nbsp;&nbsp;' . tep_draw_radio_field('gender', 'f', $female) . '&nbsp;&nbsp;' . FEMALE . '&nbsp;' . (tep_not_null(ENTRY_GENDER_TEXT) ? '<span class="inputRequirement">' . ENTRY_GENDER_TEXT . '</span>': ''); ?></td>
                  </tr>
<?php
  }
?>
                  <tr>
                    <td class="main"><?php echo ENTRY_FIRST_NAME; ?></td>
                    <td class="main"><?php echo tep_draw_input_field('firstname', $account['customers_firstname']) . '&nbsp;' . (tep_not_null(ENTRY_FIRST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_FIRST_NAME_TEXT . '</span>': ''); ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo ENTRY_LAST_NAME; ?></td>
                    <td class="main"><?php echo tep_draw_input_field('lastname', $account['customers_lastname']) . '&nbsp;' . (tep_not_null(ENTRY_LAST_NAME_TEXT) ? '<span class="inputRequirement">' . ENTRY_LAST_NAME_TEXT . '</span>': ''); ?></td>
                  </tr>
	
	
<!--PIVACF start-->
<?php
  if (ACCOUNT_CF == 'true') {
?>
          <tr>
            <td class="main"><?php echo ENTRY_CF; ?></td>
            <td class="main"><?php echo tep_draw_input_field('cf', $account['entry_cf']) . '&nbsp;' . ((tep_not_null(ENTRY_CF_TEXT) && (ACCOUNT_CF_REQ=='true')) ? '<span class="inputRequirement">' . ENTRY_CF_TEXT . '</span>': ''); ?></td>
          </tr>
<?php
  }
?>
<!--PIVACF end-->
			 
				 
				 
<?php
  if (ACCOUNT_DOB == 'true') {
?>
                  <tr>
                    <td class="main"><?php echo ENTRY_DATE_OF_BIRTH; ?></td>
                    <td class="main"><?php echo tep_draw_input_field('dob', tep_date_short($account['customers_dob'])) . '&nbsp;' . (tep_not_null(ENTRY_DATE_OF_BIRTH_TEXT) ? '<span class="inputRequirement">' . ENTRY_DATE_OF_BIRTH_TEXT . '</span>': ''); ?></td>
                  </tr>
<?php
  }
?>
                  <tr>
                    <td class="main"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
                    <td class="main"><?php echo tep_draw_input_field('email_address', $account['customers_email_address']) . '&nbsp;' . (tep_not_null(ENTRY_EMAIL_ADDRESS_TEXT) ? '<span class="inputRequirement">' . ENTRY_EMAIL_ADDRESS_TEXT . '</span>': ''); ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo ENTRY_TELEPHONE_NUMBER; ?></td>
                    <td class="main"><?php echo tep_draw_input_field('telephone', $account['customers_telephone']) . '&nbsp;' . (tep_not_null(ENTRY_TELEPHONE_NUMBER_TEXT) ? '<span class="inputRequirement">' . ENTRY_TELEPHONE_NUMBER_TEXT . '</span>': ''); ?></td>
                  </tr>
                  <tr>
                    <td class="main"><?php echo ENTRY_FAX_NUMBER; ?></td>
                    <td class="main"><?php echo tep_draw_input_field('fax', $account['customers_fax']) . '&nbsp;' . (tep_not_null(ENTRY_FAX_NUMBER_TEXT) ? '<span class="inputRequirement">' . ENTRY_FAX_NUMBER_TEXT . '</span>': ''); ?></td>
                  </tr>
                  <?php
// BOF Separate Pricing Per Customer
   if (ACCOUNT_COMPANY == 'true') { 
?>
                  <tr>
                    <td class="main"><?php echo ENTRY_COMPANY_TAX_ID; ?></td>
                    <td class="main"><?php if (!tep_not_null($account['entry_company_tax_id'])) { echo tep_draw_input_field('company_tax_id', $account['entry_company_tax_id']) . '&nbsp;' . (tep_not_null(ENTRY_COMPANY_TAX_ID_TEXT) ? '<span class="inputRequirement">' . ENTRY_COMPANY_TAX_ID_TEXT . '</span>': '');
                    } else {
                    echo $account['entry_company_tax_id'];
                    }  ?></td>
                  </tr>
<?php 
} // end if (ACCOUNT_COMPANY == 'true')
// EOF Separate Pricing Per Customer
?>

                </table></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                <td><?php echo '<a href="' . tep_href_link(FILENAME_ACCOUNT, '', 'SSL') . '">' . tep_image_button('button_back.gif', IMAGE_BUTTON_BACK) . '</a>'; ?></td>
                <td align="right"><?php echo tep_image_submit('button_continue.gif', IMAGE_BUTTON_CONTINUE); ?></td>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
    </table></form></td>
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
