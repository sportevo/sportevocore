<?php
/*
  $Id: filenames.php,v 1.1 2003/06/20 00:18:30 hpdl Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
  Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2)

  Please note: DO NOT DELETE this file if disabling the above contribution.
  Edits are listed by number. Locate and modify as needed to disable the contribution.
*/

// BOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 1 of 1
// comment below lines to disable this contribution
define('FILENAME_COMPLETE_ORDER_LIST','orderlist.php');
  define('FILENAME_ADMIN_ACCOUNT', 'admin_account.php');
  define('FILENAME_ADMIN_FILES', 'admin_files.php');
  define('FILENAME_ADMIN_MEMBERS', 'admin_members.php');
  define('FILENAME_FORBIDDEN', 'forbidden.php');
  define('FILENAME_LOGIN', 'login.php');
  define('FILENAME_LOGOFF', 'logoff.php');
  define('FILENAME_PASSWORD_FORGOTTEN', 'password_forgotten.php');
// BOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 1 of 1

// define the filenames used in the project
  define('FILENAME_BACKUP', 'backup.php');
  define('FILENAME_BANNER_MANAGER', 'banner_manager.php');
  define('FILENAME_BANNER_STATISTICS', 'banner_statistics.php');
  define('FILENAME_CACHE', 'cache.php');
  define('FILENAME_CATALOG_ACCOUNT_HISTORY_INFO', 'account_history_info.php');
  define('FILENAME_CATEGORIES', 'categories.php');
  define('FILENAME_CONFIGURATION', 'configuration.php');
  define('FILENAME_COUNTRIES', 'countries.php');
  define('FILENAME_CURRENCIES', 'currencies.php');
  define('FILENAME_CUSTOMERS', 'customers.php');
  define('FILENAME_CUSTOMERS_EXPORT', 'customer_export.php');
  define('FILENAME_DEFAULT', 'index.php');
  define('FILENAME_DEFINE_LANGUAGE', 'define_language.php');
  define('FILENAME_FILE_MANAGER', 'file_manager.php');
  define('FILENAME_GEO_ZONES', 'geo_zones.php');
  define('FILENAME_LANGUAGES', 'languages.php');
  define('FILENAME_MAIL', 'mail.php');
  define('FILENAME_MANUFACTURERS', 'manufacturers.php');
  define('FILENAME_MODULES', 'modules.php');
  define('FILENAME_NEWSLETTERS', 'newsletters.php');
  define('FILENAME_ORDERS', 'orders.php');
  define('FILENAME_ORDERS_INVOICE', 'invoice.php');
  define('FILENAME_ORDERS_PACKINGSLIP', 'packingslip.php');
  define('FILENAME_ORDERS_STATUS', 'orders_status.php');
  define('FILENAME_POPUP_IMAGE', 'popup_image.php');
  define('FILENAME_PRODUCTS_ATTRIBUTES', 'products_attributes.php');
  define('FILENAME_PRODUCTS_EXPECTED', 'products_expected.php');
  define('FILENAME_REVIEWS', 'reviews.php');
  define('FILENAME_SERVER_INFO', 'server_info.php');
  define('FILENAME_SHIPPING_MODULES', 'shipping_modules.php');
  define('FILENAME_SPECIALS', 'specials.php');
  define('FILENAME_STATS_CUSTOMERS', 'stats_customers.php');
  define('FILENAME_STATS_PRODUCTS_PURCHASED', 'stats_sales_report2.php');
  define('FILENAME_STATS_PRODUCTS_VIEWED', 'stats_products_viewed.php');
  define('FILENAME_TAX_CLASSES', 'tax_classes.php');
  define('FILENAME_TAX_RATES', 'tax_rates.php');
  define('FILENAME_WHOS_ONLINE', 'whos_online.php');
  define('FILENAME_ZONES', 'zones.php');
  // BOF Separate Pricing Per Customer
  define('FILENAME_CUSTOMERS_GROUPS', 'customers_groups.php');
  define('FILENAME_ATTRIBUTES_GROUPS', 'attributes_groups.php');
// EOF Separate Pricing Per Customer

  // order editor
  define('FILENAME_ORDERS_EDIT', 'edit_orders.php');
  define('FILENAME_ORDERS_EDIT_ADD_PRODUCT', 'edit_orders_add_product.php');
  define('FILENAME_ORDERS_EDIT_AJAX', 'edit_orders_ajax.php');
   // end order editor
     /*** Begin Header Tags SEO ***/
  define('FILENAME_HEADER_TAGS_SEO', 'header_tags_seo.php');
  define('FILENAME_HEADER_TAGS_FILL_TAGS', 'header_tags_fill_tags.php');
  /*** End Header Tags SEO ***/
  define('FILENAME_PDF_PRICELIST', 'pdf_price_list.php'); // PDF Price list
 
  define('FILENAME_PDF_PRICELINK', '../pricelist/pricelist_2.pdf'); // PDF Price list
//kgt - discount coupons
  define('FILENAME_DISCOUNT_COUPONS','coupons.php');
  define('FILENAME_DISCOUNT_COUPONS_MANUAL', 'coupons_manual.html');
  define('FILENAME_DISCOUNT_COUPONS_EXCLUSIONS', 'coupons_exclusions.php');
  //end kgt - discount coupons
  
  //kgt - discount coupons report
	define('FILENAME_STATS_DISCOUNT_COUPONS', 'stats_discount_coupons.php');
  //end kgt - discount coupons report
define('FILENAME_QUICK_UPDATES', 'quick_updates.php');
define('FILENAME_MARGIN_REPORT', 'margin_report.php');
 define('FILENAME_MARGIN_REPORT2', 'margin_report2.php');  
 
//START STOCKVIEW
define('FILENAME_STOCK', 'stockview.php');
//END STOCKVIEW

// ### BEGIN ORDER MAKER ###
  define('FILENAME_CREATE_ORDER_PROCESS', 'create_order_process.php');
  define('FILENAME_CREATE_ORDER', 'create_order.php');
// ### END ORDER MAKER ###

// BOF ADMIN pdf invoice 1.6
define('FILENAME_PDF_INVOICE','pdf_invoice.php');
define('FILENAME_PDF_PACKINGSLIP','pdf_packingslip.php');
// EOF ADMIN pdf invoice 1.6
define('FILENAME_STATS_INVENTORY', 'inventory_report.php');

//++++ QT Pro: Begin Changed code
  define('FILENAME_STATS_LOW_STOCK_ATTRIB', 'stats_low_stock_attrib.php');
  define('FILENAME_STOCK', 'stock.php');
  define('FILENAME_QTPRODOCTOR', 'qtprodoctor.php');
//++++ QT Pro: End Changed Code
// Filename for Supplier's Area
define('FILENAME_SUPPLIER_S_CATEGORIES_PRODUCTS','../supplier/supplier_s_categories_products.php');
define('FILENAME_SUPPLIER_ORDERS','../supplier/supplier_s_orders.php');
define('FILENAME_SUPPLIER_STATISTIC','../supplier/supplier_s_statistic.php');
define('FILENAME_SUPPLIER_VIEWED','../supplier/supplier_s_viewed.php');
define('FILENAME_SUPPLIERSADMIN', 'suppliersadmin.php');
define('FILENAME_SUPPLIERAREA', '../supplier/supplierarea.php');
define('FILENAME_SUPPLIERS', '../supplier/suppliers.php');
//BOF Dynamic Sitemap
  define('FILENAME_SITEMAP', 'sitemap.php');
  define('FILENAME_CREATE_XML_SITEMAPS', 'create_xml_sitemaps.php');
//EOF Dynamic Sitemap

// BOF: Featured Products
  define('FILENAME_FEATURED', 'featured.php');
  define('FILENAME_FEATURED_PRODUCTS', 'featured_products.php');
  define('FILENAME_SELECT_FEATURED', 'select_featured.php');
// EOF: Featured Products

define('FILENAME_INVOICES', 'multi_invoices.php');
  define('FILENAME_INVOICES_ZONES', 'multi_invoices_zones.php');  
  define('FILENAME_INVOICES_COUNTRIES', 'multi_invoices_countries.php');  
  define('FILENAME_PACKINGSLIPS', 'multi_packingslips.php');
  define('FILENAME_PACKINGSLIPS_ZONES', 'multi_packingslips_zones.php');  
  define('FILENAME_PACKINGSLIPS_COUNTRIES', 'multi_packingslips_countries.php');  

//Options as Images
define ('FILENAME_OPTIONS_IMAGES', 'options_images.php');

  define('FILENAME_PRODUCT_SETS', 'product_sets.php');
  define('FILENAME_PRODUCT_SETS_AJAX', 'product_sets_ajax.php');
 define('FILENAME_SITEMONITOR_ADMIN', 'sitemonitor_admin.php');
  define('FILENAME_SITEMONITOR_CONFIG_SETUP', 'sitemonitor_configure_setup.php');
  define('FILENAME_SITEMONITOR_CONFIGURE', 'sitemonitor_configure.txt');
  
  define('FILENAME_QB_CONFIGURATION', 'qb_config.php');
define('FILENAME_QB_ADMIN', 'qb_admin.php');
define('FILENAME_QB_EXPORT', 'qb_export.php');
define('FILENAME_QB_CONFIG', 'qb_config.php');
define('FILENAME_QB_MAPPING', 'qb_mappings.php');
define('FILENAME_QB_LOGS', 'qb_logs.php');
//MVS start
  define('FILENAME_VENDORS', 'vendors.php');
  define('FILENAME_VENDOR_MODULES', 'vendor_modules.php');
  define('FILENAME_PRODS_VENDORS', 'prods_by_vendor.php');
  define('FILENAME_ORDERS_VENDORS', 'orders_by_vendor.php');
  define('FILENAME_VENDORS_EMAIL_SEND', 'vendor_email_send.php');
  define('FILENAME_MOVE_VENDORS', 'move_vendor_prods.php');
//MVS end

?>