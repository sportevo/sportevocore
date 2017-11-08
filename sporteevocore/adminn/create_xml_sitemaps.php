<?php
/*
  $Id: create_xml_sitemaps.php,v2.11 2009/09/29 Kevin L. Shelton

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/
  require('includes/application_top.php');
  
	require(DIR_WS_FUNCTIONS. 'dynamic_sitemap.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<meta name="robots" content="noindex, nofollow">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href=includes/stylesheet.css>
<?php
$sitemap_url = urlencode(tep_catalog_href_link('sitemaps.xml'));
$ping = "http://www.google.com/webmasters/sitemaps/ping?sitemap=" . $sitemap_url;
echo "<script type=\"text/javascript\">\n";
echo "<!--\n";
echo "function opennotifywindows(){\n";
echo " window.open(\"$ping\",\"Google Notify\",\"toolbar=no,location=yes,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=400,height=200\");\n";
$ping = "http://submissions.ask.com/ping?sitemap=" . $sitemap_url;
echo " window.open(\"$ping\",\"Ask.com Notify\",\"toolbar=no,location=yes,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=400,height=200\");\n";
$ping = "http://search.yahooapis.com/SiteExplorerService/V1/ping?sitemap=" . $sitemap_url;
echo " window.open(\"$ping\",\"Yahoo Notify\",\"toolbar=no,location=yes,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=400,height=200\");\n";
$ping = "http://www.bing.com/webmaster/ping.aspx?sitemap=" . $sitemap_url;
echo " window.open(\"$ping\",\"Bing Notify\",\"toolbar=no,location=yes,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=400,height=200\");\n";
echo "}\n";
echo "//-->\n";
echo "</script>\n\n";
?>
</head>
<body onclick="opennotifywindows()" marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
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
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr><td class="main">
<?php 
// function to escape code xml data as required by Google
function smspecialchars($input) {
  return str_replace("'", '&apos;', htmlspecialchars($input));
}
// set values sent from site map maintenance
$wording = array('always','hourly','daily','weekly','monthly','yearly','never');
$cmcf = (isset($_POST['cmcf']) && in_array($_POST['cmcf'], $wording) ? $_POST['cmcf'] : 'weekly');
$scf = (isset($_POST['scf']) && in_array($_POST['scf'], $wording) ? $_POST['scf'] : 'monthly');
$zones = array("au_cdt" => '+09:30',
  "au_cst" => '+09:30',
  "au_cxt" => '+07:00',
  "au_edt" => '+10:00',
  "au_est" => '+10:00',
  "au_nft" => '+11:30',
  "au_wdt" => '+08:00',
  "au_wst" => '+08:00',
  "na_adt" => '-03:00',
  "na_akdt" => '-08:00',
  "na_akst" => '-09:00',
  "na_ast" => '-04:00',
  "na_cdt" => '-05:00',
  "na_cst" => '-06:00',
  "na_edt" => '-04:00',
  "na_est" => '-05:00',
  "na_hadt" => '-09:00',
  "na_hast" => '-10:00',
  "na_mdt" => '-06:00',
  "na_mst" => '-07:00',
  "na_ndt" => '-02:30',
  "na_nst" => '-03:30',
  "na_pdt" => '-07:00',
  "na_pst" => '-08:00',
  "eu_bst" => '+01:00',
  "eu_cest" => '+02:00',
  "eu_cet" => '+01:00',
  "eu_eest" => '+03:00',
  "eu_eet" => '+02:00',
  "eu_gmt" => '+00:00',
  "eu_ist" => '+01:00',
  "eu_west" => '+01:00',
  "eu_wet" => '+00:00');
$tzone =(isset($_POST['tz']) && isset($zones[$_POST['tz']]) ? $zones[$_POST['tz']] : '-08:00');

//create sitemap index
$now = date("Y-m-d\TH:i:s") . $tzone;
echo TEXT_CREATE_INDEX . $now . '<p>';
$smi = '<?xml version="1.0" encoding="UTF-8"?>' ."\n".
'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n" .
'<sitemap><loc>' . smspecialchars(tep_catalog_href_link('smproducts.xml')) . "</loc><lastmod>" . $now . "</lastmod></sitemap>\n" .
'<sitemap><loc>' . smspecialchars(tep_catalog_href_link('smnewprods.xml')) . "</loc><lastmod>" . $now . "</lastmod></sitemap>\n" .
"<sitemap><loc>" . smspecialchars(tep_catalog_href_link('smcats.xml')) . "</loc><lastmod>" . $now . "</lastmod></sitemap>\n" .
'<sitemap><loc>' . smspecialchars(tep_catalog_href_link('smmfg.xml')) . "</loc><lastmod>" . $now . "</lastmod></sitemap>\n" .
'<sitemap><loc>' . smspecialchars(tep_catalog_href_link('smspecials.xml')) . "</loc><lastmod>" . $now . "</lastmod></sitemap>\n" .
"<sitemap><loc>" . smspecialchars(tep_catalog_href_link('smmain.xml')) . "</loc><lastmod>" . $now . "</lastmod></sitemap>\n" .
'</sitemapindex>';
$sm = DIR_FS_CATALOG . 'sitemaps.xml';
$fh = fopen($sm, 'w') or die(ERROR_INDEX_FILE);
fwrite($fh, utf8_encode($smi));
fclose($fh);

//get all files in catalog that aren't set as excluded
echo TEXT_FINDING_FILES . '<br>';
	 $excluded_query = tep_db_query('select exclude_file from ' . TABLE_SITEMAP_EXCLUDE . ' where exclude_type != "0" and is_box="0"');
	 $excluded_array = array();
	 $files = array(); $cnt = 0;
	 if (tep_db_num_rows($excluded_query))
	  while($ex = tep_db_fetch_array($excluded_query))
   			$excluded_array[] = $ex['exclude_file'];
	 if ($handle = opendir(DIR_FS_CATALOG)) {
    while ($file = readdir($handle)){
		    if(!is_dir($file) && (strtolower(substr($file, -4, 4)) === ".php")) //only look at php files
		    {
						if (!in_array($file ,$excluded_array)){
				          $engFile = DIR_FS_CATALOG . DIR_WS_LANGUAGES . $language . '/' . $file;
				          if (file_exists($engFile) && IsViewable(DIR_FS_CATALOG.$file)){
				            //see if this file should be linked via ssl
				            $securelink= 'NONSSL'; // assume a non ssl page
				            $SSLfp = file(DIR_FS_CATALOG . $file ); // load the root file into a variable
				            for ($SSLidx = 0; $SSLidx < count($SSLfp); ++$SSLidx){ //go through root file line by line until the doctype tag is encountered
				              if ((!(strpos($SSLfp[$SSLidx], "breadcrumb->add") === FALSE)) && (!(strpos($SSLfp[$SSLidx], "'SSL") === FALSE))) { // determine if the bread crumb variable is in this line and it has the letters 'SSL' in it
				                $securelink= 'SSL'; // set the ssl link to true
				                break;
				              } elseif (!(strpos(strtolower($SSLfp[$SSLidx]), "<!doctype") === FALSE)) { //doctype tag is found(too soon?), exit loop and do not use SSL
				                break; // exit the loop and do not set ssl link to true
				              }
				            }
				            $files[] = array('path' => $file,
				                             'modified' => date("Y-m-d\TH:i:s", filemtime(DIR_FS_CATALOG.$file)) . $tzone,
				                             'securelink' => $securelink);
				            $cnt++;
				          }
				        }
		        }
		    }
		
		closedir($handle);
 } else echo ERROR_CANNOT_OPEN_CATALOG_DIR . DIR_FS_CATALOG.'<br>';
// create main sitemap
$xml_head = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
$xml_foot = "</urlset>";
echo TEXT_CREATE_MAIN . '<br>';
$sm = DIR_FS_CATALOG . 'smmain.xml';
$fh = fopen($sm, 'w') or die(ERROR_MAIN_FILE);
fwrite($fh, utf8_encode($xml_head));
for ($b = 0; $b < $cnt; ++$b) {
  $fPath = tep_catalog_href_link($files[$b]['path'], '', $files[$b]['securelink']);
  echo $fPath . ' --> ' . $files[$b]['modified'] . '<br>';
  fwrite($fh, utf8_encode('<url><loc>' . smspecialchars($fPath) . '</loc><lastmod>' . $files[$b]['modified'] . '</lastmod></url>' . "\n"));
   }
fwrite($fh, utf8_encode($xml_foot));
fclose($fh); 
echo $cnt . TEXT_TOTAL_FILES . '<p>';

// Return all subcategory IDs
  function get_subcategories(&$subcategories_array, $parent_id = 0) {
    $subcategories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$parent_id . "'");
    while ($subcategories = tep_db_fetch_array($subcategories_query)) {
      $subcategories_array[] = $subcategories['categories_id'];
      if ($subcategories['categories_id'] != $parent_id) {
        get_subcategories($subcategories_array, $subcategories['categories_id']);
      }
    }
  }
  
// check for existance of hidden categories
$hiddencats = array();
$category_status_check = '';
$check_query = tep_db_query("select * from " . TABLE_CATEGORIES); // look for category status variables
$check = tep_db_fetch_array($check_query);
if (isset($check['status_categ'])) { // skips if this is not set to avoid SQL error
  $category_status_check = ' and status_categ = 1';
  $hcquery = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where status_categ = 0");
  while ($cat = tep_db_fetch_array($hcquery)) {// build array of hidden categories and their subcategories
    $hiddencats[] = $cat['categories_id'];
    get_subcategories($hiddencats, $cat['categories_id']);
  }
} elseif (isset($check['categories_status'])) { // skips if this is not set to avoid SQL error
  $category_status_check = ' and categories_status = 1';
  $hcquery = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where categories_status = 0");
  while ($cat = tep_db_fetch_array($hcquery)) {// build array of hidden categories and their subcategories
    $hiddencats[] = $cat['categories_id'];
    get_subcategories($hiddencats, $cat['categories_id']);
  }
}

//create products listing sitemap
echo TEXT_CREATE_PRODUCTS . '<br>';            
$sm = DIR_FS_CATALOG . 'smproducts.xml';
$fh = fopen($sm, 'w') or die(ERROR_PRODUCTS_FILE);
fwrite($fh, utf8_encode($xml_head));
$cnt = 0;
if ((HIDE_HIDDEN_CAT_PRODS == 'true') && !empty($hiddencats)) { // if products found only in hidden categories should be hidden and hidden categories exist
  $urls_query = tep_db_query("select p.products_id, if(products_last_modified > products_date_added, products_last_modified, products_date_added) as rev_date from " . TABLE_PRODUCTS . " p join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where products_status = 1 and p.products_id = p2c.products_id and (not (p2c.categories_id in (" . implode(',', $hiddencats) . "))) group by p2c.products_id order by rev_date desc");
} else {
  $urls_query = tep_db_query("select products_id, if(products_last_modified > products_date_added, products_last_modified, products_date_added) as rev_date from " . TABLE_PRODUCTS . " where products_status = 1 order by rev_date desc");
}
while($urls = tep_db_fetch_array($urls_query)) { // list all in stock items that aren't hidden
  $this_url = tep_catalog_href_link("product_info.php", "products_id=" . $urls['products_id']); // url to your product pages
  $date_mod = str_replace(' ', 'T', $urls['rev_date']);
	$lastmod = "<lastmod>" . $date_mod . $tzone . "</lastmod>";
  $output = "<url><loc>" . smspecialchars($this_url) . "</loc>" . $lastmod . "</url>\n";
  echo $this_url . ' --> ' . $date_mod .'<br>';
	fwrite($fh, utf8_encode($output));
	$cnt++;
}
fwrite($fh, utf8_encode($xml_foot));
fclose($fh); 
echo $cnt . TEXT_TOTAL_PRODUCTS . '<p>';

//create categories listing sitemap
echo TEXT_CREATE_CATEGORIES . '<br>';            
$sm = DIR_FS_CATALOG . 'smcats.xml';
$fh = fopen($sm, 'w') or die(ERROR_CATEGORIES_FILE);
fwrite($fh, utf8_encode($xml_head));
// Return an array with the category and its subcategories
  function get_subcats($parent, $children = '') {
    global $category_status_check;
    if (!is_array($children)) $children = array($parent);
    $query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = " . (int)$parent . $category_status_check);
    while ($cats = tep_db_fetch_array($query)) {
      $children[] = $cats['categories_id'];
      $children = get_subcats($cats['categories_id'], $children);
    }
    return $children;  
  }
// Return the number of products in a category and its subcategories
  function count_products_in_category($category_id) {
    $products_query = tep_db_query("select count(p2c.products_id) as total from " . TABLE_PRODUCTS . " p join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = 1 and p2c.products_id = p.products_id  and (p2c.categories_id in (" . implode(',', get_subcats($category_id)) . "))");
    $products = tep_db_fetch_array($products_query);
    return $products['total'];
  }
// builds the category paths
  function get_paths($categories_array = '', $parent_id = '0', $path ='') {
    global $languages_id, $category_status_check;
    if (!is_array($categories_array)) $categories_array = array();
    $categories_query = tep_db_query("select categories_id from " . TABLE_CATEGORIES . " where parent_id = " . (int)$parent_id . $category_status_check);
    while ($categories = tep_db_fetch_array($categories_query)) {
      if (count_products_in_category($categories['categories_id']) > 0) { //list only categories containing active products or that have subcategories containing active products
        if ($parent_id=='0') {
	        $categories_array[] = array('pathid' => $categories['categories_id'],
                                      'catid' => $categories['categories_id']);
        } else {
	        $categories_array[] = array('pathid' => $path . $parent_id . '_' . $categories['categories_id'],
        	                            'catid' => $categories['categories_id']);
        }
        if ($categories['categories_id'] != $parent_id) {
         	$this_path=$path;
        	if ($parent_id != '0') $this_path = $path . $parent_id . '_';
          $categories_array = get_paths($categories_array, $categories['categories_id'], $this_path);
        }
      }
    }

    return $categories_array;
  }
$categories = get_paths();
$cnt = 0;
$totalpages = 0;
while ($cnt < count($categories)) {
  $prod_query = tep_db_query("select count(ptc.products_id) as numprods from " . TABLE_PRODUCTS . " p join " . TABLE_PRODUCTS_TO_CATEGORIES . " ptc where products_status = 1 and p.products_id = ptc.products_id and categories_id = " . (int)$categories[$cnt]['catid']); // number of active products in this category alone
  $num = tep_db_fetch_array($prod_query);
  $numpages = ($num['numprods'] == 0 ? 1 : ceil($num['numprods'] / MAX_DISPLAY_SEARCH_RESULTS));
  $totalpages += $numpages;
  for ($page = 1; $page <= $numpages; $page++) {// create a url for every page of each category
    $url = tep_catalog_href_link("index.php", "cPath=" . $categories[$cnt]['pathid'] . '&page=' . $page); // url to your category pages
    echo $categories[$cnt]['catid'] .' --> '. $url .'<br>';
    fwrite($fh, utf8_encode('<url><loc>' . smspecialchars($url) . "</loc><changefreq>" . $cmcf . "</changefreq></url>\n"));
  }
  $cnt++;
}
fwrite($fh, utf8_encode($xml_foot));
fclose($fh); 
echo $cnt . TEXT_TOTAL_CATEGORIES . $totalpages . TEXT_TOTAL_PAGES . '<p>';

//create manufacturers listing sitemap
echo TEXT_CREATE_MANUFACTURERS . '<br>';            
$sm = DIR_FS_CATALOG . 'smmfg.xml';
$fh = fopen($sm, 'w') or die(ERROR_MANUFACTURERS_FILE);
fwrite($fh, utf8_encode($xml_head));
$cnt = 0;
$totalpages = 0;
$mfg_query = tep_db_query('select manufacturers_id, manufacturers_name from ' . TABLE_MANUFACTURERS);
while ($mfg = tep_db_fetch_array($mfg_query)) {
  if ((HIDE_HIDDEN_CAT_PRODS == 'true') && !empty($hiddencats)) { // if products only in hidden categories should be hidden and hidden categories exist
    $prod_query = tep_db_query("select count(distinct p2c.products_id) as numprods from " . TABLE_PRODUCTS . " p join " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where products_status = 1 and manufacturers_id = " . (int)$mfg['manufacturers_id'] . ' and p.products_id = p2c.products_id and (not (p2c.categories_id in (' . implode(',', $hiddencats) . ")))");
  } else {
    $prod_query = tep_db_query("select count(products_id) as numprods from " . TABLE_PRODUCTS . " where products_status = 1 and manufacturers_id = " . (int)$mfg['manufacturers_id']);
  }
  $num = tep_db_fetch_array($prod_query);
  if ($num['numprods'] > 0) { // only list manufacturers that have products
    $numpages = ceil($num['numprods'] / MAX_DISPLAY_SEARCH_RESULTS);
    $totalpages += $numpages;
    for ($page = 1; $page <= $numpages; $page++) {// create a url for every page of each manufacturer
      $url = tep_catalog_href_link("index.php", "manufacturers_id=" . $mfg['manufacturers_id'] . '&page=' . $page); // url to your manufacturer pages
      echo $mfg['manufacturers_name'] .' --> '. $url .'<br>';
      fwrite($fh, utf8_encode('<url><loc>' . smspecialchars($url) . "</loc><changefreq>" . $cmcf . "</changefreq></url>\n"));
    }
    $cnt++;
  }
}
fwrite($fh, utf8_encode($xml_foot));
fclose($fh); 
echo $cnt . TEXT_TOTAL_MANUFACTURERS . $totalpages . TEXT_TOTAL_PAGES . '<p>';

//create specials listing sitemap
echo TEXT_CREATE_SPECIALS . '<br>';            
$sm = DIR_FS_CATALOG . 'smspecials.xml';
$fh = fopen($sm, 'w') or die(ERROR_SPECIALS_FILE);
fwrite($fh, utf8_encode($xml_head));
$cnt = 0;
if ((HIDE_HIDDEN_CAT_PRODS == 'true') && !empty($hiddencats)) { // if products only in hidden categories should be hidden and hidden categories exist
  $special_query = tep_db_query('select count(distinct s.products_id) as numspecials from ' . TABLE_SPECIALS . ' s join ' . TABLE_PRODUCTS . ' p join ' . TABLE_PRODUCTS_TO_CATEGORIES . ' p2c where p.products_status = 1 and s.products_id = p.products_id and s.status = 1 and p.products_id = p2c.products_id and (not (p2c.categories_id in (' . implode(',', $hiddencats) . ")))");
} else {
  $special_query = tep_db_query('select count(distinct s.products_id) as numspecials from ' . TABLE_SPECIALS . ' s join ' . TABLE_PRODUCTS . ' p where p.products_status = 1 and s.products_id = p.products_id and s.status = 1');
}
$num = tep_db_fetch_array($special_query);
$numpages = ($num['numspecials'] == 0 ? 1 : ceil($num['numspecials'] / MAX_DISPLAY_SPECIAL_PRODUCTS));
for ($page = 1; $page <= $numpages; $page++) { // create a url for every page of specials
  $url = tep_catalog_href_link("specials.php", "page=" . $page); // url to your specials pages
  echo $page .' --> '. $url .'<br>';
  fwrite($fh, utf8_encode('<url><loc>' . smspecialchars($url) . "</loc><changefreq>" . $scf . "</changefreq></url>\n"));
}
fwrite($fh, utf8_encode($xml_foot));
fclose($fh); 
echo $numpages . TEXT_TOTAL_PAGES . '<p>';

//create new products listing sitemap
echo TEXT_CREATE_NEWPRODS . '<br>';            
$sm = DIR_FS_CATALOG . 'smnewprods.xml';
$fh = fopen($sm, 'w') or die(ERROR_NEWPRODS_FILE);
fwrite($fh, utf8_encode($xml_head));
$cnt = 0;
if ((HIDE_HIDDEN_CAT_PRODS == 'true') && !empty($hiddencats)) { // if products only in hidden categories should be hidden and hidden categories exist
  $newprods_query = tep_db_query('select count(distinct p2c.products_id) as total from ' . TABLE_PRODUCTS . ' p join ' . TABLE_PRODUCTS_TO_CATEGORIES . ' p2c where p.products_status = 1 and p.products_id = p2c.products_id and (not (p2c.categories_id in (' . implode(',', $hiddencats) . ")))");
} else {
  $newprods_query = tep_db_query('select count(products_id) as total from ' . TABLE_PRODUCTS . ' where products_status = 1');
}
$num = tep_db_fetch_array($newprods_query);
$numpages = ($num['total'] == 0 ? 1 : ceil($num['total'] / MAX_DISPLAY_PRODUCTS_NEW));
for ($page = 1; $page <= $numpages; $page++) { // create a url for every page of specials
  $url = tep_catalog_href_link("products_new.php", "page=" . $page); // url to your specials pages
  echo $page .' --> '. $url .'<br>';
  fwrite($fh, utf8_encode('<url><loc>' . smspecialchars($url) . "</loc><changefreq>" . $cmcf . "</changefreq></url>\n"));
}
fwrite($fh, utf8_encode($xml_foot));
fclose($fh); 
echo $numpages  . TEXT_TOTAL_PAGES . '<p>' . TEXT_COMPLETED;

echo '<p><a href="' . tep_href_link(FILENAME_SITEMAP,'selected_box=tools') . '">' . TEXT_TO_MAINTENANCE . '</a><p>&nbsp;';
?>    
     </td></tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
