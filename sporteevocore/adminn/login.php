<?php
/*
  $Id: login.php,v 1.17 2003/02/14 12:57:29 dgw_ Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/2 Francesco Rossi

  Released under the GNU General Public License

  Includes Contribution:
  Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2)

  This file may be deleted if disabling the above contribution
*/

  require('includes/application_top.php');

  if (isset($_GET['action']) && ($_GET['action'] == 'process')) {
    $log = tep_db_prepare_input($_POST['log']);
    $pwd = tep_db_prepare_input($_POST['pwd']);

// Check if email exists
    $check_admin_query = tep_db_query("select admin_id as login_id, admin_groups_id as login_groups_id, admin_firstname as login_firstname, admin_email_address as login_email_address, admin_password as login_password, admin_modified as login_modified, admin_logdate as login_logdate, admin_lognum as login_lognum from " . TABLE_ADMIN . " where admin_email_address = '" . tep_db_input($log) . "'");
    if (!tep_db_num_rows($check_admin_query)) {
      $_GET['login'] = 'fail';
    } else {
      $check_admin = tep_db_fetch_array($check_admin_query);
      // Check that password is good
      if (!tep_validate_password($pwd, $check_admin['login_password'])) {
        $_GET['login'] = 'fail';
      } else {
        if (tep_session_is_registered('password_forgotten')) {
          tep_session_unregister('password_forgotten');
        }

        $login_id = $check_admin['login_id'];
        $login_groups_id = $check_admin[login_groups_id];
        $login_firstname = $check_admin['login_firstname'];
        $login_email_address = $check_admin['login_email_address'];
        $login_logdate = $check_admin['login_logdate'];
        $login_lognum = $check_admin['login_lognum'];
        $login_modified = $check_admin['login_modified'];

        tep_session_register('login_id');
        tep_session_register('login_groups_id');
        tep_session_register('login_first_name');

        //$date_now = date('Ymd');
        tep_db_query("update " . TABLE_ADMIN . " set admin_logdate = now(), admin_lognum = admin_lognum+1 where admin_id = '" . $login_id . "'");

        if (($login_lognum == 0) || !($login_logdate) || ($login_email_address == 'admin@localhost') || ($login_modified == '0000-00-00 00:00:00')) {
          tep_redirect(tep_href_link(FILENAME_ADMIN_ACCOUNT));
        } else {
          tep_redirect(tep_href_link(FILENAME_DEFAULT));
        }

      }
    }
  }

  @include(DIR_WS_LANGUAGES . $language . '/' . FILENAME_LOGIN);
  
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="style.css">
<script src="javascript/prototype.js" type="text/javascript"></script>
<script src="javascript/scriptaculous.js" type="text/javascript"></script>
<script src="javascript/effects.js" type="text/javascript"></script>
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600' rel='stylesheet' type='text/css'>
</head>
<body>
<div id="headerlogin"></div>
<div id="mainlandlogin">
  <div id="centerlandogin"> 
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr> 
        <td align="center" height="300" colspan="3" class="main">
        <table width="320" border="0" align="center" cellpadding="1"  cellspacing="0" valign="middle">
            <tr class="mainback">
              <td><table border="0" align="center" cellpadding="0"  cellspacing="0">
                <tr class="logo-head" height="50">
                  <td align="left">  <div id="opaque">
  
                    <table width="280" border="0" align="center" cellpadding="0" cellspacing="0">
                      <tr>
                        <td height="20" align="center" valign="top" class="login_heading"><?php echo tep_draw_form('login', FILENAME_LOGIN, 'action=process'); ?></td>
                      </tr>
                      <tr>
                        <td class="login_heading" valign="top" align="center">&nbsp;<?php echo HEADING_RETURNING_ADMIN; ?></td>
                      </tr>
                      <tr>
                        <td height="100%" valign="top" align="center"><table width="100%" height="100%" cellspacing="3" cellpadding="2" class="login_form">
                          <?php
																  if ($_GET['login'] == 'fail') {
																    $info_message = TEXT_LOGIN_ERROR;
																  }
                                  if (isset($info_message)) {
																?>
                          <tr>
                            <td colspan="2" class="smallText" align="center"><?php echo $info_message; ?></td>
                          </tr>
                          <?php
  } else {
?>
                          <tr>
                            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
                          </tr>
                          <?php
  }
?>
                          <tr>
                            <td class="login"><?php echo ENTRY_EMAIL_ADDRESS; ?></td>
                            <td class="login"><?php echo tep_draw_input_field('log'); ?></td>
                          </tr>
                          <tr>
                            <td class="login"><?php echo ENTRY_PASSWORD; ?></td>
                            <td class="login"><?php echo tep_draw_password_field('pwd'); ?></td>
                          </tr>
                          <tr>
                            <td colspan="2" align="center" valign="top"><?php echo tep_image_submit('button_confirm.gif', IMAGE_BUTTON_LOGIN); ?></td>
                          </tr>
                        </table></td>
                      </tr>
                      <tr>
                        <td height="20" align="left" valign="top"><?php echo '<a class="sub" href="' . tep_href_link(FILENAME_PASSWORD_FORGOTTEN, '', 'SSL') . '">' . TEXT_PASSWORD_FORGOTTEN . '</a><span class="sub">&nbsp;</span>'; ?></td>
                      </tr>
                      <tr>
                        <td height="20" align="left" valign="top">&nbsp;</td>
                      </tr>
                    </table>
                    </form>
                            </div></td>
                </tr>
              </table></td>
            </tr>
          </table>
        
        </td>
      </tr>
      <tr>
        <td height="20" colspan="3" >&nbsp;</td>
      </tr>
    </table>
  </div>
  <div id="downland"> 
    
 <div id="logo"></div>
   
</div>

</body>
<script type="text/javascript">
new Effect.Opacity('opaque', { from: 0.85, to: 0.85});

</script>
</html>