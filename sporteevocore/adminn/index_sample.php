<?php
/*
  $Id: index.php,v 1.19 2003/06/27 09:38:31 dgw_ Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/

  require('includes/application_top.php');

  $cat = array(
//my account --------------------
       	       array('title' => BOX_HEADING_MY_ACCOUNT,
                     'access' => 'true',
                     'image' => 'my_account.gif',
                     'href' => tep_href_link(FILENAME_ADMIN_ACCOUNT),
                     'children' => array(array('title' => HEADER_TITLE_ACCOUNT, 'link' => tep_href_link(FILENAME_ADMIN_ACCOUNT), 
						'access' => 'true'), 
						array('title' => HEADER_TITLE_LOGOFF, 'link' => tep_href_link(FILENAME_LOGOFF),
                                               'access' => 'true'))),
                                               
// my account end
// administrator -----------------
               array('title' => BOX_HEADING_ADMINISTRATOR,
                     'access' => tep_admin_check_boxes('administrator.php'),
                     'image' => 'administrator.gif',
                     'href' => tep_href_link(tep_selected_file('administrator.php'), 'selected_box=administrator'),
                     'children' => array(array('title' => BOX_ADMINISTRATOR_MEMBER, 'link' => tep_href_link(FILENAME_ADMIN_MEMBERS, 'selected_box=administrator'),
                                               'access' => tep_admin_check_boxes(FILENAME_ADMIN_MEMBERS, 'sub_boxes')),
                                         array('title' => BOX_ADMINISTRATOR_BOXES, 'link' => tep_href_link(FILENAME_ADMIN_FILES, 'selected_box=administrator'),
                                               'access' => tep_admin_check_boxes(FILENAME_ADMIN_FILES, 'sub_boxes')))),
// administrator end ------------------------

//configuration-------------------------------------------- 
                    array('title' => BOX_HEADING_CONFIGURATION,

                     'access' => tep_admin_check_boxes('configuration.php'),

                     'image' => 'configuration.gif',
                     'href' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration&gID=1'),
                     'children' => array(array('title' => BOX_CONFIGURATION_MYSTORE, 'link' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration&gID=1')),
                                         array('title' => BOX_CONFIGURATION_LOGGING, 'link' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration&gID=10')),
                                         array('title' => BOX_CONFIGURATION_CACHE, 'link' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration&gID=11')))),
//configuration end ----------------------------------------------  
             
//Modules --------------------              
               array('title' => BOX_HEADING_MODULES,

                     'access' => tep_admin_check_boxes('modules.php'),

                     'image' => 'modules.gif',
                     'href' => tep_href_link(FILENAME_MODULES, 'selected_box=modules&set=payment'),
                     'children' => array(array('title' => BOX_MODULES_PAYMENT, 'link' => tep_href_link(FILENAME_MODULES, 'selected_box=modules&set=payment')),
                                         array('title' => BOX_MODULES_SHIPPING, 'link' => tep_href_link(FILENAME_MODULES, 'selected_box=modules&set=shipping')))),

//modules end ------------------------

//catalog ----------------------------
               array('title' => BOX_HEADING_CATALOG,

                     'access' => tep_admin_check_boxes('catalog.php'),

                     'image' => 'catalog.gif',
                     'href' => tep_href_link(FILENAME_CATEGORIES, 'selected_box=catalog'),
                     'children' => array(array('title' => CATALOG_CONTENTS, 'link' => tep_href_link(FILENAME_CATEGORIES, 'selected_box=catalog')),
                                         array('title' => 'Easypopulate', 'link' => tep_href_link('easypopulate.php', 'selected_box=catalog')))),

//Catalog end ----------------------------

               array('title' => BOX_HEADING_LOCATION_AND_TAXES,
//Admin begin
                     'access' => tep_admin_check_boxes('taxes.php'),
//Admin end
                     'image' => 'location.gif',
                     'href' => tep_href_link(FILENAME_COUNTRIES, 'selected_box=taxes'),
                     'children' => array(array('title' => BOX_TAXES_COUNTRIES, 'link' => tep_href_link(FILENAME_COUNTRIES, 'selected_box=taxes')),
                                         array('title' => BOX_TAXES_GEO_ZONES, 'link' => tep_href_link(FILENAME_GEO_ZONES, 'selected_box=taxes')))),
               array('title' => BOX_HEADING_CUSTOMERS,
//Admin begin
                     'access' => tep_admin_check_boxes('customers.php'),
//Admin end
                     'image' => 'customers.gif',
                     'href' => tep_href_link(FILENAME_CUSTOMERS, 'selected_box=customers'),
                     'children' => array(array('title' => BOX_CUSTOMERS_CUSTOMERS, 'link' => tep_href_link(FILENAME_CUSTOMERS, 'selected_box=customers')),
                                         array('title' => BOX_CUSTOMERS_ORDERS, 'link' => tep_href_link(FILENAME_ORDERS, 'selected_box=customers')))),
               array('title' => BOX_HEADING_LOCALIZATION,
//Admin begin
                     'access' => tep_admin_check_boxes('localization.php'),
//Admin end
                     'image' => 'localization.gif',
                     'href' => tep_href_link(FILENAME_CURRENCIES, 'selected_box=localization'),
                     'children' => array(array('title' => BOX_LOCALIZATION_CURRENCIES, 'link' => tep_href_link(FILENAME_CURRENCIES, 'selected_box=localization')),
                                         array('title' => BOX_LOCALIZATION_LANGUAGES, 'link' => tep_href_link(FILENAME_LANGUAGES, 'selected_box=localization')))),

//Affiliates ------------------------
						array('title' => BOX_HEADING_AFFILIATE,
                     'image' => 'affiliate.gif',
                     'href' => tep_href_link(FILENAME_AFFILIATE_SUMMARY, 'selected_box=affiliate'),
                     'children' => array(array('title' => BOX_AFFILIATE, 'link' => tep_href_link(FILENAME_AFFILIATE, 'selected_box=affiliate')),
                                         array('title' => BOX_AFFILIATE_BANNERS, 'link' => tep_href_link(FILENAME_AFFILIATE_BANNERS, 'selected_box=affiliate')))),
// Affiliates --------------------
//reports ---------------
               array('title' => BOX_HEADING_REPORTS,

                     'access' => tep_admin_check_boxes('reports.php'),
                     'image' => 'reports.gif',
                     'href' => tep_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, 'selected_box=reports'),
                     'children' => array(array('title' => REPORTS_PRODUCTS, 'link' => tep_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, 'selected_box=reports')),
                                         array('title' => REPORTS_ORDERS, 'link' => tep_href_link(FILENAME_STATS_CUSTOMERS, 'selected_box=reports')))),
//Reports end ------------------

// Support ---------------------
               array('title' => BOX_SUPPORT_HEADING,
                     'image' => 'helpdesk.gif',
                     'href' => tep_href_link(FILENAME_SUPPORT_TICKETS, 'selected_box=support'),
                     'children' => array(array('title' => 'Support Tickets', 'link' => tep_href_link('support.php', 'selected_box=support')),
                                         array('title' => 'FAQ', 'link' => tep_href_link('faq.php', 'selected_box=support')))),
//end of support ------------------

//Tools -------------------------------
               array('title' => BOX_HEADING_TOOLS,

                     'access' => tep_admin_check_boxes('tools.php'),

                     'image' => 'tools.gif',
                     'href' => tep_href_link(FILENAME_BACKUP, 'selected_box=tools'),
                     'children' => array(array('title' => 'Who\'s online', 'link' => tep_href_link('whos_online.php', 'selected_box=tools')),
                                         array('title' => 'Send Mail', 'link' => tep_href_link('mail.php', 'selected_box=tools')),
                                         array('title' => 'Google Sitemap', 'link' => tep_href_link('googlesitemap.php', 'selected_box=tools')))));

  $languages = tep_get_languages();
  $languages_array = array();
  $languages_selected = DEFAULT_LANGUAGE;
  for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
    $languages_array[] = array('id' => $languages[$i]['code'],
                               'text' => $languages[$i]['name']);
    if ($languages[$i]['directory'] == $language) {
      $languages_selected = $languages[$i]['code'];
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<style type="text/css"><!--
a { color:#080381; text-decoration:none; }
a:hover { color:#aabbdd; text-decoration:underline; }
a.text:link, a.text:visited { color: #000000; text-decoration: none; }
a:text:hover { color: #000000; text-decoration: underline; }
a.main:link, a.main:visited { color: #ffffff; text-decoration: none; }
A.main:hover { color: #ffffff; text-decoration: underline; }
a.sub:link, a.sub:visited { color: #dddddd; text-decoration: none; }
A.sub:hover { color: #dddddd; text-decoration: underline; }
.heading { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 20px; font-weight: bold; line-height: 1.5; color: #D3DBFF; }
.main { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 17px; font-weight: bold; line-height: 1.5; color: #ffffff; }
.sub { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; font-weight: bold; line-height: 1.5; color: #dddddd; }
.text { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11px; font-weight: bold; line-height: 1.5; color: #000000; }
.menuBoxHeading { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 12px; color: #ffffff; font-weight: bold; background-color: #7187bb; border-color: #7187bb; border-style: solid; border-width: 1px; }
.infoBox { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; color: #080381; background-color: #f2f4ff; border-color: #7187bb; border-style: solid; border-width: 1px; }
.smallText { font-family: Verdana, Arial, sans-serif; font-size: 10px; }
//--></style>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">

<table border="0" width="600" height="100%" cellspacing="0" cellpadding="0" align="center" valign="middle">
  <tr>
    <td><table border="0" width="600" height="440" cellspacing="0" cellpadding="1" align="center" valign="middle">
      <tr bgcolor="#000000">
        <td><table border="0" width="600" height="440" cellspacing="0" cellpadding="0">
          <tr bgcolor="#ffffff" height="50">
            <td height="50"><?php echo '<a href="http://aabox.com/Francesco Rossi.html">' . tep_image(DIR_WS_IMAGES . 'Francesco Rossi.gif', 'Get the latest AABox MS2-MAX version here', '204', '50') . '</a>'; ?></td>
            <td align="right" class="text" nowrap><?php echo '&nbsp;&nbsp;<a href="https://aabox.com/cgi-bin/helpdesk/pdesk.cgi" target="_blank" class="headerLink">Help Desk</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="http://oscdox.com" class="headerLink">' . HEADER_TITLE_OSCDOX . '</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . HEADER_TITLE_ADMINISTRATION . '</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="' . tep_catalog_href_link() . '">' . HEADER_TITLE_ONLINE_CATALOG . '</a>'; ?>&nbsp;&nbsp;</td>
          </tr>
          <tr bgcolor="#080381">
            <td colspan="2"><table border="0" width="460" height="390" cellspacing="0" cellpadding="2">
              <tr valign="top">
                <td width="140" valign="top"><table border="0" width="140" height="390" cellspacing="0" cellpadding="2">
                  <tr>
                    <td valign="top"><br>
<?php
  $heading = array();
  $contents = array();

  $heading[] = array('params' => 'class="menuBoxHeading"',
                     'text'  => 'Francesco Rossi');

  $contents[] = array('params' => 'class="infoBox"',
                      'text'  => '<a href="http://www.ontc.eu" target="_blank">' . BOX_ENTRY_SUPPORT_SITE . '</a><br>' .
                                 '<a href="http://www.ontc.eu/community.php/forum" target="_blank">' . BOX_ENTRY_SUPPORT_FORUMS . '</a><br>' .
                                 '<a href="http://www.ontc.eu/community.php/mlists" target="_blank">' . BOX_ENTRY_MAILING_LISTS . '</a><br>' .
                                 '<a href="http://www.ontc.eu/community.php/bugs" target="_blank">' . BOX_ENTRY_BUG_REPORTS . '</a><br>' .
                                 '<a href="http://www.ontc.eu/community.php/faq" target="_blank">' . BOX_ENTRY_FAQ . '</a><br>' .
                                 '<a href="http://www.ontc.eu/community.php/irc" target="_blank">' . BOX_ENTRY_LIVE_DISCUSSIONS . '</a><br>' .
                                 '<a href="http://www.ontc.eu/community.php/cvs" target="_blank">' . BOX_ENTRY_CVS_REPOSITORY . '</a><br>' .
                                 '<a href="http://www.ontc.eu/about.php/portal" target="_blank">' . BOX_ENTRY_INFORMATION_PORTAL . '</a><br>' .
								 '<a href="http://oscdox.com" target="_blank">' . BOX_ENTRY_OSCDOX . '</a>');
  

  $box = new box;
  echo $box->menuBox($heading, $contents);

  echo '<br>';

  $orders_contents = '';
  $orders_status_query = tep_db_query("select orders_status_name, orders_status_id from " . TABLE_ORDERS_STATUS . " where language_id = '" . $languages_id . "'");
  while ($orders_status = tep_db_fetch_array($orders_status_query)) {
    $orders_pending_query = tep_db_query("select count(*) as count from " . TABLE_ORDERS . " where orders_status = '" . $orders_status['orders_status_id'] . "'");
    $orders_pending = tep_db_fetch_array($orders_pending_query);
//Admin begin
//    $orders_contents .= '<a href="' . tep_href_link(FILENAME_ORDERS, 'selected_box=customers&status=' . $orders_status['orders_status_id']) . '">' . $orders_status['orders_status_name'] . '</a>: ' . $orders_pending['count'] . '<br>';
    if (tep_admin_check_boxes(FILENAME_ORDERS, 'sub_boxes') == true) { 
      $orders_contents .= '<a href="' . tep_href_link(FILENAME_ORDERS, 'selected_box=customers&status=' . $orders_status['orders_status_id']) . '">' . $orders_status['orders_status_name'] . '</a>: ' . $orders_pending['count'] . '<br>';
    } else {
      $orders_contents .= '' . $orders_status['orders_status_name'] . ': ' . $orders_pending['count'] . '<br>';
    }
//Admin end
  }
  $orders_contents = substr($orders_contents, 0, -4);

  $heading = array();
  $contents = array();

  $heading[] = array('params' => 'class="menuBoxHeading"',
                     'text'  => BOX_TITLE_ORDERS);

  $contents[] = array('params' => 'class="infoBox"',
                      'text'  => $orders_contents);

  $box = new box;
  echo $box->menuBox($heading, $contents);

  echo '<br>';

  $customers_query = tep_db_query("select count(*) as count from " . TABLE_CUSTOMERS);
  $customers = tep_db_fetch_array($customers_query);
  $products_query = tep_db_query("select count(*) as count from " . TABLE_PRODUCTS . " where products_status = '1'");
  $products = tep_db_fetch_array($products_query);
  $reviews_query = tep_db_query("select count(*) as count from " . TABLE_REVIEWS);
  $reviews = tep_db_fetch_array($reviews_query);

  $heading = array();
  $contents = array();

  $heading[] = array('params' => 'class="menuBoxHeading"',
                     'text'  => BOX_TITLE_STATISTICS);

  $contents[] = array('params' => 'class="infoBox"',
                      'text'  => BOX_ENTRY_CUSTOMERS . ' ' . $customers['count'] . '<br>' .
                                 BOX_ENTRY_PRODUCTS . ' ' . $products['count'] . '<br>' .
                                 BOX_ENTRY_REVIEWS . ' ' . $reviews['count']);

  $box = new box;
  echo $box->menuBox($heading, $contents);

  echo '<br>';

  $contents = array();

  if (getenv('HTTPS') == 'on') {
    $size = ((getenv('SSL_CIPHER_ALGKEYSIZE')) ? getenv('SSL_CIPHER_ALGKEYSIZE') . '-bit' : '<i>' . BOX_CONNECTION_UNKNOWN . '</i>');
    $contents[] = array('params' => 'class="infoBox"',
                        'text' => tep_image(DIR_WS_ICONS . 'locked.gif', ICON_LOCKED, '', '', 'align="right"') . sprintf(BOX_CONNECTION_PROTECTED, $size));
  } else {
    $contents[] = array('params' => 'class="infoBox"',
                        'text' => tep_image(DIR_WS_ICONS . 'unlocked.gif', ICON_UNLOCKED, '', '', 'align="right"') . BOX_CONNECTION_UNPROTECTED);
  }

  $box = new box;
  echo $box->tableBlock($contents);
?>
                    </td>
                  </tr>
                </table></td>
                <td width="460"><table border="0" width="460" height="390" cellspacing="0" cellpadding="2">
                  <tr>
                    <td colspan="2"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                      <tr><?php echo tep_draw_form('languages', 'index.php', '', 'get'); ?>
                        <td class="heading"><?php echo HEADING_TITLE; ?></td>
                        <td align="right"><?php echo tep_draw_pull_down_menu('language', $languages_array, $languages_selected, 'onChange="this.form.submit();"'); ?></td>
                      </form></tr>
                    </table></td>
                  </tr>
<?php
  $col = 2;
  $counter = 0;
  for ($i = 0, $n = sizeof($cat); $i < $n; $i++) {
    $counter++;
    if ($counter < $col) {
      echo '                  <tr>' . "\n";
    }

    echo '                    <td><table border="0" cellspacing="0" cellpadding="2">' . "\n" .
         '                      <tr>' . "\n" .
         '                        <td><a href="' . $cat[$i]['href'] . '">' . tep_image(DIR_WS_IMAGES . 'categories/' . $cat[$i]['image'], $cat[$i]['title'], '32', '32') . '</a></td>' . "\n" .
         '                        <td><table border="0" cellspacing="0" cellpadding="2">' . "\n" .
         '                          <tr>' . "\n" .
         '                            <td class="main"><a href="' . $cat[$i]['href'] . '" class="main">' . $cat[$i]['title'] . '</a></td>' . "\n" .
         '                          </tr>' . "\n" .
         '                          <tr>' . "\n" .
         '                            <td class="sub">';

    $children = '';
    for ($j = 0, $k = sizeof($cat[$i]['children']); $j < $k; $j++) {
      $children .= '<a href="' . $cat[$i]['children'][$j]['link'] . '" class="sub">' . $cat[$i]['children'][$j]['title'] . '</a>, ';
    }
    echo substr($children, 0, -2);

    echo '</td> ' . "\n" .
         '                          </tr>' . "\n" .
         '                        </table></td>' . "\n" .
         '                      </tr>' . "\n" .
         '                    </table></td>' . "\n";

    if ($counter >= $col) {
      echo '                  </tr>' . "\n";
      $counter = 0;
    }
  }
?>
                </table></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php require(DIR_WS_INCLUDES . 'footer.php'); ?></td>
      </tr>
    </table></td>
  </tr>
</table>

</body>

</html>
