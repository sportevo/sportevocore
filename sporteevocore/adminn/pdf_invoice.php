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
  1.6
  full control over the color, fonts,size etc.
*/

 define('FPDF_FONTPATH','fpdf/font/');
 require('fpdf/fpdf.php');

 require('includes/application_top.php');
 require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_ORDERS_INVOICE);

 require(DIR_WS_CLASSES . 'currencies.php');
 $currencies = new currencies();

 include(DIR_WS_CLASSES . 'order.php');

 while (list($key, $oID) = each($_GET)) {
	if ($key != "oID")
		break;
		
		//Roby Professional Invoice and Packing Slip START

	  // invoice exist for this order
	  $verify_query = tep_db_query("select orders_id from " . TABLE_INVOICE . " where orders_id = '" . (int)$oID . "'");
      $verify_id = tep_db_fetch_array($verify_query);
	  $sql_data_array = array('orders_id' => $oID);
     if ($verify_id['orders_id'] == $oID)
	 {
	 }
	 // else insert date and invoice number for this order
     else
     {
      echo "CREARE FATTURA PRIMA";
      exit;
      }
	 // -END-  invoice counting

	$invoice_id_query = tep_db_query("select invoice_id, num_invoice, date_invoice from " . TABLE_INVOICE . " where orders_id = '" . (int)$oID . "'");
    $invoice_id = tep_db_fetch_array($invoice_id_query);

//Roby Professional Invoice and Packing Slip END
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
	
    $bg_color=explode(",",PDF_INV_PAGE_FILL_COLOR );
    $this->SetFillColor($bg_color[0], $bg_color[1], $bg_color[2]);
    $this->Rect($this->lMargin,0,$this->w-$this->rMargin,$this->h,'F');
	 
	//Logo
    if ( PDF_INV_SHOW_LOGO == 'true' ) {
	    $this->Image( PDF_INV_STORE_LOGO ,5,10,50);
    }

    // Invoice Number and date
	//$this->SetFont('Arial','B', 14);
	//$this->SetTextColor(158,11,14);
	//$this->SetY(37);
	//
//	$this->MultiCell(100,6,"Invoice: #" . $oID . "\n" . $date ,0,'L');	

	
	// Company Address
	$this->SetX(0);
	$this->SetY(10);
    $this->SetFont( PDF_INV_HEADER_TEXT_FONT,
                    PDF_INV_HEADER_TEXT_EFFECT,
                    PDF_INV_HEADER_TEXT_HEIGHT);  
                    
    $text_color=explode(",",PDF_INV_HEADER_TEXT_COLOR );
    $this->SetTextColor($text_color[0], $text_color[1], $text_color[2]);  
        
    $text_color=explode(",",PDF_INV_HEADER_FILL_COLOR );
    $this->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
    $text_color=explode(",",PDF_INV_HEADER_LINE_COLOR );
    $this->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );       

    $this->Ln(0);
    $this->Cell(149);
    
    if ( PDF_INV_SHOW_ADRESSSHOP == 'true' ) {    
	   $this->MultiCell(50, 3.5, STORE_NAME_ADDRESS,0,'L', '1' ); 
    }   
	
	//Roby
$this->SetFont('Arial','B',8);
	$this->SetTextColor(0);
	$this->SetY(16);
    $this->Cell(140);
	
//Roby
$this->Text(105,6,$invoice_id['num_invoice']);


	
	
	//email
	$this->SetX(0);
	$this->SetY(37);
//	$this->SetFont('Arial','B',10);
    $this->SetFont( PDF_INV_HEADER_TEXT_FONT,
                    PDF_INV_HEADER_TEXT_EFFECT,
                    PDF_INV_HEADER_TEXT_HEIGHT);  
                    
    $text_color=explode(",",PDF_INV_HEADER_TEXT_COLOR );
    $this->SetTextColor($text_color[0], $text_color[1], $text_color[2]);  
        
    $text_color=explode(",",PDF_INV_HEADER_FILL_COLOR );
    $this->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
    $text_color=explode(",",PDF_INV_HEADER_LINE_COLOR );
    $this->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );     
    
	$this->Ln(0);
    $this->Cell(81);
    if ( PDF_INV_SHOW_MAILWEB == 'true' ) {    
	   $this->MultiCell(100, 6, "E-mail: " . STORE_OWNER_EMAIL_ADDRESS,0,'R', '1' );
    }   
	
	//website
	$this->SetX(0);
	$this->SetY(42);
    $this->SetFont( PDF_INV_HEADER_TEXT_FONT,
                    PDF_INV_HEADER_TEXT_EFFECT,
                    PDF_INV_HEADER_TEXT_HEIGHT);  
                    
    $text_color=explode(",",PDF_INV_HEADER_TEXT_COLOR );
    $this->SetTextColor($text_color[0], $text_color[1], $text_color[2]);  
        
    $text_color=explode(",",PDF_INV_HEADER_FILL_COLOR );
    $this->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
    $text_color=explode(",",PDF_INV_HEADER_LINE_COLOR );
    $this->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );     
    
	$this->Ln(0);
    $this->Cell(88);
    if ( PDF_INV_SHOW_MAILWEB == 'true' ) {    
	   $this->MultiCell(100, 6, "Web  : " . HTTP_SERVER,0,'R', '1' );
    }  
    
    //Draw the top line with invoice text
    $this->Cell(50);
    $this->SetY(60);
    
    $text_color=explode(",",PDF_INV_HEADINVOICE_TEXT_COLOR );
    $this->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );

    $this->Cell(15,.1,'',1,1,'L',1);

    $this->SetFont( PDF_INV_HEADINVOICE_TEXT_FONT,
                    PDF_INV_HEADINVOICE_TEXT_EFFECT,
                    PDF_INV_HEADINVOICE_TEXT_HEIGHT); 
               
    $text_color=explode(",",PDF_INV_HEADINVOICE_TEXT_COLOR );
    $this->SetTextColor( $text_color[0], $text_color[1], $text_color[2] );

    $this->Text(22,61.5,PRINT_INVOICE);
    $this->SetY(60);

    $text_color=explode(",",PDF_INV_HEADINVOICE_TEXT_COLOR );
    $this->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );

    $this->Cell(38);
    $this->Cell(160,.1,'',1,1,'L',1);    

}

function Footer()
{
    //Position at 1.5 cm from bottom
    $this->SetY(-17);
    //Arial italic 8

    $this->SetFont( PDF_INV_FOOTER_TEXT_FONT,
                    PDF_INV_FOOTER_TEXT_EFFECT,
                    PDF_INV_FOOTER_TEXT_HEIGHT);  
                    
    $text_color=explode(",",PDF_INV_FOOTER_TEXT_COLOR );
    $this->SetTextColor($text_color[0], $text_color[1], $text_color[2]);
    
    //$text_color=explode(",",PDF_INV_FOOTER_FILL_COLOR );
    //$this->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
      
	$this->Cell(0,10, PDF_INV_FOOTER_TEXT, 0, 0,'C');
	
	$this->SetY(-14);
	//Page number
    $this->Cell(0,10,PRINT_INVOICE_PAGE_NUMBER.$this->PageNo(),0,0,'C' );
	}
}
//Instanciation of inherited class
$pdf=new PDF( PDF_INV_PORTRAIT_LANDSCAPE,'mm',array(PDF_INV_PAPER_WIDTH, PDF_INV_PAPER_HEIGHT ) ) ;

// Set the Page Margins
$pdf->SetMargins(6,2,6);	

// Add the first page
$pdf->AddPage();

//Draw Box for Invoice Address
if ( PDF_INV_SHOW_SOLDTO == 'true' ) {  
  $text_color=explode(",",PDF_INV_SOLDTO_LINE_COLOR );
  $pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );
  $pdf->SetLineWidth(0.2);
  $text_color=explode(",",PDF_INV_SOLDTO_FILL_COLOR );
  $pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );
  $pdf->RoundedRect(6, 67, 90, 35, 2, 'DF');

  //Draw the invoice address text
  $pdf->SetFont( PDF_INV_SOLDTO_TEXT_FONT,
                 PDF_INV_SOLDTO_TEXT_EFFECT,
                 PDF_INV_SOLDTO_TEXT_HEIGHT );  
                    
  $text_color=explode(",",PDF_INV_SOLDTO_TEXT_COLOR );
  $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]);  
    
  $pdf->Text(11,77, ENTRY_SOLD_TO);
  $pdf->SetX(0);
  $pdf->SetY(80);
  $pdf->Cell(9);
  $pdf->MultiCell(70, 3.3, tep_address_format(1, $order->customer, '', '', "\n"),0,'L', '1');
}  
	//Roby CF e PIVA




	$pdf->Text(16,99, "CF:");
    $pdf->Text(22,99, $order->billing['cf']);
	$pdf->Text(55,99, "PIVA:");
    $pdf->Text(65,99, $order->billing['piva']);
	

	//Draw Box for Delivery Address
if ( PDF_INV_SHOW_SENDTO == 'true' ) { 	
    $text_color=explode(",",PDF_INV_SENDTO_LINE_COLOR );
    $pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );
	$pdf->SetLineWidth(0.2);
    $text_color=explode(",",PDF_INV_SENDTO_FILL_COLOR );
    $pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );
	$pdf->RoundedRect(108, 67, 90, 35, 2, 'DF');
	
	//Draw the invoice delivery address text
    $pdf->SetFont( PDF_INV_SENDTO_TEXT_FONT,
                   PDF_INV_SENDTO_TEXT_EFFECT,
                   PDF_INV_SENDTO_TEXT_HEIGHT );
                    
    $text_color=explode(",",PDF_INV_SENDTO_TEXT_COLOR );
    $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]);  
	$pdf->Text(113,77,ENTRY_SHIP_TO);
	$pdf->SetX(0);
	$pdf->SetY(80);
    $pdf->Cell(111);
	$pdf->MultiCell(70, 3.3, tep_address_format(1, $order->delivery, '', '', "\n"),0,'L', '1');
}	

	//Draw Box for Order Number, Date & Payment method
    $text_color=explode(",",PDF_INV_ORDERDETAILS_LINE_COLOR );
    $pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );
	$pdf->SetLineWidth(0.2);
    $text_color=explode(",",PDF_INV_ORDERDETAILS_FILL_COLOR );
    $pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );
	$pdf->RoundedRect(6, 107, 192, 11, 2, 'DF');
	
	// print the order details
	$pdf->SetFont( PDF_INV_ORDERDETAILS_TEXT_FONT,
                   PDF_INV_ORDERDETAILS_TEXT_EFFECT,
                   PDF_INV_ORDERDETAILS_TEXT_HEIGHT);  
                    
    $text_color=explode(",",PDF_INV_ORDERDETAILS_TEXT_COLOR );
    $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]);  

	//Draw Order Number Text
	//$temp = str_replace('&nbsp;', ' ', PRINT_INVOICE_ORDER);
//	$pdf->Text(10,113, $temp . tep_db_input($oID));	
	//Draw Date of Order Text
	$temp = str_replace('&nbsp;', ' ', PRINT_INVOICE_DATE);
	$pdf->Text(35,113,$temp . tep_date_short($order->info['date_purchased']));	
	//Draw Payment Method Text
	$temp = substr ($order->info['payment_method'] , 0, 25);
	$pdf->Text(104,113,ENTRY_PAYMENT_METHOD . ' ' . $temp);	
	// Draw Invoice N�	yak
	$pdf->Text(80,113,"N� Fattura:");
	$pdf->Text(95,113,$invoice_id['num_invoice']);
//Fields Name position
$Y_Fields_Name_position = 125;
//Table position, under Fields Name
$Y_Table_Position = 131;


function output_table_heading($Y_Fields_Name_position){
    global $pdf;
//First create each Field Name
// color filling each Field Name box

//Bold Font for Field Name
$pdf->SetFont( PDF_INV_TABLEHEADING_TEXT_FONT,
               PDF_INV_TABLEHEADING_TEXT_EFFECT,
               PDF_INV_TABLEHEADING_TEXT_HEIGHT);  

$text_color=explode(",",PDF_INV_TABLEHEADING_TEXT_COLOR );
$pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]); 

$text_color=explode(",",PDF_INV_TABLEHEADING_LINE_COLOR );
$pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );
$text_color=explode(",",PDF_INV_TABLEHEADING_FILL_COLOR );
$pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );                

$pdf->SetY($Y_Fields_Name_position);
$pdf->SetX(6);
$pdf->Cell(9,6,TABLE_HEADING_QUANTITY,1,0,'C',1);
$pdf->SetX(15);
$pdf->Cell(27,6,TABLE_HEADING_PRODUCTS_MODEL,1,0,'C',1);
$pdf->SetX(40);
$pdf->Cell(78,6,TABLE_HEADING_PRODUCTS,1,0,'C',1);
//$pdf->SetX(105);
//$pdf->Cell(15,6,TABLE_HEADING_TAX,1,0,'C',1);
$pdf->SetX(118);
$pdf->Cell(20,6,TABLE_HEADING_PRICE_EXCLUDING_TAX,1,0,'C',1);
$pdf->SetX(138);
$pdf->Cell(20,6,TABLE_HEADING_PRICE_INCLUDING_TAX,1,0,'C',1);
$pdf->SetX(158);
$pdf->Cell(20,6,TABLE_HEADING_TOTAL_EXCLUDING_TAX,1,0,'C',1);
$pdf->SetX(178);
$pdf->Cell(20,6,TABLE_HEADING_TOTAL_INCLUDING_TAX,1,0,'C',1);
$pdf->Ln();
}
output_table_heading($Y_Fields_Name_position);
//Show the products information line by line
$item_count = 0 ;
for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
	
    $pdf->SetFont( PDF_INV_PRODUCTS_TEXT_FONT,
                   PDF_INV_PRODUCTS_TEXT_EFFECT,
                   PDF_INV_PRODUCTS_TEXT_HEIGHT);  

    $text_color=explode(",",PDF_INV_PRODUCTS_TEXT_COLOR );
    $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]); 
    
     
    $text_color=explode(",",PDF_INV_PRODUCTS_FILL_COLOR );
    $pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
    $text_color=explode(",",PDF_INV_PRODUCTS_LINE_COLOR );
    $pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );                 

	$pdf->SetY($Y_Table_Position);
	$pdf->SetX(6);
	$pdf->MultiCell(9,6,$order->products[$i]['qty'],1,'C', '1');
	
	$pdf->SetY($Y_Table_Position);
	$pdf->SetX(15);
    $pdf->SetFont( PDF_INV_PRODUCTS_TEXT_FONT,
                   PDF_INV_PRODUCTS_TEXT_EFFECT,
                   PDF_INV_PRODUCTS_TEXT_HEIGHT);  
    
	$pdf->MultiCell(25,6,$order->products[$i]['model'],1,'L', '1');
		
	$pdf->SetY($Y_Table_Position);
	$pdf->SetX(40);
	if (strlen($order->products[$i]['name']) > 40 && strlen($order->products[$i]['name']) < 50){
		$pdf->SetFont( PDF_INV_PRODUCTS_TEXT_FONT,
                       PDF_INV_PRODUCTS_TEXT_EFFECT,
                       PDF_INV_PRODUCTS_TEXT_HEIGHT);  
		$pdf->MultiCell(78,6,$order->products[$i]['name'],1,'L', '1');
		}
	else if (strlen($order->products[$i]['name']) > 50){
		$pdf->SetFont( PDF_INV_PRODUCTS_TEXT_FONT,
                       PDF_INV_PRODUCTS_TEXT_EFFECT,
                       PDF_INV_PRODUCTS_TEXT_HEIGHT);  

		$pdf->MultiCell(78,6,substr($order->products[$i]['name'],0,50),1,'L', '1');
	} else{
        $pdf->SetFont( PDF_INV_PRODUCTS_TEXT_FONT,
                       PDF_INV_PRODUCTS_TEXT_EFFECT,
                       PDF_INV_PRODUCTS_TEXT_HEIGHT);  
		
		$pdf->MultiCell(78,6,$order->products[$i]['name'],1,'L', '1');
    }
    
       
	//$pdf->SetFont('Arial','',10);
	//$pdf->SetY($Y_Table_Position);
	//$pdf->SetX(95);
	//$pdf->MultiCell(15,6,tep_display_tax_value($order->products[$i]['tax']) . '%',1,'C');

	$pdf->SetY($Y_Table_Position);
	$pdf->SetX(118);
    //$pdf->SetFont('Arial','',10);
	$pdf->SetFont( PDF_INV_PRODUCTS_TEXT_FONT,
                   PDF_INV_PRODUCTS_TEXT_EFFECT,
                   PDF_INV_PRODUCTS_TEXT_HEIGHT);  

	$pdf->MultiCell(20,6,$currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value']),1,'R', '1');
	$pdf->SetY($Y_Table_Position);
	$pdf->SetX(138);
	$pdf->MultiCell(20,6,$currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value']),1,'R', '1');
	$pdf->SetY($Y_Table_Position);
	$pdf->SetX(158);
	$pdf->MultiCell(20,6,$currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']),1,'R', '1');
	$pdf->SetY($Y_Table_Position);
	$pdf->SetX(178);
	$pdf->MultiCell(20,6,$currencies->format(tep_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value']),1,'R', '1');
	$Y_Table_Position += 6;	
	
	//get attribs 
    if ( (isset($order->products[$i]['attributes'])) && (sizeof($order->products[$i]['attributes']) > 0) ) {
        for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
	      $pdf->SetY($Y_Table_Position);
	      $pdf->SetX(40);
	      
    	  if (strlen(" - " .$order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] )> 40 && strlen(" - " .$order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value']) < 50){
		
		    $pdf->SetFont( PDF_INV_PRODUCTS_TEXT_FONT,
                           PDF_INV_PRODUCTS_TEXT_EFFECT,
                           PDF_INV_PRODUCTS_TEXT_HEIGHT);  
		    $pdf->MultiCell(78,6," - " .$order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'],1,'L', '1');
		  }
	      else if (strlen(" - " .$order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value']) > 50){
            $pdf->SetFont( PDF_INV_PRODUCTS_TEXT_FONT,
                           PDF_INV_PRODUCTS_TEXT_EFFECT,
                           PDF_INV_PRODUCTS_TEXT_HEIGHT);  

		    $pdf->MultiCell(78,6,substr(" - " .$order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'],0,50),1,'L', '1');
	      } else {
            $pdf->SetFont( PDF_INV_PRODUCTS_TEXT_FONT,
                           PDF_INV_PRODUCTS_TEXT_EFFECT,
                           PDF_INV_PRODUCTS_TEXT_HEIGHT);  
		      
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
             $text_color=explode(",",PDF_INV_PRODUCTS_TEXT_COLOR );
             $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]); 
    
     
             $text_color=explode(",",PDF_INV_PRODUCTS_FILL_COLOR );
             $pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
             $text_color=explode(",",PDF_INV_PRODUCTS_LINE_COLOR );
             $pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );    
          }         
        }
    }   

    //Check for product line overflow
    $item_count++;
//    if ((is_long($item_count / 32) && $i >= 20) || ($i == 20)){
	if ( $item_count > 20 ) {	
        $pdf->AddPage();
        //Fields Name position
        $Y_Fields_Name_position = 125;
        //Table position, under Fields Name
        $Y_Table_Position = 70;
        output_table_heading($Y_Table_Position-6);
        //if ($i == 20) 
        $item_count = 1;
        $text_color=explode(",",PDF_INV_PRODUCTS_TEXT_COLOR );
        $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]); 
    
     
        $text_color=explode(",",PDF_INV_PRODUCTS_FILL_COLOR );
        $pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
        $text_color=explode(",",PDF_INV_PRODUCTS_LINE_COLOR );
        $pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );    
    }
}

$pdf->SetFont( PDF_INV_ORDERTOTAL1_TEXT_FONT,
               PDF_INV_ORDERTOTAL1_TEXT_EFFECT,
               PDF_INV_ORDERTOTAL1_TEXT_HEIGHT);  

$text_color=explode(",",PDF_INV_ORDERTOTAL1_TEXT_COLOR );
$pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]);

$text_color=explode(",",PDF_INV_ORDERTOTAL1_FILL_COLOR );
$pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
$text_color=explode(",",PDF_INV_ORDERTOTAL1_LINE_COLOR );
$pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );       
 
for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
	$pdf->SetY($Y_Table_Position + 5);
	$pdf->SetX(0);
	$temp = substr ($order->totals[$i]['text'],0 ,3);
	if ($temp == '<b>') {
        $pdf->SetFont( PDF_INV_ORDERTOTAL2_TEXT_FONT,
                       PDF_INV_ORDERTOTAL2_TEXT_EFFECT,
                       PDF_INV_ORDERTOTAL2_TEXT_HEIGHT);  

        $text_color=explode(",",PDF_INV_ORDERTOTAL2_TEXT_COLOR );
        $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]);

        $text_color=explode(",",PDF_INV_ORDERTOTAL2_FILL_COLOR );
        $pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
        $text_color=explode(",",PDF_INV_ORDERTOTAL2_LINE_COLOR );
        $pdf->SetDrawColor( $text_color[0], $text_color[1], $text_color[2] );   
		$temp2 = substr($order->totals[$i]['text'], 3);
		$order->totals[$i]['text'] = substr($temp2, 0, strlen($temp2)-4);
	}
	$pdf->MultiCell(195,6,$order->totals[$i]['title'] . ' ' . $order->totals[$i]['text'],0,'R', '1' );
	$Y_Table_Position += 5;

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
        $text_color=explode(",",PDF_INV_ORDERTOTAL1_TEXT_COLOR );
        $pdf->SetTextColor($text_color[0], $text_color[1], $text_color[2]); 
    
     
        $text_color=explode(",",PDF_INV_ORDERTOTAL1_FILL_COLOR );
        $pdf->SetFillColor( $text_color[0], $text_color[1], $text_color[2] );  
    
        $text_color=explode(",",PDF_INV_ORDERTOTAL1_LINE_COLOR );
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
	$temp = 'invoice_' . $invoice_id['date_invoice'] . '_' . $invoice_id['num_invoice'] . '.pdf';
	$pdf->Output($temp);
?>