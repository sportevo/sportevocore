<?php 

/* 
* Dit kan natuurlijk ook gewoon een database record zijn,  
* maar om een snel 'werkend' voorbeeld te geven doe ik het maar even via een array 
*/ 
require('includes/application_top.php');
require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_PRODUCT_INFO);

$id = $_GET['zoek'];
$options_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_thumbnail from " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pov.products_options_values_id = '" .$id . "'");
$options = tep_db_fetch_array($options_query);

if($options['products_options_values_thumbnail'] != '' && OPTIONS_IMAGES_CLICK_ENLARGE == 'true')
{
echo "<a href='" . tep_href_link(FILENAME_OPTIONS_IMAGES_POPUP, "oID=" . $id) ."' target='blank'><img src=./images/options/".$options['products_options_values_thumbnail']." height=".OPTIONS_IMAGES_HEIGHT." width=".OPTIONS_IMAGES_WIDTH." border='0'></a>";
}
if($options['products_options_values_thumbnail'] == '' && OPTIONS_IMAGES_CLICK_ENLARGE == 'true')
{
echo TEXT_IMAGE_NOT_FOUND;
}
if($options['products_options_values_thumbnail'] != '' && OPTIONS_IMAGES_CLICK_ENLARGE == 'false')
{
echo "<img src=./images/options/".$options['products_options_values_thumbnail']." height=".OPTIONS_IMAGES_HEIGHT." width=".OPTIONS_IMAGES_WIDTH." border='0'>";
}
?> 
<?php require('includes/application_bottom.php'); ?>