<?php
/*
  $Id: categories.php,v 1.146 2003/07/11 14:40:27 hpdl Exp $
  adapted for Optimize Categories Box v1.2  2007/09/02

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
 // PRODUCT ATTRIBUTES CONTRIB
  if (file_exists(DIR_WS_LANGUAGES . $language . '/' . FILENAME_PRODUCTS_ATTRIBUTES)) {
    include(DIR_WS_LANGUAGES . $language . '/' . FILENAME_PRODUCTS_ATTRIBUTES);
  }
  
// PRODUCT ATTRIBUTES CONTRIB 

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();
  
  // Start Products Specifications
  require_once (DIR_WS_CLASSES . 'boxes.php');
  require_once (DIR_WS_FUNCTIONS . 'products_specifications.php');
// End Products Specifications
  
// include CountProductsStore object for use on the admin side
  require_once(DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'cache.php');
  require(DIR_FS_CATALOG. DIR_WS_CLASSES . 'CountProductsStore.php');
  $countproducts = new CountProductsStore();

  $action = (isset($_GET['action']) ? $_GET['action'] : '');
   // begin bundled products
  function bundle_avoid($bundle_id) { // returns an array of bundle_ids containing the specified bundle
    $avoid_list = array();
    $check_query = tep_db_query('select bundle_id from ' . TABLE_PRODUCTS_BUNDLES . ' where subproduct_id = ' . (int)$bundle_id);
    while ($check = tep_db_fetch_array($check_query)) {
      $avoid_list[] = $check['bundle_id'];
      $tmp = bundle_avoid($check['bundle_id']);
      $avoid_list = array_merge($avoid_list, $tmp);
    }
    return $avoid_list;
  }
  // end bundled products

  

// BOF: KategorienAdmin / OLISWISS
  	$admin_access_query = tep_db_query("select admin_groups_id, admin_cat_access, admin_right_access from " . TABLE_ADMIN . " where admin_id=" . $login_id);
	$admin_access_array = tep_db_fetch_array($admin_access_query);
	$admin_groups_id = $admin_access_array['admin_groups_id'];
	$admin_cat_access = $admin_access_array['admin_cat_access'];
	$admin_cat_access_array_cats = explode(",",$admin_cat_access);
	$admin_right_access = $admin_access_array['admin_right_access'];
// EOF: KategorienAdmin / OLISWISS


// Ultimate SEO URLs v2.1
// If the action will affect the cache entries
    if ( preg_match("(insert|update|setflag)", $action) ) include_once('includes/reset_seo_cache.php');

  if (tep_not_null($action)) {
    switch ($action) {
      case 'setflag':
        if ( ($_GET['flag'] == '0') || ($_GET['flag'] == '1') ) {
          if (isset($_GET['pID'])) {
            tep_set_product_status($_GET['pID'], $_GET['flag']);
          }

          if (USE_CACHE == 'true') {
            tep_reset_cache_block('categories');
            tep_reset_cache_block('also_purchased');
          }
		   if (USE_PRODUCTS_COUNT_CACHE == 'true') {
            tep_reset_cache_block('products_count');
        }

        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $_GET['cPath'] . '&pID=' . $_GET['pID']));
        break;
      case 'insert_category':
      case 'update_category':
        if (isset($_POST['categories_id'])) $categories_id = tep_db_prepare_input($_POST['categories_id']);
        $sort_order = tep_db_prepare_input($_POST['sort_order']);

        $sql_data_array = array('sort_order' => $sort_order);

        if ($action == 'insert_category') {
          $insert_sql_data = array('parent_id' => $current_category_id,
                                   'date_added' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

          tep_db_perform(TABLE_CATEGORIES, $sql_data_array);

          $categories_id = tep_db_insert_id();
		  // BOF: KategorienAdmin / OLI
	if (in_array("ALL",$admin_cat_access_array_cats)== false) {
	  array_push($admin_cat_access_array_cats,$categories_id);
	  $admin_cat_access = implode(",",$admin_cat_access_array_cats);
          $sql_data_array = array('admin_cat_access' => tep_db_prepare_input($admin_cat_access));
          tep_db_perform(TABLE_ADMIN, $sql_data_array, 'update', 'admin_id = \'' . $login_id . '\'');
        }
// EOF: KategorienAdmin / OLI 
        } elseif ($action == 'update_category') {
          $update_sql_data = array('last_modified' => 'now()');

          $sql_data_array = array_merge($sql_data_array, $update_sql_data);

          tep_db_perform(TABLE_CATEGORIES, $sql_data_array, 'update', "categories_id = '" . (int)$categories_id . "'");
        }

        $languages = tep_get_languages();
        for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
          $categories_name_array = $_POST['categories_name'];
/*** Begin Header Tags SEO ***/
          $categories_htc_title_array = $_POST['categories_htc_title_tag'];
          $categories_htc_desc_array = $_POST['categories_htc_desc_tag'];
          $categories_htc_keywords_array = $_POST['categories_htc_keywords_tag'];
          $categories_htc_description_array = $_POST['categories_htc_description'];
          /*** End Header Tags SEO ***/
          $language_id = $languages[$i]['id'];

          /*** Begin Header Tags SEO ***/
      $sql_data_array = array('categories_name' => tep_db_prepare_input($categories_name_array[$language_id]),
           'categories_htc_title_tag' => (tep_not_null($categories_htc_title_array[$language_id]) ? tep_db_prepare_input($categories_htc_title_array[$language_id]) :  tep_db_prepare_input($categories_name_array[$language_id])),
           'categories_htc_desc_tag' => (tep_not_null($categories_htc_desc_array[$language_id]) ? tep_db_prepare_input($categories_htc_desc_array[$language_id]) :  tep_db_prepare_input($categories_name_array[$language_id])),
           'categories_htc_keywords_tag' => (tep_not_null($categories_htc_keywords_array[$language_id]) ? tep_db_prepare_input($categories_htc_keywords_array[$language_id]) :  tep_db_prepare_input($categories_name_array[$language_id])),
           'categories_htc_description' => tep_db_prepare_input($categories_htc_description_array[$language_id]));
      /*** End Header Tags SEO ***/


          if ($action == 'insert_category') {
            $insert_sql_data = array('categories_id' => $categories_id,
                                     'language_id' => $languages[$i]['id']);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array);
          } elseif ($action == 'update_category') {
            tep_db_perform(TABLE_CATEGORIES_DESCRIPTION, $sql_data_array, 'update', "categories_id = '" . (int)$categories_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
          }
        }

        if ($categories_image = new upload('categories_image', DIR_FS_CATALOG_IMAGES)) {
          tep_db_query("update " . TABLE_CATEGORIES . " set categories_image = '" . tep_db_input($categories_image->filename) . "' where categories_id = '" . (int)$categories_id . "'");
        }

        if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }
 if (USE_PRODUCTS_COUNT_CACHE == 'true') {
            tep_reset_cache_block('products_count');
        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories_id));
        break;
 case 'delete_category_confirm':
        if (isset($_POST['categories_id'])) {
          $categories_id = tep_db_prepare_input($_POST['categories_id']);
// BOF: KategorienAdmin / OLI 
        //$categories = tep_get_category_tree($categories_id, '', '0', '',true);
          $categories = tep_get_category_tree($categories_id, '', '0', '', '',true);
// EOF: KategorienAdmin / OLI 
          $products = array();
          $products_delete = array();

          for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
            $product_ids_query = tep_db_query("select products_id from " . TABLE_PRODUCTS_TO_CATEGORIES . " where categories_id = '" . (int)$categories[$i]['id'] . "'");

            while ($product_ids = tep_db_fetch_array($product_ids_query)) {
              $products[$product_ids['products_id']]['categories'][] = $categories[$i]['id'];
            }
          }

          reset($products);
          while (list($key, $value) = each($products)) {
            $category_ids = '';

            for ($i=0, $n=sizeof($value['categories']); $i<$n; $i++) {
              $category_ids .= "'" . (int)$value['categories'][$i] . "', ";
            }
            $category_ids = substr($category_ids, 0, -2);

            $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$key . "' and categories_id not in (" . $category_ids . ")");
            $check = tep_db_fetch_array($check_query);
            if ($check['total'] < '1') {
              $products_delete[$key] = $key;
            }
          }

// removing categories can be a lengthy process
          tep_set_time_limit(0);
          for ($i=0, $n=sizeof($categories); $i<$n; $i++) {
            tep_remove_category($categories[$i]['id']);
          }

          reset($products_delete);
          while (list($key) = each($products_delete)) {
            tep_remove_product($key);
          }
        }

        if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }
 if (USE_PRODUCTS_COUNT_CACHE == 'true') {
            tep_reset_cache_block('products_count');
        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath));
        break;
      case 'delete_product_confirm':
        if (isset($_POST['products_id']) && isset($_POST['product_categories']) && is_array($_POST['product_categories'])) {
          $product_id = tep_db_prepare_input($_POST['products_id']);
          $product_categories = $_POST['product_categories'];

          for ($i=0, $n=sizeof($product_categories); $i<$n; $i++) {
            tep_db_query("delete from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_id . "' and categories_id = '" . (int)$product_categories[$i] . "'");
			            // BOF Separate Pricing Per Customer
            tep_db_query("delete from " . TABLE_PRODUCTS_GROUPS . " where products_id = '" . tep_db_input($product_id) . "' ");
            // EOF Separate Pricing Per Customer

          }

          $product_categories_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$product_id . "'");
          $product_categories = tep_db_fetch_array($product_categories_query);

          if ($product_categories['total'] == '0') {
            tep_remove_product($product_id);
          }
        }

// Start Products Specifications
        tep_db_query ("delete from " . TABLE_PRODUCTS_SPECIFICATIONS . " 
                       where products_id = '" . (int) $product_id . "'
                    ");
// End Products Specifications


        if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }
 if (USE_PRODUCTS_COUNT_CACHE == 'true') {
            tep_reset_cache_block('products_count');
        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath));
        break;
      case 'move_category_confirm':
        if (isset($_POST['categories_id']) && ($_POST['categories_id'] != $_POST['move_to_category_id'])) {
          $categories_id = tep_db_prepare_input($_POST['categories_id']);
          $new_parent_id = tep_db_prepare_input($_POST['move_to_category_id']);

          $path = explode('_', tep_get_generated_category_path_ids($new_parent_id));

          if (in_array($categories_id, $path)) {
            $messageStack->add_session(ERROR_CANNOT_MOVE_CATEGORY_TO_PARENT, 'error');

            tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories_id));
          } else {
            tep_db_query("update " . TABLE_CATEGORIES . " set parent_id = '" . (int)$new_parent_id . "', last_modified = now() where categories_id = '" . (int)$categories_id . "'");

            if (USE_CACHE == 'true') {
              tep_reset_cache_block('categories');
              tep_reset_cache_block('also_purchased');
            }
 if (USE_PRODUCTS_COUNT_CACHE == 'true') {
            tep_reset_cache_block('products_count');
        }

            tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $new_parent_id . '&cID=' . $categories_id));
          }
        }

        break;
      case 'move_product_confirm':
        $products_id = tep_db_prepare_input($_POST['products_id']);
        $new_parent_id = tep_db_prepare_input($_POST['move_to_category_id']);

        $duplicate_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "' and categories_id = '" . (int)$new_parent_id . "'");
        $duplicate_check = tep_db_fetch_array($duplicate_check_query);
        if ($duplicate_check['total'] < 1) tep_db_query("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set categories_id = '" . (int)$new_parent_id . "' where products_id = '" . (int)$products_id . "' and categories_id = '" . (int)$current_category_id . "'");

        if (USE_CACHE == 'true') {
          tep_reset_cache_block('categories');
          tep_reset_cache_block('also_purchased');
        }
 if (USE_PRODUCTS_COUNT_CACHE == 'true') {
            tep_reset_cache_block('products_count');
        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $new_parent_id . '&pID=' . $products_id));
        break;
      case 'insert_product':
      case 'update_product':
        if (isset($_POST['edit_x']) || isset($_POST['edit_y'])) {
          $action = 'new_product';
        } else {
			//BOF UltraPics
// BOF: MaxiDVD Added ULTRA IMAGES
            $image_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image='" . $_POST['products_previous_image'] . "'");
            $image_count = tep_db_fetch_array($image_count_query);
            if (($_POST['delete_image'] == 'yes') && ($image_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image']);
            }
            $image_med_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image_med='" . $_POST['products_previous_image_med'] . "'");
            $image_med_count = tep_db_fetch_array($image_med_count_query);
            if (($_POST['delete_image_med'] == 'yes') && ($image_med_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image_med']);
            }
            $image_lrg_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image_lrg='" . $_POST['products_previous_image_lrg'] . "'");
            $image_lrg_count = tep_db_fetch_array($image_lrg_count_query);
            if (($_POST['delete_image_lrg'] == 'yes') && ($image_lrg_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image_lrg']);
            }
// MaxiDVD Added ULTRA Image SM - LG 1
            $image_sm_1_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image_sm_1='" . $_POST['products_previous_image_sm_1'] . "'");
            $image_sm_1_count = tep_db_fetch_array($image_sm_1_count_query);
            if (($_POST['delete_image_sm_1'] == 'yes') && ($image_sm_1_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image_sm_1']);
            }
            $image_xl_1_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image_xl_1='" . $_POST['products_previous_image_xl_1'] . "'");
            $image_xl_1_count = tep_db_fetch_array($image_xl_1_count_query);
            if (($_POST['delete_image_xl_1'] == 'yes') && ($image_xl_1_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image_xl_1']);
            }
// MaxiDVD Added ULTRA Image SM - LG 2
            $image_sm_2_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image_sm_2='" . $_POST['products_previous_image_sm_2'] . "'");
            $image_sm_2_count = tep_db_fetch_array($image_sm_2_count_query);
            if (($_POST['delete_image_sm_2'] == 'yes') && ($image_sm_1_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image_sm_2']);
            }
            $image_xl_2_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image_xl_2='" . $_POST['products_previous_image_xl_2'] . "'");
            $image_xl_2_count = tep_db_fetch_array($image_xl_2_count_query);
            if (($_POST['delete_image_xl_2'] == 'yes') && ($image_xl_1_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image_xl_2']);
            }
// MaxiDVD Added ULTRA Image SM - LG 3
            $image_sm_3_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image_sm_3='" . $_POST['products_previous_image_sm_3'] . "'");
            $image_sm_3_count = tep_db_fetch_array($image_sm_3_count_query);
            if (($_POST['delete_image_sm_3'] == 'yes') && ($image_sm_1_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image_sm_3']);
            }
            $image_xl_3_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image_xl_3='" . $_POST['products_previous_image_xl_3'] . "'");
            $image_xl_3_count = tep_db_fetch_array($image_xl_3_count_query);
            if (($_POST['delete_image_xl_3'] == 'yes') && ($image_xl_1_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image_xl_3']);
            }
// MaxiDVD Added ULTRA Image SM - LG 4
            $image_sm_4_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image_sm_4='" . $_POST['products_previous_image_sm_4'] . "'");
            $image_sm_4_count = tep_db_fetch_array($image_sm_4_count_query);
            if (($_POST['delete_image_sm_4'] == 'yes') && ($image_sm_1_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image_sm_4']);
            }
            $image_xl_4_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image_xl_4='" . $_POST['products_previous_image_xl_4'] . "'");
            $image_xl_4_count = tep_db_fetch_array($image_xl_4_count_query);
            if (($_POST['delete_image_xl_4'] == 'yes') && ($image_xl_1_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image_xl_4']);
            }
// MaxiDVD Added ULTRA Image SM - LG 5
            $image_sm_5_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image_sm_5='" . $_POST['products_previous_image_sm_5'] . "'");
            $image_sm_5_count = tep_db_fetch_array($image_sm_5_count_query);
            if (($_POST['delete_image_sm_5'] == 'yes') && ($image_sm_1_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image_sm_5']);
            }
            $image_xl_5_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image_xl_5='" . $_POST['products_previous_image_xl_5'] . "'");
            $image_xl_5_count = tep_db_fetch_array($image_xl_5_count_query);
            if (($_POST['delete_image_xl_5'] == 'yes') && ($image_xl_1_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image_xl_5']);
            }
// MaxiDVD Added ULTRA Image SM - LG 6
            $image_sm_6_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image_sm_6='" . $_POST['products_previous_image_sm_6'] . "'");
            $image_sm_6_count = tep_db_fetch_array($image_sm_6_count_query);
            if (($_POST['delete_image_sm_6'] == 'yes') && ($image_sm_1_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image_sm_6']);
            }
            $image_xl_6_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image_xl_6='" . $_POST['products_previous_image_xl_6'] . "'");
            $image_xl_6_count = tep_db_fetch_array($image_xl_6_count_query);
            if (($_POST['delete_image_xl_6'] == 'yes') && ($image_xl_1_count['total']<= '1')) {
                unlink(DIR_FS_CATALOG_IMAGES . $_POST['products_previous_image_xl_6']);
            }
// EOF: MaxiDVD Added ULTRA IMAGES
//EOF UltraPics
          if (isset($_GET['pID'])) $products_id = tep_db_prepare_input($_GET['pID']);
          $products_date_available = tep_db_prepare_input($_POST['products_date_available']);

          $products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';

          $sql_data_array = array('products_quantity' => tep_db_prepare_input($_POST['products_quantity']),
								'products_bundle' => ($_POST['products_bundle'] == 'yes' ? 'yes' : 'no'),
                                  'sold_in_bundle_only' => ($_POST['sold_in_bundle_only'] == 'yes' ? 'yes' : 'no'),
                                  'products_model' => tep_db_prepare_input($_POST['products_model']),
                                 //MVS start
                                  'vendors_prod_id' => tep_db_prepare_input($_POST['vendors_prod_id']),
                                  'vendors_product_price' => tep_db_prepare_input($_POST['vendors_product_price']),
                                  'vendors_id' => tep_db_prepare_input($_POST['vendors_id']),
                                  'vendors_prod_comments' => tep_db_prepare_input($_POST['vendors_prod_comments']),
//MVS end
								  'products_price' => tep_db_prepare_input($_POST['products_price']),
                                 'products_cost' => tep_db_prepare_input($_POST['products_cost']),

                                  'products_date_available' => $products_date_available,
                                  'products_weight' => tep_db_prepare_input($_POST['products_weight']),
                                  'products_status' => tep_db_prepare_input($_POST['products_status']),
                                  'products_listing_status' => tep_db_prepare_input($_POST['products_listing_status']),
                                'products_master_status' => tep_db_prepare_input($_POST['products_master_status']),
                                  'products_tax_class_id' => tep_db_prepare_input($_POST['products_tax_class_id']),
                                  'manufacturers_id' => tep_db_prepare_input($_POST['manufacturers_id']),
		  							'products_master' => tep_db_prepare_input($_POST['products_master']));
		  // Master Products
                                
                            
// controll of assign master products
        if ($sql_data_array['products_master_status'] == "1") {
            $sql_data_array['products_master'] = "0"; // redefine
        }
// Master Products EOF
		  
		  
		  //BOF UltraPics
					if (($_POST['unlink_image'] == 'yes') or ($_POST['delete_image'] == 'yes')) {
						$sql_data_array['products_image'] = '';
					} else {
//EOF UltraPics


          if (isset($_POST['products_image']) && tep_not_null($_POST['products_image']) && ($_POST['products_image'] != 'none')) {
            $sql_data_array['products_image'] = tep_db_prepare_input($_POST['products_image']);
          }
//BOF UltraPics
          }
       if (($_POST['unlink_image_med'] == 'yes') or ($_POST['delete_image_med'] == 'yes')) {
            $sql_data_array['products_image_med'] = '';
           } else {
          if (isset($_POST['products_image_med']) && tep_not_null($_POST['products_image_med']) && ($_POST['products_image_med'] != 'none')) {
            $sql_data_array['products_image_med'] = tep_db_prepare_input($_POST['products_image_med']);
          }
          }
       if (($_POST['unlink_image_lrg'] == 'yes') or ($_POST['delete_image_lrg'] == 'yes')) {
            $sql_data_array['products_image_lrg'] = '';
           } else {
          if (isset($_POST['products_image_lrg']) && tep_not_null($_POST['products_image_lrg']) && ($_POST['products_image_lrg'] != 'none')) {
            $sql_data_array['products_image_lrg'] = tep_db_prepare_input($_POST['products_image_lrg']);
          }
          }
       if (($_POST['unlink_image_sm_1'] == 'yes') or ($_POST['delete_image_sm_1'] == 'yes')) {
            $sql_data_array['products_image_sm_1'] = '';
           } else {
          if (isset($_POST['products_image_sm_1']) && tep_not_null($_POST['products_image_sm_1']) && ($_POST['products_image_sm_1'] != 'none')) {
            $sql_data_array['products_image_sm_1'] = tep_db_prepare_input($_POST['products_image_sm_1']);
          }
          }
       if (($_POST['unlink_image_xl_1'] == 'yes') or ($_POST['delete_image_xl_1'] == 'yes')) {
            $sql_data_array['products_image_xl_1'] = '';
           } else {
          if (isset($_POST['products_image_xl_1']) && tep_not_null($_POST['products_image_xl_1']) && ($_POST['products_image_xl_1'] != 'none')) {
            $sql_data_array['products_image_xl_1'] = tep_db_prepare_input($_POST['products_image_xl_1']);
          }
          }
       if (($_POST['unlink_image_sm_2'] == 'yes') or ($_POST['delete_image_sm_2'] == 'yes')) {
            $sql_data_array['products_image_sm_2'] = '';
           } else {
          if (isset($_POST['products_image_sm_2']) && tep_not_null($_POST['products_image_sm_2']) && ($_POST['products_image_sm_2'] != 'none')) {
            $sql_data_array['products_image_sm_2'] = tep_db_prepare_input($_POST['products_image_sm_2']);
          }
          }
       if (($_POST['unlink_image_xl_2'] == 'yes') or ($_POST['delete_image_xl_2'] == 'yes')) {
            $sql_data_array['products_image_xl_2'] = '';
           } else {
          if (isset($_POST['products_image_xl_2']) && tep_not_null($_POST['products_image_xl_2']) && ($_POST['products_image_xl_2'] != 'none')) {
            $sql_data_array['products_image_xl_2'] = tep_db_prepare_input($_POST['products_image_xl_2']);
          }
          }
       if (($_POST['unlink_image_sm_3'] == 'yes') or ($_POST['delete_image_sm_3'] == 'yes')) {
            $sql_data_array['products_image_sm_3'] = '';
           } else {
          if (isset($_POST['products_image_sm_3']) && tep_not_null($_POST['products_image_sm_3']) && ($_POST['products_image_sm_3'] != 'none')) {
            $sql_data_array['products_image_sm_3'] = tep_db_prepare_input($_POST['products_image_sm_3']);
          }
          }
       if (($_POST['unlink_image_xl_3'] == 'yes') or ($_POST['delete_image_xl_3'] == 'yes')) {
            $sql_data_array['products_image_xl_3'] = '';
           } else {
          if (isset($_POST['products_image_xl_3']) && tep_not_null($_POST['products_image_xl_3']) && ($_POST['products_image_xl_3'] != 'none')) {
            $sql_data_array['products_image_xl_3'] = tep_db_prepare_input($_POST['products_image_xl_3']);
          }
          }
       if (($_POST['unlink_image_sm_4'] == 'yes') or ($_POST['delete_image_sm_4'] == 'yes')) {
            $sql_data_array['products_image_sm_4'] = '';
           } else {
          if (isset($_POST['products_image_sm_4']) && tep_not_null($_POST['products_image_sm_4']) && ($_POST['products_image_sm_4'] != 'none')) {
            $sql_data_array['products_image_sm_4'] = tep_db_prepare_input($_POST['products_image_sm_4']);
          }
          }
       if (($_POST['unlink_image_xl_4'] == 'yes') or ($_POST['delete_image_xl_4'] == 'yes')) {
            $sql_data_array['products_image_xl_4'] = '';
           } else {
          if (isset($_POST['products_image_xl_4']) && tep_not_null($_POST['products_image_xl_4']) && ($_POST['products_image_xl_4'] != 'none')) {
            $sql_data_array['products_image_xl_4'] = tep_db_prepare_input($_POST['products_image_xl_4']);
          }
          }
       if (($_POST['unlink_image_sm_5'] == 'yes') or ($_POST['delete_image_sm_5'] == 'yes')) {
            $sql_data_array['products_image_sm_5'] = '';
           } else {
          if (isset($_POST['products_image_sm_5']) && tep_not_null($_POST['products_image_sm_5']) && ($_POST['products_image_sm_5'] != 'none')) {
            $sql_data_array['products_image_sm_5'] = tep_db_prepare_input($_POST['products_image_sm_5']);
          }
          }
       if (($_POST['unlink_image_xl_5'] == 'yes') or ($_POST['delete_image_xl_5'] == 'yes')) {
            $sql_data_array['products_image_xl_5'] = '';
           } else {
          if (isset($_POST['products_image_xl_5']) && tep_not_null($_POST['products_image_xl_5']) && ($_POST['products_image_xl_5'] != 'none')) {
            $sql_data_array['products_image_xl_5'] = tep_db_prepare_input($_POST['products_image_xl_5']);
          }
          }
       if (($_POST['unlink_image_sm_6'] == 'yes') or ($_POST['delete_image_sm_6'] == 'yes')) {
            $sql_data_array['products_image_sm_6'] = '';
           } else {
          if (isset($_POST['products_image_sm_6']) && tep_not_null($_POST['products_image_sm_6']) && ($_POST['products_image_sm_6'] != 'none')) {
            $sql_data_array['products_image_sm_6'] = tep_db_prepare_input($_POST['products_image_sm_6']);
          }
          }
       if (($_POST['unlink_image_xl_6'] == 'yes') or ($_POST['delete_image_xl_6'] == 'yes')) {
            $sql_data_array['products_image_xl_6'] = '';
           } else {
          if (isset($_POST['products_image_xl_6']) && tep_not_null($_POST['products_image_xl_6']) && ($_POST['products_image_xl_6'] != 'none')) {
            $sql_data_array['products_image_xl_6'] = tep_db_prepare_input($_POST['products_image_xl_6']);
          }
          }
//EOF UltraPics



          if ($action == 'insert_product') {
            $insert_sql_data = array('products_date_added' => 'now()');

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

            tep_db_perform(TABLE_PRODUCTS, $sql_data_array);
            $products_id = tep_db_insert_id();

            tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$products_id . "', '" . (int)$current_category_id . "')");
          } elseif ($action == 'update_product') {
            $update_sql_data = array('products_last_modified' => 'now()');

            $sql_data_array = array_merge($sql_data_array, $update_sql_data);

            tep_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");
          }
		  // BOF Bundled Products
          if ($_POST['products_bundle'] == "yes") {
            $to_avoid = bundle_avoid($products_id);
            $subprods = array();
            $subprodqty = array();
            tep_db_query("DELETE FROM " . TABLE_PRODUCTS_BUNDLES . " WHERE bundle_id = '" . (int)$products_id . "'");
            for ($i=0, $n=100; $i<$n; $i++) {
              if (isset($_POST['subproduct_' . $i . '_qty']) && ((int)$_POST['subproduct_' . $i . '_qty'] > 0) && !in_array($_POST['subproduct_' . $i . '_id'], $to_avoid)) {
                if (in_array($_POST['subproduct_' . $i . '_id'], $subprods)) {
                  $subprodqty[$_POST['subproduct_' . $i . '_id']] += (int)$_POST['subproduct_' . $i . '_qty'];
                  tep_db_query('update ' . TABLE_PRODUCTS_BUNDLES . ' set subproduct_qty = ' . (int)$subprodqty[$_POST['subproduct_' . $i . '_id']] . ' where bundle_id = ' . (int)$products_id . ' and subproduct_id = ' . (int)$_POST['subproduct_' . $i . '_id']);
                } else {
                  $subprods[] = $_POST['subproduct_' . $i . '_id'];
                  $subprodqty[$_POST['subproduct_' . $i . '_id']] = (int)$_POST['subproduct_' . $i . '_qty'];
                  tep_db_query("INSERT INTO " . TABLE_PRODUCTS_BUNDLES . " (bundle_id, subproduct_id, subproduct_qty) VALUES ('" . (int)$products_id . "', '" . (int)$_POST['subproduct_' . $i . '_id'] . "', '" . (int)$_POST['subproduct_' . $i . '_qty'] . "')");
                }
       	      }
            }
            if (empty($subprods)) { // not a bundle if no subproducts set
              tep_db_query('update ' . TABLE_PRODUCTS . ' set products_bundle = "no" where products_id = ' . (int)$products_id);
            } else { // calculate total MSRP and weight from subproducts
              
              $weight = 0;
              foreach ($subprodqty as $id => $qty) {
                $subprod_query = tep_db_query('select  products_weight from ' . TABLE_PRODUCTS . ' where products_id = ' . (int)$id);
                $subprod = tep_db_fetch_array($subprod_query);
                
                $weight += ($subprod['products_weight'] * $qty);
              }
              tep_db_query('update ' . TABLE_PRODUCTS . ' set products_quantity = 1,  products_weight = "' . tep_db_input($weight) . '" where products_id = ' . (int)$products_id);
            }
          }
          // EOF Bundled Products

		  /** AJAX Attribute Manager  **/ 
  require_once('attributeManager/includes/attributeManagerUpdateAtomic.inc.php'); 
/** AJAX Attribute Manager  end **/


// BOF Separate Pricing Per Customer
 $customers_group_query = tep_db_query("select customers_group_id, customers_group_name from " . TABLE_CUSTOMERS_GROUPS . " where customers_group_id != '0' order by customers_group_id");
while ($customers_group = tep_db_fetch_array($customers_group_query)) // Gets all of the customers groups
  {
  $attributes_query = tep_db_query("select customers_group_id, customers_group_price from " . TABLE_PRODUCTS_GROUPS . " where ((products_id = '" . $products_id . "') && (customers_group_id = " . $customers_group['customers_group_id'] . ")) order by customers_group_id");
  $attributes = tep_db_fetch_array($attributes_query);
  if (tep_db_num_rows($attributes_query) > 0) {
    if ($_POST['sppcoption'][$customers_group['customers_group_id']]) { // this is checking if the check box is checked
      if ( ($_POST['sppcprice'][$customers_group['customers_group_id']] <> $attributes['customers_group_price']) && ($attributes['customers_group_id'] == $customers_group['customers_group_id']) ) {
	    tep_db_query("update " . TABLE_PRODUCTS_GROUPS . " set customers_group_price = '" . $_POST['sppcprice'][$customers_group['customers_group_id']] . "' where customers_group_id = '" . $attributes['customers_group_id'] . "' and products_id = '" . $products_id . "'");
        $attributes = tep_db_fetch_array($attributes_query);
      }
      elseif (($_POST['sppcprice'][$customers_group['customers_group_id']] == $attributes['customers_group_price'])) {
	    $attributes = tep_db_fetch_array($attributes_query);
      }
    }
    else {
      tep_db_query("delete from " . TABLE_PRODUCTS_GROUPS . " where customers_group_id = '" . $customers_group['customers_group_id'] . "' and products_id = '" . $products_id . "'");
      $attributes = tep_db_fetch_array($attributes_query);
    }
  }
  elseif (($_POST['sppcoption'][$customers_group['customers_group_id']]) && ($_POST['sppcprice'][$customers_group['customers_group_id']] != '')) {
    tep_db_query("insert into " . TABLE_PRODUCTS_GROUPS . " (products_id, customers_group_id, customers_group_price) values ('" . $products_id . "', '" . $customers_group['customers_group_id'] . "', '" . $_POST['sppcprice'][$customers_group['customers_group_id']] . "')");
    $attributes = tep_db_fetch_array($attributes_query);
  }
}
// EOF Separate Pricing Per Customer

          $languages = tep_get_languages();
          for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $language_id = $languages[$i]['id'];

            /*** Begin Header Tags SEO ***/
            $sql_data_array = array('products_name' => tep_db_prepare_input($_POST['products_name'][$language_id]),
                                    'products_description' => tep_db_prepare_input($_POST['products_description'][$language_id]),
                              
                                    'products_url' => tep_db_prepare_input($_POST['products_url'][$language_id]),
                                    'products_head_title_tag' => ((tep_not_null($_POST['products_head_title_tag'][$language_id])) ? tep_db_prepare_input($_POST['products_head_title_tag'][$language_id]) : tep_db_prepare_input($_POST['products_name'][$language_id])),
                                    'products_head_desc_tag' => ((tep_not_null($_POST['products_head_desc_tag'][$language_id])) ? tep_db_prepare_input($_POST['products_head_desc_tag'][$language_id]) : tep_db_prepare_input($_POST['products_name'][$language_id])),
                                     // Start Products Specifications
									'products_tab_1' => tep_db_prepare_input ($_POST['products_tab_1'][$language_id]),
									'products_tab_2' => tep_db_prepare_input ($_POST['products_tab_2'][$language_id]),
									'products_tab_3' => tep_db_prepare_input ($_POST['products_tab_3'][$language_id]),
									'products_tab_4' => tep_db_prepare_input ($_POST['products_tab_4'][$language_id]),
									'products_tab_5' => tep_db_prepare_input ($_POST['products_tab_5'][$language_id]),
									'products_tab_6' => tep_db_prepare_input ($_POST['products_tab_6'][$language_id]),
// End Products Specifications
                                    'products_head_keywords_tag' => ((tep_not_null($_POST['products_head_keywords_tag'][$language_id])) ? tep_db_prepare_input($_POST['products_head_keywords_tag'][$language_id]) : tep_db_prepare_input($_POST['products_name'][$language_id])));                                     
        
		
		   /*** End Header Tags SEO ***/

            if ($action == 'insert_product') {
              $insert_sql_data = array('products_id' => $products_id,
                                       'language_id' => $language_id);

              $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

              tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array);
            } elseif ($action == 'update_product') {
              tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "' and language_id = '" . (int)$language_id . "'");
            }



 //BOF is categorie name given, when no insert it
          if ($action == 'update_product'){
          #check db
          $check_pd = tep_db_query("select * from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "' and language_id = '" . (int)$languages[$i]['id'] . "'");
          $num_pd = tep_db_num_rows($check_pd);
          #when no entry is found, insert new products description in correct language
          if(!$num_pd){
              $insert_sql_data = array('products_id' => $products_id,
                                       'language_id' => $language_id);

            $sql_data_array = array_merge($sql_data_array, $insert_sql_data);
          #perform db entry          
          tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array);
           }
          }
          //EOF
// Start Products Specifications
          for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
            $language_id = $languages[$i]['id'];
            $specifications_query_raw = "select s.specifications_id
                                         from " . TABLE_SPECIFICATION . " s, 
                                              " . TABLE_SPECIFICATIONS_TO_CATEGORIES . " sg2c
                                         where sg2c.specification_group_id = s.specification_group_id 
                                           and sg2c.categories_id = '" . (int) $current_category_id . "'
                                       ";
            $specifications_query = tep_db_query ($specifications_query_raw);

            $count_specificatons = tep_db_num_rows ($specifications_query);
            if ($count_specificatons > 0) {
              while ($specifications = tep_db_fetch_array ($specifications_query) ) {
                $specifications_id = (int) $specifications['specifications_id'];

                tep_db_query ("delete from " . TABLE_PRODUCTS_SPECIFICATIONS . " 
                               where products_id = '" . (int) $products_id . "' 
                                 and specifications_id = '" . $specifications_id . "'
                                 and language_id = '" . $language_id . "'
                            ");
                
                $specification = $_POST['products_specification'][$specifications_id][$language_id];
                if (is_array ($specification) ) {
                  foreach ($specification as $each_specification) {
                    $each_specification = tep_db_prepare_input ($each_specification);
                    if ($each_specification != '') {
                      $sql_data_array = array ('specification' => $each_specification,
                                               'products_id' => $products_id,
                                               'specifications_id' => $specifications_id,
                                               'language_id' => $language_id
                                              );
                  
                      tep_db_perform (TABLE_PRODUCTS_SPECIFICATIONS, $sql_data_array);
                    } // if ($each_specification
                  } // foreach ($specification
                  
                } else {
                  $specification = tep_db_prepare_input ($specification);
                  if ($specification != '') {
                    $sql_data_array = array ('specification' => $specification,
                                             'products_id' => $products_id,
                                             'specifications_id' => $specifications_id,
                                             'language_id' => $language_id
                                            );
                    tep_db_perform (TABLE_PRODUCTS_SPECIFICATIONS, $sql_data_array);
                  } // if ($specification
                } //  if (is_array ... else ...
              } // while ($specifications
            } // if ($count_specificatons
          } // for ($i=0
// End Products Specifications

          }

          if (USE_CACHE == 'true') {
            tep_reset_cache_block('categories');
            tep_reset_cache_block('also_purchased');
          }
 if (USE_PRODUCTS_COUNT_CACHE == 'true') {
            tep_reset_cache_block('products_count');
        }

         
		 tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products_id . '&action=new_product'));
        }
        break;
		
		// Master Products
      case 'insert_master':
      case 'update_master':
          if (isset($_GET['pID'])) $products_id = tep_db_prepare_input($_GET['pID']);
            $products_date_available = tep_db_prepare_input($_POST['products_date_available']);

            $products_date_available = (date('Y-m-d') < $products_date_available) ? $products_date_available : 'null';
            $products_master_status = '1';

            $sql_data_array = array('products_quantity' => (int)tep_db_prepare_input($_POST['products_quantity']),
                                    'products_model' => tep_db_prepare_input($_POST['products_model']),
                             //MVS start
                             
                                  'vendors_prod_id' => tep_db_prepare_input($_POST['vendors_prod_id']),
                                  'vendors_product_price' => tep_db_prepare_input($_POST['vendors_product_price']),
                                  'vendors_id' => tep_db_prepare_input($_POST['vendors_id']),
                                  'vendors_prod_comments' => tep_db_prepare_input($_POST['vendors_prod_comments']),
//MVS end     
								    'products_price' => tep_db_prepare_input($_POST['products_price']),
                                    'products_date_available' => $products_date_available,
                                    'products_weight' => (float)tep_db_prepare_input($_POST['products_weight']),
                                    'products_status' => tep_db_prepare_input($_POST['products_status']),


                                    'products_master_status' => tep_db_prepare_input($_POST['products_master_status']),
                                    'products_tax_class_id' => tep_db_prepare_input($_POST['products_tax_class_id']),
                                    'manufacturers_id' => (int)tep_db_prepare_input($_POST['manufacturers_id']));



        $products_image = new upload('products_image');
        $products_image->set_destination(DIR_FS_CATALOG_IMAGES);
          if ($products_image->parse() && $products_image->save()) {
            $sql_data_array['products_image'] = tep_db_prepare_input($products_image->filename);
          }

            if ($action == 'insert_master') {
              $insert_sql_data = array('products_date_added' => 'now()');

              $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

              tep_db_perform(TABLE_PRODUCTS, $sql_data_array);
              $products_id = tep_db_insert_id();

              tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$products_id . "', '" . (int)$current_category_id . "')");
            } elseif ($action == 'update_master') {
              $update_sql_data = array('products_last_modified' => 'now()');

              $sql_data_array = array_merge($sql_data_array, $update_sql_data);

              tep_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "'");
            }

            $languages = tep_get_languages();
            for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
              $language_id = $languages[$i]['id'];

              $sql_data_array = array('products_name' => tep_db_prepare_input($_POST['products_name'][$language_id]),
                                      'products_description' => tep_db_prepare_input($_POST['products_description'][$language_id]),
                                      'products_url' => tep_db_prepare_input($_POST['products_url'][$language_id]));

              if ($action == 'insert_master') {
                $insert_sql_data = array('products_id' => $products_id,
                                         'language_id' => $language_id);

                $sql_data_array = array_merge($sql_data_array, $insert_sql_data);

                tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array);
              } elseif ($action == 'update_master') {
                tep_db_perform(TABLE_PRODUCTS_DESCRIPTION, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "' and language_id = '" . (int)$language_id . "'");
              }
            }

            $pi_sort_order = 0;
            $piArray = array(0);

            foreach ($HTTP_POST_FILES as $key => $value) {
// Update existing large product images
              if (preg_match('/^products_image_large_([0-9]+)$/', $key, $matches)) {
                $pi_sort_order++;

                $sql_data_array = array('htmlcontent' => tep_db_prepare_input($_POST['products_image_htmlcontent_' . $matches[1]]),
                                        'sort_order' => $pi_sort_order);

                $t = new upload($key);
                $t->set_destination(DIR_FS_CATALOG_IMAGES);
                if ($t->parse() && $t->save()) {
                  $sql_data_array['image'] = tep_db_prepare_input($t->filename);
                }

                tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array, 'update', "products_id = '" . (int)$products_id . "' and id = '" . (int)$matches[1] . "'");

                $piArray[] = (int)$matches[1];
              } elseif (preg_match('/^products_image_large_new_([0-9]+)$/', $key, $matches)) {
// Insert new large product images
                $sql_data_array = array('products_id' => (int)$products_id,
                                        'htmlcontent' => tep_db_prepare_input($_POST['products_image_htmlcontent_new_' . $matches[1]]));

                $t = new upload($key);
                $t->set_destination(DIR_FS_CATALOG_IMAGES);
                if ($t->parse() && $t->save()) {
                  $pi_sort_order++;

                  $sql_data_array['image'] = tep_db_prepare_input($t->filename);
                  $sql_data_array['sort_order'] = $pi_sort_order;

                  tep_db_perform(TABLE_PRODUCTS_IMAGES, $sql_data_array);

                  $piArray[] = tep_db_insert_id();
                }
             }
            }

            $product_images_query = tep_db_query("select image from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$products_id . "' and id not in (" . implode(',', $piArray) . ")");
            if (tep_db_num_rows($product_images_query)) {
              while ($product_images = tep_db_fetch_array($product_images_query)) {
                $duplicate_image_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_IMAGES . " where image = '" . tep_db_input($product_images['image']) . "'");
                $duplicate_image = tep_db_fetch_array($duplicate_image_query);

                if ($duplicate_image['total'] < 2) {
                  if (file_exists(DIR_FS_CATALOG_IMAGES . $product_images['image'])) {
                    @unlink(DIR_FS_CATALOG_IMAGES . $product_images['image']);
                  }
                }
            }

          tep_db_query("delete from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$products_id . "' and id not in (" . implode(',', $piArray) . ")");
        }

        if (USE_CACHE == 'true') {
              tep_reset_cache_block('categories');
              tep_reset_cache_block('also_purchased');
            }

            tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products_id));
        break;
// Master Products EOF
		
		
      case 'copy_to_confirm':
        if (isset($_POST['products_id']) && isset($_POST['categories_id'])) {
          $products_id = tep_db_prepare_input($_POST['products_id']);
          $categories_id = tep_db_prepare_input($_POST['categories_id']);

          if ($_POST['copy_as'] == 'link') {
            if ($categories_id != $current_category_id) { 
              $check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_TO_CATEGORIES . " where products_id = '" . (int)$products_id . "' and categories_id = '" . (int)$categories_id . "'");
              $check = tep_db_fetch_array($check_query);
              if ($check['total'] < '1') {
                tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$products_id . "', '" . (int)$categories_id . "')");
              }
            } else {
              $messageStack->add_session(ERROR_CANNOT_LINK_TO_SAME_CATEGORY, 'error');
            }
          } elseif ($_POST['copy_as'] == 'duplicate') {
			       
		   $product_query = tep_db_query("select products_quantity, products_model,vendors_prod_id, products_image,products_image_med, products_image_lrg, products_image_sm_1, products_image_xl_1, products_image_sm_2, products_image_xl_2, products_image_sm_3, products_image_xl_3, products_image_sm_4, products_image_xl_4, products_image_sm_5, products_image_xl_5, products_image_sm_6, products_image_xl_6, products_price, vendors_product_price, vendors_prod_comments, products_cost, products_date_available, products_weight, products_tax_class_id, vendors_id, manufacturers_id, products_master, products_master_status products_bundle, sold_in_bundle_only from " . TABLE_PRODUCTS . " where products_id = '" . (int)$products_id . "'");
    $product = tep_db_fetch_array($product_query);

           tep_db_query("insert into " . TABLE_PRODUCTS . " (products_quantity, products_model,vendors_prod_id, products_image, products_image_med, products_image_lrg, products_image_sm_1, products_image_xl_1, products_image_sm_2, products_image_xl_2, products_image_sm_3, products_image_xl_3, products_image_sm_4, products_image_xl_4, products_image_sm_5, products_image_xl_5, products_image_sm_6, products_image_xl_6, products_price, vendors_product_price, vendors_prod_comments, products_cost, products_date_added, products_date_available, products_weight, products_status, products_tax_class_id, products_bundle, sold_in_bundle_only, admin_id, manufacturers_id, products_master, products_master_status) values ('" . tep_db_input($product['products_quantity']) . "', '" . tep_db_input($product['products_model']) . "', '" . tep_db_input($product['vendors_prod_id']) . "', '" . tep_db_input($product['products_image']) . "', '" . tep_db_input($product['products_image_med']) . "', '" . tep_db_input($product['products_image_lrg']) . "', '" . tep_db_input($product['products_image_sm_1']) . "', '" . tep_db_input($product['products_image_xl_1']) . "', '" . tep_db_input($product['products_image_sm_2']) . "', '" . tep_db_input($product['products_image_xl_2']) . "', '" . tep_db_input($product['products_image_sm_3']) . "', '" . tep_db_input($product['products_image_xl_3']) . "', '" . tep_db_input($product['products_image_sm_4']) . "', '" . tep_db_input($product['products_image_xl_4']) . "', '" . tep_db_input($product['products_image_sm_5']) . "', '" . tep_db_input($product['products_image_xl_5']) . "', '" . tep_db_input($product['products_image_sm_6']) . "', '" . tep_db_input($product['products_image_xl_6']) . "',  '" . tep_db_input($product['products_price']) . "', '" . tep_db_input($product['vendors_product_price']) . "', '" . tep_db_input($product['products_cost']) . "', now(), " . (empty($product['products_date_available']) ? "null" : "'" . tep_db_input($product['products_date_available']) . "'") . ", '" . tep_db_input($product['products_weight']) . "', '0', '" . (int)$product['products_tax_class_id'] . "', '" . tep_db_input($product['products_bundle']) . "', '" . tep_db_input($product['sold_in_bundle_only'])  . "', '" . (int)$product['manufacturers_id'] . "' , '" . (int)$product['vendors_id'] . "', '" . $product['products_master']. "', '" .(int)$product['products_master_status'] . "')");
           
            
            $dup_products_id = tep_db_insert_id();
			
		
// Products Specifications
            $description_query = tep_db_query ("select language_id, 
                                                       products_name, 
                                                       products_description, 
                                                       products_url, 
                                                       products_tab_1, 
                                                       products_tab_2, 
                                                       products_tab_3, 
                                                       products_tab_4, 
                                                       products_tab_5, 
                                                       products_tab_6 
                                              from " . TABLE_PRODUCTS_DESCRIPTION . " 
                                              where products_id = '" . (int) $products_id . "'
                                            ");
            while ($description = tep_db_fetch_array ($description_query) ) {
// Products Specifications
              tep_db_query ("insert into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, language_id, products_name, products_description, products_url, products_tab_1, products_tab_2, products_tab_3, products_tab_4, products_tab_5, products_tab_6, products_viewed) values ('" . (int) $dup_products_id . "', '" . (int) $description['language_id'] . "', '" . tep_db_input($description['products_name']) . "', '" . tep_db_input ($description['products_description']) . "', '" . tep_db_input ($description['products_url']) . "', '" . tep_db_input ($description['products_tab_1']) . "', '" . tep_db_input ($description['products_tab_2']) . "', '" . tep_db_input ($description['products_tab_3']) . "', '" . tep_db_input ($description['products_tab_4']) . "', '" . tep_db_input ($description['products_tab_5']) . "', '" . tep_db_input ($description['products_tab_6']) . "', '0')");
       
			}
			
			
 // bundled products begin
            if ($product['products_bundle'] == 'yes') {
              $bundle_query = tep_db_query('select subproduct_id, subproduct_qty from ' . TABLE_PRODUCTS_BUNDLES . ' where bundle_id = ' . (int)$products_id);
              while ($subprod = tep_db_fetch_array($bundle_query)) {
                tep_db_query('insert into ' . TABLE_PRODUCTS_BUNDLES . " (bundle_id, subproduct_id, subproduct_qty) VALUES ('" . (int)$dup_products_id . "', '" . (int)$subprod['subproduct_id'] . "', '" . (int)$subprod['subproduct_qty'] . "')");
              }
            }
            // bundled products end

             /*** Begin Header Tags SEO ***/
            $description_query = tep_db_query("select language_id, products_name, products_description, products_head_title_tag, products_head_desc_tag, products_head_keywords_tag, products_url from " . TABLE_PRODUCTS_DESCRIPTION . " where products_id = '" . (int)$products_id . "'");
            while ($description = tep_db_fetch_array($description_query)) {
              tep_db_query("insert into " . TABLE_PRODUCTS_DESCRIPTION . " (products_id, language_id, products_name, products_description, products_head_title_tag, products_head_desc_tag, products_head_keywords_tag, products_url, products_viewed) values ('" . (int)$dup_products_id . "', '" . (int)$description['language_id'] . "', '" . tep_db_input($description['products_name']) . "', '" . tep_db_input($description['products_description']) . "', '" . tep_db_input($description['products_head_title_tag']) . "', '" . tep_db_input($description['products_head_desc_tag']) . "', '" . tep_db_input($description['products_head_keywords_tag']) . "', '" . tep_db_input($description['products_url']) . "', '0')");
            }       
           /*** End Header Tags SEO ***/  

            tep_db_query("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " (products_id, categories_id) values ('" . (int)$dup_products_id . "', '" . (int)$categories_id . "')");
			// BOF Separate Pricing Per Customer originally 2006-04-26 by Infobroker
      $cg_price_query = tep_db_query("select customers_group_id, customers_group_price from " . TABLE_PRODUCTS_GROUPS . " where products_id = '" . $products_id . "' order by customers_group_id");

// insert customer group prices in table products_groups when there are any for the copied product
    if (tep_db_num_rows($cg_price_query) > 0) {
      while ( $cg_prices = tep_db_fetch_array($cg_price_query)) {
        tep_db_query("insert into " . TABLE_PRODUCTS_GROUPS . " (customers_group_id, customers_group_price, products_id) values ('" . (int)$cg_prices['customers_group_id'] . "', '" . tep_db_input($cg_prices['customers_group_price']) . "', '" . (int)$dup_products_id . "')");
      } // end while ( $cg_prices = tep_db_fetch_array($cg_price_query))
    } // end if (tep_db_num_rows($cg_price_query) > 0)
    
// EOF Separate Pricing Per Customer originally 2006-04-26 by Infobroker

// Start Products Specifications
            $specifications_query = tep_db_query ("select specifications_id, 
                                                          language_id, 
                                                          specification 
                                                   from " . TABLE_PRODUCTS_SPECIFICATIONS . " 
                                                   where products_id = '" . (int)$products_id . "'
                                                 ");
            while ($specifications = tep_db_fetch_array ($specifications_query) ) {
              tep_db_query ("insert into " . TABLE_PRODUCTS_SPECIFICATIONS . " (
                                         products_id,
                                         specifications_id, 
                                         language_id, 
                                         specification) values (
                                         '" . (int) $dup_products_id . "', 
                                         '" . (int) $specifications['specification_description_id'] . "', 
                                         '" . (int)$specifications['language_id'] . "', 
                                         '" . tep_db_input ($specifications['specification']) . "')
                           ");
            } // while ($specifications
// End Products Specifications

            $products_id = $dup_products_id;
          }

          if (USE_CACHE == 'true') {
            tep_reset_cache_block('categories');
            tep_reset_cache_block('also_purchased');
          }
		   if (USE_PRODUCTS_COUNT_CACHE == 'true') {
            tep_reset_cache_block('products_count');
        }

        }

        tep_redirect(tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $categories_id . '&pID=' . $products_id));
        break;
        
      case 'new_product_preview':
// copy image only if modified
//BOF UltraPics
   if (($_POST['unlink_image'] == 'yes') or ($_POST['delete_image'] == 'yes')) {
        $products_image = '';
        $products_image_name = '';
        } else {
//EOF UltraPics
        $products_image = new upload('products_image');
        $products_image->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image->parse() && $products_image->save()) {
          $products_image_name = $products_image->filename;
        } else {
          $products_image_name = (isset($_POST['products_previous_image']) ? $_POST['products_previous_image'] : '');
        }
		//BOF UltraPics
        }
   if (($_POST['unlink_image_med'] == 'yes') or ($_POST['delete_image_med'] == 'yes')) {
        $products_image_med = '';
       $products_image_med_name = '';
        } else {
        $products_image_med = new upload('products_image_med');
        $products_image_med->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image_med->parse() && $products_image_med->save()) {
          $products_image_med_name = $products_image_med->filename;
        } else {
          $products_image_med_name = (isset($_POST['products_previous_image_med']) ? $_POST['products_previous_image_med'] : '');
        }
        }
   if (($_POST['unlink_image_lrg'] == 'yes') or ($_POST['delete_image_lrg'] == 'yes')) {
        $products_image_lrg = '';
        $products_image_lrg_name = '';
        } else {
        $products_image_lrg = new upload('products_image_lrg');
        $products_image_lrg->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image_lrg->parse() && $products_image_lrg->save()) {
          $products_image_lrg_name = $products_image_lrg->filename;
        } else {
          $products_image_lrg_name = (isset($_POST['products_previous_image_lrg']) ? $_POST['products_previous_image_lrg'] : '');
        }
        }
   if (($_POST['unlink_image_sm_1'] == 'yes') or ($_POST['delete_image_sm_1'] == 'yes')) {
        $products_image_sm_1 = '';
        $products_image_sm_1_name = '';
        } else {
        $products_image_sm_1 = new upload('products_image_sm_1');
        $products_image_sm_1->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image_sm_1->parse() && $products_image_sm_1->save()) {
          $products_image_sm_1_name = $products_image_sm_1->filename;
        } else {
          $products_image_sm_1_name = (isset($_POST['products_previous_image_sm_1']) ? $_POST['products_previous_image_sm_1'] : '');
        }
        }
   if (($_POST['unlink_image_xl_1'] == 'yes') or ($_POST['delete_image_xl_1'] == 'yes')) {
        $products_image_xl_1 = '';
        $products_image_xl_1_name = '';
        } else {
        $products_image_xl_1 = new upload('products_image_xl_1');
        $products_image_xl_1->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image_xl_1->parse() && $products_image_xl_1->save()) {
          $products_image_xl_1_name = $products_image_xl_1->filename;
        } else {
          $products_image_xl_1_name = (isset($_POST['products_previous_image_xl_1']) ? $_POST['products_previous_image_xl_1'] : '');
        }
        }
   if (($_POST['unlink_image_sm_2'] == 'yes') or ($_POST['delete_image_sm_2'] == 'yes')) {
        $products_image_sm_2 = '';
        $products_image_sm_2_name = '';
        } else {
        $products_image_sm_2 = new upload('products_image_sm_2');
        $products_image_sm_2->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image_sm_2->parse() && $products_image_sm_2->save()) {
          $products_image_sm_2_name = $products_image_sm_2->filename;
        } else {
          $products_image_sm_2_name = (isset($_POST['products_previous_image_sm_2']) ? $_POST['products_previous_image_sm_2'] : '');
        }
        }
   if (($_POST['unlink_image_xl_2'] == 'yes') or ($_POST['delete_image_xl_2'] == 'yes')) {
        $products_image_xl_2 = '';
        $products_image_xl_2_name = '';
        } else {
        $products_image_xl_2 = new upload('products_image_xl_2');
        $products_image_xl_2->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image_xl_2->parse() && $products_image_xl_2->save()) {
          $products_image_xl_2_name = $products_image_xl_2->filename;
        } else {
          $products_image_xl_2_name = (isset($_POST['products_previous_image_xl_2']) ? $_POST['products_previous_image_xl_2'] : '');
        }
        }
   if (($_POST['unlink_image_sm_3'] == 'yes') or ($_POST['delete_image_sm_3'] == 'yes')) {
        $products_image_sm_3 = '';
        $products_image_sm_3_name = '';
        } else {
        $products_image_sm_3 = new upload('products_image_sm_3');
        $products_image_sm_3->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image_sm_3->parse() && $products_image_sm_3->save()) {
          $products_image_sm_3_name = $products_image_sm_3->filename;
        } else {
          $products_image_sm_3_name = (isset($_POST['products_previous_image_sm_3']) ? $_POST['products_previous_image_sm_3'] : '');
        }
        }
   if (($_POST['unlink_image_xl_3'] == 'yes') or ($_POST['delete_image_xl_3'] == 'yes')) {
        $products_image_xl_3 = '';
        $products_image_xl_3_name = '';
        } else {
        $products_image_xl_3 = new upload('products_image_xl_3');
        $products_image_xl_3->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image_xl_3->parse() && $products_image_xl_3->save()) {
          $products_image_xl_3_name = $products_image_xl_3->filename;
        } else {
          $products_image_xl_3_name = (isset($_POST['products_previous_image_xl_3']) ? $_POST['products_previous_image_xl_3'] : '');
        }
        }
   if (($_POST['unlink_image_sm_4'] == 'yes') or ($_POST['delete_image_sm_4'] == 'yes')) {
        $products_image_sm_4 = '';
        $products_image_sm_4_name = '';
        } else {
        $products_image_sm_4 = new upload('products_image_sm_4');
        $products_image_sm_4->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image_sm_4->parse() && $products_image_sm_4->save()) {
          $products_image_sm_4_name = $products_image_sm_4->filename;
        } else {
          $products_image_sm_4_name = (isset($_POST['products_previous_image_sm_4']) ? $_POST['products_previous_image_sm_4'] : '');
        }
        }
   if (($_POST['unlink_image_xl_4'] == 'yes') or ($_POST['delete_image_xl_4'] == 'yes')) {
        $products_image_xl_4 = '';
        $products_image_xl_4_name = '';
        } else {
        $products_image_xl_4 = new upload('products_image_xl_4');
        $products_image_xl_4->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image_xl_4->parse() && $products_image_xl_4->save()) {
          $products_image_xl_4_name = $products_image_xl_4->filename;
        } else {
          $products_image_xl_4_name = (isset($_POST['products_previous_image_xl_4']) ? $_POST['products_previous_image_xl_4'] : '');
        }
        }
   if (($_POST['unlink_image_sm_5'] == 'yes') or ($_POST['delete_image_sm_5'] == 'yes')) {
        $products_image_sm_5 = '';
        $products_image_sm_5_name = '';
        } else {
        $products_image_sm_5 = new upload('products_image_sm_5');
        $products_image_sm_5->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image_sm_5->parse() && $products_image_sm_5->save()) {
          $products_image_sm_5_name = $products_image_sm_5->filename;
        } else {
          $products_image_sm_5_name = (isset($_POST['products_previous_image_sm_5']) ? $_POST['products_previous_image_sm_5'] : '');
        }
        }
   if (($_POST['unlink_image_xl_5'] == 'yes') or ($_POST['delete_image_xl_5'] == 'yes')) {
        $products_image_xl_5 = '';
        $products_image_xl_5_name = '';
        } else {
        $products_image_xl_5 = new upload('products_image_xl_5');
        $products_image_xl_5->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image_xl_5->parse() && $products_image_xl_5->save()) {
          $products_image_xl_5_name = $products_image_xl_5->filename;
        } else {
          $products_image_xl_5_name = (isset($_POST['products_previous_image_xl_5']) ? $_POST['products_previous_image_xl_5'] : '');
        }
        }
   if (($_POST['unlink_image_sm_6'] == 'yes') or ($_POST['delete_image_sm_6'] == 'yes')) {
        $products_image_sm_6 = '';
        $products_image_sm_6_name = '';
        } else {
        $products_image_sm_6 = new upload('products_image_sm_6');
        $products_image_sm_6->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image_sm_6->parse() && $products_image_sm_6->save()) {
          $products_image_sm_6_name = $products_image_sm_6->filename;
        } else {
          $products_image_sm_6_name = (isset($_POST['products_previous_image_sm_6']) ? $_POST['products_previous_image_sm_6'] : '');
        }
        }
   if (($_POST['unlink_image_xl_6'] == 'yes') or ($_POST['delete_image_xl_6'] == 'yes')) {
        $products_image_xl_6 = '';
        $products_image_xl_6_name = '';
        } else {
        $products_image_xl_6 = new upload('products_image_xl_6');
        $products_image_xl_6->set_destination(DIR_FS_CATALOG_IMAGES);
        if ($products_image_xl_6->parse() && $products_image_xl_6->save()) {
          $products_image_xl_6_name = $products_image_xl_6->filename;
        } else {
          $products_image_xl_6_name = (isset($_POST['products_previous_image_xl_6']) ? $_POST['products_previous_image_xl_6'] : '');
        }
        }
//EOF UltraPics
		
        break;
    }
  }

// check if the catalog image directory exists
  if (is_dir(DIR_FS_CATALOG_IMAGES)) {
    if (!is_writeable(DIR_FS_CATALOG_IMAGES)) $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_NOT_WRITEABLE, 'error');
  } else {
    $messageStack->add(ERROR_CATALOG_IMAGE_DIRECTORY_DOES_NOT_EXIST, 'error');
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
<!-- AJAX Attribute Manager  -->
<?php require_once( 'attributeManager/includes/attributeManagerHeader.inc.php' )?>
<!-- AJAX Attribute Manager  end -->
<?php
// Start Products Specifications
  if (SPECIFICATIONS_BOX_FRAME_STYLE == 'Tabs') {
?>
  <link href="includes/style_tabs.css" rel="stylesheet" type="text/css">
  <script language="javascript" type="text/javascript" src="includes/functions/jquery-1.3.2.min.js"></script> 
  <script language="javascript" type="text/javascript">
    $(document).ready(function(){  
      initTabs();  
    });  
  
    function initTabs() {  
      $('#tabMenu a').bind('click',function(e) {  
      e.preventDefault();  
      var thref = $(this).attr("href").replace(/#/, '');  
      $('#tabMenu a').removeClass('active');  
      $(this).addClass('active');  
      $('#tabContent div.content').removeClass('active');  
      $('#'+thref).addClass('active');  
      });  
    }  
  </script> 
<?php
  }
// End Products Specifications
?>

<script language="javascript" src="includes/javascript/product_sets.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onLoad="goOnLoad();">
<div id="spiffycalendar" class="text"></div>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="0" cellpadding="0">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top">
<?php
  if ($action == 'new_product') {
    $parameters = array('products_name' => '',
						'products_bundle' => '',
                        'sold_in_bundle_only' => 'no',
                       'products_description' => '',
                    // Start Products Specifications
                        'products_tab_1' => '',
                        'products_tab_2' => '',
                        'products_tab_3' => '',
                        'products_tab_4' => '',
                        'products_tab_5' => '',
                        'products_tab_6' => '',
// End Products Specifications   
                       'products_url' => '',
                       'products_id' => '',
                       'products_quantity' => '',
                       'products_model' => '',
                       'products_image' => '',
					   //BOF UltraPics
                       'products_image_med' => '',
                       'products_image_lrg' => '',
                       'products_image_sm_1' => '',
                       'products_image_xl_1' => '',
                       'products_image_sm_2' => '',
                       'products_image_xl_2' => '',
                       'products_image_sm_3' => '',
                       'products_image_xl_3' => '',
                       'products_image_sm_4' => '',
                       'products_image_xl_4' => '',
                       'products_image_sm_5' => '',
                       'products_image_xl_5' => '',
                       'products_image_sm_6' => '',
                       'products_image_xl_6' => '',
//EOF UltraPics
                       'products_price' => '',
                      'products_cost' => '',
					  'products_weight' => '',
                       'products_date_added' => '',
                       'products_last_modified' => '',
                       'products_date_available' => '',
                       'products_status' => '',
                // MVS start
                       'vendors_product_price' => '',
                       'vendors_prod_comments' => '',
                       'vendors_prod_id' => '',
                       'vendors_id' => '',
//MVS end
				
				       // Master
                       'products_master' => '0',
                       'products_listing_status' => '',
                           'products_tax_class_id' => '',
                       'manufacturers_id' => '');

    $pInfo = new objectInfo($parameters);

    /*** Begin Header Tags SEO ***/
   if (isset ($_GET['pID']) && ( empty($_POST) || $_GET['action_att'])) {
      $product_query = tep_db_query("select pd.products_name, pd.products_description, pd.products_head_title_tag, pd.products_head_desc_tag, pd.products_head_keywords_tag, pd.products_url, p.products_id, p.products_quantity, p.products_model, p.products_image, p.products_image_med, p.products_image_lrg, p.products_image_sm_1, p.products_image_xl_1, p.products_image_sm_2, p.products_image_xl_2, p.products_image_sm_3, p.products_image_xl_3, p.products_image_sm_4, p.products_image_xl_4, p.products_image_sm_5, p.products_image_xl_5, p.products_image_sm_6, p.products_image_xl_6, p.products_price, p.products_cost, p.products_weight, p.products_date_added, p.products_last_modified, date_format(p.products_date_available, '%Y-%m-%d') as products_date_available, p.products_status, p.products_listing_status, p.products_master, p.products_master_status, p.products_tax_class_id, p.products_bundle, p.sold_in_bundle_only, p.manufacturers_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int)$_GET['pID'] . "' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "'");
      $product = tep_db_fetch_array($product_query);                            
   /*** End Header Tags SEO ***/


      $pInfo->objectInfo($product);
    } elseif (tep_not_null($_POST)) {
      $pInfo->objectInfo($_POST);
      $products_name = $_POST['products_name'];
      $products_description = $_POST['products_description'];
      // Start Products Specifications
      $products_spec = $_POST['products_spec'];
      $products_musthave = $_POST['products_musthave'];
      $products_extraimage = $_POST['products_extraimage'];
      $products_manual = $_POST['products_manual'];
      $products_extra1 = $_POST['products_extra1'];
      $products_moreinfo = $_POST['products_moreinfo'];
      $products_tab_1 = $_POST['products_tab_1'];
      $products_tab_2 = $_POST['products_tab_2'];
      $products_tab_3 = $_POST['products_tab_3'];
      $products_tab_4 = $_POST['products_tab_4'];
      $products_tab_5 = $_POST['products_tab_5'];
      $products_tab_6 = $_POST['products_tab_6'];
// End Products Specifications
      $products_url = $_POST['products_url'];
	  
    }
    // BOF Bundled Products
    if (isset($pInfo->products_bundle) && $pInfo->products_bundle == "yes") {
    // this product is a bundle so get contents data 
      $bundle_query = tep_db_query("SELECT pb.subproduct_id, pb.subproduct_qty, pd.products_name FROM " . TABLE_PRODUCTS_DESCRIPTION . " pd INNER JOIN " . TABLE_PRODUCTS_BUNDLES . " pb ON pb.subproduct_id=pd.products_id WHERE pb.bundle_id = '" . (int)$_GET['pID'] . "' and language_id = '" . (int)$languages_id . "'");
      while ($bundle_contents = tep_db_fetch_array($bundle_query)) {
        $bundle_array[] = array('id' => $bundle_contents['subproduct_id'],
                                'qty' => $bundle_contents['subproduct_qty'],
                                'name' => $bundle_contents['products_name']);
      }
    }
    $bundle_count = count($bundle_array);
    // EOF Bundled Products
    $manufacturers_array = array(array('id' => '', 'text' => TEXT_NONE));
    $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
    while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
      $manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'],
                                     'text' => $manufacturers['manufacturers_name']);
    }
//MVS start
    $vendors_array = array(array('id' => 'admin_id', 'text' => 'admin_firstname'));
    $vendors_query = tep_db_query("select admin_id, admin_firstname from " . TABLE_ADMIN . " where admin_id=" . $login_id);
    while ($vendors = tep_db_fetch_array($vendors_query)) {
      $vendors_array[] = array('id' => $vendors['admin_id'],
                                     'text' => $vendors['admin_firstname']);
    }
//MVS end
    $tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
    while ($tax_class = tep_db_fetch_array($tax_class_query)) {
      $tax_class_array[] = array('id' => $tax_class['tax_class_id'],
                                 'text' => $tax_class['tax_class_title']);
    }


// Master Products
    $products_master_array = array(array('id' => ' ', 'text' => TEXT_MASTER_SELECT));
    $products_master_array[] = array('id' => '0', 'text' => TEXT_NONE);

    $products_master_query = tep_db_query("select p.products_id, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_master_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' order by pd.products_name");

    while ($products_master = tep_db_fetch_array($products_master_query)) {
      $products_master_array[] = array('id' => $products_master['products_id'],
                                       'text' => $products_master['products_name']);
    }
// Master Products EOF


    $languages = tep_get_languages();

    if (!isset($pInfo->products_status)) $pInfo->products_status = '1';
    switch ($pInfo->products_status) {
      case '0': $in_status = false; $out_status = true; break;
      case '1':
      default: $in_status = true; $out_status = false;
    }
?>
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<script language="JavaScript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="javascript"><!--
  var dateAvailable = new ctlSpiffyCalendarBox("dateAvailable", "new_product", "products_date_available","btnDate1","<?php echo $pInfo->products_date_available; ?>",scBTNMODE_CUSTOMBLUE);
//--></script>
<script language="javascript"><!--
var tax_rates = new Array();
<?php
    for ($i=0, $n=sizeof($tax_class_array); $i<$n; $i++) {
      if ($tax_class_array[$i]['id'] > 0) {
        echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . tep_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
      }
    }
?>

function doRound(x, places) {
  return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
}

function getTaxRate() {
  var selected_value = document.forms["new_product"].products_tax_class_id.selectedIndex;
  var parameterVal = document.forms["new_product"].products_tax_class_id[selected_value].value;

  if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
    return tax_rates[parameterVal];
  } else {
    return 0;
  }
}

function updateGross() {
  var taxRate = getTaxRate();
  var grossValue = document.forms["new_product"].products_price.value;

  if (taxRate > 0) {
    grossValue = grossValue * ((taxRate / 100) + 1);
  }

  document.forms["new_product"].products_price_gross.value = doRound(grossValue, 4);
}

function updateNet() {
  var taxRate = getTaxRate();
  var netValue = document.forms["new_product"].products_price_gross.value;

  if (taxRate > 0) {
    netValue = netValue / ((taxRate / 100) + 1);
  }

  document.forms["new_product"].products_price.value = doRound(netValue, 4);
}
//-->

// Master Products
function updateProductsMaster() {
  var selected_value = document.forms["new_product"].products_master_select.selectedIndex;
  var masValue = document.forms["new_product"].products_master_select[selected_value].value;
  var theValue = document.forms["new_product"].products_master.value;

  if(theValue != '0' && theValue != '' && masValue != ''){
    if(masValue == '0'){
      document.forms["new_product"].products_master.value = (theValue.replace(/([0-9a-zA-Z;"',.=\+\-: _])/g, '')+masValue);    //reset to 0
    } else {
    document.forms["new_product"].products_master.value = (theValue.replace(/ _/g, '')+' '+masValue+' _');
    }
  } else {
    document.forms["new_product"].products_master.value = '_ '+masValue+' _';
  }
}
// Master Products EOF

</script>
    <?php echo tep_draw_form('new_product', FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=new_product_preview', 'post', 'enctype="multipart/form-data"'); ?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo sprintf(TEXT_NEW_PRODUCT, tep_output_generated_category_path($current_category_id)); ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" cellspacing="0" cellpadding="2">
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_radio_field('products_status', '1', $in_status) . '&nbsp;' . TEXT_PRODUCT_AVAILABLE . '&nbsp;' . tep_draw_radio_field('products_status', '0', $out_status) . '&nbsp;' . TEXT_PRODUCT_NOT_AVAILABLE; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_DATE_AVAILABLE; ?><br><small>(YYYY-MM-DD)</small></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;'; ?><script language="javascript">dateAvailable.writeControl(); dateAvailable.dateFormat="yyyy-MM-dd";</script></td>
          </tr>
          <?php //MVS start ?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
             <td class="main"><?php echo TEXT_PRODUCTS_VENDORS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_pull_down_menu('admin_id', $vendors_array, $pInfo->admin_id); ?></td>
          </tr>
<?php //MVS end ?>
          <tr>	
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_MANUFACTURER; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_pull_down_menu('manufacturers_id', $manufacturers_array, $pInfo->manufacturers_id); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main"><?php if ($i == 0) echo TEXT_PRODUCTS_NAME; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('products_name[' . $languages[$i]['id'] . ']', (isset($products_name[$languages[$i]['id']]) ? stripslashes($products_name[$languages[$i]['id']]) : tep_get_products_name($pInfo->products_id, $languages[$i]['id']))); ?></td>
          </tr>
<?php
    }
?>
          <tr>
          	<td>
          <?php 
// Products Specifications
      require (DIR_WS_MODULES . FILENAME_PRODUCTS_SPECIFICATIONS_INPUT);
?>
</td>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr bgcolor="#f4f4f4">
            <td class="main"><?php echo TEXT_PRODUCTS_TAX_CLASS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_pull_down_menu('products_tax_class_id', $tax_class_array, $pInfo->products_tax_class_id, 'onchange="updateGross()"'); ?></td>
          </tr>
          <tr bgcolor="#f4f4f4">
    <td class="main"><?php echo TEXT_PRODUCTS_PRICE_COST; ?></td>
    <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_cost', $pInfo->products_cost, ''); ?></td>
  </tr>
  <tr bgcolor="#f4f4f4">
    <td class="main"><?php echo TEXT_PRODUCTS_PRICE_NET; ?></td>
    <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_price', $pInfo->products_price, 'onKeyUp="updateGross()"'); ?></td>
  </tr>
          <tr bgcolor="#f4f4f4">
            <td class="main"><?php echo TEXT_PRODUCTS_PRICE_GROSS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_price_gross', $pInfo->products_price, 'OnKeyUp="updateNet()"'); ?></td>
          </tr>
          <?php //MVS start ?>
          <tr bgcolor="#ebebff">
          <td class="main"><?php echo TEXT_VENDORS_PRODUCT_PRICE_BASE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('vendors_product_price', $pInfo->vendors_product_price, 'onKeyUp="updateNet()"'); ?></td>
          </tr>
<?php  //MVS end ?>
          <!-- AJAX Attribute Manager  -->
          <tr>
          	<td colspan="2"><?php require_once( 'attributeManager/includes/attributeManagerPlaceHolder.inc.php' )?></td>
          </tr>
<!-- AJAX Attribute Manager end -->
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<script language="javascript"><!--
updateGross();
//--></script>

<?php
// Start Products Specifications
    if (SPECIFICATIONS_BOX_FRAME_STYLE == 'Tabs') {
?>
          <tr>
            <td colspan=2>
<?php
      require (DIR_WS_MODULES . FILENAME_PRODUCTS_TABS);
    } else {
// End Products Specifications
?>
<!-- BOF Separate Pricing Per Customer -->
<?php
    $customers_group_query = tep_db_query("select customers_group_id, customers_group_name from " . TABLE_CUSTOMERS_GROUPS . " where customers_group_id != '0' order by customers_group_id");
    $header = false;
    while ($customers_group = tep_db_fetch_array($customers_group_query)) {

     if (tep_db_num_rows($customers_group_query) > 0) {
       $attributes_query = tep_db_query("select customers_group_id, customers_group_price from " . TABLE_PRODUCTS_GROUPS . " where products_id = '" . $pInfo->products_id . "' and customers_group_id = '" . $customers_group['customers_group_id'] . "' order by customers_group_id");
     } else {
         $attributes = array('customers_group_id' => 'new');
     }
 if (!$header) { ?>

    <tr bgcolor="#f4f4f4">
    <td class="main" colspan="2" style="font-style: italic"><?php echo TEXT_CUSTOMERS_GROUPS_NOTE; ?>
</td>
    </tr>
 <?php
 $header = true;
 } // end if (!header), makes sure this is only shown once
 ?>
        <tr bgcolor="#f4f4f4">
       <td class="main"><?php // only change in version 4.1.1
             if (isset($pInfo->sppcoption)) {
	   echo tep_draw_checkbox_field('sppcoption[' . $customers_group['customers_group_id'] . ']', 'sppcoption[' . $customers_group['customers_group_id'] . ']', (isset($pInfo->sppcoption[ $customers_group['customers_group_id']])) ? 1: 0);
      } else {
      echo tep_draw_checkbox_field('sppcoption[' . $customers_group['customers_group_id'] . ']', 'sppcoption[' . $customers_group['customers_group_id'] . ']', true) . '&nbsp;' . $customers_group['customers_group_name'];
      }
?>
 &nbsp;</td>
       <td class="main"><?php
       if ($attributes = tep_db_fetch_array($attributes_query)) {
       echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('sppcprice[' . $customers_group['customers_group_id'] . ']', $attributes['customers_group_price']);
       }  else {
	       if (isset($pInfo->sppcprice[$customers_group['customers_group_id']])) { // when a preview was done and the back button used
		       $sppc_cg_price = $pInfo->sppcprice[$customers_group['customers_group_id']];
	       } else { // nothing in the db, nothing in the post variables
		       $sppc_cg_price = '';
	       }
	   echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('sppcprice[' . $customers_group['customers_group_id'] . ']', $sppc_cg_price );
	 }  ?></td>
    </tr>
<?php
        } // end while ($customers_group = tep_db_fetch_array($customers_group_query))
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<!-- EOF Separate Pricing Per Customer -->

<!-- /*** Begin Header Tags SEO ***/ //-->
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
         <tr>
            <td class="main" valign="top"><?php if ($i == 0) echo TEXT_PRODUCTS_DESCRIPTION; ?></td>
            <td><table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td class="main" valign="top"><?php echo tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>&nbsp;</td>
                <td class="main"><?php echo tep_draw_fckeditor('products_description[' . $languages[$i]['id'] . ']','600','300', (isset($products_description[$languages[$i]['id']]) ? stripslashes($products_description[$languages[$i]['id']]) : tep_get_products_description($pInfo->products_id, $languages[$i]['id']))); ?></td>
              </tr>
            </table></td>
          </tr>
<?php
    }
?>
          <tr>
          	
            <td colspan="2" class="main"><hr><?php echo TEXT_PRODUCT_METTA_INFO; ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr> 
                   
<?php         
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main" valign="top"><?php if ($i == 0) echo TEXT_PRODUCTS_PAGE_TITLE; ?></td>
            <td><table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td class="main" valign="top"><?php echo tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>&nbsp;</td>
                <td class="main"><?php echo tep_draw_textarea_field('products_head_title_tag[' . $languages[$i]['id'] . ']', 'soft', '70', '15', (isset($products_head_title_tag[$languages[$i]['id']]) ? $products_head_title_tag[$languages[$i]['id']] : tep_get_products_head_title_tag($pInfo->products_id, $languages[$i]['id']))); ?></td>
              </tr>
            </table></td>
          </tr>

<?php
    }
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>          
           <tr>
            <td class="main" valign="top"><?php if ($i == 0) echo TEXT_PRODUCTS_HEADER_DESCRIPTION; ?></td>
            <td><table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td class="main" valign="top"><?php echo tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>&nbsp;</td>
                <td class="main"><?php echo tep_draw_textarea_field('products_head_desc_tag[' . $languages[$i]['id'] . ']', 'soft', '70', '15', (isset($products_head_desc_tag[$languages[$i]['id']]) ? $products_head_desc_tag[$languages[$i]['id']] : tep_get_products_head_desc_tag($pInfo->products_id, $languages[$i]['id']))); ?></td>
              
               
              </tr>
            </table></td>
          </tr>
<?php
    }
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
<?php
// Products Specifications
    } // if (SPECIFICATIONS_BOX_FRAME_STYLE ... else ...

?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>          
           <tr>
            <td class="main" valign="top"><?php if ($i == 0) echo TEXT_PRODUCTS_KEYWORDS; ?></td>
            <td><table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td class="main" valign="top"><?php echo tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>&nbsp;</td>
                <td class="main"><?php echo tep_draw_textarea_field('products_head_keywords_tag[' . $languages[$i]['id'] . ']', 'soft', '70', '15', (isset($products_head_keywords_tag[$languages[$i]['id']]) ? $products_head_keywords_tag[$languages[$i]['id']] : tep_get_products_head_keywords_tag($pInfo->products_id, $languages[$i]['id']))); ?> </td>
              </tr>
            </table></td>
          </tr>
<?php
    }
?>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td colspan="2" class="main"><hr></td>
          </tr>
<!-- /*** End Header Tags SEO ***/ //-->
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_QUANTITY; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_quantity', $pInfo->products_quantity); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <?php  //MVS start ?>
          <tr>
          	  <td class="main"><?php echo TEXT_VENDORS_PROD_ID; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('vendors_prod_id', $pInfo->vendors_prod_id); ?></td>
          </tr>
<?php  //MVS end ?>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_MODEL; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_model', $pInfo->products_model); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <!--BOF UltraPics-->
<!--BOF Original--><!--
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_IMAGE; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_file_field('products_image') . '<br>' . tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . $pInfo->products_image . tep_draw_hidden_field('products_previous_image', $pInfo->products_image); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
--><!--EOF Original-->
<?php
            $image_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image='" . $pInfo->products_image_lrg . "' or products_image_med='" . $pInfo->products_image_lrg . "' or products_image_lrg='" . $pInfo->products_image_lrg . "'");
            $image_count = tep_db_fetch_array($image_count_query);
?>
          <tr>
           <td class="dataTableRow" valign="top"><span class="main"><?php echo TEXT_PRODUCTS_IMAGE_NOTE; ?></span></td>
           <td class="dataTableRow" valign="top"><span class="smallText"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_file_field('products_image') . '<br>';
           if (($_GET['pID']) && ($pInfo->products_image) != '')
               echo tep_draw_separator('pixel_trans.gif', '24', '17" align="left') . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') .
                    tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;&nbsp;<small>' . TEXT_PRODUCTS_IMAGE_LINKED . ' [' . $image_count['total'] . ']<br>' .
                    tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE . '<br>' .
                    tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE . '<br>' .
                    tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;&nbsp;<b>' . TEXT_PRODUCTS_IMAGE . '</b> ' . $pInfo->products_image . tep_draw_hidden_field('products_previous_image', $pInfo->products_image);?></span></td>
          </tr>

<?php if (ULTIMATE_ADDITIONAL_IMAGES == 'enable') {

            $image_med_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image='" . $pInfo->products_image_lrg . "' or products_image_med='" . $pInfo->products_image_lrg . "' or products_image_lrg='" . $pInfo->products_image_lrg . "'");
            $image_med_count = tep_db_fetch_array($image_med_count_query);
?>
          <tr>
           <td class="main" valign="top"><?php echo TEXT_PRODUCTS_IMAGE_MEDIUM; ?></td>
           <td class="main" valign="top"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_file_field('products_image_med') . '<br>';
           if (($_GET['pID']) && ($pInfo->products_image_med) != '')
               echo tep_draw_separator('pixel_trans.gif', '24', '17" align="left') . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image_med, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') .
                    tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;&nbsp;<small>' . TEXT_PRODUCTS_IMAGE_LINKED . ' [' . $image_med_count['total'] . ']<br>' .
                    tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image_med" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE . '<br>' .
                    tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image_med" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE . '<br>' .
                    tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;&nbsp;<b>' . TEXT_PRODUCTS_IMAGE . '</b> ' . $pInfo->products_image_med . tep_draw_hidden_field('products_previous_image_med', $pInfo->products_image_med);?></td>
          </tr>
<?php
            $image_lrg_count_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " where products_image='" . $pInfo->products_image_lrg . "' or products_image_med='" . $pInfo->products_image_lrg . "' or products_image_lrg='" . $pInfo->products_image_lrg . "'");
            $image_lrg_count = tep_db_fetch_array($image_lrg_count_query);
?>
          <tr>
           <td class="dataTableRow" valign="top"><span class="main"><?php echo TEXT_PRODUCTS_IMAGE_LARGE; ?></span></td>
           <td class="dataTableRow" valign="top"><span class="smallText"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_file_field('products_image_lrg') . '<br>';
           if (($_GET['pID']) && ($pInfo->products_image_lrg) != '')
               echo tep_draw_separator('pixel_trans.gif', '24', '17" align="left') . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image_lrg, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') .
                    tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;&nbsp;<small>' . TEXT_PRODUCTS_IMAGE_LINKED . ' [' . $image_lrg_count['total'] . ']<br>' .
                    tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image_lrg" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE . '<br>' .
                    tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image_lrg" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE . '<br>' .
                    tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;&nbsp;<b>' . TEXT_PRODUCTS_IMAGE . '</b> ' . $pInfo->products_image_lrg . tep_draw_hidden_field('products_previous_image_lrg', $pInfo->products_image_lrg);?></span></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '20'); ?></td>
          </tr>

         <tr>
            <td class="main" colspan="3"><?php echo TEXT_PRODUCTS_IMAGE_ADDITIONAL . '<br><hr>';?></td>
          </tr>
          <tr>
            <td class="smalltext" colspan="3"><table border="0" cellpadding="2" cellspacing="0" width="100%">
              <tr>
                <td class="smalltext" colspan="2" valign="top"><?php echo TEXT_PRODUCTS_IMAGE_TH_NOTICE; ?></td>
                <td class="smalltext" colspan="2" valign="top"><?php echo TEXT_PRODUCTS_IMAGE_XL_NOTICE; ?></td>
              </tr>
              
              <tr>
                <td class="dataTableRow" valign="top"><span class="smallText"><?php echo TEXT_PRODUCTS_IMAGE_SM_1; ?></span></td>
                <td class="dataTableRow" valign="top"><span class="smallText"><?php echo tep_draw_file_field('products_image_sm_1') . tep_draw_hidden_field('products_previous_image_sm_1', $pInfo->products_image_sm_1); ?></span></td>
                <td class="dataTableRow" valign="top"><span class="smallText"><?php echo TEXT_PRODUCTS_IMAGE_XL_1; ?></span></td>
                <td class="dataTableRow" valign="top"><span class="smallText"><?php echo tep_draw_file_field('products_image_xl_1') . tep_draw_hidden_field('products_previous_image_xl_1', $pInfo->products_image_xl_1); ?></span></td>
              </tr>
  <?php
      if (($_GET['pID']) && ($pInfo->products_image_sm_1) != '' or ($pInfo->products_image_xl_1) != '') {
    ?>
              <tr>
                <td class="dataTableRow" colspan="2" valign="top"><?php if (tep_not_null($pInfo->products_image_sm_1)) { ?><span class="smallText"><?php echo $pInfo->products_image_sm_1 . '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image_sm_1, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') . '<br>'. tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image_sm_1" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image_sm_1" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '1', '42'); ?></span><?php } ?></td>
                <td class="dataTableRow" colspan="2" valign="top"><?php if (tep_not_null($pInfo->products_image_xl_1)) { ?><span class="smallText"><?php echo $pInfo->products_image_xl_1 . '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image_xl_1, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') . '<br>'. tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image_xl_1" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image_xl_1" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '1', '42'); ?></span><?php } ?></td>
              </tr>
  <?php
     }
   ?>
              <tr>
                <td class="smallText" valign="top"><?php echo TEXT_PRODUCTS_IMAGE_SM_2; ?></td>
                <td class="smallText" valign="top"><?php echo tep_draw_file_field('products_image_sm_2') . tep_draw_hidden_field('products_previous_image_sm_2', $pInfo->products_image_sm_2); ?></td>
                <td class="smallText" valign="top"><?php echo TEXT_PRODUCTS_IMAGE_XL_2; ?></td>
                <td class="smallText" valign="top"><?php echo tep_draw_file_field('products_image_xl_2') . tep_draw_hidden_field('products_previous_image_xl_2', $pInfo->products_image_xl_2); ?></td>
             </tr>
  <?php
      if (($_GET['pID']) && ($pInfo->products_image_sm_2) != '' or ($pInfo->products_image_xl_2) != '') {
    ?>
              <tr>
                <td class="smallText" valign="top" colspan="2"><?php if (tep_not_null($pInfo->products_image_sm_2)) { ?><?php echo $pInfo->products_image_sm_2 . '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image_sm_2, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') . '<br>'. tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image_sm_2" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image_sm_2" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '1', '42'); ?><?php } ?></td>
                <td class="smallText" valign="top" colspan="2"><?php if (tep_not_null($pInfo->products_image_xl_2)) { ?><?php echo $pInfo->products_image_xl_2 . '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image_xl_2, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') . '<br>'. tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image_xl_2" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image_xl_2" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '1', '42'); ?><?php } ?></td>
              </tr>
  <?php
     }
   ?>
              <tr>
                <td class="dataTableRow" valign="top"><span class="smallText"><?php echo TEXT_PRODUCTS_IMAGE_SM_3; ?></span></td>
                <td class="dataTableRow" valign="top"><span class="smallText"><?php echo tep_draw_file_field('products_image_sm_3') . tep_draw_hidden_field('products_previous_image_sm_3', $pInfo->products_image_sm_3); ?></span></td>
                <td class="dataTableRow" valign="top"><span class="smallText"><?php echo TEXT_PRODUCTS_IMAGE_XL_3; ?></span></td>
                <td class="dataTableRow" valign="top"><span class="smallText"><?php echo tep_draw_file_field('products_image_xl_3') . tep_draw_hidden_field('products_previous_image_xl_3', $pInfo->products_image_xl_3); ?></span></td>
              </tr>
  <?php
      if (($_GET['pID']) && ($pInfo->products_image_sm_3) != '' or ($pInfo->products_image_xl_3) != '') {
    ?>
              <tr>
                <td class="dataTableRow" colspan="2" valign="top"><?php if (tep_not_null($pInfo->products_image_sm_3)) { ?><span class="smallText"><?php echo $pInfo->products_image_sm_3 . '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image_sm_3, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') . '<br>'. tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image_sm_3" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image_sm_3" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '1', '42'); ?></span><?php } ?></td>
                <td class="dataTableRow" colspan="2" valign="top"><?php if (tep_not_null($pInfo->products_image_xl_3)) { ?><span class="smallText"><?php echo $pInfo->products_image_xl_3 . '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image_xl_3, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') . '<br>'. tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image_xl_3" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image_xl_3" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '1', '42'); ?></span><?php } ?></td>
              </tr>
  <?php
     }
   ?>

              <tr>
                <td class="smallText" valign="top"><?php echo TEXT_PRODUCTS_IMAGE_SM_4; ?></td>
                <td class="smallText" valign="top"><?php echo tep_draw_file_field('products_image_sm_4') . tep_draw_hidden_field('products_previous_image_sm_4', $pInfo->products_image_sm_4); ?></td>
                <td class="smallText" valign="top"><?php echo TEXT_PRODUCTS_IMAGE_XL_4; ?></td>
                <td class="smallText" valign="top"><?php echo tep_draw_file_field('products_image_xl_4') . tep_draw_hidden_field('products_previous_image_xl_4', $pInfo->products_image_xl_4); ?></td>
             </tr>
  <?php
      if (($_GET['pID']) && ($pInfo->products_image_sm_4) != '' or ($pInfo->products_image_xl_4) != '') {
    ?>
              <tr>
                <td class="smallText" valign="top" colspan="2"><?php if (tep_not_null($pInfo->products_image_sm_4)) { ?><?php echo $pInfo->products_image_sm_4 . '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image_sm_4, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') . '<br>'. tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image_sm_4" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image_sm_4" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '1', '42'); ?><?php } ?></td>
                <td class="smallText" valign="top" colspan="2"><?php if (tep_not_null($pInfo->products_image_xl_4)) { ?><?php echo $pInfo->products_image_xl_4 . '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image_xl_4, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') . '<br>'. tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image_xl_4" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image_xl_4" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '1', '42'); ?><?php } ?></td>
              </tr>
  <?php
     }
   ?>


              <tr>
                <td class="dataTableRow" valign="top"><span class="smallText"><?php echo TEXT_PRODUCTS_IMAGE_SM_5; ?></span></td>
                <td class="dataTableRow" valign="top"><span class="smallText"><?php echo tep_draw_file_field('products_image_sm_5') . tep_draw_hidden_field('products_previous_image_sm_5', $pInfo->products_image_sm_5); ?></span></td>
                <td class="dataTableRow" valign="top"><span class="smallText"><?php echo TEXT_PRODUCTS_IMAGE_XL_5; ?></span></td>
                <td class="dataTableRow" valign="top"><span class="smallText"><?php echo tep_draw_file_field('products_image_xl_5') . tep_draw_hidden_field('products_previous_image_xl_5', $pInfo->products_image_xl_5); ?></span></td>
              </tr>
  <?php
      if (($_GET['pID']) && ($pInfo->products_image_sm_5) != '' or ($pInfo->products_image_xl_5) != '') {
    ?>
              <tr>
                <td class="dataTableRow" colspan="2" valign="top"><?php if (tep_not_null($pInfo->products_image_sm_5)) { ?><span class="smallText"><?php echo $pInfo->products_image_sm_5 . '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image_sm_5, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') . '<br>'. tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image_sm_5" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image_sm_5" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '1', '42'); ?></span><?php } ?></td>
                <td class="dataTableRow" colspan="2" valign="top"><?php if (tep_not_null($pInfo->products_image_xl_5)) { ?><span class="smallText"><?php echo $pInfo->products_image_xl_5 . '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image_xl_5, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') . '<br>'. tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image_xl_5" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image_xl_5" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '1', '42'); ?></span><?php } ?></td>
              </tr>
  <?php
     }
   ?>
              <tr>
                <td class="smallText" valign="top"><?php echo TEXT_PRODUCTS_IMAGE_SM_6; ?></td>
                <td class="smalltext" valign="top"><?php echo tep_draw_file_field('products_image_sm_6') . tep_draw_hidden_field('products_previous_image_sm_6', $pInfo->products_image_sm_6); ?></td>
                <td class="smallText" valign="top"><?php echo TEXT_PRODUCTS_IMAGE_XL_6; ?></td>
                <td class="smalltext" valign="top"><?php echo tep_draw_file_field('products_image_xl_6') . tep_draw_hidden_field('products_previous_image_xl_6', $pInfo->products_image_xl_6); ?></td>
             </tr>
  <?php
      if (($_GET['pID']) && ($pInfo->products_image_sm_6) != '' or ($pInfo->products_image_xl_6) != '') {
    ?>
              <tr>
                <td class="smallText" valign="top" colspan="2"><?php if (tep_not_null($pInfo->products_image_sm_6)) { ?><?php echo $pInfo->products_image_sm_6 . '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image_sm_6, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') . '<br>'. tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image_sm_6" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image_sm_6" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '1', '42'); ?><?php } ?></td>
                <td class="smallText" valign="top" colspan="2"><?php if (tep_not_null($pInfo->products_image_xl_6)) { ?><?php echo $pInfo->products_image_xl_6 . '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $pInfo->products_image_xl_6, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="left" hspace="0" vspace="5"') . '<br>'. tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="unlink_image_xl_6" value="yes">' . TEXT_PRODUCTS_IMAGE_REMOVE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '5', '15') . '&nbsp;<input type="checkbox" name="delete_image_xl_6" value="yes">' . TEXT_PRODUCTS_IMAGE_DELETE_SHORT . '<br>' . tep_draw_separator('pixel_trans.gif', '1', '42'); ?><?php } ?></td>
              </tr>
  <?php
     }
   ?>

            </table></td>
          </tr>
          
          
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
<?php
}
?>
<!--EOF UltraPics-->
<?php
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
          <tr>
            <td class="main"><?php if ($i == 0) echo TEXT_PRODUCTS_URL . '<br><small>' . TEXT_PRODUCTS_URL_WITHOUT_HTTP . '</small>'; ?></td>
            <td class="main"><?php echo tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('products_url[' . $languages[$i]['id'] . ']', (isset($products_url[$languages[$i]['id']]) ? stripslashes($products_url[$languages[$i]['id']]) : tep_get_products_url($pInfo->products_id, $languages[$i]['id']))); ?></td>
          </tr>
<?php
    }
?>
<tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_WEIGHT; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_weight', $pInfo->products_weight); ?></td>
          </tr>
          
          <!-- BOF Bundled Products -->
          <tr bgcolor="#EEEEEE">
            <td></td>
            <td class="main" valign="top">
            <?php echo tep_draw_radio_field('sold_in_bundle_only', 'no', true, $pInfo->sold_in_bundle_only) . ENTRY_AVAILABLE_SEPARATELY . '<br />' . tep_draw_radio_field('sold_in_bundle_only', 'yes', false, $pInfo->sold_in_bundle_only) . ENTRY_IN_BUNDLE_ONLY; ?>
            </td>
          </tr>
          <tr bgcolor="#EEEEEE">
            <td class="main" valign="top">
              <?php echo TEXT_PRODUCTS_BUNDLE; ?>
            </td>
            <td class="main" valign="top">
              <table>
                <tr>
                  <td class="main" valign="top">
                    <?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . tep_draw_pull_down_menu('products_bundle', array(array('id'=>'no','text'=>'No'),array('id'=>'yes','text'=>'Yes')), $pInfo->products_bundle) . '<br><a href="javascript:" onclick="addSubproduct()">' . TEXT_ADD_LINE . '</a><br>'; ?>
                  </td>
                  <td class="main" valign="top">
<script type="text/javascript"><!--
function fillCodes() {
  for (var n=0;n<100;n++) {
    var this_subproduct_id = eval("document.new_product.subproduct_" + n + "_id")
    var this_subproduct_name = eval("document.new_product.subproduct_" + n + "_name")
    var this_subproduct_qty = eval("document.new_product.subproduct_" + n + "_qty")
    if (this_subproduct_id.value == "") {
      this_subproduct_id.value = document.new_product.subproduct_selector.value
      this_subproduct_qty.value = "1"
      var name = document.new_product.subproduct_selector[document.new_product.subproduct_selector.selectedIndex].text
        this_subproduct_name.value = name
        document.returnValue = true;
        return true;
    }
  }
}

function clearSubproduct(n) {
  var this_subproduct_id = eval("document.new_product.subproduct_" + n + "_id");
  var this_subproduct_name = eval("document.new_product.subproduct_" + n + "_name");
  var this_subproduct_qty = eval("document.new_product.subproduct_" + n + "_qty");
  this_subproduct_id.value = "";
  this_subproduct_name.value = "";
  this_subproduct_qty.value = "";
}

function addSubproduct(){
  var n = parseInt(document.getElementById('bundled_subproducts_i').value);
  var HTML = document.getElementById('bundled_subproducts');
  currentElement = document.createElement("input");
  currentElement.setAttribute("disabled");
  currentElement.setAttribute("size","30");
  currentElement.setAttribute("type", "text");
  currentElement.setAttribute("name", 'subproduct_' + n + '_name');
  currentElement.setAttribute("value", "");
  HTML.appendChild(currentElement);
  currentElement = document.createElement("input");
  currentElement.setAttribute("size","3");
  currentElement.setAttribute("type", "hidden");
  currentElement.setAttribute("name", 'subproduct_' + n + '_id');
  currentElement.setAttribute("value", "");
  HTML.appendChild(currentElement);
  currentElement = document.createTextNode(' ');
  HTML.appendChild(currentElement);
  currentElement = document.createElement("input");
  currentElement.setAttribute("size","3");
  currentElement.setAttribute("type", "text");
  currentElement.setAttribute("name", 'subproduct_' + n + '_qty');
  currentElement.setAttribute("value", "");
  HTML.appendChild(currentElement);
  document.createTextNode('&nbsp;');
  HTML.appendChild(currentElement);
  var myLink = document.createElement('a');
  var href = document.createAttribute('href');
  myLink.setAttribute('href','javascript:');
  myLink.setAttribute('onclick', 'clearSubproduct(' + n + ')');
  <?php echo "myLink.innerText = ' [x] " . TEXT_REMOVE_PRODUCT . "';\n"; ?>
  HTML.appendChild(myLink);
  currentElement = document.createElement("br");
  HTML.appendChild(currentElement);
  document.getElementById('bundled_subproducts_i').value = n + 1;
}
            //--></script>
                    <div id="bundled_subproducts">
<?php
    echo TEXT_BUNDLE_HEADING . "<br />\n";
    for ($i=0, $n = $bundle_count ? $bundle_count+1:3; $i<$n; $i++) {
      echo '<input type="text" disabled size="30" name="subproduct_' . $i . '_name" value="' . tep_output_string($bundle_array[$i]['name']) . '">' . "\n";
      echo '<input type="hidden" size="3" name="subproduct_' . $i . '_id" value="' . $bundle_array[$i]['id'] . '">' . "\n";
      echo '<input type="text" size="3" name="subproduct_' . $i . '_qty" value="' . $bundle_array[$i]['qty'] . '">' . "\n";
      echo '<a href="javascript:clearSubproduct(' . $i . ')">[x] ' . TEXT_REMOVE_PRODUCT . "</a><br>\n";
    }
?>
                    </div>
                  </td>
                </tr>
                <tr>
                  <td colspan=2 class="main">
<?php
    echo tep_draw_hidden_field('bundled_subproducts_i', $i,'id="bundled_subproducts_i"');
    echo TEXT_ADD_PRODUCT . '<select name="subproduct_selector" onChange="fillCodes()">';
    echo '<option name="null" value="" SELECTED></option>';
    $where_str = '';
    if (isset($_GET['pID'])) {
      $bundle_check = bundle_avoid($_GET['pID']);
      if (!empty($bundle_check)) {
        $where_str = ' and (not (p.products_id in (' . implode(',', $bundle_check) . ')))';
      }
     }
    $products = tep_db_query("select pd.products_name, p.products_id, p.products_model from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id <> " . (int)$_GET['pID'] . $where_str . " order by p.products_model");
    while($products_values = tep_db_fetch_array($products)) {
      echo "\n" . '<option name="' . $products_values['products_id'] . '" value="' . $products_values['products_id'] . '">' . $products_values['products_name'] . " (" . $products_values['products_model'] . ')</option>';
    }
    echo '</select>';
?>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <!-- EOF Bundled Products -->

          
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
	  <?php include(DIR_WS_MODULES . FILENAME_PRODUCT_SETS);?>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <!-- Master Products //-->
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_MASTER; ?></td>
            <td class="main">
            <?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . tep_draw_pull_down_menu('products_master_select', $products_master_array, '0', 'size="5" onchange="updateProductsMaster()"'); ?><br />
            <?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . tep_draw_input_field('products_master', $pInfo->products_master, "size=38")   . "&nbsp;(_ x y _);(_ x _);(0)=default only"; ?>
            </td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_LISTING_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_radio_field('products_listing_status', '1', $in_listing_status) . '&nbsp;' . TEXT_LIST_PRODUCT . '&nbsp;' . tep_draw_radio_field('products_listing_status', '0', $out_listing_status) . '&nbsp;' . TEXT_HIDE_PRODUCT; ?></td>
          </tr>
          <tr>
            <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
          </tr>
          <tr>
            <td class="main"><?php echo TEXT_PRODUCTS_MASTER_STATUS; ?></td>
            <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_checkbox_field('products_master_status', '1', $in_master_status); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="smallText" align="right"><?php echo tep_draw_hidden_field('products_date_added', (tep_not_null($pInfo->products_date_added) ? $pInfo->products_date_added : date('Y-m-d'))) . tep_image_submit('button_preview.gif', IMAGE_PREVIEW, 'disk', null, 'primary') . tep_image_button('button_cancel.gif', IMAGE_CANCEL, 'close', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : ''))); ?></td>
      </tr>
    </table>
    
<script type="text/javascript">
$('#products_date_available').datepicker({
  dateFormat: 'yy-mm-dd'
});
</script>

    </form>
<?php
  } elseif ($action == 'new_master') {

    $parameters = array('products_name' => '',
                        'products_description' => '',
                        'products_url' => '',
                        'products_id' => '',
                        'products_quantity' => '',
                        'products_model' => '',
                        'products_image' => '',
                        'products_larger_images' => array(),
                        'products_price' => '',
                        'products_weight' => '',
                        'products_date_added' => '',
                        'products_last_modified' => '',
                        'products_date_available' => '',
                        'products_status' => '',
                        'products_tax_class_id' => '',
                   //MVS
				        'vendors_product_price' => '',
                       'vendors_prod_comments' => '',
                       'vendors_prod_id' => '',
                       'vendors_id' => '',
                        'manufacturers_id' => '');

    $pInfo = new objectInfo($parameters);

    if (isset($_GET['pID']) && empty($_POST)) {
      $product_query = tep_db_query("select pd.products_name, pd.products_description, pd.products_url, p.products_id, p.products_quantity, p.products_model, p.products_image, p.products_price, p.products_weight, p.products_date_added, p.products_last_modified, date_format(p.products_date_available, '%Y-%m-%d') as products_date_available, p.products_status, p.products_master_status, p.products_tax_class_id, p.manufacturers_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int)$_GET['pID'] . "' and p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "'");

      $product = tep_db_fetch_array($product_query);

      $pInfo->objectInfo($product);

      $product_images_query = tep_db_query("select id, image, htmlcontent, sort_order from " . TABLE_PRODUCTS_IMAGES . " where products_id = '" . (int)$product['products_id'] . "' order by sort_order");
      while ($product_images = tep_db_fetch_array($product_images_query)) {
        $pInfo->products_larger_images[] = array('id' => $product_images['id'],
                                                 'image' => $product_images['image'],
                                                 'htmlcontent' => $product_images['htmlcontent'],
                                                 'sort_order' => $product_images['sort_order']);
      }
    }

    $manufacturers_array = array(array('id' => '', 'text' => TEXT_NONE));
    $manufacturers_query = tep_db_query("select manufacturers_id, manufacturers_name from " . TABLE_MANUFACTURERS . " order by manufacturers_name");
    while ($manufacturers = tep_db_fetch_array($manufacturers_query)) {
      $manufacturers_array[] = array('id' => $manufacturers['manufacturers_id'],
                                     'text' => $manufacturers['manufacturers_name']);
    }

    $tax_class_array = array(array('id' => '0', 'text' => TEXT_NONE));
    $tax_class_query = tep_db_query("select tax_class_id, tax_class_title from " . TABLE_TAX_CLASS . " order by tax_class_title");
    while ($tax_class = tep_db_fetch_array($tax_class_query)) {
      $tax_class_array[] = array('id' => $tax_class['tax_class_id'],
                                 'text' => $tax_class['tax_class_title']);
    }

    $languages = tep_get_languages();

    if (!isset($pInfo->products_status)) $pInfo->products_status = '1';
      switch ($pInfo->products_status) {
        case '0': $in_status = false; $out_status = true; break;
        case '1':
        default: $in_status = true; $out_status = false;
      }

    if (!isset($pInfo->products_master_status)) $pInfo->products_master_status = '1';
      switch ($pInfo->products_master_status) {
        case '0': 
        case '1': $in_master_status = false; $out_master_status = true; break;
        default: $in_master_status = true; $out_master_status = false;
     }

    $form_action = (isset($_GET['pID'])) ? 'update_master' : 'insert_master';
?>
<script type="text/javascript"><!--
var tax_rates = new Array();
<?php
for ($i=0, $n=sizeof($tax_class_array); $i<$n; $i++) {
  if ($tax_class_array[$i]['id'] > 0) {
    echo 'tax_rates["' . $tax_class_array[$i]['id'] . '"] = ' . tep_get_tax_rate_value($tax_class_array[$i]['id']) . ';' . "\n";
  }
}
?>

function doRound(x, places) {
return Math.round(x * Math.pow(10, places)) / Math.pow(10, places);
}

function getTaxRate() {
var selected_value = document.forms["new_master"].products_tax_class_id.selectedIndex;
var parameterVal = document.forms["new_master"].products_tax_class_id[selected_value].value;

if ( (parameterVal > 0) && (tax_rates[parameterVal] > 0) ) {
return tax_rates[parameterVal];
} else {
return 0;
}
}

function updateGross() {
var taxRate = getTaxRate();
var grossValue = document.forms["new_master"].products_price.value;

if (taxRate > 0) {
grossValue = grossValue * ((taxRate / 100) + 1);
}

document.forms["new_master"].products_price_gross.value = doRound(grossValue, 4);
}

function updateNet() {
var taxRate = getTaxRate();
var netValue = document.forms["new_master"].products_price_gross.value;

if (taxRate > 0) {
netValue = netValue / ((taxRate / 100) + 1);
}

document.forms["new_master"].products_price.value = doRound(netValue, 4);
}
//--></script>
<?php echo tep_draw_form('new_master', FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data"'); ?>
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td class="pageHeading"><?php echo sprintf(TEXT_NEW_MASTER_PRODUCT, tep_output_generated_category_path($current_category_id)); ?></td>
        <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
      </tr>
    </table></td>
  </tr>
  <tr>
    <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>
  <tr>
    <td><table border="0" cellspacing="0" cellpadding="2">
      <tr>
        <td class="main"><?php echo TEXT_PRODUCTS_STATUS; ?></td>
        <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_radio_field('products_status', '1', $in_status) . '&nbsp;' . TEXT_PRODUCT_AVAILABLE . '&nbsp;' . tep_draw_radio_field('products_status', '0', $out_status) . '&nbsp;' . TEXT_PRODUCT_NOT_AVAILABLE; ?></td>
      </tr>
<?php $products_master_status = '1'; ?>
      <tr>
        <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo TEXT_PRODUCTS_DATE_AVAILABLE; ?><br /><small>(YYYY-MM-DD)</small></td>
        <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_date_available', $pInfo->products_date_available, 'id="products_date_available"') . ' <small>(YYYY-MM-DD)</small>'; ?></td>
      </tr>
      <tr>
        <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo TEXT_PRODUCTS_MANUFACTURER; ?></td>
        <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_pull_down_menu('manufacturers_id', $manufacturers_array, $pInfo->manufacturers_id); ?></td>
      </tr>
      <tr>
        <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
      <tr>
        <td class="main"><?php if ($i == 0) echo TEXT_PRODUCTS_NAME; ?></td>
        <td class="main"><?php echo tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('products_name[' . $languages[$i]['id'] . ']', (isset($products_name[$languages[$i]['id']]) ? stripslashes($products_name[$languages[$i]['id']]) : tep_get_products_name($pInfo->products_id, $languages[$i]['id']))); ?></td>
      </tr>
<?php
}
?>
      <tr>
        <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr bgcolor="#ebebff">
        <td class="main"><?php echo TEXT_PRODUCTS_TAX_CLASS; ?></td>
        <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_pull_down_menu('products_tax_class_id', $tax_class_array, $pInfo->products_tax_class_id, 'onchange="updateGross()"'); ?></td>
      </tr>
      <tr bgcolor="#ebebff">
        <td class="main"><?php echo TEXT_PRODUCTS_PRICE_NET; ?></td>
        <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_price', $pInfo->products_price, 'onKeyUp="updateGross()"'); ?></td>
      </tr>
      <tr bgcolor="#ebebff">
        <td class="main"><?php echo TEXT_PRODUCTS_PRICE_GROSS; ?></td>
        <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_price_gross', $pInfo->products_price, 'OnKeyUp="updateNet()"'); ?></td>
      </tr>
      <tr>
        <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<script type="text/javascript"><!--
updateGross();
//--></script>
<?php
for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
     
<?php
}
?>
      <tr>
        <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo TEXT_PRODUCTS_QUANTITY; ?></td>
        <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_quantity', $pInfo->products_quantity); ?></td>
      </tr>
      <tr>
        <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo TEXT_PRODUCTS_MODEL; ?></td>
        <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_model', $pInfo->products_model); ?></td>
      </tr>
      <tr>
        <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
            <td class="main" valign="top"><?php echo TEXT_PRODUCTS_IMAGE; ?></td>
            <td class="main" style="padding-left: 30px;">
              <div><?php echo '<strong>' . TEXT_PRODUCTS_MAIN_IMAGE . ' <small>(' . SMALL_IMAGE_WIDTH . ' x ' . SMALL_IMAGE_HEIGHT . 'px)</small></strong><br />' . (tep_not_null($pInfo->products_image) ? '<a href="' . DIR_WS_CATALOG_IMAGES . $pInfo->products_image . '" target="_blank">' . $pInfo->products_image . '</a> &#124; ' : '') . tep_draw_file_field('products_image'); ?></div>

              <ul id="piList">
<?php
    $pi_counter = 0;

    foreach ($pInfo->products_larger_images as $pi) {
      $pi_counter++;

      echo '                <li id="piId' . $pi_counter . '" class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s" style="float: right;"></span><a href="#" onclick="showPiDelConfirm(' . $pi_counter . ');return false;" class="ui-icon ui-icon-trash" style="float: right;"></a><strong>' . TEXT_PRODUCTS_LARGE_IMAGE . '</strong><br />' . tep_draw_file_field('products_image_large_' . $pi['id']) . '<br /><a href="' . DIR_WS_CATALOG_IMAGES . $pi['image'] . '" target="_blank">' . $pi['image'] . '</a><br /><br />' . TEXT_PRODUCTS_LARGE_IMAGE_HTML_CONTENT . '<br />' . tep_draw_textarea_field('products_image_htmlcontent_' . $pi['id'], 'soft', '70', '3', $pi['htmlcontent']) . '</li>';
    }
?>
              </ul>

              <a href="#" onclick="addNewPiForm();return false;"><span class="ui-icon ui-icon-plus" style="float: left;"></span><?php echo TEXT_PRODUCTS_ADD_LARGE_IMAGE; ?></a>

<div id="piDelConfirm" title="<?php echo TEXT_PRODUCTS_LARGE_IMAGE_DELETE_TITLE; ?>">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><?php echo TEXT_PRODUCTS_LARGE_IMAGE_CONFIRM_DELETE; ?></p>
</div>

<style type="text/css">
#piList { list-style-type: none; margin: 0; padding: 0; }
#piList li { margin: 5px 0; padding: 2px; }
</style>

<script type="text/javascript">
$('#piList').sortable({
  containment: 'parent'
});

var piSize = <?php echo $pi_counter; ?>;

function addNewPiForm() {
  piSize++;

  $('#piList').append('<li id="piId' + piSize + '" class="ui-state-default"><span class="ui-icon ui-icon-arrowthick-2-n-s" style="float: right;"></span><a href="#" onclick="showPiDelConfirm(' + piSize + ');return false;" class="ui-icon ui-icon-trash" style="float: right;"></a><strong><?php echo TEXT_PRODUCTS_LARGE_IMAGE; ?></strong><br /><input type="file" name="products_image_large_new_' + piSize + '" /><br /><br /><?php echo TEXT_PRODUCTS_LARGE_IMAGE_HTML_CONTENT; ?><br /><textarea name="products_image_htmlcontent_new_' + piSize + '" wrap="soft" cols="70" rows="3"></textarea></li>');
}

var piDelConfirmId = 0;

$('#piDelConfirm').dialog({
  autoOpen: false,
  resizable: false,
  draggable: false,
  modal: true,
  buttons: {
    'Delete': function() {
      $('#piId' + piDelConfirmId).effect('blind').remove();
      $(this).dialog('close');
    },
    Cancel: function() {
      $(this).dialog('close');
    }
  }
});

function showPiDelConfirm(piId) {
  piDelConfirmId = piId;

  $('#piDelConfirm').dialog('open');
}
</script>

        </td>
      </tr>
      <tr>
        <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
?>
      <tr>
        <td class="main"><?php if ($i == 0) echo TEXT_PRODUCTS_URL . '<br /><small>' . TEXT_PRODUCTS_URL_WITHOUT_HTTP . '</small>'; ?></td>
        <td class="main"><?php echo tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('products_url[' . $languages[$i]['id'] . ']', (isset($products_url[$languages[$i]['id']]) ? stripslashes($products_url[$languages[$i]['id']]) : tep_get_products_url($pInfo->products_id, $languages[$i]['id']))); ?></td>
      </tr>
<?php
}
?>
      <tr>
        <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo TEXT_PRODUCTS_WEIGHT; ?></td>
        <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_input_field('products_weight', $pInfo->products_weight); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo TEXT_PRODUCTS_MASTER_STATUS; ?></td>
        <td class="main"><?php echo tep_draw_separator('pixel_trans.gif', '24', '15') . '&nbsp;' . tep_draw_checkbox_field('products_master_status', '1', $out_master_status); ?></td>
      </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
    <tr>
      <td class="smallText" align="right"><?php echo tep_draw_hidden_field('products_date_added', (tep_not_null($pInfo->products_date_added) ? $pInfo->products_date_added : date('Y-m-d'))) . tep_image_submit('button_preview.gif', IMAGE_PREVIEW) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '')) . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
    </tr>
    </table>
<!-- Master Products EOF //-->

      <tr>
        <td class="main" align="right"><?php echo tep_draw_hidden_field('products_date_added', (tep_not_null($pInfo->products_date_added) ? $pInfo->products_date_added : date('Y-m-d'))) . tep_image_submit('button_preview.gif', IMAGE_PREVIEW) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>'; ?></td>
      </tr>
    </table></form>
<!-- /*** Begin Header Tags SEO ***/ //-->     
<?php
  } elseif ($action == 'new_product_preview') {
	  // begin bundled products
          function display_bundle($bundle_id, $bundle_price, $lid) {
            global $pInfo, $currencies, $_POST, $_GET;
          ?>
          <table border="0" width="95%" cellspacing="1" cellpadding="2" class="columnLeft">
            <tr class="menuBoxContent">
              <td>
                <table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="main" colspan="5"><b>
                    <?php
                  $bundle_sum = 0;
                  $bdata = array();
		              echo TEXT_PRODUCTS_BY_BUNDLE . "</b></td></tr>\n";
                  if ((isset($_GET['read']) && ($_GET['read'] == 'only')) || (isset($_GET['pID']) && ($_GET['pID'] != $bundle_id)) || (!isset($_GET['pID']) && is_numeric($bundle_id))) {
  		              $bundle_query = tep_db_query(" SELECT pd.products_name, pb.*, p.products_bundle, p.products_id, p.products_model, p.products_price, p.products_image FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON p.products_id=pd.products_id INNER JOIN " . TABLE_PRODUCTS_BUNDLES . " pb ON pb.subproduct_id=pd.products_id WHERE pb.bundle_id = " . (int)$bundle_id . " and language_id = '" . (int)$lid . "'");
  		              while ($bundle_data = tep_db_fetch_array($bundle_query)) {
  		                $bdata[] = $bundle_data;
  		              }
  		            } else {
                    for ($i=0, $n=100; $i<$n; $i++) {
                      if (isset($_POST['subproduct_' . $i . '_qty']) && $_POST['subproduct_' . $i . '_qty'] > 0) {
                        $tmp = array('bundle_id' => $bundle_id,
                                   'subproduct_id' => (int)$_POST['subproduct_' . $i . '_id'],
                                   'subproduct_qty' => (int)$_POST['subproduct_' . $i . '_qty']);
                        $bundle_query = tep_db_query(" SELECT pd.products_name, p.products_bundle, p.products_id, p.products_model, p.products_price, p.products_image FROM " . TABLE_PRODUCTS . " p INNER JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON p.products_id=pd.products_id WHERE p.products_id = " . (int)$_POST['subproduct_' . $i . '_id'] . " and language_id = '" . (int)$lid . "'");
      		              while ($bundle_data = tep_db_fetch_array($bundle_query)) {
  	    	                $bdata[] = array_merge($tmp, $bundle_data);
  		                  }
                      }                      
                    }
  		            }
  		            foreach ($bdata as $bundle_data) {
	                  echo "<tr><td class=main valign=top>" ;
	                  echo tep_image(DIR_WS_CATALOG_IMAGES . $bundle_data['products_image'], $bundle_data['products_name'], intval(SMALL_IMAGE_WIDTH / 2), intval(SMALL_IMAGE_HEIGHT / 2), 'hspace="1" vspace="1"') . '</td>';
	                  // comment out the following line to hide the subproduct qty
	                  echo "<td class=main align=right><b>" . $bundle_data['subproduct_qty'] . "&nbsp;x&nbsp;</b></td>";
	                  echo  '<td class=main><a href="' . tep_catalog_href_link('product_info.php', 'products_id=' . (int)$bundle_data['products_id']) . '" target="_blank"><b>&nbsp;(' . $bundle_data['products_model'] . ') '  . $bundle_data['products_name'] . '</b></a>';
	                  if ($bundle_data['products_bundle'] == "yes") display_bundle($bundle_data['subproduct_id'], $bundle_data['products_price'], $lid);
	                  echo '</td>';
	                  echo '<td align=right class=main><b>&nbsp;' .  $currencies->display_price($bundle_data['products_price'], tep_get_tax_rate($pInfo->products_tax_class_id)) . "</b></td></tr>\n";
	                  $bundle_sum += $bundle_data['products_price']*$bundle_data['subproduct_qty'];
		              }
		              $bundle_saving = $bundle_sum - $bundle_price;
		              $bundle_sum = $currencies->display_price($bundle_sum, tep_get_tax_rate($pInfo->products_tax_class_id));
		              $bundle_saving =  $currencies->display_price($bundle_saving, tep_get_tax_rate($pInfo->products_tax_class_id));
		              // comment out the following line to hide the "saving" text
		              echo "<tr><td colspan=5 class=main><p><b>" . TEXT_RATE_COSTS . '&nbsp;' . $bundle_sum . '</b></td></tr><tr><td class=main colspan=5><font color="red"><b>' . TEXT_IT_SAVE . '&nbsp;' . $bundle_saving . "</font></b></td></tr>\n";
		            ?>
              </table></td>
            </tr>
          </table>
          <?php
          }
    // end bundled products

    if (tep_not_null($_POST)) {
      $pInfo = new objectInfo($_POST);
      $products_name = $_POST['products_name'];
      $products_description = $_POST['products_description'];
      $products_head_title_tag = $_POST['products_head_title_tag'];
      $products_head_desc_tag = $_POST['products_head_desc_tag'];
      $products_head_keywords_tag = $_POST['products_head_keywords_tag'];
	  // Start Products Specifications
      $products_tab_1 = $_POST['products_tab_1'];
      $products_tab_2 = $_POST['products_tab_2'];
      $products_tab_3 = $_POST['products_tab_3'];
      $products_tab_4 = $_POST['products_tab_4'];
      $products_tab_5 = $_POST['products_tab_5'];
      $products_tab_6 = $_POST['products_tab_6'];
// End Products Specifications
	  
      $products_url = $_POST['products_url'];
    } else {
      $product_query = tep_db_query("select p.products_id, pd.language_id, pd.products_name, pd.products_description, pd.products_head_title_tag, pd.products_head_desc_tag,  p.vendors_prod_id, pd.products_head_keywords_tag, pd.products_url, p.vendors_product_price, p.products_quantity, p.vendors_prod_comments, p.products_model, p.products_image, p.products_image_med, p.products_image_lrg, p.products_image_sm_1, p.products_image_xl_1, p.products_image_sm_2, p.products_image_xl_2, p.products_image_sm_3, p.products_image_xl_3, p.products_image_sm_4, p.products_image_xl_4, p.products_image_sm_5, p.products_image_xl_5, p.products_image_sm_6, p.products_price, p.products_weight, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.products_bundle, p.sold_in_bundle_only,  p.vendors_id, p.manufacturers_id  from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and p.products_id = '" . (int)$_GET['pID'] . "'");
	 
	 // Master Products
      $product_query = tep_db_query("select p.products_id, pd.language_id, pd.products_name, pd.products_description, pd.products_url, p.products_quantity, p.products_model, p.products_image, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.manufacturers_id, p.products_master  from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = pd.products_id and p.products_id = '" . (int)$_GET['pID'] . "'");
// Master Products EOF

	 
	 
	 
	  $product = tep_db_fetch_array($product_query); 
 /*** End Header Tags SEO ***/

      $pInfo = new objectInfo($product);
      $products_image_name = $pInfo->products_image;
    }

    
    // Master Products
    if (!isset($pInfo->products_listing_status)) $pInfo->products_listing_status = '1';
      switch ($pInfo->products_listing_status) {
        case '0': $in_listing_status = false; $out_listing_status = true; break;
        case '1':
        default: $in_listing_status = true; $out_listing_status = false;
      }

    if (!isset($pInfo->products_master_status)) $pInfo->products_master_status = '1';
      switch ($pInfo->products_master_status) {
        case '0': $in_master_status = false; $out_master_status = true; break;
        case '1':
        default: $in_master_status = false; $out_master_status = false;
     }
// Master Products EOF
    
    $form_action = (isset($_GET['pID'])) ? 'update_product' : 'insert_product';

    echo tep_draw_form($form_action, FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '') . '&action=' . $form_action, 'post', 'enctype="multipart/form-data"');

    $languages = tep_get_languages();
    for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
      if (isset($_GET['read']) && ($_GET['read'] == 'only')) {
        $pInfo->products_name = tep_get_products_name($pInfo->products_id, $languages[$i]['id']);
        $pInfo->products_description = tep_get_products_description($pInfo->products_id, $languages[$i]['id']);
       // Start Products Specifications
        $products_tabs = tep_get_products_tabs ($pInfo->products_id, $languages[$i]['id']);
        $pInfo->products_tab_1 = $products_tabs['1'];
        $pInfo->products_tab_2 = $products_tabs['2'];
        $pInfo->products_tab_3 = $products_tabs['3'];
        $pInfo->products_tab_4 = $products_tabs['4'];
        $pInfo->products_tab_5 = $products_tabs['5'];
        $pInfo->products_tab_6 = $products_tabs['6'];
// End Products Specifications
	   
	    $pInfo->products_url = tep_get_products_url($pInfo->products_id, $languages[$i]['id']);
      } else {
        $pInfo->products_name = tep_db_prepare_input($products_name[$languages[$i]['id']]);
        $pInfo->products_description = tep_db_prepare_input($products_description[$languages[$i]['id']]);
        $pInfo->products_url = tep_db_prepare_input($products_url[$languages[$i]['id']]);
      }
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . $pInfo->products_name; ?></td>
          <?php  //MVS start?>
            <td class="pageHeading" align="right"><?php echo TEXT_VENDORS_PRODUCT_PRICE_TITLE . $currencies->format($pInfo->products_price); ?></td>
            <td class="pageHeading" align="right"><?php echo TEXT_VENDORS_PRICE_TITLE . $currencies->format($pInfo->vendors_product_price); ?></td>
          </tr>
<?php //MVS end?>
            <td class="pageHeading" align="right"><?php echo $currencies->format($pInfo->products_price); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <!--BOF UltraPics-->
<!--BOF Original--><!--
        <td class="main"><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $products_image_name, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="right" hspace="5" vspace="5"') . $pInfo->products_description; ?></td>
--><!--EOF Original-->
<!-- BOF Bundled Products-->          
      <tr>
        <td class="main">
          <?php
          $pid = (isset($_GET['pID']) ? $_GET['pID'] : $pInfo->products_id);
          if ($pInfo->products_bundle == "yes") {
            display_bundle($pid, $pInfo->products_price, $languages[$i]['id'], $languages[$i]['directory']);
          }
          ?>
        </td>
        <?php 
      echo tep_image(DIR_WS_CATALOG_IMAGES . $products_image_name, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="right" hspace="5" vspace="5"') . $pInfo->products_description; 
// Products Specifications
     // include (DIR_WS_MODULES . FILENAME_PRODUCTS_SPECIFICATIONS);
?>
      </tr>
      <tr>
        <td class="main">
          <?php
          if ($pInfo->sold_in_bundle_only == "yes") {
            echo '<b>' . TEXT_SOLD_IN_BUNDLE . '</b><blockquote>';
            $bquery = tep_db_query('select bundle_id from ' . TABLE_PRODUCTS_BUNDLES . ' where subproduct_id = ' . (int)$pid);
            while ($bid = tep_db_fetch_array($bquery)) {
              $binfo_query = tep_db_query('select p.products_model, pd.products_name from ' . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_id = '" . (int)$bid['bundle_id'] . "' and pd.products_id = p.products_id and pd.language_id = " . (int)$languages[$i]['id']);
              $binfo = tep_db_fetch_array($binfo_query);
              echo '<a href="' . tep_catalog_href_link('product_info.php', 'products_id=' . (int)$bid['bundle_id']) . '" target="_blank">[' . $binfo['products_model'] . '] ' . $binfo['products_name'] . '</a><br />';
            }
            echo '</blockquote>';
          }
          ?>
        </td>
      </tr>
      <!-- EOF Bundled Products-->

        <td class="main">
              <?php if (ULTIMATE_ADDITIONAL_IMAGES == 'enable') { ?><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $products_image_lrg_name, TEXT_PRODUCTS_IMAGE . ' ' . $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="right" hspace="5" vspace="5"'); ?><?php } ?>
              <?php if (ULTIMATE_ADDITIONAL_IMAGES == 'enable') { ?><?php echo tep_image(DIR_WS_CATALOG_IMAGES . $products_image_med_name, TEXT_PRODUCTS_IMAGE . ' ' . $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="right" hspace="5" vspace="5"'); ?><?php } ?>
              <?php echo tep_image(DIR_WS_CATALOG_IMAGES . $products_image_name, TEXT_PRODUCTS_IMAGE . ' ' . $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="right" hspace="5" vspace="5"'); ?>
              <?php echo $pInfo->products_description . '<br><br><center>'; ?>
              <?php if (ULTIMATE_ADDITIONAL_IMAGES == 'enable') { ?>
              <?php echo tep_image(DIR_WS_CATALOG_IMAGES . $products_image_sm_1_name, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="center" hspace="5" vspace="5"'); ?>
              <?php echo tep_image(DIR_WS_CATALOG_IMAGES . $products_image_sm_2_name, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="center" hspace="5" vspace="5"'); ?>
              <?php echo tep_image(DIR_WS_CATALOG_IMAGES . $products_image_sm_3_name, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="center" hspace="5" vspace="5"') . '<br>'; ?>
              <?php echo tep_image(DIR_WS_CATALOG_IMAGES . $products_image_sm_4_name, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="center" hspace="5" vspace="5"'); ?>
              <?php echo tep_image(DIR_WS_CATALOG_IMAGES . $products_image_sm_5_name, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="center" hspace="5" vspace="5"'); ?>
              <?php echo tep_image(DIR_WS_CATALOG_IMAGES . $products_image_sm_6_name, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'align="center" hspace="5" vspace="5"') . '<br>'; ?>
              <?php } ?>
        </td>
<!--EOF UltraPics-->      </tr>
<?php
      if ($pInfo->products_url) {
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
      <tr>
        <td class="main"><?php echo sprintf(TEXT_PRODUCT_MORE_INFORMATION, $pInfo->products_url); ?></td>
      </tr>
<?php
      }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
      if ($pInfo->products_date_available > date('Y-m-d')) {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_PRODUCT_DATE_AVAILABLE, tep_date_long($pInfo->products_date_available)); ?></td>
      </tr>
<?php
      } else {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_PRODUCT_DATE_ADDED, tep_date_long($pInfo->products_date_added)); ?></td>
      </tr>
<?php
      }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
      </tr>
<?php
    }

    if (isset($_GET['read']) && ($_GET['read'] == 'only')) {
      if (isset($_GET['origin'])) {
        $pos_params = strpos($_GET['origin'], '?', 0);
        if ($pos_params != false) {
          $back_url = substr($_GET['origin'], 0, $pos_params);
          $back_url_params = substr($_GET['origin'], $pos_params + 1);
        } else {
          $back_url = $_GET['origin'];
          $back_url_params = '';
        }
      } else {
        $back_url = FILENAME_CATEGORIES;
        $back_url_params = 'cPath=' . $cPath . '&pID=' . $pInfo->products_id;
      }
?>
      <tr>
        <td align="right"><?php echo '<a href="' . tep_href_link($back_url, $back_url_params, 'NONSSL') . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>'; ?></td>
      </tr>
<?php
    } else {
?>
      <tr>
        <td align="right" class="smallText">
<?php
/* Re-Post all POST'ed variables */
      reset($_POST);
      while (list($key, $value) = each($_POST)) {
       // BOF Separate Pricing per Customer
        if (is_array($value)) {
          while (list($k, $v) = each($value)) {
          echo tep_draw_hidden_field($key . '[' . $k . ']', htmlspecialchars(stripslashes($v)));
          }
        } else {
// EOF Separate Pricing per Customer



		
          echo tep_draw_hidden_field($key, htmlspecialchars(stripslashes($value)));
        }
      }
      /*** Begin Header Tags SEO ***/
      $languages = tep_get_languages();
      for ($i=0, $n=sizeof($languages); $i<$n; $i++) {
        echo tep_draw_hidden_field('products_name[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($products_name[$languages[$i]['id']])));
        echo tep_draw_hidden_field('products_description[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($products_description[$languages[$i]['id']])));
        echo tep_draw_hidden_field('products_head_title_tag[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($products_head_title_tag[$languages[$i]['id']])));
        echo tep_draw_hidden_field('products_head_desc_tag[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($products_head_desc_tag[$languages[$i]['id']])));
        echo tep_draw_hidden_field('products_head_keywords_tag[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($products_head_keywords_tag[$languages[$i]['id']])));
        echo tep_draw_hidden_field('products_url[' . $languages[$i]['id'] . ']', htmlspecialchars(stripslashes($products_url[$languages[$i]['id']])));
// Start Products Specifications
        echo tep_draw_hidden_field ('products_tab_1[' . $languages[$i]['id'] . ']', htmlspecialchars (stripslashes ($products_tab_1[$languages[$i]['id']])));
        echo tep_draw_hidden_field ('products_tab_2[' . $languages[$i]['id'] . ']', htmlspecialchars (stripslashes ($products_tab_2[$languages[$i]['id']])));
        echo tep_draw_hidden_field ('products_tab_3[' . $languages[$i]['id'] . ']', htmlspecialchars (stripslashes ($products_tab_3[$languages[$i]['id']])));
        echo tep_draw_hidden_field ('products_tab_4[' . $languages[$i]['id'] . ']', htmlspecialchars (stripslashes ($products_tab_4[$languages[$i]['id']])));
        echo tep_draw_hidden_field ('products_tab_5[' . $languages[$i]['id'] . ']', htmlspecialchars (stripslashes ($products_tab_5[$languages[$i]['id']])));
        echo tep_draw_hidden_field ('products_tab_6[' . $languages[$i]['id'] . ']', htmlspecialchars (stripslashes ($products_tab_6[$languages[$i]['id']])));

        $specifications_query_raw = "select s.specifications_id
                                     from " . TABLE_SPECIFICATION . " s, 
                                          " . TABLE_SPECIFICATIONS_TO_CATEGORIES . " sg2c
                                     where sg2c.specification_group_id = s.specification_group_id 
                                       and sg2c.categories_id = '" . (int) $current_category_id . "'
                                   ";
        // print $specifications_query_raw . "<br>\n";
        $specifications_query = tep_db_query ($specifications_query_raw);
        while ($specifications = tep_db_fetch_array ($specifications_query) ) {
          $specification_id = $specifications['specifications_id'];
          $specification = $_POST['products_specification'][$specification_id][$languages[$i]['id']];
          if (is_array ($specification) ) {
            $value_number = 0;
            foreach ($specification as $each_specification) {
              echo tep_draw_hidden_field ('products_specification[' . $specification_id . '][' . $languages[$i]['id'] . '][' . $value_number . ']', htmlspecialchars (stripslashes ($each_specification) ) ) . "\n";
              $value_number++;
            }
          } else {
            echo tep_draw_hidden_field ('products_specification[' . $specification_id . '][' . $languages[$i]['id'] . ']', htmlspecialchars (stripslashes ($specification) ) ) . "\n";
          }
        }
// End Products Specifications
      
}       
      /*** End Header Tags SEO ***/ 
      echo tep_draw_hidden_field('products_image', stripslashes($products_image_name));
	  //BOF UltraPics
      echo tep_draw_hidden_field('products_image_med', stripslashes($products_image_med_name));
      echo tep_draw_hidden_field('products_image_lrg', stripslashes($products_image_lrg_name));
      echo tep_draw_hidden_field('products_image_sm_1', stripslashes($products_image_sm_1_name));
      echo tep_draw_hidden_field('products_image_xl_1', stripslashes($products_image_xl_1_name));
      echo tep_draw_hidden_field('products_image_sm_2', stripslashes($products_image_sm_2_name));
      echo tep_draw_hidden_field('products_image_xl_2', stripslashes($products_image_xl_2_name));
      echo tep_draw_hidden_field('products_image_sm_3', stripslashes($products_image_sm_3_name));
      echo tep_draw_hidden_field('products_image_xl_3', stripslashes($products_image_xl_3_name));
      echo tep_draw_hidden_field('products_image_sm_4', stripslashes($products_image_sm_4_name));
      echo tep_draw_hidden_field('products_image_xl_4', stripslashes($products_image_xl_4_name));
      echo tep_draw_hidden_field('products_image_sm_5', stripslashes($products_image_sm_5_name));
      echo tep_draw_hidden_field('products_image_xl_5', stripslashes($products_image_xl_5_name));
      echo tep_draw_hidden_field('products_image_sm_6', stripslashes($products_image_sm_6_name));
      echo tep_draw_hidden_field('products_image_xl_6', stripslashes($products_image_xl_6_name));
//EOF UltraPics

      echo tep_image_submit('button_back.gif', IMAGE_BACK, 'name="edit"') . '&nbsp;&nbsp;';

      if (isset($_GET['pID'])) {
        echo tep_image_submit('button_update.gif', IMAGE_UPDATE);
      } else {
        echo tep_image_submit('button_insert.gif', IMAGE_INSERT);
      }
      echo '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . (isset($_GET['pID']) ? '&pID=' . $_GET['pID'] : '')) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>';
?></td>
      </tr>
    </table></form>
<?php
    }
  } else {
?>
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', 1, HEADING_IMAGE_HEIGHT); ?></td>
            <td align="right"><table border="0" width="100%" cellspacing="0" cellpadding="0">
              <tr>
                <td class="smallText" align="right">

<?php
// BOF: KategorienAdmin / OLISWISS
  if ($admin_groups_id == 1) {
    echo tep_draw_form('search', FILENAME_CATEGORIES, '', 'get');
    echo HEADING_TITLE_SEARCH . ' ' . tep_draw_input_field('search');
    echo '</form>';
  }
// EOF: KategorienAdmin / OLISWISS
?>
                </td>
              </tr>
              <tr>
                <td class="smallText" align="right">
<?php
// BOF: KategorienAdmin / OLISWISS
//  echo tep_draw_form('goto', FILENAME_CATEGORIES, '', 'get');
//  echo HEADING_TITLE_GOTO . ' ' . tep_draw_pull_down_menu('cPath', tep_get_category_tree(), $current_category_id, 'onChange="this.form.submit();"');
//  echo '</form>';
  if (is_array($admin_cat_access_array_cats) && (in_array("ALL",$admin_cat_access_array_cats)== false) && (pos($admin_cat_access_array_cats)!= "")) {
    echo tep_draw_form('goto', FILENAME_CATEGORIES, '', 'get');
    echo HEADING_TITLE_GOTO . ' ' . tep_draw_pull_down_menu('cPath', tep_get_category_tree('','','','',$admin_cat_access_array_cats), $current_category_id, 'onChange="this.form.submit();"');
    echo '</form>';
  } else if (in_array("ALL",$admin_cat_access_array_cats)== true) { //nur Top-ADMIN
    echo tep_draw_form('goto', FILENAME_CATEGORIES, '', 'get');
    echo HEADING_TITLE_GOTO . ' ' . tep_draw_pull_down_menu('cPath', tep_get_category_tree(), $current_category_id, 'onChange="this.form.submit();"');
    echo '</form>';	
  }
// EOF: KategorienAdmin / OLISWISS
?>
                </td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_CATEGORIES_PRODUCTS; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo TABLE_HEADING_STATUS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
    $categories_count = 0;
    $rows = 0;
    if (isset($_GET['search'])) {
      $search = tep_db_prepare_input($_GET['search']);

     /*** Begin Header Tags SEO ***/
      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified, cd.categories_htc_title_tag, cd.categories_htc_desc_tag, cd.categories_htc_keywords_tag, cd.categories_htc_description from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' and cd.categories_name like '%" . tep_db_input($search) . "%' order by c.sort_order, cd.categories_name");
    } else {
      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified, cd.categories_htc_title_tag, cd.categories_htc_desc_tag, cd.categories_htc_keywords_tag, cd.categories_htc_description from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by c.sort_order, cd.categories_name");
    /*** End Header Tags SEO ***/    }
  
    
    // BOF: KategorienAdmin / OLISWISS
    if ($admin_cat_access == "ALL") {
      $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$current_category_id . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by c.sort_order, cd.categories_name");
    } else if ($admin_cat_access == ""){
      $categories_query = tep_db_query("");
    } else {
   $categories_query = tep_db_query("select c.categories_id, cd.categories_name, c.categories_image, c.parent_id, c.sort_order, c.date_added, c.last_modified from " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd where c.parent_id = '" . (int)$admin_cat_access . "' and c.categories_id = cd.categories_id and cd.language_id = '" . (int)$languages_id . "' order by c.sort_order, cd.categories_name");
   }
// EOF: KategorienAdmin / OLISWISS
    
    
    
    
    
    while ($categories = tep_db_fetch_array($categories_query)) {
      $categories_count++;
      $rows++;

// Get parent_id for subcategories if search
      if (isset($_GET['search'])) $cPath= $categories['parent_id'];

      if ((!isset($_GET['cID']) && !isset($_GET['pID']) || (isset($_GET['cID']) && ($_GET['cID'] == $categories['categories_id']))) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
        $category_childs = array('childs_count' => tep_childs_in_category_count($categories['categories_id']));
        $category_products = array('products_count' => tep_products_in_category_count($categories['categories_id']));

        $cInfo_array = array_merge($categories, $category_childs, $category_products);
        $cInfo = new objectInfo($cInfo_array);
      }
// BOF: KategorienAdmin / OLISWISS
     if ($admin_groups_id == 1 || in_array($categories['categories_id'],$admin_cat_access_array_cats) || $categories['parent_id'] != 0) {
       if ($admin_groups_id == 1 || in_array($_GET['cPath'],$admin_cat_access_array_cats) || in_array($categories['categories_id'],$admin_cat_access_array_cats)) {
// EOF: KategorienAdmin / OLISWISS




      if (isset($cInfo) && is_object($cInfo) && ($categories['categories_id'] == $cInfo->categories_id) ) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, tep_get_path($categories['categories_id'])) . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories['categories_id']) . '\'">' . "\n";
      }
?>
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, tep_get_path($categories['categories_id'])) . '">' . tep_image(DIR_WS_ICONS . 'folder.gif', ICON_FOLDER) . '</a>&nbsp;<b>' . $categories['categories_name'] . '</b>'; ?></td>
                <td class="dataTableContent" align="center">&nbsp;</td>
                <td class="dataTableContent" align="right"><?php if (isset($cInfo) && is_object($cInfo) && ($categories['categories_id'] == $cInfo->categories_id) ) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $categories['categories_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
// BOF: KategorienAdmin / OLISWISS
       }
     }
// EOF: KategorienAdmin / OLISWISS

    }

    $products_count = 0;
    if (isset($_GET['search'])) {
     $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_quantity, p.products_image, p.products_price, p.vendors_product_price, p.vendors_prod_comments, p.products_cost, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_master, p.products_master_status, p.products_listing_status, p.products_status, p2c.categories_id from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and pd.products_name like '%" . tep_db_input($search) . "%' order by p.products_master");
   } else {
        $products_query = tep_db_query("select p.products_id, pd.products_name, p.products_quantity, p.products_image, p.products_price, p.vendors_product_price, p.vendors_prod_comments, p.products_cost, p.products_date_added, p.products_last_modified, p.products_date_available, p.products_status, p.products_master, p.products_master_status, p.products_listing_status from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where p.products_id = pd.products_id and pd.language_id = '" . (int)$languages_id . "' and p.products_id = p2c.products_id and p2c.categories_id = '" . (int)$current_category_id . "' order by p.products_master");
   }
   while ($products = tep_db_fetch_array($products_query)) {
      $products_count++;
      $rows++;




// Get categories_id for product if search
      if (isset($_GET['search'])) $cPath = $products['categories_id'];

      if ( (!isset($_GET['pID']) && !isset($_GET['cID']) || (isset($_GET['pID']) && ($_GET['pID'] == $products['products_id']))) && !isset($pInfo) && !isset($cInfo) && (substr($action, 0, 3) != 'new')) {
// find out the rating average from customer reviews
        $reviews_query = tep_db_query("select (avg(reviews_rating) / 5 * 100) as average_rating from " . TABLE_REVIEWS . " where products_id = '" . (int)$products['products_id'] . "'");
        $reviews = tep_db_fetch_array($reviews_query);
        $pInfo_array = array_merge($products, $reviews);
        $pInfo = new objectInfo($pInfo_array);
      }
// BOF: KategorienAdmin / OLISWISS
     if ($admin_groups_id == 1 || in_array($categories['categories_id'],$admin_cat_access_array_cats) || $cPath != 0) {
// EOF: KategorienAdmin / OLISWISS

      if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id) ) {
        echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products['products_id'] . '&action=new_product_preview&read=only') . '\'">' . "\n";
      } else {
        echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products['products_id']) . '\'">' . "\n";
      }
?>
<!-- Master Products -->
                <td class="dataTableContent"><?php echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products['products_id'] . '&action=new_product_preview') . '">' . tep_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW) . '</a>&nbsp;';
      if ($products['products_master_status'] == "1" && $products['products_master'] == "0") {
        echo '<span style="color: #800080;">' . $products['products_name'] . ' ('.TEXT_MASTER.')</span></td>';
//      } elseif ($products['products_master'] == "" || $products['products_master'] == "0") {
      } elseif (strpos(trim(str_replace('_', '', $products['products_master'])), ' ') !== false || trim(str_replace('_', '', $products['products_master'])) !=='0') {
        echo '<span style="color: #0080C0">' . $products['products_name'] . ' ('.TEXT_SLAVE.')</span></td>';
      } else {
        echo $products['products_name'] . '</td>';
      }
?>
<!-- Master Products EOF -->              <td class="dataTableContent" align="center">
<?php
      if ($products['products_status'] == '1') {
        echo tep_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, 'action=setflag&flag=0&pID=' . $products['products_id'] . '&cPath=' . $cPath) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_red_light.gif', IMAGE_ICON_STATUS_RED_LIGHT, 10, 10) . '</a>';
      } else {
        echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'action=setflag&flag=1&pID=' . $products['products_id'] . '&cPath=' . $cPath) . '">' . tep_image(DIR_WS_IMAGES . 'icon_status_green_light.gif', IMAGE_ICON_STATUS_GREEN_LIGHT, 10, 10) . '</a>&nbsp;&nbsp;' . tep_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10);
      }
?></td>
                <td class="dataTableContent" align="right"><?php if (isset($pInfo) && is_object($pInfo) && ($products['products_id'] == $pInfo->products_id)) { echo tep_image(DIR_WS_IMAGES . 'icon_arrow_right.gif', ''); } else { echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $products['products_id']) . '">' . tep_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
// BOF: KategorienAdmin / OLISWISS
      }
// EOF: KategorienAdmin / OLISWISS

    }

    $cPath_back = '';
    if (sizeof($cPath_array) > 0) {
      for ($i=0, $n=sizeof($cPath_array)-1; $i<$n; $i++) {
        if (empty($cPath_back)) {
          $cPath_back .= $cPath_array[$i];
        } else {
          $cPath_back .= '_' . $cPath_array[$i];
        }
      }
    }

    $cPath_back = (tep_not_null($cPath_back)) ? 'cPath=' . $cPath_back . '&' : '';
?>
              <tr>
                <td colspan="3"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                  <?php // BOF: KategorienAdmin / OLISWISS
	if($admin_groups_id == 1){
?>
                    <td class="smallText"><?php echo TEXT_CATEGORIES . '&nbsp;' . $categories_count . '<br>' . TEXT_PRODUCTS . '&nbsp;' . $products_count; ?></td>
                    <td align="right" class="smallText"><?php if (sizeof($cPath_array) > 0) echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, $cPath_back . 'cID=' . $current_category_id) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;'; if (!isset($_GET['search'])) echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&action=new_category') . '">' . tep_image_button('button_new_category.gif', IMAGE_NEW_CATEGORY) . '</a>&nbsp;<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&action=new_product') . '">' . tep_image_button('button_new_product.gif', IMAGE_NEW_PRODUCT) . '</a>'; ?>&nbsp;</td>
                    <?php
	} else {
?>
                    <td></td>
                    <td align="right" class="smallText">
                    <?php if (sizeof($cPath_array) > 0) echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, $cPath_back . 'cID=' . $current_category_id) . '">' . tep_image_button('button_back.gif', IMAGE_BACK) . '</a>&nbsp;';
                    if (!isset($_GET['search']) && strstr($admin_right_access,"CNEW")) echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&action=new_category') . '">' . tep_image_button('button_new_category.gif', IMAGE_NEW_CATEGORY) . '</a>&nbsp;'; 
                    if (!isset($_GET['search']) && strstr($admin_right_access,"PNEW") && $cInfo->parent_id !='0') echo '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&action=new_product') . '">' . tep_image_button('button_new_product.gif', IMAGE_NEW_PRODUCT) . '</a>'; ?>&nbsp;</td>

<!-- Master Products //-->
                    <td align="right" class="smallText">
                    <?php// if (sizeof($cPath_array) > 0) echo tep_draw_button(IMAGE_BACK, 'triangle-1-w', tep_href_link(FILENAME_CATEGORIES, $cPath_back . 'cID=' . $current_category_id)); if (!isset($_GET['search'])) echo tep_draw_button(IMAGE_NEW_CATEGORY, 'plus', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&action=new_category')) . tep_draw_button(IMAGE_NEW_MASTER, 'plus', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&action=new_master')) . tep_draw_button(IMAGE_NEW_PRODUCT, 'plus', tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&action=new_product'));***// ?>&nbsp;</td>
<!-- Master Products EOF //-->



<?php
	}
// EOF: KategorienAdmin / OLISWISS
?>

                  </tr>
                </table></td>
              </tr>
            </table></td>
<?php
    $heading = array();
    $contents = array();
    switch ($action) {
      case 'new_category':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_NEW_CATEGORY . '</b>');

        $contents = array('form' => tep_draw_form('newcategory', FILENAME_CATEGORIES, 'action=insert_category&cPath=' . $cPath, 'post', 'enctype="multipart/form-data"'));
        $contents[] = array('text' => TEXT_NEW_CATEGORY_INTRO);

        $category_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $category_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_name[' . $languages[$i]['id'] . ']');
        /*** Begin Header Tags SEO ***/
          $category_htc_title_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_htc_title_tag[' . $languages[$i]['id'] . ']');
          $category_htc_desc_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_htc_desc_tag[' . $languages[$i]['id'] . ']');
          $category_htc_keywords_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_htc_keywords_tag[' . $languages[$i]['id'] . ']');
          $category_htc_description_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('categories_htc_description[' . $languages[$i]['id'] . ']', 'hard', 30, 5, '');
          /*** End Header Tags SEO ***/
		}

        $contents[] = array('text' => '<br>' . TEXT_CATEGORIES_NAME . $category_inputs_string);
        $contents[] = array('text' => '<br>' . TEXT_CATEGORIES_IMAGE . '<br>' . tep_draw_file_field('categories_image'));
        $contents[] = array('text' => '<br>' . TEXT_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', '', 'size="2"'));
		/*** Begin Header Tags SEO ***/
        $contents[] = array('text' => '<br>' . 'Header Tags Category Title' . $category_htc_title_string);
        $contents[] = array('text' => '<br>' . 'Header Tags Category Description' . $category_htc_desc_string);
        $contents[] = array('text' => '<br>' . 'Header Tags Category Keywords' . $category_htc_keywords_string);
        $contents[] = array('text' => '<br>' . 'Header Tags Categories Description' . $category_htc_description_string);
        /*** End Header Tags SEO ***/
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'edit_category':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_EDIT_CATEGORY . '</b>');

        $contents = array('form' => tep_draw_form('categories', FILENAME_CATEGORIES, 'action=update_category&cPath=' . $cPath, 'post', 'enctype="multipart/form-data"') . tep_draw_hidden_field('categories_id', $cInfo->categories_id));
        $contents[] = array('text' => TEXT_EDIT_INTRO);

        $category_inputs_string = '';
        $languages = tep_get_languages();
        for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
          $category_inputs_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_name[' . $languages[$i]['id'] . ']', tep_get_category_name($cInfo->categories_id, $languages[$i]['id']));
		  /*** Begin Header Tags SEO ***/
          $category_htc_title_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_htc_title_tag[' . $languages[$i]['id'] . ']', tep_get_category_htc_title($cInfo->categories_id, $languages[$i]['id']));
          $category_htc_desc_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_htc_desc_tag[' . $languages[$i]['id'] . ']', tep_get_category_htc_desc($cInfo->categories_id, $languages[$i]['id']));
          $category_htc_keywords_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_input_field('categories_htc_keywords_tag[' . $languages[$i]['id'] . ']', tep_get_category_htc_keywords($cInfo->categories_id, $languages[$i]['id']));
          $category_htc_description_string .= '<br>' . tep_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']) . '&nbsp;' . tep_draw_textarea_field('categories_htc_description[' . $languages[$i]['id'] . ']', 'hard', 30, 5, tep_get_category_htc_description($cInfo->categories_id, $languages[$i]['id']));
          /*** End Header Tags SEO ***/
        }

        $contents[] = array('text' => '<br>' . TEXT_EDIT_CATEGORIES_NAME . $category_inputs_string);
        $contents[] = array('text' => '<br>' . tep_image(DIR_WS_CATALOG_IMAGES . $cInfo->categories_image, $cInfo->categories_name) . '<br>' . DIR_WS_CATALOG_IMAGES . '<br><b>' . $cInfo->categories_image . '</b>');
        $contents[] = array('text' => '<br>' . TEXT_EDIT_CATEGORIES_IMAGE . '<br>' . tep_draw_file_field('categories_image'));
        $contents[] = array('text' => '<br>' . TEXT_EDIT_SORT_ORDER . '<br>' . tep_draw_input_field('sort_order', $cInfo->sort_order, 'size="2"'));
		/*** Begin Header Tags SEO ***/
        $contents[] = array('text' => '<br>' . 'Header Tags Category Title' . $category_htc_title_string);
        $contents[] = array('text' => '<br>' . 'Header Tags Category Description' . $category_htc_desc_string);
        $contents[] = array('text' => '<br>' . 'Header Tags Category Keywords' . $category_htc_keywords_string);
        $contents[] = array('text' => '<br>' . 'Header Tags Categories Description' . $category_htc_description_string);
        /*** End Header Tags SEO ***/

        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_save.gif', IMAGE_SAVE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'delete_category':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_CATEGORY . '</b>');

        $contents = array('form' => tep_draw_form('categories', FILENAME_CATEGORIES, 'action=delete_category_confirm&cPath=' . $cPath) . tep_draw_hidden_field('categories_id', $cInfo->categories_id));
        $contents[] = array('text' => TEXT_DELETE_CATEGORY_INTRO);
        $contents[] = array('text' => '<br><b>' . $cInfo->categories_name . '</b>');
        if ($cInfo->childs_count > 0) $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_CHILDS, $cInfo->childs_count));
        if ($cInfo->products_count > 0) $contents[] = array('text' => '<br>' . sprintf(TEXT_DELETE_WARNING_PRODUCTS, $cInfo->products_count));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'move_category':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_MOVE_CATEGORY . '</b>');

        $contents = array('form' => tep_draw_form('categories', FILENAME_CATEGORIES, 'action=move_category_confirm&cPath=' . $cPath) . tep_draw_hidden_field('categories_id', $cInfo->categories_id));
        $contents[] = array('text' => sprintf(TEXT_MOVE_CATEGORIES_INTRO, $cInfo->categories_name));
        $contents[] = array('text' => '<br>' . sprintf(TEXT_MOVE, $cInfo->categories_name) . '<br>' . tep_draw_pull_down_menu('move_to_category_id', tep_get_category_tree(), $current_category_id));
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_move.gif', IMAGE_MOVE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'delete_product':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_DELETE_PRODUCT . '</b>');

        $contents = array('form' => tep_draw_form('products', FILENAME_CATEGORIES, 'action=delete_product_confirm&cPath=' . $cPath) . tep_draw_hidden_field('products_id', $pInfo->products_id));
        $contents[] = array('text' => TEXT_DELETE_PRODUCT_INTRO);
        $contents[] = array('text' => '<br><b>' . $pInfo->products_name . '</b>');

        $product_categories_string = '';
        $product_categories = tep_generate_category_path($pInfo->products_id, 'product');
        for ($i = 0, $n = sizeof($product_categories); $i < $n; $i++) {
          $category_path = '';
          for ($j = 0, $k = sizeof($product_categories[$i]); $j < $k; $j++) {
            $category_path .= $product_categories[$i][$j]['text'] . '&nbsp;&gt;&nbsp;';
          }
          $category_path = substr($category_path, 0, -16);
          $product_categories_string .= tep_draw_checkbox_field('product_categories[]', $product_categories[$i][sizeof($product_categories[$i])-1]['id'], true) . '&nbsp;' . $category_path . '<br>';
        }
        $product_categories_string = substr($product_categories_string, 0, -4);

        $contents[] = array('text' => '<br>' . $product_categories_string);
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_delete.gif', IMAGE_DELETE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'move_product':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_MOVE_PRODUCT . '</b>');

        $contents = array('form' => tep_draw_form('products', FILENAME_CATEGORIES, 'action=move_product_confirm&cPath=' . $cPath) . tep_draw_hidden_field('products_id', $pInfo->products_id));
        $contents[] = array('text' => sprintf(TEXT_MOVE_PRODUCTS_INTRO, $pInfo->products_name));
        $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENT_CATEGORIES . '<br><b>' . tep_output_generated_category_path($pInfo->products_id, 'product') . '</b>');
        // BOF: KategorienAdmin / OLISWISS
  if (is_array($admin_cat_access_array_cats) && (in_array("ALL",$admin_cat_access_array_cats)== false) && (pos($admin_cat_access_array_cats)!= "")) {
    $contents[] = array('text' => '<br>' . sprintf(TEXT_MOVE, $pInfo->products_name) . '<br>' . tep_draw_pull_down_menu('move_to_category_id', tep_get_category_tree('','','0','',$admin_cat_access_array_cats), $current_category_id));
  } else if (in_array("ALL",$admin_cat_access_array_cats)== true) { //nur Top-ADMIN
    $contents[] = array('text' => '<br>' . sprintf(TEXT_MOVE, $pInfo->products_name) . '<br>' . tep_draw_pull_down_menu('move_to_category_id', tep_get_category_tree(), $current_category_id));
  }
// EOF: KategorienAdmin / OLISWISS
$contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_move.gif', IMAGE_MOVE) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      case 'copy_to':
        $heading[] = array('text' => '<b>' . TEXT_INFO_HEADING_COPY_TO . '</b>');

        $contents = array('form' => tep_draw_form('copy_to', FILENAME_CATEGORIES, 'action=copy_to_confirm&cPath=' . $cPath) . tep_draw_hidden_field('products_id', $pInfo->products_id));
        $contents[] = array('text' => TEXT_INFO_COPY_TO_INTRO);
        $contents[] = array('text' => '<br>' . TEXT_INFO_CURRENT_CATEGORIES . '<br><b>' . tep_output_generated_category_path($pInfo->products_id, 'product') . '</b>');
        // BOF: KategorienAdmin / OLISWISS
  if (is_array($admin_cat_access_array_cats) && (in_array("ALL",$admin_cat_access_array_cats)== false) && (pos($admin_cat_access_array_cats)!= "")) {
    $contents[] = array('text' => '<br>' . TEXT_CATEGORIES . '<br>' . tep_draw_pull_down_menu('categories_id', tep_get_category_tree('','','0','',$admin_cat_access_array_cats), $current_category_id));
  } else if (in_array("ALL",$admin_cat_access_array_cats)== true) { //nur Top-ADMIN
    $contents[] = array('text' => '<br>' . TEXT_CATEGORIES . '<br>' . tep_draw_pull_down_menu('categories_id', tep_get_category_tree(), $current_category_id));
  }
// EOF: KategorienAdmin / OLISWISS        
$contents[] = array('text' => '<br>' . TEXT_HOW_TO_COPY . '<br>' . tep_draw_radio_field('copy_as', 'link', true) . ' ' . TEXT_COPY_AS_LINK . '<br>' . tep_draw_radio_field('copy_as', 'duplicate') . ' ' . TEXT_COPY_AS_DUPLICATE);
        $contents[] = array('align' => 'center', 'text' => '<br>' . tep_image_submit('button_copy.gif', IMAGE_COPY) . ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id) . '">' . tep_image_button('button_cancel.gif', IMAGE_CANCEL) . '</a>');
        break;
      default:
        if ($rows > 0) {
          if (isset($cInfo) && is_object($cInfo)) { // category info box contents
            $heading[] = array('text' => '<b>' . $cInfo->categories_name . '</b>');
// BOF: KategorienAdmin / OLISWISS
	    if ($admin_groups_id == 1) {
	    	if (tep_get_products_master_status($pInfo->products_id) !=1) {
              $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id . '&action=edit_category') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id . '&action=delete_category') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id . '&action=move_category') . '">' . tep_image_button('button_move.gif', IMAGE_MOVE) . '</a>');
	    
		 } else {
		 	  $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=new_master') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=delete_product') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=move_product') . '">' . tep_image_button('button_move.gif', IMAGE_MOVE) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=copy_to') . '">' . tep_image_button('button_copy_to.gif', IMAGE_COPY_TO) . '</a>');
			
         
            }
// Master Products EOF
			
			} else {
	      if (strstr($admin_right_access,"CEDIT")) {  
	        $c_right_string .= ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id . '&action=edit_category') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a>';
	      }
	      if (strstr($admin_right_access,"CDELETE")) {
	      	$c_right_string .= ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id . '&action=delete_category') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a>';
	      }
	      if (strstr($admin_right_access,"CMOVE")) {
	        $c_right_string .= ' <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&cID=' . $cInfo->categories_id . '&action=move_category') . '">' . tep_image_button('button_move.gif', IMAGE_MOVE) . '</a>';
	      }
	    $contents[] = array('align' => 'center', 'text' => $c_right_string);
	    }
// EOF: KategorienAdmin / OLISWISS     
$contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($cInfo->date_added));
            if (tep_not_null($cInfo->last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($cInfo->last_modified));
            $contents[] = array('text' => '<br>' . tep_info_image($cInfo->categories_image, $cInfo->categories_name, HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT) . '<br>' . $cInfo->categories_image);
            $contents[] = array('text' => '<br>' . TEXT_SUBCATEGORIES . ' ' . $cInfo->childs_count . '<br>' . TEXT_PRODUCTS . ' ' . $cInfo->products_count);
          } elseif (isset($pInfo) && is_object($pInfo)) { // product info box contents
          //MVS start
            $vendors_query_2 = tep_db_query("select v.vendors_id, v.vendors_name from vendors v, products p where v.vendors_id=p.vendors_id and p.products_id='" . $pInfo->products_id . "'");
            while ($vendors_2 = tep_db_fetch_array($vendors_query_2)) {
              $current_vendor_name = $vendors_2['vendors_name'];
            }     
// MVS end
            $heading[] = array('text' => '<b>' . tep_get_products_name($pInfo->products_id, $languages_id) . '</b>');

            //BOF UltraPics
//BOF Original
/*
            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=new_product') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=delete_product') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=move_product') . '">' . tep_image_button('button_move.gif', IMAGE_MOVE) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=copy_to') . '">' . tep_image_button('button_copy_to.gif', IMAGE_COPY_TO) . '</a>');
*/
//EOF Original
            $contents[] = array('align' => 'center', 'text' => '<a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=new_product') . '">' . tep_image_button('button_edit.gif', IMAGE_EDIT) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=delete_product') . '">' . tep_image_button('button_delete.gif', IMAGE_DELETE) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=move_product') . '">' . tep_image_button('button_move.gif', IMAGE_MOVE) . '</a> <a href="' . tep_href_link(FILENAME_CATEGORIES, 'cPath=' . $cPath . '&pID=' . $pInfo->products_id . '&action=copy_to') . '">' . tep_image_button('button_copy_to.gif', IMAGE_COPY_TO) . '</a>  <a target="_blank" href="' . HTTP_CATALOG_SERVER . DIR_WS_CATALOG . 'product_info.php?products_id=' . $pInfo->products_id . '">' . tep_image_button('button_preview.gif', IMAGE_PREVIEW) . '</a>');
//EOF UltraPics            $contents[] = array('text' => '<br>' . TEXT_DATE_ADDED . ' ' . tep_date_short($pInfo->products_date_added));
            if (tep_not_null($pInfo->products_last_modified)) $contents[] = array('text' => TEXT_LAST_MODIFIED . ' ' . tep_date_short($pInfo->products_last_modified));
            if (date('Y-m-d') < $pInfo->products_date_available) $contents[] = array('text' => TEXT_DATE_AVAILABLE . ' ' . tep_date_short($pInfo->products_date_available));
            $contents[] = array('text' => '<br>' . tep_info_image($pInfo->products_image, $pInfo->products_name, SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '<br>' . $pInfo->products_image);
             //MVS start
            $contents[] = array('text' => '<br>' . TEXT_PRODUCTS_PRICE_INFO . '<b> ' . $currencies->format($pInfo->products_price) . '</b><br>' . TEXT_VENDOR . '<b>' . $current_vendor_name . '</b><br>' . TEXT_VENDORS_PRODUCT_PRICE_INFO . '<b>' . $currencies->format($pInfo->vendors_product_price) . '</b><br>' . TEXT_PRODUCTS_QUANTITY_INFO . ' <b>' . $pInfo->products_quantity . '</b>');
//MVS end
             $contents[] = array('text' => '<br>' . TEXT_PRODUCTS_COST_INFO . ' ' . $currencies->format($pInfo->products_cost) . '<br>' . TEXT_PRODUCTS_PRICE_INFO . ' ' . $currencies->format($pInfo->products_price) . '<br><br>' . TEXT_PRODUCTS_PROFIT_INFO . ' ' . $currencies->format($pInfo->products_price-$pInfo->products_cost) . '<br><br>' . TEXT_PRODUCTS_QUANTITY_INFO . ' ' . $pInfo->products_quantity); 
            $contents[] = array('text' => '<br>' . TEXT_PRODUCTS_AVERAGE_RATING . ' ' . number_format($pInfo->average_rating, 2) . '%');
          }
        } else { // create category/product info
          $heading[] = array('text' => '<b>' . EMPTY_CATEGORY . '</b>');

          $contents[] = array('text' => TEXT_NO_CHILD_CATEGORIES_OR_PRODUCTS);
        }
        break;
    }

    if ( (tep_not_null($heading)) && (tep_not_null($contents)) ) {
      echo '            <td width="25%" valign="top">' . "\n";

      $box = new box;
      echo $box->infoBox($heading, $contents);

      echo '            </td>' . "\n";
    }
?>
          </tr>
        </table></td>
      </tr>
    </table>
<?php
  }
?>


    </td>
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
