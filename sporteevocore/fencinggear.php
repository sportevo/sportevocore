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

$cwd=getcwd();
	chdir('/web/htdocs/www.primahometest.eu/home/prima/'); 
	include('includes/application_top.php');
	ob_start();
	
	
	include(DIR_WS_MODULES.'featured3.php');//include the file for the box you want
	$sbox=ob_get_contents();//save it in a variable for later use
	ob_clean();//clean the buffer
	
	$sbox=str_replace("src=\"images", "src=\"http://www.primahometest.eu/prima/images",$sbox);
	
	chdir($cwd);


//********* COPY THE CODE ABOVE TO THE TOP OF THE FILE YOU WANT YOUR BOX IN *******\\
?>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<link href="stylesheet2.css" rel="stylesheet" type="text/css" />

<div id="glider">
<div id="image">&gt;&gt;
      </div>
<div id="image2">&lt;&lt;
      </div>
            
 <div id="slider">
      
      <div id="content">
      <?php 
	echo $sbox; //output your box here (or wherever)
?>

      </div>
 </div>
</div>
<script>    $(function() {
    $('#image').toggle(function (){
        $("#slider").animate({"left":"-50%"}, 1000);
    }, function() {
        $("#slider").animate({"left":"-100%"}, 1000);
    });
    
}); $(function() {
    $('#image2').toggle(function (){
        $("#slider").animate({"left":"0%"}, 1000);
    }, function() {
        $("#slider").animate({"left":"0%"}, 1000);
    });
    
});</script>