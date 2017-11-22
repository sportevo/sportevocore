<?php

$code = 'function gd_info() {
       $array = Array(
                       "GD Version" => "",
                       "FreeType Support" => 0,
                       "FreeType Support" => 0,
                       "FreeType Linkage" => "",
                       "T1Lib Support" => 0,
                       "GIF Read Support" => 0,
                       "GIF Create Support" => 0,
                       "JPG Support" => 0,
                       "PNG Support" => 0,
                       "WBMP Support" => 0,
                       "XBM Support" => 0
                     );
       $gif_support = 0;

       ob_start();
       eval("phpinfo();");
       $info = ob_get_contents();
       ob_end_clean();
     
       foreach(explode("\n", $info) as $line) {
           if(strpos($line, "GD Version")!==false)
               $array["GD Version"] = trim(str_replace("GD Version", "", strip_tags($line)));
           if(strpos($line, "FreeType Support")!==false)
               $array["FreeType Support"] = trim(str_replace("FreeType Support", "", strip_tags($line)));
           if(strpos($line, "FreeType Linkage")!==false)
               $array["FreeType Linkage"] = trim(str_replace("FreeType Linkage", "", strip_tags($line)));
           if(strpos($line, "T1Lib Support")!==false)
               $array["T1Lib Support"] = trim(str_replace("T1Lib Support", "", strip_tags($line)));
           if(strpos($line, "GIF Read Support")!==false)
               $array["GIF Read Support"] = trim(str_replace("GIF Read Support", "", strip_tags($line)));
           if(strpos($line, "GIF Create Support")!==false)
               $array["GIF Create Support"] = trim(str_replace("GIF Create Support", "", strip_tags($line)));
           if(strpos($line, "GIF Support")!==false)
               $gif_support = trim(str_replace("GIF Support", "", strip_tags($line)));
           if(strpos($line, "JPG Support")!==false)
               $array["JPG Support"] = trim(str_replace("JPG Support", "", strip_tags($line)));
           if(strpos($line, "PNG Support")!==false)
               $array["PNG Support"] = trim(str_replace("PNG Support", "", strip_tags($line)));
           if(strpos($line, "WBMP Support")!==false)
               $array["WBMP Support"] = trim(str_replace("WBMP Support", "", strip_tags($line)));
           if(strpos($line, "XBM Support")!==false)
               $array["XBM Support"] = trim(str_replace("XBM Support", "", strip_tags($line)));
       }
       
       if($gif_support==="enabled") {
           $array["GIF Read Support"]  = 1;
           $array["GIF Create Support"] = 1;
       }

       if($array["FreeType Support"]==="enabled"){
           $array["FreeType Support"] = 1;    }
 
       if($array["T1Lib Support"]==="enabled")
           $array["T1Lib Support"] = 1;    
       
       if($array["GIF Read Support"]==="enabled"){
           $array["GIF Read Support"] = 1;    }
 
       if($array["GIF Create Support"]==="enabled")
           $array["GIF Create Support"] = 1;    

       if($array["JPG Support"]==="enabled")
           $array["JPG Support"] = 1;
           
       if($array["PNG Support"]==="enabled")
           $array["PNG Support"] = 1;
           
       if($array["WBMP Support"]==="enabled")
           $array["WBMP Support"] = 1;
           
       if($array["XBM Support"]==="enabled")
           $array["XBM Support"] = 1;
       
       return $array;
   }';

function gd_version() {
	global $code;
	if (empty($result)) {
		if (!function_exists('gd_info')) $gd_info = eval($code);
		else $gd_info = gd_info();
		
		if (substr($gd_info['GD Version'], 0, strlen('bundled (')) == 'bundled (') {
			$result = (float) substr($gd_info['GD Version'], strlen('bundled ('), 3); 
		} else {
			$result = (float) substr($gd_info['GD Version'], 0, 3); 
		}
	}
	return $result;
}

function ImageCreateFunction($x_size, $y_size) {
	$ImageCreateFunction = 'ImageCreate';
	if (gd_version() >= 2.0) {
		$ImageCreateFunction = 'ImageCreateTrueColor';
	}
	if (!function_exists($ImageCreateFunction)) {
		return FALSE;
	}
	return $ImageCreateFunction($x_size, $y_size);
}

function ImageCopyFunction($dst_im, $src_im, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH) {
	$ImageCopyFunction = 'ImageCopyResized';
	if (gd_version() >= 2.0) {
		$ImageCopyFunction = 'ImageCopyResampled';
	}
	if (!function_exists($ImageCopyFunction)) {
		return FALSE;
	}
	return $ImageCopyFunction($dst_im, $src_im, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
}

function generateThumb($sourceFilename, $thumb_x, $square=FALSE, $border=FALSE) {
	$image_info = getimagesize($sourceFilename);
	$image_width = $image_info[0];
	$image_height = $image_info[1];
	
	if ($image_width<$thumb_x) $thumb_x = $image_width;
	
	if ($square) { 
		$thumb_image_x = $thumb_x;
		$thumb_image_y = $thumb_x;
	}

	// workaround for v1.6.2 where the GIF images arent recognized.
	$ImgCreate = 'ImageCreateFromJPEG';
	switch ($image_info['mime']) {
		case 'image/gif':
			$ImgCreate = 'ImageCreateFromGIF';
			break;
		case 'image/jpeg':
			$ImgCreate = 'ImageCreateFromJPEG';
			break;
		case 'image/png':
			$ImgCreate = 'ImageCreateFromPNG';
			break;
	}

	if (!$square) {
		$shrinkratio = $image_width/$thumb_x;
		$thumb_y = $image_height/$shrinkratio;
		$start_x = 0;
		$start_y = 0;
	} else if ($square) {
		if ($image_width>$image_height) {
			$shrinkratio = $image_width/$thumb_x;
			$thumb_y = $image_height/$shrinkratio;
			$start_x = 0;
			$start_y = (abs($thumb_image_y - $thumb_y)) / 2;
		} else if ($image_width<=$image_height) {
			$shrinkratio = $image_height/$thumb_x;
			$thumb_y =$thumb_x;
			$thumb_x = $image_width/$shrinkratio;
			$start_y = 0;
			$start_x = (abs($thumb_image_x - $thumb_x)) / 2;
		}
	}
	
	$thumbInput = @$ImgCreate($sourceFilename); 

   	if (!$thumbInput) { /* See if it failed */
    	$thumbInput  = imagecreate($thumb_x, $thumb_y); /* Create a blank image */
       	$bgc = imagecolorallocate($thumbInput, 255, 255, 255);
       	$tc  = imagecolorallocate($thumbInput, 0, 0, 0);
       	imagefilledrectangle($thumbInput, 0, 0, 150, 30, $bgc);
       	/* Output an errmsg */
       	imagestring($thumbInput, 1, 5, 5, "Error loading $sourceFilename", $tc);
		imagejpeg($thumbInput,'',90);
		imagedestroy($thumbInput);
   	} else {
		if ($square) {
			$thumbOutput = ImageCreateFunction($thumb_image_x,$thumb_image_y) or die("couldn't create image"); 
			$border_x = $thumb_image_x - 1;
			$border_y = $thumb_image_y - 1;
		} else {
			$thumbOutput = ImageCreateFunction($thumb_x,$thumb_y) or die("couldn't create image"); 
			$border_x = $thumb_x - 1;
			$border_y = $thumb_y - 1;
		}
	
		$background_color = imagecolorallocate($thumbOutput, 255, 255, 255);
		imagefill($thumbOutput,0,0,$background_color);
		ImageCopyFunction($thumbOutput,$thumbInput,$start_x,$start_y,0,0,$thumb_x,$thumb_y,$image_width,$image_height) or die("coudln't resize image"); 
		if ($border) {
			$border_color = imagecolorallocate($thumbOutput, 0, 0, 0);
			imagerectangle($thumbOutput,0,0,$border_x,$border_y,$border_color) or die("couldn't create image"); 
		}
	
		imagejpeg($thumbOutput,'',90);
		imagedestroy($thumbOutput);
	}
}


$pic = $_GET['pic'];
$thumbWidth = $_GET['w'];

$isSquare = false;
$isBorder = false;

	if (isset($pic)&&$thumbWidth>0) {
		header("Content-type: image/jpeg");
		generateThumb($pic, $thumbWidth, $isSquare, $isBorder);
	} else if (!isset($pic)) {
		echo "<strong>ERROR:</strong> No image submitted";
	} else if ($thumbWidth<=0) {
		echo "<strong>ERROR:</strong> Invalid resizing option";
	}
?>