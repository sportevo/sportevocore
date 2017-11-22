<?php
/*
  $Id: osc_export.php, 2004-02-07
  
  H. Zimniak, zimniak.com
  zimniak.com - digitale mediaproducties
  http://www.zimniak.com

  Werking:
  Deze module haalt bepaalde velden van een order op ter verdere verwerking
  binnen een third party ordersysteem of database. Aangezien Francesco Rossi de
  benodigde velden over meerdere tabellen verdeeld opslaat, voorzien drie
  sql-queries in het ophalen van alle benodgde velden.
  
  Het systeem is eenvoudig aan te passen om meerdere velden te exporteren
  of het veldscheidingsteken aan te passen.

  Released under the GNU General Public License
*/

// geef gewenst veldscheidingsteken op (in dit voorbeeld 'komma')
$fieldseparator = ',';

require('file:///C|/Users/Admin/AppData/Local/Temp/Temp1_osC CSV Export.zip/osC CSV Export/catalog/admin/includes/application_top.php');

?>

<!doctype html public '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=<?php echo CHARSET; ?>'>
<title><?php echo TITLE; ?></title>
<link rel='stylesheet' type='text/css' href='file:///C|/Users/Admin/AppData/Local/Temp/Temp1_osC CSV Export.zip/osC CSV Export/catalog/admin/includes/stylesheet.css'>
</head>
<body marginwidth='0' marginheight='0' topmargin='25' bottommargin='0' leftmargin='25' rightmargin='0' bgcolor='#FFFFFF'>

<?php
$query1 = mysql_query('SELECT * FROM orders WHERE orders_id=$id') or die (mysql_error());
$tabel1 = mysql_fetch_array($query1);

$cid = $tabel1['customers_id'];
$ordernr = $tabel1['orders_id'];
$klantnaam = $tabel1['customers_name'];
$bedrijfsnaam = $tabel1['customers_company'];
$postadres = $tabel1['customers_street_address'];
$postplaats = $tabel1['customers_city'];
$postcode = $tabel1['customers_postcode'];
$telefoon = $tabel1['customers_telephone'];
$telfax = $tabel1['customers_fax'];
$email = $tabel1['customers_email_address'];
$afl_tav = $tabel1['delivery_name'];
$afl_bedr = $tabel1['delivery_company'];
$afl_adres = $tabel1['delivery_street_address'];
$afl_plaats = $tabel1['delivery_city'];
$afl_pc = $tabel1['delivery_postcode'];
$betaalwijze = $tabel1['payment_method'];

// haal gegevens uit klantenbestand (standaardadres, geslacht, telefax)
$query2 = mysql_query('SELECT * FROM customers WHERE customers_id=$cid') or die (mysql_error());
$tabel2 = mysql_fetch_array($query2);

$defadid = $tabel2['customers_default_address_id'];
$telefax = $tabel2['customers_fax'];
$geslacht = $tabel2['customers_gender'];

// *** BOF ALLEEN BENODIGD ALS TVA INTRACOM 3.3 GEINSTALLEERD IS ***
// haal gegevens uit standaard adresboek (VAT)
$query3 = mysql_query('SELECT * FROM address_book WHERE address_book_id=$defadid') or die (mysql_error());
$tabel3 = mysql_fetch_array($query3);

$vat = $tabel3['entry_tva_intracom'];
// *** EOF ALLEEN BENODIGD ALS TVA INTRACOM 3.3 GEINSTALLEERD IS ***


// verzamel benodigde velden in variabele '$export'
$export = $ordernr.$fieldseparator
.$cid.$fieldseparator
.$geslacht.$fieldseparator
.$klantnaam.$fieldseparator
.$bedrijfsnaam.$fieldseparator
.$postadres.$fieldseparator
.$postplaats.$fieldseparator
.$postcode.$fieldseparator
.$telefoon.$fieldseparator
.$telefax.$fieldseparator
.$email.$fieldseparator
.$afl_tav.$fieldseparator
.$afl_bedr.$fieldseparator
.$afl_adres.$fieldseparator
.$afl_plaats.$fieldseparator
.$afl_pc.$fieldseparator
// *** BOF ALLEEN BENODIGD ALS TVA INTRACOM 3.3 GEINSTALLEERD IS ***
.$vat.$fieldseparator;
// *** EOF ALLEEN BENODIGD ALS TVA INTRACOM 3.3 GEINSTALLEERD IS ***

// schermcontrole van (een deel van) de ge�xporteerde gegevens
echo '<table><tr><td class=main colspan=2><b>Er is een CSV-exportbestand aangemaakt voor:</b><br><br>';
echo 'Order ID: <b>'.$ordernr.'</b><br>';
echo 'Customer ID: <b>'.$cid.'</b><br>';
echo 'Achternaam: <b>'.$klantnaam.'</b><br>';
echo 'Bedrijfsnaam: <b>'.$bedrijfsnaam.'</b><br>';
echo 'Plaats: <b>'.$postplaats.'</b><br>';
echo 'E-mailadres: <b>'.$email.'</b><br>';
echo '<td></tr><BR><center><font face=verdana size=1>osC CSV Export 1.0<HR><BR>';

// toon verzamelde velden in tekstveld (incl. mogelijkheid om de complete inhoud te selecteren
echo '<tr><td><BR><BR><form>
<input type=button value="Selecteer inhoud van het hele veld" onClick="javascript:this.form.scriptsource.focus();this.form.scriptsource.select();"><br>
<textarea name=scriptsource rows=10 cols=50 wrap=ON>'.$export.'</textarea></form></td></tr></table></html>';
?>


