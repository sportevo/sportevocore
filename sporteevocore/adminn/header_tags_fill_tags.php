<?php
/*
  $Id: header_tags_fill_tags.php,v 1.0 2005/08/25
  Originally Created by: Jack York - http://www.Francesco Rossi-solution.com
ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/
 
  require('includes/application_top.php'); 
  require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_HEADER_TAGS_SEO);
  require_once('includes/functions/header_tags.php');

  /****************** READ IN FORM DATA ******************/
  $categories_fill = $_POST['group1'];
  $manufacturers_fill = $_POST['group2'];
  $products_fill = $_POST['group3'];
  $productsMetaDesc = $_POST['group4'];
  $productsMetaKeywords = $_POST['group5'];
  $productsMetaDescLength = $_POST['fillMetaDescrlength'];

  $checkedCats = array();
  $checkedManuf = array();
  $checkedProds = array();
  $checkedMetaDesc = array('yes' => '', 'no' => 'checked');
  $checkedMetaKeywords = array('yes' => '', 'no' => 'checked');  
  $languages = tep_get_languages();
  $updateDB = false;
  $updateTextCat = '';
  $updateTextManuf = '';
  $updateTextProd = '';
  
  $filltagsPopup = array();    
  if (HEADER_TAGS_DISPLAY_HELP_POPUPS)
    $filltagsPopup = GetPopupText('filltags');
    
  /****************** FILL THE CATEGORIES ******************/
   
  if (isset($categories_fill))
  {
    if ($categories_fill == 'none') 
    {
       $checkedCats['none'] = 'Checked';
    }
    else
    { 
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) 
      {
        $categories_tags_query = tep_db_query("select categories_name, categories_id, categories_htc_title_tag, categories_htc_desc_tag, categories_htc_keywords_tag, language_id from  " . TABLE_CATEGORIES_DESCRIPTION . " where language_id = '" . (int)$languages[$i]['id']. "'");
        while ($categories_tags = tep_db_fetch_array($categories_tags_query))
        {
          $updateDB = false;
          
          switch ($categories_fill)
          {
            case 'empty':
              if (! tep_not_null($categories_tags['categories_htc_title_tag']))
              {
                $updateDB = true;
                $updateTextCat = 'Empty Category tags have been filled.';
              }  
              $checkedCats['empty'] = 'Checked';
            break;
            
            case 'full':
              $updateDB = true;
              $updateTextCat = 'All Category tags have been filled.';
              $checkedCats['full'] = 'Checked';
            break;
            
            default:      //assume clear all
              tep_db_query("update " . TABLE_CATEGORIES_DESCRIPTION . " set categories_htc_title_tag='', categories_htc_desc_tag = '', categories_htc_keywords_tag = '' where categories_id = '" . $categories_tags['categories_id']."' and language_id  = '" . (int)$languages[$i]['id'] . "'");
              $updateTextCat = 'All Category tags have been cleared.';
              $checkedCats['clear'] = 'Checked';
              $checkedMetaDesc = array('yes' => '', 'no' => 'checked');
              $checkedMetaKeywords = array('yes' => '', 'no' => 'checked');              
            break;
          }
                        
          if ($updateDB)
            tep_db_query("update " . TABLE_CATEGORIES_DESCRIPTION . " set categories_htc_title_tag='".addslashes(strip_tags($categories_tags['categories_name']))."', categories_htc_desc_tag = '". addslashes($categories_tags['categories_name'])."', categories_htc_keywords_tag = '". addslashes(strip_tags($categories_tags['categories_name'])) . "' where categories_id = '" . $categories_tags['categories_id']."' and language_id  = '" . (int)$languages[$i]['id'] . "'");
        }
      }
    }
  }
  else
    $checkedCats['none'] = 'Checked';
   
  /****************** FILL THE MANUFACTURERS ******************/
   
  if (isset($manufacturers_fill))
  {
    if ($manufacturers_fill == 'none') 
    {
       $checkedManuf['none'] = 'Checked';
    }
    else
    { 
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) 
      {    
        $manufacturers_tags_query = tep_db_query("select m.manufacturers_name, m.manufacturers_id, mi.languages_id, mi.manufacturers_htc_title_tag, mi.manufacturers_htc_desc_tag, mi.manufacturers_htc_keywords_tag from " . TABLE_MANUFACTURERS . " m, " . TABLE_MANUFACTURERS_INFO . " mi where mi.languages_id = '" . (int)$languages[$i]['id'] . "'");
        while ($manufacturers_tags = tep_db_fetch_array($manufacturers_tags_query))
        {
          $updateDB = false;
          
          switch ($manufacturers_fill)
          {
            case 'empty':
              if (! tep_not_null($manufacturers_tags['manufacturers_htc_title_tag']))
              {
                $updateDB = true;
                $updateTextManuf = 'Empty Manufacturers tags have been filled.';
              }  
              $checkedManuf['empty'] = 'Checked';
            break;
            
            case 'full':
              $updateDB = true;
              $updateTextManuf = 'All Manufacturers tags have been filled.';
              $checkedManuf['full'] = 'Checked';
            break;
            
            default:      //assume clear all
              tep_db_query("update " . TABLE_MANUFACTURERS_INFO . " set manufacturers_htc_title_tag='', manufacturers_htc_desc_tag = '', manufacturers_htc_keywords_tag = '' where manufacturers_id = '" . $manufacturers_tags['manufacturers_id']."' and languages_id  = '" . (int)$languages[$i]['id'] . "'");
              $updateTextManuf = 'All Manufacturers tags have been cleared.';
              $checkedManuf['clear'] = 'Checked';
              $checkedMetaDesc = array('yes' => '', 'no' => 'checked');
              $checkedMetaKeywords = array('yes' => '', 'no' => 'checked');              
            break;
          }      
               
          if ($updateDB)
            tep_db_query("update " . TABLE_MANUFACTURERS_INFO . " set manufacturers_htc_title_tag='".addslashes(strip_tags($manufacturers_tags['manufacturers_name']))."', manufacturers_htc_desc_tag = '". addslashes($manufacturers_tags['manufacturers_name'])."', manufacturers_htc_keywords_tag = '". addslashes(strip_tags($manufacturers_tags['manufacturers_name'])) . "' where manufacturers_id = '" . $manufacturers_tags['manufacturers_id']."' and languages_id  = '" . (int)$languages[$i]['id'] . "'");
        }
      }
    }
  }
  else
    $checkedManuf['none'] = 'Checked';
       
  /****************** FILL THE PRODUCTS ******************/  
  
  if (isset($products_fill))
  {
    if ($products_fill == 'none') 
    {
       $checkedProds['none'] = 'Checked';
    }
    else
    { 
      for ($i = 0, $n = sizeof($languages); $i < $n; $i++) 
      {    
        $products_tags_query = tep_db_query("select products_name, products_description, products_id, products_head_title_tag, products_head_desc_tag, products_head_keywords_tag, language_id from " . TABLE_PRODUCTS_DESCRIPTION . " where language_id = '" . (int)$languages[$i]['id'] . "'");
        while ($products_tags = tep_db_fetch_array($products_tags_query))
        {
          $updateDB = false;
        
          switch ($products_fill)
          {
            case 'empty':
              if (! tep_not_null($products_tags['products_head_title_tag']))
              {
                $updateDB = true;
                $updateTextProd = 'Empty Product tags have been filled.';
              }  
              $checkedProds['empty'] = 'Checked';
            break;
            
            case 'full':
              $updateDB = true;
              $updateTextProd = 'All Product tags have been filled.';
              $checkedProds['full'] = 'Checked';
            break;
            
            default:      //assume clear all
              tep_db_query("update " . TABLE_PRODUCTS_DESCRIPTION . " set products_head_title_tag='', products_head_desc_tag = '', products_head_keywords_tag =  '' where products_id = '" . $products_tags['products_id'] . "' and language_id='". (int)$languages[$i]['id'] ."'");
              $updateTextProd = 'All Product tags have been cleared.';
              $checkedProds['clear'] = 'Checked';
              $checkedMetaDesc = array('yes' => '', 'no' => 'checked');
              $checkedMetaKeywords = array('yes' => '', 'no' => 'checked');
            break;
          }
                 
          if ($updateDB)
          {
            /************************ FILL THE DESCRIPTION **********************/
            if ($productsMetaDesc == 'fillMetaDesc_yes')          //fill the description with all or part of the 
            {                                                     //product description
              if (! empty($products_tags['products_description']))
              {
                if (isset($productsMetaDescLength) && (int)$productsMetaDescLength > 3 && (int)$productsMetaDescLength < strlen($products_tags['products_description']))
                  $desc = substr($products_tags['products_description'], 0, (int)$productsMetaDescLength);
                else                                              //length not entered or too small    
                  $desc = $products_tags['products_description']; //so use the whole description
              }   
              else
                $desc = $products_tags['products_name'];  
            }  
            else
            {        
              $desc = $products_tags['products_name'];           
            }  
            
            /************************ FILL THE KEYWORDS **********************/
            if ($productsMetaKeywords == 'fillMetaKeywords_yes')  //fill the keywords from those found on the page 
            {                                                     
              $pageName = 'product_info.php' .'?products_id=' . $products_tags['products_id'] . '?language='. $languages[$i]['code'];
              $keywordStr = GetKeywordsFromSite($pageName); //get the keywords from the page
              
              if (strpos($keywordStr, "Failed") !== FALSE)
              {
                $messageStack->add($keywordStr, 'failure');
                $keywords = $products_tags['products_name'];  //fill in for default         
              }  
              else
              {
                $keywords = (tep_not_null($keywordStr)) ? $keywordStr : $products_tags['products_name'];
              }            
            }  
            else
            {        
              $keywords = $products_tags['products_name'];           
            }  

            tep_db_query("update " . TABLE_PRODUCTS_DESCRIPTION . " set products_head_title_tag='".addslashes(strip_tags($products_tags['products_name']))."', products_head_desc_tag = '". addslashes(strip_tags($desc))."', products_head_keywords_tag =  '" . addslashes(strip_tags($products_tags['products_name'])) . "' where products_id = '" . $products_tags['products_id'] . "' and language_id='". (int)$languages[$i]['id'] ."'");
          } 
        }
      }
      $checkedMetaDesc = ($productsMetaDesc == 'fillMetaDesc_yes') ? array('yes' => 'checked', 'no' => '') : array('yes' => '', 'no' => 'checked');
      $checkedMetaKeywords = ($productsMetaKeywords == 'fillMetaKeywords_yes') ? array('yes' => 'checked', 'no' => '') : array('yes' => '', 'no' => 'checked'); 
    }
  }
  else
  { 
    $checkedProds['none'] = 'Checked';
    $checkedMetaDesc = array('yes' => '', 'no' => 'checked');
    $checkedMetaKeywords = array('yes' => '', 'no' => 'checked');
  }

  if (isset($_POST['show_missing_tags']) & $_POST['show_missing_tags'] == 'on')
  {
    $checkDesc = (isset($_POST['include_missing_description']) && $_POST['include_missing_description'] == 'on') ? true : false;
    $missingTags = CheckForMissingTags(true, $checkDesc);
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
td.HTC_title {font-family: Verdana, Arial, sans-serif; color: sienna; background: #f0f1f1; font-size: 14px; font-weight: bold; text-align: center;} 
</style>
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
      <td class="HTC_Head"><?php echo HEADING_TITLE_FILL_TAGS; ?></td>
     </tr>
     <tr>
      <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
     <tr>
      <td class="HTC_subHead"><?php echo TEXT_FILL_TAGS; ?></td>
     </tr>
     
     <!-- Begin of Header Tags -->      
     
    
     
     
     <tr>
      <td align="right"><?php echo tep_draw_form('header_tags', FILENAME_HEADER_TAGS_FILL_TAGS, '', 'post') . tep_draw_hidden_field('action', 'process'); ?></td>
       <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
       </tr>

       <tr>
        <td><table border="1" style="border: ridge 4 px;">
         <tr> 
          <td colspan="3" class="HTC_title"><?php echo HEADING_TITLE_FILL_TAGS_OVERRIDES; ?></td>
         </tr> 
         <tr>
          <td width="55%"><table border="0" width="100%">        
           <tr class="main"> 
            <td colspan="3"><?php echo TEXT_FILL_WITH_DESCIPTION; ?></td>
           </tr> 
           <tr> 
            <td width="100%"><table border="0" width="65%"> 
             <tr class="main">
              <td align=left title="<?php echo $filltagsPopup['filldesc_yes']; ?>" class="popup"><INPUT TYPE="radio" NAME="group4" VALUE="fillMetaDesc_yes"<?php echo $checkedMetaDesc['yes']; ?>> <?php echo TEXT_YES; ?>&nbsp;</td>
              <td align=left title="<?php echo $filltagsPopup['filldesc_no']; ?>" class="popup"><INPUT TYPE="radio" NAME="group4" VALUE="fillmetaDesc_no"<?php echo $checkedMetaDesc['no']; ?>>&nbsp;<?php echo TEXT_NO; ?></td>
              <td align="right" class="main" title="<?php echo $filltagsPopup['filldesc_size']; ?>" class="popup"><?php echo TEXT_LIMIT_TO . '&nbsp;' . tep_draw_input_field('fillMetaDescrlength', '', 'maxlength="255", size="5"', false) . '&nbsp;' . TEXT_CHARACTERS; ?> </td>
             </tr>
            </table></td> 
           </tr> 
           
           <tr><td colspan="3" width="100%" height="30"><?php echo tep_black_line(); ?></td></tr>
           
           <tr class="main"> 
            <td colspan="3"><?php echo TEXT_FILL_KEYWORDS_FROM_SHOP; ?></td>
           </tr> 
           <tr>
            <td width="100%"><table border="0" width="24%"> 
             <tr class="main">         
              <td align=left title="<?php echo $filltagsPopup['fillkeywords_yes']; ?>" class="popup"><INPUT TYPE="radio" NAME="group5" VALUE="fillMetaKeywords_yes"<?php echo $checkedMetaKeywords['yes']; ?>> <?php echo TEXT_YES; ?>&nbsp;</td>
              <td align=left title="<?php echo $filltagsPopup['fillkeywords_no']; ?>" class="popup"><INPUT TYPE="radio" NAME="group5" VALUE="fillmetaKeywords_no"<?php echo $checkedMetaKeywords['no']; ?>>&nbsp;<?php echo TEXT_NO; ?></td>
             </tr>
            </table></td> 
           </tr>     
                    
          </table></td>
          <td valign="top"><table border="0">
           <tr>
            <td class="HTC_subHead"><?php echo TEXT_EXPLAIN_DESC; ?></td>
           </tr>
          </table></td>            
         </tr>        
        </table></td>
       </tr> 
        

       <tr>        
        <td><table border="1" width="100%" style="border: ridge 4 px;">
         <tr> 
          <td colspan="3" class="HTC_title"><?php echo HEADING_TITLE_FILL_TAGS_OPTIONS; ?></td>
         </tr> 
         <tr>
          <td width="55%"><table border="0" width="100%">
           <tr>
            <td><table border="0" width="100%">
             <tr class="smallText">
              <th><?php echo HEADING_TITLE_SEO_CATEGORIES; ?></th>
              <th><?php echo HEADING_TITLE_SEO_MANUFACTURERS; ?></th>          
              <th><?php echo HEADING_TITLE_SEO_PRODUCTS; ?></th>
             </tr> 
             <tr class="smallText">          
              <td align=left title="<?php echo $filltagsPopup['skipall']; ?>" class="popup"><INPUT TYPE="radio" NAME="group1" VALUE="none" <?php echo $checkedCats['none']; ?>> <?php echo HEADING_TITLE_SEO_SKIPALL; ?></td>
              <td align=left title="<?php echo $filltagsPopup['skipall']; ?>" class="popup"><INPUT TYPE="radio" NAME="group2" VALUE="none" <?php echo $checkedManuf['none']; ?>> <?php echo HEADING_TITLE_SEO_SKIPALL; ?></td>
              <td align=left title="<?php echo $filltagsPopup['skipall']; ?>" class="popup"><INPUT TYPE="radio" NAME="group3" VALUE="none" <?php echo $checkedProds['none']; ?>> <?php echo HEADING_TITLE_SEO_SKIPALL; ?></td>
             </tr>
             <tr class="smallText"> 
              <td align=left title="<?php echo $filltagsPopup['empty']; ?>"><INPUT TYPE="radio" NAME="group1" VALUE="empty"<?php echo $checkedCats['empty']; ?> > <?php echo HEADING_TITLE_SEO_FILLONLY; ?></td>
              <td align=left title="<?php echo $filltagsPopup['empty']; ?>"><INPUT TYPE="radio" NAME="group2" VALUE="empty" <?php echo $checkedManuf['empty']; ?>> <?php echo HEADING_TITLE_SEO_FILLONLY; ?></td>
              <td align=left title="<?php echo $filltagsPopup['empty']; ?>"><INPUT TYPE="radio" NAME="group3" VALUE="empty" <?php echo $checkedProds['empty']; ?>> <?php echo HEADING_TITLE_SEO_FILLONLY; ?></td>
             </tr>
             <tr class="smallText"> 
              <td align=left title="<?php echo $filltagsPopup['full']; ?>"><INPUT TYPE="radio" NAME="group1" VALUE="full" <?php echo $checkedCats['full']; ?>> <?php echo HEADING_TITLE_SEO_FILLALL; ?></td>
              <td align=left title="<?php echo $filltagsPopup['full']; ?>"><INPUT TYPE="radio" NAME="group2" VALUE="full" <?php echo $checkedManuf['full']; ?>> <?php echo HEADING_TITLE_SEO_FILLALL; ?></td>
              <td align=left title="<?php echo $filltagsPopup['full']; ?>"><INPUT TYPE="radio" NAME="group3" VALUE="full" <?php echo $checkedProds['full']; ?>> <?php echo HEADING_TITLE_SEO_FILLALL; ?></td>
             </tr>
             <tr class="smallText"> 
              <td align=left title="<?php echo $filltagsPopup['clear']; ?>"><INPUT TYPE="radio" NAME="group1" VALUE="clear" <?php echo $checkedCats['clear']; ?>> <?php echo HEADING_TITLE_SEO_CLEARALL; ?></td>
              <td align=left title="<?php echo $filltagsPopup['clear']; ?>"><INPUT TYPE="radio" NAME="group2" VALUE="clear" <?php echo $checkedManuf['clear']; ?>> <?php echo HEADING_TITLE_SEO_CLEARALL; ?></td>
              <td align=left title="<?php echo $filltagsPopup['clear']; ?>"><INPUT TYPE="radio" NAME="group3" VALUE="clear" <?php echo $checkedProds['clear']; ?>> <?php echo HEADING_TITLE_SEO_CLEARALL; ?></td>
             </tr>
            </table></td>         
           </tr> 
           <tr>
            <td width="55%"><table border="0" width="95%">
             <tr class="smallText"> 
              <td align=left title="<?php echo $filltagsPopup['show_missing_tags']; ?>"><INPUT TYPE="checkbox" NAME="show_missing_tags" ><?php echo HEADING_TITLE_SEO_SHOW_MISSING_TAGS; ?></td>
              <td align=left title="<?php echo $filltagsPopup['include_missing_description']; ?>"><INPUT TYPE="checkbox" NAME="include_missing_description" ><?php echo HEADING_TITLE_SEO_INCLUDE_MISSING_DESCRIPTION; ?></td>
             </tr>
            </table></td>
           </tr>  
         
           <tr>
            <td><table border="0" width="100%">
             <tr>
              <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
             </tr>
             <tr> 
              <td align="center"><?php echo (tep_image_submit('button_update.gif', IMAGE_UPDATE) ) . ' <a href="' . tep_href_link(FILENAME_HEADER_TAGS_ENGLISH, tep_get_all_get_params(array('action'))) .'">' . '</a>'; ?></td>
             </tr>

             <?php if (tep_not_null($updateTextCat) || tep_not_null($updateTextManuf) || tep_not_null($updateTextProd)) { ?>
             <tr>
              <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
             </tr> 
             <?php } ?> 
                    
             <?php if (tep_not_null($updateTextCat)) { ?>
              <tr>
               <td class="HTC_subHead"><?php echo $updateTextCat; ?></td>
              </tr> 
              <?php }  
               if (tep_not_null($updateTextManuf)) { ?>
              <tr>
               <td class="HTC_subHead"><?php echo $updateTextManuf; ?></td>
              </tr>
             <?php } 
               if (tep_not_null($updateTextProd)) { ?>
              <tr>
               <td class="HTC_subHead"><?php echo $updateTextProd; ?></td>
              </tr>
              <?php }  
               if (tep_not_null($missingTags)) { ?>
              <tr>
               <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
              </tr>                
              <tr>
               <td class="HTC_subHead" style="font-weight: bold;"><?php echo TEXT_MISSING_TAGS; ?></td>
              </tr>               
              <tr>
               <td class="HTC_subHead"><?php echo $missingTags; ?></td>
              </tr>
             <?php } ?>
            </table></td>
           </tr>
          </table></td>
        
          <td valign="top"><table border="0" width="100%">
           <tr>
            <td class="HTC_subHead"><?php echo TEXT_EXPLAIN_FILLTAGS; ?></td>
           </tr>
          </table></td>
   
         </tr>                  
        </table></td>  
       </tr> 
      </form></td>
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
