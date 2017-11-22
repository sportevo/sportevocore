<?php
/*
  $Id: catalog/admin/paps.php,v 1.2 20:57:52 NCB Exp $

 	by hanuman at Open Source Services
  Support and Forum at http://www.product-attribute-pictures.com/index.php/component/option,com_ccboard/Itemid,30/forum,3/view,topiclist/
*/
  require('includes/application_top.php');
  
  $action = (isset($_GET['action']) ? $_GET['action'] : '');
  
  $prod_id='';
  
  $unlink_pas = '';
  $arr_model_info = array();
   		
	$products = tep_db_query("select p.products_id, p.products_model, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id order by p.products_model");
				
  while ($products_values = tep_db_fetch_array($products)) {
  	$arr_model_info[$products_values['products_model']] = $products_values['products_name'];
  }
	
	require(DIR_WS_CLASSES . 'paps_delegate.php');
	$paps_delegate = new paps_delegate();
		
  if (tep_not_null($action)) {
		
    switch ($action) {

    	case 'get_paps':

     		if((isset($_POST['product_names'])) && ($_POST['product_names'] != "default")) {
    			$prod_id = $_POST['product_names'];
    		} elseif((isset($_POST['product_id_nos'])) && ($_POST['product_id_nos'] != "default")) {
    			$prod_id = $_POST['product_id_nos'];
    		}
    		
    		if(isset($_GET['prod_id'])) {   			
    			$prod_id = $_GET['prod_id'];
    		} 
    		
    		$arr_attrib_arrays = array();
    		
    		if($prod_id != '') {
					$arr_attrib_arrays = $paps_delegate->populateAttribArrays($prod_id);
    		}
    		
    	break;
    	
    	case 'delete_paps':
    	
    		$chk_no = $_POST['chk_count'];
    		$prod_id = $_POST['prod_id'];
				$files_to_delete = array();
				
    		for($i = 0; $i < $chk_no; $i++) {
    			$checkbox_id = 'check' . $i;
    			if(isset($_POST[$checkbox_id])) {
    				$files_to_delete[] = $_POST[$checkbox_id];
    			}
    		} 
    		
    		if((count($files_to_delete) > 0)&&(isset($_POST['attr_name']))) {   		
    			$paps_delegate->deletePics($files_to_delete, $prod_id, $_POST['attr_name']);
				} 
			
			
			tep_redirect(tep_href_link('paps.php', 'action=get_paps&prod_id='.$prod_id.'&delok=true'));
   	
    	break;
    	
    	case 'save_pap':

				$attname = $_POST['attr_name'][0];
				$att_vals = $_POST['att_val'];
				$prod_id = $_POST['prod_id'];
				$send_attvals = array();



			
					$save_path=DIR_FS_CATALOG . "images/paps/";
	
					$file = $_FILES['userfile'];
					$k = count($file['name']);
					$uploads_error = false;
					$filenames = array();
					for($i=0 ; $i < $k ; $i++) {
					
					
					if((isset($save_path)) && ($save_path!="") && ($att_vals[$i]!='')) {	

							$filenames[] = $file['name'][$i];
							$send_attvals[] = $att_vals[$i];
							if (move_uploaded_file($file['tmp_name'][$i], $save_path . $file['name'][$i])) {
									//success
							}
						}	
					}

		    	$paps_delegate->createNewRows($attname, $prod_id, $filenames, $send_attvals);
						
			  tep_redirect(tep_href_link('paps.php', 'action=get_paps&prod_id='.$prod_id.'&savok=true'));
  	
		    	
    	break;
    	
    	case 'update_txt_disp':
    		    		
    		if(isset($_POST['mn']) && isset($_POST['a_name']) && isset($_POST['san']) && isset($_POST['sav'])) {
    			$paps_delegate->updatePictureHeadings($_POST['mn'], $_POST['a_name'], $_POST['san'], $_POST['sav']);
    		}
    		
    		if(isset($_POST['mn']) && isset($_POST['a_name']) && isset($_POST['pics_row'])) {
    			$paps_delegate->updatePicsPerRow($_POST['mn'], $_POST['a_name'], $_POST['pics_row']);	
    		}
    		
    		if(isset($_POST['mn']) && isset($_POST['a_name']) && isset($_POST['t_wid']) && isset($_POST['m_wid'])) {
    			$paps_delegate->updatePicWidths($_POST['mn'], $_POST['t_wid'], $_POST['m_wid'], $_POST['a_name']);	
    		}
    		
    		if(isset($_POST['mn'])){
    			tep_redirect(tep_href_link('paps.php', 'action=get_paps&prod_id='.$_POST['mn']).'&utdok=true');
    		}
    	break;
    	
    	case 'update_prod_globs':
    		
    		if(isset($_POST['mn']) && isset($_POST['ulink'])) {
    				$paps_delegate->updateProductGlobals($_POST['mn'], $_POST['ulink']);
    		}
    		if(isset($_POST['mn'])){
    			tep_redirect(tep_href_link('paps.php', 'action=get_paps&prod_id='.$_POST['mn'].'&updok=true'));
    		}
    	break;
    	
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript"><!--
function go_option() {
  if (document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value != "none") {
    location = "<?php echo tep_href_link(FILENAME_PRODUCTS_ATTRIBUTES, 'option_page=' . ($_GET['option_page'] ? $_GET['option_page'] : 1)); ?>&option_order_by="+document.option_order_by.selected.options[document.option_order_by.selected.selectedIndex].value;
  }
}
//--></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="0" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top">
    	<table border="0" width="100%" cellspacing="0" cellpadding="0">
<!--  //BODY OF HTML HERE!!!!! -->

				<tr>
					<td width="100%">
					<table width="100%">
					<tr>
					<td align= "center"class="pageHeading"><br><?php echo PAPS_TITLE; ?>
					</td>
					<td align="right" class="smallText">
  					<table border="1" rules="none" frame="box">
  						<tr><td><a target="_blank" href="http://www.product-attribute-pictures.com/"><?php echo PAPS_UPGRADE; ?></a></td>
  						</tr>
  						<tr><td><a target="_blank" href="http://www.product-attribute-pictures.com/index.php/component/option,com_ccboard/Itemid,30/forum,3/view,topiclist/"><?php echo PAPS_SUPPORT; ?></a></td>
  						</tr>
  						
  					</table>
  				</td>
  				</tr>
  				</table>
  				</td>
				</tr>
  			<tr>
  				<td align="center">
  				<br>
  				<table>
  					<tr>
  				<td class="main"><?php echo PAPS_SELECT_PRODUCT_NAME; ?></td>
  				<td>
  					<form name="option" action="<?php echo tep_href_link(FILENAME_PAPS, 'action=get_paps', 'NONSSL'); ?>" method="post">
	  					<select onChange="submit();" name="product_names">
									<?php
									    $products = tep_db_query("select p.products_id, p.products_model, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by pd.products_name");
									    echo '<option SELECTED value="default">' . PAPS_CHOOSE . '</option>';
									    while ($products_values = tep_db_fetch_array($products)) {
									   
									      echo '<option name="' . $products_values['products_name'] . '" value="' . $products_values['products_id'] . '">' . $products_values['products_name'] . '</option>';
									    } 
									?>
	            </select>	            
  				</td>
  			</tr>
  			<tr>
  				<td class="main"><?php echo PAPS_SELECT_PRODUCT_NUMBER; ?></td>
  				<td>
  					<select onChange="submit();" name="product_model_nos">
								<?php
								    $products = tep_db_query("select p.products_id, p.products_model, pd.products_name from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where pd.products_id = p.products_id and pd.language_id = '" . $languages_id . "' order by p.products_model");
								    echo '<option SELECTED value="default">' . PAPS_CHOOSE . '</option>';
								    while ($products_values = tep_db_fetch_array($products)) {
								     
								      echo '<option name="' . $products_values['products_model'] . '" value="' . $products_values['products_id'] . '">' . $products_values['products_model'] . '</option>';
								    } 
								?>
	            </select>
	            </form>
  				</td>	
  				</tr>
  				</table>
  				</td>
  			</tr>
  			
  <?php 
 
  if((isset($arr_attrib_arrays[0][0]))&&($arr_attrib_arrays[0][0] != '')) { 
   	
  	
  			$arr_globals = $paps_delegate->getProductGlobals($prod_id); 
  ?>
  			
  			<tr>
					<td class="main" align="center"><br><b><?php echo PAPS_CURRENT_PICS; ?><u>
<!--					<?php echo $arr_model_info[$model_no] . ', Model No. ' . $model_no; ?> -->
					<?php echo $paps_delegate->getProdName($prod_id) . ', Model No. ' . $paps_delegate->getProdModel($prod_id); ?>
					
					</u></b>
					</td>
				</tr>
				<tr>
        	<td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      	</tr>
      	<?php if(isset($_GET['updok'])) { ?>
      		<tr>
        	<td class="smallText" align="center"><font color="green"><?php echo PAPS_UPD_SUCCESS; ?></font></td>
      	</tr>
      <?php } ?> 
				<tr>
					<td align="center" valign="top">
  					<form name="prod_globs" action="<?php echo tep_href_link(FILENAME_PAPS, 'action=update_prod_globs', 'NONSSL'); ?>" method="post">
  					<table border="1">
  						<tr class="dataTableHeadingRow">
  							<td align="center" class="dataTableHeadingContent" colspan="3"><?php echo PAPS_GLOBAL_SETTINGS; ?>
  							</td>
  						</tr>
  						<tr class="dataTableHeadingRow">
  							<td class="dataTableHeadingContent"><?php echo PAPS_UNLINK_PAS; ?>
  							</td>
  							<td class="dataTableHeadingContent"><input type="radio" name="ulink" value="1" <?php if($arr_globals['unlink']) echo 'CHECKED'; ?>><?php echo PAPS_YES; ?></td>
			 					<td class="dataTableHeadingContent"><input type="radio" name="ulink" value="0" <?php if(!$arr_globals['unlink']) echo 'CHECKED'; ?>><?php echo PAPS_NO; ?></td>
  						</tr>
  						<tr class="dataTableHeadingRow">
  							<td align="center" class="dataTableHeadingContent" colspan="3">

<input type="hidden" name="mn" value="<?php echo $prod_id; ?>" />
  							<?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?>
  							</td>
  						</tr>
  					</table>
  					</form>
  				</td>	
				</tr>
				<tr>
        	<td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      	</tr>
      	<?php if(isset($_GET['delok'])) { ?>
      		<tr>
        	<td class="smallText" align="center"><font color="green"><?php echo PAPS_DEL_SUCCESS; ?></font></td>
      	</tr>
      <?php } ?>
      	<?php if(isset($_GET['savok'])) { ?>
      		<tr>
        	<td class="smallText" align="center"><font color="green"><?php echo PAPS_FUL_SUCCESS; ?></font></td>
      	</tr>
      <?php } ?>
      <?php if(isset($_GET['utdok'])) { ?>
      		<tr>
        	<td class="smallText" align="center"><font color="green"><?php echo PAPS_UTD_SUCCESS; ?></font></td>
      	</tr>
      <?php } ?>
				<tr>	
					<td>
					<table width="100%">
						<tr>	
							
				<?php
		      	for($i = 0; $i < count($arr_attrib_arrays); $i++) {
					   ?>
			
					<td valign="top" align="center" width="<?php 100/count($arr_attrib_arrays); ?>"><br><form name="option<?php echo $i; ?>" action="<?php echo tep_href_link(FILENAME_PAPS, 'action=delete_paps', 'NONSSL'); ?>" method="post">
						<table align="center" border="1" rules="none" frame="box">
							<tr class="dataTableHeadingRow">
								<td class="dataTableHeadingContent" align="center">Attribute Name
								</td>
								<td class="dataTableHeadingContent" align="center">Attribute Value
								</td>
								<td class="dataTableHeadingContent" align="center">View
								</td>
								<td align="center"><img src='images/icons/delete.gif'/>
								</td>
							</tr>
				   <?php
				   	$rows = 0;
				   	for($x = 0; $x < count($arr_attrib_arrays[$i]); $x++) { 
				   		$arr_filenames = explode(':', $paps_delegate->getAllFiles($prod_id, $arr_attrib_arrays[$i][0]));
				   		$rows++; ?>
												
				  		<tr class="<?php echo (floor($rows/2) == ($rows/2) ? 'attributes-even' : 'attributes-odd'); ?>">
				   			<td align="center"><?php if($x == 0) echo '<b>' . str_replace('[',' ',$arr_attrib_arrays[$i][$x]) . 's</b>'; ?>
				   			</td>
				   			<td>
				   			<?php 
				   			//echo str_replace('[',' ',$paps_delegate->getAttributeOptionName($arr_attrib_arrays[$i][$x+1])); 
				   			if(isset($arr_attrib_arrays[$i][$x+1]))echo str_replace('[',' ',$arr_attrib_arrays[$i][$x+1]); 
				   			?>
				   			</td>
				   			<td align="center"><?php if($x < count($arr_attrib_arrays[$i])-1)echo '<a class="headerLink" target="_blank" href="../images/paps/' . $arr_filenames[$x] . '"><img src="images/icons/preview.gif"/></a>'; ?>
				   			</td>																																		
				   			<td align="center"><?php if($x < count($arr_attrib_arrays[$i])-1)echo '<input type="checkbox" name="check' . $x . '" value="' . $arr_attrib_arrays[$i][$x+1] . '" />';?>
				   			</td>
				   		</tr>
				   			
				   				   	
				   	<?php		} ?>
				   		<tr>
				   			<td colspan="4" align="right">
				   				<input type="hidden" name="chk_count" value="<?php echo (count($arr_attrib_arrays[$i])-1); ?>">
<input type="hidden" name="prod_id" value="<?php echo $prod_id; ?>">
<input type="hidden" name="attr_name" value="<?php echo $arr_attrib_arrays[$i][0]; ?>">

				   				<?php echo tep_image_submit('button_delete.gif', IMAGE_DELETE); ?>
				   				
				   			</td>
				   		</tr>
			 			</table>
			 			</form>
			 			<br>
			 			<?php
			 			$attr_display_arr = $paps_delegate->getAttributeHeaderDisplaySettings($arr_attrib_arrays[$i][0], $prod_id);
			 			$pics_num = $paps_delegate->getMaxPicsPerRow($arr_attrib_arrays[$i][0], $prod_id);
			 			$img_widths_arr = $paps_delegate->getImageWidths($arr_attrib_arrays[$i][0], $prod_id);
			 			?>
			 			<form name="txt_display<?php echo $i; ?>" action="<?php echo tep_href_link(FILENAME_PAPS, 'action=update_txt_disp', 'NONSSL'); ?>" method="post">
						<table align="center" border="1" rules="none" frame="box" cellspacing="0">
			 				<tr class="dataTableHeadingRow">
			 					<td class="dataTableHeadingContent"><?php echo PAPS_SHOW_ATTRIBUTE_NAME; ?></td>
			 					<td class="dataTableHeadingContent"><input type="radio" name="san" value="1" <?php if($attr_display_arr['san']) echo 'CHECKED'; ?>><?php echo PAPS_YES; ?></td>
			 					<td class="dataTableHeadingContent"><input type="radio" name="san" value="0" <?php if(!$attr_display_arr['san']) echo 'CHECKED'; ?>><?php echo PAPS_NO; ?></td>
			 				</tr>
			 				<tr class="dataTableHeadingRow">
			 					<td class="dataTableHeadingContent"><?php echo PAPS_SHOW_ATTRIBUTE_VALUES; ?></td>
			 					<td class="dataTableHeadingContent"><input type="radio" name="sav" value="1" <?php if($attr_display_arr['sav']) echo 'CHECKED'; ?>><?php echo PAPS_YES; ?></td>
			 					<td class="dataTableHeadingContent"><input type="radio" name="sav" value="0" <?php if(!$attr_display_arr['sav']) echo 'CHECKED'; ?>><?php echo PAPS_NO; ?></td>
			 				</tr>
			 				
			 				<tr class="dataTableHeadingRow">
			 					<td class="dataTableHeadingContent" colspan="2"><?php echo PAPS_NUM_ROWS; ?></td>
			 					<td class="dataTableHeadingContent"><input type="text" name="pics_row" value="<?php if($pics_num['pics_no']!=''){echo $pics_num['pics_no'];}else{echo '3';} ?>" size="2"></td>
			 				</tr>
			 				<tr class="dataTableHeadingRow">
			 					<td class="dataTableHeadingContent" colspan="2"><?php echo PAPS_THUMB_WIDTH; ?></td>
			 					<td class="dataTableHeadingContent"><input type="text" name="t_wid" value="<?php if($img_widths_arr['t_w']!=''){echo $img_widths_arr['t_w'];}else{echo '80';} ?>" size="2"></td>
			 				</tr>
			 				<tr class="dataTableHeadingRow">
			 					<td class="dataTableHeadingContent" colspan="2"><?php echo PAPS_MAIN_WIDTH; ?></td>
			 					<td class="dataTableHeadingContent"><input type="text" name="m_wid" value="<?php if($img_widths_arr['m_w']!=''){echo $img_widths_arr['m_w'];}else{echo '300';}  ?>" size="2"></td>
			 				</tr>
			 				<tr class="dataTableHeadingRow">			 				
			 					<td align="right" colspan="3">
	<input type="hidden" name="mn" value="<?php echo $prod_id; ?>" /> 
			 						<input type="hidden" name="a_name" value="<?php echo $arr_attrib_arrays[$i][0]; ?>" />	
			 						<?php echo tep_image_submit('button_update.gif', IMAGE_UPDATE); ?>
			 					</td>
			 				</tr>
						</table>
						</form>
			 			<br>
			 		</td>
			 
				<?php  }?>     					
				</tr>	
				</table>
				</tr>
				<tr>
					<td><?php echo tep_black_line(); ?>
					</td>
				</tr>
  			<?php } else if($prod_id != ''){ ?>
  			<tr>
					<td><br><br><br><?php echo tep_black_line(); ?>
					</td>
				</tr>
  			<tr>
					<td class="main" align="center"><br><b><?php echo PAPS_NO_PICS; ?><u>
					<?php echo $paps_delegate->getProdName($prod_id) . ', Model No. ' . $paps_delegate->getProdModel($prod_id); ?>
					</u></b>
					</td>
				</tr>
				<tr>
					<td><br><?php echo tep_black_line(); ?>
					<br></td>
				</tr>
			<?php } if($prod_id != '') {?>			
			<tr>
					<td class="main" align="center"><br><b><?php echo PAPS_ADD_A_PAP; ?></b>
					</td>
				</tr>
				<tr>
					<td class="smallText" align="center"><?php echo PAPS_JPG_REMINDER; ?>
					</td>
				</tr>
	
				<tr>
					<td>
						<table width="100%">
						<tr>
							<td align="center">
								<form enctype="multipart/form-data" action="<?php echo tep_href_link(FILENAME_PAPS, 'action=save_pap', 'NONSSL'); ?>" method="POST">
									<table align="center" border="1" rules="none" frame="box">
				
				<tr>
					<td colspan="3" align="right"></td>
				</tr>
				<tr class="dataTableHeadingRow">
					<td class="dataTableHeadingContent" align="center"><?php echo PAPS_ATTRIBUTE_NAME; ?></td>
					<td class="dataTableHeadingContent" align="center"><?php echo PAPS_ATTRIBUTE_VALUE; ?></td>
					<td class="dataTableHeadingContent" align="center"><?php echo PAPS_FILE; ?></td>
				</tr>
				
				<?php for($i=0; $i < 5; $i++) { ?>
					<tr class="attributes-even">
						<td align="right"><input type="text" name="attr_name[<?php echo $i; ?>]"/></td>
						<td align="center"><input type="text" name="att_val[<?php echo $i; ?>]"/></td>	
						<td align="left"><input type="file" name="userfile[<?php echo $i; ?>]"></td>
					</tr>					
				<?php } ?>
										
				<tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
 
				<tr>
					<td colspan="3" align="center">
<input type="hidden" name="prod_id" value="<?php echo $prod_id; ?>" />
						<?php echo tep_image_submit('button_upload.gif', IMAGE_UPLOAD); ?>
					</td>
				</tr>
			</table>
								</form>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				
  <?php } ?>
<!--  //END OF BODY OF HTML!!!!! -->
<tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      
  </table>
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
