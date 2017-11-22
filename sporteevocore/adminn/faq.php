<?php
/*
  $Id: faq.php v.0.1 26.05.2002 
  povered by Adgrafics-Ukraine http://adgrafics.net 
  victor@zolochevsky.com

  The Exchange Project - Community Made Shopping!
  http://www.theexchangeproject.org 

  Copyright (c) 2015/0,2001 The Exchange Project

  Released under the GNU General Public License
*/

  require('includes/application_top.php');
  require(DIR_WS_LANGUAGES . $language . '/' . 'faq.php');
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
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


    <td width="100%" valign="top">
<table border=0 width="100%">
<?

// include(DIR_WS_FUNCTIONS . 'faq.php');

switch($adgrafics_faq) {

case "Added":
		$data=browse_faq();
 		$no=1;
		 if (sizeof($data) > 0) {while (list($key, $val)=each($data)) {$no++; }	} ;
		$title="" . ADD_QUEUE_FAQ . " #$no";
		echo form("$PHP_SELF?adgrafics_faq=AddSure", $hidden);
		include(DIR_WS_INCLUDES . 'faq_form.php');
	break;

case "AddSure":
		if ($v_order && $answer && $question) {
		if ((INT)$v_order) {
		add_faq($_POST);
		$data=browse_faq();
		$title="$confirm " . ADD_QUEUE_FAQ . " #$v_order " . SUCCED_FAQ . "";
		include(DIR_WS_INCLUDES . 'faq_list.php');
		} else {$error="20";}
		} else {$error="80";}

	break;

case "Edit":
		if ($faq_id) {
		$edit=read_data($faq_id);
		$hidden[faq_id]=$faq_id;
		$data=browse_faq();
		$button=array("Update");
		$title="" . EDIT_ID_FAQ . " #$faq_id";
		echo form("$PHP_SELF?adgrafics_faq=Update", $hidden);
		include(DIR_WS_INCLUDES . 'faq_form.php');
		} else {$error="80";}
	break;

case "Update":
		if ($faq_id && $question && $answer && $v_order) {
		if ((INT)$v_order) {
		update_faq($_POST);
		$data=browse_faq();
		$title="$confirm " . UPDATE_ID_FAQ . " #$faq_id " . SUCCED_FAQ . "";
		include(DIR_WS_INCLUDES . 'faq_list.php');
		} else {$error="20"; } 
		} else {$error="80";}
	break;

case "Activation":
		$data[faq_id]= $faq_id;
		$data[visible]= 1;
		$data[v_order]= $v_order;
		$data[question]= $question;			
		$data[answer]= $answer;			
		$data[date]= $date;
		update_faq($data);
		$data=browse_faq();			
		$title="$confirm " . ACTIVATION_ID_FAQ . " #$faq_id " . SUCCED_FAQ . "";
		include(DIR_WS_INCLUDES . 'faq_list.php');
	break;

case "Deactivation":
		$data[faq_id]= $faq_id;
		$data[visible]= 0;
		$data[v_order]= $v_order;
		$data[question]= $question;			
		$data[answer]= $answer;			
		$data[date]= $date;
		update_faq($data);
		$data=browse_faq();			
		$title="$confirm " . DEACTIVATION_ID_FAQ . " #$faq_id  " . SUCCED_FAQ . "";
		include(DIR_WS_INCLUDES . 'faq_list.php');

	break;

case "Delete":
		if ($faq_id) {
		$delete=read_data($faq_id);
		$data=browse_faq();
		$hidden[faq_id]=$faq_id;
		$title="" . DELETE_CONFITMATION_ID_FAQ . " #$faq_id";
		echo "<tr class=pageHeading><td>$title  </td></tr>";
		echo "<tr><td>" . QUESTION_FAQ . " $delete[question]</td></tr>";
		echo "<tr><td>" . ANSWER_FAQ . " $delete[answer]</td></tr><tr><td align=right>";
		echo form("$PHP_SELF?adgrafics_faq=DelSure&faq_id=$val[faq_id]", $hidden);
		echo image_submit("button_delete.gif",DELETE_FAQ);
		$ims=img("button_cancel.gif",ADMINISTRATOR_FAQ);
		echo href("$PHP_SELF", "$ims");
		echo "</form></td></tr>";
		} else {$error="80";}
		break;


case "DelSure":
		if ($faq_id) {
		delete_faq($faq_id);
		$data=browse_faq();
		$title="$confirm " . DELETED_ID_FAQ . " #$faq_id ";
		include(DIR_WS_INCLUDES . 'faq_list.php');
		} else {$error="80";}
		break;
default:
		$data=browse_faq();
		$title="" . ADMINISTRATOR_FAQ . "";
		include(DIR_WS_INCLUDES . 'faq_list.php');
	}
if ($error) {
		$content=error_message($error);
		echo $content;
		$data=browse_faq();
 		$no=1;
		 if (sizeof($data) > 0) {while (list($key, $val)=each($data)) {$no++; }	} ;
		$title="" . ADD_QUEUE_FAQ . " #$no";
		echo form("$PHP_SELF?adgrafics_faq=AddSure", $hidden);
		include(DIR_WS_INCLUDES . 'faq_form.php');
}
?>
</table>
</td>


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
