<?php
/**
 * iphone.php -- version 1.5.0 (2010-03-25)
 * 
 * An Francesco Rossi/Zen Cart/xtCommerce plugin for use with the iPhone/iPod-App "MyShop", available at the iTunes AppStore.
 * This file should be located in the admin directory of your Francesco Rossi/Zen Cart installation.
 *
 * By Dirk Malorny <info (at) malorny (dot) net>
 * You may modify this script to fulfill your special needs, but all modifications you make are at your own risk!
 */
 
require('includes/application_top.php');
header("Content-type: text/xml");

define("VERSION","1.5.0");
define("DELIMITER","|");
define("TABLE_IPHONE",'iphone');
// =========================== MAIN ===========================


if (function_exists("zen_db_input")) {
	define("SHOPSYSTEM","zen");
} else if (function_exists("xtc_db_input")) {
	define("SHOPSYSTEM","xtc");
} else {
	define("SHOPSYSTEM","osc");
}
	
switch ($_REQUEST['met'])
{

	case 'ping':	
		
		$xml_data = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>"."\n\t"."  <response id=\"ping\" status=\"yes\" /> ";
		echo $xml_data;
		break;
	
	case 'ping_with_auth':		
	 				
		$usernm = $_REQUEST['usr'];
		$pass = $_REQUEST['pwd'];
			
		if(user_authantication($usernm,$pass))
			$xml_data = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>"."\n\t"."<response id=\"ping_with_auth\" status=\"yes\" />";
		else
			$xml_data = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>"."\n\t"."<response id=\"ping_with_auth\" status=\"no\" />";		
			
		echo $xml_data;
		break;
						
	case 'orders':	
	 	
		$usernm = $_REQUEST['usr'];
		$pass = $_REQUEST['pwd'];
		
		if(user_authantication($usernm,$pass))
		{
			
			if($_REQUEST['date'] != '' and !is_null($_REQUEST['date']))
				$date = date('Y-m-d',strtotime($_REQUEST['date'])); 
			else
				$date = NULL; 
				
			$orderid = NULL;
			if(isset($_REQUEST['sortby']) and $_REQUEST['sortby'] != '')
			{
				if($_REQUEST['sortby']=='name')
					$orderby = 'o.customers_name';
				elseif ($_REQUEST['sortby']=='date')
					$orderby = 'o.date_purchased';
				elseif ($_REQUEST['sortby']=='amount')
					$orderby = 'order_total';
				else
					$orderby = 'o.orders_id';
			}
			else
			{
				$orderby = 'orders_id';	
			}
			$limit = NULL;
			$orders_query = create_orders_query($date,$orderid,$orderby,$limit);
			$num_row = mysql_num_rows($orders_query);
			/*$orders = fetch($orders_query);
			var_dump($orders);*/
			if($num_row)
			{
				$xml_data = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>"."\n";
				$xml_data .= "<orders>"."\n";
				
				while($orders = fetch($orders_query)){
					$new = 0;
					$update = 0;
					
					if($orders['orders_status_id'] == 1)
						$new = 1;	
					else
						$update = 1;
						
					$xml_data .= "\t" . "<order order_id='".$orders['orders_id']."' currency='".$orders['currency']."' status='".$orders['orders_status_name']."' date='".date ('d-m-Y H:i:s',strtotime($orders['date_purchased']))."' exltax_total=\"\" inctax_total='".$orders['order_total']."' shipping_street='".$orders['delivery_street_address']."' shipping_city='".$orders['delivery_city']."' shipping_postcode='".$orders['delivery_postcode']."' shipping_country_name='".$orders['delivery_country']."' shipping_telephone=\"\" billing_street='".$orders['billing_street_address']."' billing_city='".$orders['billing_city']."' billing_postcode='".$orders['billing_postcode']."' billing_country_name='".$orders['billing_country']."' billing_telephone=\"\" new=\"$new\" updated=\"$update\">"."\n";
					
					$customer_query = create_customer_query(NULL,$orders['customers_id'],NULL);
					$customer = fetch($customer_query);
					
					$xml_data .= "\t\t"."<customer customer_id='". $customer['customers_id'] ."' firstname='". $customer['customers_firstname'] ."' lastname='". $customer['customers_lastname'] ."' email='" . $customer['customers_email_address'] ."' street='". $customer['entry_street_address'] ."' city='". $customer['entry_city'] . "' postcode='". $customer['entry_postcode'] ."' country_name='". $customer['countries_name'] . "' telephone='". $customer['customers_telephone'] ."' />"."\n";
					
					$xml_data .= "\t\t"."<shipping description='". $orders['shipping_method'] ."' exltax=\"\" inctax='". str_replace ('$','',$orders['shipping_rate']) ."'/>"."\n";
					$xml_data .= "\t\t"."<products>"."\n";
					
					$item_query = create_items_query($orders['orders_id']);
					while($items = fetch($item_query)){
						$xml_data .= "\t\t\t"."<product product_id='". $items['products_id'] ."' sku='". $items['products_model'] ."' name='". htmlspecialchars($items['products_name'],ENT_QUOTES) ."' qty_ordered='". $items['products_quantity'] ."' exltax_price='". $items['products_price'] * $items['currency_value'] ."' inctax_price='". $items['final_price'] * $items['currency_value'] ."'/>"."\n";
					}
					$xml_data .= "\t\t"."</products>"."\n";
					$xml_data .= "\t"."</order>"."\n";
				}
			
				$xml_data .= "</orders>";
			}
			else
			{
				$xml_data .= "<?xml version=\"1.0\" encoding=\"utf-8\" ?>"."\n\t"."<response id=\"orders\" status=\"empty\" />";
			}
			
			if($num_row)
			{
				update_access_date();
			}
			
		}
		else
		{
			$xml_data = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>"."\n\t"."<response id=\"ping_with_auth\" status=\"no\" />";		
		}
		
			echo $xml_data; 
		break;

	case 'summary':	

		$xml_data = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>";
		$xml_data .= "\n\t";
		
		$usernm = $_REQUEST['usr'];
		$pass = $_REQUEST['pwd'];
		
		if(user_authantication($usernm,$pass))
		{
			
			$date = last_access_date();
			$summary_array = create_summary_query($date['last_access_date']);
			$curr = '';
			$net = 0;
			$gross = 0;
			$totorders = 0;
			$customers = 0;
			$c_id = 0;
			$ordlstacsdt = 0;
			$ordupdacsdt = 0;
			while ($summary = fetch($summary_array))
			{
				$totorders++;
				$curr = $summary['currency'];
				$net += $summary['subtotal'] - $summary['tax_rate'];
				$gross += $summary['order_total'];
				if($c_id != $summary['customers_id']) {
					$customers++;
					$c_id = $summary['customers_id'];
				}
				/*echo '<pre>';
				var_dump($summary);
				die;*/
			}
			
			if($date['last_access_date'] != '0000-00-00')
			{
				$summary_array_last_acs = create_summary_query($date['last_access_date']);
				$ordlstacsdt = mysql_num_rows($summary_array_last_acs);
				$summary_array_last_mod = create_summary_query($date['last_access_date'],true);
				$ordupdacsdt = mysql_num_rows($summary_array_last_mod);
				$lstacsdt = date('d-m-Y G:i:s',strtotime($date['last_access_date']));
				
			}
			else
			{
				$lstacsdt = '';
				$ordlstacsdt = $totorders;
			}
			
			$xml_data .= "<summary currency=\"$curr\" net=\"$net\" gross=\"$gross\" orders=\"$totorders\" customers=\"$customers\" lastaccessdate=\"$lstacsdt\" orders_created_after_lastaccess=\"$ordlstacsdt\" orders_updated_after_lastaccess=\"$ordupdacsdt\"/>";
		}
		else
		{
			$xml_data .= "<response id=\"ping_with_auth\" status=\"no\" />";
		}
		
		echo $xml_data;
			//die;
		
		break;
		


default:		
		
		$xml_data = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>" . "\n\t" . "<response id=\"login\" status=\"error\" reason=\"access_denied\" />";
		echo $xml_data;
		break;
}



// =========================== QUERY functions ===========================

function create_customer_query($lu,$of,$li)
{
	$filter = '';
	$limit  = '';
	if (isset($lu) && my_not_null($lu)) {
		$last_update = my_db_input(my_db_prepare_input($lu));
		$filter .= " and i.customers_info_date_account_created > '$last_update' 
					  or i.customers_info_date_account_last_modified > '$last_update'";
	}
	if (isset($of) && my_not_null($of)) {
		$offset = my_db_input(my_db_prepare_input($of));
		$filter .= " and c.customers_id = $offset";
	}
	if (isset($li) && my_not_null($li)) {
		$limit = "limit " . my_db_input(my_db_prepare_input($li));
	}

	$customers_query = query("
	select 
		c.customers_id, 
		c.customers_lastname,
		c.customers_firstname,
		c.customers_email_address,
		NULLIF(DATE_FORMAT(c.customers_dob,'%Y-%m-%d'),'0000-00-00') customers_dob,
		c.customers_telephone,
		c.customers_fax,
		a.entry_company,
		a.entry_street_address,
		a.entry_postcode,
		a.entry_city,
		a.entry_state,
		l.countries_name 
	from " . TABLE_CUSTOMERS . " c 
		 left join " . TABLE_ADDRESS_BOOK . " a on c.customers_id = a.customers_id and c.customers_default_address_id = a.address_book_id
		 left join " . TABLE_COUNTRIES . " l on a.entry_country_id = l.countries_id
		 left join " . TABLE_CUSTOMERS_INFO . " i on  c.customers_id = i.customers_info_id
	where 1
		$filter 
	order by c.customers_id
	$limit
	");
	
	return $customers_query;
}

function create_products_query($lu,$of,$li)
{
	$filter = '';
	$limit  = '';
	if (isset($lu) && my_not_null($lu)) {
		$last_update = my_db_input(my_db_prepare_input($lu));
		$filter = " and p.products_date_added > '$last_update' 
					  or p.products_last_modified > '$last_update'";
	}
	if (isset($of) && my_not_null($of)) {
		$offset = my_db_input(my_db_prepare_input($of));
		$filter .= " and p.products_id < $offset";
	}
	if (isset($li) && my_not_null($li)) {
		$limit = "limit " . my_db_input(my_db_prepare_input($li));
	}

	$products_query = query("
	select 
		p.products_id, 
		p.products_model,
		p.products_price,
		d.products_name,
		d.products_description
	from " . TABLE_PRODUCTS . " p
		 left join " . TABLE_PRODUCTS_DESCRIPTION . " d on d.products_id = p.products_id
		 left join " . TABLE_LANGUAGES . " l on l.languages_id = d.language_id
	where 1
		$filter 
	order by p.products_id DESC
	$limit
	");
	
	return $products_query;
}

/*
	function create_orders_query
	paramater::
	$lu = last update date or last modified date.
	$of = Order id.
	$ob = Order by .. eg. name,order id, order date, amount.
	$li = Number of recorders want to fetch.
*/

function create_orders_query($lu,$of,$ob,$li)
{
	$filter = '';
	$limit  = '';
	$orderby = 'order by o.orders_id';
	
	if (isset($lu) && my_not_null($lu)) {
		$last_update = my_db_input(my_db_prepare_input($lu));
		$filter .= " and (o.date_purchased >= '$last_update' or o.last_modified >= '$last_update')";
	}
	else
	{
		$date = last_access_date();
		$last_update = $date['last_access_date'];
		$filter .= " and (o.date_purchased >= '$last_update' or ( o.last_modified != 'NULL' and o.last_modified <= '$last_update'))";
	}
	
	if (isset($of) && my_not_null($of)) {
		$offset = my_db_input(my_db_prepare_input($of));
		$filter .= " and o.orders_id < $offset";
	}
	
	if (isset($ob) && my_not_null($ob)) {
		$orderby = "order by " . my_db_input(my_db_prepare_input($ob));
	}
	
	if (isset($li) && my_not_null($li)) {
		$limit = "limit " . my_db_input(my_db_prepare_input($li));
	}
	
	/*echo "
	select 
		o.orders_id,
		o.customers_id,
		o.customers_name,
		o.payment_method,
		DATE_FORMAT(o.date_purchased,'%Y-%m-%d %H:%i:%s') date_purchased,
		o.currency,
		o.currency_value,
		s.orders_status_id,
		s.orders_status_name,
		(select value from " . TABLE_ORDERS_TOTAL . " ot where o.orders_id = ot.orders_id and class='ot_total') as order_total,
		(select title from " . TABLE_ORDERS_TOTAL . " ot where o.orders_id = ot.orders_id and class='ot_shipping') as shipping_method,
		(select text from " . TABLE_ORDERS_TOTAL . " ot where o.orders_id = ot.orders_id and class='ot_shipping') as shipping_rate,
		o.customers_street_address,
		o.customers_city,
		o.customers_postcode,
		o.customers_state,
		o.customers_country,
		o.delivery_name,
		o.delivery_street_address,
		o.delivery_city,
		o.delivery_postcode,
		o.delivery_state,
		o.delivery_country,
		o.billing_name,
		o.billing_street_address,
		o.billing_city,
		o.billing_postcode,
		o.billing_state,
		o.billing_country
	from " . TABLE_ORDERS . " o,
		 " . TABLE_ORDERS_STATUS . " s 
	where o.orders_status = s.orders_status_id 
		and s.language_id = '".fetch_default_language_id()."' 
		$filter
	 $orderby DESC
	$limit
	";
	die;*/
	
	$orders_query = query("
	select 
		o.orders_id,
		o.customers_id,
		o.customers_name,
		o.payment_method,
		DATE_FORMAT(o.date_purchased,'%Y-%m-%d %H:%i:%s') date_purchased,
		o.currency,
		o.currency_value,
		s.orders_status_id,
		s.orders_status_name,
		(select value from " . TABLE_ORDERS_TOTAL . " ot where o.orders_id = ot.orders_id and class='ot_total') as order_total,
		(select title from " . TABLE_ORDERS_TOTAL . " ot where o.orders_id = ot.orders_id and class='ot_shipping') as shipping_method,
		(select text from " . TABLE_ORDERS_TOTAL . " ot where o.orders_id = ot.orders_id and class='ot_shipping') as shipping_rate,
		o.customers_street_address,
		o.customers_city,
		o.customers_postcode,
		o.customers_state,
		o.customers_country,
		o.delivery_name,
		o.delivery_street_address,
		o.delivery_city,
		o.delivery_postcode,
		o.delivery_state,
		o.delivery_country,
		o.billing_name,
		o.billing_street_address,
		o.billing_city,
		o.billing_postcode,
		o.billing_state,
		o.billing_country
	from " . TABLE_ORDERS . " o,
		 " . TABLE_ORDERS_STATUS . " s 
	where o.orders_status = s.orders_status_id 
		and s.language_id = '".fetch_default_language_id()."' 
		$filter
	 $orderby DESC
	$limit
	");
	
	return $orders_query;
}

function create_summary_query($date,$mod = false)
{
	$filter = " and s.orders_status_id = 3 ";
	if(!is_null($date))
	{
		$filter = " and s.orders_status_id = 3 and o.date_purchased >= '$date'";
	}
	
	if($mod)
	{
		$filter = " and o.date_purchased < '$date' and o.last_modified >= '$date'";
	}
	
	
	$summary_query = query("
	select 
		o.orders_id,
		o.customers_id,
		o.customers_name,
		o.payment_method,
		DATE_FORMAT(o.date_purchased,'%Y-%m-%d %H:%i:%s') date_purchased,
		o.currency,
		o.currency_value,
		s.orders_status_id,
		s.orders_status_name,
		(select value from " . TABLE_ORDERS_TOTAL . " ot where o.orders_id = ot.orders_id and class='ot_total') as order_total,
		(select value from " . TABLE_ORDERS_TOTAL . " ot where o.orders_id = ot.orders_id and class='ot_shipping') as shipping_rate,
		(select value from " . TABLE_ORDERS_TOTAL . " ot where o.orders_id = ot.orders_id and class='ot_tax') as tax_rate,
		(select value from " . TABLE_ORDERS_TOTAL . " ot where o.orders_id = ot.orders_id and class='ot_subtotal') as subtotal,
		o.customers_street_address,
		o.customers_city,
		o.customers_postcode,
		o.customers_state,
		o.customers_country,
		o.delivery_name,
		o.delivery_street_address,
		o.delivery_city,
		o.delivery_postcode,
		o.delivery_state,
		o.delivery_country,
		o.billing_name,
		o.billing_street_address,
		o.billing_city,
		o.billing_postcode,
		o.billing_state,
		o.billing_country
	from " . TABLE_ORDERS . " o,
		 " . TABLE_ORDERS_STATUS . " s 
	where o.orders_status = s.orders_status_id 
		and s.language_id = '".fetch_default_language_id()."'
		$filter
	 order by o.orders_id DESC
	");
	
	return $summary_query;
}


function create_items_query($orderIds)
{
	$allow_tax = (SHOPSYSTEM == "xtc" ? ",op.allow_tax" : "");
	
	$items_query = query("
	select 
		op.orders_products_id,
		op.orders_id,
		op.products_id,
		op.products_quantity,
		op.products_model,
		op.products_name,
		op.products_price,
		op.final_price,
		op.products_tax,
		o.currency_value,
		group_concat(replace(concat_ws(':',replace(opa.products_options,':','{colon}'),replace(opa.products_options_values,':','{colon}')),'*','{star}') separator '*') attributes
		$allow_tax
	from " . TABLE_ORDERS_PRODUCTS . " op 
	left outer join
		 " . TABLE_ORDERS_PRODUCTS_ATTRIBUTES . " opa
		 on op.orders_id=opa.orders_id
		and op.orders_products_id=opa.orders_products_id
	left outer join
		 " . TABLE_ORDERS . " o
		 on op.orders_id=o.orders_id
	where op.orders_id in ($orderIds)
	group by op.orders_products_id
	order by op.orders_id DESC
	");
	
	return $items_query;
}

function create_status_query()
{
	$status_query = query("
	select 
		s.orders_status_id,
		s.orders_status_name
	from " . TABLE_ORDERS_STATUS . " s 
	where s.language_id = '".fetch_default_language_id()."' 
	order by s.orders_status_id
	");
	
	return $status_query;
}

function create_order_count_query()
{
	$query = query("select count(*) as count from ".TABLE_ORDERS);
	return $query;
}

function create_customer_count_query()
{
	$query = query("select count(*) as count from ".TABLE_CUSTOMERS);
	return $query;
}

function user_authantication($usernm,$pass)
{				
	$customers_query = query("select * from ".TABLE_IPHONE." where username like('$usernm') and password like('$pass')");
	$user_total = mysql_num_rows($customers_query);
	if($user_total)
		return true;
	else
		return false;
}

function last_access_date()
{
	$date_query = query("select * from ".TABLE_IPHONE." where id = 1");
	return fetch($date_query);
}

function update_access_date()
{
	$today = date('Y-m-d G:i:s',time());
	query("update ".TABLE_IPHONE." set last_access_date = '$today' where id = 1");
}

// =========================== MISC functions ===========================

function outputLine($arr) {
	$arr = array_map("clean", $arr);
	$line = implode(DELIMITER,$arr);
	$line = unhtmlentities($line);
	$line = mb_convert_encoding($line,"UTF-8");
	echo $line."\n";
}

function outputStatusLine($arr) {
	$line = implode("*",$arr);
	$line = unhtmlentities($line);
	$line = mb_convert_encoding($line,"UTF-8");
	echo $line."\n";
}

function clean($str)
{
	$str = str_replace("\n",' ',$str);
	$str = str_replace(DELIMITER,'{pipe}',$str);
	return $str;
}

function escape($str)
{
	$str = str_replace(':','{colon}',$str);
	$str = str_replace('*','{star}',$str);
	return $str;
}

function unhtmlentities($string)
{
    // replace numeric entities
    $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
    $string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
    // replace literal entities
    $trans_tbl = get_html_translation_table(HTML_ENTITIES);
    $trans_tbl = array_flip($trans_tbl);
    return strtr($string, $trans_tbl);
}

function query($sql) {
	$result = my_db_query($sql);
	if (!$result) {
		echo mysql_error();
	}
	return $result;
}

function fetch($resultset) {
	$result = my_db_fetch_array($resultset);
	if (!$result) {
		echo mysql_error();
	}
	return $result;
}

function fetch_default_language_id() {
	$query = query("select languages_id from " . TABLE_LANGUAGES . " where code like '". DEFAULT_LANGUAGE ."'");
	$result = tep_db_fetch_array($query);
	if (!$result) {
		echo mysql_error();
	}
	return $result['languages_id'];
}


// ============================== Adapters ==============================
function my_not_null($q) {
	switch (SHOPSYSTEM) {
		case "zen": return zen_not_null($q);
		case "xtc": return xtc_not_null($q);
		default:    return tep_not_null($q);
	}
}

function my_db_input($q) {
	switch (SHOPSYSTEM) {
		case "zen": return zen_db_input($q);
		case "xtc": return xtc_db_input($q);
		default:    return tep_db_input($q);
	}
}

function my_db_query($q) {
	switch (SHOPSYSTEM) {
		case "zen": return mysql_query($q);
		case "xtc": return xtc_db_query($q);
		default:    return tep_db_query($q);
	}
}

function my_db_fetch_array($q) {
	switch (SHOPSYSTEM) {
		case "zen": return mysql_fetch_assoc($q);
		case "xtc": return xtc_db_fetch_array($q);
		default:    return tep_db_fetch_array($q);
	}
}

function my_db_prepare_input($q) {
	switch (SHOPSYSTEM) {
		case "zen": return zen_db_prepare_input($q);
		case "xtc": return xtc_db_prepare_input($q);
		default:    return tep_db_prepare_input($q);
	}
}

?>