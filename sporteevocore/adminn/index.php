<?php
/*
  $Id: index.php,v 1.19 2003/06/27 09:38:31 dgw_ Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
  Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2)

  Please note: DO NOT DELETE this file if disabling the above contribution.
  Edits are listed by number. Locate and modify as needed to disable the contribution.
*/

  require('includes/application_top.php');
  
  // BOF: KategorienAdmin / OLISWISS
  if ($login_groups_id != 1) {
    tep_redirect(tep_href_link(FILENAME_CATEGORIES, ''));
  }
// BOF: KategorienAdmin / OLISWISS

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();
 // Support ---------------------
               array('title' => BOX_SUPPORT_HEADING,
                     'image' => 'helpdesk.gif',
                     'href' => tep_href_link(FILENAME_SUPPORT_TICKETS, 'selected_box=support'),
                     'children' => array(array('title' => 'Support Tickets', 'link' => tep_href_link('support.php', 'selected_box=support')),
                                         array('title' => 'FAQ', 'link' => tep_href_link('faq.php', 'selected_box=support'))));
//end of support ------------------ 
// BOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 1 of 10
// uncomment below line to disable this contribution
//	$cat = array(array('title' => BOX_HEADING_CONFIGURATION,
// comment below lines to disable this contribution
  $cat = array(array('title' => BOX_HEADING_ADMINISTRATOR,
                     'access' => tep_admin_check_boxes('administrator.php'),
                     'image' => 'administrator.gif',
                     'href' => tep_href_link(tep_selected_file('administrator.php'), 'selected_box=administrator'),
                     'children' => array(array('title' => BOX_ADMINISTRATOR_MEMBER, 'link' => tep_href_link(FILENAME_ADMIN_MEMBERS, 'selected_box=administrator'),
                                               'access' => tep_admin_check_boxes(FILENAME_ADMIN_MEMBERS, 'sub_boxes')),
                                         array('title' => BOX_ADMINISTRATOR_BOXES, 'link' => tep_href_link(FILENAME_ADMIN_FILES, 'selected_box=administrator'),
                                               'access' => tep_admin_check_boxes(FILENAME_ADMIN_FILES, 'sub_boxes')))),
								array('title' => BOX_HEADING_MY_ACCOUNT,
                     'access' => 'true',
                     'image' => 'my_account.gif',
                     'href' => tep_href_link(FILENAME_ADMIN_ACCOUNT),
                     'children' => array(array('title' => HEADER_TITLE_ACCOUNT, 'link' => tep_href_link(FILENAME_ADMIN_ACCOUNT),
                                               'access' => 'true'),
                                         array('title' => HEADER_TITLE_LOGOFF, 'link' => tep_href_link(FILENAME_LOGOFF),
                                               'access' => 'true'))),
                array('title' => BOX_HEADING_CONFIGURATION,
                     'access' => tep_admin_check_boxes('configuration.php'),
// EOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 1 of 10
                     'image' => 'configuration.gif',
                     'href' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration&gID=1'),
                     'children' => array(array('title' => BOX_CONFIGURATION_MYSTORE, 'link' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration&gID=1')),
                                         array('title' => BOX_CONFIGURATION_LOGGING, 'link' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration&gID=10')),
                                         array('title' => BOX_CONFIGURATION_CACHE, 'link' => tep_href_link(FILENAME_CONFIGURATION, 'selected_box=configuration&gID=11')))),
              	array('title' => BOX_HEADING_MODULES,
// BOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 2 of 10
// comment out below line to disable this contribution
                     'access' => tep_admin_check_boxes('modules.php'),
// EOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 2 of 10
                     'image' => 'modules.gif',
                     'href' => tep_href_link(FILENAME_MODULES, 'selected_box=modules&set=payment'),
                     'children' => array(array('title' => BOX_MODULES_PAYMENT, 'link' => tep_href_link(FILENAME_MODULES, 'selected_box=modules&set=payment')),
                                         array('title' => BOX_MODULES_SHIPPING, 'link' => tep_href_link(FILENAME_MODULES, 'selected_box=modules&set=shipping')))),
                array('title' => BOX_HEADING_CATALOG,
// BOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 3 of 10
// comment out below line to disable this contribution
                     'access' => tep_admin_check_boxes('catalog.php'),
// EOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 3 of 10
                     'image' => 'catalog.gif',
                     'href' => tep_href_link(FILENAME_CATEGORIES, 'selected_box=catalog'),
                     'children' => array(array('title' => CATALOG_CONTENTS, 'link' => tep_href_link(FILENAME_CATEGORIES, 'selected_box=catalog')),
                                         array('title' => BOX_CATALOG_MANUFACTURERS, 'link' => tep_href_link(FILENAME_MANUFACTURERS, 'selected_box=catalog')))),
               	array('title' => BOX_HEADING_LOCATION_AND_TAXES,
// BOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 4 of 10
// comment out below line to disable this contribution
                     'access' => tep_admin_check_boxes('taxes.php'),
// EOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 4 of 10
                     'image' => 'location.gif',
                     'href' => tep_href_link(FILENAME_COUNTRIES, 'selected_box=taxes'),
                     'children' => array(array('title' => BOX_TAXES_COUNTRIES, 'link' => tep_href_link(FILENAME_COUNTRIES, 'selected_box=taxes')),
                                         array('title' => BOX_TAXES_GEO_ZONES, 'link' => tep_href_link(FILENAME_GEO_ZONES, 'selected_box=taxes')))),
               	array('title' => BOX_HEADING_CUSTOMERS,
// BOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 5 of 10
// comment out below line to disable this contribution
                     'access' => tep_admin_check_boxes('customers.php'),
// EOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 5 of 10
                     'image' => 'customers.gif',
                     'href' => tep_href_link(FILENAME_CUSTOMERS, 'selected_box=customers'),
                     'children' => array(array('title' => BOX_CUSTOMERS_CUSTOMERS, 'link' => tep_href_link(FILENAME_CUSTOMERS, 'selected_box=customers')),
                                         array('title' => BOX_CUSTOMERS_ORDERS, 'link' => tep_href_link(FILENAME_ORDERS, 'selected_box=customers')))),
               	array('title' => BOX_HEADING_LOCALIZATION,
// BOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 6 of 10
// comment out below line to disable this contribution
                     'access' => tep_admin_check_boxes('localization.php'),
// EOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 6 of 10
                     'image' => 'localization.gif',
                     'href' => tep_href_link(FILENAME_CURRENCIES, 'selected_box=localization'),
                     'children' => array(array('title' => BOX_LOCALIZATION_CURRENCIES, 'link' => tep_href_link(FILENAME_CURRENCIES, 'selected_box=localization')),
                                         array('title' => BOX_LOCALIZATION_LANGUAGES, 'link' => tep_href_link(FILENAME_LANGUAGES, 'selected_box=localization')))),
               	array('title' => BOX_HEADING_REPORTS,
// BOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 7 of 10
// comment out below line to disable this contribution
                     'access' => tep_admin_check_boxes('reports.php'),
// EOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 7 of 10
                     'image' => 'reports.gif',
                     'href' => tep_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, 'selected_box=reports'),
                     'children' => array(array('title' => REPORTS_PRODUCTS, 'link' => tep_href_link(FILENAME_STATS_PRODUCTS_PURCHASED, 'selected_box=reports')),
                                         array('title' => REPORTS_ORDERS, 'link' => tep_href_link(FILENAME_STATS_CUSTOMERS, 'selected_box=reports')))),
										 
               	array('title' => BOX_HEADING_TOOLS,
// BOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 8 of 10
// comment out below line to disable this contribution
                     'access' => tep_admin_check_boxes('tools.php'),
// EOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 8 of 10
                     'image' => 'tools.gif',
                     'href' => tep_href_link(FILENAME_BACKUP, 'selected_box=tools'),
                     'children' => array(array('title' => TOOLS_BACKUP, 'link' => tep_href_link(FILENAME_BACKUP, 'selected_box=tools')),
                                         array('title' => TOOLS_BANNERS, 'link' => tep_href_link(FILENAME_BANNER_MANAGER, 'selected_box=tools')),
                                         array('title' => TOOLS_FILES, 'link' => tep_href_link(FILENAME_FILE_MANAGER, 'selected_box=tools')))));

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
<!-- BOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 9 of 10 -->
<link rel="stylesheet" type="text/css" href="style.css">

<!-- EOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 9 of 10 -->
</head>
<body>




<div id="logo"></div>

	<div id="centerland"> 
	<div class="menubutton"><a class="menubutton2" href="index.php">home</a></div><div class="menubutton"><a class="menubutton2" href="inventory_report.php"><?php echo BOX_HEADING_REPORTS ?> </a></div> <div class="menubutton"><a class="menubutton2" href="orders.php"><?php echo BOX_CUSTOMERS_ORDERS ?></a></div><div class="menubutton"> <a class="menubutton2" href="create_order.php"><?php echo IMAGE_CREATE_ORDER ?></a> </div><div class="menubutton"><a class="menubutton2" href="categories.php?selected_box=catalog"><?php echo BOX_HEADING_CATALOG ?></a></div> 
    
</div>
    	<div id="langholder"><?php echo tep_draw_form('adminlanguage', FILENAME_DEFAULT, '', 'get') . tep_draw_pull_down_menu('language', $languages_array, $languages_selected, 'onChange="this.form.submit();"') . tep_hide_session_id() . '</form>'; ?></td>
           	       </div>
	


  <div id="mainland"> 
    <div id="maincontainer">
     
      <?php
  $col = 2;
  $counter = 0;
  for ($i = 0, $n = sizeof($cat); $i < $n; $i++) {
    $counter++;
    if ($counter < $col) {
      echo '        			<div class="optionmenu">' . "\n";
    }

    echo '          				<div id="mainmenus"><span class="standardtd">' . "\n" .
    		 '										<div id="icons">' . "\n" .
         '              				<span class="standardtr">' . "\n" .
         '                  			<span class="standardtd"><a href="' . $cat[$i]['href'] . '">' . tep_image(DIR_WS_IMAGES . 'categories/' . $cat[$i]['image'], $cat[$i]['title'], '32', '32') . '</a></span>' . "\n" .
         '                  			<span class="standardtd">' . "\n" .
         '													' . "\n" .
         '                      			<span class="standardtr">' . "\n" .
         '                        			<span class="indexmain"><a href="' . $cat[$i]['href'] . '" class="indexmain">' . $cat[$i]['title'] . '</a></span>' . "\n" .
         '                      			</span>' . "\n" .
         '                      			<span class="standardtr">' . "\n" .
         '                      				<div id="sub">';

    $children = '';
    for ($j = 0, $k = sizeof($cat[$i]['children']); $j < $k; $j++) {
      $children .= '<a href="' . $cat[$i]['children'][$j]['link'] . '" class="sub">' . $cat[$i]['children'][$j]['title'] . '</a>, ';
    }
    echo substr($children, 0, -2);

    echo '															</div> ' . "\n" .
         '                      			</span>' . "\n" .
         '                    			</div>' . "\n" .
         '												</td>' . "\n" .
         '                			</span>' . "\n" .
         '              			</div>' . "\n" .
         '									</span>' . "\n";

    if ($counter >= $col) {
      echo '        			</div>' . "\n";
      $counter = 0;
    }
  }

?>
    </div>
      
    </div>
  </div>
  

















</body>

</html>