<?php
/*
  $Id: server_info.php,v 1.6 2003/06/30 13:13:49 dgw_ Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  /********************** BEGIN VERSION CHECKER *********************/
  if (file_exists(DIR_WS_FUNCTIONS . 'version_checker.php'))
  {
     require(DIR_WS_LANGUAGES . $language . '/version_checker.php');
     require(DIR_WS_FUNCTIONS . 'version_checker.php');
     $contribPath = 'http://addons.Francesco Rossi.com/info/4513';
     $currentVersion = 'GoogleBase V 2.8';
     $contribName = 'GoogleBase V';
     $versionStatus = '';
  }
  /********************** END VERSION CHECKER *********************/

  $checkingVersion = false;
  $action = (isset($_POST['action']) ? $_POST['action'] : '');

  if (tep_not_null($action))
  {
     /********************** CHECK THE VERSION ***********************/
     if ($action == 'getversion')
     {
         $checkingVersion = true;
         if (isset($_POST['version_check']) && $_POST['version_check'] == 'on')
             $versionStatus = AnnounceVersion($contribPath, $currentVersion, $contribName);
     }
  }

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
</head>
<body>
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
        <td><table border="0" width="95%" cellspacing="0" cellpadding="0">
            <tr>
               <td class="pageHeading" valign="top" style="white-space:nowrap;"><?php echo $currentVersion; ?></td>
            </tr>
            <tr>
               <td class="smallText" valign="top"><?php echo HEADING_TITLE_SUPPORT_THREAD; ?></td>
            </tr>
        </table></td>
        <td><table border="0" width="100%">
         <tr>
          <td class="smallText" align="right"><?php echo HEADING_TITLE_AUTHOR; ?></td>
         </tr>
         <?php
         if (function_exists('AnnounceVersion')) {
            if (false) { //requires database change so skip
         ?>
               <tr>
                  <td class="smallText" align="right" style="font-weight: bold; color: red;"><?php echo AnnounceVersion($contribPath, $currentVersion, $contribName); ?></td>
               </tr>
         <?php } else if (tep_not_null($versionStatus)) {
           echo '<tr><td class="smallText" align="right" style="font-weight: bold; color: red;">' . $versionStatus . '</td></tr>';
         } else {
           echo tep_draw_form('version_check', 'feeders.php', '', 'post') . tep_draw_hidden_field('action', 'getversion');
         ?>
               <tr>
                  <td class="smallText" align="right" style="font-weight: bold; color: red;"><INPUT TYPE="radio" NAME="version_check" onClick="this.form.submit();"><?php echo TEXT_VERSION_CHECK_UPDATES; ?></td>
               </tr>
           </form>
         <?php } } else { ?>
            <tr>
               <td class="smallText" align="right" style="font-weight: bold; color: red;"><?php echo TEXT_MISSING_VERSION_CHECKER; ?></td>
            </tr>
         <?php } ?>
        </table></td>
       </tr>

       <tr><td height="20"></td></tr>
       <tr>
        <td class="smallText"><?php echo '<a href="' . tep_href_link('bingfeeder.php?noftp=1') . '" target=_blank">' . TEXT_FEEDERS_BING_NOFTP . '</a>'; ?></td>
       </tr>
       <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
       </tr>
       <tr>
        <td class="smallText"><?php echo '<a href="' . tep_href_link('bingfeeder.php') . '" target=_blank">' . TEXT_FEEDERS_BING . '</a>'; ?></td>
       </tr>

       <tr><td height="10"></td></tr>
       <tr><td><?php echo tep_black_line(); ?></td></tr>
       <tr><td height="10"></td></tr>
       <tr>
        <td class="smallText"><?php echo '<a href="' . tep_href_link('googlefeeder.php?noftp=1') . '" target=_blank">' . TEXT_FEEDERS_GOOGLE_NOFTP . '</a>'; ?></td>
       </tr>
       <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
       </tr>
       <tr>
        <td class="smallText"><?php echo '<a href="' . tep_href_link('googlefeeder.php') . '" target=_blank">' . TEXT_FEEDERS_GOOGLE . '</a>'; ?></td>
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
