<?php
/*
  $Id: create_pdf,v 1.4 2005/04/07

ONTC Ecom - Developers Version
  http://www.ontc.eu

  Copyright (c) 2015/3 Francesco Rossi

  Released under the GNU General Public License
  
  Written by Neil Westlake (nwestlake@gmail.com) for www.Digiprintuk.com

  Version History:
  1.1
  Initial release
  1.2
  Corrected problem displaying PDF when from a HTTPS URL.
  1.3
  Modified item display to allow page continuation when more than 20 products are on one invoice.
  1.4
  Corrected problem with page continuation, now invoices will allow for an unlimited amount of products on one invoice
*/


 define('FPDF_FONTPATH','fpdf/font/');
 require('fpdf/fpdf.php');

 require('includes/application_top.php');
 require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_ORDERS_PACKINGSLIP);

 require(DIR_WS_CLASSES . 'currencies.php');
 $currencies = new currencies();

 include(DIR_WS_CLASSES . 'order.php');

 while (list($key, $oID) = each($_GET)) {
	if ($key != "oID")
		break;
		 $orders_query = tep_db_query("select orders_id from " . TABLE_ORDERS . " where orders_id = '" . $oID . "'");
		 $order = new order($oID);

class PDF extends FPDF
{
//Page header
 function RoundedRect($x, $y, $w, $h,$r, $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' or $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2f %.2f m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2f %.2f l', $xc*$k,($hp-$y)*$k ));

        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2f %.2f l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2f %.2f l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2f %.2f l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }
	
function Header()
{
	global $oID;
	$date = strftime('%A, %d %B %Y');
	 
    $bg_color=explode(",",PDF_PCKSLP_PAGE_FILL_COLOR );
    $this->SetFillColor($bg_color[0], $bg_color[1], $bg_color[2]);
    $this->Rect($this->lMargin,0,$this->w-$this->rMargin,$this->h,'F');	 
	//Logo
    //$this->Image('images/Francesco Rossi.jpeg',5,10,50);
    if ( PDF_PCKSLP_SHOW_LOGO == 'true' ) {
	    $this->Image( PDF_PCKSLP_STORE_LOGO ,5,10,50);
    }

    // Invoice Number and date
	//$this->SetFont('Arial','B',14);
	//$this->SetTextColor(158,11,14);
	$this->SetFont( PDF_PCKSLP_HEADER_TEXT_FONT,
                    PDF_PCKSLP_HEADER_TEXT_EFFECT,
                    PDF_PCKSLP_HEADER_TEXT_HEIGHT);  
                    
    $text_color=explode(",",PDF_PCKSLP_HEADER_TEXT_COLOR );
    $this->SetTextColor($text_color[0], $text_color[1], $text_color[2]);  
        
    $text_color=explode(",",PDF_PCKSLP_HEADER_FILL_COLOR );
    $this->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
    $text_color=explode(",",PDF_PCKSLP_HEADER_LINE_COLOR );
    $this->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );       

	$this->SetY(37); 
   	$this->MultiCell(100,6, PRINT_DELIVERY_INVOICE . $oID . "\n" . $date ,0,'L', '1' );
   
	// Company Address
	$this->SetX(0);
	$this->SetY(10);

    $this->SetFont( PDF_PCKSLP_HEADER_TEXT_FONT,
                    PDF_PCKSLP_HEADER_TEXT_EFFECT,
                    PDF_PCKSLP_HEADER_TEXT_HEIGHT);  
                    
    $text_color=explode(",",PDF_PCKSLP_HEADER_TEXT_COLOR );
    $this->SetTextColor($text_color[0], $text_color[1], $text_color[2]);  
        
    $text_color=explode(",",PDF_PCKSLP_HEADER_FILL_COLOR );
    $this->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
    $text_color=explode(",",PDF_PCKSLP_HEADER_LINE_COLOR );
    $this->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );       

    $this->Ln(0);
    $this->Cell(149);
    
    if ( PDF_PCKSLP_SHOW_ADRESSSHOP == 'true' ) {    
	   $this->MultiCell(50, 3.5, STORE_NAME_ADDRESS,0,'L', '1' ); 
    } 
		
	//email
	$this->SetX(0);
	$this->SetY(37);
	$this->SetFont( PDF_PCKSLP_HEADER_TEXT_FONT,
                    PDF_PCKSLP_HEADER_TEXT_EFFECT,
                    PDF_PCKSLP_HEADER_TEXT_HEIGHT);  
                    
    $text_color=explode(",",PDF_PCKSLP_HEADER_TEXT_COLOR );
    $this->SetTextColor($text_color[0], $text_color[1], $text_color[2]);  
        
    $text_color=explode(",",PDF_PCKSLP_HEADER_FILL_COLOR );
    $this->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
    $text_color=explode(",",PDF_PCKSLP_HEADER_LINE_COLOR );
    $this->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );     
    
	$this->Ln(0);
    $this->Cell(81);
    if ( PDF_PCKSLP_SHOW_MAILWEB == 'true' ) {    
	   $this->MultiCell(100, 6, "E-mail: " . STORE_OWNER_EMAIL_ADDRESS,0,'R', '1' );
    }   

	//website
	$this->SetX(0);
	$this->SetY(42);

	$this->SetFont( PDF_PCKSLP_HEADER_TEXT_FONT,
                    PDF_PCKSLP_HEADER_TEXT_EFFECT,
                    PDF_PCKSLP_HEADER_TEXT_HEIGHT);  
                    
    $text_color=explode(",",PDF_PCKSLP_HEADER_TEXT_COLOR );
    $this->SetTextColor($text_color[0], $text_color[1], $text_color[2]);  
        
    $text_color=explode(",",PDF_PCKSLP_HEADER_FILL_COLOR );
    $this->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
    $text_color=explode(",",PDF_PCKSLP_HEADER_LINE_COLOR );
    $this->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );     
    
	$this->Ln(0);
    $this->Cell(88);
    if ( PDF_PCKSLP_SHOW_MAILWEB == 'true' ) {    
	   $this->MultiCell(100, 6, "Web  : " . HTTP_SERVER,0,'R', '1' );
    }
    
    $this->Cell(50);
    $this->SetY(60);
    
    $text_color=explode(",",PDF_PCKSLP_HEADINVOICE_TEXT_COLOR );
    $this->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );

    $this->Cell(15,.1,'',1,1,'L',1);

    $this->SetFont( PDF_PCKSLP_HEADINVOICE_TEXT_FONT,
                    PDF_PCKSLP_HEADINVOICE_TEXT_EFFECT,
                    PDF_PCKSLP_HEADINVOICE_TEXT_HEIGHT); 
               
    $text_color=explode(",",PDF_PCKSLP_HEADINVOICE_TEXT_COLOR );
    $this->SetTextColor( $text_color[0], $text_color[1], $text_color[2] );

    $this->Text(22,61.5, DELIVERY_NOTE );
    $this->SetY(60);

    $text_color=explode(",",PDF_PCKSLP_HEADINVOICE_TEXT_COLOR );
    $this->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );

    $this->Cell(55);
    $this->Cell(140,.1,'',1,1,'L',1);    

}

function Footer()
{
    //Position at 1.5 cm from bottom
    $this->SetY(-17);
 
    $this->SetFont( PDF_PCKSLP_FOOTER_TEXT_FONT,
                    PDF_PCKSLP_FOOTER_TEXT_EFFECT,
                    PDF_PCKSLP_FOOTER_TEXT_HEIGHT);  
                    
    $text_color=explode(",",PDF_PCKSLP_FOOTER_TEXT_COLOR );
    $this->SetTextColor($text_color[0], $text_color[1], $text_color[2]);  
	$this->Cell(0,10, PDF_PCKSLP_FOOTER_TEXT, 0,0,'C');
	
	$this->SetY(-14);

	//Page number
    $this->Cell(0,10,PRINT_DELIVERY_PAGE_NUMBER.$this->PageNo(),0,0,'C');
	}
}
//Instanciation of inherited class
$pdf=new PDF();

// Set the Page Margins
$pdf->SetMargins(6,2,6);

// Add the first page
$pdf->AddPage();

//Draw Box for Invoice Address
if ( PDF_PCKSLP_SHOW_SENDTO == 'true' ) { 	

    $text_color=explode(",",PDF_PCKSLP_SENDTO_LINE_COLOR );
    $pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );
	$pdf->SetLineWidth(0.2);

    $text_color=explode(",",PDF_PCKSLP_SENDTO_FILL_COLOR );
    $pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );
    $pdf->RoundedRect(6, 67, 90, 35, 2, 'DF');

//Draw the invoice address text

	$pdf->SetFont( PDF_PCKSLP_SENDTO_TEXT_FONT,
                   PDF_PCKSLP_SENDTO_TEXT_EFFECT,
                   PDF_PCKSLP_SENDTO_TEXT_HEIGHT);  
                    
    $text_color=explode(",",PDF_PCKSLP_SENDTO_TEXT_COLOR );
    $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]); 
	$pdf->Text(11,77, ENTRY_SHIP_TO);
	$pdf->SetX(0);
	$pdf->SetY(80);

    $pdf->Cell(9);
	$pdf->MultiCell(70, 3.3, tep_address_format(1, $order->delivery, '', '', "\n"),0,'L', '1' );
	
	//Draw Box for Order Number, Date & Payment method
    $text_color=explode(",",PDF_PCKSLP_ORDERDETAILS_LINE_COLOR );
    $pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );
	$pdf->SetLineWidth(0.2);

    $text_color=explode(",",PDF_PCKSLP_ORDERDETAILS_FILL_COLOR );
    $pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );
	$pdf->RoundedRect(6, 107, 192, 11, 2, 'DF');

	//Draw Order Number Text
	$pdf->SetFont( PDF_PCKSLP_ORDERDETAILS_TEXT_FONT,
                   PDF_PCKSLP_ORDERDETAILS_TEXT_EFFECT,
                   PDF_PCKSLP_ORDERDETAILS_TEXT_HEIGHT);  
                    
    $text_color=explode(",",PDF_PCKSLP_ORDERDETAILS_TEXT_COLOR );
    $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]);  
    
	$temp = str_replace('&nbsp;', ' ', PRINT_DELIVERY_ORDERNR);
	$pdf->Text(10,113, $temp . tep_db_input($oID));	
	//Draw Date of Order Text
	$temp = str_replace('&nbsp;', ' ', PRINT_DELIVERY_DATE);
	$pdf->Text(75,113,$temp . tep_date_short($order->info['date_purchased']));	
}		
//Fields Name position
$Y_Fields_Name_position = 125;
//Table position, under Fields Name
$Y_Table_Position = 131;


function output_table_heading($Y_Fields_Name_position){
    global $pdf;
//First create each Field Name

//Bold Font for Field Name
$pdf->SetFont( PDF_PCKSLP_TABLEHEADING_TEXT_FONT,
               PDF_PCKSLP_TABLEHEADING_TEXT_EFFECT,
               PDF_PCKSLP_TABLEHEADING_TEXT_HEIGHT);  

$text_color=explode(",",PDF_PCKSLP_TABLEHEADING_TEXT_COLOR );
$pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]); 

$text_color=explode(",",PDF_PCKSLP_TABLEHEADING_LINE_COLOR );
$pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );
//$pdf->SetFillColor(245);
$text_color=explode(",",PDF_PCKSLP_TABLEHEADING_FILL_COLOR );
$pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );     

$pdf->SetY($Y_Fields_Name_position);
$pdf->SetX(6);
$pdf->Cell(14,6,TABLE_HEADING_QUANTITY,1,0,'C',1);
$pdf->SetX(20);
$pdf->Cell(40,6,TABLE_HEADING_PRODUCTS_MODEL,1,0,'C',1);
$pdf->SetX(60);
$pdf->Cell(138,6,TABLE_HEADING_PRODUCTS,1,0,'C',1);
$pdf->Ln();
}
output_table_heading($Y_Fields_Name_position);
//Show the products information line by line
for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {

    $pdf->SetFont( PDF_PCKSLP_PRODUCTS_TEXT_FONT,
                   PDF_PCKSLP_PRODUCTS_TEXT_EFFECT,
                   PDF_PCKSLP_PRODUCTS_TEXT_HEIGHT);  

    $text_color=explode(",",PDF_PCKSLP_PRODUCTS_TEXT_COLOR );
    $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]); 
    
     
    $text_color=explode(",",PDF_PCKSLP_PRODUCTS_FILL_COLOR );
    $pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
    $text_color=explode(",",PDF_PCKSLP_PRODUCTS_LINE_COLOR );
    $pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );     
    
	$pdf->SetY($Y_Table_Position);
	$pdf->SetX(6);
	$pdf->MultiCell(14,6,$order->products[$i]['qty'],1,'C', '1');
	$pdf->SetY($Y_Table_Position);
	$pdf->SetX(20);
    //$pdf->SetFont('Arial','',8);
    $pdf->SetFont( PDF_PCKSLP_PRODUCTS_TEXT_FONT,
                   PDF_PCKSLP_PRODUCTS_TEXT_EFFECT,
                   PDF_PCKSLP_PRODUCTS_TEXT_HEIGHT);  
	$pdf->MultiCell(40,6,$order->products[$i]['model'],1,'L', '1' );
	$pdf->SetY($Y_Table_Position);
	$pdf->SetX(60);
	if (strlen($order->products[$i]['name']) > 40 && strlen($order->products[$i]['name']) < 50){
		//$pdf->SetFont('Arial','',8);
		$pdf->SetFont( PDF_PCKSLP_PRODUCTS_TEXT_FONT,
                       PDF_PCKSLP_PRODUCTS_TEXT_EFFECT,
                       PDF_PCKSLP_PRODUCTS_TEXT_HEIGHT);  
		$pdf->MultiCell(138,6,$order->products[$i]['name'],1,'L', '1' );
		}
	else if (strlen($order->products[$i]['name']) > 50){
		//$pdf->SetFont('Arial','',8);
		$pdf->SetFont( PDF_PCKSLP_PRODUCTS_TEXT_FONT,
                       PDF_PCKSLP_PRODUCTS_TEXT_EFFECT,
                       PDF_PCKSLP_PRODUCTS_TEXT_HEIGHT);  
		$pdf->MultiCell(138,6,substr($order->products[$i]['name'],0,50),1,'L', '1' );
		}
	else{
		$pdf->SetFont( PDF_PCKSLP_PRODUCTS_TEXT_FONT,
                       PDF_PCKSLP_PRODUCTS_TEXT_EFFECT,
                       PDF_PCKSLP_PRODUCTS_TEXT_HEIGHT);  
		$pdf->MultiCell(138,6,$order->products[$i]['name'],1,'L', '1' );
		}
	$Y_Table_Position += 6;

	//get attribs 
    if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
        for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
	      $pdf->SetY($Y_Table_Position);
	      $pdf->SetX(60);
	      
    	  if (strlen(" - " .$order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] )> 40 && strlen(" - " .$order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value']) < 50){
		
		    $pdf->SetFont( PDF_PCKSLP_PRODUCTS_TEXT_FONT,
                           PDF_PCKSLP_PRODUCTS_TEXT_EFFECT,
                           PDF_PCKSLP_PRODUCTS_TEXT_HEIGHT);  
		    $pdf->MultiCell(78,6," - " .$order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'],1,'L', '1');
		  }
	      else if (strlen(" - " .$order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value']) > 50){
            $pdf->SetFont( PDF_PCKSLP_PRODUCTS_TEXT_FONT,
                           PDF_PCKSLP_PRODUCTS_TEXT_EFFECT,
                           PDF_PCKSLP_PRODUCTS_TEXT_HEIGHT);  

		    $pdf->MultiCell(78,6,substr(" - " .$order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'],0,50),1,'L', '1');
	      } else {
            $pdf->SetFont( PDF_PCKSLP_PRODUCTS_TEXT_FONT,
                           PDF_PCKSLP_PRODUCTS_TEXT_EFFECT,
                           PDF_PCKSLP_PRODUCTS_TEXT_HEIGHT);  
		      
		    $pdf->MultiCell(78,6," - " .$order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'],1,'L', '1');
          }	      

          //$pdf->MultiCell(78,6," - " .$order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'],1,'L', '1');
          $Y_Table_Position += 6;

          //Check for product line overflow
          $item_count++;
	      if ( $item_count > 20 ) {
             $pdf->AddPage();
             //Fields Name position
             $Y_Fields_Name_position = 125;
             //Table position, under Fields Name
             $Y_Table_Position = 70;
             output_table_heading($Y_Table_Position-6);
             $item_count = 1;
             $text_color=explode(",",PDF_PCKSLP_PRODUCTS_TEXT_COLOR );
             $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]); 
    
     
             $text_color=explode(",",PDF_PCKSLP_PRODUCTS_FILL_COLOR );
             $pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
             $text_color=explode(",",PDF_PCKSLP_PRODUCTS_LINE_COLOR );
             $pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );    
          }         
        }
    } 
    //Check for product line overflow
    $item_count++;
	if ( $item_count > 20 ) {	
        $pdf->AddPage();
        //Fields Name position
        $Y_Fields_Name_position = 125;
        //Table position, under Fields Name
        $Y_Table_Position = 70;
        output_table_heading($Y_Table_Position-6);
        $item_count = 1;
        $text_color=explode(",",PDF_INV_PRODUCTS_TEXT_COLOR );
        $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]); 
    
     
        $text_color=explode(",",PDF_INV_PRODUCTS_FILL_COLOR );
        $pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
        $text_color=explode(",",PDF_INV_PRODUCTS_LINE_COLOR );
        $pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );    
    }    
}

	// Draw the shipping address for label
	//Draw the invoice delivery address text
	/*
    $pdf->SetFont('Arial','B',11);
	$pdf->SetTextColor(0);
	//$pdf->Text(117,61,ENTRY_SHIP_TO);
	//$pdf->SetX(0);
	$pdf->SetY(240);
    $pdf->Cell(20);
	$pdf->MultiCell(50, 4, strtoupper(tep_address_format(1, $order->delivery, '', '', "\n")),0,'L');
		*/
}
// PDF's created now output the file
$pdf->Output();
?>