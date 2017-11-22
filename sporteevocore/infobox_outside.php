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
	include(DIR_WS_BOXES.'search2.php');//include the file for the box you want
	$sbox=ob_get_contents();//save it in a variable for later use
	ob_clean();//clean the buffer
	include(DIR_WS.'products_new.php');//include another file for the box you want
	$wbox=ob_get_contents();//save it in a variable for later use too
	ob_end_clean();//stop buffering

	//replace relative image paths with absolute urls (you may not need this. i did.)
	$sbox=str_replace("src=\"images", "src=\"http://www.schermaontc.com/ecom/images",$sbox);
	$wbox=str_replace("src=\"images", "src=\"http://www.schermaontc.com/ecom/images",$wbox);

	chdir($cwd);//change back to original working directory

//********* COPY THE CODE ABOVE TO THE TOP OF THE FILE YOU WANT YOUR BOX IN *******\\
?>

<!-- ********* SAMPLE PAGE USAGE BELOW ********* -->

<html>
<head>
<!--make sure to include the stylesheet if you want the box to have same style as your shop-->
<style type="text/css">

#cover {
	position: absolute;
	top: 0%;
	left: 0px;
	height: 10%;
	width: 100%;
	background: #ffffff;
	background-color: #0093F0;
	}

body {
	margin: 0px;
	padding: 0px;
}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"></head>
<body bgcolor="#0093F0">

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