<?php
/*
ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Pave Designs (http://www.pavedesigns.com)
------------------------------------------------------------------------
01 Feb 2007 by lostndazed: 
Could not get version 0.8.3 to get past "Headers Already Sent" errors.
Have adapted an older version to include encode_utf8 (seems to work but I'm not a programmer) and now validates.
Set to show price and still validate.
Removed images as it would not validate with them.
Added date code to help prevent duplicates showing.
Taxes added to price shown in feed has been made optional.
Changed README.txt instructions for more clarity.
Changed README.txt added additional instructions on how to have all your items show in feed.
 
                   end  C h a n g e l o g
##################################################################### */

require('includes/application_top.php');


$connection = mysql_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD)    or die("Couldn't make connection.");
// select database
$db = mysql_select_db(DB_DATABASE, $connection) or die(mysql_error());

// Si la langue n'est pas sp�cifi�e
if ($_GET['language'] == "") {
  $lang_query = tep_db_query("select languages_id, code from " . TABLE_LANGUAGES . " where directory = '" . $language . "'");
} else {
  $cur_language = tep_db_output($_GET['language']);
  $lang_query = tep_db_query("select languages_id, code from " . TABLE_LANGUAGES . " where code = '" . $cur_language . "'");
}

// R�cup�re le code (fr, en, etc.) et l'id (1, 2, etc.) de la langue courante
if (tep_db_num_rows($lang_query)) {
  $lang_a = tep_db_fetch_array($lang_query);
    $lang_code = $lang_a['code'];
    $lang_id = $lang_a['languages_id'];
}

// If the default of your catalog is not what you want in your RSS feed, then
// please change the constants:
// Enter an appropriate title for your website
define(RSS_TITLE, STORE_NAME);
// Enter your main shopping cart link
define(WEBLINK, HTTP_SERVER);
// Enter a description of your shopping cart
define(DESCRIPTION, TITLE);
/////////////////////////////////////////////////////////////
//That's it.  No More Editing (Unless you need to add tax to the total or 
//renamed DB tables or need to switch to SEO links (Apache Rewrite URL)
//IF YOU NEED TO ADD TAX TO TOTAL SHOWN, GOTO LINE 107 & MAKE CHANGE AS DIRECTED.
/////////////////////////////////////////////////////////////
$store_name = STORE_NAME;
$rss_title = RSS_TITLE;
$weblink = WEBLINK;
$description = DESCRIPTION;


// Encode in UTF-8
$store_name =  utf8_encode ($store_name);
$rss_title =  utf8_encode ($rss_title);
$weblink =  utf8_encode ($weblink);
$description =  utf8_encode ($description);


Header("Content-Type: text/xml");
echo '<?xml version="1.0" encoding="ISO-8859-1" ?>';
echo '<?xml-stylesheet href="http://www.w3.org/2000/08/w3c-synd/style.css" type="text/css"?>' . "\n";
echo "<!-- RSS for " . STORE_NAME . ", generated on " . date(r) . " -->\n";
?>
<rss version="0.92">
<channel>
<title><?php echo RSS_TITLE; ?></title>
<link><?php echo WEBLINK;?></link>
<description><?php echo DESCRIPTION; ?></description>
<language><?php echo $lang_code; ?></language>
<lastBuildDate><?php echo date(r); ?></lastBuildDate>

<?php
// Create SQL statement
if ($_GET['cPath'] != "") {
  $sql = "SELECT p.products_id, products_model, products_image, products_price, products_date_added, products_tax_class_id FROM products p, products_to_categories pc WHERE p.products_id = pc.products_id AND pc.categories_id = '" . $_GET['cPath'] . "' AND products_status=1 ORDER BY products_id DESC LIMIT " . MAX_DISPLAY_SEARCH_RESULTS;
} else {
  $sql = "SELECT products_id, products_model, products_image, products_price,  products_date_added, products_tax_class_id FROM products WHERE products_status=1 ORDER BY products_id DESC LIMIT " . MAX_DISPLAY_SEARCH_RESULTS;
}
// Execute SQL query and get result
$sql_result = mysql_query($sql,$connection) or die("Couldn't execute query.");

// Format results by row
while ($row = mysql_fetch_array($sql_result)) {
  $id = $row["products_id"];


  // RSS Links for Ultimate SEO (Gareth Houston 10 May 2005)
  $link = tep_href_link(FILENAME_PRODUCT_INFO, 'products_id=' . $id) ;

  $model = $row["products_model"];
  $image = $row["products_image"];
  $price = $row["products_price"];
  $tax = $row["products_tax_class_id"];  
  $added = date(r,strtotime($row['products_date_added']));

// Add VAt if product subject to VAT (might not be perfect if you have different VAT zones)
// If you need the added tax to show, then uncomment the next 5 lines. Otherwise, leave as is.
//  $sql3 = "SELECT tax_rate FROM tax_rates WHERE  tax_class_id = " . $tax . " LIMIT 1";
//  $sql3_result = mysql_query($sql3,$connection) or die("Couldn't execute query.");
//  $row3 = mysql_fetch_array($sql3_result);
//  $tax = ($row3["tax_rate"] / 100)+1;
//  $price = $price * $tax;
  if ($price=='$0.00') {$price= 'Many price options availably for this product';}  else {
  $price = $currencies->format($price);}

  $sql2 = "SELECT products_name, products_description FROM products_description WHERE products_id = '$id' AND language_id = '$lang_id' LIMIT 1";
  $sql2_result = mysql_query($sql2,$connection) or die("Couldn't execute query.");
  $row2 = mysql_fetch_array($sql2_result);
  
  $name =  $row2["products_name"];
  $desc = $row2["products_description"];

// add extra data here
  // Conversion en UTF-8
  $name = utf8_encode ($name);
  $desc = utf8_encode ($desc);
  $price = utf8_encode ($price);
  $link = utf8_encode ($link);

  
  // http://www.w3.org/TR/REC-xml/#dt-chardata
  // "The ampersand character (&) and the left angle bracket (<) MUST NOT appear in their literal form"
  $name = str_replace('&','&amp;',$name);
  $desc = str_replace('&','&amp;',$desc);
  $link = str_replace('&','&amp;',$link);

  $name = str_replace('<','&lt;',$name);
  $desc = str_replace('<','&lt;',$desc);
  $link = str_replace('<','&lt;',$link);

  $name = str_replace('>','&gt;',$name);
  $desc = str_replace('>','&gt;',$desc);
  $link = str_replace('>','&gt;',$link);

  
  echo '<item>
  	<title>' . $name . ' ' . $price . '</title>
  	<link>' . $link . '</link>
  	<description>' . $desc . '</description>
	<pubDate>' .  $added . '</pubDate>
	</item>' . "\n";
}
// free resources and close connection
mysql_free_result($sql_result);
mysql_close($connection);
?>
</channel>
</rss>