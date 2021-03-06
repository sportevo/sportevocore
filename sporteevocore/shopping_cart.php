<?php


/*
  $Id: shopping_cart.php,v 1.73 2003/06/09 23:03:56 hpdl Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

  require("includes/application_top.php");

  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_SHOPPING_CART);

  $breadcrumb->add(NAVBAR_TITLE, tep_href_link(FILENAME_SHOPPING_CART));
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">

</head>
<body>
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->

  
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
  
<!-- body_text //-->
    <?php echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_SHOPPING_CART, 'action=update_product')); ?>
    
    <div id="stscart">
            <h2><?php echo HEADING_TITLE; ?></h2>
           
 
 	
<?php
  if ($cart->count_contents() > 0) {
?>
 

<?php


    $info_box_contents = array();
    $info_box_contents[0][] = array(
                                    'params' => 'id="productListing-remove"',
                                    'text' => TABLE_HEADING_REMOVE);

     $info_box_contents[0][] = array(
                                    'params' => 'id="productListing-qty"',
                                    'text' => TABLE_HEADING_QUANTITY);
    
    $info_box_contents[0][] = array('params' => 'id="productListing-prod"',
                                    'text' => TABLE_HEADING_PRODUCTS);


    $info_box_contents[0][] = array(
                                    'params' => 'id="productListing-total"',
                                    'text' => TABLE_HEADING_TOTAL);
									
									
   
?>

<?php
    $any_out_of_stock = 0;
    $products = $cart->get_products();
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
// Push all attributes information in an array
      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        while (list($option, $value) = each($products[$i]['attributes'])) {
          echo tep_draw_hidden_field('id[' . $products[$i]['id'] . '][' . $option . ']', $value);

// Yuval - Start - Fix a bug:: check stock per product attribute quantity
/*
          $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix
                                      from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                      where pa.products_id = '" . $products[$i]['id'] . "'
                                       and pa.options_id = '" . $option . "'
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = '" . $value . "'
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = '" . $languages_id . "'
                                       and poval.language_id = '" . $languages_id . "'");
*/
          $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pa.products_attributes_id
                                      from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                      where pa.products_id = '" . $products[$i]['id'] . "'
                                       and pa.options_id = '" . $option . "'
                                       and pa.options_id = popt.products_options_id
                                       and pa.options_values_id = '" . $value . "'
                                       and pa.options_values_id = poval.products_options_values_id
                                       and popt.language_id = '" . $languages_id . "'
                                       and poval.language_id = '" . $languages_id . "'");
// Yuval - End - Fix a bug:: check stock per product attribute quantity








          $attributes_values = tep_db_fetch_array($attributes);
          $products[$i][$option]['products_options_name'] = $attributes_values['products_options_name'];
          $products[$i][$option]['options_values_id'] = $value;
          $products[$i][$option]['products_options_values_name'] = $attributes_values['products_options_values_name'];
          $products[$i][$option]['options_values_price'] = $attributes_values['options_values_price'];
          $products[$i][$option]['price_prefix'] = $attributes_values['price_prefix'];
// Yuval - Start - Fix a bug:: check stock per product attribute quantity
          $products[$i][$option]['products_attributes_id'] = $attributes_values['products_attributes_id'];
// Yuval - End - Fix a bug:: check stock per product attribute quantity

        }
      }
    }
    for ($i=0, $n=sizeof($products); $i<$n; $i++) {
      if (($i/2) == floor($i/2)) {
        $info_box_contents[] = array('params' => '<div id="productListing-odd">');
      } else {
        $info_box_contents[] = array('params' => '<div id="productListing-odd">');
      }
      $cur_row = sizeof($info_box_contents) - 1;

      $info_box_contents[$cur_row][] = array(
                                             'params' => 'id="productListing-check"',
                                             'text' => tep_draw_checkbox_field('cart_delete[]', $products[$i]['id']));

      $products_name = '' .
                       '  ' .
                       '    <div id="shoppingcart-img"><a href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '">' . tep_image(DIR_WS_IMAGES . $products[$i]['image'], $products[$i]['name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT) . '</a></div>' .
                       '    <div id="productListing-title"><a class="prodTextb" href="' . tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $products[$i]['id']) . '">' . $products[$i]['name'] . '</a>';

       if (STOCK_CHECK == 'true') {
// Yuval - Start - Fix a bug:: check stock per product attribute quantity
//        $stock_check = tep_check_stock($products[$i]['id'], $products[$i]['quantity']);
  if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) { 
        reset($products[$i]['attributes']);
        while (list($option, $value) = each($products[$i]['attributes'])) {
          $stock_check = tep_check_stock($products[$i]['id'], $products[$i][$option]['products_attributes_id'], $products[$i]['quantity']);
  }
// Yuval - End - Fix a bug:: check stock per product attribute quantity

		 
// Yuval - End - Fix a bug:: check stock per product attribute quantity
          if (tep_not_null($stock_check)) {
            $any_out_of_stock = 1;
  
            $products_name .= $stock_check;
 					}
        }
      }

      if (isset($products[$i]['attributes']) && is_array($products[$i]['attributes'])) {
        reset($products[$i]['attributes']);
        while (list($option, $value) = each($products[$i]['attributes'])) {
          $products_name .= '<br><small><i class="icart"> - ' . $products[$i][$option]['products_options_name'] . ' ' . $products[$i][$option]['products_options_values_name'] . '</i></small></div>';
        }
      }

      $products_name .= '  ' .
                        '' .
                        '';

       $info_box_contents[$cur_row][] = array(
                                             'params' =>'class="productListing-cart"',
                                             'text' => '<span class="cartprice">' . $currencies->display_price($products[$i]['final_price'], tep_get_tax_rate($products[$i]['tax_class_id']), $products[$i]['quantity']) . '</span>');
   

      $info_box_contents[$cur_row][] = array(
                                             'params' => 'id="productListing-input"',
                                             'text' => tep_draw_input_field('cart_quantity[]', $products[$i]['quantity'], 'size="4"') . tep_draw_hidden_field('products_id[]', $products[$i]['id']));


     
   $info_box_contents[$cur_row][] = array('params' => 'id="productListing-data"',
                                             'text' => $products_name);
   
   

   $info_box_contents[$cur_row][] = array('text' => '');
  
    
    }

    
    new productListingBox($info_box_contents);
?>


   
   <?php
    $back = sizeof($navigation->path)-2;
    if (isset($navigation->path[$back])) {
?>
             <hr class="hrloginhr">
                
		<div id="subtotal">
			<div id="subtotalname"><?php echo SUB_TITLE_SUB_TOTAL; ?> </div>
			<div id="subtotalprice"><?php echo $currencies->format($cart->show_total()); ?></div>
		</div> 
		
			
		
		<div id="buttonbox">  
               <?php echo '<a href="' . tep_href_link($navigation->path[$back]['page'], tep_array_to_string($navigation->path[$back]['get'], array('action')), $navigation->path[$back]['mode']) . '">' . tep_image_button('button_continue_shopping.gif', IMAGE_BUTTON_CONTINUE_SHOPPING) . '</a>'; ?>
<?php
    }
?>
         </div><div id="buttonbox">   <?php echo tep_image_submit('button_update_cart.gif', IMAGE_BUTTON_UPDATE_CART); ?></div>  
<div id="buttonbox">  
             <?php echo '<a href="' . tep_href_link(FILENAME_CHECKOUT_SHIPPING, '', 'SSL') . '">' . tep_image_button('button_checkout.gif', IMAGE_BUTTON_CHECKOUT) . '</a>'; ?>
             </div>     

	

		
		
     
     
     
      </div>



  <?php
  } else {
?>
    
        <div class="maincapture"><?php new infoBox(array(array('text' => TEXT_CART_EMPTY))); ?></div>
		<div class="accountBox">
			<div class="accountBoxContents">

				<div id="buttonbox"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></div>
               
                
<?php
  }
?>
     
           
           	<?php
    if ($any_out_of_stock == 1) {
      if (STOCK_ALLOW_CHECKOUT == 'true') {
?> 
   		
        <span class="stockWarning"><br><?php echo OUT_OF_STOCK_CAN_CHECKOUT; ?></span>
      
<?php
      } else {
?>
      
        <span class="stockWarning"><br><?php echo OUT_OF_STOCK_CANT_CHECKOUT; ?></span>
       
<?php
      }
    }
?>

	
	 
	 </div>
		</div>
	</div>
	</form>
<!-- body_text_eof //-->
   
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
   
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
