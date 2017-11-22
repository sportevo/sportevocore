<?php
/*
 * coupons_exclusions.php
 * September 26, 2006
 * author: Kristen G. Thorson
 * ot_discount_coupon_codes version 3.0
 *
 *
 * Released under the GNU General Public License
 *
 */


  /**********************************************/
  //toggle these true/false to determine which fields to display in product list in the same order as they are listed below

  $display_fields = array(
                          'categories' => array( 'c.categories_id' => false,
                                                 'cd.categories_name' => true ),
                                                 
                          'customers' => array( 'c.customers_id' => false,
                                                'CONCAT( c.customers_firstname, CONCAT( " ", c.customers_lastname ) ) AS name' => true,
                                                'c.customers_email_address' => false ),

                          'manufacturers' => array( 'm.manufacturers_id' => false,
                                                    //'mi.manufacturers_url' => false,
                                                    'm.manufacturers_name' => true ),
                                                    
                          'zones' => array( 'gz.geo_zone_name' => true,
                                            'gz.geo_zone_description' => true ),  

                          'products' => array( 'p.products_id' => false,
                                               'p.products_model' => false,
                                               'pd.products_name' => true,
                                               'p.products_price' => false ) );

  //separator for the fields in the listing
  $separator = ' :: ';

  //category exclusions
  //separator for the category names
  $category_separator = '->';
  //toggle true/false to determine whether to display the full category path
  $category_path = true;
  //end category exclusions

  /**********************************************/



  require( 'includes/application_top.php' );
  require( DIR_WS_FUNCTIONS.'coupons.php' );
  require( DIR_WS_CLASSES.'coupons_exclusions.php' );

  $action = ( isset( $_POST['action'] ) ? $_POST['action'] : '' );
  if( isset( $_GET['cID'] ) && $_GET['cID'] != '' ) $coupons_id = tep_db_input( $_GET['cID'] );
  else tep_redirect( tep_href_link( FILENAME_DISCOUNT_COUPONS, 'error='.ERROR_DISCOUNT_COUPONS_NO_COUPON_CODE ) );
  $type = ( isset( $_GET['type'] ) && $_GET['type'] != '' ? tep_db_input( $_GET['type'] ) : '' );

  if( !( $exclusion = new coupons_exclusions( $coupons_id, $type ) ) ) tep_redirect( tep_href_link( FILENAME_DISCOUNT_COUPONS, 'cID='.$coupons_id.'&error='.ERROR_DISCOUNT_COUPONS_INVALID_TYPE ) );

  if( tep_not_null( $action ) ) {
    switch( $action ) {
    	case 'Save':
    		//print_r( '<pre>'.print_r( $exclusion, true ).'</pre>' );
    		//print_r( '<pre>'.print_r( $_POST, true ).'</pre>' );
    		$exclusion->save( $_POST['selected_'.$type] );
    		tep_redirect( tep_href_link( FILENAME_DISCOUNT_COUPONS, 'cID='.$coupons_id.'&message='.MESSAGE_DISCOUNT_COUPONS_EXCLUSIONS_SAVED ) );
    		break;
    	case 'Cancel':
    		break;
    }
    tep_redirect( tep_href_link( FILENAME_DISCOUNT_COUPONS, 'cID='.$coupons_id ) );
  } else {

    $display_fields = array_keys( $display_fields[$type], true );
  	$display_fields = ( count( $display_fields ) > 0 ? ', '.implode( ', ', $display_fields ) : '' );

  	switch( $type ) {

  		case 'customers':
        $sql_selected = 'SELECT dc2c.customers_id AS id'.$display_fields.'
  												FROM '.TABLE_DISCOUNT_COUPONS_TO_CUSTOMERS.' dc2c
  												LEFT JOIN '.TABLE_CUSTOMERS.' c
  													ON c.customers_id=dc2c.customers_id
  												WHERE dc2c.coupons_id="'.$coupons_id.'"';
  			$sql_all = 'SELECT c.customers_id AS id'.$display_fields.'
										FROM '.TABLE_CUSTOMERS.' c
											%s';
  			$where = ' WHERE c.customers_id NOT IN( %s ) ';
				break;

			case 'categories':
        $sql_selected = 'SELECT dc2c.categories_id AS id'.$display_fields.'
  												FROM '.TABLE_DISCOUNT_COUPONS_TO_CATEGORIES.' dc2c
  												LEFT JOIN '.TABLE_CATEGORIES_DESCRIPTION.' cd
  													ON cd.categories_id=dc2c.categories_id
  												LEFT JOIN '.TABLE_CATEGORIES.' c
  													ON c.categories_id=cd.categories_id
  												WHERE dc2c.coupons_id="'.$coupons_id.'"
  													AND cd.language_id='.(int)$languages_id;
  			$sql_all = 'SELECT c.categories_id AS id'.$display_fields.'
										FROM '.TABLE_CATEGORIES_DESCRIPTION.' cd
                    LEFT JOIN '.TABLE_CATEGORIES.' c
  										ON c.categories_id=cd.categories_id
										WHERE cd.language_id='.(int)$languages_id.'
											%s';
  			$where = ' AND c.categories_id NOT IN( %s ) ';
				break;

			case 'manufacturers':
        $sql_selected = 'SELECT m.manufacturers_id AS id'.$display_fields.'
  												FROM '.TABLE_DISCOUNT_COUPONS_TO_MANUFACTURERS.' dc2m
  												LEFT JOIN '.TABLE_MANUFACTURERS.' m
  													ON m.manufacturers_id=dc2m.manufacturers_id
  												WHERE dc2m.coupons_id="'.$coupons_id.'"';
        /*$sql_selected = 'SELECT m.manufacturers_id AS id'.$display_fields.'
                          FROM '.TABLE_DISCOUNT_COUPONS_TO_MANUFACTURERS.' dc2m
                          LEFT JOIN '.TABLE_MANUFACTURERS.' m
                            ON m.manufacturers_id=dc2m.manufacturers_id
                          LEFT JOIN '.TABLE_MANUFACTURERS_INFO.' mi
                            ON mi.manufacturers_id=m.manufacturers_id
                          WHERE dc2m.coupons_id="'.$coupons_id.'"
                            AND mi.languages_id='.(int)$languages_id;*/                            
  			/*$sql_all = 'SELECT m.manufacturers_id AS id'.$display_fields.'
										FROM '.TABLE_MANUFACTURERS_INFO.' mi
                    LEFT JOIN '.TABLE_MANUFACTURERS.' m
  										ON m.manufacturers_id=mi.manufacturers_id
										WHERE mi.languages_id='.(int)$languages_id.'
											%s';*/
        $sql_all = 'SELECT m.manufacturers_id AS id'.$display_fields.'
                    FROM '.TABLE_MANUFACTURERS.' m
                      %s';                      
  			$where = ' WHERE m.manufacturers_id NOT IN( %s ) ';
				break;

 			case 'products':
        $sql_selected = 'SELECT p.products_id AS id'.$display_fields.'
  												FROM '.TABLE_DISCOUNT_COUPONS_TO_PRODUCTS.' dc2p
  												LEFT JOIN '.TABLE_PRODUCTS_DESCRIPTION.' pd
  													ON pd.products_id=dc2p.products_id
  												LEFT JOIN '.TABLE_PRODUCTS.' p
  													ON p.products_id=pd.products_id
  												WHERE dc2p.coupons_id="'.$coupons_id.'"
  													AND pd.language_id='.(int)$languages_id;
  			$sql_all = 'SELECT p.products_id AS id'.$display_fields.'
										FROM '.TABLE_PRODUCTS_DESCRIPTION.' pd
										LEFT JOIN '.TABLE_PRODUCTS.' p
											ON p.products_id=pd.products_id
										WHERE pd.language_id='.(int)$languages_id.'
											%s';
  			$where = ' AND p.products_id NOT IN( %s ) ';
				break;
        
      case 'zones' :
        $sql_selected = 'SELECT dc2z.geo_zone_id AS id'.$display_fields.'
                          FROM '.TABLE_DISCOUNT_COUPONS_TO_ZONES.' dc2z
                          LEFT JOIN '.TABLE_GEO_ZONES.' gz
                            USING( geo_zone_id )
                          WHERE dc2z.coupons_id="'.$coupons_id.'"';
        $sql_all = 'SELECT gz.geo_zone_id AS id'.$display_fields.'
                    FROM '.TABLE_GEO_ZONES.' gz
                    %s';
        $where = 'WHERE gz.geo_zone_id NOT IN(%s) ';
      break;

		}

		if( ( $selected_ids = $exclusion->get_selected_options( $sql_selected, $separator/*category exclusions*/, $category_separator, ( $type == 'categories' ? $category_path : false )/*end category exclusions*/ ) ) === false ) tep_redirect( tep_href_link( FILENAME_DISCOUNT_COUPONS, 'cID='.$coupons_id.'&error='.ERROR_DISCOUNT_COUPONS_SELECTED_LIST ) );

		$where = ( count( $selected_ids ) > 0 ? sprintf( $where, implode( ', ', $selected_ids ) ) : '' );
		$sql_all = sprintf( $sql_all, $where );
    
    //print_r( '<pre>'.print_r( $sql_all, true ).'</pre>' );

		if( ( $exclusion->get_all_options( $sql_all, $separator/*category exclusions*/, $category_separator, ( $type == 'categories' ? $category_path : false )/*end category exclusions*/, $selected_ids ) ) === false ) tep_redirect( tep_href_link( FILENAME_DISCOUNT_COUPONS, 'cID='.$coupons_id.'&error='.ERROR_DISCOUNT_COUPONS_ALL_LIST ) );
		//print_r( '<pre>'.print_r( $exclusion, true ).'</pre>' );
	}

?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
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
        <td><table border="0" width="100%" cellspacing="5" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo sprintf( HEADING_TITLE, $coupons_id ); ?></td>
            <td class="specialPrice" align="right">NOTICE: <a href="<?php echo tep_href_link( DIR_WS_LANGUAGES.$language.'/'.FILENAME_DISCOUNT_COUPONS_MANUAL ).'">'.HEADING_TITLE_VIEW_MANUAL; ?></a></td>
          </tr>
          <tr>
          	<td colspan="2">
<?php
	echo $exclusion->display();
?>
						</td>
          </tr>
        </table></td>
      </tr>
    </table></td>
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