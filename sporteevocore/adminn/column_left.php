<?php
/*
  $Id: column_left.php,v 1.15 2002/01/11 05:03:25 hpdl Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/2 Francesco Rossi

  Released under the GNU General Public License
  Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2)

  Please note: DO NOT DELETE this file if disabling the above contribution.
  Edits are listed by number. Locate and modify as needed to disable the contribution.
*/

// BOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 1 of 1
// reverse comments to the below lines to disable this contribution
//  require(DIR_WS_BOXES . 'configuration.php');
//  require(DIR_WS_BOXES . 'catalog.php');
//  require(DIR_WS_BOXES . 'modules.php');
//  require(DIR_WS_BOXES . 'customers.php');
//  require(DIR_WS_BOXES . 'taxes.php');
//  require(DIR_WS_BOXES . 'localization.php');
//  require(DIR_WS_BOXES . 'reports.php');
//  require(DIR_WS_BOXES . 'tools.php');

  if (tep_admin_check_boxes('administrator.php') == true) {
    require(DIR_WS_BOXES . 'administrator.php');
  }
  if (tep_admin_check_boxes('configuration.php') == true) {
    require(DIR_WS_BOXES . 'configuration.php');
  }
  if (tep_admin_check_boxes('catalog.php') == true) {
    require(DIR_WS_BOXES . 'catalog.php');
  }
  if (tep_admin_check_boxes('modules.php') == true) {
    require(DIR_WS_BOXES . 'modules.php');
  }
  if (tep_admin_check_boxes('customers.php') == true) {
    require(DIR_WS_BOXES . 'customers.php');
	  
  }
  if (tep_admin_check_boxes('vendors.php') == true) {
  require(DIR_WS_BOXES . 'vendors.php');
  }
  if (tep_admin_check_boxes('taxes.php') == true) {
    require(DIR_WS_BOXES . 'taxes.php');
  }
   if (tep_admin_check_boxes('invoices.php') == true) {
   require(DIR_WS_BOXES . 'invoices.php');
   }
   if (tep_admin_check_boxes('packingslips.php') == true) {
  require(DIR_WS_BOXES . 'packingslips.php');
   }
  if (tep_admin_check_boxes('localization.php') == true) {
    require(DIR_WS_BOXES . 'localization.php');
  }
  if (tep_admin_check_boxes('reports.php') == true) {
    require(DIR_WS_BOXES . 'reports.php');
  }
  if (tep_admin_check_boxes('tools.php') == true) {
    require(DIR_WS_BOXES . 'tools.php');
  }
   if (tep_admin_check_boxes('support.php') == true) {
    require(DIR_WS_BOXES . 'support.php');
  }
if (tep_admin_check_boxes('suppliersadmin.php') == true) {
    require(DIR_WS_BOXES . 'suppliersadmin.php');
}
if (tep_admin_check_boxes('supplierarea.php') == true) {
    require(DIR_WS_BOXES . 'supplierarea.php');
  }
  require(DIR_WS_BOXES . 'myqbi.php');
// EOE Access with Level Account (v. 2.2a) for the Admin Area of Francesco Rossi (MS2) 1 of 1
 require(DIR_WS_BOXES . 'ticket.php');
 require(DIR_WS_BOXES . 'gv_admin.php');  // ICW CREDIT CLASS Gift Voucher Addittion
 /*** Begin Header Tags SEO ***/
require(DIR_WS_BOXES . 'header_tags_seo.php');
/*** End Header Tags SEO ***/

  require(DIR_WS_BOXES . 'sitemonitor.php'); 

?>