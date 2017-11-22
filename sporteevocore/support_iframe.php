<?php
      $ticketid = $_GET['ticket_id'];



  require('./includes/application_top.php');
      $ticket_query = tep_db_query("
SELECT 
CONCAT(c.customers_firstname, ' ', c.customers_lastname) AS customers_name, 
sh.ticket_comments, 
sh.department_id, 
sh.admin_id, 
sh.last_modified, 
sh.submitted_by,
d.support_department_name,
a.support_assign_name 

FROM " . TABLE_SUPPORT_TICKETS_HISTORY . "  sh, 
" . TABLE_SUPPORT_TICKETS . " t, 
" . TABLE_CUSTOMERS . " c, 
" . TABLE_SUPPORT_DEPARTMENT . " d, 
" . TABLE_SUPPORT_ASSIGN . " a 
WHERE sh.ticket_id = '$ticketid' AND 
t.ticket_id = sh.ticket_id AND 
c.customers_id = t.customers_id AND
sh.department_id = d.support_department_id AND 
sh.admin_id = a.support_assign_id AND 
d.language_id = '$languages_id' AND
a.language_id = '$languages_id' 
ORDER BY sh.support_history_id DESC");


  
	$thiscomments = '';

  while ($ticket_ind = tep_db_fetch_array($ticket_query)) {

  	if ($ticket_ind['submitted_by'] == 'customer'){
  		$thiscomments .= "<i><strong>". $ticket_ind['customers_name'] . "</strong> on " . $ticket_ind['last_modified'] . "</i> <br><span style=\"color:#0000ff\">" . $ticket_ind['ticket_comments'] . "</span><p>";
  		}
    else {
    	if ($ticket_ind['support_assign_name']){$support_name = '(' . $ticket_ind['support_assign_name'] . ')';}
    	$thiscomments .= "<i><strong>" . $ticket_ind['support_department_name'] . " (". $ticket_ind['support_assign_name'] . ")</strong> on " . $ticket_ind['last_modified'] . "</i><br><span style=\"color:#ff0000\">" . $ticket_ind['ticket_comments'] . "</span><p>";}

  }
  
//	$ticket['ticket_comments'] = $thiscomments;

  global $ticket;
  
  
  
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<base href="<?php echo (getenv('HTTPS') == 'on' ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG; ?>">


</head>
<body marginwidth="3" marginheight="3" topmargin="3" bottommargin="3" leftmargin="3" rightmargin="3">
	
<?php echo ((strlen($thiscomments) > 0) ? nl2br($thiscomments) : '<i>' . TEXT_NO_COMMENTS_AVAILABLE . '</i>'); ?>

</body>
</html>
