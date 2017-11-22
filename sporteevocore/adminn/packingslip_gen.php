<?php
/*
  $Id: packingslips_gen.php,v 1.128 2003/05/28 18:00:29 hpdl Exp $

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/7 Francesco Rossi

  Released under the GNU General Public License
*/

  include('includes/application_top.php');


  $slipID = (isset($_GET['oslipID']) ? $_GET['oslipID'] : '');
  $filename='';
  $slips_query = tep_db_query("select packingslip_filename from " . TABLE_PACKINGSLIPS . " where packingslip_id =" . $slipID);  
  while ($slip = tep_db_fetch_array($slips_query)) {
         $filename = $slip['packingslip_filename'];
            
  }      

  
  //tep_redirect(tep_href_link($filename, tep_get_all_get_params(''), 'SSL'));
  tep_redirect(tep_href_link( $filename . '.php', tep_get_all_get_params(''), 'SSL'));
  

  require(DIR_WS_INCLUDES . 'application_bottom.php');
?>


<html>
<head>
</head>
<body>
<?php echo $filename ?>
</body>
</html>



  