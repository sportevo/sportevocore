<?php
/*
  $Id: pdf_price_list.php,v 1.00 2005/09/27 ep Exp $
  
  by by Infobroker, Germany

 
  Erich Paeper - info@cooleshops.de

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
*/

  
//define('FPDF_FONTPATH','font/');
require('pdf_config_price_list.php');
require('pdf_fpdf.php');
require('includes/application_top.php');

$products_index_array;

class PDF extends FPDF
{
//Current column
var $col=0;
//Ordinate of the beginning of the columns
var $y0;
var $categories_string_spe = '';
var $categories_string = '';
var $categories_id = '';
var $levels = '';
//var $parent_category_name;  Take out for PHP5 and take out $ pre all $parent_category_name
var $ifw = 0;     //internal width  margin for the products (image and text) description
var $text_fw = 100; //text width for the products (text) description
var $ifh = 0;     //internal height margin for the products description 
var $products_index_array;
var $products_index_list='';

 function Header()
 {
    
  //Background Color
    $background_color_table=explode(",",BACKGROUND_COLOR);
    $this->SetFillColor($background_color_table[0], $background_color_table[1], $background_color_table[2]);
    $this->ifw = $this->fw * 0.95; // A4 portrait = 200 
    $this->ifh = $this->fh * 0.87; // A4 portrait = 260
    $this->Rect(0,0,$this->fw,$this->fh,F); // Draw background
	
    //Logo: If LOGO_IMAGE defined, show image with logo, else show text
    if (PDF_LOGO) {
        $this->Image(DIR_FS_CATALOG.DIR_WS_IMAGES.PDF_LOGO,10,8,0,29);
    } else {	
        $this->SetFont('Arial','B',18);
	$this->SetLineWidth(0);
        $w=$this->GetStringWidth(PDF_TITLE)+16;
        //$this->SetX((210-$w)/2);
	$this->SetFillColor(100,100,100);
        $this->Cell($w,9,PDF_TITLE,0,0,'C');
    }
    //Year in court
    $aujourdhui = getdate();
    $annee = strftime(PDF_DATE_FORMAT);

    $this->SetFont('Arial','B',12);
    $this->Cell(0,9,$annee."    ",0,1,'R');
    if (PDF_LOGO) {
        $this->Ln(20);
    } else {
        $this->Ln(2);
    } 
    $x=$this->GetX();
    $y=$this->GetY();
    $this->Line($x,$y,$this->ifw,$y);
    $this->Ln(1);
    //Schutz der Ordinate
    $this->y0=$this->GetY();
 }

 function Footer()
 {
    //Footer
    $this->SetY(-15);
    $x=$this->GetX();
    $y=$this->GetY();
    $this->SetLineWidth(0.2);
    $this->Line($x,$y,$this->ifw,$y);
    $this->SetFont('Arial','I',8);
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}   ',0,0,'R');
 }
 
 function CheckPageBreak($h)
 {
    //Wenn die H�he h , manuellen Seitensprung verursacht //$x=$this->SetX(56);
    if($this->GetY()+$h>$this->PageBreakTrigger)    $this->AddPage($this->CurOrientation);
 }
 
    function NbLines($w,$txt)
    {
	//Rechnen Sie die Anzahl der Linien, da� MultiCell von Breite w besetzt
	$cw=&$this->CurrentFont['cw'];
	if($w==0)
		$w=$this->w-$this->rMargin-$this->x;
	$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
	$s=str_replace("\r",'',$txt);
	$nb=strlen($s);
	if($nb>0 and $s[$nb-1]=="\n")
		$nb--;
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$nl=1;
	while($i<$nb)
	{
		$c=$s[$i];
		if($c=="\n")
		{
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$nl++;
			continue;
		}
		if($c==' ')
			$sep=$i;
		$l+=$cw[$c];
		if($l>$wmax)
		{
			if($sep==-1)
			{
				if($i==$j)
					$i++;
			}
			else
				$i=$sep+1;
			$sep=-1;
			$j=$i;
			$l=0;
			$nl++;
		}
		else
			$i++;
	}
	return $nl;
    }

 function LineString($x,$y,$txt,$cellheight)
 {
    //calculate the width of the string
    $stringwidth=$this->GetStringWidth($txt);
    //calculate the width of an alpha/numerical char
    $numberswidth=$this->GetStringWidth('1');
    $xpos=($x+$numberswidth);
    $ypos=($y+($cellheight/2));
    $this->Line($xpos,$ypos,($xpos+$stringwidth),$ypos);
 }
 
 function ShowImage(&$width,&$height,$link,$path)
 {
    $width=min($width,MAX_IMAGE_WIDTH);
    $height=min($height,MAX_IMAGE_HEIGHT);

    if(RESIZE_IMAGES) {
	$destination =DIR_FS_CATALOG."catalogues/";
	if(substr(strtolower($path), (strlen($path)-4),4)==".jpg" || substr(strtolower($path), (strlen($path)-5),5)==".jpeg") {
            $src=imagecreatefromjpeg($path);
   	} else if (substr(strtolower($path), (strlen($path)-4),4)==".png") {
      	    $src=imagecreatefrompng($path);
   	} else {
      	    echo "Only PNG and JPEG";
            exit();
   	}
   
   	$array=explode("/", $path);
   	$last=sizeof($array);
        $size = getimagesize($path);
   	if($size[0] > $size[1]) {
     	    $im=imagecreate($width/PDF_TO_MM_FACTOR, $height/PDF_TO_MM_FACTOR);
            imagecopyresized($im, $src, 0, 0, 0, 0,$width/PDF_TO_MM_FACTOR, $height/PDF_TO_MM_FACTOR, $size[0], $size[1]);
   	} else {
     	    $im=imagecreate($height/PDF_TO_MM_FACTOR,$width/PDF_TO_MM_FACTOR);
            imagecopyresized($im, $src, 0, 0, 0, 0, $height/PDF_TO_MM_FACTOR, $width/PDF_TO_MM_FACTOR, $size[0], $size[1]);
  	}
  	if(!imagejpeg($im, $destination.$array[$last-1])) {
    	    exit();
    	}

        $path=$destination.$array[$last-1];
        $this->SetLineWidth(1);  
	$this->Cell($width+3,$height,"",1,0);
	$this->SetLineWidth(0.2);
	$this->Image($path,($this->GetX()-$width), $this->GetY(), $width, $height,'',$link);
	$this->SetFont('Arial','',8);
	unlink($path);
    } else {
	$this->SetLineWidth(1);
	// NH $this->Cell($width,$height,"",1,0);
	$this->Cell($width+3,$height,"",SIZE_BORDER_IMAGE,0);
	$this->SetLineWidth(0.2);
	//NH $this->Image($path,($this->GetX()-$width), $this->GetY(), $width, $height,'',$link);
	$this->Image($path,($this->GetX()-$width), $this->GetY(),$width ,'' ,'',$link);
	$this->SetFont('Arial','',8);
    }
 }


//Ordnen des Baumes (Niveau ist das Niveau der unter- Kategorie)
 function Order($cid, $level, $foo, $cpath)
 {
    if ($cid != 0) {
	if($level>1) {
	    $nbspaces=7;
            $dessinrep="|___ ";
	    //ich drehe die Zeichnung um
	    $revstring = strrev($dessinrep);
            //ich ihn f�ge nbspace f�r jedes Niveau von unter- repertoire hinzu
	    $revstring .= str_repeat(" ",$nbspaces*($level-2));
	    //je r�inverse la chaine
	    $this->categories_string_spe .= strrev($revstring);			  
	} 
	$this->levels .=$level." ";
	$this->categories_id .= $cid." ";
	$this->categories_string .= $foo[$cid]['name'];
        $this->categories_string_spe .=  $foo[$cid]['name'];
     
        if (SHOW_COUNTS) {
            $products_in_category = tep_products_in_category_count($cid,'false');
            if ($products_in_category > 0) {
                $this->categories_string_spe .= ' (' . $products_in_category . ')';
            }
        }
	$this->categories_string .= "\n";
        $this->categories_string_spe .= "\n";
    }
    //To traverse the tree of the categories (reading of the table of chopping as in Perl)
    if (sizeof($foo) > 0 ) {
        foreach ($foo as $key => $value) {
            if ($foo[$key]['parent'] == $cid) {
                $this->Order($key, $level+1, $foo, $cid);
            }
        }
    }
 }

// Revision the function for PHP 5  
 function ParentsName($current_category_level,$i,&$categorieslevelsarray, &$categoriesnamearray)
 {
    $k=$i;
    while($k>0)	{
    	if($categorieslevelsarray[$k] == ($current_category_level-1)) {		
	    $this->parent_category_name=$categoriesnamearray[$k];
            break;
    	}	
	$k--;
    }
 }
 
 function CalculatedSpace($y1,$y2,$imageheight)
 {
    //Wenn die Kommentare sind - wichtig, da� das Bild beim Anschlagraum
    if(($h2=$y2-$y1) < $imageheight) {
        $this->Ln(($imageheight-$h2)+3);
    } else {
        $this->Ln(3);
    }
 }
 
  function PrepareIndex($name,$manufacturer,$category)
 {
    $this->products_index_array[] = array (
                                        'name' => substr($name,0,55),
                                        'manufacturer' => substr($manufacturer,0,20),
                                        'category' => substr($category,0,18),
                                        'page' => $this->PageNo());
 }

  function DrawIndex()
 {
    //5 = H�he der Zellen
    $h= 5 * sizeof($this->products_index_array) ."<br>";
    if($h< $this->ifh) {
	$this->CheckPageBreak($h);
    }
    $this->AddPage();
    $this->Ln(5);
//    echo "<br>HHHH sizeof= " . sizeof($this->products_index_array);

    if (!function_exists(CompareIndex)) {
        function CompareIndex($a, $b)
       {
    //        return strcmp($a['name'], $b['name']);
            return strncasecmp($a['name'],$b['name'],8); // seulement les 8 premiers caracteres
       }
    }
    usort($this->products_index_array, CompareIndex);

    $this->SetFont('Courier','B',11);
    $this->Cell(1,11,"",0,0);
    $this->MultiCell($this->ifw,11,PDF_INDEX_HEADER,0,'C');
    $this->SetFont('Courier','',11);
    if (strlen(INDEX_SEPARATOR) < 1) {
        $index_separator=" ";
    } else {
        $index_separator=INDEX_SEPARATOR;
    }
    foreach ($this->products_index_array as $key => $value) {
        if (strlen($value['manufacturer']) > 0) {
            $ligne_index = str_pad($value['name']." - ". $value['manufacturer'],53,$index_separator,STR_PAD_RIGHT);
        } else {
            $ligne_index = str_pad($value['name'],53,$index_separator,STR_PAD_RIGHT);
        }
	$ligne_index .= str_pad($value['category'],18,$index_separator,STR_PAD_LEFT);
	$ligne_index .= str_pad($value['page'], 5, $index_separator, STR_PAD_LEFT);
	$this->Cell(1,6,"",0,0);
	$this->MultiCell(0,6,$ligne_index,0,'C');
//        echo "<br>HHHH : " . $ligne_index;
    }
//    echo "<br>HHHH wpt =" .$this->wPt .  " fw =" . $this->fw.  " ifw =" . $this->ifw ." text_fw =" . $this->text_fw;
//    echo "<br>HHHH hpt =" .$this->hPt .  " fh =" . $this->fh.  " ifh =" . $this->ifh;
 }

 function DrawCells($data_array)
 {
    $totallines=0;
	 for($i=2;$i<(sizeof($data_array)-1);$i++)
	 {
	    $totallines+=$this->NbLines(($this->ifw -$data_array[0]),$data_array[$i]);
	 }
	 
	 //5 = H�he der Zellen - eigentlich Faktor f�r F�llung der Seite
	 $h=1*($totallines+1)."<br>";
	 
	 //wenn die Beschreibung des Produktes nicht die ganze Seite nimmt
	 if($h< $this->ifh)
	 {
	    $this->CheckPageBreak($h);
	 }
	 
	 
	 if(SHOW_PRODUCTS_LINKS)
	 { // NH   DIR_WS_CATALOG
	 	$link=HTTP_CATALOG_SERVER . DIR_WS_CATALOG ."product_info.php?products_id=".$data_array[10]."&language=".$data_array[11];
	 }
	 else
	 {
	 	 $link='';
	 }
	 
	 if(SHOW_IMAGES && strlen($data_array[12]))
	 {	
	 	//If Small Image Width and Small Image Height are defined
	 	if(strlen($data_array[0])>1 && strlen($data_array[1])>1)
		{
	 	    $this->ShowImage($data_array[0],$data_array[1],$link,$data_array[12]);
                    $y1=$this->GetY();
		}
                //If only Small Image Width is defined
		else if(strlen($data_array[0])>1 && strlen($data_array[1]))
		{   
		    $heightwidth=getimagesize($data_array[12]);
		    $data_array[0]=$data_array[0];
		    $data_array[1]=$heightwidth[1]*PDF_TO_MM_FACTOR;
                    $this->ShowImage($data_array[0],$data_array[1],$link,$data_array[12]);
	 	    $y1=$this->GetY();
		}
		//If only Small Image Height is defined
		else if(strlen($data_array[0]) && strlen($data_array[1])>1)
		{
		    $heightwidth=getimagesize($data_array[12]);
                    $data_array[0]=$width=$heightwidth[0]*PDF_TO_MM_FACTOR;
		    $data_array[1]=$data_array[1];
	 	    $this->ShowImage($data_array[0],$data_array[1],$link,$data_array[12]);
                    $y1=$this->GetY();
		}
		else
		{
		    $heightwidth=getimagesize($data_array[12]);
                    $data_array[0]=$heightwidth[0]*PDF_TO_MM_FACTOR;
		    $data_array[1]=$heightwidth[1]*PDF_TO_MM_FACTOR;
                    $this->ShowImage($data_array[0],$data_array[1],$link,$data_array[12]);
	 	    $y1=$this->GetY();
		}
		
		//Margin=10
		$this->SetX(10);
	}
	else
	{
		$data_array[0]=$data_array[1]=0;
		$y1=$this->GetY();
		$this->SetFont('Arial','',8);
	}
	// Calculation open space has right image
        $this->text_fw = $this->ifw - 18 - $data_array[0];
	
		 if(SHOW_MODEL)
	 {
	 	if(strlen($data_array[3]))
		{
	 		$this->Cell($data_array[0]+6,5,"",0,0);
			    $x=$this->GetX();
                $y=$this->GetY();

	 		$this->Cell(40,8," ".$data_array[3]."",PRODUCTS_BORDER,'L');
		}
	 }
 
	 if(SHOW_NAME)
	 {
	    if(strlen($data_array[2]))
	    {
	        // Cell(marge gauche, hauteur, text, bordure, )
		$this->Cell($data_array[0]+6,5,"",0,0);
			$x=$this->SetX(56);
           // $y=$this->SetY(0);
        //        $name_color_table=explode(",",NAME_COLOR);
            //    $this->SetFillColor($name_color_table[0],$name_color_table[1],$name_color_table[2]);
 		$this->Cell(90,8,$data_array[2],PRODUCTS_BORDER,'L',1);
		
            }
	 }

	 if(SHOW_DATE_ADDED)
	 {
	 	if(strlen($data_array[4]))
		{
	    	$this->Cell($data_array[0]+6,5,"",0,0);
	 		$this->Cell($this->text_fw,5,$data_array[4],PRODUCTS_BORDER,'L');
		}
	 }
	 if(SHOW_MANUFACTURER)
	 {
	    if(strlen($data_array[5]))	{
	 	$this->Cell($data_array[0]+6,5,"",0,0);
		$x=$this->SetX(146);
	    $this->SetFont('Arial','I');
   		$this->Cell(30,8,$data_array[5],PRODUCTS_BORDER,'L');
		$this->SetFont('Arial','');
	    }
	 }
	 // NH  sI it does not have there a edge, addition of a small separator
/*if (!PRODUCTS_BORDER) {
            $this->Cell($data_array[0]+0,0,"",0,0);
           // $x=$this->GetX();
			//$x=$this->SetX(-30);
           // $y=$this->GetY();
            $this->Cell(0,0,"",0,'L');
            $this->LineString(0,0,"",0);
           // $this->Line($x+4,$y,$x+15,$y);
	 }*/
	 // NH  if there is no edge, addition of a small separator
	 if (!PRODUCTS_BORDER) {
            $this->Cell($data_array[0]+6,2,"",0,0);
            $x=$this->GetX();
            $y=$this->GetY();
            $this->Cell($this->text_fw,1,"",0,'C');
            //$this->LineString($x+3,$y,"                 ",2);
            $this->Line($x,$y,$x,$y);
	 }
	 
	 if(SHOW_DESCRIPTION)
	 {
	 	if(strlen($data_array[6]))
		{
	 		$this->Cell($data_array[0]+6,5,"",0,0);
	 		$this->Cell($this->text_fw,5,$data_array[6],PRODUCTS_BORDER,'L');
		}
	 }
	 if(SHOW_TAX_CLASS_ID)
	 {
	 	if(strlen($data_array[7]))
		{
                        $this->Cell($data_array[0]+6,5,"",0,0);
	 		$this->Cell($this->text_fw,5,$data_array[7],PRODUCTS_BORDER,'L');
		}
	 
	 }
	 if(VAT == '1')
	 {
	 	 $vatprice_query=tep_db_query("select p.products_id, p.products_tax_class_id, tr.tax_rate from " . TABLE_PRODUCTS . " p, " . TABLE_TAX_RATES . " tr where p.products_id = '" . $data_array[10] . "' and p.products_tax_class_id = tr.tax_class_id");
		while($vatprice1 = tep_db_fetch_array($vatprice_query)) {
		$steuer = $vatprice1['tax_rate'];
		}
		$vatprice=sprintf("%01.".DIGITS_AFTER_DOT."f",(($steuer/100)*$data_array[9])+$data_array[9]);
		$vatspecialsprice=sprintf("%01.".DIGITS_AFTER_DOT."f",(($steuer/100)*$data_array[8])+$data_array[8]);
	 }
	 else
	 {
	 	$vatprice=sprintf("%01.".DIGITS_AFTER_DOT."f",$data_array[9]);
	 	$vatspecialsprice=sprintf("%01.".DIGITS_AFTER_DOT."f",$data_array[8]);
	 }
	 if(SHOW_PRICES)
	 {
            // NH, wenn es keinen Rand gibt, Zusatz eines kleinen separateur
  /*             if (!PRODUCTS_BORDER) {
                    $this->Cell($data_array[0]+6,5,"",0,0);
                   // $x=$this->GetX();
                   // $y=$this->GetY();
                    $this->Cell($this->text_fw,1,"",0,'C');
                    //$this->LineString($x+3,$y,"                 ",2);
                    $this->Line($x+4,$y,$x+15,$y);
               } */

                if (!PRODUCTS_BORDER) {
                    $this->Cell($data_array[0]+6,2,"",0,0);
                    $x=$this->GetX();
                    $y=$this->GetY();
                    $this->Cell($this->text_fw,1,"",0,'C');
                    //$this->LineString($x+3,$y,"                 ",2);
                    $this->Line($x+4,$y,$x+15,$y);
                }


	 	if(strlen($data_array[8])) //If special price 
		{		
		    $this->Cell($data_array[0]+6,5,"",0,0);
	        $x=$this->SetX(176);
		    //$y=-5;
             //$x=$this->GetX();
		   // $y=$this->SetY(0);
		    $specials_price_color_table=explode(",",SPECIALS_PRICE_COLOR);
		    $this->SetTextColor($specials_price_color_table[0],$specials_price_color_table[1],$specials_price_color_table[2]);
		    $this->SetFont('Arial','B','');


		    if(CURRENCY_RIGHT_OR_LEFT == 'R') {
            $this->Cell(25,8,$vatprice.CURRENCY."\t\t\t".$vatspecialsprice.CURRENCY,PRODUCTS_BORDER,'L'); // die Hinzuf�gung eines param,1 gef�llt die Grundfarbe );
		    } else if (CURRENCY_RIGHT_OR_LEFT == 'L') {
	  		$this->Cell($this->text_fw,8,CURRENCY.$vatprice."\t\t\t".CURRENCY.$vatspecialsprice,PRODUCTS_BORDER,'L'); // die Hinzuf�gung eines param,1 gef�llt die Grundfarbe );
		    } else {
                        echo "<b>Choose L or R for CURRENCY_RIGHT_OR_LEFT</b>";
			exit();
		    }
		     //$x=$this->SetX(176);

            $this->LineString('176',$y,$vatprice.CURRENCY,8);
		}
		else if(strlen($data_array[9]))
		{
		    $this->Cell($data_array[0]+6,5,"",0,0);
			  $x=$this->SetX(176);
			//  $y=-5;
                    if(CURRENCY_RIGHT_OR_LEFT == 'R') {
			$this->Cell(25,8,$vatprice.CURRENCY,PRODUCTS_BORDER,'L');
		    }else if(CURRENCY_RIGHT_OR_LEFT == 'L') {
			$this->Cell($this->text_fw,8,CURRENCY.$vatprice,PRODUCTS_BORDER,'L');
		    } else {
		    	echo "<b>Choose L or R for CURRENCY_RIGHT_OR_LEFT</b>";
	    		exit();
		    }	
		}
		$this->SetTextColor(0,0,0);
	 }
	 $y2=$this->GetY();
	 
	 // wenn die Beschreibung des Produktes nicht die ganze Seite nimmt
	 if($h< $this->ifh)
	 {
		 $this->CalculatedSpace($y1,$y2,$data_array[1]);
 	 }
	 else
	 {
	 	$this->Ln(5);
	 }
 
 }
 
  function CategoriesTree($languages_id,$languages_code)
 { 
    //selectionne all categories
    $query = "SELECT c.categories_id, cd.categories_name, c.parent_id
              FROM " . TABLE_CATEGORIES . " c, " . TABLE_CATEGORIES_DESCRIPTION . " cd
              WHERE c.categories_id = cd.categories_id and cd.language_id='" . $languages_id ."'
	      ORDER by sort_order, cd.categories_name";

    $categories_query = tep_db_query($query);
    while ($categories = tep_db_fetch_array($categories_query)) {
        //Count of chopping
        $foo[$categories['categories_id']] = array(
					'name' => $categories['categories_name'],
					'parent' => $categories['parent_id']);
    }

        $this->Order(0, 0, $foo, '');
        $this->AddPage();
        $this->TitreChapitre("");
    if (SHOW_INTRODUCTION) {
        $this->Ln(18);
        $file= DIR_FS_CATALOG_LANGUAGES . tep_get_languages_directory($languages_code) . '/pdf_define_intro.php';

//            echo "<br>HHHH " . $file;
        if (file_exists($file)) {
            $file_array = @file($file);
            $file_contents = @implode('', $file_array);
            $this->MultiCell(0,6,strip_tags($file_contents),$this->ifw,1,'J');
        }

    }
    $this->SetFont('Arial','',DIRECTORIES_TREE_FONT_SIZE);
    if (SHOW_TREE) {
        $this->Ln(15);
        $this->MultiCell(0,6,$this->categories_string_spe,0,1,'L');
    }

 }

// Revision the function for MYSQL 5 
 function CategoriesListing($languages_id, $languages_code)
 {   
    $this->products_index_array=array();
    $this->products_index_list='';
    $this->index_lenght=0;

    //Recuperation de toutes les categories dans l'ordre
    $categoriesidarray=explode(" ",$this->categories_id);
    $categoriesnamearray=explode("\n",$this->categories_string);
    $categorieslevelsarray=explode(" ",$this->levels);
	  
    //Convertion pixels -> mm
    $imagewidth=SMALL_IMAGE_WIDTH*PDF_TO_MM_FACTOR;
    $imageheight=SMALL_IMAGE_HEIGHT*PDF_TO_MM_FACTOR;
	 
    for($i=0; $i<sizeof($categoriesidarray)-1; $i++) {
        $category_count_products = tep_products_in_category_count($categoriesidarray[$i],'false');
        if (!((!SHOW_EMPTY_CATEGORIES) and ($category_count_products < 1))) {
            $taille=0;
            $current_category_id=$categoriesidarray[$i];
            $current_category_name=$categoriesnamearray[$i];
            $current_category_level=$categorieslevelsarray[$i];
            $requete_prod="select p.products_id, pd.products_name, pd.products_description, p.products_image, p.products_model, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, p.products_date_added, m.manufacturers_name from ((" . TABLE_PRODUCTS . " p) left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd) left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . $languages_id . "' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id and p2c.categories_id='".$current_category_id."' order by pd.products_name, p.products_date_added DESC";
							  
            $SHOW_catalog_query = tep_db_query($requete_prod);
            while ($print_catalog = tep_db_fetch_array($SHOW_catalog_query)) {
                $print_catalog_array[$taille++] = array(
                                        'id' => $print_catalog['products_id'],
			            	'name' => $print_catalog['products_name'],		                          'description' => $print_catalog['products_description'],
			                'model' => $print_catalog['products_model'],
			            	'image' => $print_catalog['products_image'],
		            		'price' => $print_catalog['products_price'],					    'specials_price' => $print_catalog['specials_new_products_price'],
    		       			'tax_class_id' => $print_catalog['products_tax_class_id'],
	               			'date_added' => tep_date_long($print_catalog['products_date_added']),
    	               			'manufacturer' => $print_catalog['manufacturers_name']);
            }

            //Forschung der Name der Vaterkategorie
			// Revision $parent_category_name for PHP 5  
            $this->parent_category_name='';
            $this->ParentsName($current_category_level,$i,$categorieslevelsarray, $categoriesnamearray);
                            
            if (($current_category_level == 1) and (CATEGORIES_PAGE_SEPARATOR)) {
                $this->AddPage();
                $this->Ln(120);
                $this->SetFont('Arial','',12);
                $titles_color_table=explode(",",CENTER_TITLES_CELL_COLOR);
                $this->SetFillColor($titles_color_table[0], $titles_color_table[1], $titles_color_table[2]);
                $this->Cell(45,5,"",0,0);
                $this->MultiCell(100,10,$current_category_name,1,'C',1);
            }
    
            if ($taille > 0) { // nonempty category
            //    $this->AddPage();  // beginnt neue Kategorie mit neuer Seite
			// Revision $parent_category_name for PHP 5  
                if (strlen($this->parent_category_name) > 0 ) {
                    $this->TitreChapitre($this->parent_category_name. CATEGORIES_SEPARATOR .$current_category_name);
                } else {
                    $this->TitreChapitre($current_category_name);
                }
                $this->Ln(3); // NH
                $this->SetFont('Arial','',11);

                for($j=0; $j<$taille; $j++ ) {
                    // NH if not image definie, image by default 
                    if (strlen($print_catalog_array[$j]['image']) > 0) {
                        $imagepath=DIR_FS_CATALOG.DIR_WS_IMAGES.$print_catalog_array[$j]['image'];
                    } else {
                        $imagepath=DIR_FS_CATALOG.DIR_WS_IMAGES.'/'.DEFAULT_IMAGE;
                    }
                    $id=$print_catalog_array[$j]['id'];
                    $name=rtrim(strip_tags($print_catalog_array[$j]['name']));
                    $model=rtrim(strip_tags($print_catalog_array[$j]['model']));
                    $description=rtrim(strip_tags($print_catalog_array[$j]['description']));
                    $manufacturer=rtrim(strip_tags($print_catalog_array[$j]['manufacturer']));
                    $price=rtrim(strip_tags($print_catalog_array[$j]['price']));
                    $specials_price=rtrim(strip_tags($print_catalog_array[$j]['specials_price']));
                    $tax_class_id=rtrim(strip_tags($print_catalog_array[$j]['tax_class_id']));
                    $date_added=rtrim(strip_tags($print_catalog_array[$j]['date_added']));
			
                    $data_array=array($imagewidth,$imageheight,$name,$model,$date_added,$manufacturer,$description,$tax_class_id,$specials_price,$price,$id,$languages_code,$imagepath);
                    $this->Ln(PRODUCTS_SEPARATOR); // NH blank space before the products description cells 
                    $this->DrawCells($data_array);
                    if (SHOW_INDEX) {
                        switch (INDEX_EXTRA_FIELD) {
                            case 1 : $this->PrepareIndex($name,$manufacturer,$current_category_name);
                                    break;
                            case 2 : $this->PrepareIndex($name,$model,$current_category_name);
                                    break;
                            case 3 : $this->PrepareIndex($name,$date_added,$current_category_name);
                                    break;
                           default : $this->PrepareIndex($name,"",$current_category_name);
                        }
                    }
                }
            }
        }
    }   
 }

// Revision the function for MYSQL 5  
 function NewProducts($languages_id, $languages_code)
 {
    $products_new_query_raw = "select p.products_id, pd.products_name, pd.products_description, p.products_image, p.products_model, p.products_price, p.products_tax_class_id, IF(s.status, s.specials_new_products_price, NULL) as specials_new_products_price, p.products_date_added, m.manufacturers_name from ((" . TABLE_PRODUCTS . " p) left join " . TABLE_MANUFACTURERS . " m on p.manufacturers_id = m.manufacturers_id, " . TABLE_PRODUCTS_DESCRIPTION . " pd) left join " . TABLE_SPECIALS . " s on p.products_id = s.products_id, " . TABLE_CATEGORIES . " c, " . TABLE_PRODUCTS_TO_CATEGORIES . " p2c where products_status = '1' and p.products_id = pd.products_id and pd.language_id = '" . $languages_id . "' and p.products_id = p2c.products_id and p2c.categories_id = c.categories_id order by p.products_date_added DESC, pd.products_name";
	
    $products_new_query = tep_db_query($products_new_query_raw);
   
    while($products_new = tep_db_fetch_array($products_new_query)) {
        $products_new_array[] = array('id' => $products_new['products_id'],
                                  'name' => $products_new['products_name'],
                                  'image' => $products_new['products_image'],
		                 		  'description' => $products_new['products_description'],
		                		  'model' => $products_new['products_model'],
                                  'price' => $products_new['products_price'],
                                  'specials_price' => $products_new['specials_new_products_price'],
                                  'tax_class_id' => $products_new['products_tax_class_id'],
                                  'date_added' => tep_date_long($products_new['products_date_added']),
                                  'manufacturer' => $products_new['manufacturers_name']);
    }
  
    $this->AddPage();
    $this->Ln(120);
    $this->SetFont('Arial','',12);
    $new_color_table=explode(",",NEW_CELL_COLOR);
    $this->SetFillColor($new_color_table[0], $new_color_table[1], $new_color_table[2]);
    $this->Cell(45,5,"",0,0);
    $this->MultiCell(100,10,NEW_TITLE,1,'C',1);
    $this->Ln(100);
	
    //Convertion pixels -> mm
    $imagewidth=SMALL_IMAGE_WIDTH*PDF_TO_MM_FACTOR;
    $imageheight=SMALL_IMAGE_HEIGHT*PDF_TO_MM_FACTOR;
    
    for($nb=0; $nb<MAX_DISPLAY_PRODUCTS_NEW; $nb++) {
	$id=$products_new_array[$nb]['id'];
        $name=rtrim(strip_tags($products_new_array[$nb]['name']));
	$model=rtrim(strip_tags($products_new_array[$nb]['model']));
	$description=rtrim(strip_tags($products_new_array[$nb]['description']));
        $manufacturer=rtrim(strip_tags($products_new_array[$nb]['manufacturer']));
	$price=rtrim(strip_tags($products_new_array[$nb]['price']));
	$specials_price=rtrim(strip_tags($products_new_array[$nb]['specials_price']));
	$tax_class_id=rtrim(strip_tags($products_new_array[$nb]['tax_class_id']));
	$date_added=rtrim(strip_tags($products_new_array[$nb]['date_added']));
			
	$imagepath=DIR_FS_CATALOG.DIR_WS_IMAGES.$products_new_array[$nb]['image'];
	$data_array=array($imagewidth,$imageheight,$model,$name,$date_added,$manufacturer,$description,$tax_class_id,$specials_price,$price,$id,$languages_code,$imagepath);
	$this->DrawCells($data_array);
    }
 }

 function TitreChapitre($lib) {
    //Titel Kategorien
	//  $x=$this->GetX();
    // $y=$this->GetY();
	
	// Abstand zwischen Kategoriennamen und zu den davorstehendenden Produkten 
	    $this->Ln(10);

    $this->SetFont('Arial','',10);
    $titles_color_table=explode(",",HIGHT_TITLES_CELL_COLOR);
    $this->SetFillColor($titles_color_table[0], $titles_color_table[1], $titles_color_table[2]);
	$x=$this->SetX(15);
	//$Y=$this->SetY(150);
    $this->Cell(0,1,$lib,$this->ifw,1,'L',1);
    $this->Ln(1);
    //Schutz der Ordinate
    $this->y0=$this->GetY();
 }
 
}
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<script language="javascript" src="includes/general.js"></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top">
	  <table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="1" cellpadding="1" class="columnLeft">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
      </table>
		</td>
<!-- body_text //-->
    <td width="100%" valign="top">
     <table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td>
          <table border="0" width="100%" cellspacing="0" cellpadding="0">
            <tr>
              <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
              <td class="pageHeading" align="right"><?php echo tep_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
            </tr>
          </table>
	</td>
      </tr>
<?php
    switch ($_GET['action']) {
      case 'save':
        $languages = tep_get_languages();
        $languages_string = '';
      
//        $i=1;
        for ($i=0; $i<sizeof($languages); $i++)
        {
            $pdf=new PDF();
            $pdf->Open();
            $pdf->SetDisplayMode("real");
            $pdf->AliasNbPages();
            if(SHOW_NEW_PRODUCTS) 
			$pdf->NewProducts($languages[$i]['id'],$languages[$i]['code']);
            $pdf->CategoriesTree($languages[$i]['id'],$languages[$i]['code']);
            $pdf->CategoriesListing($languages[$i]['id'],$languages[$i]['code']);
            if (SHOW_INDEX) {
              //  $pdf->DrawIndex();
            }
            $pdf->Output(DIR_FS_CATALOG . DIR_WS_PDF_PRICELIST . PDF_FILENAME . "_" . $languages[$i]['id'].".pdf",false);
        }
?>
      <tr>
	<td>
	  <table>
    	    <tr>
		<td class="main"><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<? echo PDF_GENERATED . " <font color=red>".$i. "</font>";  ?></td>
	    </tr>
	  </table>
        </td>
      </tr>
<?php
        break;
      default:
        echo '<tr><td class="main"><br><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp' . PDF_PRE_GENERATED . '&nbsp;&nbsp;';
        echo tep_draw_form('language', FILENAME_PDF_PRICELIST, 'action=save');
        echo tep_image_submit('button_generate.gif', IMAGE_GENERATE) . '&nbsp;<a href="' . tep_href_link(FILENAME_PDF_PRICELIST, 'lngdir=' . $_GET['lngdir']) . '">';
        echo "</td></tr></form>";
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
