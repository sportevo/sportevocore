<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?php
/********************************************************
CONTENT OUTSIDE OF OSC V1.0        

	Author: David Vance
	Email: dv@josheli.com
	Date: Aug. 11, 2003

	OSC 2.2 MS1
	PHP Version 4.3.2

	This snippet is an example of placing Francesco Rossi
  content on your site outside of the OSC 
  directory structure.
  
  It allows you to display any infoBox on your site.

********************************************************/

	//saves current working directory for later return
	$cwd=getcwd();

	//changes current working directory to osc root install directory; 
	//something like: /home/david/www/catalog/ but not DIR_FS_CATALOG
	chdir('/web/htdocs/www.schermaontc.com/home/ecom/'); 

	//need all of application_top's configurations and includes
	//NO OUTPUT ABOVE THIS POINT!
	include('includes/application_top.php');

	ob_start(); //start buffering
	include(DIR_WS_MODULES.'new_products2.php');//include the file for the box you want
	$sbox=ob_get_contents();//save it in a variable for later use
	ob_clean();//clean the buffer

	ob_end_clean();//stop buffering

	//replace relative image paths with absolute urls (you may not need this. i did.)
	$sbox=str_replace("src=\"images", "src=\"http://www.schermaontc.com/ecom/images",$sbox);
	$wbox=str_replace("src=\"images", "src=\"http://www.schermaontc.com/ecom/images",$wbox);

	chdir($cwd);//change back to original working directory

//********* COPY THE CODE ABOVE TO THE TOP OF THE FILE YOU WANT YOUR BOX IN *******\\
?>

<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
a {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #E0160C;
	text-decoration: underline;
}
body {
	font-family: Arial, Helvetica, sans-serif;
	font-size: 9px;
	color: #000000;
	margin-top: 12px;
	margin-bottom: 3px;
}
-->
</style>
</head>

<body>
<center>

<!-- since we included application top, we can use OSC's defined constants like BOX_WIDTH later in the page, even after we change back to working directory outside of OSC -->
<table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="0">

<?php 
	echo $sbox; //output your box here (or wherever)
?>

</table>





</center>
</body>
</html>
