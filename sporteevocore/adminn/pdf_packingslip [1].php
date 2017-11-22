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
 require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_ORDERS_INVOICE);

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
	//Logo
    $this->Image('images/invoice_logo.jpg',5,10,50);

    // Invoice Number and date
	$this->SetFont('Arial','B',14);
	$this->SetTextColor(158,11,14);
	$this->SetY(37);
	$this->MultiCell(100,6,"Invoice: #" . $oID . "\n" . $date ,0,'L');
	
	// Company Address
	$this->SetX(0);
	$this->SetY(10);
    $this->SetFont('Arial','B',10);
	$this->SetTextColor(158,11,14);
    $this->Ln(0);
    $this->Cell(149);
	$this->MultiCell(50, 3.5, STORE_NAME_ADDRESS,0,'L');  
	
	//email
	$this->SetX(0);
	$this->SetY(37);
	$this->SetFont('Arial','B',10);
	$this->SetTextColor(158,11,14);
	$this->Ln(0);
    $this->Cell(95);
	$this->MultiCell(100, 6, "E-mail: " . STORE_OWNER_EMAIL_ADDRESS,0,'R');
	
	//website
	$this->SetX(0);
	$this->SetY(42);
	$this->SetFont('Arial','B',10);
	$this->SetTextColor(158,11,14);
	$this->Ln(0);
    $this->Cell(88);
	$this->MultiCell(100, 6, "Web: " . HTTP_SERVER,0,'R');
}

function Footer()
{
    //Position at 1.5 cm from bottom
    $this->SetY(-17);
    //Arial italic 8
    $this->SetFont('Arial','',10);
	$this->SetTextColor(158,11,14);
	$this->Cell(0,10, PRINT_INVOICE_TEXT, 0,0,'C');
	//$this->SetY(-15);
   	//$this->Cell(0,10, PRINT_INVOICE_URL, 0,0,'C');
	//Page number
    //$this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
}
//Instanciation of inherited class
$pdf=new PDF();

// Set the Page Margins
$pdf->SetMargins(6,2,6);

// Add the first page
$pdf->AddPage();

//Draw the top line with invoice text
$pdf->Cell(50);
$pdf->SetY(60);
$pdf->SetDrawColor(153,153,153);
$pdf->Cell(15,.1,'',1,1,'L',1);
$pdf->SetFont('Arial','BI',15);
$pdf->SetTextColor(153,153,153);
$pdf->Text(22,61.5,'Delivery Note');
$pdf->SetY(60);
$pdf->SetDrawColor(153,153,153);
$pdf->Cell(52);
$pdf->Cell(143,.1,'',1,1,'L',1);

//Draw Box for Invoice Address
$pdf->SetDrawColor(0);
$pdf->SetLineWidth(0.2);
$pdf->SetFillColor(245);
$pdf->RoundedRect(6, 67, 90, 35, 2, 'DF');

//Draw the invoice address text
    $pdf->SetFont('Arial','B',10);
	$pdf->SetTextColor(0);
	$pdf->Text(11,77, ENTRY_SHIP_TO);
	$pdf->SetX(0);
	$pdf->SetY(80);
    //$pdf->SetFont('Arial','B',8);
	//$pdf->SetTextColor(0);
    $pdf->Cell(9);
	$pdf->MultiCell(70, 3.3, tep_address_format(1, $order->delivery, '', '', "\n"),0,'L');
	
	//Draw Box for Order Number, Date & Payment method
	$pdf->SetDrawColor(0);
	$pdf->SetLineWidth(0.2);
	$pdf->SetFillColor(245);
	$pdf->RoundedRect(6, 107, 192, 11, 2, 'DF');

	//Draw Order Number Text
	$temp = str_replace('&nbsp;', ' ', PRINT_INVOICE_ORDERNR);
	$pdf->Text(10,113, $temp . tep_db_input($oID));	
	//Draw Date of Order Text
	$temp = str_replace('&nbsp;', ' ', PRINT_INVOICE_DATE);
	$pdf->Text(75,113,$temp . tep_date_short($order->info['date_purchased']));	
		
//Fields Name position
$Y_Fields_Name_position = 125;
//Table position, under Fields Name
$Y_Table_Position = 131;


function output_table_heading($Y_Fields_Name_position){
    global $pdf;
//First create each Field Name
//Gray color filling each Field Name box
$pdf->SetFillColor(245);
//Bold Font for Field Name
$pdf->SetFont('Arial','B',10);
$pdf->SetY($Y_Fields_Name_position);
$pdf->SetX(6);
$pdf->Cell(14,6,'Qty',1,0,'C',1);
$pdf->SetX(20);
$pdf->Cell(40,6,TABLE_HEADING_PRODUCTS_MODEL,1,0,'C',1);
$pdf->SetX(60);
$pdf->Cell(138,6,TABLE_HEADING_PRODUCTS,1,0,'C',1);
$pdf->Ln();
}
output_table_heading($Y_Fields_Name_position);
//Show the products information line by line
for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
	$pdf->SetFont('Arial','',10);
	$pdf->SetY($Y_Table_Position);
	$pdf->SetX(6);
	$pdf->MultiCell(14,6,$order->products[$i]['qty'],1,'C');
	$pdf->SetY($Y_Table_Position);
	$pdf->SetX(20);
    $pdf->SetFont('Arial','',8);
	$pdf->MultiCell(40,6,$order->products[$i]['model'],1,'C');
	$pdf->SetY($Y_Table_Position);
	$pdf->SetX(60);
	if (strlen($order->products[$i]['name']) > 40 && strlen($order->products[$i]['name']) < 50){
		$pdf->SetFont('Arial','',8);
		$pdf->MultiCell(138,6,$order->products[$i]['name'],1,'L');
		}
	else if (strlen($order->products[$i]['name']) > 50){
		$pdf->SetFont('Arial','',8);
		$pdf->MultiCell(138,6,substr($order->products[$i]['name'],0,50),1,'L');
		}
	else{
		$pdf->MultiCell(138,6,$order->products[$i]['name'],1,'L');
		}
	$Y_Table_Position += 6;

    //Check for product line overflow
     $item_count++;
    if ((is_long($item_count / 32) && $i >= 20) || ($i == 20)){
        $pdf->AddPage();
        //Fields Name position
        $Y_Fields_Name_position = 125;
        //Table position, under Fields Name
        $Y_Table_Position = 70;
        output_table_heading($Y_Table_Position-6);
        if ($i == 20) $item_count = 1;
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