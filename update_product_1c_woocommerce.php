<?php
//error_reporting(E_ALL);
error_reporting  (E_ALL & ~E_NOTICE);
if (function_exists("set_time_limit")){
	set_time_limit(6000);
}

if(!defined('DS'))
{
   define('DS',DIRECTORY_SEPARATOR);
}

$directory = search_dir();
define ( 'JPATH_BASE', dirname ( __DIR__ ).DS.$directory ); //Путь к директории где установлен движок Opencart.
define ( 'DIR_LOGS', JPATH_BASE .DS. 'logs' .DS. '' );

require_once ( JPATH_BASE .DS.'database.php');

// initialize the application WooCommerce
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

if ( file_exists( ABSPATH . 'wp-config.php') ) {

	/** The config file resides in ABSPATH */
	require_once( ABSPATH . 'wp-config.php' );

} elseif ( @file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! @file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {

	/** The config file resides one level above ABSPATH but is not part of another installation */
	require_once( dirname( ABSPATH ) . '/wp-config.php' );

} else {

	// A config file doesn't exist

	define( 'WPINC', 'wp-includes' );
	require_once( ABSPATH . WPINC . '/load.php' );

	// Standardize $_SERVER variables across setups.
	wp_fix_server_vars();

	require_once( ABSPATH . WPINC . '/functions.php' );

	$path = wp_guess_url() . '/wp-admin/setup-config.php';

	/*
	 * We're going to redirect to setup-config.php. While this shouldn't result
	 * in an infinite loop, that's a silly thing to assume, don't you think? If
	 * we're traveling in circles, our last-ditch effort is "Need more help?"
	 */
	if ( false === strpos( $_SERVER['REQUEST_URI'], 'setup-config' ) ) {
		header( 'Location: ' . $path );
		exit;
	}

	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
	require_once( ABSPATH . WPINC . '/version.php' );

	wp_check_php_mysql_versions();
	wp_load_translations_early();

	// Die with an error message
	$die  = sprintf(
		/* translators: %s: wp-config.php */
		__( "There doesn't seem to be a %s file. I need this before we can get started." ),
		'<code>wp-config.php</code>'
	) . '</p>';
	$die .= '<p>' . sprintf(
		/* translators: %s: Codex URL */
		__( "Need more help? <a href='%s'>We got it</a>." ),
		__( 'https://codex.wordpress.org/Editing_wp-config.php' )
	) . '</p>';
	$die .= '<p>' . sprintf(
		/* translators: %s: wp-config.php */
		__( "You can create a %s file through a web interface, but this doesn't work for all server setups. The safest way is to manually create the file." ),
		'<code>wp-config.php</code>'
	) . '</p>';
	$die .= '<p><a href="' . $path . '" class="button button-large">' . __( "Create a Configuration File" ) . '</a>';

	wp_die( $die, __( 'WordPress &rsaquo; Error' ) );
}
global $wpdb;
define ( 'DB_PREFIX', $wpdb->prefix );

function search_dir() {
$dir_file = dirname(__FILE__);
$dir_dir = dirname ( __DIR__ );
$directory_public_html_1 = str_replace($dir_dir,"",$dir_file);
$directory_public_html_2 = str_replace("/","",$directory_public_html_1);
$directory_public_html_cc = stripcslashes($directory_public_html_2);
return $directory_public_html_cc;
} 

/*
function set_language_id() {
global $wpdb;
	
	$config_language = 'config_language';
	$language_setting = $wpdb->get_results("SELECT * FROM `" . wpdb_PREFIX . "setting` WHERE `key` = '" . $config_language . "'"); 
	if ($language_setting->num_rows) {
		$language_value = (string)$language_setting->row['value'];
		$language_query = $wpdb->get_results("SELECT language_id FROM " . wpdb_PREFIX . "language WHERE code = '" . $language_value . "'");
		if ($language_query->num_rows) {
			$language_id = (int)$language_query->row['language_id'];
			define ( 'LANGUAGE_ID', $language_id );
		}
	}else{
		define ( 'LANGUAGE_ID', 1 );//Идентификатор языка сайта, по умолчанию 1 (Русский)
	}	
	unset($language_setting);
} 
set_language_id();
*/

//Если сопоставление прооисходит по артикулу
if (isset ( $_REQUEST ['uid'] ) && isset ( $_REQUEST ['artikul'] ) && isset ( $_REQUEST ['encoding'] )) 
{
global $wpdb;
	$uid = $_REQUEST ['uid'];
	$artikul = $_REQUEST ['artikul'];
	$encoding = $_REQUEST ['encoding'];
	$artikul = html_entity_decode($artikul);
	$artikul = preg_replace ("~(\\\|\*|\?|\[|\?|\]|\(|\\\$|\))~", "",$artikul);

	$UidArray = $wpdb->get_results("SELECT ID FROM " . DB_PREFIX ."posts where product_1c_id = '" . $uid . "' AND post_type = 'product'" );	
	if ((count($UidArray) == 0)) {
		$ArtikulArray = $wpdb->get_results ( "SELECT post_id FROM " . DB_PREFIX ."postmeta where meta_value = '" . $artikul . "' and meta_key = '_sku'" );
		if (count($ArtikulArray)>0) {
			foreach($ArtikulArray as $Product){
				$product_id = (int)$Product->post_id;	
			}	
			$Update_Product = $wpdb->query ( "UPDATE " . DB_PREFIX . "posts SET product_1c_id = '".$uid."' WHERE ID = '". (int)$product_id."' AND post_type = 'product'");
			$result = "Updated product id = ".$product_id;
		}else{
			$result = "Not found the product in Article = ".$artikul;
		}
	}else{
		foreach($UidArray as $Product){
			$product_id = (int)$Product->ID;	
		}
		$result = "The product is already mapped with 1C. product_id = ".$product_id;
	}
	
	if ($encoding == 'utf8'){
		$result = iconv( "UTF-8", "CP1251//IGNORE", $result );
		print ($result);
	}else{
		print ($result);
	} 
}

//Если сопоставление прооисходит по наименованию
if (isset ( $_REQUEST ['uid'] ) && isset ( $_REQUEST ['nameproduct'] ) && isset ( $_REQUEST ['encoding'] )) 
{
global $wpdb;

	$uid = $_REQUEST ['uid'];
	//$uid = addcslashes($uid);
	$nameproduct = $_REQUEST ['nameproduct'];
	$nameproduct = decodeStringFromUrlEncode($nameproduct, 'woocommerce');
	$encoding = $_REQUEST ['encoding'];	
	
	$UidArray = $wpdb->get_results("SELECT ID FROM " . DB_PREFIX ."posts where product_1c_id = '" . $uid . "' AND post_type = 'product'" );	
	if ((count($UidArray) == 0)) {
		$NameProductArray = $wpdb->get_results ( "SELECT ID FROM " . DB_PREFIX ."posts where post_title = '" . $nameproduct . "' AND post_type = 'product'" );
		if (count($NameProductArray)>0) {
			foreach($NameProductArray as $Product){
				$product_id = (int)$Product->ID;	
			}	
			$Update_Product = $wpdb->query ( "UPDATE " . DB_PREFIX . "posts SET product_1c_id = '".$uid."' WHERE ID = '". (int)$product_id."' AND post_type = 'product'");
			$result = "Updated product id = ".$product_id;
		}else{
			$result = "Not found the product in Article = ".$artikul;
		}
	}else{
		foreach($UidArray as $Product){
			$product_id = (int)$Product->ID;	
		}
		$result = "The product is already mapped with 1C. product_id = ".$product_id;
	}
		
	if ($encoding == 'utf8'){
		$result = iconv( "UTF-8", "CP1251//IGNORE", $result );
		print ($result);
	}else{
		print ($result);
	} 	
}

if (isset ( $_REQUEST ['deleteall'] )  && isset ( $_REQUEST ['encoding'] )) 
{
global $wpdb;

	$encoding = $_REQUEST ['encoding'];
	$deleteall = $_REQUEST ['deleteall'];
	$updateall = $wpdb->query ( "UPDATE " . DB_PREFIX . "posts SET product_1c_id='' where post_type = 'product'");
	$result = "The product_1c_id was successfully delete.";
	if ($encoding == 'utf8'){
		$result = iconv( "UTF-8", "CP1251//IGNORE", $result );
		print ($result);
	}else{
		print ($result);
	} 	
}

//Сопоставление по элементу
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'search' && isset( $_REQUEST ['uidproduct'] ) && isset ( $_REQUEST ['artikulproduct'] ) && isset ( $_REQUEST ['nameproduct'] ) && isset ( $_REQUEST ['encoding'] )) 
{
global $wpdb;

	$encoding = $_REQUEST ['encoding'];
	$uidproduct = $_REQUEST ['uidproduct'];
	$artikulproduct = $_REQUEST ['artikulproduct'];
	$artikulproduct = htmlentities($artikulproduct, ENT_QUOTES, "UTF-8");
	$nameproduct = $_REQUEST ['nameproduct'];
	$nameproduct = decodeStringFromUrlEncode($nameproduct, 'woocommerce');
	
	$product_array = array();
	
	$no_spaces ='<?xml version="1.0" encoding="UTF-8"?><productInfo></productInfo>';
	$xml = new SimpleXMLElement ( $no_spaces );
	$doc = $xml->addChild ( "Товары" );
	$product_query  = $wpdb->get_results("SELECT ID FROM " . DB_PREFIX ."posts where product_1c_id = '" . $uidproduct . "' AND post_type = 'product'" );	
	if (count($product_query)>0) {
		foreach ($product_query as $product_desc){
			$product_array[] = $product_desc->ID; 
		}
	}
	if ($artikulproduct <> '0') {
		$product_query  = $wpdb->get_results ( "SELECT post_id FROM " . DB_PREFIX ."postmeta where meta_value = '" . $artikulproduct . "' and meta_key = '_sku'" );
		if (count($product_query)>0) {
			foreach ($product_query as $product_desc){
				$product_array[] = $product_desc->post_id; 
			}
		}
	}
	if ($nameproduct <> '0') {
		$product_query  = $wpdb->get_results ( "SELECT ID FROM " . DB_PREFIX ."posts where post_title = '" . $nameproduct . "' AND post_type = 'product'" );
		if (count($product_query)>0) {
			foreach ($product_query as $product_desc){
				$product_array[] = $product_desc->ID;
			}
		}
		
		$product_query  = $wpdb->get_results ( "SELECT ID FROM " . DB_PREFIX ."posts where post_title LIKE '%".$nameproduct."%' AND post_type = 'product'" );
		if (count($product_query)>0) {
			foreach ($product_query as $product_desc){
				$product_array[] = $product_desc->ID;
			}
		}
	}
	
	$product_array = array_unique($product_array);
	foreach ($product_array as $product_id){
		$product_info_array = array();
		$product_query  = $wpdb->get_results("SELECT * FROM " . DB_PREFIX ."posts where ID = '" . $product_id . "' AND post_type = 'product'" );	
		if (count($product_query)>0) {
			if (count($product_query)>0) {
				foreach ($product_query as $product_desc){
					$product_info_array['post_title'] = $product_desc->post_title;
					$product_info_array['product_1c_id'] = $product_desc->product_1c_id;
					$product_info_array['post_status'] = $product_desc->post_status;
				}
			}
			$product_description_query  = $wpdb->get_results ( "SELECT * FROM " . DB_PREFIX ."postmeta where post_id = '" . $product_id . "'" );
			if (count($product_description_query)>0) {
				foreach ($product_description_query as $product_desc){
					$product_info_array[$product_desc->meta_key] = $product_desc->meta_value;
				}
			}
			
			$product_id = (int)$product_id;
			$product_name = (isset($product_info_array['post_title'])) ? (string)$product_info_array['post_title'] : 'Наименование не заполнено';
			$product_1c_id = (isset($product_info_array['product_1c_id'])) ? (string)$product_info_array['product_1c_id'] : '';
			$product_model = (isset($product_info_array['post_status'])) ? (string)$product_info_array['post_status'] : 'other';
			$product_sku = (isset($product_info_array['_sku'])) ? (string)$product_info_array['_sku'] : '';
			$product_option_doch_count = getCountOptionDochProduct($product_id);
				
			$t1 = $doc->addChild ( "Товар" );
			$t2 = $t1->addChild ( "ИдСайта", $product_id );
			$t2 = $t1->addChild ( "НаименованиеТовара", formatStringForXML($product_name));
			$t2 = $t1->addChild ( "Модель", formatStringForXML($product_model));
			$t2 = $t1->addChild ( "SKU", formatStringForXML($product_sku));
			$t2 = $t1->addChild ( "ИдНоменклатуры1С", $product_1c_id);
			$t2 = $t1->addChild ( "КолвоОпцийДочТоваров", $product_option_doch_count);
		}
	}
	
	if ($encoding == 'utf8'){
		$xml_text = $xml->asXML();
		header("Content-Type: text/xml");
		$text = iconv( "UTF-8", "CP1251//TRANSLIT", $xml_text );
		print $text;
	}else {
		header("Content-Type: text/xml");
		print $xml->asXML ();
	}	
}

if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'update' && isset( $_REQUEST ['uidproduct'] ) && isset ( $_REQUEST ['productid'] ) && isset ( $_REQUEST ['encoding'] )) 
{
global $wpdb;

	$encoding = $_REQUEST ['encoding'];
	$uidproduct = $_REQUEST ['uidproduct'];
	$productid = $_REQUEST ['productid'];
	
	if ((!empty($productid)) and ($productid <> '0')) {
		$product_query  = $wpdb->get_results ( "SELECT ID FROM " . DB_PREFIX . "posts WHERE product_1c_id = '".$uidproduct."' and post_type = 'product'"); 
		if (count($product_query)>0) {
			foreach ($product_query as $product_desc){
				$product_id_find = (int)$product_desc->ID;
				$update_product = $wpdb->query ( "UPDATE " . DB_PREFIX . "posts SET product_1c_id='' WHERE ID = '".$product_id_find."' and post_type = 'product'");	
			}
		}
		$update_product = $wpdb->query ( "UPDATE " . DB_PREFIX . "posts SET product_1c_id='".$uidproduct."' WHERE ID = '".(int)$productid."' and post_type = 'product'");
		$result = "Updated product id =".$productid;
	}else{
		$result = "Bad result. No find product_id";
	}
	
	if ($encoding == 'utf8'){
		$result = iconv( "UTF-8", "CP1251//IGNORE", $result );
		print ($result);
	}else{
		print ($result);
	} 	
}


function getCountOptionDochProduct ($product_id) {
global $wpdb;
		
	$result = 0;
	$OptionDochProductQuery = $wpdb->get_results ( "SELECT COUNT(*) AS cn FROM " . DB_PREFIX ."posts WHERE post_parent = '" . $product_id . "' and post_type = 'product_variation'" );
	if (count($OptionDochProductQuery)>0) {
		foreach ($OptionDochProductQuery as $OptionDochProduct){
			$result = $OptionDochProduct->cn;
		}
	}
	return $result;
}

function decodeStringFromUrlEncode($string, $type_cms = 'opencart') {
	$string = htmlentities($string, ENT_QUOTES, "UTF-8");	
	$encoding_str = mb_detect_encoding($string);
	if($encoding_str != "UTF-8"){
		$string = iconv( $encoding_str, "UTF-8//IGNORE", $string );
	}
	if ($type_cms == 'woocommerce'){
		$string = htmlspecialchars_decode($string);
	}
	$string = stripslashes($string);
	return $string;
}
?>