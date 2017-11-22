<?php
/*
  $Id: checkout_process.php,v 1.128 2003/05/28 18:00:29 hpdl Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/7 Francesco Rossi

  Released under the GNU General Public License
*/

  include('includes/application_top.php');


  $invoiceID = (isset($_GET['oInvoiceID']) ? $_GET['oInvoiceID'] : '');
  $filename='';
  $invoices_query = tep_db_query("select invoice_filename from " . "invoices " . "where invoice_id =" . $invoiceID);  
  while ($invoice = tep_db_fetch_array($invoices_query)) {
         $filename = $invoice['invoice_filename'];
            
  }      

  
  //tep_redirect(tep_href_link($filename, tep_get_all_get_params(''), 'SSL'));
  tep_redirect(tep_href_link( $filename . '.php', tep_get_all_get_params(''), 'SSL'));

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>






  