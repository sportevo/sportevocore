<?php
/* 
  $Id: index.php,v 1.1 2003/06/11 17:37:59 hpdl Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
// BOF Separate Pricing Per Customer
  if (isset($_SESSION['sppc_customer_group_id']) && $_SESSION['sppc_customer_group_id'] != '0') {
  $customer_group_id = $_SESSION['sppc_customer_group_id'];
  } else {
   $customer_group_id = '0';
  }
// EOF Separate Pricing Per Customer

// Products Specifications
  require_once (DIR_WS_FUNCTIONS . 'products_specifications.php');


// the following cPath references come from application_top.php
   $category_depth = 'top';
  global $countproducts;
  if (isset($cPath) && tep_not_null($cPath)) {
    // $countproducts['prods_in_category'] is not set when SHOW_COUNTS is not true
    if (is_object($countproducts) && is_array($countproducts->prods_in_category)) {
      $categories_products['total'] = $countproducts->CountProductsInCategory((int)$current_category_id);
    } else {
    $categories_products_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int)$current_category_id . "'");
    $cateqories_products = tep_db_fetch_array($categories_products_query);
    } // end if else (is_object($countproducts))
    if ($cateqories_products['total'] > 0) {
      $category_depth = 'products'; // display products
    } else {
      if (is_object($countproducts)) {
        $category_parent['total'] = $countproducts->countChildCategories((int)$current_category_id);
      } else {
      $category_parent_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " where parent_id = '" . (int)$current_category_id . "'");
      $category_parent = tep_db_fetch_array($category_parent_query);
      } // end if else (is_object($countproducts))
      if ($category_parent['total'] > 0) {
        $category_depth = 'nested'; // navigate through the categories
      } else {
        $category_depth = 'products'; // category has no products, but display the 'no products' message
      }
    }
  }

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_DEFAULT);
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<!-- <base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>"> //-->
<?php // Start Categories Images box ?>
<SCRIPT LANGUAGE="JavaScript" type="text/javascript"><!--
function set_CSS(el_id, CSS_attr, CSS_val) {
  var el = document.getElementById(el_id);
  if (el) el.style[CSS_attr] = CSS_val;
}
//-->
</script>

<?php // End Categories Images box ?>

</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<div id="tablemain" >
  
    <span class="columnleftcontaier"><div id="columnleft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </div></span>
<!-- body_text - check /fucntions/sts.php for correct capture //-->
<?php
  if ($category_depth == 'nested') {
    /*** Begin Header Tags SEO ***/
    $category_query = tep_db_query("select cd.categories_name, c.categories_image, cd.categories_htc_title_tag, cd.categories_htc_description from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = '" . (int)$current_category_id . "' and cd.categories_id = '" . (int)$current_category_id . "' and cd.language_id = '" . (int)$languages_id . "'");
    /*** end Header Tags SEO ***/
    $category = tep_db_fetch_array($category_query);
?>
    <span class="standardtd"><div id="tavolaprincipale">
      
      <div id="tavolaprova" >
           <div id="precat">
          
           <div id="greenstripe">
            
          
         
           <?php if (tep_not_null($category['categories_htc_description'])) { ?>
          
           <span id="headertwo">
           <div id="input6"> <form name="quick_find" action="http://www.sportevo.pro/advanced_search_result.php" method="get">
								<input type="hidden" name="search_in_description" value="1">
								<button type="submit" class="input6"><i class="fa fa-search fa-2x"></i>
								</button><input class="input5" type="text"  placeholder=" SEARCH" name="keywords" >
								
							</form> </div>
               
              
           <h2><?php echo $category['categories_htc_title_tag']; ?>
               <?php /*** echo $category['categories_htc_description']; ***/ ?></h2></span>
          <?php /*** Begin Header Tags SEO ***/ ?>
            <div id="headerone"><h2><?php echo $category['categories_htc_title_tag']; ?></h2></div>
          <?php } 
          /*** End Header Tags SEO ***/ 
          ?>
      
        
      
     	
           </div>
           
           

            </div>  
          <input type="checkbox" name="toggle1" id="toggle1" class="toggle1" />
        <label id="toggle_label" for="toggle1"></label>  	
            	
		<div id="preelecont" class="preelecont">
			<?php /*** echo $category['categories_htc_description']; ***/ ?>
			
			<div id="preelefilt">
    	
      	<div id="preelecat">
       <ul class="ulcat">
<?php
    if (isset($cPath) && strpos('_', $cPath)) {
// check to see if there are deeper categories within the current category
      $category_links = array_reverse($cPath_array);
      for($i=0, $n=sizeof($category_links); $i<$n; $i++) {
        $categories_query = tep_db_query("select count(*) as total from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "'");
        $categories = tep_db_fetch_array($categories_query);
        if ($categories['total'] < 1) {
        // do nothing, go through the loop
        } else {
          $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, pcategories.parent_id as grand_parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES . " AS pcategories, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$category_links[$i] . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and pcategories.categories_id = '" . (int)$category_links[$i] . "' order by c.sort_order, cd.categories_name");
          break; // we've found the deepest category the customer is in
        }
      }
    } else {
      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, pcategories.parent_id as grand_parent_id from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES . " AS pcategories, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and pcategories.categories_id = '" . (int)$current_category_id . "' order by c.sort_order, cd.categories_name");
    }

    $number_of_categories = tep_db_num_rows($categories_query);

    $rows = 0;
    while ($categories = tep_db_fetch_array($categories_query)) {
    $rows++;
    $cPath_new = tep_get_path($categories['categories_id'], $categories['parent_id'], $categories['grand_parent_id']);
      $width = (int)(100 / MAX_DISPLAY_CATEGORIES_PER_ROW) . '%';
      echo '                <li class="elencocat"> <a class="elencocat" href="' . tep_href_link(FILENAME_DEFAULT, $cPath_new) . '">' . $categories['categories_name'] . '</a></li>' . "\n";
      if ((($rows / MAX_DISPLAY_CATEGORIES_PER_ROW) == floor($rows / MAX_DISPLAY_CATEGORIES_PER_ROW)) && ($rows != $number_of_categories)) {
        echo '              ' . "\n";
        echo '              ' . "\n";
      }
    }

// needed for the new products module shown below
    $new_products_category_id = $current_category_id;
?>
    </ul>          
   </div>       
            	
  <?php
// Start Products Specifications
  if (SPECIFICATIONS_FILTERS_MODULE == 'True') {
?>
      
      
<?php
    require (DIR_WS_MODULES . 'products_filter.php');
?>
       
<?php
  }
// End Products Specifications
?>  
</div>   
 

         
         
        
         
          </div>
          
          <div id="newprodscapsule">
         
           <?php include(DIR_WS_MODULES . FILENAME_FEATURED); ?>
         </div>
           
           </div>
          <div id="footer"> 
															
						
<div id="partners" name="lang">
								<div id="logoneg">
								<a href="http://www.sportevo.pro"><img alt="sportevo" src="http://www.sportevo.pro/imgs/logoneg.png"></a>
								</div>
								

								<br></br>
								
								<div id="mc_embed_signup">
										
							<form action="//sporteevo.us10.list-manage.com/subscribe/post?u=a9bc0628468b58ea380c799b2&amp;id=3e4b5d2ceb" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
  
    <div id="mc_embed_signup_scroll">

	<input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>

    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_a9bc0628468b58ea380c799b2_3e4b5d2ceb" tabindex="-1" value=""></div>
    <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
    		 <label for="mce-EMAIL">The world of Sport is changing: stay Updated!</label>
    	<a href="https://www.sportevo.pro/playlikeapro/blog/"><i style="color:#F4F4F4;" class="fa fa-wordpress fa-2x" aria-hidden="true"></i></a>
			<a href="https://www.linkedin.com/company-beta/11053461/"><i style="color:#F4F4F4;" class="fa fa-linkedin-square fa-2x" aria-hidden="true"></i></a>
	<a href="https://www.facebook.com/sportevo.pro/">   <i style="color:#F4F4F4;" class="fa fa-facebook-square fa-2x" aria-hidden="true"></i></a> 
	<a href="https://www.instagram.com/sportevo.pro/"><i style="color:#F4F4F4;" class="fa fa-instagram fa-2x" aria-hidden="true"></i></a>
	<br>
    </div>
</form>
<br>
 <br />
        <img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/cc-badges-ppppcmcvdam.png" style="height:30px; " alt="Credit Card Badges" />            
	</div>	 	
								
							</div>
						</div> 	
    
          <!--- END Header Tags SEO Social Bookmarks -->  
        </div>
     
    </div>
<?php
  } elseif ($category_depth == 'products' || isset($_GET['manufacturers_id'])) {
  	// Start Product Specifications
    if ($category_depth == 'products' && SPECIFICATIONS_BOX_COMP_INDEX == 'True') {
      require_once (DIR_WS_MODULES . FILENAME_COMPARISON);
    } else {
// End Product Specifications 
	
// create column list
    $define_list = array('PRODUCT_LIST_MODEL' => PRODUCT_LIST_MODEL,
                         'PRODUCT_LIST_NAME' => PRODUCT_LIST_NAME,
                         'PRODUCT_LIST_MANUFACTURER' => PRODUCT_LIST_MANUFACTURER,
                         'PRODUCT_LIST_PRICE' => PRODUCT_LIST_PRICE,
                         'PRODUCT_LIST_QUANTITY' => PRODUCT_LIST_QUANTITY,
                         'PRODUCT_LIST_WEIGHT' => PRODUCT_LIST_WEIGHT,
                         'PRODUCT_LIST_IMAGE' => PRODUCT_LIST_IMAGE,
                         'PRODUCT_LIST_BUY_NOW' => PRODUCT_LIST_BUY_NOW);

    asort($define_list);

    $column_list = array();
    reset($define_list);
    while (list($key, $value) = each($define_list)) {
      if ($value > 0) $column_list[] = $key;
    }
// BOF Separate Pricing Per Customer
// this will build the table with specials prices for the retail group or update it if needed
// this function should have been added to includes/functions/database.php
   if ($customer_group_id == '0') {
   tep_db_check_age_specials_retail_table();
   }
   $status_product_prices_table = false;
   $status_need_to_get_prices = false;

   // find out if sorting by price has been requested
   if ( (isset($_GET['sort'])) && (ereg('[1-8][ad]', $_GET['sort'])) && (substr($_GET['sort'], 0, 1) <= sizeof($column_list)) && $customer_group_id != '0' ){
    $_sort_col = substr($_GET['sort'], 0 , 1);
    if ($column_list[$_sort_col-1] == 'PRODUCT_LIST_PRICE') {
      $status_need_to_get_prices = true;
      }
   }

   if ($status_need_to_get_prices == true && $customer_group_id != '0') {
   $product_prices_table = TABLE_PRODUCTS_GROUP_PRICES.$customer_group_id;
   // the table with product prices for a particular customer group is re-built only a number of times per hour
   // (setting in /includes/database_tables.php called MAXIMUM_DELAY_UPDATE_PG_PRICES_TABLE, in minutes)
   // to trigger the update the next function is called (new function that should have been
   // added to includes/functions/database.php)
   tep_db_check_age_products_group_prices_cg_table($customer_group_id);
   $status_product_prices_table = true;

   } // end if ($status_need_to_get_prices == true && $customer_group_id != '0')
// EOF Separate Pricing Per Customer

   $select_column_list = "p.sold_in_bundle_only, ";


    for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
      switch ($column_list[$i]) {
        case 'PRODUCT_LIST_MODEL':
          $select_column_list .= 'p.products_model, ';
          break;
        case 'PRODUCT_LIST_NAME':
          $select_column_list .= 'pd.products_name, ';
          break;
        case 'PRODUCT_LIST_MANUFACTURER':
          $select_column_list .= 'm.manufacturers_name, ';
          break;
        case 'PRODUCT_LIST_QUANTITY':
          $select_column_list .= 'p.products_quantity, ';
          break;
        case 'PRODUCT_LIST_IMAGE':
          $select_column_list .= 'p.products_image, ';
          break;
        case 'PRODUCT_LIST_WEIGHT':
          $select_column_list .= 'p.products_weight, ';
          break;
      }
    }

// show the products of a specified manufacturer
    if (isset($_GET['manufacturers_id'])) {
      if (isset($_GET['filter_id']) && tep_not_null($_GET['filter_id'])) {
// We are asked to show only a specific category
// BOF Separate Pricing Per Customer
	if ($status_product_prices_table == true) { // ok in mysql 5
	$listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, tmp_pp.products_price, p.products_tax_class_id, IF(tmp_pp.status, tmp_pp.specials_new_products_price, NULL) as specials_new_products_price, IF(tmp_pp.status, tmp_pp.specials_new_products_price, tmp_pp.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . $product_prices_table . " as tmp_pp using(products_id), " . TABLE_PRODUCTS_DESCRIPTION . " pd , " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$_GET['filter_id'] . "'";		
	} else { // either retail or no need to get correct special prices -- changed for mysql 5
	$listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS_RETAIL_PRICES . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$_GET['filter_id'] . "'";
	} // end else { // either retail...
// EOF Separate Pricing Per Customer
      } else {
// We show them all
// BOF Separate Pricing Per Customer
        if ($status_product_prices_table == true) { // ok in mysql 5
        $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, tmp_pp.products_price, p.products_tax_class_id, IF(tmp_pp.status, tmp_pp.specials_new_products_price, NULL) as specials_new_products_price, IF(tmp_pp.status, tmp_pp.specials_new_products_price, tmp_pp.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . $product_prices_table . " as tmp_pp using(products_id), " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'";	
	} else { // either retail or no need to get correct special prices -- changed for mysql 5
        $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . TABLE_SPECIALS_RETAIL_PRICES . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'";
	} // end else { // either retail...
// EOF Separate Pricing Per Customer
      }
    } else {
// show the products in a given categorie
      if (isset($_GET['filter_id']) && tep_not_null($_GET['filter_id'])) {
// We are asked to show only specific catgeory;  
// BOF Separate Pricing Per Customer
        if ($status_product_prices_table == true) { // ok for mysql 5
        $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_master, p.products_master_status, tmp_pp.products_price, p.products_tax_class_id, IF(tmp_pp.status, tmp_pp.specials_new_products_price, NULL) as specials_new_products_price, IF(tmp_pp.status, tmp_pp.specials_new_products_price, tmp_pp.products_price) as final_price from " . TABLE_PRODUCTS . " p left join " . $product_prices_table . " as tmp_pp using(products_id), " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$_GET['filter_id'] . "' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$current_category_id . "'";	
        } else { // either retail or no need to get correct special prices -- ok in mysql 5
        $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_master, p.products_master_status, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_MANUFACTURERS . " m, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c left join " . TABLE_SPECIALS_RETAIL_PRICES . " s using(products_id) where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and m.manufacturers_id = '" . (int)$_GET['filter_id'] . "' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$current_category_id . "'";
        } // end else { // either retail...
// EOF Separate Pricing Per Customer
      } else {
// We show them all

//Master Products EOF
// BOF Separate Pricing Per Customer --last query changed for mysql 5 compatibility
        if ($status_product_prices_table == true) {
	// original, no need to change for mysql 5
	$listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_master, p.products_master_status, tmp_pp.products_price, p.products_tax_class_id, IF(tmp_pp.status, tmp_pp.specials_new_products_price, NULL) as specials_new_products_price, IF(tmp_pp.status, tmp_pp.specials_new_products_price, tmp_pp.products_price) as final_price from " . TABLE_PRODUCTS_DESCRIPTION . " pd left join " . $product_prices_table . " as tmp_pp using(products_id), " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$current_category_id . "'";
        } else { // either retail or no need to get correct special prices -- changed for mysql 5
        $listing_sql = "select " . $select_column_list . " p.products_id, p.manufacturers_id, p.products_price, p.products_master, p.products_master_status, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, IF(s.status, s.specials_new_products_price, p.products_price) as final_price from " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS . " p left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id left join " . TABLE_SPECIALS_RETAIL_PRICES . " s on p.products_id = s.products_id, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_status = '1' and p.products_id = p2c.products_id and pd.products_id = p2c.products_id and pd.language_id = '" . (int)$languages_id . "' and p2c.categories_id = '" . (int)$current_category_id . "'"; 
      } // end else { // either retail...
// EOF Separate Pricing per Customer
      }
    }

    if ( (!isset($_GET['sort'])) || (!ereg('[1-8][ad]', $_GET['sort'])) || (substr($_GET['sort'], 0, 1) > sizeof($column_list)) ) {
      for ($i=0, $n=sizeof($column_list); $i<$n; $i++) {
        if ($column_list[$i] == 'PRODUCT_LIST_NAME') {
          $_GET['sort'] = $i+1 . 'a';
          $listing_sql .= " order by pd.products_name";
          break;
        }
      }
    } else {
      $sort_col = substr($_GET['sort'], 0 , 1);
      $sort_order = substr($_GET['sort'], 1);
      $listing_sql .= ' order by ';
      switch ($column_list[$sort_col-1]) {
        case 'PRODUCT_LIST_MODEL':
          $listing_sql .= "p.products_model " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
        case 'PRODUCT_LIST_NAME':
          $listing_sql .= "pd.products_name " . ($sort_order == 'd' ? 'desc' : '');
          break;
        case 'PRODUCT_LIST_MANUFACTURER':
          $listing_sql .= "m.manufacturers_name " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
        case 'PRODUCT_LIST_QUANTITY':
          $listing_sql .= "p.products_quantity " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
        case 'PRODUCT_LIST_IMAGE':
          $listing_sql .= "pd.products_name";
          break;
        case 'PRODUCT_LIST_WEIGHT':
          $listing_sql .= "p.products_weight " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
        case 'PRODUCT_LIST_PRICE':
          $listing_sql .= "final_price " . ($sort_order == 'd' ? 'desc' : '') . ", pd.products_name";
          break;
      }
    }
/*** Begin Header Tags SEO ***/
    if (isset($_GET['manufacturers_id']))
      $db_query = tep_db_query("select manufacturers_htc_title_tag as htc_title, manufacturers_htc_description as htc_description from " . TABLE_MANUFACTURERS_INFO . " where languages_id = '" . (int)$languages_id . "' and manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'");
    else
      $db_query = tep_db_query("select categories_htc_title_tag as htc_title, categories_htc_description as htc_description from " . TABLE_CATEGORIES_DESCRIPTION . " where categories_id = '" . (int)$current_category_id . "' and language_id = '" . (int)$languages_id . "'");

    $htc = tep_db_fetch_array($db_query);
    ?>
    
    	<div id="tavolaprincipale">
      
      <div id="tavolaprova" >
           
    	        
            
            <div id="headerone"><h2><?php echo $htc['htc_title']; ?></h2></div>
            
            <div id="greenstripe"></div>
            	  
     
     
            	
        
       
      
        
       	
    <?php /*** End Header Tags SEO ***/ ?>



          <?php /*** Begin Header Tags SEO ***/// Get the right image for the top-right
    $image = DIR_WS_IMAGES . 'table_background_list.gif';
    if (isset($_GET['manufacturers_id'])) {
      $image = tep_db_query("select manufacturers_image from " . TABLE_MANUFACTURERS . " where manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "'");
      $image = tep_db_fetch_array($image);
      $image = $image['manufacturers_image'];
    } elseif ($current_category_id) {
      $image = tep_db_query("select categories_image from " . TABLE_CATEGORIES . " where categories_id = '" . (int)$current_category_id . "'");
      $image = tep_db_fetch_array($image);
      $image = $image['categories_image'];
    } ?>
           
          
          <?php if (tep_not_null($htc['htc_description'])) { ?>
         
        
           <span id="headertwo">
             <div id="input6"> <form name="quick_find" action="http://www.sportevo.pro/advanced_search_result.php" method="get">
								<input type="hidden" name="search_in_description" value="1">
								<button type="submit" class="input6"><i class="fa fa-search fa-2x"></i>
								</button><input class="input5" type="text"  placeholder=" SEARCH" name="keywords" >
								
							</form> </div>
			
          <div id="infoboxholder">
          <div id="imgcat"><?php if (isset($_GET['manufacturers_id'])) echo '<div id="imgcont">' . tep_image(DIR_WS_IMAGES . $image, $category['categories_htc_title_tag'] . '</div>'); ?></div></div>
          <h2><?php echo $htc['htc_title']; ?></h2> </br>
           <div  class="infoBoxContents"><?php if (isset($_GET['manufacturers_id'])) echo $htc['htc_description']; ?></div>
        </div>
        
        </span>
          <?php } 
          /*** End Header Tags SEO ***/ 
          ?>
          
    <?php
// optional Product List Filter
    if (PRODUCT_LIST_FILTER > 0) {
      if (isset($_GET['manufacturers_id'])) {
        $filterlist_sql = "select distinct c.categories_id as id, cd.categories_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where p.products_status = '1' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and p2c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and p.manufacturers_id = '" . (int)$_GET['manufacturers_id'] . "' order by cd.categories_name";
      } else {
        $filterlist_sql= "select distinct m.manufacturers_id as id, m.manufacturers_name as name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c, " . TABLE_MANUFACTURERS . " m where p.products_status = '1' and p.manufacturers_id = m.manufacturers_id and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$current_category_id . "' order by m.manufacturers_name";
      }
      $filterlist_query = tep_db_query($filterlist_sql);
      if (tep_db_num_rows($filterlist_query) > 1) {
        echo '            <div align="center" class="main">' . tep_draw_form('filter', tep_href_link( FILENAME_DEFAULT ), 'get') . '&nbsp;';
        if (isset($_GET['manufacturers_id'])) {
          echo tep_draw_hidden_field('manufacturers_id', $_GET['manufacturers_id']);
          $options = array(array('id' => '', 'text' => TEXT_ALL_CATEGORIES));
        } else {
          echo tep_draw_hidden_field('cPath', $cPath);
          $options = array(array('id' => '', 'text' => TEXT_ALL_MANUFACTURERS));
        }
        echo tep_draw_hidden_field('sort', $_GET['sort']);
        while ($filterlist = tep_db_fetch_array($filterlist_query)) {
          $options[] = array('id' => $filterlist['id'], 'text' => $filterlist['name']);
        }
        echo tep_draw_pull_down_menu('filter_id', $options, (isset($_GET['filter_id']) ? $_GET['filter_id'] : ''), 'onchange="this.form.submit()"');
        echo '</form></div>' . "\n";
      }
    }


?> 
   
          <!--- END Header Tags SEO Social Bookmarks -->  
 
    <?php
// Start Product Specifications
      // Check the number of products is above the minimum for the comparison table
      $check_query_raw = "select distinct p2c.products_id
                           from " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c,
                                " . TABLE_SPECIFICATION_GROUPS . " sg,
                                " . TABLE_SPECIFICATIONS_TO_CATEGORIES . " sg2c
                           where sg.show_comparison = 'True'
                             and sg.specification_group_id = sg2c.specification_group_id
                             and p2c.categories_id = sg2c.categories_id 
                             and sg2c.categories_id = '" . (int) $current_category_id . "'
                         ";
      // print $check_query_raw . "<br>\n";
      $check_query = tep_db_query ($check_query_raw);
      $show_comparison = SPECIFICATIONS_MINIMUM_COMPARISON <= tep_db_num_rows ($check_query);

      if (SPECIFICATIONS_BOX_COMP_INDEX == 'False' && SPECIFICATIONS_COMP_LINK == 'True' && $current_category_id != 0 && $show_comparison == true && tep_has_spec_group ($current_category_id, 'show_comparison') == true) {
        echo '                <td align="center"><a href="' . tep_href_link (FILENAME_COMPARISON, 'cPath=' . $cPath) . '">' . tep_image_button ('button_products_comparison.gif', TEXT_BUTTON_COMPARISON) . '</a></td>' . "\n";
      } // if (SPECIFICATIONS_BOX_COMP_INDEX
// End Product Specifications
?>

<div id="preelecont">
			<div id="preelefilt">
<i><img class="breaker" src="http://www.sportevo.pro/images/threew.png"></i> 
<?php
// Start Products Specifications
  if (SPECIFICATIONS_FILTERS_MODULE == 'True') {
?>
  
<?php
    require (DIR_WS_MODULES . 'products_filter.php');
?>
<?php
  }
// End Products Specifications
?>
</div>



</div>

<div id="newprodscapsule">



	<?php include(DIR_WS_MODULES . FILENAME_PRODUCT_LISTING); ?>

<?php
// Product Specifications
    } // if ($category_depth == 'products' ... else ...
  } else { // default page
?>
</div>
<div id="preeleconthome">
			<div id="preelefilt">
            	
      	
            	
  <?php
// Start Products Specifications
 // if (SPECIFICATIONS_FILTERS_MODULE == 'True') {
?>
      
      
<?php
   // require (DIR_WS_MODULES . 'products_filter.php');
?>
       
<?php
//  }
// End Products Specifications
?>  
</div>   
           </div>
<?php 
// Start Categories Images box
    if (CATEGORIES_IMAGES_BOX == 'True') {
?>
       
<div id="newprodscapsule">
                      <div class="imageboxbox"><?php include(DIR_WS_MODULES . FILENAME_CATEGORIES_IMAGES); ?></div>
        </div>
<?php 
    } // if (CATEGORIES_IMAGES_BOX
// End Categories Images box
?>
     
       <span class="infoBoxHeading">selected by Sportevo</span>
        <?php include(DIR_WS_MODULES . FILENAME_FEATUREDB); ?>
     <br>
					
						
         
         
          <?php /*--- Beginning of Addition Products Cycle Slideshow ---*/ ?>
        
       
          
<?php /*--- End of Addition Products Cycle Slideshow ---*/ ?>
          
          
        
         
<?php
    include(DIR_WS_MODULES . FILENAME_UPCOMING_PRODUCTS);
?>
      
<?php
  }
?>
<!-- body_text_eof //-->
    
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
   
</div>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>

</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
