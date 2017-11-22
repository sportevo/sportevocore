<?php
/*
  $Id: database_tables.php,v 1.1 2003/06/20 00:18:30 hpdl Exp $

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
  define('TABLE_ADMIN', 'admin');
  define('TABLE_ADMIN_FILES', 'admin_files');
  define('TABLE_ADMIN_GROUPS', 'admin_groups');
// EOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 1 of 1

// define the database table names used in the project
  define('TABLE_ADDRESS_BOOK', 'address_book');
  define('TABLE_ADDRESS_FORMAT', 'address_format');
  define('TABLE_BANNERS', 'banners');
  define('TABLE_BANNERS_HISTORY', 'banners_history');
  define('TABLE_CATEGORIES', 'categories');
  define('TABLE_CATEGORIES_DESCRIPTION', 'categories_description');
  define('TABLE_CONFIGURATION', 'configuration');
  define('TABLE_CONFIGURATION_GROUP', 'configuration_group');
  define('TABLE_COUNTRIES', 'countries');
  define('TABLE_CURRENCIES', 'currencies');
  define('TABLE_CUSTOMERS', 'customers');
  define('TABLE_CUSTOMERS_BASKET', 'customers_basket');
  define('TABLE_CUSTOMERS_BASKET_ATTRIBUTES', 'customers_basket_attributes');
  define('TABLE_CUSTOMERS_INFO', 'customers_info');
  define('TABLE_LANGUAGES', 'languages');
  define('TABLE_MANUFACTURERS', 'manufacturers');
  define('TABLE_MANUFACTURERS_INFO', 'manufacturers_info');
  define('TABLE_NEWSLETTERS', 'newsletters');
  define('TABLE_ORDERS', 'orders');
  define('TABLE_ORDERS_PRODUCTS', 'orders_products');
  define('TABLE_ORDERS_PRODUCTS_ATTRIBUTES', 'orders_products_attributes');
  define('TABLE_ORDERS_PRODUCTS_DOWNLOAD', 'orders_products_download');
  define('TABLE_ORDERS_STATUS', 'orders_status');
  define('TABLE_ORDERS_STATUS_HISTORY', 'orders_status_history');
  define('TABLE_ORDERS_TOTAL', 'orders_total');
  define('TABLE_PRODUCTS', 'products');
  define('TABLE_PRODUCTS_ATTRIBUTES', 'products_attributes');
  define('TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD', 'products_attributes_download');
  define('TABLE_PRODUCTS_DESCRIPTION', 'products_description');
  define('TABLE_PRODUCTS_NOTIFICATIONS', 'products_notifications');
  define('TABLE_PRODUCTS_OPTIONS', 'products_options');
  define('TABLE_PRODUCTS_OPTIONS_VALUES', 'products_options_values');
  define('TABLE_PRODUCTS_OPTIONS_VALUES_TO_PRODUCTS_OPTIONS', 'products_options_values_to_products_options');
  define('TABLE_PRODUCTS_TO_CATEGORIES', 'products_to_categories');
  define('TABLE_REVIEWS', 'reviews');
  define('TABLE_REVIEWS_DESCRIPTION', 'reviews_description');
  define('TABLE_SESSIONS', 'sessions');
  define('TABLE_SPECIALS', 'specials');
  define('TABLE_TAX_CLASS', 'tax_class');
  define('TABLE_TAX_RATES', 'tax_rates');
  define('TABLE_GEO_ZONES', 'geo_zones');
  define('TABLE_ZONES_TO_GEO_ZONES', 'zones_to_geo_zones');
  define('TABLE_WHOS_ONLINE', 'whos_online');
  define('TABLE_ZONES', 'zones');
 
// this will define the maximum time in minutes between updates of a products_group_prices_cg_# table
// changes in table specials will trigger an immediate update if a query needs this particular table
  define('MAXIMUM_DELAY_UPDATE_PG_PRICES_TABLE', '15');
// EOF Separate Pricing per Customer

  // BOF Separate Pricing Per Customer
  define('TABLE_PRODUCTS_GROUPS', 'products_groups');
  define('TABLE_CUSTOMERS_GROUPS', 'customers_groups');
  define('TABLE_PRODUCTS_GROUP_PRICES', 'products_group_prices_cg_');
  define('TABLE_PRODUCTS_ATTRIBUTES_GROUPS', 'products_attributes_groups');
// EOF Separate Pricing Per Customer

   /*** Begin Header Tags SEO ***/
  define('TABLE_HEADERTAGS', 'headertags');
  define('TABLE_HEADERTAGS_DEFAULT', 'headertags_default');
  /*** End Header Tags SEO ***/
   //kgt - discount coupons
  define('TABLE_DISCOUNT_COUPONS', 'discount_coupons');
  define('TABLE_DISCOUNT_COUPONS_TO_ORDERS', 'discount_coupons_to_orders');
  define('TABLE_DISCOUNT_COUPONS_TO_CATEGORIES', 'discount_coupons_to_categories');
  define('TABLE_DISCOUNT_COUPONS_TO_PRODUCTS', 'discount_coupons_to_products');
  define('TABLE_DISCOUNT_COUPONS_TO_MANUFACTURERS', 'discount_coupons_to_manufacturers');
  define('TABLE_DISCOUNT_COUPONS_TO_CUSTOMERS', 'discount_coupons_to_customers');
  define('TABLE_DISCOUNT_COUPONS_TO_ZONES', 'discount_coupons_to_zones');
  //end kgt - discount coupons
  //Contribution Prof_Invoice&PackingSlip    START
  		define('TABLE_INVOICE', 'table_invoice');
		//Contribution Prof_Invoice&PackingSlip    END

// Begin Suppliers
  define('TABLE_SUPPLIERS', 'suppliers');
  define('TABLE_SUPPLIERS_INFO', 'suppliers_info');
  define('TABLE_SUPPLIERS_PRODUCTS_GROUPS', 'suppliers_products_groups');
  define('TABLE_CATEGORIES_TO_SUPPLIERS', 'categories_to_suppliers');
 
// End Suppliers 
//BOF Dynamic Sitemap
  define('TABLE_SITEMAP_EXCLUDE', 'sitemap_exclude');
//EOF Dynamic Sitemap
// BOF: Featured Products
  define('TABLE_FEATURED', 'featured');
// EOF: Featured Products
  define('TABLE_FILES_UPLOADED', 'files_uploaded');  //BOF - Zappo - Option Types v2 - ONE LINE - File Uploading
    define('TABLE_PRODUCTS_OPTIONS_TYPES', 'products_options_types');  //BOF - Zappo - Option Types v2 - ONE LINE - Option Types


define('TABLE_INVOICES', 'invoices');
  define('TABLE_INVOICE_TO_COUNTRIES', 'invoice_to_countries');
  define('TABLE_INVOICE_TO_GEO_ZONES', 'invoice_to_geo_zones');  
  define('TABLE_PACKINGSLIPS', 'packingslips');
  define('TABLE_PACKINGSLIP_TO_COUNTRIES', 'packingslip_to_countries');
  define('TABLE_PACKINGSLIP_TO_GEO_ZONES', 'packingslip_to_geo_zones');  

//paps 
define('TABLE_PAPS_HEADINGS','paps_headings'); 
define('TABLE_PAPS_GLOBALS','paps_globals'); 

// BOF Bundled Products
  define('TABLE_PRODUCTS_BUNDLES', 'products_bundles');
// EOF Bundled Products

define('TABLE_PRODUCTS_SETS', 'products_sets');
  define('TABLE_PRODUCTS_SETS_CATEGORIES', 'products_sets_categories');
  define('TABLE_PRODUCTS_SETS_TO_PRODUCTS', 'products_sets_to_products');
  // BOF Bundled Products
  define('TABLE_PRODUCTS_BUNDLES', 'products_bundles');
// EOF Bundled Products
define('TABLE_QBCONFIG', 'myqbi_config');
define('TABLE_QBINVITEMS', 'myqbi_invitems');
define('TABLE_QBMISCIMPORT', 'myqbi_miscimport');
define('TABLE_QBMAPTABLE', 'myqbi_maptable');
define('TABLE_QBPOSTEDORDERS', 'myqbi_postedorders');
define('TABLE_QBPRODUCTS', 'myqbi_products');
define('TABLE_QBLAYERS', 'myqbi_layers');
//MVS start
  define('TABLE_VENDORS', 'vendors');
  define('TABLE_VENDORS_INFO', 'vendors_info');
  define('TABLE_VENDOR_CONFIGURATION', 'vendor_configuration');
  define('TABLE_ORDERS_SHIPPING', 'orders_shipping');
  define('TABLE_PACKAGING', 'packaging');
//MVS end


?>