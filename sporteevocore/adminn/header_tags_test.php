<?php
/*
  $Id: header_tags_seo.php,v 1.2 2008/08/08
  header_tags_seo Originally Created by: Jack_mcs
ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/
 
  require('includes/application_top.php');
  require_once('includes/functions/header_tags.php');
  require(DIR_WS_LANGUAGES . $language . '/header_tags_seo.php');

  $filename = DIR_FS_CATALOG. DIR_WS_INCLUDES . 'header_tags.php'; 
  $languages = tep_get_languages();
  $results = array();

  /********************** CHECK THE INPUT **********************/
  if (isset($_POST['action']) && $_POST['action'] == 'test')
  {
    /*************** CHECK THE FILE PERMISSIONS ***************/
    $filename = DIR_FS_CATALOG. DIR_WS_INCLUDES . 'header_tags.php'; 
    if (GetPermissions(DIR_FS_CATALOG_IMAGES) != Getpermissions($filename))
    {
      $results[] = ERROR_HEADING_PERMISSIONS;
      $results[] = sprintf(ERROR_WRONG_PERMISSIONS, $filename, Getpermissions(DIR_WS_IMAGES));
    }    

    /*************** CHECK THE SEARCH ENGINE FRIENDLY SETTING ***************/
    $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key like 'SEARCH_ENGINE_FRIENDLY_URLS'");
    $check = tep_db_fetch_array($check_query);
    if ($check['configuration_value'] == 'true') //the option is enabled
    {
      $results[] = ERROR_HEADING_SEARCH_ENGINE_OPTION;
      $results[] = sprintf(ERROR_SEARCH_ENGINE_OPTION);
    }    
        
    /*************** CHECK IF FILES ARE PRESENT ***************/
    $files = array();
    $files[] = DIR_FS_ADMIN . 'header_tags_seo.php';
    $files[] = DIR_FS_ADMIN . 'header_tags_fill_tags.php';
    $files[] = DIR_FS_ADMIN . DIR_WS_INCLUDES . 'header_tags_seo_words.txt';  
    $files[] = DIR_FS_ADMIN . DIR_WS_FUNCTIONS . 'header_tags.php';          
    $files[] = DIR_FS_ADMIN . DIR_WS_BOXES . 'header_tags_seo.php';
    $files[] = DIR_FS_ADMIN . DIR_WS_LANGUAGES . $language . '/header_tags_seo.php';
    $files[] = DIR_FS_CATALOG . DIR_WS_INCLUDES . 'header_tags.php';    
    $files[] = DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'clean_html_comments.php';
    $files[] = DIR_FS_CATALOG . DIR_WS_FUNCTIONS . 'header_tags.php';
    $files[] = DIR_FS_CATALOG . DIR_WS_BOXES . 'header_tags.php';
    $files[] = DIR_FS_CATALOG . DIR_WS_INCLUDES . 'modules/header_tags_social_bookmarks.php';
    
    $found = false;
    for ($i = 0; $i < count($files); ++$i)
    {
      if (! file_exists($files[$i]))
      {
        if (! $found)
        {
          $results[] = ERROR_HEADING_MISSING_FILE;
          $found = true;
        }
        $results[] = sprintf(ERROR_MISSING_FILE, $files[$i]);
      }  
    }

    /*************** CHECK IF HEADER TAGS TABLES EXIST ***************/
    $dbError = false; //for master group of database errors
    $check_query = tep_db_query("SHOW TABLES LIKE '" . TABLE_HEADERTAGS . "'");
    if (tep_db_num_rows($check_query) == 0)
    {
      if (! $dbError)
      {
        $results[] = ERROR_HEADING_DATABASE;
        $dbError = true;
      }  
      $results[] = sprintf(ERROR_MISSING_DATABASE, TABLE_HEADERTAGS);    
    }
    $check_query = tep_db_query("SHOW TABLES LIKE '" . TABLE_HEADERTAGS_DEFAULT . "'");
    if (tep_db_num_rows($check_query) == 0)
    {
      if (! $dbError)
      {
        $results[] = ERROR_HEADING_DATABASE;
        $dbError = true;
      }  
      $results[] = sprintf(ERROR_MISSING_DATABASE, TABLE_HEADERTAGS_DEFAULT);    
    }

    /*************** CHECK IF HEADER TAGS FIELDS EXIST ***************/    
    $tables = array(TABLE_PRODUCTS_DESCRIPTION => 'products_head_title_tag',
                    TABLE_CATEGORIES_DESCRIPTION => 'categories_htc_title_tag',
                    TABLE_MANUFACTURERS_INFO => 'manufacturers_htc_title_tag'
                   );
                   
    foreach ($tables as $table => $field)
    {
  	  $check_query = tep_db_query("select * from ". $table);
      $found = false;
      for ($ctr = 0; $ctr < tep_db_fetch_fields($check_query); $ctr++) 
      {
  	 	  if (false != strstr(mysql_field_name($check_query, $ctr), $field))
        {
          $found = true;
          break;
        }   
  	  }
      if (! $found)
      {
        if (! $dbError)
        {
          $results[] = ERROR_HEADING_DATABASE;
          $dbError = true;
        }  
        $results[] = sprintf(ERROR_MISSING_DATABASE_FIELD, $field, $table);      
      }
    }
    
    /*************** CHECK IF CONFIGURATION ENTRIES EXIST ***************/    
    $check_query = tep_db_query("select * from ". TABLE_CONFIGURATION_GROUP . " where configuration_group_title LIKE 'Header Tags SEO'");    
    if (tep_db_num_rows($check_query) == 0)  
    {
        if (! $dbError)
        {
          $results[] = ERROR_HEADING_DATABASE;
          $dbError = true;
        }  
        $results[] = ERROR_MISSING_CONFIGURATION_GROUP;    
    }
    $check_query = tep_db_query("select * from ". TABLE_CONFIGURATION . " where configuration_key LIKE 'Header_Tags%'");    
    if (tep_db_num_rows($check_query) == 0)  
    {
        if (! $dbError)
        {
          $results[] = ERROR_HEADING_DATABASE;
          $dbError = true;
        }  
        $results[] = ERROR_MISSING_CONFIGURATION;    
    }    
    
    /*************** CHECK IF STS HAS THE REQUIRED ENTRY ***************/    
    $stsInstalled = false;
    $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE_STS_DEFAULT_STATUS'");
    $check = tep_db_fetch_array($check_query);
    if ($check['configuration_value'] == 'true') //this is an STS installation and it's enabled
    {
      $stsInstalled = true;
      $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key like 'MODULE_STS_DEFAULT_NORMAL'");
      $check = tep_db_fetch_array($check_query);
      if (strpos($check['configuration_value'], "headertags.php") == FALSE)
      {
         if (! $dbError)
         {
            $results[] = ERROR_HEADING_STS;
            $dbError = true;
         }  
         $results[] = ERROR_MISSING_STS_FILE;          
      }
    }       

    /*************** CHECK IF FILES HAVE HEADER TAGS CODE CHANGES ***************/    
    $file = array(0 => 'index.php',
                  1 => 'product_info.php');
    for ($i = 0; $i < count($file); ++$i)
    {              
      $path = DIR_FS_CATALOG . $file[$i];
      $lines = array();
      $lines = @file($path);  //load in the filenames file
      $found = false;
      foreach((array)$lines as $line)
      {
         if (strpos($line, "require(DIR_WS_INCLUDES . 'header_tags.php');") !== FALSE)
         {
           $found = true;
           break;
         }
      }
      if (! $found)
      {
        if (! $dbError)
        {
          $results[] = ERROR_HEADING_MISSING_CODE;
          $dbError = true;
        }  
        $results[] = sprintf(ERROR_MISSING_CODE, $file[$i]);      
      }
      else if ($stsInstalled)
      {
        if (! $dbError)
        {
          $results[] = ERROR_HEADING_STS;
          $dbError = true;
        }  
        $results[] = sprintf(ERROR_STS_EXTRA_CODE, $file[$i]);   
      }
    }        
 
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<style type="text/css">
td.HTC_Head {font-family: Verdana, Arial, sans-serif; color: sienna; font-size: 18px; font-weight: bold; } 
td.HTC_subHead {font-family: Verdana, Arial, sans-serif; color: sienna; font-size: 12px; } 
.HTC_title {background: #fof1f1; text-align: center;} 

.popup
{
  color: yellow;
  cursor: pointer;
  text-decoration: none
}
</style>
<script language="javascript">
function confirmdelete(form, page)
{
 if (confirm('Do you really want to delete ' + page + '?\r\n\r\nThis only deletes the entry in Header Tags, not the actual file.'))
  form.submit();
  
 return false;
}
function UpdateSortOrder(page)
{
 var checkbox = "option_" + page; 
 var ckbox_status = document.getElementById(checkbox).checked; 

 if (ckbox_status == false)
  document.getElementById(page).disabled = true;
 else  
  document.getElementById(page).disabled = false;
 
}
</script>
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
      <td class="HTC_Head" colspan="2"><?php echo HEADING_TITLE_TEST; ?></td>
     </tr>
     <tr>
      <td colspan="2"><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
     </tr>
     <tr>
      <td colspan="2"><?php echo tep_black_line(); ?></td>
     </tr>     
 
     <!-- Begin of Header Test -->   
     <tr>
      <td align="right"><table width="100%" border="0" cellspacing="0" cellpadding="0">     
       <!-- begin left column new page -->
       <tr>
        <td align="right" width="60%" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
         <?php echo tep_draw_form('header_tags', FILENAME_HEADER_TAGS_TEST, '', 'post') . tep_draw_hidden_field('action', 'test'); ?></td>
          <tr>
           <td class="main" height="60" valign="top"><?php echo TEXT_TEXT_INFORMATION; ?></td>
          </tr>
          <tr> 
           <td align="center"><?php echo (tep_image_submit('button_test.gif', 'Test') ) . ' <a href="' . tep_href_link(FILENAME_HEADER_TAGS_SEO, '') .'">' . '</a>'; ?></td>
          </tr>
          <tr>
           <td><?php echo tep_black_line(); ?></td>
          </tr>          
         </form>
        </table></td>
       </tr>
       <!-- end Header Tags Test -->    

       <?php if (count($results) > 0) { ?>  
       <tr><td height="10"></td></tr>     
       <tr>
        <td class="HTC_Head"><?php echo TEST_RESULTS_HEADING; ?></td>
       </tr>
       <?php for ($i = 0; $i < count($results); ++$i) {  
        if (strpos($results[$i], "<b") !== FALSE) { ?>
         <tr><td height="10"></td></tr>
        <?php } ?>
       <tr>
        <td class="smallText"><?php echo $results[$i]; ?></td>
       </tr> 
       <?php } } ?>
      </table></td> 
     </tr>
     <!-- end of Header Tags -->
 	 
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
