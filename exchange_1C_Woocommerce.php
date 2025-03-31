<?php
//******************************************************************
//*Модуль интеграции WooCommerce и 1С Управление торговлей 10.3/11**
//**********************Версия 2.2.4********************************
//*********************site-with-1c.ru******************************
//**********************Copyright 2024******************************
//*********************schoolbook.com.ua***************************
ob_start();
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//ini_set('html_errors', 'on');
//error_reporting('E_ALL');
@header('X-Accel-Buffering: no');
@ini_set('output_buffering', 'Off'); 
@ini_set('output_handler', ''); 
@ini_set('zlib.output_handler',''); 
@ini_set('zlib.output_compression', 'Off'); 
error_reporting  (E_ALL & ~E_NOTICE);
@ignore_user_abort(true);
@set_time_limit(6000);
//*****************************************************************
//******************************************************************
$lincekey = 'xYIM6xEv';
//******************************************************************
//******************************************************************
//*********************Системые настройки***************************
define( '_JEXEC', 1 );
define ( 'VM_ZIPSIZE', 1073741824 ); // Максимальный размер отправляемого архива в байтах (по умолчанию 1 гб) 
if(!defined('DS'))
{
   define('DS',DIRECTORY_SEPARATOR);
}

if(isset($_GET['statusload'])){
	echo "progress"."\n";
	exit();
}

$directory = search_dir();
define ( 'JPATH_BASE', dirname ( __FILE__ ) ); //Путь к директории где установлен движок Wordpress.
$directory_logs = JPATH_BASE.DS.'logs';
if (!file_exists($directory_logs)) {
	mkdir($directory_logs, 0755);
}
define ( 'DIR_LOGS', JPATH_BASE .DS. 'logs' .DS. '' );
define ( 'VM_CATALOG_IMAGE', 'wp-content' . DS .'uploads'. DS . 'image_1c' );//каталог картинок
$directory_images = JPATH_BASE . DS .VM_CATALOG_IMAGE;
if (!file_exists($directory_images)) {
	mkdir($directory_images, 0755);
}
$directory_temp = JPATH_BASE.DS."TEMP" ;
if (!file_exists($directory_temp)) {
	mkdir($directory_temp, 0755);
}
define ( 'JPATH_BASE_PICTURE', $directory_images );
require_once ( JPATH_BASE .DS.'database.php');

// initialize the application WooCommerce
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

if ( file_exists( ABSPATH . 'wp-config.php') ) {

	/** The config file resides in ABSPATH */
	require_once( ABSPATH . 'wp-config.php' );

} elseif ( @file_exists( dirname( ABSPATH ) .DS.'wp-config.php' ) && ! @file_exists( dirname( ABSPATH ).DS.'wp-settings.php' ) ) {

	/** The config file resides one level above ABSPATH but is not part of another installation */
	require_once( dirname( ABSPATH ) .DS.'wp-config.php' );

} else {

	// A config file doesn't exist

	define( 'WPINC', 'wp-includes' );
	require_once( ABSPATH . WPINC .DS.'load.php' );

	// Standardize $_SERVER variables across setups.
	wp_fix_server_vars();

	require_once( ABSPATH . WPINC .DS.'functions.php' );

	$path = wp_guess_url() .DS.'wp-admin'.DS.'setup-config.php';

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
	require_once( ABSPATH . WPINC .DS.'version.php' );

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

$accumulate_trash = ob_clean();
ob_start("fatal_error_handler");
set_error_handler('error_handler');
set_exception_handler('exception_handler');

global $wpdb;
define ( 'DB_PREFIX', $wpdb->prefix );
$TimeBefore = 0;
$FilenameUpload = '';
$posix = Posix::generatePosix();
$ThisPage = str_replace('/', '', str_replace(__DIR__, '',__FILE__));
$ThisPage = str_replace("\\", '', $ThisPage);
$CategoryArray = array ();
$TovarArray = array();
$ShopperGroupsArray = array();
$TovarIdFeatureArray = array();
$StopNameCreateSvoistvaArray = array('Краткое описание');
$StopNameCreateSvoistvaVariationArray = array('Артикул');
ReadSetting();

function search_dir() {
	$dir_file = dirname(__FILE__);
	$dir_dir = dirname ( __DIR__ );
	$directory_public_html_1 = str_replace($dir_dir,"",$dir_file);
	$directory_public_html_2 = str_replace("/","",$directory_public_html_1);
	$directory_public_html_cc = stripcslashes($directory_public_html_2);
	return $directory_public_html_cc;
} 

function error_handler($code, $msg, $file, $line) {
	$allNameError = "Произошла ошибка $msg ($code)\n $file ($line)";
	write_log($allNameError);
	return;
}

function exception_handler($exception) {
    $trace = $exception->getTrace();
	$msg = $exception->getMessage();
	$file = $trace[0]['file'];
	$line = $trace[0]['line'];
	$allNameError = "Произошла ошибка $msg \n $file ($line)";
	write_log($allNameError);
	return;
}

function fatal_error_handler($buffer) {
  if (preg_match("|(Fatal error</b>:)(.+)(<br)|", $buffer, $regs) ) {
  	 write_log($buffer);
  }
  return $buffer;
}

register_shutdown_function('shutdown');
function shutdown() {

	$connection_status = 'UNKNOWN';
	$connection_status_id = connection_status();
	switch ((string)$connection_status_id) {
		case '0':
			$connection_status = 'NORMAL';
		break;
		case '1':
			$connection_status = 'ABORTED';
		break;
		case '2':
			$connection_status = 'TIMEOUT';
		break;
		case '3':
			$connection_status = 'ABORTED and TIMEOUT';
		break;
		case 0:
			$connection_status = 'NORMAL';
		break;
		case 1:
			$connection_status = 'ABORTED';
		break;
		case 2:
			$connection_status = 'TIMEOUT';
		break;
		case 3:
			$connection_status = 'ABORTED and TIMEOUT';
		break;
	}
	
	if (($connection_status_id <> 0) or ($connection_status_id <> '0')){
		global $posix;
		write_log('Процесс('.$posix.') был прерван. connection_status = '.$connection_status);
		global $wpdb;
		$status_query  = $wpdb->get_results ( "SELECT * FROM " . DB_PREFIX . "status_exchange_1c"); 
		if (count($status_query)>0) {
			foreach ($status_query as $status_exchange){		
				if ((isset($status_exchange->status)) and ($status_exchange->status == 'progress')){
					$filename = $status_exchange['filename'];
					$connection_status = connection_status();
					saveStatusProgress ($filename, 'stop', 'ERROR! connection_status ='.$connection_status);
					write_log("Процесс(".$posix."). Завершение чтения файла ".$filename.". connection_status =".$connection_status);		
				}
			}
		}
	}
}

$domain = parseDM($_SERVER['SERVER_NAME']); //schoolbook.com.ua change HTTP_HOST
$print_key = md5($domain.$lincekey);
$full_url_site = getFullUrlSite();

function ReadSetting() {	
global $wpdb;
global $StopNameCreateSvoistvaArray;
global $StopNameCreateSvoistvaVariationArray;
	$setting_params = $wpdb->get_results("SELECT `name_setting`, `value` FROM `" . DB_PREFIX . "setting_exchange_1c`");
	if(count($setting_params)>0){	
		foreach ($setting_params as $setting) {
			$name_setting = $setting->name_setting;
			$value = $setting->value;
			define ( $name_setting, $value );
		}
	}else{
		//*********************Системые настройки***************************
		define ( 'VM_CODING', 'UTF-8' ); //Для использования другой кодировки в XML файлах используйте значение "UTF-8" или значение "Default" - кодировка по умолчанию
		define ( 'UT_10_3', 0 ); //Модуль интеграции используется для 1С Управление торговлей ред. 10.3 (отличная обработка свойств номенклатуры, отключение удаления картинок при выгрузке только изменений)
		define ( 'BUH_3', 0 ); //Модуль интеграции используется для 1С Бухгалтерия предприятия 3.х (дополнительные параметры выгрузки НДС по товару)
		define ( 'UNF_1_6_15', 0 ); //Модуль интеграции используется для 1С УНФ 1.6.15.х Решение проблемы с загрузкой онлайн оплат
		define ( 'STOP_PROGRESS', 0); //Отключить статус ожидания загрузки progress: 1 - вкл , 0 - выкл
		define ( 'VM_PASSWORD', 1); //Вкл или Выкл проверку пароля при авторизации 1С: 1 - вкл , 0 - выкл
		define ( 'USE_HEARBEAT', 1); 
		define ( 'VM_TIME_LIMIT', 30);
		define ( 'QUANTITY_DOSE', 100);// Количество в порции: используется при значенит PARTS_EXCHANGE = 1, по умолчанию 100
		//******************************************************************
		//****************Настройки загрузки цен номенклатуры***************
		define ( 'VM_PRICE_1C', 0 ); //Использовать цены по соглашению в 1С/или устанавливать цену для всех пользователей сайта по умолчанию: 1 - вкл цен по соглашению, 0 - выкл (используем "для всех")
		define ( 'VM_TYPE_PRICE_1C', 'Розничная' );// Тип цен номенклатуры для отображение цены с видом "Default" в opencart, используется для 1С Розница с обновлением только цен и остатков, и при значение VM_PRICE_1C = 1
		define ( 'VM_NDS', 0 ); //Учитывать НДС в цене товара в заказе с сайта
		//******************************************************************
		//*******************Настройки загрузки реквизитов******************
		define ( 'VM_CREATE_PRODUCT', 1 ); //Создавать товары по данным 1С
		define ( 'IMAGE_LOAD', 1 ); //Режим загрузки изображений товаров на сайт из 1С: 1 - вкл , 0 - выкл. По умолчанию загрузка картинок включена.
		define ( 'VM_SVOISTVA_1C', 1 ); //Использовать и отображать доп. реквизиты номенклатуры из 1С: 1 - вкл , 0 - выкл
		define ( 'VM_MANUFACTURER_1C', 1 ); //Загрузка производителя номенклатуры будет загружаться через свойство "Производитель": 1 - вкл , 0 - выкл (производитель будет загружаться из реквизита "Производитель" указанный в карточке Номенклатуры )
		define ( 'VM_CURRENCY', 1 ); //Выгружать валюту для заказа с сайта: 1 - вкл , 0 - выкл
		define ( 'VM_MANUFACTURER_DESCRIPTION', 1 );  //Вкл или выкл загрузка описания производителя: 1 - вкл , 0 - выкл 
		define ( 'VM_FOLDER', 1 );//Загружать группы номенклатуры
		define ( 'VM_HIERARCHY_FOLDER', 1 );//Загружать иерархию групп номенклатуры
		define ( 'VM_PRODUCT_LOAD_IN_PARENTCATEGORY', 0 );//Отображать товары в родительских категориях
		define ( 'VM_PRODUCT_VIEW_PRICE0', 1 );//Отображать товары на сайте у которых цена = 0 либо еще не загружена
		define ( 'VM_PRODUCT_VIEW_COUNT0', 1 );//Отображать товары на сайте у которых остаток = 0 либо еще не загружен
		//******************************************************************
		//***************Обновление данных на сайте*************************
		define ( 'VM_UPDATE_CATEGORY', 1 ); //Обновлять категории у существующих товаров на сайте: 1 - вкл. обновление, 0 - выкл
		define ( 'VM_UPDATE_META', 1 ); //Обновлять мета-информацию у существующих товаров на сайте: 1 - вкл. обновление, 0 - выкл
		define ( 'VM_UPDATE_DESC', 1 ); //Обновлять описание у существующих товаров на сайте: 1 - вкл. обновление, 0 - выкл
		define ( 'VM_UPDATE_MANUFACTURE', 1 );//Обновлять производителя у товара
		define ( 'VM_UPDATE_IMAGE', 1 );//Обновлять картинки у товара
		define ( 'VM_UPDATE_ARTIKUL', 1 );//Обновлять артикул у товара
		define ( 'VM_UPDATE_SVOISTVA', 1 );// Обновлять свойства у товара
		define ( 'VM_UPDATE_NAME', 1 ); //Обновлять наименование у товаров
		define ( 'VM_UPDATE_COUNT', 1 ); //Обновлять количество у товаров
		define ( 'VM_UPDATE_PRICE', 1 ); //Обновлять цену у товаров

		//******************************************************************
		//***********Настройки обмена характеристик номенклатуры************
		define ( 'VM_FEATURES_1C', 1 ); //Использовать характерстики номенклатуры 1С (справочник "Характеристики номенклатуры") на сайте: 1 - вкл , 0 - выкл
		define ( 'VM_NAME_FEATURES', 'Характеристики товара' ); //Наименование группы атрибутов характеристик номенклатуры
		define ( 'VM_NAME_OPTION', 'Характеристика товара' ); //Наименование группы опций товара. Если значение пустое, то модуль будет искать и подставлять названия свойств справочника "Характеристики номенклатуры"
		define ( 'VM_FEATURES_1C_PRICE', 0 ); //При значении "1" цены характеристик номенклатуры НЕ вычитаются из основной цены товара, подставляются в опции товара как есть из 1С. По умолчанию "0" - цены вычисляются.
		define ( 'VM_PRICE_PARENT_FEATURES', 1 ); //Подставлять минимальную цену характерстики номенклатуры для родителя (основного) товара  1С: 1 - вкл , 0 - выкл. Если 0 - цена будет подставляться та, которая установлена в 1С
		define ( 'VM_COUNT_PARENT_FEATURES', 1 );//Вычислять количество товара суммируя количество в опциях товара: 1 - вкл, 0 - выкл
		//******************************************************************
		//*********Настройки обмена заказами*******************
		define ( 'VM_UPDATE_STATUS_ORDER', 1 );//Обновлять статус заказа на сайте по данным из 1С
		define ( 'VM_ORDER_STATUS_PROCESSING', '' );//Установить статус заказа после выгрузки в 1С
	}
	if (defined('VM_PROPERTY_STOP_LIST')){
		if ((VM_PROPERTY_STOP_LIST <> "") or (VM_PROPERTY_STOP_LIST <> 1) or (VM_PROPERTY_STOP_LIST <> 0)){
			$StopNameCreateSvoistvaArray = array();
			$std_setting = json_decode(VM_PROPERTY_STOP_LIST, false);
			foreach($std_setting as $setting){
				$id_property = $setting->input_id;
				$name_property = $setting->input_value;		
				$StopNameCreateSvoistvaArray[$id_property] = $name_property;
			}
		}	
	}
	if (defined('VM_PROPERTY_VARIATION_STOP_LIST')){
		if ((VM_PROPERTY_VARIATION_STOP_LIST <> "") or (VM_PROPERTY_VARIATION_STOP_LIST <> 1) or (VM_PROPERTY_VARIATION_STOP_LIST <> 0)){
			$StopNameCreateSvoistvaVariationArray = array();
			$std_setting = json_decode(VM_PROPERTY_VARIATION_STOP_LIST, false);
			foreach($std_setting as $setting){
				$id_property = $setting->input_id;
				$name_property = $setting->input_value;		
				$StopNameCreateSvoistvaVariationArray[$id_property] = $name_property;
			}
		}	
	}

	$stock_status = VM_STOCK_STATUS;
	$backorders = 'no';
	if ($stock_status == 'onbackorder_yes'){
		$stock_status = 'onbackorder';			
		$backorders = 'yes';
	}
	if ($stock_status == 'onbackorder_notify'){
		$stock_status = 'onbackorder';			
		$backorders = 'notify';
	}
	define ( 'VM_STOCK_STATUS_VALUE', $stock_status ); 
	define ( 'VM_BACKORDERS_VALUE', $backorders ); 
}

function authorization($username, $password) {
global $registry;
global $print_key;

		require_once(JPATH_BASE.DS.'wp-includes'.DS.'class-phpass.php');
		$check = '';
		$user_data = get_user_by('login', $username);
		if (empty($user_data)){
			$user_data = get_user_by('email', $username);
		}
		if (!empty($user_data)){
			$user_login = $user_data->user_login;
			$user_email = $user_data->user_email;
			$user_pass = $user_data->user_pass;
			
			$wp_hasher = new PasswordHash(8, TRUE);
			$check = $wp_hasher->CheckPassword($password, $user_pass);
		}
		if (!empty($check)){
			return connecting($print_key);
		} else {			
			return 'false user name or password';			
		}		
}

function CheckAuthUser() {
global $print_key;
	
	$remote_user = '';
	if (isset($_SERVER['REMOTE_USER'])){
		$remote_user = $_SERVER['REMOTE_USER'];	
	}else{
		if (isset($_SERVER['REDIRECT_REMOTE_USER'])){
			$remote_user = $_SERVER['REDIRECT_REMOTE_USER'];	
		}	
	}
	$strTmp = base64_decode(substr($remote_user,6));
	$findmas = strpos($strTmp, ':');
	if ($findmas !== false) {
		list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', $strTmp);
	}

	$sessid_info = "\n"."key" . "\n".$print_key."\n" . "sessid=".$print_key."";
	if (VM_PASSWORD == 1){
		if ((isset($_SERVER['PHP_AUTH_USER'])) and (isset($_SERVER['PHP_AUTH_PW']))) {
			$username	=	($_SERVER['PHP_AUTH_USER']);
			$password	=	($_SERVER['PHP_AUTH_PW']);
			//выполняем авторизацию
			print authorization($username, $password);								
			print $sessid_info;
		}else {			
			print 'false user name or password';			
		}
	}else{
		print connecting($print_key);	
		print $sessid_info;
	}
}

function CheckAccess(){
global $print_key;

	if (USE_PROTECTION == 1){
		if (!isset($_REQUEST ['sessid'])) {
			print 'failure'."\n"."access is denied";
			exit();
		}
		if ((isset($_REQUEST ['sessid'])) and ($_REQUEST ['sessid'] <> $print_key)) {
			print 'failure'."\n"."access is denied";
			exit();
		}
	}
}

function LoadFile($filename_a) {
	if (isset ( $filename_a )) {
		/*
		//код для загрузки данных из OC Linux
		$PosDS = strpos (  $filename_a,  '/' );
		$lunex = 0;
		if ($PosDS === false) {
		} else {
			if ($PosDS == 0){
				$filename_a = substr($filename_a, 1);
				$lunex = 1;
			}
		}
		
		$PosDS = strpos (  $filename_a,  '/' );	
		if ($PosDS > 0){
		$parts   = explode( '/' , $filename_a );
			if ($lunex == 0){
				$filename_a   = $parts[2];
			}else{
				$filename_a   = $parts[4];
			}
		}*/
		//--код для загрузки данных из OC Linux
		
		$filename_a = getFileFromPath($filename_a);			
		$isXML = false;
		if (isset($filename_a)){
			$findXml = strpos($filename_a, '.xml');
			if ($findXml === false) {
				$isXML = false;
			} else {
				$isXML = true;
				saveStatusProgress ($filename_a, 'start', 'new load file ='.$filename_a.'');
				HeartBeat::clearElementUploadAll($filename_a);
			}
		}			
		$filename_to_save = JPATH_BASE . DS .'TEMP'. DS . $filename_a;	
		if (function_exists('file_get_contents')) {
            $data = file_get_contents('php://input');
        } elseif (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $data = $GLOBALS['HTTP_RAW_POST_DATA'];
        } else {
            $data = false;
        }
		if ($data !== false) {				
			if ($png_file = fopen ( $filename_to_save, "wb" )) {
				$result = fwrite ( $png_file, $data );
					if (isset($result)) {
						fclose ( $png_file );
						chmod($filename_to_save , 0777);			
						$isZip = false;
						$file_explode = explode('.', $filename_a);
						$extension = end($file_explode);
						if (($extension == 'zip') or ($extension == 'rar')){
							$isZip = true;
							$zip = new ZipArchive;
							if ($zip->open($filename_to_save) === TRUE) {
								$zip->extractTo(JPATH_BASE . DS .'TEMP');
								$zip->close();
								unlink($filename_to_save);
							}
						}	
						if((STOP_PROGRESS == 1) and ($isXML == false) and ($isZip == false)){
							$copy_result = copyFileToImageFolder($filename_a, 'Неопределено');
						}
						unset($data, $filename_to_save, $png_file, $result, $filename_a);
						return "success";
					}else{
						write_log("Не удалось прочитать файл:".$filename_a);
						return "error upload FILE";
					}			
			}else{
				write_log("Ошибка открытия файла:".$filename_a);
				return "error upload FILE";
			}			
		}else{
			write_log ("Не удалось получить файл:".$filename_a);
			return "error upload FILE";
		}
	}	
	write_log ("Ошибка загрузки файла");
	return "error POST";	
}

function LoadFileZakaz() {
global $wpdb;
	
	$filename_a =  $_REQUEST ['filename'];
	unset($_REQUEST ['filename']);
	$filename_a = getFileFromPath($filename_a);	
	
	$use_bitrix = false;
	$PosDS = strpos (  $filename_a,  "documents" );
	if ($PosDS !== false){
		$use_bitrix = true;
	}
	
	$filename_to_save = JPATH_BASE . DS .'TEMP'. DS . $filename_a;
	if (function_exists('file_get_contents')) {
        $data = file_get_contents('php://input');
    } elseif (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
        $data = $GLOBALS['HTTP_RAW_POST_DATA'];
    } else {
        $data = false;
    }
	if ($data !== false) { 
		if ($png_file = fopen ( $filename_to_save, "wb" )) {
			$result = fwrite ( $png_file, $data );
			usleep(1000);
			if ($result === strlen($data)) {
				fclose ( $png_file );
				chmod($filename_to_save , 0777);
				unset($result, $data);
				$file_zakaz =  simplexml_load_file ($filename_to_save);
				
				if (isset($file_zakaz->Документ)){
					$document = $file_zakaz->Документ;
					$use_bitrix = false;
				}
				if (isset($file_zakaz->Контейнер)){
					$document = $file_zakaz->Контейнер;
					$use_bitrix = true;
				}
				if (isset($document)){
					if ($use_bitrix == true){
						foreach ($document as $container){
							foreach ($container as $zakaz_1c){
								if ( version_compare( WC_VERSION, '8.0.0', ">=" ) ) {
									LoadZakazWC8_0_0($zakaz_1c);
								}else{
									LoadZakaz($zakaz_1c);
								}
							}
						}
					}else{
						foreach ($document as $zakaz_1c){
							if ( version_compare( WC_VERSION, '8.0.0', ">=" ) ) {
								LoadZakazWC8_0_0($zakaz_1c);
							}else{								
								LoadZakaz($zakaz_1c); 
							}
						}
					}
				}else{
					write_log('В файле '.$filename_to_save.' отсутствуют документы для обработки');
				}
				if (VM_DELETE_TEMP == 1){
					clear_files_temp($filename_to_save);	
				}
			}	
		}else{
			write_log ('error upload FILE='.$filename_a);
			return "error upload FILE";
		}
	}
	return "success";
}

function LoadZakaz($zakaz_1c){
global $wpdb;
	
	if (empty($zakaz_1c)){
		return;
	}
	
	$nomer = (isset($zakaz_1c->Номер)) ? (string)$zakaz_1c->Номер : '';
	$date  = (isset($zakaz_1c->Дата)) ? (string)$zakaz_1c->Дата : '';
	$order_postmeta_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "postmeta WHERE meta_value = '" . $nomer . "' AND meta_key = '_alg_wc_custom_order_number'" );
	if (count($order_postmeta_query)>0){
		foreach($order_postmeta_query as $order_postmeta){
			$nomer = (int)$order_postmeta->post_id;	
		}	
	}
	$order_array  = $wpdb->get_results( "SELECT ID FROM " . DB_PREFIX . "posts WHERE ID = '" . (int)$nomer . "' AND post_type = 'shop_order'" );
	if (count($order_array)>0){
		if (isset($zakaz_1c->ЗначенияРеквизитов->ЗначениеРеквизита)){
			foreach ($zakaz_1c->ЗначенияРеквизитов->ЗначениеРеквизита as $zakaz_data){
				$name_param = (isset($zakaz_data->Наименование)) ? (string)$zakaz_data->Наименование : '';
				$value_param = (isset($zakaz_data->Значение)) ? (string)$zakaz_data->Значение : '';
				if ($name_param == "Дата оплаты по 1С"){
					$oplata_date = $value_param;
				}
				if (($name_param == "Оплачен") and ($value_param == "true")){
					$oplata_date = $date;
				}
				if ($name_param == "Номер отгрузки по 1С"){
					$nomer_real = $value_param;
				}
				if ($name_param == "Дата отгрузки по 1С" and $value_param <> 'T'){
					$date_real = $value_param;
				}
				if (($name_param == "Отгружен") and ($value_param == "true")){
					$nomer_real = $nomer;
					$date_real  = $date;
				}
				if ($name_param == "ПометкаУдаления"){
					$delete_order = $value_param;
				}
			}
		}
		//отражаем статус оплаты
		if (isset ( $oplata_date)){	
			$order_status_oplacheno = VM_ORDER_STATUS_PAID;
			if ($order_status_oplacheno <> ''){						
				$update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "posts  SET post_status='" . $order_status_oplacheno . "' where ID = '" . (int)$nomer . "' AND post_type = 'shop_order'" );
			}
		}
								
		//отражаем статус отгрузки			
		if (isset ( $nomer_real,$date_real)) {
			$order_status_dostavleno = VM_ORDER_STATUS_COMPLETE;
			if ($order_status_dostavleno <> ''){
				$update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "posts  SET post_status='" . $order_status_dostavleno . "' where ID = '" . (int)$nomer . "' AND post_type = 'shop_order'" );
			}	
		}

		//отражаем статус отмены заказа
		if (isset ($delete_order)){
			if  (($delete_order == 'true') or ($delete_order == 'Истина')){
				$order_status_otmeneno = OrderStatusReturn ('wc-cancelled');							
				$update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "posts  SET post_status='" . $order_status_otmeneno . "' where ID = '" . (int)$nomer . "' AND post_type = 'shop_order'" );	
			}
		}
		$oplata_date = NULL;
		$nomer_real	= NULL;
		$date_real = NULL;
		$delete_order = NULL;

		if (VM_UPDATE_ORDERDATA1C == 1){
			$wpdb->query("DELETE FROM " . DB_PREFIX . "woocommerce_order_itemmeta WHERE order_item_id IN (SELECT order_item_id FROM " . DB_PREFIX . "woocommerce_order_items WHERE order_id = '".(int)$nomer."' AND order_item_type <> 'shipping')");
			$wpdb->query("DELETE FROM " . DB_PREFIX . "woocommerce_order_items WHERE order_id = '".(int)$nomer."' AND order_item_type <> 'shipping'");
									
			$customer_id  = 0;
			$shipping_amount = 0;
			$coupon_amount = 0;
			$date_created = date('Y-m-d H:i:s');
			$order_product_lookup_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX ."wc_order_product_lookup WHERE order_id = '" . (int)$nomer . "'" );
			if (count($order_product_lookup_query)>0){
				foreach($order_product_lookup_query as $order_product_lookup){
					$date_created = $order_product_lookup->date_created;
					$customer_id  = $order_product_lookup->customer_id;	
					$shipping_amount = $order_product_lookup->shipping_amount;	
					$coupon_amount = $order_product_lookup->coupon_amount;					
				}		
			}							
			$wpdb->query("DELETE FROM " . DB_PREFIX . "wc_order_product_lookup WHERE order_id = '".(int)$nomer."'");
								
			if (isset($zakaz_1c->Товары->Товар)){
				$OrderTotal1c = (isset($zakaz_1c->Сумма)) ? (float)$zakaz_1c->Сумма : '';
				$sub_total = 0;	
				foreach ($zakaz_1c->Товары->Товар as $product_data){
					$IdTovar1c = (isset($product_data->Ид)) ? (string)$product_data->Ид : '';
					$Artikul = (isset($product_data->Артикул)) ? (string)$product_data->Артикул : '';
					$Artikul = !empty($Artikul) ? $Artikul : 'Не указано';
					$Name = (isset($product_data->Наименование)) ? (string)$product_data->Наименование : 'Наименование не заполнено';
					$Price = (isset($product_data->ЦенаЗаЕдиницу)) ? (string)$product_data->ЦенаЗаЕдиницу : 0;
					$Price = (float)preg_replace("/[^0-9\.]/", '', str_replace(",",".",$Price)); //замена запятых на точку
					$Quantity = (isset($product_data->Количество)) ? (string)$product_data->Количество : 0;
					$Quantity = (float)preg_replace("/[^0-9\.]/", '', str_replace(",",".",$Quantity)); //замена запятых на точку
					$Summ = (isset($product_data->Сумма)) ? (string)$product_data->Сумма : 0;
					$Summ = (float)preg_replace("/[^0-9\.]/", '', str_replace(",",".",$Summ)); //замена запятых на точку
					$tax_amount = 0;
					$vat_rate = 0;
					if (isset($product_data->Налоги->Налог)){
						$tax_amount = 0;
						foreach ($product_data->Налоги->Налог as $tax_data){
							$tax = (isset($tax_data->Сумма)) ? (string)$tax_data->Сумма : 0;
							$tax = (float)preg_replace("/[^0-9\.]/", '', str_replace(",",".",$tax)); //замена запятых на точку
							$tax_amount = $tax_amount + $tax;
							$vat_rate = (isset($tax_data->Ставка)) ? (float)$tax_data->Ставка : 0;
						}
					}
										
					$product_info = array(
						'order_id'        => $nomer,
						'order_item_type' => '',
						'IdTovar1c'       => $IdTovar1c,
						'Artikul'         => $Artikul,
						'Name'            => $Name,
						'Price'           => $Price,
						'Quantity'        => $Quantity,
						'Summ'            => $Summ,
						'date_created'    => $date_created,
						'customer_id'     => $customer_id,
						'shipping_amount' => $shipping_amount,
						'coupon_amount'   => $coupon_amount,
						'tax_amount'      => $tax_amount,
						'vat_rate'        => $vat_rate
					);
															
					$typeProduct = 'Товар';
					if (isset($product_data->ЗначенияРеквизитов->ЗначениеРеквизита)){
						foreach ($product_data->ЗначенияРеквизитов->ЗначениеРеквизита as $property_value_data){
							$name_property = (isset($property_value_data->Наименование)) ? (string)$property_value_data->Наименование : '';
							$value_property = (isset($property_value_data->Значение)) ? (string)$property_value_data->Значение : '';
							if ($name_property == "ТипНоменклатуры"){
								if ($value_property == 'Услуга'){
									$typeProduct = 'Услуга';
								}
							}
						}
					}
					if ($typeProduct == 'Товар'){
						$product_info['order_item_type'] = 'line_item';
						insertOrderProduct($product_info);
					}
					if ($typeProduct == 'Услуга'){
						$product_info['order_item_type'] = 'shipping';
						$words_array = array('достав', 'посылк', 'бандерол', 'курьер');
						$isProduct = true;
						foreach($words_array as $word){
							$pos = strrpos($Name, $word);
							if (!$pos === false) { 
								$isProduct = false;
							}
						}
						if ($isProduct == true){
							//insertOrderProduct($product_info); //если добавлять услуги как товары, то в 1С будут дубли услуг
						}else{
							//insertOrderProduct($product_info); //отключено обновление услуг
						}
					}									
				}
				$date_modified = date('Y-m-d H:i:s');
				$wpdb->query("UPDATE " . DB_PREFIX . "posts SET post_modified ='" . $date_modified . "', post_modified_gmt ='" . $date_modified . "' WHERE ID = '".(int)$nomer."'");
				$order_subtotal_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "woocommerce_order_itemmeta WHERE order_item_id IN (SELECT order_item_id FROM " . DB_PREFIX . "woocommerce_order_items WHERE order_id = '".(int)$nomer."') AND meta_key = '_line_total'");
				if (count($order_subtotal_query)>0){
					foreach($order_subtotal_query as $order_subtotal_item){
						$line_total = $order_subtotal_item->meta_value;
						$sub_total  = $sub_total + $line_total;
					}
				}
				$total_sales = $sub_total;
				$wpdb->query("UPDATE " . DB_PREFIX . "wc_order_stats SET total_sales ='" . $total_sales . "', net_total ='" . $sub_total . "' WHERE order_id = '".(int)$nomer."'");
				$wpdb->query("UPDATE " . DB_PREFIX . "postmeta SET meta_value ='" . $total_sales . "' WHERE meta_key ='_order_total' AND post_id = '".(int)$nomer."'");
			}
		}
	}else{
		write_log('Заказ '.$nomer.' не найден на сайте');
	}	
}

function LoadZakazWC8_0_0($zakaz_1c){
global $wpdb;
	
	if (empty($zakaz_1c)){
		return;
	}
	
	$nomer = (isset($zakaz_1c->Номер)) ? (string)$zakaz_1c->Номер : '';
	$date  = (isset($zakaz_1c->Дата)) ? (string)$zakaz_1c->Дата : '';
	$date_modified = date('Y-m-d H:i:s');
	$order_array  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "wc_orders WHERE id = '" . $nomer . "'" );
	if (count($order_array)>0){
		if (isset($zakaz_1c->ЗначенияРеквизитов->ЗначениеРеквизита)){
			foreach ($zakaz_1c->ЗначенияРеквизитов->ЗначениеРеквизита as $zakaz_data){
				$name_param = (isset($zakaz_data->Наименование)) ? (string)$zakaz_data->Наименование : '';
				$value_param = (isset($zakaz_data->Значение)) ? (string)$zakaz_data->Значение : '';
				if ($name_param == "Дата оплаты по 1С"){
					$oplata_date = $value_param;
				}
				if (($name_param == "Оплачен") and ($value_param == "true")){
					$oplata_date = $date;
				}
				if ($name_param == "Номер отгрузки по 1С"){
					$nomer_real = $value_param;
				}
				if ($name_param == "Дата отгрузки по 1С" and $value_param <> 'T'){
					$date_real = $value_param;
				}
				if (($name_param == "Отгружен") and ($value_param == "true")){
					$nomer_real = $nomer;
					$date_real  = $date;
				}
				if ($name_param == "ПометкаУдаления"){
					$delete_order = $value_param;
				}
			}
		}
		//отражаем статус оплаты
		if (isset ( $oplata_date)){	
			$order_status_oplacheno = VM_ORDER_STATUS_PAID;
			if ($order_status_oplacheno <> ''){						
				$update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "posts  SET post_modified='" . $date_modified . "', post_modified_gmt='" . $date_modified . "' where ID = '" . (int)$nomer . "' AND post_type = 'shop_order_placehold'" );
				$update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "wc_orders  SET status='" . $order_status_oplacheno . "', date_updated_gmt='". $date_modified ."' where id = '" . (int)$nomer . "'" );
			}
		}
								
		//отражаем статус отгрузки			
		if (isset ( $nomer_real,$date_real)) {
			$order_status_dostavleno = VM_ORDER_STATUS_COMPLETE;
			if ($order_status_dostavleno <> ''){
				$update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "posts  SET post_modified='" . $date_modified . "', post_modified_gmt='" . $date_modified . "' where ID = '" . (int)$nomer . "' AND post_type = 'shop_order_placehold'" );
				$update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "wc_orders  SET status='" . $order_status_dostavleno . "', date_updated_gmt='". $date_modified ."' where id = '" . (int)$nomer . "'" );
			}
		}

		//отражаем статус отмены заказа
		if (isset ($delete_order)){
			if  (($delete_order == 'true') or ($delete_order == 'Истина')){
				$order_status_otmeneno = OrderStatusReturn ('wc-cancelled');							
				$update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "posts  SET post_modified='" . $date_modified . "', post_modified_gmt='" . $date_modified . "' where ID = '" . (int)$nomer . "' AND post_type = 'shop_order_placehold'" );
				$update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "wc_orders  SET status='" . $order_status_otmeneno . "', date_updated_gmt='". $date_modified ."' where id = '" . (int)$nomer . "'" );
			}
		}
		$oplata_date = NULL;
		$nomer_real	= NULL;
		$date_real = NULL;
		$delete_order = NULL;

		if (VM_UPDATE_ORDERDATA1C == 1){
			$wpdb->query("DELETE FROM " . DB_PREFIX . "woocommerce_order_itemmeta WHERE order_item_id IN (SELECT order_item_id FROM " . DB_PREFIX . "woocommerce_order_items WHERE order_id = '".(int)$nomer."' AND order_item_type <> 'shipping')");
			$wpdb->query("DELETE FROM " . DB_PREFIX . "woocommerce_order_items WHERE order_id = '".(int)$nomer."' AND order_item_type <> 'shipping'");
									
			$customer_id  = 0;
			$shipping_amount = 0;
			$coupon_amount = 0;
			$date_created = date('Y-m-d H:i:s');
			$order_product_lookup_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX ."wc_order_product_lookup WHERE order_id = '" . (int)$nomer . "'" );
			if (count($order_product_lookup_query)>0){
				foreach($order_product_lookup_query as $order_product_lookup){
					$date_created = $order_product_lookup->date_created;
					$customer_id  = $order_product_lookup->customer_id;	
					$shipping_amount = $order_product_lookup->shipping_amount;	
					$coupon_amount = $order_product_lookup->coupon_amount;					
				}		
			}							
			$wpdb->query("DELETE FROM " . DB_PREFIX . "wc_order_product_lookup WHERE order_id = '".(int)$nomer."'");
								
			if (isset($zakaz_1c->Товары->Товар)){
				$OrderTotal1c = (isset($zakaz_1c->Сумма)) ? (float)$zakaz_1c->Сумма : '';
				$sub_total = 0;	
				foreach ($zakaz_1c->Товары->Товар as $product_data){
					$IdTovar1c = (isset($product_data->Ид)) ? (string)$product_data->Ид : '';
					$Artikul = (isset($product_data->Артикул)) ? (string)$product_data->Артикул : '';
					$Artikul = !empty($Artikul) ? $Artikul : 'Не указано';
					$Name = (isset($product_data->Наименование)) ? (string)$product_data->Наименование : 'Наименование не заполнено';
					$Price = (isset($product_data->ЦенаЗаЕдиницу)) ? (string)$product_data->ЦенаЗаЕдиницу : 0;
					$Price = (float)preg_replace("/[^0-9\.]/", '', str_replace(",",".",$Price)); //замена запятых на точку
					$Quantity = (isset($product_data->Количество)) ? (string)$product_data->Количество : 0;
					$Quantity = (float)preg_replace("/[^0-9\.]/", '', str_replace(",",".",$Quantity)); //замена запятых на точку
					$Summ = (isset($product_data->Сумма)) ? (string)$product_data->Сумма : 0;
					$Summ = (float)preg_replace("/[^0-9\.]/", '', str_replace(",",".",$Summ)); //замена запятых на точку
					$tax_amount = 0;
					$vat_rate = 0;
					if (isset($product_data->Налоги->Налог)){
						$tax_amount = 0;
						foreach ($product_data->Налоги->Налог as $tax_data){
							$tax = (isset($tax_data->Сумма)) ? (string)$tax_data->Сумма : 0;
							$tax = (float)preg_replace("/[^0-9\.]/", '', str_replace(",",".",$tax)); //замена запятых на точку
							$tax_amount = $tax_amount + $tax;
							$vat_rate = (isset($tax_data->Ставка)) ? (float)$tax_data->Ставка : 0;
						}
					}
										
					$product_info = array(
						'order_id'        => $nomer,
						'order_item_type' => '',
						'IdTovar1c'       => $IdTovar1c,
						'Artikul'         => $Artikul,
						'Name'            => $Name,
						'Price'           => $Price,
						'Quantity'        => $Quantity,
						'Summ'            => $Summ,
						'date_created'    => $date_created,
						'customer_id'     => $customer_id,
						'shipping_amount' => $shipping_amount,
						'coupon_amount'   => $coupon_amount,
						'tax_amount'      => $tax_amount,
						'vat_rate'        => $vat_rate
					);
															
					$typeProduct = 'Товар';
					if (isset($product_data->ЗначенияРеквизитов->ЗначениеРеквизита)){
						foreach ($product_data->ЗначенияРеквизитов->ЗначениеРеквизита as $property_value_data){
							$name_property = (isset($property_value_data->Наименование)) ? (string)$property_value_data->Наименование : '';
							$value_property = (isset($property_value_data->Значение)) ? (string)$property_value_data->Значение : '';
							if ($name_property == "ТипНоменклатуры"){
								if ($value_property == 'Услуга'){
									$typeProduct = 'Услуга';
								}
							}
						}
					}
					if ($typeProduct == 'Товар'){
						$product_info['order_item_type'] = 'line_item';
						insertOrderProduct($product_info);
					}
					if ($typeProduct == 'Услуга'){
						$product_info['order_item_type'] = 'shipping';
						$words_array = array('достав', 'посылк', 'бандерол', 'курьер');
						$isProduct = true;
						foreach($words_array as $word){
							$pos = strrpos($Name, $word);
							if (!$pos === false) { 
								$isProduct = false;
							}
						}
						if ($isProduct == true){
							//insertOrderProduct($product_info); //если добавлять услуги как товары, то в 1С будут дубли услуг
						}else{
							//insertOrderProduct($product_info); //отключено обновление услуг
						}
					}									
				}
				$wpdb->query("UPDATE " . DB_PREFIX . "posts SET post_modified ='" . $date_modified . "', post_modified_gmt ='" . $date_modified . "' WHERE ID = '".(int)$nomer."'");
				$order_subtotal_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "woocommerce_order_itemmeta WHERE order_item_id IN (SELECT order_item_id FROM " . DB_PREFIX . "woocommerce_order_items WHERE order_id = '".(int)$nomer."') AND meta_key = '_line_total'");
				if (count($order_subtotal_query)>0){
					foreach($order_subtotal_query as $order_subtotal_item){
						$line_total = $order_subtotal_item->meta_value;
						$sub_total  = $sub_total + $line_total;
					}
				}
				$total_sales = $sub_total;
				$wpdb->query("UPDATE " . DB_PREFIX . "wc_order_stats SET total_sales ='" . $total_sales . "', net_total ='" . $sub_total . "' WHERE order_id = '".(int)$nomer."'");
				$wpdb->query("UPDATE " . DB_PREFIX . "wc_orders SET total_amount ='" . $total_sales . "' WHERE id = '".(int)$nomer."'");
			}
		}
	}else{
		write_log('Заказ '.$nomer.' не найден на сайте');
	}	
}

function insertOrderProduct($product_data) {
global $wpdb;	
	$IdTovar1c = (isset($product_data['IdTovar1c'])) ? $product_data['IdTovar1c'] : '';	
	$order_id = (isset($product_data['order_id'])) ? $product_data['order_id'] : 0;
	$Name = (isset($product_data['Name'])) ? $product_data['Name'] : '';
	$Artikul = (isset($product_data['Artikul'])) ? $product_data['Artikul'] : '';
	$Quantity = (isset($product_data['Quantity'])) ? $product_data['Quantity'] : 0;
	$Price = (isset($product_data['Price'])) ? $product_data['Price'] : 0;
	$Summ = (isset($product_data['Summ'])) ? $product_data['Summ'] : 0;
	$order_item_type = (isset($product_data['order_item_type'])) ? $product_data['order_item_type'] : '';
	$date_created = (isset($product_data['date_created'])) ? $product_data['date_created'] : date('Y-m-d H:i:s');
	$customer_id = (isset($product_data['customer_id'])) ? $product_data['customer_id'] : 0;
	$shipping_amount = (isset($product_data['shipping_amount'])) ? $product_data['shipping_amount'] : 0;
	$coupon_amount = (isset($product_data['coupon_amount'])) ? $product_data['coupon_amount'] : 0;
	$tax_amount = (isset($product_data['tax_amount'])) ? $product_data['tax_amount'] : 0;
	$vat_rate = (isset($product_data['vat_rate'])) ? $product_data['vat_rate'] : 0;
		
	$product_id = 0;
	$product_id_query  = $wpdb->get_results( "SELECT ID, post_title, post_parent FROM " . DB_PREFIX ."posts where product_1c_id = '" . $IdTovar1c . "'" );
	if (count($product_id_query)>0){
		foreach($product_id_query as $product_data){
			$post_id = (int)$product_data->ID;
			$parent_id = (int)$product_data->post_parent;
		    $Name =	$product_data->post_title;	
			$product_id   = ($parent_id == 0) ? $post_id : $parent_id;
			$variation_id = ($parent_id == 0) ? 0        : $post_id;			
		}		
	}
	if ($product_id == 0) {
		write_log('По данным заказа из 1С №'.$order_id.' не найден товар в базе сайта по ИД = '. $IdTovar1c);
		return;
	}

	$ins = new stdClass ();
	$ins->order_item_id = NULL;
	$ins->order_item_name = substr($Name, 0, 254);
	$ins->order_item_type = $order_item_type;
	$ins->order_id = $order_id;
	insertObject ( "" . DB_PREFIX ."woocommerce_order_items", $ins, 'order_item_id' ) ;
	$order_item_id = ( int )$ins->order_item_id; 
	
	if ($order_item_type == 'line_item') {
		$internal_meta_keys = array(
			'_product_id'  			=> $product_id,
			'_variation_id'  		=> $variation_id,
			'_qty'  			    => $Quantity,
			'_tax_class'   			=> '',
			'_line_subtotal'  	    => $Price,
			'_line_subtotal_tax'    => '0',
			'_line_total'   		=> $Summ,
			'_line_tax'   		    => $tax_amount,
			'_line_tax_data'   		=> 'a:2:{s:5:"total";a:0:{}s:8:"subtotal";a:0:{}}',
			'_reduced_stock'   		=> '1'
		);
				
		$ins = new stdClass ();
		$ins->order_item_id = $order_item_id;
		$ins->order_id = $order_id;
		$ins->product_id = $product_id;
		$ins->variation_id = $variation_id;
		$ins->customer_id = $customer_id;
		$ins->date_created = $date_created; 
		$ins->product_qty = $Quantity;
		$ins->product_net_revenue = $Summ;
		$ins->product_gross_revenue = $Summ + $shipping_amount;
		$ins->coupon_amount = $coupon_amount;
		$ins->tax_amount = 0;
		$ins->shipping_amount = $shipping_amount;
		$ins->shipping_tax_amount = 0;
		insertObject ( "" . DB_PREFIX ."wc_order_product_lookup", $ins) ;
	}
	
	if ($order_item_type == 'shipping') {
		$internal_meta_keys = array(
			'method_id'  			=> 'flat_rate',
			'instance_id'  		    => '1',
			'cost'  			    => $Summ,
			'total_tax'   		    => $SummTax, 
			'taxes'  	            => 'a:1:{s:5:"total";a:0:{}}',
			'Товары'                => ''
		);
	}
	
	$text_query_arr = array();
	foreach ($internal_meta_keys as $key => $value){
		$text_query_arr[] = "(NULL, '".$order_item_id."', '".$key."', '".$value."')";
	}
	if (!empty($text_query_arr)) {
		if (count($text_query_arr)== 1 ){
			$text_query_final = $text_query_arr[0];	
		}else{
			$text_query_final = implode(',', $text_query_arr);
		} 
		$wpdb->query( "INSERT INTO " . DB_PREFIX . "woocommerce_order_itemmeta (meta_id, order_item_id, meta_key, meta_value) VALUES ".$text_query_final."" );
	}
}

function CategoryArrayFill($xml, $CategoryArray, $owner) {	
global $wpdb;
	//рекурсия
	if (!isset($xml->Группы)){
		return $CategoryArray;
	}
	
	foreach ($xml->Группы as $GroupCategory){
		if (isset($GroupCategory->Группа)){
			foreach ($GroupCategory->Группа as $Category){
				$name = (isset($Category->Наименование)) ? (string)$Category->Наименование : 'Наименование не задано';
				$cnt = (isset($Category->Ид)) ? (string)$Category->Ид : 'empty';
				$name = htmlentities($name, ENT_QUOTES, "UTF-8");

				if (isset($CategoryArray[$cnt]['category_id'])){
					continue;
				}

				$CategoryArray [$cnt] ['name'] = $name;
				$CategoryArray [$cnt] ['owner'] = $owner;
							
				$CategoryIdQuery  = $wpdb->get_results( "SELECT tt.parent as parent, tt.term_taxonomy_id as term_taxonomy_id, tt.count as count, t.name as name, t.slug as slug, t.term_id as term_id  FROM " . DB_PREFIX . "terms AS t LEFT OUTER JOIN " . DB_PREFIX ."term_taxonomy AS tt ON t.term_id = tt.term_id WHERE t.category_1c_id = '" . $cnt. "' AND tt.taxonomy = 'product_cat'" );
				if (count($CategoryIdQuery)>0){
					foreach($CategoryIdQuery as $CategoryResult){
						$CategoryId = $CategoryResult->term_id;
					}
					$CategoryArray [$cnt] ['category_id'] = $CategoryId; 
					$Category_id_update = $wpdb->query(  "UPDATE " . DB_PREFIX . "terms SET name='".$name."' where term_id ='".(int)$CategoryId."'");
				}else{
					$CategoryNameQuery  = $wpdb->get_results ( "SELECT tt.parent as parent, tt.term_taxonomy_id as term_taxonomy_id, tt.count as count, t.name as name, t.slug as slug, t.term_id as term_id  FROM " . DB_PREFIX . "terms AS t LEFT OUTER JOIN " . DB_PREFIX ."term_taxonomy AS tt ON t.term_id = tt.term_id WHERE t.name = '" . $name. "' AND tt.taxonomy = 'product_cat' AND (t.category_1c_id = '' or t.category_1c_id IS NULL)" );
					if (count($CategoryNameQuery)>0){
						foreach($CategoryNameQuery as $CategoryResult){
							$CategoryId = $CategoryResult->term_id;
						}
						$CategoryArray [$cnt] ['category_id'] = $CategoryId; 
						$CategoryNameUpdate = $wpdb->query(  "UPDATE " . DB_PREFIX . "terms SET category_1c_id = '".$cnt."' where term_id ='".(int)$CategoryId."'");
					}else{
						$CategoryArray [$cnt] ['category_id'] = NewCategory($name, $cnt);	
					}
				}
				$new_owner = $CategoryArray[$cnt]['category_id'];
				$CategoryArray = CategoryArrayFill( $Category, $CategoryArray, $new_owner);
			}
		}
	}
	return $CategoryArray;
}

function NewCategory($CategoryName, $Category1c_id) {	
global $wpdb;
	
	$category_id = 0;	
	if ((function_exists('wp_insert_term')) and (function_exists('is_wp_error')) and (USE_FUNCTION_WOOCOMMERCE == 1)) {
		$ModelSeourlGenerate = new ModuleSeoUrlGenerator();
		$slug_result = $ModelSeourlGenerate->seoUrlGenerateAjax($CategoryName, DB_PREFIX.'terms', 'slug', true);
			
		$insert_data = wp_insert_term(
			$CategoryName,  // новый термин
			'product_cat', // таксономия
			array(
				'description' => '',
				'slug'        => $slug_result,
				'parent'      => 0
			)
		);
		
		if( ! is_wp_error($insert_data) ){
			$category_id = $insert_data['term_id'];
			$Category_id_update = $wpdb->query(  "UPDATE " . DB_PREFIX . "terms SET category_1c_id='".$Category1c_id."' where term_id ='".(int)$category_id."'");
		}
	}
	
	if ((empty($category_id)) or ($category_id == 0)){	
		$ins = new stdClass ();
		$ins->term_id = NULL;
		$ins->name = $CategoryName;
		$ins->slug = '';
		$ins->term_group = 0;
		$ins->category_1c_id = $Category1c_id;
		insertObject ( "" . DB_PREFIX ."terms", $ins, 'term_id'  ) ;
		$category_id = ( int )$ins->term_id;
		
		$ins = new stdClass ();
		$ins->term_taxonomy_id = NULL;
		$ins->term_id = $category_id;
		$ins->taxonomy = 'product_cat';
		$ins->description = '';
		$ins->parent = 0;
		$ins->count = 0;
		insertObject ( "" . DB_PREFIX ."term_taxonomy", $ins ) ;
		
		update_url_alias ($category_id, $CategoryName, 'terms', 'slug', 'term_id');
	}
	return $category_id;
}

function CategoryXrefFill($CategoryArray) {
global $wpdb;
	if (VM_HIERARCHY_FOLDER == 1){
		foreach ( $CategoryArray as $category ) {
			//поиск группы  по id
			$CategoryParentId = 0;
			$parent_id_query  = $wpdb->get_results ( "SELECT tt.parent as parent FROM " . DB_PREFIX . "terms AS t LEFT OUTER JOIN " . DB_PREFIX ."term_taxonomy AS tt ON t.term_id = tt.term_id where t.term_id = '" . (int)$category ['category_id'] . "' AND tt.taxonomy = 'product_cat'" );
			if (count($parent_id_query)>0) {
				foreach($parent_id_query as $parent_id){
					$CategoryParentId = $parent_id->parent;
				}
			}else{
				return;	
			}
			
			$categoryowner = (int)$category ['owner'];
			$CategoryParentIdInt = (int)$CategoryParentId;
			if ($CategoryParentIdInt != $categoryowner ) {
				//случай категория входит не в ту родительскую категорию, переписываем
				$wpdb->query( "UPDATE " . DB_PREFIX . "term_taxonomy SET parent ='".$category ['owner']."' where term_id ='" . (int)$category ['category_id'] . "'" );
			}
		}
	}
}

function getCategoryInfo($category_1c_id) {
global $wpdb;
	$category_info_array = array();
	$CategoryIdQuery  = $wpdb->get_results ( "SELECT tt.parent as parent, tt.term_taxonomy_id as term_taxonomy_id, tt.count as count, t.name as name, t.slug as slug, t.term_id as term_id  FROM " . DB_PREFIX . "terms AS t LEFT OUTER JOIN " . DB_PREFIX ."term_taxonomy AS tt ON t.term_id = tt.term_id WHERE t.category_1c_id = '" . $category_1c_id. "' AND tt.taxonomy = 'product_cat'" );
	if (count($CategoryIdQuery)>0){
		foreach($CategoryIdQuery as $Category){
			$category_info_array['parent'] = $Category->parent;
			$category_info_array['term_taxonomy_id'] = $Category->term_taxonomy_id;
			$category_info_array['count'] = $Category->count;
			$category_info_array['name'] = $Category->name;	
			$category_info_array['slug'] = $Category->slug;		
			$category_info_array['term_id'] = $Category->term_id;					
		}
	}
	return $category_info_array;
}

function NewBrand($BrandName) {	
global $wpdb;
	
	$brand_id = 0;	
	if ((function_exists('wp_insert_term')) and (function_exists('is_wp_error')) and (USE_FUNCTION_WOOCOMMERCE == 1)) {
		$ModelSeourlGenerate = new ModuleSeoUrlGenerator();
		$slug_result = $ModelSeourlGenerate->seoUrlGenerateAjax($BrandName, DB_PREFIX.'terms', 'slug', true);
			
		$insert_data = wp_insert_term(
			$BrandName,  // новый термин
			'product_brand', // таксономия
			array(
				'description' => '',
				'slug'        => $slug_result,
				'parent'      => 0
			)
		);
		
		if( ! is_wp_error($insert_data) ){
			$brand_id = $insert_data['term_id'];
		}
	}
	
	if ((empty($brand_id)) or ($brand_id == 0)){	
		$ins = new stdClass ();
		$ins->term_id = NULL;
		$ins->name = $BrandName;
		$ins->slug = '';
		$ins->term_group = 0;
		$ins->category_1c_id = '';
		insertObject ( "" . DB_PREFIX ."terms", $ins, 'term_id'  ) ;
		$brand_id = ( int )$ins->term_id;
		
		$ins = new stdClass ();
		$ins->term_taxonomy_id = NULL;
		$ins->term_id = $brand_id;
		$ins->taxonomy = 'product_brand';
		$ins->description = '';
		$ins->parent = 0;
		$ins->count = 0;
		insertObject ( "" . DB_PREFIX ."term_taxonomy", $ins ) ;
		
		update_url_alias ($brand_id, $BrandName, 'terms', 'slug', 'term_id');
	}
	return $brand_id;
}

function BrandXref($term_taxonomy_id, $product_id) {
global $wpdb;
	
	$term_taxonomy_array = array();
	$CategoryIdArray = $wpdb->get_results( "SELECT term_taxonomy_id FROM " . DB_PREFIX ."term_taxonomy where taxonomy = 'product_brand'" );
	if (count($CategoryIdArray)>0){
		foreach($CategoryIdArray as $Category){
			$term_taxonomy_array[] = $Category->term_taxonomy_id;	
		}
	}
	
	if(!empty($term_taxonomy_array)){
		$text_query = array();
		foreach ($term_taxonomy_array as $term_taxonomy){
			$text_query[] = "`term_taxonomy_id` = '".$term_taxonomy."'";
		}
		if (!empty($text_query)){
			if (count($text_query)== 1 ){
				$text_query_final = $text_query[0];	
			}else{
				$text_query_final = implode(' OR ', $text_query);
			}
			$wpdb->query ("DELETE FROM `" . DB_PREFIX . "term_relationships` WHERE `object_id` = '" .(int)$product_id. "' AND (" .$text_query_final. ")");
		}
	}	
	
	if ($term_taxonomy_id == 0) {
		return;
	}
	
	$ins = new stdClass ();
	$ins->object_id = (int)$product_id;
	$ins->term_taxonomy_id = (int)$term_taxonomy_id;
	$ins->term_order = '0';
	insertObject ( "" . DB_PREFIX ."term_relationships", $ins ) ;

}

function getBrandInfo($BrandName) {
global $wpdb;
	$brand_info_array = array();
	$BrandIdQuery  = $wpdb->get_results ( "SELECT tt.parent as parent, tt.term_taxonomy_id as term_taxonomy_id, tt.count as count, t.name as name, t.slug as slug, t.term_id as term_id  FROM " . DB_PREFIX . "terms AS t LEFT OUTER JOIN " . DB_PREFIX ."term_taxonomy AS tt ON t.term_id = tt.term_id WHERE t.name = '" . $BrandName. "' AND tt.taxonomy = 'product_brand'" );
	if (count($BrandIdQuery)>0){
		foreach($BrandIdQuery as $Brand){
			$brand_info_array['parent'] 		  = $Brand->parent;
			$brand_info_array['term_taxonomy_id'] = $Brand->term_taxonomy_id;
			$brand_info_array['count'] 			  = $Brand->count;
			$brand_info_array['name'] 			  = $Brand->name;	
			$brand_info_array['slug'] 			  = $Brand->slug;		
			$brand_info_array['term_id'] 		  = $Brand->term_id;					
		}
	}
	return $brand_info_array;
}


function deleteTaxonomyForObject($object_id, $taxonomy) {
global $wpdb;
	$term_query  = $wpdb->get_results( "SELECT tt.term_id AS term_id,  tr.term_taxonomy_id AS term_taxonomy_id, t.slug AS slug  FROM " . DB_PREFIX ."term_relationships AS tr LEFT JOIN " . DB_PREFIX ."term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id LEFT JOIN " . DB_PREFIX ."terms AS t ON t.term_id = tt.term_id where tr.object_id = '".(int)$object_id."' AND tt.taxonomy = '".$taxonomy."'" );
	if (count($term_query)>0) {
		foreach($term_query as $terms){
			$term_id          = $terms->term_id;
			$term_taxonomy_id = $terms->term_taxonomy_id;
			$slug             = $terms->slug;
			if (($slug != 'featured') and ($slug != 'exclude-from-catalog') and ($slug != 'exclude-from-search')){ //рекомендованные товары не удаляем, также не удаляем настройки публикации товара hidden
				$wpdb->query("DELETE FROM " . DB_PREFIX . "term_relationships where object_id = '".(int)$object_id."' AND term_taxonomy_id = '".$term_taxonomy_id."'");
				updateCountInTermTaxonomy($term_taxonomy_id, $term_id, $taxonomy);
			}	
		}
	}
}

function updateCountInTermTaxonomy($term_taxonomy_id, $term_id, $taxonomy) { //универсальная функция установки колва записей для term_taxonomy
global $wpdb;
	//выборка связей по термам, пример:
	//SELECT * FROM wp_term_relationships AS wtr LEFT JOIN wp_term_taxonomy AS wtt ON wtr.term_taxonomy_id = wtt.term_taxonomy_id LEFT JOIN wp_terms AS wt ON wtt.term_id = wt.term_id WHERE wtr.object_id = '41796' or wtr.object_id = '114823'

	$term_array = array();
	$object_count = 0;
	if ($term_taxonomy_id <> 0){
		if ($taxonomy == 'product_cat'){
			//для категорий считаем кол-во только по товарам
			$relationships_query  = $wpdb->get_results( "SELECT COUNT(object_id) AS object_count FROM " . DB_PREFIX ."posts AS wp LEFT JOIN " . DB_PREFIX ."term_relationships AS wtr ON wp.ID = wtr.object_id where wtr.term_taxonomy_id = '".(int)$term_taxonomy_id."' and wp.post_type = 'product'" );
		}else{
			$relationships_query  = $wpdb->get_results( "SELECT COUNT(object_id) AS object_count FROM " . DB_PREFIX ."term_relationships where term_taxonomy_id = '".(int)$term_taxonomy_id."'" );
		}
		if (count($relationships_query)>0) {
			foreach($relationships_query as $relationships){
				$object_count = $relationships->object_count;
			}
			$wpdb->query( "UPDATE " . DB_PREFIX . "term_taxonomy SET count ='".$object_count."' where taxonomy = '".$taxonomy."' and term_taxonomy_id = '".(int)$term_taxonomy_id."'" );
			if (function_exists('clean_taxonomy_cache')){
				clean_taxonomy_cache( $taxonomy );
			}
			if ($taxonomy == 'product_cat'){
				$term_array[$term_id] = $object_count;
			}
		}
	}
	//перезаполнение категории uncategorized
	if ($taxonomy == 'product_cat'){	
		$terms_query  = $wpdb->get_results( "SELECT term_id FROM " . DB_PREFIX ."terms where slug = 'uncategorized'" );
		if (count($terms_query)>0) {
			foreach($terms_query as $term){
				$term_id = (int)$term->term_id;
			}
			$term_taxonomy_query  = $wpdb->get_results( "SELECT term_taxonomy_id FROM " . DB_PREFIX ."term_taxonomy where taxonomy = '".$taxonomy."' and term_id = '".(int)$term_id."'" );
			if (count($term_taxonomy_query)>0) {
				foreach($term_taxonomy_query as $term_taxonomy){
					$term_taxonomy_id = $term_taxonomy->term_taxonomy_id;				
				}
				$relationships_query  = $wpdb->get_results( "SELECT COUNT(object_id) AS object_count FROM " . DB_PREFIX ."term_relationships where term_taxonomy_id = '".(int)$term_taxonomy_id."'" );
				if (count($relationships_query)>0) {
					foreach($relationships_query as $relationships){
						$object_count = $relationships->object_count;
					}
					$wpdb->query( "UPDATE " . DB_PREFIX . "term_taxonomy SET count ='".$object_count."' where term_taxonomy_id = '".(int)$term_taxonomy_id."'" );
					$term_array[$term_id] = $object_count;
				}	
			}						
		}
		if (function_exists('clean_taxonomy_cache')){
			clean_taxonomy_cache( $taxonomy );
		}	
	}
	
	foreach($term_array as $term_id_key=>$term_id_value){
		if ($term_id_key <> 0){
			$meta_key = 'product_count_product_cat';
			$termmeta_query  = $wpdb->get_results( "SELECT meta_id FROM " . DB_PREFIX ."termmeta where meta_key = '".$meta_key."' and term_id = '".(int)$term_id_key."'" );
			if (count($termmeta_query)>0) {
				foreach($termmeta_query as $termmeta){
					$meta_id = $termmeta->meta_id;
				}
				$wpdb->query( "UPDATE " . DB_PREFIX . "termmeta SET meta_value ='".$term_id_value."' where meta_id = '".$meta_id."' and term_id = '".(int)$term_id_key."' and meta_key = '".$meta_key."'" );
			}else{
				$ins = new stdClass ();
				$ins->meta_id = NULL;
				$ins->meta_key = $meta_key;
				$ins->meta_value = $term_id_value;
				$ins->term_id = (int)$term_id_key;
				insertObject ( "" . DB_PREFIX ."termmeta", $ins) ;
			}
		}
	}
	
	if (function_exists('wp_cache_flush')){
		wp_cache_flush();
	}
	if (function_exists('delete_transient')){
		delete_transient( 'wc_term_counts' );	
	}
}

function NewProductsXref($term_taxonomy_id, $product_id) {
global $wpdb;
	
	$term_taxonomy_array = array();
	$CategoryIdArray = $wpdb->get_results( "SELECT term_taxonomy_id FROM " . DB_PREFIX ."term_taxonomy where taxonomy = 'product_cat'" );
	if (count($CategoryIdArray)>0){
		foreach($CategoryIdArray as $Category){
			$term_taxonomy_array[] = $Category->term_taxonomy_id;	
		}
	}
	
	if(!empty($term_taxonomy_array)){
		$text_query = array();
		foreach ($term_taxonomy_array as $term_taxonomy){
			$text_query[] = "`term_taxonomy_id` = '".$term_taxonomy."'";
		}
		if (!empty($text_query)){
			if (count($text_query)== 1 ){
				$text_query_final = $text_query[0];	
			}else{
				$text_query_final = implode(' OR ', $text_query);
			}
			$wpdb->query ("DELETE FROM `" . DB_PREFIX . "term_relationships` WHERE `object_id` = '" .(int)$product_id. "' AND (" .$text_query_final. ")");
		}
	}	
	
	if ($term_taxonomy_id == 0) {
		return;
	}
	
	$ins = new stdClass ();
	$ins->object_id = (int)$product_id;
	$ins->term_taxonomy_id = (int)$term_taxonomy_id;
	$ins->term_order = '0';
	insertObject ( "" . DB_PREFIX ."term_relationships", $ins ) ;

	if ((VM_PRODUCT_LOAD_IN_PARENTCATEGORY == 1) AND (VM_FOLDER == 1)){
		ParentCategoryFillProduct($term_taxonomy_id, $product_id);
	}
}

function ParentCategoryFillProduct($term_taxonomy_id, $product_id) { //рекурсия
global $wpdb;

	$search_parent_category  = $wpdb->get_results( "SELECT parent FROM " . DB_PREFIX . "term_taxonomy WHERE term_taxonomy_id = '" . (int)$term_taxonomy_id . "' AND taxonomy = 'product_cat'" );
	if (count($search_parent_category)>0) {
		$new_term_taxonomy_id =  0;
		$new_term_id = 0;
		foreach($search_parent_category as $parent_category){
			if (count($search_parent_category)>0) {
				$parent_id = (int)$parent_category->parent;
				$search_id_category  = $wpdb->get_results( "SELECT term_taxonomy_id, term_id FROM " . DB_PREFIX . "term_taxonomy WHERE term_id = '" . (int)$parent_id . "' AND taxonomy = 'product_cat'" );
				if (count($search_id_category)>0) {
					foreach($search_id_category as $search_id){
						$new_term_taxonomy_id = (int)$search_id->term_taxonomy_id;
						$new_term_id = (int)$search_id->term_id;
					}
				}
			}
		}
		if($new_term_taxonomy_id > 0){
			$ins = new stdClass ();
			$ins->object_id = (int)$product_id;
			$ins->term_taxonomy_id = (int)$new_term_taxonomy_id;
			insertObject ( "" . DB_PREFIX ."term_relationships", $ins ) ;
			updateCountInTermTaxonomy($new_term_taxonomy_id, $new_term_id, 'product_cat');
			ParentCategoryFillProduct($new_term_taxonomy_id, $product_id);
		}
	}	
}

function NewProduct($product_SKU, $product_name, $product_desc, $product_full_image, $product_thumb_image, $IdTovar1c, $Opisanie, $Izgotovitel, $Weight, $Length, $Width, $Height, $mark_delete, $BriefDescription) {
global $wpdb;
global $full_url_site;	
	
	$ins = new stdClass ();
	$ins->ID = NULL;
	$ins->post_author = 1;
	$ins->post_date = date('Y-m-d H:i:s');
	$ins->post_date_gmt = date('Y-m-d H:i:s');
	$ins->post_content = $Opisanie;
	$ins->post_title = $product_name;
	if (VM_KRATKOE_OPISANIE == 1){
		$ins->post_excerpt = $BriefDescription;
	}else{
		$ins->post_excerpt = '';
	}

	$product_publish = 'publish';
	if ((VM_PRODUCT_VIEW_PRICE0 == 0) or (VM_PRODUCT_VIEW_COUNT0 == 0)){ 
		$product_publish = 'draft';
	}
	if (($mark_delete == true) and (VM_DELETE_MARK_PRODUCT == 'HIDE')){
		$product_publish = 'draft';
	}
	$ins->post_status = $product_publish;

	$ins->comment_status = 'open';
	$ins->ping_status = 'closed';
	$ins->post_password = '';
	$ins->post_name = '';
	$ins->to_ping = '';
	$ins->pinged = '';
	$ins->post_modified = date('Y-m-d H:i:s');
	$ins->post_modified_gmt = date('Y-m-d H:i:s');
	$ins->post_content_filtered = '';
	$ins->post_parent = 0;
	$ins->guid = '';
	$ins->menu_order = 0;
	$ins->post_type = 'product';
	$ins->post_mime_type = '';
	$ins->comment_count = 0;
	$ins->product_1c_id = $IdTovar1c;
	insertObject ( "" . DB_PREFIX ."posts", $ins, 'ID' );
	$product_id = (int)$ins->ID;
	
	$guid = $full_url_site.'/?post_type=product&#038;p='.$product_id; //https://sitewith1c-demo4.000webhostapp.com/?post_type=product&#038;p=10
	$wpdb->query( "UPDATE " . DB_PREFIX . "posts SET guid ='".$guid."' where ID ='" . (int)$product_id . "'" );
			
	update_url_alias ($product_id, $product_name, 'posts', 'post_name', 'ID');
	$array_null = array();
	$array_serialize = serialize($array_null);
	$internal_meta_keys = array(
		'_sku'  					=> $product_SKU,
		'_price'  					=> '0',
		'_regular_price'  			=> '0',
		'_sale_price'   			=> '',
		'_sale_price_dates_from'  	=> '',
		'_sale_price_dates_to'   	=> '',
		'total_sales'   			=> '1',
		'_tax_status'   			=> 'taxable',
		'_tax_class'   			    => '',
		'_manage_stock'   			=> 'yes',
		'_stock'   			        => '0',
		'_stock_status'   			=> VM_STOCK_STATUS_VALUE,
		'_backorders'   			=> VM_BACKORDERS_VALUE,
		'_low_stock_amount'   		=> '0',
		'_sold_individually'   		=> 'no',
		'_weight'   			    => $Weight,
		'_length'   				=> $Length,
		'_width'   					=> $Width,
		'_height'   				=> $Height,
		'_upsell_ids' 				=> $array_serialize,
		'_crosssell_ids' 			=> $array_serialize,
		'_purchase_note' 			=> '', 
		'_default_attributes' 		=> $array_serialize,
		'_product_attributes' 		=> $array_serialize,
		'_virtual' 					=> 'no',
		'_downloadable' 			=> 'no',
		'_download_limit' 			=> '-1',
		'_download_expiry' 			=> '-1',
		'_wc_rating_count' 			=> $array_serialize,
		'_wc_average_rating' 		=> '0',
		'_wc_review_count' 			=> '0',
		'_thumbnail_id'			    => '0',
		'_product_image_gallery' 	=> '',
		'_product_version' 			=> '3.5.3',
		'_edit_last'  				=> '1',
		'_product_layout' 			=> '',
		'_product_style' 			=> '',
		'_total_stock_quantity' 	=> '0',
		'_accessory_ids' 			=> 'a:0:{}',
		'_specifications_display_attributes' => 'yes',
		'_specifications_attributes_title' 	 => '',
		'_specifications' 	=> '',
		'slide_template' 	=> 'default'	
	);
	
	$text_query_arr = array();
	foreach ($internal_meta_keys as $key => $value){
		$text_query_arr[] = "(NULL, '".$product_id."', '".$key."', '".$value."')";
	}
	if (!empty($text_query_arr)) {
		if (count($text_query_arr)== 1 ){
			$text_query_final = $text_query_arr[0];	
		}else{
			$text_query_final = implode(',', $text_query_arr);
		} 
		$wpdb->query( "INSERT INTO " . DB_PREFIX . "postmeta (meta_id, post_id, meta_key, meta_value) VALUES ".$text_query_final."" );
	}
	
	installProductTypeId('simple', $product_id);
	
	return $product_id;
}

function installProductTypeId($product_type_name, $product_id) { //рекурсия
global $wpdb;

	$product_type_id = 2;
	$search_product_type_name  = $wpdb->get_results ( "SELECT t.term_id as term_id, tt.term_taxonomy_id as term_taxonomy_id, t.name as name, t.slug as slug FROM " . DB_PREFIX . "terms AS t LEFT OUTER JOIN " . DB_PREFIX ."term_taxonomy AS tt ON t.term_id = tt.term_id  WHERE  t.name = '".$product_type_name."' AND tt.taxonomy = 'product_type'" );
	if (count($search_product_type_name)>0) {
		foreach($search_product_type_name as $product_type_name_result){
			$product_type_id = (int)$product_type_name_result->term_taxonomy_id;
		}
	}
	$search_product_type  = $wpdb->get_results ( "SELECT t.term_id as term_id, tt.term_taxonomy_id as term_taxonomy_id, t.name as name, t.slug as slug, tr.object_id as object_id  FROM " . DB_PREFIX . "terms AS t LEFT OUTER JOIN " . DB_PREFIX ."term_taxonomy AS tt ON t.term_id = tt.term_id LEFT OUTER JOIN " . DB_PREFIX ."term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id  WHERE tt.taxonomy = 'product_type' AND tr.object_id = '" . (int)$product_id. "'" );
	if (count($search_product_type)>0) {
		foreach($search_product_type as $product_type_result){
			$product_type_id_old = (int)$product_type_result->term_taxonomy_id;
		}
		if ((isset($product_type_id_old)) and ($product_type_id <> $product_type_id_old)){
			$wpdb->query ("DELETE FROM " . DB_PREFIX . "term_relationships WHERE object_id = '" .$product_id. "' AND term_taxonomy_id = '".$product_type_id_old."'");
			$ins = new stdClass ();
			$ins->object_id = (int)$product_id;
			$ins->term_taxonomy_id = (int)$product_type_id;
			$ins->term_order = '0';
			insertObject ( "" . DB_PREFIX ."term_relationships", $ins ) ;
		}
	}else{
		$ins = new stdClass ();
		$ins->object_id = (int)$product_id;
		$ins->term_taxonomy_id = (int)$product_type_id;
		$ins->term_order = '0';
		insertObject ( "" . DB_PREFIX ."term_relationships", $ins ) ;
	}
}

function AddDirectorySvoistva($xml_product , $xml_all_svoistva){ //$xml_all){
global $wpdb;
global $StopNameCreateSvoistvaArray;

$SvoistvoArray = array();
$SvoistvoType = array();

	if (VM_SVOISTVA_1C == 1){
		 
		$PropertyStd = '';
		if (isset($xml_all_svoistva->Свойство)) {
			$PropertyStd = $xml_all_svoistva->Свойство;
		}
		if (isset($xml_all_svoistva->СвойствоНоменклатуры)) {
			$PropertyStd = $xml_all_svoistva->СвойствоНоменклатуры;
		} 
		 
		if (!empty($PropertyStd)) {
			foreach ($PropertyStd as $Svoistvo) {
				$id_svoistva = (isset($Svoistvo->Ид)) ? (string)$Svoistvo->Ид : '';
				$id_svoistva = formatString($id_svoistva);
				$name_svoistva = (isset($Svoistvo->Наименование)) ? (string)$Svoistvo->Наименование : '';
				$name_svoistva = formatString($name_svoistva);
						
				if (!in_array($name_svoistva, $StopNameCreateSvoistvaArray)){
					$type_value = (isset($Svoistvo->ТипЗначений)) ? (string)$Svoistvo->ТипЗначений : 'Строка';
					if ($type_value == 'Справочник') {		
							$SvoistvoArray[$id_svoistva] = $name_svoistva;
							$SvoistvoType[$id_svoistva] = $type_value;
							foreach($Svoistvo->ВариантыЗначений->Справочник as $option_value){
								if ((string)$option_value->Значение != '') {
									$znach_option_value = formatString((string)$option_value->Значение);
									$SvoistvoValues[$id_svoistva][(string)$option_value->ИдЗначения] = $znach_option_value;
								}
							}						
					}else{
						$SvoistvoArray[$id_svoistva] = $name_svoistva;
						$SvoistvoType[$id_svoistva] = $type_value;
					}
				}
						
			}
		
			//СЧИТЫВАЕМ СВОЙСТВА ТОВАРОВ
			if (isset($xml_product->Товар)){
				foreach ($xml_product->Товар as $Tovar){
					$tovar_id =(string)$Tovar->Ид ;
					$ProductIdArray = $wpdb->get_results( "SELECT ID FROM " . DB_PREFIX ."posts where product_1c_id = '" . $tovar_id . "'" );
					if (count($ProductIdArray)>0){
						foreach($ProductIdArray as $Product){
							$product_id = $Product->ID;	
						}
						
						if (isset($Tovar->ЗначенияСвойств->ЗначенияСвойства)){
							foreach ($Tovar->ЗначенияСвойств->ЗначенияСвойства as $ZnachSvoistvaTovar){
								$id_svoistvo_tovar = (isset($ZnachSvoistvaTovar->Ид)) ? (string)$ZnachSvoistvaTovar->Ид : '';
								$znach_svoistvo_tovar = (isset($ZnachSvoistvaTovar->Значение)) ? (string)$ZnachSvoistvaTovar->Значение : '';
								$znach_svoistvo_tovar = formatString($znach_svoistvo_tovar);
								
								if (isset($SvoistvoArray[$id_svoistvo_tovar])){
									$type_value = $SvoistvoType[$id_svoistvo_tovar];
									if ($type_value == 'Справочник') {
										if ((isset($SvoistvoValues[$id_svoistvo_tovar][$znach_svoistvo_tovar])) and (!empty($SvoistvoValues[$id_svoistvo_tovar][$znach_svoistvo_tovar]))){	//проверяем наличие свойств в массиве										
											insertAttributeValue($SvoistvoArray[$id_svoistvo_tovar], $SvoistvoValues[$id_svoistvo_tovar][$znach_svoistvo_tovar], $id_svoistvo_tovar, $product_id, 0);												
										}
									}else{
										if (!empty($znach_svoistvo_tovar)){
											insertAttributeValue($SvoistvoArray[$id_svoistvo_tovar], $znach_svoistvo_tovar, $id_svoistvo_tovar, $product_id, 0);
										}										
									}
								}
								
							}	
						}	
						
					}
				}
			}
		}
	}	
	
	unset($SvoistvoArray);
	unset($SvoistvoType);
}

function insertAttributeValue($attribute_name, $attribute_value, $id_svoistvo_tovar, $product_id, $is_variation) {
global $wpdb;	
	
	$attribute_name = formatStringAttributeValue($attribute_name);
	$attribute_value = formatStringAttributeValue($attribute_value);

	if (VM_GROUP_ATTRIBUTE == 1){
		$attribute_name = mb_substr($attribute_name,0,200); 
		$attribute_value = mb_substr($attribute_value,0,200);
	}
	
	$is_taxonomy = 0;		
	if ((VM_GROUP_ATTRIBUTE == 1) ){
		$attribute_query = $wpdb->get_results("SELECT attribute_id, attribute_name  FROM " . DB_PREFIX ."woocommerce_attribute_taxonomies where attribute_1c_id = '" . $id_svoistvo_tovar . "'" );	
		if ((count($attribute_query) == 0)) {
			$ModelSeourlGenerate = new ModuleSeoUrlGenerator();
			$attribute_slug = $ModelSeourlGenerate->seoUrlGenerateAjax(mb_substr($attribute_name, 0, 26), DB_PREFIX.'woocommerce_attribute_taxonomies', 'attribute_name' , true);
			$args = array(
				'id'  			=> '',
				'name'  		=> $attribute_name,
				'slug'  		=> $attribute_slug,
				'type'  		=> 'select',
				'order_by'  	=> 'menu_order',
				'has_archives'  => 1
				);	
			$attribute_id = createGroupAttributeWP ($args, $id_svoistvo_tovar, $attribute_name);
			$wpdb->query( "UPDATE " . DB_PREFIX . "woocommerce_attribute_taxonomies SET attribute_1c_id ='".$id_svoistvo_tovar."' where attribute_id = '" . (int)$attribute_id . "'" );
		}else{
			foreach($attribute_query as $attribute_result){
				$attribute_id = (int)$attribute_result->attribute_id;	
				$attribute_slug = $attribute_result->attribute_name;
			}
			$args = array(
				'id'  			=> $attribute_id,
				'name'  		=> $attribute_name,
				'slug'  		=> $attribute_slug,
				'type'  		=> 'select',
				'order_by'  	=> 'menu_order',
				'has_archives'  => 1
				);
			$attribute_id = createGroupAttributeWP ($args, $id_svoistvo_tovar, $attribute_name);
		}	
		$attribute_query = $wpdb->get_results("SELECT attribute_name FROM " . DB_PREFIX ."woocommerce_attribute_taxonomies where attribute_id = '" . $attribute_id . "'" );	
		if ((count($attribute_query) > 0)) {
			foreach($attribute_query as $attribute_result){
				$pa_attribute_name = 'pa_'.$attribute_result->attribute_name;	
			}
		}else{
			$pa_attribute_name = str_replace(' ', '-', mb_strtolower($attribute_name));
			$pa_attribute_name = mb_strtolower(rus2translit($pa_attribute_name));
			$pa_attribute_name = 'pa_'.mb_strtolower($pa_attribute_name);
		}
		
		$term_property = getTermProperty($attribute_value, $pa_attribute_name);
		if ((isset($term_property['term_id'])) and (empty($term_property['term_id']))){
			$ins = new stdClass ();
			$ins->term_id = NULL;
			$ins->name = $attribute_value;
			$ins->slug = '';
			$ins->term_group = 0;
			$ins->category_1c_id = '';
			insertObject ( "" . DB_PREFIX ."terms", $ins, 'term_id') ;
			$term_id = ( int )$ins->term_id;
			update_url_alias ($term_id, mb_substr($attribute_value, 0, 26), 'terms', 'slug', 'term_id');
		}else{
			if (isset($term_property['term_id'])){
				$term_id = $term_property['term_id'];	
			}
		}
		$term_taxonomy_query = $wpdb->get_results("SELECT term_taxonomy_id FROM " . DB_PREFIX ."term_taxonomy where taxonomy = '" . $pa_attribute_name . "' and term_id = '" . $term_id . "'" );	
		if ((count($term_taxonomy_query) == 0)) {	
			$ins = new stdClass ();
			$ins->term_taxonomy_id = NULL;
			$ins->term_id = $term_id;
			$ins->taxonomy = $pa_attribute_name;
			$ins->description = '';
			$ins->parent = 0;
			$ins->count = 0;
			insertObject ( "" . DB_PREFIX ."term_taxonomy", $ins, 'term_taxonomy_id') ;
			$term_taxonomy_id = ( int )$ins->term_taxonomy_id;
		}else{
			foreach($term_taxonomy_query as $term_taxonomy_result){
				$term_taxonomy_id = (int)$term_taxonomy_result->term_taxonomy_id;	
			}
		}	
		$order_pa_attribute_name = 'order_'.$pa_attribute_name;
		$termmeta_query = $wpdb->get_results("SELECT meta_id FROM " . DB_PREFIX ."termmeta where meta_key = '" . $order_pa_attribute_name . "' and term_id = '" . $term_id . "'" );	
		if ((count($termmeta_query) == 0)) {	
			$ins = new stdClass ();
			$ins->meta_id = NULL;
			$ins->term_id = $term_id;
			$ins->meta_key = $order_pa_attribute_name;
			$ins->meta_value = 0;
			insertObject ( "" . DB_PREFIX ."termmeta", $ins, 'meta_id') ;
			$meta_id = ( int )$ins->meta_id;
		}else{
			foreach($termmeta_query as $termmeta_result){
				$meta_id = (int)$termmeta_result->meta_id;	
			}
		}
		if ($is_variation == 0){
			deleteProductAttribute($pa_attribute_name, $product_id);
		}
		$term_relationships_query = $wpdb->get_results("SELECT * FROM " . DB_PREFIX ."term_relationships where term_taxonomy_id = '" . $term_taxonomy_id . "' and object_id = '" . $product_id . "'" );	
		if ((count($term_relationships_query) == 0)) {	
			$ins = new stdClass ();
			$ins->object_id = (int)$product_id;
			$ins->term_taxonomy_id = (int)$term_taxonomy_id;
			$ins->term_order = 0;
			insertObject ( "" . DB_PREFIX ."term_relationships", $ins ) ;
		}
		
		updateCountInTermTaxonomy($term_taxonomy_id, $term_id, $pa_attribute_name);
			
		$is_taxonomy = 1;
		$attribute_name = $pa_attribute_name;
	}
	
	$new_product_attribute = array(
		'name'  			=> $attribute_name,
		'value'  			=> $attribute_value,
		'position'  		=> '0',
		'is_visible'  		=> '1',
		'is_variation'  	=> $is_variation,
		'is_taxonomy'  		=> $is_taxonomy,
		'attribute_1c_id'  	=> $id_svoistvo_tovar
		);
	$key_attribute = sanitize_title($attribute_name);
	$product_attributes = getProductAttribute($product_id);
	if (!empty($product_attributes)){
		foreach ($product_attributes as $key => $value){
			if ($is_variation == 1){
				if ((isset($value['is_variation'])) and ($value['is_variation'] == 1) and (!isset($value['attribute_1c_id']))){
					unset($product_attributes[$key]);
				}
			}
			if (isset($value['attribute_1c_id'])){
				if ($value['attribute_1c_id'] == $id_svoistvo_tovar){
					$old_product_attribute = $product_attributes[$key];
					$old_product_attribute['value'] =$attribute_value;
					$product_attributes[$key] = $old_product_attribute;
				}else{
					$product_attributes[$key_attribute] = $new_product_attribute;
				}
			}else{
				$product_attributes[$key_attribute] = $new_product_attribute;
			}
		}
	}
	if (empty($product_attributes)){
		$product_attributes = array();
		$product_attributes[$key_attribute] = $new_product_attribute;
	}
	setProductAttributes($product_attributes, $product_id);
}

function getProductAttribute ($product_id){
global $wpdb;
	
	$product_attributes = '';
	$attribute_query  =  $wpdb->get_results( "SELECT `meta_value` FROM `".DB_PREFIX."postmeta` WHERE `post_id` = '".(int)$product_id."' and `meta_key` = '_product_attributes'" );
	if (count($attribute_query)>0) {
		foreach($attribute_query as $attribute){
			$product_attributes = $attribute->meta_value;
		}
	}
	if (!empty($product_attributes)){
		$product_attributes = getUnserialize($product_attributes);
		if (!empty($product_attributes) and ($product_attributes == false)){
			write_log('Ошибка чтения атрибутов товара: '. $product_id.' Очищаем значения атрибутов товара');
			$empty_array = array();
			setProductAttributes($empty_array, $product_id);
			$product_attributes = array();
		}
	}
	return $product_attributes;
}

function addAttributeValue($key_attribute, $new_attribute_value, $product_id) {
global $wpdb;	
	$new_attribute_value = trim($new_attribute_value);
	$new_attribute_value = formatString($new_attribute_value,1);
	$new_attribute_value = str_replace(array('/', '\\'), ' ', $new_attribute_value);
	$product_attributes = getProductAttribute($product_id);
	if (!empty($product_attributes)){
		foreach ($product_attributes as $key => $value){
			if ($key == $key_attribute){
				$old_product_attribute = $product_attributes[$key];
				if (isset($old_product_attribute['value'])){
					$old_attribute_value = trim($old_product_attribute['value']);
					$parts_value   = explode( ' | ' , $old_attribute_value );
					if (empty($parts_value)){
						if ( $old_attribute_value <> $new_attribute_value){
							$old_product_attribute['value'] = $old_product_attribute['value'].' | '.$new_attribute_value; 
						}else{
							//
						}
					}else{
						$parts_value[] = $new_attribute_value;
						$old_product_attribute['value'] = implode(' | ', $parts_value);
					}
				}
				$product_attributes[$key] = $old_product_attribute;
			}
		}
	}else{
		write_log('Ошибка! Не найдены атрибуты для товара = '. $product_id);
	}

	if (!empty($product_attributes)){
		setProductAttributes($product_attributes, $product_id);
	}
}

function setProductAttributes($product_attributes, $product_id) {
global $wpdb;	

	$serialize_product_attributes = @serialize($product_attributes);
	$wpdb->query( "UPDATE " . DB_PREFIX . "postmeta SET meta_value ='".$serialize_product_attributes."' where meta_key = '_product_attributes' and post_id ='" . (int)$product_id . "'" );

	if (function_exists('delete_transient')) {//and (USE_FUNCTION_WOOCOMMERCE == 1)){
		delete_transient( 'wc_attribute_taxonomies' );
	}
}

function deleteProductAttribute($attribute_name, $product_id) {
global $wpdb;
	
	$term_taxonomy_array = array();
	$TermTaxonomyIdArray = $wpdb->get_results( "SELECT term_taxonomy_id FROM " . DB_PREFIX ."term_taxonomy where taxonomy = '".$attribute_name."'");
	if (count($TermTaxonomyIdArray)>0){
		foreach($TermTaxonomyIdArray as $TermTaxonomyId){
			$term_taxonomy_array[] = $TermTaxonomyId->term_taxonomy_id;	
		}
	}
	
	if(!empty($term_taxonomy_array)){
		$text_query = array();
		foreach ($term_taxonomy_array as $term_taxonomy){
			$text_query[] = "`term_taxonomy_id` = '".$term_taxonomy."'";	
			//$wpdb->query ("DELETE FROM `" . DB_PREFIX . "term_relationships` WHERE `object_id` = '" .(int)$product_id. "' AND `term_taxonomy_id` = '".(int)$term_taxonomy."'");
		}		
		if (!empty($text_query)){
			if (count($text_query)== 1 ){
				$text_query_final = $text_query[0];	
			}else{
				$text_query_final = implode(' OR ', $text_query);
			}
			$wpdb->query ("DELETE FROM `" . DB_PREFIX . "term_relationships` WHERE `object_id` = '" .(int)$product_id. "' AND (" .$text_query_final. ")");
		}
	}	
}

function deleteProductAttributeValue($attribute_name, $attribute_value, $product_id) {
global $wpdb;
	
	$term_taxonomy_array = array();
	$product_taxonomy = getTermTaxnomyObject($product_id);
	if (count($product_taxonomy)>0){
		foreach($product_taxonomy as $taxonomy){
			if ((isset($taxonomy->taxonomy)) and (isset($taxonomy->name))  and (isset($taxonomy->term_taxonomy_id))){
				$product_attribute_name = $taxonomy->taxonomy;
				$product_attribute_value = $taxonomy->name;
				$term_taxonomy_id = $taxonomy->term_taxonomy_id;
				if (($product_attribute_name == $attribute_name) and ($product_attribute_value == $attribute_value)){
					$term_taxonomy_array[] = $term_taxonomy_id;
				}
			}
		}		
	}

	if(!empty($term_taxonomy_array)){
		$text_query = array();
		foreach ($term_taxonomy_array as $term_taxonomy){
			$text_query[] = "`term_taxonomy_id` = '".$term_taxonomy."'";	
		}		
		if (!empty($text_query)){
			if (count($text_query)== 1 ){
				$text_query_final = $text_query[0];	
			}else{
				$text_query_final = implode(' OR ', $text_query);
			}
			$wpdb->query ("DELETE FROM `" . DB_PREFIX . "term_relationships` WHERE `object_id` = '" .(int)$product_id. "' AND (" .$text_query_final. ")");
		}
	}	
}

function deleteDiffAttributesInProductAttribute($product_id){
global $wpdb;

	$attributes_variation = array();
	$attributes_as_is = array();
	
	$attributes_info = array();
	$product_attributes_main = getPostmetaObject($product_id, '_product_attributes');
	if (count($product_attributes_main)>0){
		foreach ($product_attributes_main as $attributes) {
			$meta_value = $attributes->meta_value;
			$attributes_info = getUnserialize($meta_value);
		}
	}

	$ProductAllArrayQuery = $wpdb->get_results( "SELECT p1.ID AS product_id, p2.ID AS product_variation_id
	FROM " . DB_PREFIX . "posts AS p1 LEFT JOIN " . DB_PREFIX . "posts AS p2 ON p1.ID = p2.post_parent 
	WHERE (p1.ID = ".$product_id.") AND p1.post_type = 'product' AND p2.post_type = 'product_variation'");
	if (count($ProductAllArrayQuery) > 0 ){
		foreach ($ProductAllArrayQuery as $ProductAllResult){
			$find_str = 'attribute_';
			$variation_attributes = getPostmetaObject($ProductAllResult->product_variation_id, $find_str);
			if (count($variation_attributes)>0){
				foreach ($variation_attributes as $variation_attribute) {
					$variation_attribute_name = $variation_attribute->meta_key;
					$variation_attribute_name = str_replace($find_str,'', $variation_attribute_name);
					$variation_attribute_value = trim($variation_attribute->meta_value);
						
					$is_variation = 0;			
					if (isset($attributes_info[$variation_attribute_name])){
						$attribute_info = $attributes_info[$variation_attribute_name];
						if (isset($attribute_info['is_variation'])){
							$is_variation = $attribute_info['is_variation'];	
						}
					}
					if (($is_variation == 0) and (VM_UPDATE_SVOISTVA == 0)){
						continue;
					}
					$attributes_variation[$variation_attribute_name][] = $variation_attribute_value;	
				}
			}
		}
	}
	
	if (count($attributes_variation)>0){
		foreach ($attributes_variation as $attributes_variation_name => $attributes_variation_res){
			$product_attributes = getTermTaxnomyObject($product_id, $attributes_variation_name);
	
			foreach ($product_attributes as $product_attribute) {
				if (isset($product_attribute->name)){
					$attributes_as_is[$attributes_variation_name][] = $product_attribute->name;
				}
			}
		}
	}

	if (count($attributes_as_is)>0){
		foreach ($attributes_as_is as $attributes_as_is_name => $attributes_as_is_res){
			if ((isset($attributes_as_is[$attributes_as_is_name])) and (isset($attributes_variation[$attributes_as_is_name]))){
				$diff_array = array_diff_assoc($attributes_as_is[$attributes_as_is_name], $attributes_variation[$attributes_as_is_name]);
				if (count($diff_array)>0){
					foreach ($diff_array as $attribute_value){
						deleteProductAttributeValue($attributes_as_is_name, $attribute_value, $product_id);
					}
				}
			}
		}
	}
}

function createGroupAttributeWP($args, $id_svoistvo_tovar, $attribute_name){
global $wpdb;
	//Array of attribute parameters.
	//$args {
	//@type int    `$id`           Unique identifier, used to update an attribute.
	//@type string `$name`         Attribute name. Always required.
	//@type string `$slug`         Attribute alphanumeric identifier.
	//@type string `$type`         Type of attribute.
	//						   Core by default accepts: `'select'` and `'text'`.
	//						   Default to `'select'`.
	//@type string `$order_by`     Sort order.
	//						   Accepts: `'menu_order'`, `'name'`, `'name_num'` and `'id'`.
	//						   Default to `'menu_order'`.
	//@type bool   `$has_archives` Enable or disable attribute archives. False by default.
	//}
	$attribute_id = 0;
	if ((function_exists('wc_create_attribute')) and (function_exists('is_wp_error')) and (USE_FUNCTION_WOOCOMMERCE == 1)){
		require_once ( JPATH_BASE .DS.'wp-content'.DS.'plugins'.DS.'woocommerce'.DS.'includes'.DS.'wc-attribute-functions.php');
		$attribute_id = wc_create_attribute( $args ); //since 3.2.0
		if ((function_exists('is_wp_error')) and (is_wp_error($attribute_id))){
			$attribute_id = 0;
		}
	}
	if ((empty($attribute_id)) or ($attribute_id == 0)){
		//попытка ручной перезаписи attribute_taxonomies
		$attribute_query = $wpdb->get_results("SELECT attribute_id FROM " . DB_PREFIX ."woocommerce_attribute_taxonomies where attribute_1c_id = '" . $id_svoistvo_tovar . "'" );	
		if ((count($attribute_query) == 0)) {
			$ins = new stdClass ();
			$ins->attribute_id = NULL;
			
			//$slug = preg_replace( '/^pa\_/', '', wc_sanitize_taxonomy_name( $attribute_name ) );
			//$slug = substr($slug, 0, 26);
			$slug = '';
			
			$ins->attribute_name = $slug;
			$ins->attribute_label = $attribute_name;
			$ins->attribute_type = 'select';
			$ins->attribute_orderby = 'menu_order';
			$ins->attribute_public = 1;
			$ins->attribute_1c_id = $id_svoistvo_tovar;
			insertObject ( "" . DB_PREFIX ."woocommerce_attribute_taxonomies", $ins, 'attribute_id' );
			$attribute_id = (int)$ins->attribute_id;

			update_url_alias ($attribute_id, mb_substr($attribute_name, 0, 26), 'woocommerce_attribute_taxonomies', 'attribute_name', 'attribute_id');
		}else{
			$wpdb->query( "UPDATE " . DB_PREFIX . "woocommerce_attribute_taxonomies SET attribute_label ='".$attribute_name."' where attribute_1c_id = '" . (int)$id_svoistvo_tovar . "'" );
			foreach($attribute_query as $attribute_result){
				$attribute_id = (int)$attribute_result->attribute_id;	
			}
		}
	}
	return $attribute_id;
}

function deleteVariationAttributeInProductAttribute($product_id, $variation_id){
	
	$product_attributes = getProductAttribute($product_id);
	$product_attributes_new = $product_attributes;

	$find_str = 'attribute_';
	$variation_attributes = getPostmetaObject($variation_id, $find_str);
	foreach ($variation_attributes as $variation_attribute) {
		$variation_attribute_name = $variation_attribute->meta_key;
		$variation_attribute_name = str_replace($find_str,'', $variation_attribute_name);
		$variation_attribute_value = trim($variation_attribute->meta_value);
		
		foreach ($product_attributes as $product_attribute_key => $product_attribute_result) {
			if (($product_attribute_key == $variation_attribute_name) and (isset($product_attribute_result['value'])) and
				 (isset($product_attribute_result['is_variation']))){
				if ($product_attribute_result['is_variation'] == 1){
					$product_attribute_value = $product_attribute_result['value'];
				
					$PosDS = strpos($product_attribute_value, '|');
					if ($PosDS !== false){
						$parts_new = array();
						$parts   = explode( '|' , $product_attribute_value );
						foreach ($parts as $part){
							$part = trim($part);
							if ($part <> $variation_attribute_value){
								$parts_new[] = $part;
							}
						}
						$product_attribute_value = (!empty($parts_new)) ? (implode(' | ', $parts_new)) : '';
					}else{
						$product_attribute_value = str_replace($variation_attribute_value, '', $product_attribute_value);
					}
					$product_attributes_new[$product_attribute_key]['value'] = $product_attribute_value;
				}
			}
		}
	}
	setProductAttributes($product_attributes_new, $product_id);
}

function getTermProperty($term_name, $term_taxonomy, $category_1c_id = ''){
global $wpdb;
	
	//нельзя чтобы на одну term_id было несколько taxonomy 
	//проверка дублей запросом: SELECT term_id, COUNT(term_id) FROM wp_term_taxonomy GROUP BY term_id HAVING COUNT(term_id) > 1;
	$term_property = array("term_id" => "", "slug" => "");
	if (empty($category_1c_id)){
		$term_query = $wpdb->get_results("SELECT t.slug AS slug, t.term_id AS term_id FROM " . DB_PREFIX ."term_taxonomy AS tt LEFT JOIN " . DB_PREFIX ."terms AS t ON t.term_id = tt.term_id  WHERE t.name = '" . $term_name . "' and (t.category_1c_id = '' or t.category_1c_id IS NULL) and tt.taxonomy = '".$term_taxonomy."'" );
	}else{
		$term_query = $wpdb->get_results("SELECT t.slug AS slug, t.term_id AS term_id FROM " . DB_PREFIX ."term_taxonomy AS tt LEFT JOIN " . DB_PREFIX ."terms AS t ON t.term_id = tt.term_id  WHERE t.name = '" . $term_name . "' and t.category_1c_id = '".$category_1c_id."'' and tt.taxonomy = '".$term_taxonomy."'" );
	}	
	if ((count($term_query) > 0)){
		foreach($term_query as $term_result){
			$term_property['term_id'] = $term_result->term_id;
			$term_property['slug'] = $term_result->slug;
		}
	}
	return $term_property;
}

function formatStringAttributeValue($value){	
	$value = trim($value);
	$value = formatString($value,1);
	
	$pos = strrpos($value, '\\');
	if ($pos !== false) { 
		$value = str_replace('\\', " ", $value);
		$value = str_replace(' &', "&", $value);
	}

	$pos = strrpos($value, '/');
	if ($pos !== false) { 
		$value = str_replace('/', " ", $value);
	}

	return($value);
}

function getUnserialize( $text ) {
	if ( isSerialized( $text ) ) { // don't attempt to unserialize data that wasn't serialized going in
		return @unserialize( $text );
	}
	return $text;
}

function getTermTaxnomyObject($object_id, $find_taxonomy = ""){
global $wpdb;
	
	$object_taxnomy = array();
	if (empty($find_taxonomy)){
		$object_taxnomy_query  =  $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "term_relationships AS wtr LEFT JOIN " . DB_PREFIX . "term_taxonomy AS wtt ON wtr.term_taxonomy_id = wtt.term_taxonomy_id LEFT JOIN " . DB_PREFIX . "terms AS wt ON wtt.term_id = wt.term_id WHERE wtr.object_id = '".(int)$object_id."'" );
	}else{
		$object_taxnomy_query  =  $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "term_relationships AS wtr LEFT JOIN " . DB_PREFIX . "term_taxonomy AS wtt ON wtr.term_taxonomy_id = wtt.term_taxonomy_id LEFT JOIN " . DB_PREFIX . "terms AS wt ON wtt.term_id = wt.term_id WHERE wtr.object_id = '".(int)$object_id."' AND wtt.taxonomy = '".$find_taxonomy."'" );
	}
	if (count($object_taxnomy_query)>0) {
		foreach($object_taxnomy_query as $result_key => $result_value){
			$object_taxnomy[$result_key] = $result_value;
		}
	}
	return $object_taxnomy;
}

function getPostmetaObject($object_id, $find_meta_key = ""){
global $wpdb;
	
	$postmeta_array = array();
	if (empty($find_meta_key)){
		$postmeta_query  =  $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "postmeta WHERE post_id = '".(int)$object_id."'" );
	}else{
		$postmeta_query  =  $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "postmeta WHERE post_id = '".(int)$object_id."' AND meta_key LIKE '%".$find_meta_key."%'" );
	}
	if (count($postmeta_query)>0) {
		foreach($postmeta_query as $result_key => $result_value){
			$postmeta_array[$result_key] = $result_value;
		}
	}
	return $postmeta_array;
}

function getPostmetaInfo($object_id){
global $wpdb;
	
	$postmeta_array = array();
	$postmeta_query  =  $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "postmeta WHERE post_id = '".(int)$object_id."'" );
	if (count($postmeta_query)>0) {
		foreach($postmeta_query as $postmeta){
			if ((isset($postmeta->meta_key)) and (isset($postmeta->meta_value))){
				$meta_key = $postmeta->meta_key;
				$meta_value = $postmeta->meta_value;
				$postmeta_array[$meta_key] = $meta_value;
			}		
		}
	}
	return $postmeta_array;
}

function getOutOfStockStatusObject($object_id){	
	$stock_status = VM_STOCK_STATUS_VALUE;
	$backorders   = VM_BACKORDERS_VALUE;
	
	$postmeta_array = getPostmetaObject($object_id, "_backorders");
	if (count($postmeta_array)>0){
		foreach ($postmeta_array as $postmeta) {
			$postmeta_name = $postmeta->meta_key;
			if ($postmeta_name == '_backorders'){
				$backorders = trim($postmeta->meta_value);
			}		
		}
	}
	
	if ($backorders == 'no'){
		$stock_status = 'outofstock';
	}
	if ($backorders == 'yes'){
		$stock_status = 'onbackorder';
	}
	if ($backorders == 'notify'){
		$stock_status = 'onbackorder';	
	}
	return $stock_status;
}

function isSerialized( $data, $strict = true ) {
	// if it isn't a string, it isn't serialized.
	if ( ! is_string( $data ) ) {
		return false;
	}
	$data = trim( $data );
	if ( 'N;' == $data ) {
		return true;
	}
	if ( strlen( $data ) < 4 ) {
		return false;
	}
	if ( ':' !== $data[1] ) {
		return false;
	}
	if ( $strict ) {
		$lastc = substr( $data, -1 );
		if ( ';' !== $lastc && '}' !== $lastc ) {
			return false;
		}
	} else {
		$semicolon = strpos( $data, ';' );
		$brace     = strpos( $data, '}' );
		// Either ; or } must exist.
		if ( false === $semicolon && false === $brace ) {
			return false;
		}
		// But neither must be in the first X characters.
		if ( false !== $semicolon && $semicolon < 3 ) {
			return false;
		}
		if ( false !== $brace && $brace < 4 ) {
			return false;
		}
	}
	$token = $data[0];
	switch ( $token ) {
		case 's':
			if ( $strict ) {
				if ( '"' !== substr( $data, -2, 1 ) ) {
					return false;
				}
			} elseif ( false === strpos( $data, '"' ) ) {
				return false;
			}
			// or else fall through
		case 'a':
		case 'O':
			return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
		case 'b':
		case 'i':
		case 'd':
			$end = $strict ? '$' : '';
			return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
	}
	return false;
}

function TovarArrayFill($xml, $xml_all_svoistva, $CatalogContainsChanges, $all_product_count, $FilePart = 0) {
global $wpdb;
global $CategoryArray;
global $FilenameUpload;
global $ThisPage;
global $posix;
$product_count = 0;
$product_count_continue = 0;
$time_start = strtotime(date('Y-m-d H:i:s'));
$type_upload = 'product';
HeartBeat::setCountElementAll($all_product_count);
	
	if (!isset ($xml->Товар)){
		write_log("ERROR! no products");
		print "no products!";
		return;
	}
	$last_element_upload = HeartBeat::getLastElementUpload($FilenameUpload);
	//СЧИТЫВАЕМ ТОВАРЫ
	foreach ($xml->Товар as $Tovar){	
		$product_count++;
		HeartBeat::setCountElementNow($product_count);
		
		$IdTovar1c = (isset($Tovar->Ид)) ? (string)$Tovar->Ид : '';
		$HeartBeatStatus = HeartBeat::getNext($FilenameUpload, $FilePart, $ThisPage, $posix, $type_upload, $IdTovar1c, $last_element_upload);
		if ($HeartBeatStatus == 'next'){
			$product_count_continue++;
			continue;
		}
		progressLoad($product_count,$product_count_continue, $FilePart, $all_product_count, $time_start, strtotime(date('Y-m-d H:i:s')), "товаров");
		if ($HeartBeatStatus == 'false'){
			exit();
		}
		
		$Artikul = (isset($Tovar->Артикул)) ? (string)$Tovar->Артикул : '';
		$Artikul = !empty($Artikul) ? $Artikul : '';
		$Name = (isset($Tovar->Наименование)) ? (string)$Tovar->Наименование : 'Наименование не заполнено';
		$Opisanie = (isset($Tovar->Описание)) ? (string)$Tovar->Описание : '';
		$Code = (isset($Tovar->Код)) ? (string)$Tovar->Код : '';
		$Status = (isset($Tovar->Статус)) ? (string)$Tovar->Статус : '';
	
		$Izgotovitel = ''; //обнуляем поле Производитель
		if (isset($Tovar->Изготовитель->Наименование)){
			$Izgotovitel = (string)$Tovar->Изготовитель->Наименование; //для УТ 11 (Загрузка производителя через реквизит "Производитель")
		}else {
			$Izgotovitel = get_manufacturer_in_svoistvo($Tovar, $xml_all_svoistva);
		}	
		if (empty ($Izgotovitel)){
			$Izgotovitel = 'Производитель не указан';
		}	
		$mark_delete = false;
		if ((isset($Tovar['Статус'])) and ($Tovar['Статус'] == "Удален")){
			$mark_delete = true;
		}
		if ($Status == "Удален"){
			$mark_delete = true;
		}
		//считываем реквизит Краткое описание		
		$BriefDescription = get_value_in_svoistvo($Tovar, $xml_all_svoistva, 'Краткое описание');	
		//считываем реквизит товара Полное наименование, Вес и ОписаниеВФорматеHTML
		$NameFull = '';
		$Length = get_value_in_svoistvo($Tovar, $xml_all_svoistva, 'Длина');
		$Width =  get_value_in_svoistvo($Tovar, $xml_all_svoistva, 'Ширина');
		$Height = get_value_in_svoistvo($Tovar, $xml_all_svoistva, 'Высота');
		$Weight = get_value_in_svoistvo($Tovar, $xml_all_svoistva, 'Вес');
		$Brand  = get_value_in_svoistvo($Tovar, $xml_all_svoistva, 'Бренд');
		
		$ValueRequisite = '';
		if (isset($Tovar->ЗначенияРеквизитов->ЗначениеРеквизита)){
			$ValueRequisite = $Tovar->ЗначенияРеквизитов->ЗначениеРеквизита;
		}
		if (isset($Tovar->ЗначениеРеквизита)){
			$ValueRequisite = $Tovar->ЗначениеРеквизита;
		}
		if (!empty($ValueRequisite)){
			foreach ($ValueRequisite as $RekvizitData){	
				if (isset($RekvizitData->Наименование)){
					if ($RekvizitData->Наименование	==	"Наименование"){
						$NameFull = (isset($RekvizitData->Значение)) ? (string)$RekvizitData->Значение : '';
					}
					if ($RekvizitData->Наименование	==	"Полное наименование"){
						$NameFull = (isset($RekvizitData->Значение)) ? (string)$RekvizitData->Значение : '';
					}
					if ($RekvizitData->Наименование	==	"ОписаниеВФорматеHTML"){
						$OpisanieHTML =	(isset($RekvizitData->Значение)) ? (string)$RekvizitData->Значение : '';
						$Opisanie .= $OpisanieHTML; 
					}
					if ($RekvizitData->Наименование	==	"Вес"){
						$Weight = (isset($RekvizitData->Значение)) ? (string)$RekvizitData->Значение : '';
					}
					if ($RekvizitData->Наименование	==	"Длина"){
						$Length = (isset($RekvizitData->Значение)) ? (string)$RekvizitData->Значение : '';
					}
					if ($RekvizitData->Наименование	==	"Ширина"){
						$Width = (isset($RekvizitData->Значение)) ? (string)$RekvizitData->Значение : '';
					}
					if ($RekvizitData->Наименование	==	"Высота"){
						$Height = (isset($RekvizitData->Значение)) ? (string)$RekvizitData->Значение : '';
					}
					if ($RekvizitData->Наименование	==	"Код"){
						$Code = (isset($RekvizitData->Значение)) ? (string)$RekvizitData->Значение : '';
					}
					if (($RekvizitData->Наименование ==	"Производитель") or ($RekvizitData->Наименование ==	"Изготовитель")){
						$Manufacturer = (isset($RekvizitData->Значение)) ? (string)$RekvizitData->Значение : '';
						$Izgotovitel = (empty($Izgotovitel)) ? $Manufacturer : $Izgotovitel;
					}
					if ($RekvizitData->Наименование	==	"Бренд"){
						$Code = (isset($RekvizitData->Значение)) ? (string)$RekvizitData->Значение : '';
					}
				}
			}
		}

		//экранируем полученные данные о товаре
		$Name = formatString($Name, 1);
		$NameFull =formatString($NameFull, 1);
		$Artikul = formatString($Artikul, 1);
		$Code = formatString($Code, 1);
		$Opisanie = nl2br($Opisanie);
		//$Opisanie = formatString($Opisanie); //отключено форматирование описания
		$Izgotovitel = formatString($Izgotovitel);
		$Brand  = formatString($Brand);
		
		//проверяем на числовое значение
		$Weight = str_replace(",",".",$Weight); //замена запятых на точку
		$Weight = (float)preg_replace("/[^0-9\.]/", '', $Weight);
		$Length = str_replace(",",".",$Length); //замена запятых на точку
		$Length = (float)preg_replace("/[^0-9\.]/", '', $Length);
		$Width = str_replace(",",".",$Width); //замена запятых на точку
		$Width = (float)preg_replace("/[^0-9\.]/", '', $Width);
		$Height = str_replace(",",".",$Height); //замена запятых на точку
		$Height = (float)preg_replace("/[^0-9\.]/", '', $Height);

		if ((VM_ALLNAMEUSE == 1) and (!empty($NameFull))){
			$Name = $NameFull; 
		}
		if (($Artikul == 'Не указано') and (!empty($Code))){
			$Artikul = $Code; 
		}

		$stock_status = VM_STOCK_STATUS_VALUE;

		//отбираем позиции с ИД из базы vm
		$ProductIdArray = $wpdb->get_results("SELECT ID FROM " . DB_PREFIX ."posts where product_1c_id = '" . $IdTovar1c . "' AND post_type = 'product'" );	
		if ((count($ProductIdArray) == 0)) {
			//товара с таким ИД нет
			if (VM_CREATE_PRODUCT == 0){
				continue;
			}

			if (($mark_delete == true) and (VM_DELETE_MARK_PRODUCT == 'DELETE')){
				continue;
			}	
			$SrcImgName = '';
			$OutImgName = '';
			$product_id = NewProduct( $Artikul, $Name, $NameFull, $SrcImgName, $OutImgName,$IdTovar1c, $Opisanie, $Izgotovitel, $Weight, $Length, $Width, $Height, $mark_delete, $BriefDescription);
			$stock_status = getOutOfStockStatusObject($product_id);			
			$args_lookuptable = array(
				'sku'		 	 => $Artikul,
				'min_price'  	 => 0,
				'max_price' 	 => 0,
				'onsale'     	 => 0,
				'stock_quantity' => 0,
				'stock_status'   => $stock_status,
				'total_sales' 	 => 1
			);
			createProductForLookupTable($product_id, $args_lookuptable);
			
			if (VM_MANUFACTURER_1C == 1){
				insertAttributeValue('Производитель', $Izgotovitel, 'Производитель', $product_id, 0);
			}
			
			//Связываем товар и группу 
			if (VM_FOLDER == 1){
				$IdGroupVm = 0;
				$CategoryTermId = 0;
				foreach ($Tovar->Группы as $GroupsData){
					$IdGroup1c = (isset($GroupsData->Ид)) ? (string)$GroupsData->Ид : '';
					$categoryInfoArray = getCategoryInfo($IdGroup1c);
					if (isset($categoryInfoArray['term_taxonomy_id'])) {
						$IdGroupVm = $categoryInfoArray['term_taxonomy_id'];
					}
					if (isset($categoryInfoArray['term_id'])) {
						$CategoryTermId = $categoryInfoArray['term_id'];
					}
					
				}	
				if ($IdGroupVm <> 0){
					NewProductsXref($IdGroupVm, $product_id);
					updateCountInTermTaxonomy($IdGroupVm, $CategoryTermId, 'product_cat');
				}
			}

			//заполняем Бренд у товара 
			if ((version_compare( WC_VERSION, '8.0.0', ">=" )) and (!empty($Brand))) {
				$IdBrandVm = 0;
				$BrandTermId = 0;
				$BrandInfoArray = getBrandInfo($Brand);
				if (empty($BrandInfoArray)){
					$brand_id = NewBrand($Brand);
					$BrandInfoArray = getBrandInfo($Brand);
				}
				if (isset($BrandInfoArray['term_taxonomy_id'])) {
					$IdBrandVm = $BrandInfoArray['term_taxonomy_id'];
				}
				if (isset($BrandInfoArray['term_id'])) {
					$BrandTermId = $BrandInfoArray['term_id'];
				}
				if ($IdBrandVm <> 0){
					BrandXref($IdBrandVm, $product_id);
					updateCountInTermTaxonomy($IdBrandVm, $BrandTermId, 'product_brand');
				}
			}

			//загружаем картинки
			if (IMAGE_LOAD == 1) {
				loadImagesForProduct($Tovar, $Name, $product_id);
			}
		}else{
			//товар в базе есть, обновляем его имя и полное имя и описание 
			foreach($ProductIdArray as $Product){
				$product_id = (int)$Product->ID;	
			}			
			if (($mark_delete == true) and (VM_DELETE_MARK_PRODUCT == 'DELETE')){
				DeleteProduct((int)$product_id, $Name);
				continue; //завершаем обработку товара
			}

			$updatePostsFieldArray = array();
			$updatePostmetaFieldArray = array();
			
			if (($mark_delete == true) and (VM_DELETE_MARK_PRODUCT == 'HIDE')){
				$updatePostsFieldArray['post_status'] = 'draft';
			}
			if ((VM_PRODUCT_VIEW_PRICE0 == 0) or (VM_PRODUCT_VIEW_COUNT0 == 0)){ 
				//отключаем отображение товара, до момента установки цены или кол-ва на товар
				$updatePostsFieldArray['post_status'] = 'draft';
				$updatePostmetaFieldArray['_stock'] = 0;
			}

			if (VM_UPDATE_NAME == 1){
				$updatePostsFieldArray['post_title'] = $Name;
			}
			if (VM_UPDATE_DESC == 1){
				$updatePostsFieldArray['post_content'] = $Opisanie;
			}
			if (VM_KRATKOE_OPISANIE == 1){
				$updatePostsFieldArray['post_excerpt'] = $BriefDescription;
			}
			
			if (VM_UPDATE_ARTIKUL == 1){
				$updatePostmetaFieldArray['_sku'] = $Artikul;
				$args_lookuptable = array(
					'sku' => $Artikul
				);
				createProductForLookupTable($product_id, $args_lookuptable);
			}		
			if (VM_UPDATE_META == 1){
				//$updatePostmetaFieldArray['meta_title'] = "Купить ".$Name;
				//$updatePostmetaFieldArray['meta_description'] = "Купить ".$Name;
				//$words = explode(' ', $Name);
				//$metakey = implode(',', $words) . ',' . $Name;
				//$updatePostmetaFieldArray['meta_keyword'] = $metakey;
			}
			if ((!empty($Weight)) and ($Weight > 0)){
				$updatePostmetaFieldArray['_weight'] = $Weight;
			}
			if ((!empty($Length)) and ($Length > 0)){
				$updatePostmetaFieldArray['_length'] = $Length;
			}
			if ((!empty($Width)) and ($Width > 0)){
				$updatePostmetaFieldArray['_width'] = $Width;
			}
			if ((!empty($Height)) and ($Height > 0)){
				$updatePostmetaFieldArray['_height'] = $Height;
			}
					
			$text_query = array();
			foreach ($updatePostsFieldArray as $updatePostsFieldKey => $updatePostsFieldValue){
				$text_query[] = $updatePostsFieldKey." = '".$updatePostsFieldValue."'";
			}
			if (!empty($text_query)){
				if (count($text_query)== 1 ){
					$text_query_final = $text_query[0];	
				}else{
					$text_query_final = implode(' , ', $text_query);
				}
				$product_update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "posts SET ".$text_query_final." WHERE ID='". (int)$product_id."'");
			}
			unset($text_query);
			foreach ($updatePostmetaFieldArray as $updatePostmetaFieldKey => $updatePostmetaFieldValue){
				$product_update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "postmeta SET meta_value = '".$updatePostmetaFieldValue."' WHERE post_id='". (int)$product_id."' AND meta_key = '".$updatePostmetaFieldKey."'");
			}
			unset($updatePostsFieldArray, $updatePostmetaFieldArray);
			
			//обновление свойств
			if ((VM_SVOISTVA_1C == 1) and (VM_UPDATE_SVOISTVA == 1)){				
				$empty_array = array();
				setProductAttributes($empty_array, $product_id);
			}
			
			//обновляем производителей
			if ((VM_MANUFACTURER_1C == 1) and (VM_UPDATE_MANUFACTURE == 1)){
				insertAttributeValue('Производитель', $Izgotovitel, 'Производитель', $product_id, 0);
			}
			
			if (VM_UPDATE_CATEGORY == 1){
				$IdGroupVm = 0;
				$CategoryTermId = 0;
				foreach ($Tovar->Группы as $GroupsData){
					$IdGroup1c = (isset($GroupsData->Ид)) ? (string)$GroupsData->Ид : '';
					$categoryInfoArray = getCategoryInfo($IdGroup1c);
					if (isset($categoryInfoArray['term_taxonomy_id'])) {
						$IdGroupVm = $categoryInfoArray['term_taxonomy_id'];
					}
					if (isset($categoryInfoArray['term_id'])) {
						$CategoryTermId = $categoryInfoArray['term_id'];
					}
					
				}	
				if ($IdGroupVm <> 0){
					NewProductsXref($IdGroupVm, $product_id);
					updateCountInTermTaxonomy($IdGroupVm, $CategoryTermId, 'product_cat');
				}
			}

			//заполняем Бренд у товара 
			if ((version_compare( WC_VERSION, '8.0.0', ">=" )) and (!empty($Brand))) {
				$IdBrandVm = 0;
				$BrandTermId = 0;
				$BrandInfoArray = getBrandInfo($Brand);
				if (empty($BrandInfoArray)){
					$brand_id = NewBrand($Brand);
					$BrandInfoArray = getBrandInfo($Brand);
				}
				if (isset($BrandInfoArray['term_taxonomy_id'])) {
					$IdBrandVm = $BrandInfoArray['term_taxonomy_id'];
				}
				if (isset($BrandInfoArray['term_id'])) {
					$BrandTermId = $BrandInfoArray['term_id'];
				}
				if ($IdBrandVm <> 0){
					BrandXref($IdBrandVm, $product_id);
					updateCountInTermTaxonomy($IdBrandVm, $BrandTermId, 'product_brand');
				}
			}

			deleteTaxonomyForObject($product_id, 'product_visibility'); //удаляем для отображения товара в категориях в шаблоне
					
			//загружаем картинки
			if ((IMAGE_LOAD == 1) and (VM_UPDATE_IMAGE == 1)){
				
				//очищаем все картинки
				if (UT_10_3 == 0) { // удаляем все картинки у товара, если УТ 11						
					DeleteImages ($product_id);
				}
				
				if (($CatalogContainsChanges == 'false') AND (UT_10_3 == 1)) { //удаляем картинки, если УТ 10.3
					DeleteImages ($product_id);
				}
				
				if (($CatalogContainsChanges == 'true') AND (UT_10_3 == 1)) {
					//не удаляем картинки, если УТ 10.3
				}
				loadImagesForProduct($Tovar, $Name, $product_id);				
			}						
		}
		if ((function_exists("get_post")) and (function_exists("wp_update_post"))){
			$post = get_post($product_id);
			wp_update_post($post);
		}	
	}
	HeartBeat::clearElementUploadInStatusProgress($FilenameUpload, $FilePart, $type_upload);
}

function loadImagesForProduct($Tovar, $Name, $product_id){
$images_array = array();
$count_images = 0;
	if (isset($Tovar->Картинка)){
		foreach ($Tovar->Картинка as $PicturePath){
			$images_array[] = $PicturePath;
		}
	}
	$images_array = array_unique($images_array);
	if (!empty($images_array)){
		foreach ($images_array as $PicturePath){
			$count_images = $count_images + 1;
			if ( $PicturePath <> ''){
				$PicturePath = getFileFromPath($PicturePath);	
				//переносим файл с картинкой в папку, где храняться картинки
				$copy_result = copyFileToImageFolder($PicturePath, $Name);
				if ($copy_result['status_result'] == 'true') {						
					UpdateImages($product_id, $Name, $PicturePath, $count_images, $copy_result['file_path_db']);
				}
				if (($copy_result['status_result'] == 'false') and (STOP_PROGRESS == 1)){
					UpdateImages($product_id, $Name, $PicturePath, $count_images, $copy_result['file_path_db']);
				}
			}							
		}
	}
	unset($images_array);
}

function copyFileToImageFolder($filename, $product_name){
	$folder_name = getNameForFolder($product_name);
	if (STOP_PROGRESS == 0){
		$folder = JPATH_BASE_PICTURE . DS . $folder_name;
		if (!file_exists($folder)){
			mkdir($folder, 0755);
		}
	}
	$temp_catalog = JPATH_BASE . DS ."TEMP" . DS . $filename;
	if (STOP_PROGRESS == 0){
		$copy_catalog = JPATH_BASE_PICTURE . DS .$folder_name. DS . $filename;
		$file_path_db = VM_CATALOG_IMAGE . DS .$folder_name. DS . $filename;
	}else{
		$copy_catalog = JPATH_BASE_PICTURE . DS . $filename;
		$file_path_db = VM_CATALOG_IMAGE . DS . $filename;
	}
	$result = array('file_path_all' => $copy_catalog,
					'file_path_db'  => $file_path_db,	
					'status_result' => 'false');
	if (file_exists($temp_catalog)){
		$size = getimagesize($temp_catalog);//проверка на картинку равную 0 байт
		if (empty($size)) {
			$result['status_result'] = 'false';
			write_log("Ошибка копирования файла! Не определен размер файла ".$filename.", возможно он равен 0 байт. Товар: ".$product_name."");
			return $result;	
		}
		if ((!empty($size)) and ($size[0] == 0) and ($size[1]  == 0)) {
			$result['status_result'] = 'false';
			write_log("Ошибка копирования файла! Размер файла ".$filename." равен 0 байт. Товар: ".$product_name."");
			return $result;			
		}			
		if (!copy($temp_catalog, $copy_catalog)){
			write_log("Не удалось скопировать ".$filename." для товара ".$product_name."");	
			$result['status_result'] = 'false';	
		}else{
			$result['status_result'] = 'true';
			if (VM_DELETE_TEMP == 1){
				clear_files_temp($filename);	
			}	
		}
	}else{
		if (file_exists($copy_catalog)){
			$result['status_result'] =  'true';
		}else{
			write_log("Не найден файл ".$filename." в папке TEMP для товара ".$product_name."");
			$result['status_result'] =  'false';
		}
	}
	return $result;
}

function UpdateImages($product_id, $Name, $PicturePath, $count_images, $file_path_db) {
global $wpdb;	
global $full_url_site;
	
	$post_name = rus2translit($Name);
	$guid = $full_url_site. DS .$file_path_db;
	$RParts   = explode( '.' , $PicturePath );
	$RPicturePath   = $RParts[0];
	$extension = $RParts[1];
	$image_type = 'image/'.$extension; 
			
	$ins = new stdClass ();
	$ins->ID = NULL;
	$ins->post_author = 1;
	$ins->post_date = date('Y-m-d H:i:s');
	$ins->post_date_gmt = date('Y-m-d H:i:s');
	$ins->post_content = '';
	$ins->post_title = $Name;
	$ins->post_excerpt = '';
	$ins->post_status = 'inherit';
	$ins->comment_status = 'open';
	$ins->ping_status = 'closed';
	$ins->post_password = '';
	$ins->post_name = $post_name;
	$ins->to_ping = '';
	$ins->pinged = '';
	$ins->post_modified = date('Y-m-d H:i:s');
	$ins->post_modified_gmt = date('Y-m-d H:i:s');
	$ins->post_content_filtered = '';
	$ins->post_parent = $product_id;
	$ins->guid = $guid;
	$ins->menu_order = $count_images;
	$ins->post_type = 'attachment';
	
	$RParts   = explode( '.' , $PicturePath );
	$RPicturePath   = $RParts[0];
	$extension = $RParts[1];
	
	$ins->post_mime_type = $image_type;
	$ins->comment_count = 0;
	$ins->product_1c_id = '';
	insertObject ( "" . DB_PREFIX ."posts", $ins, 'ID' );
	$image_id = (int)$ins->ID;	
	
	$attached_file_query = $wpdb->get_results("SELECT * FROM " . DB_PREFIX ."postmeta where post_id = '" . $image_id . "' AND meta_key = '_wp_attached_file'" );	
	if ((count($attached_file_query) == 0)) {
		$ins = new stdClass ();
		$ins->meta_id = NULL;
		$ins->post_id = $image_id;
		$ins->meta_key = '_wp_attached_file';
		$ins->meta_value = $file_path_db;
		insertObject ( "" . DB_PREFIX ."postmeta", $ins);
	}
	
	$attached_file_query = $wpdb->get_results("SELECT * FROM " . DB_PREFIX ."postmeta where post_id = '" . $image_id . "' AND meta_key = '_wp_attachment_metadata'" );	
	if ((count($attached_file_query) == 0)) {
		require_once( ABSPATH . 'wp-admin'. DS .'includes'. DS .'image.php' );
		$filename = ABSPATH . $file_path_db;
		if (file_exists($filename)){
			if ((function_exists('wp_generate_attachment_metadata')) and (function_exists('wp_update_attachment_metadata'))){
				$attach_data = wp_generate_attachment_metadata( $image_id, $filename );
				wp_update_attachment_metadata( $image_id, $attach_data );
			}else{
				write_log("Ошибка! Не найдена функция wp_generate_attachment_metadata и wp_update_attachment_metadata");
			}
		}
	}
	
	if ($count_images == 1){
		$product_thumbnail_id_query = $wpdb->get_results("SELECT meta_value FROM " . DB_PREFIX ."postmeta where post_id = '" . $product_id . "' AND meta_key = '_thumbnail_id'" );	
		if ((count($product_thumbnail_id_query) > 0)) {
			$wpdb->query ( "UPDATE " . DB_PREFIX . "postmeta SET meta_value='".$image_id."' WHERE post_id = '". (int) $product_id."' AND meta_key = '_thumbnail_id'");
		}else{
			$ins = new stdClass ();
			$ins->meta_id = NULL;
			$ins->post_id = $product_id;
			$ins->meta_key = '_thumbnail_id';
			$ins->meta_value = $image_id;
			insertObject ( "" . DB_PREFIX ."postmeta", $ins);
		}
	}else{
		$product_image_gallery = '';
		$product_image_gallery_query = $wpdb->get_results("SELECT meta_value FROM " . DB_PREFIX ."postmeta where post_id = '" . $product_id . "' AND meta_key = '_product_image_gallery'" );	
		if ((count($product_image_gallery_query) > 0)) {
			foreach($product_image_gallery_query as $product_image_gallery_result){
				$product_image_gallery = $product_image_gallery_result->meta_value;
			}
			$image_gallery_array = explode(',',$product_image_gallery);
			if (($image_gallery_array <> false) or (!empty($image_gallery_array))){
				$product_image_gallery = $product_image_gallery.','.$image_id;
			}else{
				$product_image_gallery = $image_id;
			}
			$product_image_gallery = trim($product_image_gallery, ',');
			$wpdb->query ( "UPDATE " . DB_PREFIX . "postmeta SET meta_value='".$product_image_gallery."' WHERE post_id = '". (int) $product_id."' AND meta_key = '_product_image_gallery'");
		}else{
			$ins = new stdClass ();
			$ins->meta_id = NULL;
			$ins->post_id = $product_id;
			$ins->meta_key = '_product_image_gallery';
			$ins->meta_value = $image_id;
			insertObject ( "" . DB_PREFIX ."postmeta", $ins);
		}
	}
}

function DeleteImages ($product_id) {
global $wpdb;
	$images_delete = array();
	$images_query = $wpdb->get_results("SELECT * FROM " . DB_PREFIX ."posts where post_parent = '" . $product_id . "' AND post_type = 'attachment' AND post_mime_type LIKE '%image%'" );	
	if ((count($images_query) > 0)) {
		foreach($images_query as $image){
			$images_delete[] = $image->ID;
		}		
	}
	$text_query_arr = array();
	foreach ($images_delete as $image_id){
		$text_query_arr[] = "post_id = '" .$image_id. "'";
	}
	if (!empty($text_query_arr)) {
		if (count($text_query_arr)== 1 ){
			$text_query_final = $text_query_arr[0];	
		}else{
			$text_query_final = implode(' OR ', $text_query_arr);
		} 
		$wpdb->query ("DELETE FROM " . DB_PREFIX . "postmeta WHERE (" .$text_query_final. ") AND (meta_key = '_wp_attached_file' OR meta_key = '_wp_attachment_metadata')");
	}
	$wpdb->query ("DELETE FROM " . DB_PREFIX . "posts WHERE post_parent = '" .$product_id. "' AND post_type = 'attachment' AND post_mime_type LIKE '%image%'");
	$wpdb->query ( "UPDATE " . DB_PREFIX . "postmeta SET meta_value='' WHERE post_id = '". (int) $product_id."' AND (meta_key = '_product_image_gallery' OR meta_key = '_thumbnail_id')");
}

function DeleteProduct($product_id, $product_name){

	$is_delete = false;
	$file_class_wc_api_products = JPATH_BASE .DS.'wp-content'.DS.'plugins'.DS.'woocommerce'.DS.'includes'.DS.'api'.DS.'legacy'.DS.'v1'.DS.'class-wc-api-products.php';
	if (file_exists($file_class_wc_api_products)){
		require_once ($file_class_wc_api_products);	
		$WC_API_Products = new WC_API_Products();
		$var = $WC_API_Products->delete_product($product_id);
		$is_delete = true;	
	}
	if (($is_delete == false) and (function_exists("wp_delete_post"))){	
		$result = wp_delete_post( $product_id, true );	
		$is_delete = true;
	}
	if ($is_delete == true){
		write_log("На сайте по данным 1С был удален: ".$product_name." (".$product_id.")");
	}
}

function get_manufacturer_in_svoistvo($Tovar, $xml_all_svoistva){
		
	$Izgotovitel = '';	
	$PropertyStd = '';
	if (isset($xml_all_svoistva->Свойство)) {
		$PropertyStd = $xml_all_svoistva->Свойство;
	}
	if (isset($xml_all_svoistva->СвойствоНоменклатуры)) {
		$PropertyStd = $xml_all_svoistva->СвойствоНоменклатуры;
	}
		
	if (!empty($PropertyStd)) {
		foreach ($PropertyStd as $SvArrPro){
			$id_svoistvo = (isset($SvArrPro->Ид)) ? (string)$SvArrPro->Ид : '';
			$name_svoistvo = (isset($SvArrPro->Наименование)) ? (string)$SvArrPro->Наименование : '';
			$IDArrayProizvoditel [$name_svoistvo] ['id_svoistvo'] = $id_svoistvo;
			if (isset($SvArrPro->ВариантыЗначений->Справочник)){
				foreach ($SvArrPro->ВариантыЗначений->Справочник as $VariantiZnachenii ){
					$id_znachenia = (isset($VariantiZnachenii->ИдЗначения)) ? (string)$VariantiZnachenii->ИдЗначения : '';
					$name_znachenia = (isset($VariantiZnachenii->Значение)) ? (string)$VariantiZnachenii->Значение : '';
					$SvoistvaArrayProizvoditel [$id_svoistvo] [$name_svoistvo] [$id_znachenia] ['name_znachenia'] = $name_znachenia;
				}
			}
		}
		
		if (isset($Tovar->ЗначенияСвойств->ЗначенияСвойства)){
			foreach ($Tovar->ЗначенияСвойств->ЗначенияСвойства as $ZnachSvoistvaTovar){
				$id_svoistvo_tovar = (isset($ZnachSvoistvaTovar->Ид)) ? (string)$ZnachSvoistvaTovar->Ид : '';
				$znach_svoistvo_tovar = (isset($ZnachSvoistvaTovar->Значение)) ? (string)$ZnachSvoistvaTovar->Значение : '';
				$SvoistvaTovara [$id_svoistvo_tovar]['znach_svoistvo_tovar'] = $znach_svoistvo_tovar;
			}
			if (isset($IDArrayProizvoditel ['Производитель'] ['id_svoistvo'])){
				$id_proizvoditelya = $IDArrayProizvoditel ['Производитель'] ['id_svoistvo'];
				if (isset($SvoistvaTovara [$id_proizvoditelya]['znach_svoistvo_tovar'])){
					$id_proizvoditelya_tovar = $SvoistvaTovara [$id_proizvoditelya]['znach_svoistvo_tovar'];
					if (isset($SvoistvaArrayProizvoditel [$id_proizvoditelya] ['Производитель'] [$id_proizvoditelya_tovar] ['name_znachenia'])){
						$id_svoistvo_proizvoditel_naiti = $SvoistvaArrayProizvoditel [$id_proizvoditelya] ['Производитель'] [$id_proizvoditelya_tovar] ['name_znachenia'];
						$Izgotovitel =  $id_svoistvo_proizvoditel_naiti;
					}else{
						if (UT_10_3 == 1){
							$Izgotovitel = $id_proizvoditelya_tovar;
						}
					}
				}
			}
		}
			return $Izgotovitel;
	}else{
		return $Izgotovitel;
	}		
}

function get_value_in_svoistvo($Tovar, $xml_all_svoistva, $PropertyName){
		
	$ValueProperty = '';	
	$PropertyStd = '';
	$is_string = false;
	if (isset($xml_all_svoistva->Свойство)) {
		$PropertyStd = $xml_all_svoistva->Свойство;
	}
	if (isset($xml_all_svoistva->СвойствоНоменклатуры)) {
		$PropertyStd = $xml_all_svoistva->СвойствоНоменклатуры;
	}
		
	if (!empty($PropertyStd)) {
		foreach ($PropertyStd as $SvArrPro){
			$id_svoistvo = (isset($SvArrPro->Ид)) ? (string)$SvArrPro->Ид : '';
			$name_svoistvo = (isset($SvArrPro->Наименование)) ? (string)$SvArrPro->Наименование : '';
			$IdArrayProperty [$name_svoistvo] ['id_svoistvo'] = $id_svoistvo;
			if (isset($SvArrPro->ВариантыЗначений->Справочник)){
				foreach ($SvArrPro->ВариантыЗначений->Справочник as $VariantiZnachenii ){
					$id_znachenia = (isset($VariantiZnachenii->ИдЗначения)) ? (string)$VariantiZnachenii->ИдЗначения : '';
					$name_znachenia = (isset($VariantiZnachenii->Значение)) ? (string)$VariantiZnachenii->Значение : '';
					$SvoistvaArrayProperty [$id_svoistvo] [$name_svoistvo] [$id_znachenia] ['name_znachenia'] = $name_znachenia;
				}
			}else{
				$is_string = true;
			}
		}
		
		if (isset($Tovar->ЗначенияСвойств->ЗначенияСвойства)){
			foreach ($Tovar->ЗначенияСвойств->ЗначенияСвойства as $ZnachSvoistvaTovar){
				$id_svoistvo_tovar = (isset($ZnachSvoistvaTovar->Ид)) ? (string)$ZnachSvoistvaTovar->Ид : '';
				$znach_svoistvo_tovar = (isset($ZnachSvoistvaTovar->Значение)) ? (string)$ZnachSvoistvaTovar->Значение : '';
				$SvoistvaTovara [$id_svoistvo_tovar]['znach_svoistvo_tovar'] = $znach_svoistvo_tovar;
			}
			if (isset($IdArrayProperty [$PropertyName] ['id_svoistvo'])){
				$id_property = $IdArrayProperty [$PropertyName] ['id_svoistvo'];
				if (isset($SvoistvaTovara [$id_property]['znach_svoistvo_tovar'])){
					$id_property_tovar = $SvoistvaTovara [$id_property]['znach_svoistvo_tovar'];
					if (isset($SvoistvaArrayProperty [$id_property] [$PropertyName] [$id_property_tovar] ['name_znachenia'])){
						$id_svoistvo_search = $SvoistvaArrayProperty [$id_property] [$PropertyName] [$id_property_tovar] ['name_znachenia'];
						$ValueProperty =  $id_svoistvo_search;
					}else{
						if ($is_string == true){
							$ValueProperty = $id_property_tovar;
						}
					}
				}
			}
		}
			return $ValueProperty;
	}else{
		return $ValueProperty;
	}		
}

function update_url_alias ($id,  $Name, $name_table, $name_slug, $name_id){
global $wpdb;

	$ModelSeourlGenerate = new ModuleSeoUrlGenerator();
	$table = DB_PREFIX.$name_table;
	$result = $ModelSeourlGenerate->seoUrlGenerateAjax($Name, $table, $name_slug, true);
	$update_url_alias = $wpdb->query ( "UPDATE ".$table." SET  ".$name_slug."='".$result."' where ".$name_id."='".(int)$id."'");
}

//функция установки цен номенклатуры и загрузки остатков
function product_price_update($offers, $ShopperGroups, $all_count_element, $FilePart = 0) { 
global $wpdb;
global $FilenameUpload;
global $ThisPage;
global $posix;
$element_count = 0;
$element_count_continue = 0;
$time_start = strtotime(date('Y-m-d H:i:s'));
$type_upload = 'price';
HeartBeat::setCountElementAll($all_count_element);
$ShopperGroupsArray = array();

	if (isset ( $ShopperGroups->ТипЦены )){
		foreach ($ShopperGroups->ТипЦены as $ShopperGroup ) { 
			$ShopperGroupId1c = (isset($ShopperGroup->Ид)) ? (string)$ShopperGroup->Ид : '';
			$ShopperGroupName = (isset($ShopperGroup->Наименование)) ? (string)$ShopperGroup->Наименование : '';
			$ShopperGroupsArray[$ShopperGroupId1c] = $ShopperGroupName;
		}
	}
	$is_modified = false;
	if (isset ( $offers->Предложение )){
		$last_element_upload = HeartBeat::getLastElementUpload($FilenameUpload);
		foreach ($offers->Предложение as $product_price_data ) { 
			$element_count++;
			HeartBeat::setCountElementNow($element_count);
			
			$product_id_1c = (isset($product_price_data->Ид)) ? (string)$product_price_data->Ид : '';
			$HeartBeatStatus = HeartBeat::getNext($FilenameUpload, $FilePart, $ThisPage, $posix, $type_upload, $product_id_1c, $last_element_upload);
			if ($HeartBeatStatus == 'next'){
				$element_count_continue++;
				continue;
			}
			progressLoad($element_count, $element_count_continue, $FilePart, $all_count_element, $time_start, strtotime(date('Y-m-d H:i:s')), "предложений");
			if ($HeartBeatStatus == 'false'){
				exit();
			}

			$ProductIdQuery = $wpdb->get_results ( "SELECT ID FROM " . DB_PREFIX . "posts WHERE product_1c_id = '".$product_id_1c."' and post_type = 'product'" );
			if (count($ProductIdQuery)>0) {
				foreach($ProductIdQuery as $Product){
					$product_id = (int)$Product->ID;		
				}
				$postmeta_array = getPostmetaInfo($product_id);
				if (VM_UPDATE_PRICE == 1){ 	
					if ((isset($product_price_data->Цены->Цена)) and (isset($product_id))) {
						$sale_price = '';
						$product_price_array = array();
						$onsale = 0;
						$main_price = 0;
						foreach ( $product_price_data->Цены->Цена as $price_data) { 
							$mPriceStr = (isset($price_data->ЦенаЗаЕдиницу)) ? (string)$price_data->ЦенаЗаЕдиницу : '0';
							$mPrice = str_replace(",",".",$mPriceStr); //замена запятых на точку
							$mPrice = (float)preg_replace("/[^0-9\.]/", '', $mPrice);
							$mCurr_data = (isset($price_data->Валюта)) ? (string)$price_data->Валюта : 'RUB';
							$mCurr = getRightNameCurrency($mCurr_data);
							$shopper_group_id_1c =(string)$price_data->ИдТипаЦены; 
							if ((isset($ShopperGroupsArray[$shopper_group_id_1c])) and (!empty(VM_SALE_PRICE_1C)) and ($ShopperGroupsArray[$shopper_group_id_1c] == VM_SALE_PRICE_1C)){
								if ($mPrice > 0){
									$sale_price = $mPrice;
									$onsale = 1;
									$is_modified = true;
								}
								
							}else{
								if ($mPrice > 0){ 
									$product_price_array[] = $mPrice;
									$is_modified = true;
								}
								$main_price = $mPrice; 
							}	
						}
						
						if (count($product_price_array) > 0){
							$product_price = min($product_price_array);
						}else{
							$product_price = 0;	
						}

						//создим meta_key цен, если их нет	
						if (!isset($postmeta_array['_price'])){
							$ins = new stdClass ();
							$ins->meta_id = NULL;
							$ins->post_id = $product_id;
							$ins->meta_key = '_price';
							$ins->meta_value = 0;
							insertObject ( "" . DB_PREFIX ."postmeta", $ins);
						}
						if (!isset($postmeta_array['_sale_price'])){
							$ins = new stdClass ();
							$ins->meta_id = NULL;
							$ins->post_id = $product_id;
							$ins->meta_key = '_sale_price';
							$ins->meta_value = '';
							insertObject ( "" . DB_PREFIX ."postmeta", $ins);
						}
												
						$wpdb->query ( "UPDATE " . DB_PREFIX . "postmeta SET meta_value='".$sale_price."' WHERE post_id = '". (int) $product_id."' AND meta_key = '_sale_price'");	
						if(!empty($sale_price)){
							$min_price = $sale_price;
							$wpdb->query ( "UPDATE " . DB_PREFIX . "postmeta SET meta_value='".$sale_price."' WHERE post_id = '". (int) $product_id."' AND meta_key = '_price'");
							$RegularPriceQuery = $wpdb->get_results ( "SELECT * FROM " . DB_PREFIX . "postmeta WHERE post_id = '". (int) $product_id."' AND meta_key = '_regular_price'" );
							if (count($RegularPriceQuery)>0) {
								$wpdb->query ( "UPDATE " . DB_PREFIX . "postmeta SET meta_value='".$product_price."' WHERE post_id = '". (int) $product_id."' AND meta_key = '_regular_price'");
							}else{
								$ins = new stdClass ();
								$ins->meta_id = NULL;
								$ins->post_id = $product_id;
								$ins->meta_key = '_regular_price';
								$ins->meta_value = $product_price;
								insertObject ( "" . DB_PREFIX ."postmeta", $ins);
							}
						}else{
							$min_price = $mPrice;
							$wpdb->query ( "UPDATE " . DB_PREFIX . "postmeta SET meta_value='".$product_price."' WHERE post_id = '". (int) $product_id."' AND (meta_key = '_regular_price' OR meta_key = '_price')");
						}
						
						
						$args_lookuptable = array(
							'onsale' => $onsale,
							'min_price' => $min_price,
							'max_price' => $mPrice
						);
						createProductForLookupTable($product_id, $args_lookuptable);				
					}
				}
				if ((isset($mPrice)) and ($mPrice > 0) and (VM_PRODUCT_VIEW_PRICE0 == 0)){
					$wpdb->query ( "UPDATE " . DB_PREFIX . "posts SET post_status='publish' where ID='". (int) $product_id."' and post_type = 'product'"); 
				}
								
				//загружаем текущие остатки
				if (isset($product_price_data->Количество)) {
					$product_in_stock =(int)$product_price_data->Количество;
				}else{						
					if (isset($product_price_data->Склад)){
						$count_in_sclad = 0;
						foreach ( $product_price_data->Склад as $warehouse){ 
							if (isset($warehouse['КоличествоНаСкладе'])){
								$count_in_sclad = $count_in_sclad + (int)$warehouse['КоличествоНаСкладе'];
							}					
						}
						$product_in_stock = $count_in_sclad;
					}else{
						$product_in_stock = 0;
					}		
				}	
				if (isset($product_price_data->Остатки->Остаток->Склад)) {
					$count_in_sclad = 0;
					foreach ($product_price_data->Остатки->Остаток->Склад as $rests){
						$rest = (isset($rests->Количество)) ? (int)$rests->Количество : 0;
						$count_in_sclad = $count_in_sclad + $rest;
					}
					$product_in_stock = $count_in_sclad;
				}
				
				if (VM_UPDATE_COUNT == 1){ 
					if (!isset($postmeta_array['_stock'])){
						$ins = new stdClass ();
						$ins->meta_id = NULL;
						$ins->post_id = $product_id;
						$ins->meta_key = '_stock';
						$ins->meta_value = $product_in_stock;
						insertObject ( "" . DB_PREFIX ."postmeta", $ins);
					}

					$wpdb->query ( "UPDATE " . DB_PREFIX . "postmeta SET meta_value='".$product_in_stock."' WHERE post_id = '". (int) $product_id."' AND meta_key = '_stock'");
					if ($product_in_stock == 0){
						$stock_status = getOutOfStockStatusObject($product_id);	
					}else{
						$stock_status = 'instock';
					}

					if (!isset($postmeta_array['_stock_status'])){
						$ins = new stdClass ();
						$ins->meta_id = NULL;
						$ins->post_id = $product_id;
						$ins->meta_key = '_stock';
						$ins->meta_value = $stock_status;
						insertObject ( "" . DB_PREFIX ."postmeta", $ins);
					}
					$wpdb->query ( "UPDATE " . DB_PREFIX . "postmeta SET meta_value='".$stock_status."' WHERE post_id = '". (int) $product_id."' AND meta_key = '_stock_status'");
					
					$args_lookuptable = array(
						'stock_quantity' => $product_in_stock,
						'stock_status'   => $stock_status
					);
					createProductForLookupTable($product_id, $args_lookuptable);
					$is_modified = true;
				}

				if ((isset($product_in_stock)) and (VM_PRODUCT_VIEW_COUNT0 == 0)){
					$status = 'draft';
					if($product_in_stock > 0){
						$status = 'publish';
					}
					if ((isset($main_price)) and ($main_price <= 0) and (VM_PRODUCT_VIEW_PRICE0 == 0)){
						$status = 'draft';
					}
					$wpdb->query ( "UPDATE " . DB_PREFIX . "posts SET post_status='".$status."' where ID='". (int) $product_id."' and post_type = 'product'"); 
					$is_modified = true;
				}

				if ($is_modified == true){
					$date_now = date('Y-m-d H:i:s');
					$wpdb->query ( "UPDATE " . DB_PREFIX . "posts SET post_modified='".$date_now."' WHERE ID = '". (int) $product_id."' AND post_type = 'product'");
					if (function_exists('wc_delete_product_transients')){
						wc_delete_product_transients( $product_id );
					}
				}
		  	}
		} 
		HeartBeat::clearElementUploadInStatusProgress($FilenameUpload, $FilePart, $type_upload);
	}
} 

function FeaturesArrayFill($offers, $ShopperGroups, $CatalogContainsChanges, $FilePart = 0) {
global $wpdb;
global $TovarIdFeatureArray;
global $languages;
global $FilenameUpload;
global $ThisPage;
global $posix;
global $StopNameCreateSvoistvaVariationArray;
$FeaturesArray = array();
$ProductId1CArray = array();
$ShopperGroupsArray = array();
$type_upload = 'feature';

	if (!isset($offers->Предложение)){
		return $FeaturesArray;
	}
	
	if (isset ( $ShopperGroups->ТипЦены )){
		foreach ($ShopperGroups->ТипЦены as $ShopperGroup ) { 
			$ShopperGroupId1c = (isset($ShopperGroup->Ид)) ? (string)$ShopperGroup->Ид : '';
			$ShopperGroupName = (isset($ShopperGroup->Наименование)) ? (string)$ShopperGroup->Наименование : '';
			$ShopperGroupsArray[$ShopperGroupId1c] = $ShopperGroupName;
		}
	}

	//$is_true = true; //ВКЛЮЧЕНО
	if (VM_DELETE_FEATURES == 1){
		//чтение характеристик номенклатуры для очистки характеристик на сайте у товара 
		$ProductId1CArray = array();
		$FeatureId1CArray = array();
		$ProductAllArray = array();
		$FeatureAllArray = array();
		foreach ( $offers->Предложение as $product_features ) { 
			$features_id_1c = (isset($product_features->Ид)) ? (string)$product_features->Ид : '';
			$pos = strrpos($features_id_1c, "#");
			if ($pos === false) { 
				//не найдена характерстика номенклатуры 
			}else{
				$str=strpos($features_id_1c, "#");
				$product_id_1c=substr($features_id_1c, 0, $str);			
				$ProductId1CArray[] = $product_id_1c;
				$FeatureId1CArray[] = $features_id_1c;
			}
		}
		//собираем id вариаций по всем товарам в файле выгрузки
		$text_query = array();
		foreach ($ProductId1CArray as $ProductId1C){
			$text_query[] = "p1.product_1c_id = '".$ProductId1C."'";
		}
		if (!empty($text_query)){
			if (count($text_query)== 1 ){
				$text_query_final = $text_query[0];	
			}else{
				$text_query_final = implode(' OR ', $text_query);
			}
			
			$ProductAllArrayQuery = $wpdb->get_results( "SELECT p1.ID AS product_id, p2.ID AS product_variation_id
			FROM " . DB_PREFIX . "posts AS p1 LEFT JOIN " . DB_PREFIX . "posts AS p2 ON p1.ID = p2.post_parent 
			WHERE (".$text_query_final.") AND p1.post_type = 'product' AND p2.post_type = 'product_variation'");
			if (count($ProductAllArrayQuery) > 0 ){
				foreach ($ProductAllArrayQuery as $ProductAllResult){
					$ProductAllArray[$ProductAllResult->product_variation_id] = $ProductAllResult->product_id;
				}
			}		
		}
		
		//собираем id вариаций по файлу выгрузки
		$text_query = array();
		foreach ($FeatureId1CArray as $FeatureId1C){
			$text_query[] = "product_1c_id = '".$FeatureId1C."'";
		}
		if (!empty($text_query)){
			if (count($text_query)== 1 ){
				$text_query_final = $text_query[0];	
			}else{
				$text_query_final = implode(' OR ', $text_query);
			}
			
			$FeatureAllArrayQuery = $wpdb->get_results( "SELECT post_parent AS product_id, ID AS product_variation_id
			FROM " . DB_PREFIX . "posts WHERE (".$text_query_final.") AND post_type = 'product_variation'");
			if (count($FeatureAllArrayQuery) > 0 ) {
				foreach ($FeatureAllArrayQuery as $FeatureAllResult){
					$FeatureAllArray[$FeatureAllResult->product_variation_id] = $FeatureAllResult->product_id;
				}
			}				
		}
		$diff_array = array_diff_assoc($ProductAllArray, $FeatureAllArray);
		if (count($diff_array) > 0) {
			foreach($diff_array as $product_variation_id => $product_id){
				deleteVariationAttributeInProductAttribute($product_id, $product_variation_id);
				DeleteProduct((int)$product_variation_id, 'вариация товара');
			}
		}

		if (count($ProductAllArray) > 0 ){
			$ProductAllArray = array_unique($ProductAllArray);
			foreach ($ProductAllArray as $product_id){
				deleteDiffAttributesInProductAttribute($product_id);
			}
		}
	}

	$last_element_upload = HeartBeat::getLastElementUpload($FilenameUpload);
	//чтение характеристик номенклатуры для заполения данных
	foreach ( $offers->Предложение as $product_features ) { 
		$features_id_1c = (isset($product_features->Ид)) ? (string)$product_features->Ид : '';
		$HeartBeatStatus = HeartBeat::getNext($FilenameUpload, $FilePart, $ThisPage, $posix, $type_upload, $features_id_1c, $last_element_upload);
		if ($HeartBeatStatus == 'next'){
			continue;
		}
		if ($HeartBeatStatus == 'false'){
			exit();
		}
		
		$product_name = (isset($product_features->Наименование)) ? (string)$product_features->Наименование : 'Наименование характеристики не задано';
		$product_SKU = (isset($product_features->Штрихкод)) ? (string)$product_features->Штрихкод : '';
		$product_SKU = formatString($product_SKU);
		$feature_name_all = (isset($product_features->Наименование)) ? (string)$product_features->Наименование : 'Наименование характеристики не задано';
		$image_feature = (isset($product_features->Картинка)) ? (string)$product_features->Картинка : '';
		$artikul = (isset($product_features->Артикул)) ? (string)$product_features->Артикул : '';

		//разбор характеристик товара
		$pos = strrpos($features_id_1c, "#");
		if ($pos === false) { 
			//не найдена характерстика номенклатуры 
		}else{		
			$str=strpos($features_id_1c, "#");
			$product_id_1c=substr($features_id_1c, 0, $str);
			$product_id_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "posts where product_1c_id = '".$product_id_1c."' AND post_type = 'product'" );	
			if (count($product_id_query)>0) {
				foreach($product_id_query as $product_id_result){
					$product_id = (int)$product_id_result->ID;	
					$product_id_name = $product_id_result->post_title;	
					$product_id_guid = $product_id_result->guid;	
				}
				installProductTypeId('variable', $product_id);
					
				$name_feature = str_replace(htmlspecialchars_decode($product_id_name), "", $product_name);
				$name_feature = str_replace("(", "", $name_feature);
				$name_feature = str_replace(")", "", $name_feature);
				$name_feature = formatString(trim($name_feature));
					
				$property_features_name = '';
				$property_features_value = '';
				
				$property_array = array();
				$name_option = VM_NAME_OPTION;
				if (empty ($name_option)){
					if (isset($product_features->ХарактеристикиТовара->ХарактеристикаТовара)){
						foreach ( $product_features->ХарактеристикиТовара->ХарактеристикаТовара as $property_features ) { 
							if (isset($property_features->Наименование)){
								$property_features_name = (string)$property_features->Наименование;
								$pos_features_name = strrpos($property_features_name, "(");
								if (!$pos_features_name === false){
									$property_features_name = trim(mb_substr($property_features_name,0,mb_strpos($property_features_name,'(')));
									$property_features_name = formatStringAttributeValue($property_features_name);
								}
								if (!in_array($property_features_name, $StopNameCreateSvoistvaVariationArray)){
									if (isset($property_features->Значение)){
										$property_features_value = (string)$property_features->Значение;
										$property_features_value = formatStringAttributeValue($property_features_value);
										$property_array[$property_features_name] = trim($property_features_value);
									}
								}
								if (trim($property_features_name) == 'Артикул'){
									$artikul = (string)$property_features->Значение;
								}
							}
						}
					}
					if (!empty($property_array)){
						foreach($property_array as $key=>$value){
							insertAttributeValue($key, $value, $key, $product_id, 1);
						}
					}
				}
				if (empty($property_array)){
					if (!empty($name_option)){
						$property_features_name = trim(VM_NAME_OPTION);
						$property_features_name = formatStringAttributeValue($property_features_name);
					}else{
						$property_features_name = 'Характеристика товара';
					}
					insertAttributeValue($property_features_name, $name_feature, $property_features_name, $product_id, 1);
					$property_array[$property_features_name] = $name_feature;
				}

				if (empty($product_SKU)) {
					$product_SKU = $artikul;
				}
								
				//получение цены и остатка
				$feature_price = 0;
				$sale_price = '';	
				if (isset($product_features->Цены->Цена)){
					$price_array = array();
					foreach ($product_features->Цены->Цена as $price_data ) {
						$mPriceStr = (isset($price_data->ЦенаЗаЕдиницу)) ? (string)$price_data->ЦенаЗаЕдиницу : '0';
						$mPrice = str_replace(",",".",$mPriceStr); //замена запятых на точку
						$mPrice = (float)preg_replace("/[^0-9\.]/", '', $mPrice);
						$mCurrency = (isset($price_data->Валюта)) ? (string)$price_data->Валюта : 'RUB';				
						$shopper_group_id_1c =(string)$price_data->ИдТипаЦены;					
						if (isset($ShopperGroupsArray[$shopper_group_id_1c])){
							if ((!empty(VM_SALE_PRICE_1C)) and ($ShopperGroupsArray[$shopper_group_id_1c] == VM_SALE_PRICE_1C)){
								if ($mPrice == 0){
									$sale_price = '';
								}else{
									$sale_price = $mPrice;
								}
							}else{
								$price_array[] = $mPrice;
							}
						}else{
							$price_array[] = $mPrice; //при выгрузке только изменений нет справочника видов цен
						}
					}
					if(!empty($price_array)){
						$feature_price = max($price_array);	
					}
				}
				
				$feature_count = 0;
				if (isset($product_features->Количество)) {
					$feature_count =(int)$product_features->Количество;
				}else{						
					if (isset($product_features->Склад)){
						$count_in_sclad = 0;
						foreach ( $product_features->Склад as $warehouse){ 
							if (isset($warehouse['КоличествоНаСкладе'])){
								$count_in_sclad = $count_in_sclad + (int)$warehouse['КоличествоНаСкладе'];
							}					
						}
						$feature_count = $count_in_sclad;
					}else{
						$feature_count = 0;
					}		
				}
				$stock_status = getOutOfStockStatusObject($product_id);	
				if ($feature_count > 0){
					$stock_status = 'instock';
				}
			
				//создаем вариацию 
				$features_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "posts where product_1c_id = '".$features_id_1c."' AND post_type = 'product_variation'" );	
				if (count($features_query)>0) {
					foreach($features_query as $features_result){
						$feature_id = (int)$features_result->ID;
					}
					
					$wpdb->query("DELETE FROM " . DB_PREFIX . "postmeta WHERE meta_key LIKE 'attribute_%' AND post_id = '".$feature_id."'");
					
					$updatePostsFieldArray = array();
					$updatePostmetaFieldArray = array();
					if (VM_UPDATE_NAME == 1){
						$updatePostsFieldArray['post_title'] = $feature_name_all;
					}
					$updatePostsFieldArray['post_modified'] = date('Y-m-d H:i:s');
					$updatePostsFieldArray['post_modified_gmt'] = date('Y-m-d H:i:s');
					$updatePostsFieldArray['post_parent'] = $product_id;

					//if ((isset($feature_count)) and (VM_PRODUCT_VIEW_COUNT0 == 0)){ //если нужно скрывать вариации, при этом вариации также скрываются и в админке 
					//	$status = 'draft';
					//	if($feature_count > 0){
					//		$status = 'publish';
					//	}
					//	if ((isset($feature_price)) and ($feature_price <= 0) and (VM_PRODUCT_VIEW_PRICE0 == 0)){
					//		$status = 'draft';
					//	}
					//	$updatePostsFieldArray['post_status'] = $status;
					//}
					
					if (VM_UPDATE_ARTIKUL == 1){
						$updatePostmetaFieldArray['_sku'] = $product_SKU;
						$args_lookuptable = array(
							'sku' => $product_SKU
						);
						createProductForLookupTable($feature_id, $args_lookuptable);
					}	
					if (VM_UPDATE_PRICE == 1){
						if ((!empty($sale_price)) and ($sale_price > 0)){
							$updatePostmetaFieldArray['_price'] = $sale_price;	
						}else{
							$updatePostmetaFieldArray['_price'] = $feature_price;
						}
						$updatePostmetaFieldArray['_regular_price'] = $feature_price;
						$updatePostmetaFieldArray['_sale_price'] = $sale_price;
						
						$args_lookuptable = array();
						if (!empty($sale_price)){
							$args_lookuptable['onsale'] =  1;
							$args_lookuptable['min_price'] = $sale_price;
							$args_lookuptable['max_price'] = $feature_price;
						}else{
							$args_lookuptable['onsale'] =  0;
							$args_lookuptable['min_price'] = $feature_price;
							$args_lookuptable['max_price'] = $feature_price;
						}
						createProductForLookupTable($feature_id, $args_lookuptable);	
					}
					if (VM_UPDATE_COUNT == 1){
						$updatePostmetaFieldArray['_stock'] = $feature_count;
						$updatePostmetaFieldArray['_stock_status'] = $stock_status;
						$args_lookuptable = array(
							'stock_quantity' => $feature_count,
							'stock_status' 	 => $stock_status
						);
						createProductForLookupTable($feature_id, $args_lookuptable);
					}		
					$text_query = array();
					foreach ($updatePostsFieldArray as $updatePostsFieldKey => $updatePostsFieldValue){
						$text_query[] = $updatePostsFieldKey." = '".$updatePostsFieldValue."'";
					}
					if (!empty($text_query)){
						if (count($text_query)== 1 ){
							$text_query_final = $text_query[0];	
						}else{
							$text_query_final = implode(' , ', $text_query);
						}
						$product_update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "posts SET ".$text_query_final." WHERE ID='". (int)$feature_id."'");
					}
					unset($text_query);
					foreach ($updatePostmetaFieldArray as $updatePostmetaFieldKey => $updatePostmetaFieldValue){
						$product_update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "postmeta SET meta_value = '".$updatePostmetaFieldValue."' WHERE post_id='". (int)$feature_id."' AND meta_key = '".$updatePostmetaFieldKey."'");
					}
					
					$internal_meta_keys = array();
					foreach($property_array as $key => $value){
						if (VM_GROUP_ATTRIBUTE == 1) {
							$group_attribute_query  = $wpdb->get_results( "SELECT attribute_name FROM " . DB_PREFIX . "woocommerce_attribute_taxonomies where attribute_1c_id = '".$key."'" );	
							if (count($group_attribute_query)>0) {
								foreach($group_attribute_query as $group_attribute_result){
									$key_attribute = (string)$group_attribute_result->attribute_name;
									$key_attribute = 'pa_'.$key_attribute;
								}
								$term_property = getTermProperty($value, $key_attribute);	
								if (isset($term_property['slug'])) {
									$value = $term_property['slug'];
								}
							}else{
								$key_attribute = sanitize_title($key);	
							}
						}else{
							$key_attribute = sanitize_title($key);	
						}
						$attribute = 'attribute_'.$key_attribute;
						$internal_meta_keys[$attribute] = $value;
					}
					
					$text_query_arr = array();
					foreach ($internal_meta_keys as $key => $value){
						$text_query_arr[] = "(NULL, '".$feature_id."', '".$key."', '".$value."')";
					}
					if (!empty($text_query_arr)) {
						if (count($text_query_arr)== 1 ){
							$text_query_final = $text_query_arr[0];	
						}else{
							$text_query_final = implode(',', $text_query_arr);
						} 
						$wpdb->query( "INSERT INTO " . DB_PREFIX . "postmeta (meta_id, post_id, meta_key, meta_value) VALUES ".$text_query_final."" );
					}
					unset($updatePostsFieldArray, $updatePostmetaFieldArray);

					//загрузка картинок характеристики (для УНФ 1.6.4 и старше)	
					if ((IMAGE_LOAD == 1) and (VM_UPDATE_IMAGE == 1)){						
						if ($CatalogContainsChanges == 'false') {
							DeleteImages ($feature_id);
						}
						
						if ($CatalogContainsChanges == 'true') {
							//не удаляем картинки, если это изменение пакета предложений
						}
						loadImagesForProduct($product_features, $product_name, $feature_id);
					}
				}else{
					$ins = new stdClass ();
					$ins->ID = NULL;
					$ins->post_author = 1;
					$ins->post_date = date('Y-m-d H:i:s');
					$ins->post_date_gmt = date('Y-m-d H:i:s');
					$ins->post_content = '';
					$ins->post_title = $feature_name_all;
					$ins->post_excerpt = '';
					$ins->post_status = 'publish';
					$ins->comment_status = 'open';
					$ins->ping_status = 'closed';
					$ins->post_password = '';
					$ins->post_name = '';
					$ins->to_ping = '';
					$ins->pinged = '';
					$ins->post_modified = date('Y-m-d H:i:s');
					$ins->post_modified_gmt = date('Y-m-d H:i:s');
					$ins->post_content_filtered = '';
					$ins->post_parent = $product_id;
					$ins->guid = $product_id_guid;
					$ins->menu_order = 1;
					$ins->post_type = 'product_variation';
					$ins->post_mime_type = '';
					$ins->comment_count = 0;
					$ins->product_1c_id = $features_id_1c;
					insertObject ( "" . DB_PREFIX ."posts", $ins, 'ID' );
					$feature_id = (int)$ins->ID;
							
					update_url_alias ($feature_id, $product_name, 'posts', 'post_name', 'ID');
					$array_null = array();
					$array_serialize = serialize($array_null);

					if ((!empty($sale_price)) and ($sale_price > 0)){
						$setup_price = $sale_price;
					}else{
						$setup_price = $feature_price;
					}
					
					$args_lookuptable = array();
					if (!empty($sale_price)){
						$args_lookuptable['onsale'] =  1;
						$args_lookuptable['min_price'] = $sale_price;
						$args_lookuptable['max_price'] = $feature_price;
					}else{
						$args_lookuptable['onsale'] =  0;
						$args_lookuptable['min_price'] = $feature_price;
						$args_lookuptable['max_price'] = $feature_price;
					}
					$args_lookuptable['sku'] =  $product_SKU;
					$args_lookuptable['total_sales'] =  0;
					createProductForLookupTable($feature_id, $args_lookuptable);

					$internal_meta_keys = array(
						'_sku'  					=> $product_SKU,
						'_price'  					=> $setup_price,
						'_regular_price'  			=> $feature_price,
						'_sale_price'   			=> $sale_price,
						'_sale_price_dates_from'  	=> '',
						'_sale_price_dates_to'   	=> '',
						'total_sales'   			=> '1',
						'_tax_status'   			=> 'taxable',
						'_tax_class'   			    => '',
						'_manage_stock'   			=> 'yes',
						'_stock'   			        => $feature_count,
						'_stock_status'   			=> $stock_status,
						'_backorders'   			=> VM_BACKORDERS_VALUE,
						'_low_stock_amount'   		=> '0',
						'_sold_individually'   		=> 'no',
						'_weight'   			    => '',
						'_length'   				=> '',
						'_width'   					=> '',
						'_height'   				=> '',
						'_upsell_ids' 				=> $array_serialize,
						'_crosssell_ids' 			=> $array_serialize,
						'_purchase_note' 			=> '', 
						'_default_attributes' 		=> $array_serialize,
						'_product_attributes' 		=> $array_serialize,
						'_virtual' 					=> 'no',
						'_downloadable' 			=> 'no',
						'_download_limit' 			=> '-1',
						'_download_expiry' 			=> '-1',
						'_wc_rating_count' 			=> $array_serialize,
						'_wc_average_rating' 		=> '0',
						'_wc_review_count' 			=> '0',
						'_thumbnail_id'			    => '0',
						'_product_image_gallery' 	=> '',
						'_product_version' 			=> '3.5.3',
						'_edit_last'  				=> '1',
						'_edit_lock' 				=> '',
						'_variation_description' 	=> '',
					);
					
					foreach($property_array as $key => $value){
						if (VM_GROUP_ATTRIBUTE == 1) {
							$group_attribute_query  = $wpdb->get_results( "SELECT attribute_name FROM " . DB_PREFIX . "woocommerce_attribute_taxonomies where attribute_1c_id = '".$key."'" );	
							if (count($group_attribute_query)>0) {
								foreach($group_attribute_query as $group_attribute_result){
									$key_attribute = (string)$group_attribute_result->attribute_name;
									$key_attribute = 'pa_'.$key_attribute;
								}		
								$term_property = getTermProperty($value, $key_attribute);	
								if (isset($term_property['slug'])) {
									$value = $term_property['slug'];
								}	
							}else{
								$key_attribute = sanitize_title($key);	
							}
						}else{
							$key_attribute = sanitize_title($key);	
						}	
						$attribute = 'attribute_'.$key_attribute;
						$internal_meta_keys[$attribute] = $value;
					}
					
					$text_query_arr = array();
					foreach ($internal_meta_keys as $key => $value){
						$text_query_arr[] = "(NULL, '".$feature_id."', '".$key."', '".$value."')";
					}
					if (!empty($text_query_arr)) {
						if (count($text_query_arr)== 1 ){
							$text_query_final = $text_query_arr[0];	
						}else{
							$text_query_final = implode(',', $text_query_arr);
						} 
						$wpdb->query( "INSERT INTO " . DB_PREFIX . "postmeta (meta_id, post_id, meta_key, meta_value) VALUES ".$text_query_final."" );
					}

					//загрузка картинок характеристики (для УНФ 1.6.4 и старше)	
					if ((IMAGE_LOAD == 1) and (VM_UPDATE_IMAGE == 1)){
						DeleteImages ($feature_id);
						if (!empty($image_feature)){
							loadImagesForProduct($product_features, $product_name, $feature_id);
						}
					}
				}
				//loadImagesForFeature($image_feature, $feature_name_all, $option_value_id, $product_option_value_id);
				//формируем массив характеристик номенклатуры
				$FeaturesArray[$feature_id] = $product_id;
			}
		}
	}
	if (!empty($FeaturesArray)){
		foreach ($FeaturesArray as $key_feature_id => $value_product_id){
			$product_attributes = getProductAttribute ($value_product_id);
			if (!empty($product_attributes)){
				foreach($product_attributes as $key => $value){
					$feature_query = $wpdb->get_results ( "SELECT * FROM " . DB_PREFIX ."postmeta where post_id = '" . $key_feature_id . "'" );
					if (count($feature_query)>0) {
						$attribute_key = 'attribute_'.$key;
						$attribute_use = false;
						foreach($feature_query as $meta_result){
							$meta_key = $meta_result->meta_key;;
							if($meta_key == $attribute_key){
								$attribute_use = true;
								//--
								$meta_value = $meta_result->meta_value;
								if (isset($value['value'])){
									$parts_value   = explode( ' | ' , $value['value'] );
									if (empty($parts_value)){
										if ( $value['value'] <> $meta_value){
											addAttributeValue($key, $meta_value, $value_product_id);
										}
									}else{
										$find = false;
										foreach($parts_value as $part){
											if($part == $meta_value){
												$find = true;
											}
										}
										if ($find == false){
											addAttributeValue($key, $meta_value, $value_product_id);
										}
									}
								}
								//--
							}
						}
						if ($attribute_use == false){
							if (isset($value['value'])){
								$wpdb->query( "INSERT INTO " . DB_PREFIX . "postmeta (meta_id, post_id, meta_key, meta_value) VALUES (NULL, '".$key_feature_id."', '".$attribute_key."', '".$value['value']."')" );
							}
						}
					}
				}
			}
		}
	}
	HeartBeat::clearElementUploadInStatusProgress($FilenameUpload, $FilePart, $type_upload);
	return $FeaturesArray;	
}

function update_price_and_quantity_features($FeaturesArray) {
global $wpdb;
	$ProductArray = array();	
	if (!empty($FeaturesArray)){
		foreach($FeaturesArray as $product_id){
			$ProductArray[] = $product_id;
		}
		$ProductArray = array_unique($ProductArray);
		foreach($ProductArray as $product_id){
			$product_price_array = array();
			$product_count = 0;
			$product_price = 0;
			$product_query  = $wpdb->get_results ( "SELECT p.ID as post_id, pm.meta_key as meta_key, pm.meta_value as meta_value FROM " . DB_PREFIX . "posts AS p LEFT OUTER JOIN " . DB_PREFIX ."postmeta AS pm ON p.ID = pm.post_id where p.post_parent = '" . $product_id . "' AND p.post_type = 'product_variation'" );
			if (count($product_query)>0) {
				$product_count = 0;
				$product_price = 0;
				foreach($product_query as $meta_result){
					$meta_key = $meta_result->meta_key;					
					$meta_value = $meta_result->meta_value;
					if($meta_key == '_stock'){
						$product_count = $product_count + $meta_value;
					}
					if($meta_key == '_price'){
						if ($meta_value > 0){
							$product_price_array[] = $meta_value;
						}
					}
				}
			}
			if(!empty($product_price_array)){
				$product_price = min($product_price_array);
				$product_max_price = max($product_price_array);
			}
			$is_modified = false;
			$updatePostmetaFieldArray = array();
			$updatePostsFieldArray = array();
			//обновление количества родителя
			if (VM_COUNT_PARENT_FEATURES == 1){
				$updatePostmetaFieldArray['_stock'] = $product_count;
				$stock_status = getOutOfStockStatusObject($product_id);
				if ($product_count > 0){
					$stock_status = 'instock';
				}
				$updatePostmetaFieldArray['_stock_status'] = $stock_status;
				
				if (VM_PRODUCT_VIEW_COUNT0 == 0) { //отключаем отображение товара, до момента установки кол-ва на товар
					$post_status = 'draft';
					if ($product_count > 0){
						$post_status = 'publish';
					}
					$updatePostsFieldArray['post_status'] = $post_status;
				}
				
				$args_lookuptable = array(
					'stock_quantity' => $product_count,
					'stock_status' => $stock_status
				);
				createProductForLookupTable($product_id, $args_lookuptable);
				
				$is_modified = true;
			}
			//обновление цены родителя
			if (VM_PRICE_PARENT_FEATURES == 1){
				$updatePostmetaFieldArray['_price'] = $product_price;
				$updatePostmetaFieldArray['_regular_price'] = '';
				if (VM_PRODUCT_VIEW_PRICE0 == 0) { //отключаем отображение товара, до момента установки цены на товар
					$post_status = 'draft';
					if ($product_price > 0){
						if ((VM_PRODUCT_VIEW_COUNT0 == 0) and ($product_count <= 0)){
							$post_status = 'draft';
						}else{
							$post_status = 'publish';
						}	
					}
					$updatePostsFieldArray['post_status'] = $post_status;
				}
				
				$args_lookuptable = array();
				if (isset($product_max_price)){
					$args_lookuptable['max_price'] = $product_max_price;
				}else{
					$args_lookuptable['max_price'] = $product_price;
				}
				$args_lookuptable['min_price'] = $product_price;
				createProductForLookupTable($product_id, $args_lookuptable);
				
				$is_modified = true;
			}
			if(!empty($updatePostmetaFieldArray)){
				foreach ($updatePostmetaFieldArray as $updatePostmetaFieldKey => $updatePostmetaFieldValue){
					$product_update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "postmeta SET meta_value = '".$updatePostmetaFieldValue."' WHERE post_id='". (int)$product_id."' AND meta_key = '".$updatePostmetaFieldKey."'");
				}
			}
			if(!empty($updatePostsFieldArray)){
				$text_query = array();
				foreach ($updatePostsFieldArray as $updatePostsFieldKey => $updatePostsFieldValue){
					$text_query[] = $updatePostsFieldKey." = '".$updatePostsFieldValue."'";
				}
				if (!empty($text_query)){
					if (count($text_query)== 1 ){
						$text_query_final = $text_query[0];	
					}else{
						$text_query_final = implode(' , ', $text_query);
					}
					$product_update  = $wpdb->query ( "UPDATE " . DB_PREFIX . "posts SET ".$text_query_final." WHERE ID='". (int)$product_id."'");
				}
				unset($text_query);
			}
			if($is_modified == true){
				if (function_exists('wc_delete_product_transients')){
					wc_delete_product_transients( $product_id ); 
				}
			}
		}
	}	
}

function GetOrders() {
global $wpdb;

	$order_status_ozhidanie  = OrderStatusReturn ('wc-on-hold');	
	$order_status_otmeneno   = OrderStatusReturn ('wc-cancelled');
	$order_status_vobrabotke = OrderStatusReturn ('wc-processing');
	$order_status_oplacheno  = VM_ORDER_STATUS_PAID;
	$order_status_dostavleno = VM_ORDER_STATUS_COMPLETE;

	$date_up = '1990-01-01 00:00:00';
	$order_date_load_query = $wpdb->get_results("SELECT value FROM " . DB_PREFIX . "setting_exchange_1c WHERE name_setting = 'VM_ORDER_DATE_LOAD'"); 
	if (count($order_date_load_query)>0){
		foreach($order_date_load_query as $order_date_load_result){
			$order_date_load = $order_date_load_result->value;
		}
		if (!empty($order_date_load)){
			$date_up = $order_date_load.' 00:00:00';
		}
	}
	$OrdersArray = array(); 
	$count_orders = 0;
	$text_query_final = getAllowStatuses('`post_status`');
	$orders_query_text = "SELECT * FROM `" . DB_PREFIX . "posts` WHERE `post_type` = 'shop_order' AND `post_date` > '".$date_up."' AND (".$text_query_final.")";
	//$orders_query  = $wpdb->get_results( "SELECT * FROM `" . DB_PREFIX . "posts` WHERE `post_type` = 'shop_order' AND `ID` = '51312'");
	$orders_query  = $wpdb->get_results($orders_query_text);	
	if (count($orders_query)>0){
		foreach ($orders_query as $zakazy){
			$count_orders = $count_orders + 1;
			
			$order_info = array();
			$order_info_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "postmeta WHERE post_id = '".(int)$zakazy->ID."'");
			if (count($order_info_query)>0){
				foreach($order_info_query as $order_info_result){
					$order_info[$order_info_result->meta_key] = $order_info_result->meta_value;
				}
			}	
			
			$order_id 	     = (isset($zakazy->ID)) ? (int)$zakazy->ID : 0;
			$order_status_id = (isset($zakazy->post_status)) ? $zakazy->post_status : 0;
			$order_number    = (isset($order_info['_alg_wc_custom_order_number'])) ? (string)$order_info['_alg_wc_custom_order_number'] : $order_id;
			$order_key       = (isset($order_info['_order_key'])) ? (string)$order_info['_order_key'] : '';
			$order_total     = (isset($order_info['_order_total'])) ? (float)$order_info['_order_total'] : 0;
			$date_order      = (isset($zakazy->post_date)) ? strtotime($zakazy->post_date) : date("Y-m-d H:i:s");
			$date            = date("Y-m-d", $date_order);
			$time            = date("H:i:s", $date_order);
			
			$order_number    = formatStringForXML($order_number);
			$order_key       = formatStringForXML($order_key);
			
			$OrdersArray[$order_number]['Ид'] = $order_number;
			$OrdersArray[$order_number]['Номер'] = $order_number;		
			$OrdersArray[$order_number]['Дата'] = $date;
			$OrdersArray[$order_number]['Время'] = $time;
			$OrdersArray[$order_number]['ХозОперация'] = "Заказ товара";
			$OrdersArray[$order_number]['Роль'] = "Продавец";
			$OrdersArray[$order_number]['Сумма'] = $order_total;
			$OrdersArray[$order_number]['Курс'] = "1";
			
			//Валюта документа
			if (VM_CURRENCY == 1){
				$val = 'RUB';
				$currency_query = $wpdb->get_results("SELECT `option_value` FROM `" . DB_PREFIX . "options` WHERE `option_name` = 'woocommerce_currency'"); 
				if (count($currency_query)>0){
					foreach($currency_query as $currency){
						$val = $currency->option_value;
					}
				}
				$val = getRightNameCurrency($val);
				$OrdersArray[$order_number]['Валюта'] = $val;	
			}

			//Контрагент
			$customer_id         = '';
			$customer_first_name = (isset($order_info['_billing_first_name'])) ? (string)$order_info['_billing_first_name'] : '';
			$customer_last_name  = (isset($order_info['_billing_last_name'])) ? (string)$order_info['_billing_last_name'] : '';
			$customer_email      = (isset($order_info['_billing_email'])) ? (string)$order_info['_billing_email'] : 'non@email.com';
			$customer_telephone  = (isset($order_info['_billing_phone'])) ? (string)$order_info['_billing_phone'] : '';
			$customer_fax        = (isset($order_info['_billing_fax'])) ? (string)$order_info['_billing_fax'] : '';
			$customer_off_name   = (isset($order_info['_billing_company'])) ? (string)$order_info['_billing_company'] : '';
			$member              = (isset($order_info['_shipping_company'])) ? (string)$order_info['_shipping_company'] : '';
			
			$customer_first_name = formatStringForXML($customer_first_name);
			$customer_last_name  = formatStringForXML($customer_last_name);
			$customer_email      = formatStringForXML($customer_email);
			$customer_telephone  = formatStringForXML($customer_telephone);
			$customer_off_name   = formatStringForXML($customer_off_name);
			$member              = formatStringForXML($member);
			
			
			$FIO = $customer_first_name . " " . $customer_last_name;			
			$FIO_no_spacing = str_replace(' ', '', $FIO);
			if (empty($FIO_no_spacing)) {
				$FIO =  "Покупатель с сайта";
			}
			if (!empty($customer_email)){
				$FIO = $FIO.' ('.$customer_email.')';
			}
			
			if (empty($customer_off_name)){
				$OrdersArray[$order_number]['Контрагент']['Ид'] = $FIO;
				$OrdersArray[$order_number]['Контрагент']['Наименование'] = $FIO;
				$OrdersArray[$order_number]['Контрагент']['ПолноеНаименование'] = $FIO;
			}else{
				$OrdersArray[$order_number]['Контрагент']['Ид'] = $customer_off_name;
				$OrdersArray[$order_number]['Контрагент']['Наименование'] = $customer_off_name;
				$OrdersArray[$order_number]['Контрагент']['ПолноеНаименование'] = $customer_off_name;
				$OrdersArray[$order_number]['Контрагент']['ОфициальноеНаименование'] = $customer_off_name;
			}
			
			//Контакты
			if (!empty($customer_telephone)){
				$OrdersArray[$order_number]['Контрагент']['Телефон']['Представление'] = $customer_telephone;
				$OrdersArray[$order_number]['Контрагент']['Контакт']['ТелефонРабочий'] = $customer_telephone;
			}
			if (!empty($customer_email)){
				$OrdersArray[$order_number]['Контрагент']['email']['Представление']   = $customer_email;
				$OrdersArray[$order_number]['Контрагент']['Контакт']['Почта'] = $customer_email;
			}
			if (!empty($customer_fax)){
				$OrdersArray[$order_number]['Контрагент']['Факс']['Представление'] = $customer_fax;
				$OrdersArray[$order_number]['Контрагент']['Контакт']['Факс'] = $customer_fax;
			}
			
			//Представители
			if (!empty($member)){
				$OrdersArray[$order_number]['Контрагент']['Представитель'] = $member;
			}
						
			//Юридический адрес
			$country = ((isset($order_info['_billing_country'])) and (!empty($order_info['_billing_country']))) ? (string)$order_info['_billing_country'] : 'Россия';
			$country = getRightNameCountry($country);
			$postcode = ((isset($order_info['_billing_postcode'])) and (!empty($order_info['_billing_postcode']))) ? (string)$order_info['_billing_postcode'] : '';
			$state = ((isset($order_info['_billing_state'])) and (!empty($order_info['_billing_state']))) ? (string)$order_info['_billing_state'] : '';
			$city = ((isset($order_info['_billing_city'])) and (!empty($order_info['_billing_city']))) ? (string)$order_info['_billing_city'] : '';
			$address_1 = ((isset($order_info['_billing_address_1'])) and (!empty($order_info['_billing_address_1']))) ? (string)$order_info['_billing_address_1'] : '';
			$address_2 = ((isset($order_info['_billing_address_2'])) and (!empty($order_info['_billing_address_2']))) ? (string)$order_info['_billing_address_2'] : '';

			$country   = formatStringForXML($country);
			$postcode  = formatStringForXML($postcode);
			$state     = formatStringForXML($state);
			$city      = formatStringForXML($city);
			$address_1 = formatStringForXML($address_1);
			$address_2 = formatStringForXML($address_2);
			
			
			$address = array();	
			if (!empty($postcode)) { $address[] = $postcode;}
			if (!empty($country))  { $address[] = $country;}
			if (!empty($state))    { $address[] = $state;}
			if (!empty($city))     { $address[] = $city;}
			if (!empty($address_1)){ $address[] = $address_1;}
			if (!empty($address_2)){ $address[] = $address_2;}		
			$presentment = implode(', ', $address);
			
			$OrdersArray[$order_number]['Контрагент']['АдресРегистрации']['Представление']   = $presentment;
			$OrdersArray[$order_number]['Контрагент']['АдресРегистрации']['Страна']          = $country;
			$OrdersArray[$order_number]['Контрагент']['АдресРегистрации']['Регион']          = $state;
			$OrdersArray[$order_number]['Контрагент']['АдресРегистрации']['Почтовый индекс'] = $postcode;
			$OrdersArray[$order_number]['Контрагент']['АдресРегистрации']['Город']           = $city;
			$OrdersArray[$order_number]['Контрагент']['АдресРегистрации']['Улица']           = $address_1;
			
			$OrdersArray[$order_number]['Контрагент']['ЮридическийАдрес']['Представление']   = $presentment;
			$OrdersArray[$order_number]['Контрагент']['ЮридическийАдрес']['Страна']          = $country;
			$OrdersArray[$order_number]['Контрагент']['ЮридическийАдрес']['Регион']          = $state;
			$OrdersArray[$order_number]['Контрагент']['ЮридическийАдрес']['Почтовый индекс'] = $postcode;
			$OrdersArray[$order_number]['Контрагент']['ЮридическийАдрес']['Город']           = $city;
			$OrdersArray[$order_number]['Контрагент']['ЮридическийАдрес']['Улица']           = $address_1;
			
			//Фактический адрес
			$country = ((isset($order_info['_shipping_country'])) and (!empty($order_info['_shipping_country']))) ? (string)$order_info['_shipping_country'] : 'Россия';
			$country = getRightNameCountry($country);
			$postcode = ((isset($order_info['_shipping_postcode'])) and (!empty($order_info['_shipping_postcode']))) ? (string)$order_info['_shipping_postcode'] : '';
			$state = ((isset($order_info['_shipping_state'])) and (!empty($order_info['_shipping_state']))) ? (string)$order_info['_shipping_state'] : '';
			$city = ((isset($order_info['_shipping_city'])) and (!empty($order_info['_shipping_city']))) ? (string)$order_info['_shipping_city'] : '';
			$address_1 = ((isset($order_info['_shipping_address_1'])) and (!empty($order_info['_shipping_address_1']))) ? (string)$order_info['_shipping_address_1'] : '';
			$address_2 = ((isset($order_info['_shipping_address_2'])) and (!empty($order_info['_shipping_address_2']))) ? (string)$order_info['_shipping_address_2'] : '';

			$country   = formatStringForXML($country);
			$postcode  = formatStringForXML($postcode);
			$state     = formatStringForXML($state);
			$city      = formatStringForXML($city);
			$address_1 = formatStringForXML($address_1);
			$address_2 = formatStringForXML($address_2);
			
			$address = array();	
			if (!empty($postcode)) { $address[] = $postcode;}
			if (!empty($country))  { $address[] = $country;}
			if (!empty($state))    { $address[] = $state;}
			if (!empty($city))     { $address[] = $city;}
			if (!empty($address_1)){ $address[] = $address_1;}
			if (!empty($address_2)){ $address[] = $address_2;}		
			$presentment = implode(', ', $address);
			
			$OrdersArray[$order_number]['Контрагент']['Адрес']['Представление']   = $presentment;
			$OrdersArray[$order_number]['Контрагент']['Адрес']['Страна']          = $country;
			$OrdersArray[$order_number]['Контрагент']['Адрес']['Регион']          = $state;
			$OrdersArray[$order_number]['Контрагент']['Адрес']['Почтовый индекс'] = $postcode;
			$OrdersArray[$order_number]['Контрагент']['Адрес']['Город']           = $city;
			$OrdersArray[$order_number]['Контрагент']['Адрес']['Улица']           = $address_1;
			
			
			
			//Заполнение поля комментарий
			$status_order    =  OrderStatusInfo($order_status_id);
			$user_comment    = (isset($zakazy->post_excerpt)) ? strip_tags($zakazy->post_excerpt) : '';
			$payment_method  = (isset($order_info['_payment_method_title'])) ? (string)$order_info['_payment_method_title'] : '';
			$shipping_method = (isset($zakazy->ID)) ? getShippingMethodOrder($zakazy->ID) : '';	
			$shipping_price  = (isset($order_info['_order_shipping'])) ? (float)$order_info['_order_shipping'] : 0;
			
			$status_order    = formatStringForXML($status_order);
			$user_comment    = formatStringForXML($user_comment);
			$payment_method  = formatStringForXML($payment_method);
			$shipping_method = formatStringForXML($shipping_method);
				
			$comment = '';
			$comment = $comment . "Статус на сайте: ". $status_order ."; \n";
			if (!empty($payment_method)) {
				$comment = $comment . "Оплата: ". $payment_method ."; \n";	
			}	
			if (!empty($shipping_method)) {
				$comment = $comment . "Доставка: ". $shipping_method ."; \n";
				if ($shipping_price > 0){
					if (!empty($presentment)){ 
						$comment = $comment . "Адрес доставки: ". $presentment ."; \n";	
					}
				}
			}
			$comment = $comment . "Комментарий: ". $user_comment ." ";	
			$OrdersArray[$order_number]['Комментарий'] = $comment;
			
			//Разбор товаров
			$products_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "woocommerce_order_items WHERE order_id = '".(int)$order_id."' AND order_item_type = 'line_item'" );		
			$kolvo_tovarov = 0;
			foreach ($products_query as $razbor_zakaza_col) {$kolvo_tovarov = $kolvo_tovarov + 1;}
			foreach ($products_query as $razbor_zakaza_t) {	
				$info_product = array();
				$product_id = (int)$razbor_zakaza_t->order_item_id;
				$info_products_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "woocommerce_order_itemmeta WHERE order_item_id = '".(int)$product_id."'" );
				if (count($info_products_query)>0){
					foreach($info_products_query as $info_products_result){
						$info_product[$info_products_result->meta_key] = $info_products_result->meta_value;
					}
				}		
				$product_id_easy  = (isset($info_product['_product_id'])) ? (int)$info_product['_product_id'] : '';
				$variation_id  = ((isset($info_product['_variation_id'])) and ($info_product['_variation_id'] > 0)) ? (int)$info_product['_variation_id'] : '';
				if (!empty($product_id_easy)){
					$product_id = $product_id_easy;
					$post_type = 'product';
					if (!empty($variation_id)){
						$product_id = $info_product['_variation_id'];
						$post_type = 'product_variation';
					}
					
					$info_products_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "postmeta WHERE post_id = '".(int)$product_id."'" );
					if (count($info_products_query)>0){
						foreach($info_products_query as $info_products_result){
							$info_product[$info_products_result->meta_key] = $info_products_result->meta_value;
						}
					}
					$info_products_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "posts WHERE ID = '".(int)$product_id."' AND post_type = '".$post_type."'" );
					if (count($info_products_query)>0){
						foreach($info_products_query as $info_products_result){
							$info_product['product_1c_id'] = $info_products_result->product_1c_id;
							$info_product['post_title'] = $info_products_result->post_title;
						}
					}
				}
				
				if (isset($info_product['post_title'])){
					$product_name_tovar = $info_product['post_title'];
				}else{
					$product_name_tovar = $razbor_zakaza_t->order_item_name;
				}
				$product_name_tovar = formatStringForXML($product_name_tovar);
				if(empty($product_name_tovar)){
					$product_name_tovar = 'Наименование не задано';
				}
				
				$product_1c_id   = ((isset($info_product['product_1c_id'])) and (!empty($info_product['product_1c_id'])))? (string)$info_product['product_1c_id'] : $product_name_tovar;
				$product_price   = (isset($info_product['_line_total'])) ? (float)$info_product['_line_total'] : 0;
				$product_tax     = (isset($info_product['_line_tax'])) ? (float)$info_product['_line_tax'] : 0;
				$product_count   = (isset($info_product['_qty'])) ? (float)$info_product['_qty'] : 0;
				$product_artikul = (isset($info_product['_sku'])) ? (string)$info_product['_sku'] : '';
				$product_sale    = ((isset($order_info['_cart_discount'])) and ($order_info['_cart_discount'] > 0)) ? $order_info['_cart_discount'] : 0;
				
				$product_name_tovar = formatStringForXML($product_name_tovar);
				$product_1c_id      = formatStringForXML($product_1c_id);
				$product_artikul    = formatStringForXML($product_artikul);

				$OrdersArray[$order_number]['Товары'][$product_id]['Ид'] = $product_1c_id;
				$OrdersArray[$order_number]['Товары'][$product_id]['Наименование'] = $product_name_tovar;
				$OrdersArray[$order_number]['Товары'][$product_id]['Артикул'] = $product_artikul;
				$OrdersArray[$order_number]['Товары'][$product_id]['БазоваяЕдиница'] = "шт";
				$OrdersArray[$order_number]['Товары'][$product_id]['Единица'] = "шт";
				$OrdersArray[$order_number]['Товары'][$product_id]['Коэффициент'] = "1";
				$OrdersArray[$order_number]['Товары'][$product_id]['Количество'] = $product_count;
				$OrdersArray[$order_number]['Товары'][$product_id]['ВидНоменклатуры'] = "Товар";
				$OrdersArray[$order_number]['Товары'][$product_id]['ТипНоменклатуры'] = "Товар";
				
				if (VM_NDS == 1){
					//$product_price = ($product_price  * $product_tax  / 100) + $product_price;
					$product_price = $product_price + $product_tax;
				}
				if ($product_count > 0) {
					$OrdersArray[$order_number]['Товары'][$product_id]['ЦенаЗаЕдиницу'] = $product_price / $product_count;
				}else{
					$OrdersArray[$order_number]['Товары'][$product_id]['ЦенаЗаЕдиницу'] = $product_price;	
				}
				$OrdersArray[$order_number]['Товары'][$product_id]['Сумма'] = $product_price;
				
				//Учет НДС
				if (BUH_3 == 1){
					$OrdersArray[$order_number]['Товары'][$product_id]['НДС'] = "БЕЗ НДС";
				}
				
				//Скидки
				$summa_skidki_tovar = 0;									
				if ($product_sale > 0){
					//Скидка задана весь заказ								
					if ($shipping_price > 0){
						$summa_skidki_tovar = $product_sale/($kolvo_tovarov+1);
					}else{
						$summa_skidki_tovar = $product_sale/($kolvo_tovarov);
					}
					$summa_skidki_tovar = round($summa_skidki_tovar);	
				}
				if (VM_CALC_SALE == 1){
					$OrdersArray[$order_number]['Товары'][$product_id]['Скидка'] = $summa_skidki_tovar;
				}
			}

			//Доставка	
			if (($shipping_price > 0) and (VM_USE_SERVICE_IN_ORDER == 1)){		
				$shipping_id = 0;
				$shipping_tax = (isset($order_info['_order_shipping_tax'])) ? (float)$order_info['_order_shipping_tax'] : 0;
				$shipping_sale    = ((isset($order_info['_cart_discount'])) and ($order_info['_cart_discount'] > 0)) ? $order_info['_cart_discount'] : 0;
				
				if (empty($shipping_method)){
					$shipping_method = 'Наименование не задано';
				}
				
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['Ид'] = 'ORDER_DELIVERY';
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['Наименование'] = $shipping_method;
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['Артикул'] = '';
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['БазоваяЕдиница'] = "шт";
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['Единица'] = "шт";
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['Коэффициент'] = "1";
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['Количество'] = 1;
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['ВидНоменклатуры'] = "Услуга";
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['ТипНоменклатуры'] = "Услуга";
				
				if (VM_NDS == 1){
					//$shipping_price = ($shipping_price  * $shipping_tax  / 100) + $shipping_price;
					$shipping_price = $shipping_price + $shipping_tax;
				}
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['ЦенаЗаЕдиницу'] = $shipping_price;
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['Сумма'] = $shipping_price * 1;

				//Скидки
				$summa_skidki_tovar = 0;
				if ($shipping_sale > 0) {									
					$summa_skidki_tovar = $shipping_sale/($kolvo_tovarov+1);	
					$summa_skidki_tovar = round($summa_skidki_tovar);	
				}
				if (VM_CALC_SALE == 1){
					$OrdersArray[$order_number]['Услуги'][$shipping_id]['Скидка'] = $summa_skidki_tovar;
				}
			}
			
			//Информация о методе доставки	
			if ($shipping_price > 0){					
				$shipping_first_name = (isset($order_info['_shipping_first_name'])) ? (string)$order_info['_shipping_first_name'] : '';
				$shipping_last_name  = (isset($order_info['_shipping_last_name'])) ? (string)$order_info['_shipping_last_name'] : '';
				$receiver = trim($shipping_first_name.' '.$shipping_last_name);
				
				$receiver = formatStringForXML($receiver);
				
				$shipping_code = rus2translit($shipping_method);
				$shipping_code = mb_substr((md5($shipping_code)),1,10); 
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Метод доставки ИД']    = $shipping_code;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Метод доставки']       = $shipping_method;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Адрес доставки']       = $presentment;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Комментарий доставки'] = $presentment;
				
				if (!empty($receiver)){
					$OrdersArray[$order_number]['ЗначениеРеквизита']['Получатель'] = $receiver;
				}
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Контактный телефон'] = $customer_telephone;	
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Почта получателя']   = $customer_email;
				if (VM_USE_SERVICE_IN_ORDER == 0) {
					$shipping_sale    = ((isset($order_info['_cart_discount'])) and ($order_info['_cart_discount'] > 0)) ? $order_info['_cart_discount'] : 0;
					if ($shipping_sale > 0) {									
						$summa_skidki_tovar = $shipping_sale/($kolvo_tovarov+1);	
						$summa_skidki_tovar = round($summa_skidki_tovar);	
						$shipping_price = $shipping_price - $summa_skidki_tovar;
					}
					$OrdersArray[$order_number]['ЗначениеРеквизита']['Стоимость доставки'] = $shipping_price;
				}
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Трек-номер']         = $order_key;
			}
			//Статус заказа 
			//Оплаченный заказ	
			if($order_status_id == $order_status_oplacheno){
				
				$date_added = (isset($order_info['_date_paid'])) ? strtotime($order_info['_date_paid']) : $date_order;
				$date_added_date = date("Ymd", $date_added);
				$date_added_time = date("Hms", $date_added);
				$date_payment = $date_added_date.$date_added_time; //Дата = '20170825125905'; // 25 августа 2017 года 12:59:05	
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Статус заказа'] = $status_order;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Дата отгрузки'] = $date;
				
				//Оплата заказа на сайте (УНФ 1.6.11.46)
				if (empty($date_payment)){  
					$date_added = $date_order;
					$date_added_date = date("Ymd", $date_added);
					$date_added_time = date("Hms", $date_added);
					$date_payment = $date_added_date.$date_added_time;				
				}
				if (UNF_1_6_15 == 1){
					$date_payment = date("Y-m-d", $date_added);
				}
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Дата оплаты']                 = $date_payment;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Метод оплаты']                = "Интернет";
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Метод оплаты ИД']             = "Интернет";
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Номер платежного документа']  = $order_id;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Заказ оплачен']               = "true";
				
				//обмен по 54-ФЗ
				$OrdersArray[$order_number]['ОтправитьЧекККМ'] = "true";
			}
			//Отмененный заказ
			if($order_status_id == $order_status_otmeneno){
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Статус заказа'] = $status_order;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Отменен']       = "true";		
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Дата отгрузки'] = $date;	
			}
			//Любой другой статус	
			if(($order_status_id <> $order_status_oplacheno) or ($order_status_id <> $order_status_otmeneno)) {
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Статус заказа'] = $status_order;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Дата отгрузки'] = $date;
			}

			if (VM_ORDER_STATUS_PROCESSING <> ''){
				$wpdb->query("UPDATE " . DB_PREFIX . "posts SET post_status='".VM_ORDER_STATUS_PROCESSING."' WHERE ID='".$order_id."'"); 	
			}

		}
	}
	return $OrdersArray;
}

function GetOrdersWC8_0_0() {
global $wpdb;

	$order_status_ozhidanie  = OrderStatusReturn ('wc-on-hold');
	$order_status_otmeneno   = OrderStatusReturn ('wc-cancelled');
	$order_status_vobrabotke = OrderStatusReturn ('wc-processing');
	$order_status_oplacheno  = VM_ORDER_STATUS_PAID;
	$order_status_dostavleno = VM_ORDER_STATUS_COMPLETE;
	
	$date_up = '1990-01-01 00:00:00';
	$order_date_load_query = $wpdb->get_results("SELECT value FROM " . DB_PREFIX . "setting_exchange_1c WHERE name_setting = 'VM_ORDER_DATE_LOAD'"); 
	if (count($order_date_load_query)>0){
		foreach($order_date_load_query as $order_date_load_result){
			$order_date_load = $order_date_load_result->value;
		}
		if (!empty($order_date_load)){
			$date_up = $order_date_load.' 00:00:00';
		}
	}
	$OrdersArray = array(); 
	$count_orders = 0;

	$text_query_final = getAllowStatuses('o.`status`');
	$orders_query_text = "SELECT * FROM `" . DB_PREFIX . "wc_orders` AS o LEFT JOIN `" . DB_PREFIX . "posts` AS p ON o.`id` = p.`ID` WHERE o.`date_created_gmt` > '".$date_up."' AND (".$text_query_final.")";	
	$orders_query  = $wpdb->get_results($orders_query_text);	
	if (count($orders_query)>0){
		foreach ($orders_query as $zakazy){
			$count_orders = $count_orders + 1;		
			$order_info = array();
			$order_info_query  = $wpdb->get_results( "SELECT * FROM `" . DB_PREFIX . "wc_orders` AS o LEFT JOIN 
													`" . DB_PREFIX . "wc_order_operational_data` AS ood ON o.id = ood.order_id LEFT JOIN
													`" . DB_PREFIX . "wc_order_addresses` AS oa ON o.id = oa.order_id LEFT JOIN	
													`" . DB_PREFIX . "wc_order_stats` AS os ON o.id = os.order_id LEFT JOIN
													`" . DB_PREFIX . "wc_order_tax_lookup` AS otl ON o.id = otl.order_id 													
													 WHERE o.id = '".(int)$zakazy->id."'");
			if (count($order_info_query)>0){
				foreach($order_info_query as $order_info_result){
					$order_info['_order_key']      = (isset($order_info_result->order_key)) ? (string)$order_info_result->order_key : '';
					$order_info['order_total']     = (isset($order_info_result->total_amount)) ? (float)$order_info_result->total_amount : 0;
					$order_info['order_status_id'] = (isset($order_info_result->status)) ? $order_info_result->status : ((isset($zakazy->status)) ? $zakazy->status : 0);
					$order_info['date_order'] 	   = (isset($order_info_result->date_created_gmt)) ? strtotime($order_info_result->date_created_gmt) : date("Y-m-d H:i:s");
					$order_info['_alg_wc_custom_order_number'] = ''; 

					$order_info['customer_first_name'] = (isset($order_info_result->first_name)) ? (string)$order_info_result->first_name : '';
					$order_info['customer_last_name']  = (isset($order_info_result->last_name)) ? (string)$order_info_result->last_name : '';
					$order_info['customer_email']      = (isset($order_info_result->email)) ? (string)$order_info_result->email : 'non@email.com';
					$order_info['customer_telephone']  = (isset($order_info_result->phone)) ? (string)$order_info_result->phone : '';
					$order_info['customer_fax']        = (isset($order_info_result->fax)) ? (string)$order_info_result->fax : '';
					$order_info['customer_off_name']   = (isset($order_info_result->company)) ? (string)$order_info_result->company : '';
					$order_info['member']              =  '';	

					//адрес
					$order_info['_billing_country']   = ((isset($order_info_result->country)) and (!empty($order_info_result->country))) ? (string)$order_info_result->country : 'Россия';
					$order_info['_billing_postcode']  = ((isset($order_info_result->postcode)) and (!empty($order_info_result->postcode))) ? (string)$order_info_result->postcode : '';
					$order_info['_billing_state'] 	  = ((isset($order_info_result->state)) and (!empty($order_info_result->state))) ? (string)$order_info_result->state : '';
					$order_info['_billing_city'] 	  = ((isset($order_info_result->city)) and (!empty($order_info_result->city))) ? (string)$order_info_result->city : '';
					$order_info['_billing_address_1'] = ((isset($order_info_result->address_1)) and (!empty($order_info_result->address_1))) ? (string)$order_info_result->address_1 : '';
					$order_info['_billing_address_2'] = ((isset($order_info_result->address_2)) and (!empty($order_info_result->address_2))) ? (string)$order_info_result->address_2 : '';
					
					$order_info['user_comment']          = (isset($order_info_result->customer_note)) ? strip_tags($order_info_result->customer_note) : '';
					$order_info['_payment_method_title'] = (isset($order_info_result->payment_method_title)) ? (string)$order_info_result->payment_method_title : '';	
					$order_info['_cart_discount']        = (isset($order_info_result->discount_total_amount)) ? (float)$order_info_result->discount_total_amount : 0;				
					
					$order_info['_order_shipping']       = (isset($order_info_result->shipping_total_amount)) ? (float)$order_info_result->shipping_total_amount : 0;
					$order_info['_order_shipping_tax']   = (isset($order_info_result->shipping_tax_amount)) ? (float)$order_info_result->shipping_tax_amount : 0;
					$order_info['_shipping_first_name']  = (isset($order_info_result->shipping_method_title)) ? (string)$order_info_result->shipping_method_title : 'Доставка';
					$order_info['_shipping_last_name']   =  '';
				}
			}	
			
			$order_id 	     = (isset($zakazy->id)) ? (int)$zakazy->id : 0;
			$order_status_id = (isset($order_info['order_status_id'])) ? $order_info['order_status_id'] : 0;
			$order_number    = (isset($order_info['_alg_wc_custom_order_number']) and (!empty($order_info['_alg_wc_custom_order_number']))) ? (string)$order_info['_alg_wc_custom_order_number'] : $order_id;
			$order_key       = (isset($order_info['_order_key'])) ? (string)$order_info['_order_key'] : '';
			$order_total     = (isset($order_info['order_total'])) ? (float)$order_info['order_total'] : 0;
			$date_order      = (isset($order_info['date_order'])) ? $order_info['date_order'] : date("Y-m-d H:i:s");

			$type_date_order = gettype($date_order);
			if ($type_date_order == "string") {
				$date_order = strtotime($date_order);
			}
			$date            = date("Y-m-d", $date_order);
			$time            = date("H:i:s", $date_order);
			
			$order_number    = formatStringForXML($order_number);
			$order_key       = formatStringForXML($order_key);
			
			$OrdersArray[$order_number]['Ид'] = $order_number;
			$OrdersArray[$order_number]['Номер'] = $order_number;		
			$OrdersArray[$order_number]['Дата'] = $date;
			$OrdersArray[$order_number]['Время'] = $time;
			$OrdersArray[$order_number]['ХозОперация'] = "Заказ товара";
			$OrdersArray[$order_number]['Роль'] = "Продавец";
			$OrdersArray[$order_number]['Сумма'] = $order_total;
			$OrdersArray[$order_number]['Курс'] = "1";
			
			//Валюта документа
			if (VM_CURRENCY == 1){
				$val = (isset($zakazy->currency)) ? (int)$zakazy->currency : 'RUB';
				$val = getRightNameCurrency($val);
				$OrdersArray[$order_number]['Валюта'] = $val;	
			}

			//Контрагент
			$customer_id         = '';
			$customer_first_name = (isset($order_info['customer_first_name'])) ? (string)$order_info['customer_first_name'] : '';
			$customer_last_name  = (isset($order_info['customer_last_name'])) ? (string)$order_info['customer_last_name'] : '';
			$customer_email      = (isset($order_info['customer_email'])) ? (string)$order_info['customer_email'] : 'non@email.com';
			$customer_telephone  = (isset($order_info['customer_telephone'])) ? (string)$order_info['customer_telephone'] : '';
			$customer_fax        = (isset($order_info['customer_fax'])) ? (string)$order_info['customer_fax'] : '';
			$customer_off_name   = (isset($order_info['customer_off_name'])) ? (string)$order_info['customer_off_name'] : '';
			$member              = (isset($order_info['member'])) ? (string)$order_info['member'] : '';
			
			$customer_first_name = formatStringForXML($customer_first_name);
			$customer_last_name  = formatStringForXML($customer_last_name);
			$customer_email      = formatStringForXML($customer_email);
			$customer_telephone  = formatStringForXML($customer_telephone);
			$customer_off_name   = formatStringForXML($customer_off_name);
			$member              = formatStringForXML($member);
			
			
			$FIO = $customer_first_name . " " . $customer_last_name;			
			$FIO_no_spacing = str_replace(' ', '', $FIO);
			if (empty($FIO_no_spacing)) {
				$FIO =  "Покупатель с сайта";
			}
			if (!empty($customer_email)){
				$FIO = $FIO.' ('.$customer_email.')';
			}
			
			if (empty($customer_off_name)){
				$OrdersArray[$order_number]['Контрагент']['Ид'] = $FIO;
				$OrdersArray[$order_number]['Контрагент']['Наименование'] = $FIO;
				$OrdersArray[$order_number]['Контрагент']['ПолноеНаименование'] = $FIO;
			}else{
				$OrdersArray[$order_number]['Контрагент']['Ид'] = $customer_off_name;
				$OrdersArray[$order_number]['Контрагент']['Наименование'] = $customer_off_name;
				$OrdersArray[$order_number]['Контрагент']['ПолноеНаименование'] = $customer_off_name;
				$OrdersArray[$order_number]['Контрагент']['ОфициальноеНаименование'] = $customer_off_name;
			}
			
			//Контакты
			if (!empty($customer_telephone)){
				$OrdersArray[$order_number]['Контрагент']['Телефон']['Представление'] = $customer_telephone;
				$OrdersArray[$order_number]['Контрагент']['Контакт']['ТелефонРабочий'] = $customer_telephone;
			}
			if (!empty($customer_email)){
				$OrdersArray[$order_number]['Контрагент']['email']['Представление']   = $customer_email;
				$OrdersArray[$order_number]['Контрагент']['Контакт']['Почта'] = $customer_email;
			}
			if (!empty($customer_fax)){
				$OrdersArray[$order_number]['Контрагент']['Факс']['Представление'] = $customer_fax;
				$OrdersArray[$order_number]['Контрагент']['Контакт']['Факс'] = $customer_fax;
			}
			
			//Представители
			if (!empty($member)){
				$OrdersArray[$order_number]['Контрагент']['Представитель'] = $member;
			}
						
			//Юридический адрес
			$country   = (isset($order_info['_billing_country'])) ? (string)$order_info['_billing_country'] : 'Россия';
			$country   = getRightNameCountry($country);
			$postcode  = (isset($order_info['_billing_postcode'])) ? (string)$order_info['_billing_postcode'] : '';
			$state     = (isset($order_info['_billing_state'])) ? (string)$order_info['_billing_state'] : '';
			$city      = (isset($order_info['_billing_city'])) ? (string)$order_info['_billing_city'] : '';
			$address_1 = (isset($order_info['_billing_address_1'])) ? (string)$order_info['_billing_address_1'] : '';
			$address_2 = (isset($order_info['_billing_address_2'])) ? (string)$order_info['_billing_address_2'] : '';

			$country   = formatStringForXML($country);
			$postcode  = formatStringForXML($postcode);
			$state     = formatStringForXML($state);
			$city      = formatStringForXML($city);
			$address_1 = formatStringForXML($address_1);
			$address_2 = formatStringForXML($address_2);
			
			
			$address = array();	
			if (!empty($postcode)) { $address[] = $postcode;}
			if (!empty($country))  { $address[] = $country;}
			if (!empty($state))    { $address[] = $state;}
			if (!empty($city))     { $address[] = $city;}
			if (!empty($address_1)){ $address[] = $address_1;}
			if (!empty($address_2)){ $address[] = $address_2;}		
			$presentment = implode(', ', $address);
			
			$OrdersArray[$order_number]['Контрагент']['АдресРегистрации']['Представление']   = $presentment;
			$OrdersArray[$order_number]['Контрагент']['АдресРегистрации']['Страна']          = $country;
			$OrdersArray[$order_number]['Контрагент']['АдресРегистрации']['Регион']          = $state;
			$OrdersArray[$order_number]['Контрагент']['АдресРегистрации']['Почтовый индекс'] = $postcode;
			$OrdersArray[$order_number]['Контрагент']['АдресРегистрации']['Город']           = $city;
			$OrdersArray[$order_number]['Контрагент']['АдресРегистрации']['Улица']           = $address_1;
			
			$OrdersArray[$order_number]['Контрагент']['ЮридическийАдрес']['Представление']   = $presentment;
			$OrdersArray[$order_number]['Контрагент']['ЮридическийАдрес']['Страна']          = $country;
			$OrdersArray[$order_number]['Контрагент']['ЮридическийАдрес']['Регион']          = $state;
			$OrdersArray[$order_number]['Контрагент']['ЮридическийАдрес']['Почтовый индекс'] = $postcode;
			$OrdersArray[$order_number]['Контрагент']['ЮридическийАдрес']['Город']           = $city;
			$OrdersArray[$order_number]['Контрагент']['ЮридическийАдрес']['Улица']           = $address_1;
			
			//Фактический адрес
			$country   = (isset($order_info['_billing_country'])) ? (string)$order_info['_billing_country'] : 'Россия';
			$country   = getRightNameCountry($country);
			$postcode  = (isset($order_info['_billing_postcode'])) ? (string)$order_info['_billing_postcode'] : '';
			$state     = (isset($order_info['_billing_state'])) ? (string)$order_info['_billing_state'] : '';
			$city      = (isset($order_info['_billing_city'])) ? (string)$order_info['_billing_city'] : '';
			$address_1 = (isset($order_info['_billing_address_1'])) ? (string)$order_info['_billing_address_1'] : '';
			$address_2 = (isset($order_info['_billing_address_2'])) ? (string)$order_info['_billing_address_2'] : '';

			$country   = formatStringForXML($country);
			$postcode  = formatStringForXML($postcode);
			$state     = formatStringForXML($state);
			$city      = formatStringForXML($city);
			$address_1 = formatStringForXML($address_1);
			$address_2 = formatStringForXML($address_2);
			
			$address = array();	
			if (!empty($postcode)) { $address[] = $postcode;}
			if (!empty($country))  { $address[] = $country;}
			if (!empty($state))    { $address[] = $state;}
			if (!empty($city))     { $address[] = $city;}
			if (!empty($address_1)){ $address[] = $address_1;}
			if (!empty($address_2)){ $address[] = $address_2;}		
			$presentment = implode(', ', $address);
			
			$OrdersArray[$order_number]['Контрагент']['Адрес']['Представление']   = $presentment;
			$OrdersArray[$order_number]['Контрагент']['Адрес']['Страна']          = $country;
			$OrdersArray[$order_number]['Контрагент']['Адрес']['Регион']          = $state;
			$OrdersArray[$order_number]['Контрагент']['Адрес']['Почтовый индекс'] = $postcode;
			$OrdersArray[$order_number]['Контрагент']['Адрес']['Город']           = $city;
			$OrdersArray[$order_number]['Контрагент']['Адрес']['Улица']           = $address_1;
					
			//Заполнение поля комментарий
			$status_order    =  OrderStatusInfo($order_status_id);
			$user_comment    = (isset($order_info['user_comment'])) ? (string)$order_info['user_comment'] : '';
			$payment_method  = (isset($order_info['_payment_method_title'])) ? (string)$order_info['_payment_method_title'] : '';
			$shipping_method = (isset($zakazy->ID)) ? getShippingMethodOrder($zakazy->ID) : '';	
			$shipping_price  = (isset($order_info['_order_shipping'])) ? (float)$order_info['_order_shipping'] : 0;
			
			$status_order    = formatStringForXML($status_order);
			$user_comment    = formatStringForXML($user_comment);
			$payment_method  = formatStringForXML($payment_method);
			$shipping_method = formatStringForXML($shipping_method);
				
			$comment = '';
			$comment = $comment . "Статус на сайте: ". $status_order ."; \n";
			if (!empty($payment_method)) {
				$comment = $comment . "Оплата: ". $payment_method ."; \n";	
			}	
			if (!empty($shipping_method)) {
				$comment = $comment . "Доставка: ". $shipping_method ."; \n";
				if ($shipping_price > 0){
					if (!empty($presentment)){ 
						$comment = $comment . "Адрес доставки: ". $presentment ."; \n";	
					}
				}
			}
			$comment = $comment . "Комментарий: ". $user_comment ." ";	
			$OrdersArray[$order_number]['Комментарий'] = $comment;
			
			//Разбор товаров
			$products_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "woocommerce_order_items WHERE order_id = '".(int)$order_id."' AND order_item_type = 'line_item'" );		
			$kolvo_tovarov = 0;
			foreach ($products_query as $razbor_zakaza_col) {$kolvo_tovarov = $kolvo_tovarov + 1;}
			foreach ($products_query as $razbor_zakaza_t) {	
				$info_product = array();
				$product_id = (int)$razbor_zakaza_t->order_item_id;
				$info_products_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "woocommerce_order_itemmeta WHERE order_item_id = '".(int)$product_id."'" );
				if (count($info_products_query)>0){
					foreach($info_products_query as $info_products_result){
						$info_product[$info_products_result->meta_key] = $info_products_result->meta_value;
					}
				}		
				$product_id_easy  = (isset($info_product['_product_id'])) ? (int)$info_product['_product_id'] : '';
				$variation_id  = ((isset($info_product['_variation_id'])) and ($info_product['_variation_id'] > 0)) ? (int)$info_product['_variation_id'] : '';
				if (!empty($product_id_easy)){
					$product_id = $product_id_easy;
					$post_type = 'product';
					if (!empty($variation_id)){
						$product_id = $info_product['_variation_id'];
						$post_type = 'product_variation';
					}
					
					$info_products_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "postmeta WHERE post_id = '".(int)$product_id."'" );
					if (count($info_products_query)>0){
						foreach($info_products_query as $info_products_result){
							$info_product[$info_products_result->meta_key] = $info_products_result->meta_value;
						}
					}
					$info_products_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "posts WHERE ID = '".(int)$product_id."' AND post_type = '".$post_type."'" );
					if (count($info_products_query)>0){
						foreach($info_products_query as $info_products_result){
							$info_product['product_1c_id'] = $info_products_result->product_1c_id;
							$info_product['post_title'] = $info_products_result->post_title;
						}
					}
				}
				
				if (isset($info_product['post_title'])){
					$product_name_tovar = $info_product['post_title'];
				}else{
					$product_name_tovar = $razbor_zakaza_t->order_item_name;
				}
				$product_name_tovar = formatStringForXML($product_name_tovar);
				if(empty($product_name_tovar)){
					$product_name_tovar = 'Наименование не задано';
				}
				
				$product_1c_id   = ((isset($info_product['product_1c_id'])) and (!empty($info_product['product_1c_id'])))? (string)$info_product['product_1c_id'] : $product_name_tovar;
				$product_price   = (isset($info_product['_line_total'])) ? (float)$info_product['_line_total'] : 0;
				$product_tax     = (isset($info_product['_line_tax'])) ? (float)$info_product['_line_tax'] : 0;
				$product_count   = (isset($info_product['_qty'])) ? (float)$info_product['_qty'] : 0;
				$product_artikul = (isset($info_product['_sku'])) ? (string)$info_product['_sku'] : '';
				$product_sale    = ((isset($order_info['_cart_discount'])) and ($order_info['_cart_discount'] > 0)) ? $order_info['_cart_discount'] : 0;
				
				$product_name_tovar = formatStringForXML($product_name_tovar);
				$product_1c_id      = formatStringForXML($product_1c_id);
				$product_artikul    = formatStringForXML($product_artikul);

				$OrdersArray[$order_number]['Товары'][$product_id]['Ид'] = $product_1c_id;
				$OrdersArray[$order_number]['Товары'][$product_id]['Наименование'] = $product_name_tovar;
				$OrdersArray[$order_number]['Товары'][$product_id]['Артикул'] = $product_artikul;
				$OrdersArray[$order_number]['Товары'][$product_id]['БазоваяЕдиница'] = "шт";
				$OrdersArray[$order_number]['Товары'][$product_id]['Единица'] = "шт";
				$OrdersArray[$order_number]['Товары'][$product_id]['Коэффициент'] = "1";
				$OrdersArray[$order_number]['Товары'][$product_id]['Количество'] = $product_count;
				$OrdersArray[$order_number]['Товары'][$product_id]['ВидНоменклатуры'] = "Товар";
				$OrdersArray[$order_number]['Товары'][$product_id]['ТипНоменклатуры'] = "Товар";
				
				if (VM_NDS == 1){
					//$product_price = ($product_price  * $product_tax  / 100) + $product_price;
					$product_price = $product_price + $product_tax;
				}
				if ($product_count > 0) {
					$OrdersArray[$order_number]['Товары'][$product_id]['ЦенаЗаЕдиницу'] = $product_price / $product_count;
				}else{
					$OrdersArray[$order_number]['Товары'][$product_id]['ЦенаЗаЕдиницу'] = $product_price;	
				}
				$OrdersArray[$order_number]['Товары'][$product_id]['Сумма'] = $product_price;
				
				//Учет НДС
				if (BUH_3 == 1){
					$OrdersArray[$order_number]['Товары'][$product_id]['НДС'] = "БЕЗ НДС";
				}
				
				//Скидки
				$summa_skidki_tovar = 0;									
				if ($product_sale > 0){
					//Скидка задана весь заказ								
					if ($shipping_price > 0){
						$summa_skidki_tovar = $product_sale/($kolvo_tovarov+1);
					}else{
						$summa_skidki_tovar = $product_sale/($kolvo_tovarov);
					}
					$summa_skidki_tovar = round($summa_skidki_tovar);	
				}
				if (VM_CALC_SALE == 1){
					$OrdersArray[$order_number]['Товары'][$product_id]['Скидка'] = $summa_skidki_tovar;
				}
			}

			//Доставка	
			if ($shipping_price > 0){				
				$shipping_id = 0;
				$shipping_tax = (isset($order_info['_order_shipping_tax'])) ? (float)$order_info['_order_shipping_tax'] : 0;
				$shipping_sale    = ((isset($order_info['_cart_discount'])) and ($order_info['_cart_discount'] > 0)) ? $order_info['_cart_discount'] : 0;
				
				if (empty($shipping_method)){
					$shipping_method = 'Наименование не задано';
				}
				
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['Ид'] = 'ORDER_DELIVERY';
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['Наименование'] = $shipping_method;
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['Артикул'] = '';
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['БазоваяЕдиница'] = "шт";
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['Единица'] = "шт";
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['Коэффициент'] = "1";
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['Количество'] = 1;
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['ВидНоменклатуры'] = "Услуга";
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['ТипНоменклатуры'] = "Услуга";
				
				if (VM_NDS == 1){
					//$shipping_price = ($shipping_price  * $shipping_tax  / 100) + $shipping_price;
					$shipping_price = $shipping_price + $shipping_tax;
				}
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['ЦенаЗаЕдиницу'] = $shipping_price;
				$OrdersArray[$order_number]['Услуги'][$shipping_id]['Сумма'] = $shipping_price * 1;

				//Скидки
				$summa_skidki_tovar = 0;
				if ($shipping_sale > 0) {									
					$summa_skidki_tovar = $shipping_sale/($kolvo_tovarov+1);	
					$summa_skidki_tovar = round($summa_skidki_tovar);	
				}
				if (VM_CALC_SALE == 1){
					$OrdersArray[$order_number]['Услуги'][$shipping_id]['Скидка'] = $summa_skidki_tovar;
				}
			}
			
			//Информация о методе доставки	
			if ($shipping_price > 0){					
				$shipping_first_name = (isset($order_info['_shipping_first_name'])) ? (string)$order_info['_shipping_first_name'] : '';
				$shipping_last_name  = (isset($order_info['_shipping_last_name'])) ? (string)$order_info['_shipping_last_name'] : '';
				$receiver = trim($shipping_first_name.' '.$shipping_last_name);
				
				$receiver = formatStringForXML($receiver);
				
				$shipping_code = rus2translit($shipping_method);
				$shipping_code = mb_substr((md5($shipping_code)),1,10); 
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Метод доставки ИД']    = $shipping_code;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Метод доставки']       = $shipping_method;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Адрес доставки']       = $presentment;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Комментарий доставки'] = $presentment;
				
				if (!empty($receiver)){
					$OrdersArray[$order_number]['ЗначениеРеквизита']['Получатель'] = $receiver;
				}
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Контактный телефон'] = $customer_telephone;	
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Почта получателя']   = $customer_email;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Стоимость доставки'] = $shipping_price;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Трек-номер']         = $order_key;
			}
			//Статус заказа 
			//Оплаченный заказ	
			if($order_status_id == $order_status_oplacheno){
				
				$date_added = (isset($order_info['_date_paid'])) ? strtotime($order_info['_date_paid']) : $date_order;
				$date_added_date = date("Ymd", $date_added);
				$date_added_time = date("Hms", $date_added);
				$date_payment = $date_added_date.$date_added_time; //Дата = '20170825125905'; // 25 августа 2017 года 12:59:05	
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Статус заказа'] = $status_order;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Дата отгрузки'] = $date;
				
				//Оплата заказа на сайте (УНФ 1.6.11.46)
				if (empty($date_payment)){  
					$date_added = $date_order;
					$date_added_date = date("Ymd", $date_added);
					$date_added_time = date("Hms", $date_added);
					$date_payment = $date_added_date.$date_added_time;				
				}
				if (UNF_1_6_15 == 1){
					$date_payment = date("Y-m-d", $date_added);
				}
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Дата оплаты']                 = $date_payment;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Метод оплаты']                = "Интернет";
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Метод оплаты ИД']             = "Интернет";
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Номер платежного документа']  = $order_id;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Заказ оплачен']               = "true";
				
				//обмен по 54-ФЗ
				$OrdersArray[$order_number]['ОтправитьЧекККМ'] = "true";
			}
			//Отмененный заказ
			if($order_status_id == $order_status_otmeneno){
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Статус заказа'] = $status_order;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Отменен']       = "true";		
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Дата отгрузки'] = $date;	
			}
			//Любой другой статус	
			if(($order_status_id <> $order_status_oplacheno) or ($order_status_id <> $order_status_otmeneno)) {
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Статус заказа'] = $status_order;
				$OrdersArray[$order_number]['ЗначениеРеквизита']['Дата отгрузки'] = $date;
			}

			if (VM_ORDER_STATUS_PROCESSING <> ''){
				$date_modified = date('Y-m-d H:i:s');
				$update  = $wpdb->query("UPDATE " . DB_PREFIX . "posts  SET post_modified='" . $date_modified . "', post_modified_gmt='" . $date_modified . "' where ID = '" . (int)$order_id . "' AND post_type = 'shop_order_placehold'" );
				$update  = $wpdb->query("UPDATE " . DB_PREFIX . "wc_orders SET status='".VM_ORDER_STATUS_PROCESSING."', date_updated_gmt='". $date_modified ."' WHERE id='".$order_id."'"); 	
			}

		}
	}
	return $OrdersArray;
}

function CreateZakaz($use_bitrix = false) {
	$timechange = time ();
	$count_orders = 0;
	$OrdersArray = array();
	
	if ($use_bitrix == true){
		$no_spaces = '<?xml version="1.0" encoding="UTF-8"?><КоммерческаяИнформация ВерсияСхемы="3.1" ДатаФормирования="' . date ( 'Y-m-d', $timechange ) . 'T' . date ( 'H:i:s', $timechange ) . '"></КоммерческаяИнформация>';
		$xml = new SimpleXMLElement($no_spaces);
			
		$readStatus = readStatusProgress('upload_orders_bitrix');
		$time_upload = strtotime($readStatus['date_exchange']);
		$time_now = strtotime(date('Y-m-d H:i:s'));
		$diff = abs($time_now - $time_upload);
		if ($diff > 60){
			$pack = $xml->addChild ( "Контейнер" );
			if ( version_compare( WC_VERSION, '8.0.0', ">=" ) ) {
				$OrdersArray = GetOrdersWC8_0_0();
				if (empty($OrdersArray)){
					$OrdersArray = GetOrders();
				}
			}else{
				$OrdersArray = GetOrders();
			}
			saveStatusProgress ('upload_orders_bitrix', 'success', 'time upload');	
		}
	}else{
		$no_spaces = '<?xml version="1.0" encoding="UTF-8"?><КоммерческаяИнформация ВерсияСхемы="2.04" ДатаФормирования="' . date ( 'Y-m-d', $timechange ) . 'T' . date ( 'H:i:s', $timechange ) . '"></КоммерческаяИнформация>';
		$xml = new SimpleXMLElement($no_spaces);
		if ( version_compare( WC_VERSION, '8.0.0', ">=" ) ) {
			$OrdersArray = GetOrdersWC8_0_0();
			if (empty($OrdersArray)){
				$OrdersArray = GetOrders();
			}
		}else{
			$OrdersArray = GetOrders();
		}
	}

	foreach ($OrdersArray as $Order){
		$count_orders++;
		if ($use_bitrix == true){
			$doc = $pack->addChild ( "Документ" );
		}else{
			$doc = $xml->addChild ( "Документ" );
		}
				
		if (isset($Order['Ид'])){
			$doc->addChild ( "Ид", $Order['Ид'] );
		}
		if (isset($Order['Номер'])){
			$doc->addChild ( "Номер", $Order['Номер'] );
		}
		if (isset($Order['Дата'])){
			$doc->addChild ( "Дата", $Order['Дата'] );
		}
		if (isset($Order['Время'])){
			$doc->addChild ( "Время", $Order['Время'] );
		}
		if (isset($Order['ХозОперация'])){
			$doc->addChild ( "ХозОперация", $Order['ХозОперация'] );
		}
		if (isset($Order['Роль'])){
			$doc->addChild ( "Роль", $Order['Роль'] );
		}
		if (isset($Order['Валюта'])){
			$doc->addChild ( "Валюта", $Order['Валюта'] );
		}
		if (isset($Order['Курс'])){
			$doc->addChild ( "Курс", $Order['Курс'] );
		}
		if (isset($Order['Сумма'])){
			$doc->addChild ( "Сумма", $Order['Сумма'] );
		}
			
		$k1 = $doc->addChild ( 'Контрагенты' );
		$k1_1 = $k1->addChild ( 'Контрагент' );	
		if (isset($Order['Контрагент']['Ид'])){
			$k1_2 = $k1_1->addChild ( "Ид", $Order['Контрагент']['Ид']);
		}
		if (isset($Order['Контрагент']['Наименование'])){
			$k1_2 = $k1_1->addChild ( "Наименование", $Order['Контрагент']['Наименование'] );
		}
		if (isset($Order['Контрагент']['Роль'])){
			$k1_2 = $k1_1->addChild ( "Роль", $Order['Контрагент']['Роль'] );
		}
		if (isset($Order['Контрагент']['ПолноеНаименование'])){
			$k1_2 = $k1_1->addChild ( "ПолноеНаименование", $Order['Контрагент']['ПолноеНаименование'] );
		}
		if (isset($Order['Контрагент']['ОфициальноеНаименование'])){
			$k1_2 = $k1_1->addChild ( "ОфициальноеНаименование", $Order['Контрагент']['ОфициальноеНаименование'] );
		}
		if (isset($Order['Контрагент']['ИНН'])){
			$k1_2 = $k1_1->addChild ( "ИНН", $Order['Контрагент']['ИНН'] );
		}
		if (isset($Order['Контрагент']['КПП'])){
			$k1_2 = $k1_1->addChild ( "КПП", $Order['Контрагент']['КПП'] );
		}

		if (isset($Order['Контрагент']['Телефон']['Представление'])){
			$k1_2 = $k1_1->addChild ( "Телефон");
			$k1_2->addChild ( "Представление", $Order['Контрагент']['Телефон']['Представление'] );
		}
		if (isset($Order['Контрагент']['email']['Представление'])){
			$k1_2 = $k1_1->addChild ( "email");
			$k1_2->addChild ( "Представление", $Order['Контрагент']['email']['Представление'] );
		}
		
		//Контакты
		$contacts = $k1_1->addChild ( 'Контакты' );
		if (isset($Order['Контрагент']['Контакт']['ТелефонРабочий'])){
			$cont = $contacts->addChild ( 'Контакт' );
			$cont->addChild ( 'Тип', 'ТелефонРабочий' );
			$cont->addChild ( 'Значение', $Order['Контрагент']['Контакт']['ТелефонРабочий'] );
			}
		if (isset($Order['Контрагент']['Контакт']['Почта'])){
			$cont = $contacts->addChild ( 'Контакт' );
			$cont->addChild ( 'Тип', 'Почта' );
			$cont->addChild ( 'Значение', $Order['Контрагент']['Контакт']['Почта'] );
		}
		if (isset($Order['Контрагент']['Контакт']['Факс'])){
			$cont = $contacts->addChild ( 'Контакт' );
			$cont->addChild ( 'Тип', 'Факс' );
			$cont->addChild ( 'Значение', $Order['Контрагент']['Контакт']['Факс'] );
		}
		
		//Представители
		if (isset($Order['Контрагент']['Представитель']['Наименование'])){
			$addr = $k1_1->addChild ('Представители');
			$addrField = $addr->addChild ( 'Представитель');
			$addrField2 = $addrField ->addChild ( 'Контрагент');
			$addrField3 = $addrField2 ->addChild ( 'Наименование', $Order['Контрагент']['Представитель']['Наименование']);
		}
		
		if (isset($Order['Комментарий'])){
			$doc->addChild ( "Комментарий", $Order['Комментарий'] );
		}
		
		foreach ($Order['Контрагент'] as $OrderUnitName => $OrderUnit){
			if (($OrderUnitName == 'АдресРегистрации') or ($OrderUnitName == 'ЮридическийАдрес') or ($OrderUnitName == 'Адрес')){
				$addr = $k1_1->addChild ($OrderUnitName);			
				if (isset($Order['Контрагент'][$OrderUnitName]['Представление'])){
					$addr->addChild ( 'Представление', $Order['Контрагент'][$OrderUnitName]['Представление'] );
				}
				
				if (isset($Order['Контрагент'][$OrderUnitName]['Страна'])){
					$addrField = $addr->addChild ( 'АдресноеПоле' );
					$addrField->addChild ( 'Тип', 'Страна' );
					$addrField->addChild ( 'Значение', $Order['Контрагент'][$OrderUnitName]['Страна'] );
				}
				
				if (isset($Order['Контрагент'][$OrderUnitName]['Регион'])){
					$addrField = $addr->addChild ( 'АдресноеПоле' );
					$addrField->addChild ( 'Тип', 'Регион' );
					$addrField->addChild ( 'Значение', $Order['Контрагент'][$OrderUnitName]['Регион'] );
				}
				
				if (isset($Order['Контрагент'][$OrderUnitName]['Почтовый индекс'])){
					$addrField = $addr->addChild ( 'АдресноеПоле' );
					$addrField->addChild ( 'Тип', 'Почтовый индекс' );
					$addrField->addChild ( 'Значение', $Order['Контрагент'][$OrderUnitName]['Почтовый индекс'] );
				}
				
				if (isset($Order['Контрагент'][$OrderUnitName]['Город'])){
					$addrField = $addr->addChild ( 'АдресноеПоле' );
					$addrField->addChild ( 'Тип', 'Город' );
					$addrField->addChild ( 'Значение', $Order['Контрагент'][$OrderUnitName]['Город'] );
				}
				
				if (isset($Order['Контрагент'][$OrderUnitName]['Улица'])){
					$addrField = $addr->addChild ( 'АдресноеПоле' );
					$addrField->addChild ( 'Тип', 'Улица' );
					$addrField->addChild ( 'Значение', $Order['Контрагент'][$OrderUnitName]['Улица'] );
				}
			}
		}
		
		//Товары и Услуги
		$table_order = array('Товары', 'Услуги');		
		foreach ($table_order as $table){		
			if (!isset($t1)){
				$t1 = $doc->addChild ( 'Товары' );
			}
			if (isset($Order[$table])){			
				foreach($Order[$table] as $Product){
					if (!isset($Product['Наименование'])){
						continue;
					}
					$t1_1 = $t1->addChild ( 'Товар' );
					if (isset($Product['Ид'])){
						$t1_2 = $t1_1->addChild ( "Ид", $Product['Ид'] ); 
					}
					if (isset($Product['Наименование'])){
						$t1_2 = $t1_1->addChild ( "Наименование", $Product['Наименование'] ); 
					}
					if (isset($Product['Коэффициент'])){
						$t1_2 = $t1_1->addChild ( "Коэффициент", $Product['Коэффициент'] ); 
					}
					if (isset($Product['БазоваяЕдиница'])){
						$t1_2 = $t1_1->addChild ( "БазоваяЕдиница", $Product['БазоваяЕдиница'] ); 
						$t1_2->addAttribute("Код", "796");	
					}
					if (isset($Product['Единица'])){
						$t1_2 = $t1_1->addChild ( "Единица", $Product['Единица'] ); 
						$t1_2->addAttribute("Ид", $Product['Единица']);
						$t1_2->addAttribute("Код", "796");
						$t1_2->addAttribute("НаименованиеКраткое", $Product['Единица']);	
						$t1_2->addAttribute("НаименованиеПолное", $Product['Единица']);						
					}
					if (isset($Product['НДС'])){
						$t1_2 = $t1_1->addChild ( "СтавкиНалогов");
						$t1_3 = $t1_2->addChild ( "СтавкаНалога");
						$t1_4 = $t1_3->addChild ( "Наименование", "НДС");
						$t1_4 = $t1_3->addChild ( "Ставка", $Product['НДС']);
					}
					if (isset($Product['ЦенаЗаЕдиницу'])){
						$t1_2 = $t1_1->addChild ( "ЦенаЗаЕдиницу", $Product['ЦенаЗаЕдиницу'] ); 
					}
					if (isset($Product['Количество'])){
						$t1_2 = $t1_1->addChild ( "Количество", $Product['Количество'] ); 
					}
					if (isset($Product['Сумма'])){
						$t1_2 = $t1_1->addChild ( "Сумма", $Product['Сумма'] ); 
					}
					if (isset($Product['Артикул'])){
						$t1_2 = $t1_1->addChild ( "Артикул", $Product['Артикул'] ); 
					}
						
					$t1_2 = $t1_1->addChild ( "ЗначенияРеквизитов" );
					if (isset($Product['ВидНоменклатуры'])){
						$t1_3 = $t1_2->addChild ( "ЗначениеРеквизита" );
						$t1_4 = $t1_3->addChild ( "Наименование", "ВидНоменклатуры" );
						$t1_4 = $t1_3->addChild ( "Значение", $Product['ВидНоменклатуры'] );
					}
					if (isset($Product['ТипНоменклатуры'])){
						$t1_3 = $t1_2->addChild ( "ЗначениеРеквизита" );
						$t1_4 = $t1_3->addChild ( "Наименование", "ТипНоменклатуры" );
						$t1_4 = $t1_3->addChild ( "Значение", $Product['ТипНоменклатуры'] );
					}
									
					if (isset($Product['Скидка'])){
						$sk0 = $t1_1->addChild ( 'Скидки'  );
						$sk1 = $sk0->addChild ( 'Скидка'  );
						$sk2= $sk1->addChild ( 'УчтеноВСумме' , 'false' );
						$sk2= $sk1->addChild ( "Сумма", $Product['Скидка'] ); 
					}
				}
			}
			
		}
		
		if (isset($Order['ЗначениеРеквизита'])){
			$s1_2 = $doc->addChild ( "ЗначенияРеквизитов" );
			foreach ($Order['ЗначениеРеквизита'] as $PropertyName => $PropertyValue) {
				if (isset($Order['ЗначениеРеквизита'][$PropertyName])){
					$s1_3 = $s1_2->addChild ( "ЗначениеРеквизита" );
					$s1_3->addChild ( "Наименование", $PropertyName );
					$s1_3->addChild ( "Значение", $Order['ЗначениеРеквизита'][$PropertyName] );
				}
			}
		}
		
		//обмен по 54-ФЗ
		if (isset($Order['ОтправитьЧекККМ'])){		
			$doc->addChild ( "ОтправитьЧекККМ" , $Order['ОтправитьЧекККМ'] );	
		}
		unset($t1);
		unset($Order);		
	}
	
	write_log ('Выгружено заказов: '.$count_orders);
	if (VM_CODING == 'UTF-8') {
		$xml_text = $xml->asXML();
		header("Content-Type: text/xml");
		$text = iconv( "UTF-8", "CP1251//TRANSLIT", $xml_text );
		print $text;
	}elseif(VM_CODING == 'Default'){
		header("Content-Type: text/xml");
		print $xml->asXML ();
	}else{
		$contents = $xml->asXML();
		$encoding_str = mb_detect_encoding($contents);
		if($encoding_str != "WINDOWS-1251"){
			$contents = iconv( $encoding_str, "CP1251//IGNORE", $contents );
		}
		$str = (function_exists("mb_strlen")? mb_strlen($contents, 'latin1'): strlen($contents));
		header("Content-Type: application/xml; charset=windows-1251");
		header("Content-Length: ".$str);
		echo $contents;
	}
	exit();
}

function getAllowStatuses($where_name) {
global $wpdb;

	$status_query = $wpdb->get_results("SELECT value FROM " . DB_PREFIX . "setting_exchange_1c WHERE name_setting = 'VM_STATUS_EXCHANGE'"); 
	$text_query = array();
	if (count($status_query)>0){
		foreach($status_query as $status_result){
			$status_value = $status_result->value;
		}
		$std_status_setting = json_decode($status_value, false);
		foreach($std_status_setting as $status_setting){
			$status_id = $status_setting->status_id;
			$enable_exchange = $status_setting->enable_exchange;
			if ($enable_exchange == '1'){
				//$text_query[] = "`post_status` = '".$status_id."'";
				$text_query[] = $where_name." = '".$status_id."'";
			}
		}
	}
	$text_query_final = "";
	if (!empty($text_query)){
		if (count($text_query)== 1 ){
			$text_query_final = $text_query[0];	
		}else{
			$text_query_final = implode(' OR ', $text_query);
		}
	}
	return $text_query_final;
}

function getRightNameCountry($country_name){
	$country = 'Россия';
	switch ($country_name) {
		case 'Российская Федерация': $country = 'Россия';      break;
		case 'Russian Federation':   $country = 'Россия';      break;
		case 'RU': 					 $country = 'Россия';      break;
		case 'KZ':                   $country = 'Казахстан';   break;
		case 'Kazakhstan':           $country = 'Казахстан';   break;
		case 'UA':					 $country = 'Украина';     break;
		case 'Ukraine':				 $country = 'Украина';     break;
		case 'BY':					 $country = 'Белоруссия';  break;
		case 'Belarus':				 $country = 'Белоруссия';  break;
		case 'LV':					 $country = 'Латвия';      break;
		case 'Latvia':			     $country = 'Латвия';      break;
		case 'LT':					 $country = 'Литва';       break;
		case 'Lithuania':			 $country = 'Литва';       break;
		case 'EE':					 $country = 'Эстония';     break;
		case 'Estonia':			     $country = 'Эстония';     break;
		case 'KG':					 $country = 'Киргизия';    break;
		case 'Kyrgyzstan':			 $country = 'Киргизия';    break;
		case 'TJ':					 $country = 'Таджикистан'; break;
		case 'Tajikistan':			 $country = 'Таджикистан'; break;
		case 'Latvija':			     $country = 'Латвия';      break;
		case 'Deutschland':			 $country = 'Германия';    break;
		case 'Rossiya':			     $country = 'Россия';      break;
	}
	if (!empty(VM_DEFAULT_COUNTRY)) {
		$country = VM_DEFAULT_COUNTRY;
	}
	return $country;
}

function getRightNameCurrency($val){
	$currency = 'RUB';
	switch ($val) {
		case 'RUB': $currency = 'RUB'; break;
		case 'руб': $currency = 'RUB'; break;
		case 'rub': $currency = 'RUB'; break;
		case '131': $currency = 'руб'; break;
		case 'евр': $currency = 'EUR'; break;
		case 'eur': $currency = 'EUR'; break;
		case 'EUR': $currency = 'EUR'; break;
		case 'usd': $currency = 'USD'; break;
		case 'дол': $currency = 'USD'; break;
		case 'dol': $currency = 'USD'; break;
		case 'uan': $currency = 'UAH'; break;
		case 'гри': $currency = 'UAH'; break;
		case 'грн': $currency = 'UAH'; break;
		case 'grn': $currency = 'UAH'; break;
		case 'uah': $currency = 'UAH'; break;
		case 'UAH': $currency = 'UAH'; break;
		case 'лв':  $currency = 'KZT'; break;
		case 'KZT': $currency = 'KZT'; break;	
		case 'BYN': $currency = 'BYN'; break;
		case 'бел': $currency = 'BYR'; break;	
		case 'BYR': $currency = 'BYR'; break;	
		case 'KGS': $currency = 'KGS'; break;
		case 'LVL': $currency = 'LVL'; break;
		case 'lvl': $currency = 'LVL'; break;
		case 'GEL': $currency = 'GEL'; break;
		case 'gel': $currency = 'GEL'; break;		
	}

	if (!empty(VM_NAME_CURRENCY_DEFAULT)){
		$currency = VM_NAME_CURRENCY_DEFAULT;
	}
	return $currency;
}

function OrderStatusReturn($NameStatus) {

	//'wc-pending'    => _x( 'Pending payment', 'Order status', 'woocommerce' ),
	//'wc-processing' => _x( 'Processing', 'Order status', 'woocommerce' ),
	//'wc-on-hold'    => _x( 'On hold', 'Order status', 'woocommerce' ),
	//'wc-completed'  => _x( 'Completed', 'Order status', 'woocommerce' ),
	//'wc-cancelled'  => _x( 'Cancelled', 'Order status', 'woocommerce' ),
	//'wc-refunded'   => _x( 'Refunded', 'Order status', 'woocommerce' ),
	//'wc-failed'     => _x( 'Failed', 'Order status', 'woocommerce' ),

	$name_status_array = explode( '|' , $NameStatus );
	if (count($name_status_array) == 0 ){
		$name_status_array[] = $NameStatus;
	}

	$status = 'wc-processing';
	require_once ( JPATH_BASE .DS.'wp-content'.DS.'plugins'.DS.'woocommerce'.DS.'includes'.DS.'wc-order-functions.php');
	if (function_exists('wc_get_order_statuses')){
		$order_statuses = wc_get_order_statuses();
		if (isset($order_statuses)){
			foreach ($order_statuses as $key=>$value){
				if ($key == $NameStatus){
					$status = $key;
				}
			}
		}
	}
	return $status;
}

function OrderStatusInfo($status_key) {

	$status_name = 'Неизвестно';
	require_once ( JPATH_BASE .DS.'wp-content'.DS.'plugins'.DS.'woocommerce'.DS.'includes'.DS.'wc-order-functions.php');
	if (function_exists('wc_get_order_statuses')){
		$order_statuses = wc_get_order_statuses();
		if (isset($order_statuses)){
			foreach ($order_statuses as $key=>$value) {
				if ($key == $status_key){
					$status_name = $value;
				}
			}
		}
	}
	return $status_name;
}

function getShippingMethodOrder($order_id) {
global $wpdb;
	$ShippingMethodResult = 'Не указано';
	$ShippingMethodInfo = array();
	$shipping_method_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "woocommerce_order_items WHERE order_id = '".(int)$order_id."' AND order_item_type = 'shipping'");
	if (count($shipping_method_query)>0){
		foreach($shipping_method_query as $shipping_method){
			$ShippingMethodInfo[] = $shipping_method->order_item_name;
		}
		if (!empty($ShippingMethodInfo)){
			if (count($ShippingMethodInfo) == 1){
				$ShippingMethodResult = $ShippingMethodInfo[0];
			}else{
				$ShippingMethodResult = implode(',',$ShippingMethodInfo);
			}
		}
	}	
	return $ShippingMethodResult;
}

function updateProductLookupTablesColumnWC(){ //only Woo > 3.6.0
global $wpdb;
	$column_to_update = array(
		'min_max_price',
		'stock_quantity',
		'stock_status',
		'onsale'
	);
	require_once ( JPATH_BASE .DS.'wp-content'.DS.'plugins'.DS.'woocommerce'.DS.'includes'.DS.'wc-product-functions.php');
	
	if (function_exists('wc_update_product_lookup_tables_column')){
		foreach ($column_to_update as $column){
			wc_update_product_lookup_tables_column( $column );
		}
	}	
}

function createProductForLookupTable($product_id, $args_lookuptable){ //only Woo > 3.6.0
global $wpdb;
	if(isset($wpdb->wc_product_meta_lookup)){
		
		if (!is_array($args_lookuptable)){
			return;
		}
			
		$product_meta_lookup_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "wc_product_meta_lookup WHERE product_id = '".(int)$product_id."'");
		if (count($product_meta_lookup_query)> 0){
			$text_query = array();
			foreach ($args_lookuptable as $args_key => $args_value){
				$text_query[] = $args_key." = '".$args_value."'";
			}
			if (!empty($text_query)){
				if (count($text_query)== 1 ){
					$text_query_final = $text_query[0];	
				}else{
					$text_query_final = implode(' , ', $text_query);
				}
				$wpdb->query ( "UPDATE " . DB_PREFIX . "wc_product_meta_lookup SET ".$text_query_final." WHERE product_id='". (int)$product_id."'");
			}
			unset($text_query);			
		}else{
			$sku = (isset($args_lookuptable['sku'])) ? $args_lookuptable['sku'] : 'Не указано';
			$min_price = (isset($args_lookuptable['min_price'])) ? $args_lookuptable['min_price'] : 0;
			$max_price = (isset($args_lookuptable['max_price'])) ? $args_lookuptable['max_price'] : 0;
			$onsale = (isset($args_lookuptable['onsale'])) ? $args_lookuptable['onsale'] : 0;
			$stock_quantity = (isset($args_lookuptable['stock_quantity'])) ? $args_lookuptable['stock_quantity'] : 0;
			$outofstock = getOutOfStockStatusObject($product_id);
			$stock_status = (isset($args_lookuptable['stock_status'])) ? $args_lookuptable['stock_status'] : $outofstock;
			$total_sales = (isset($args_lookuptable['total_sales'])) ? $args_lookuptable['total_sales'] : 1;
			
			$ins = new stdClass ();
			$ins->product_id = (int)$product_id;
			$ins->sku = $sku;
			$ins->virtual = 0;
			$ins->downloadable = 0;
			$ins->min_price = $min_price;
			$ins->max_price = $max_price;
			$ins->onsale = $onsale;
			$ins->stock_quantity = $stock_quantity;
			$ins->stock_status = $stock_status;
			$ins->rating_count = 0;
			$ins->average_rating = 0;
			$ins->total_sales = $total_sales;
			insertObject ( "" . DB_PREFIX ."wc_product_meta_lookup", $ins);
		}	
	}
	unset($args_lookuptable);	
}

function clearAllTransientWC(){ 
global $wpdb;
	$transient_array = array(
		'wc_layered_nav_counts' //очищение для widget layered
	);	
	foreach ($transient_array as $transient){
		$wpdb->query ( "DELETE FROM " . DB_PREFIX . "options WHERE option_name LIKE '%".$transient."%'");	
	}
}

//порцонный обмен данными
function XMLParser_getelement($file, $name_element){	
	
	$reader = new XMLReader();
	$reader->open($file);
	
	while ($reader->read()) {
		switch ($reader->nodeType) {
			case (XMLREADER::ELEMENT):
				if ($reader->name == $name_element && $reader->nodeType == XMLReader::ELEMENT) {
					$isset_name_element = true;	
					$reader->next();
				}
		}
	}
	$reader->close();
    unset($reader);
	if (isset($isset_name_element)){
		return $isset_name_element;
	}else{
		return false;
	}
		
}

function XMLParser_getAttribute($file, $name_element, $name_attribute){	
	
	$reader = new XMLReader();
	$reader->open($file);
	
	while ($reader->read()) {
		switch ($reader->nodeType) {
			case (XMLREADER::ELEMENT):
				if ($reader->name == $name_element && $reader->nodeType == XMLReader::ELEMENT) {
					
					$Attribute = $reader->getAttribute($name_attribute);	
				}
		}
	}
	$reader->close();
    unset($reader);
	if (isset($Attribute)){
		return $Attribute;
	}else{
		return "false";
	}		
}

function XMLParser_element_count($file, $name_element){	
	
	$reader = new XMLReader();
	$reader->open($file);
	
	$count = 0;
	while ($reader->read()) {
		switch ($reader->nodeType) {
			case (XMLREADER::ELEMENT):
				if ($reader->name == $name_element && $reader->nodeType == XMLReader::ELEMENT) {
					
					$count = $count +1;	
				}
		}
	}
	$reader->close();
    unset($reader);	
	return $count;
}

function XMLParser_file($file, $start_element, $finish_element, $name_element, $name_elements , $all = false){	
	
	if (function_exists('gc_enable') && !gc_enabled()) {
		gc_enable();
	}

	$groups_array = array();
	
	$domdoc = new DOMDocument('1.0', 'UTF-8');
	$domdoc->formatOutput = true;
	$domdoc->validateOnParse = true;
	if ($name_element == "Группа"){
		$element_ki = $domdoc->createElement("КоммерческаяИнформация");
		$newdomdoc = $domdoc->appendChild($element_ki);
		
		$element = $domdoc->createElement($name_elements);
		$newdomdoc = $newdomdoc->appendChild($element);
		
	}else{
		$element = $domdoc->createElement($name_elements);
		$newdomdoc = $domdoc->appendChild($element);
	}
	
	$reader = new XMLReader();
	$reader->open($file);
	
	$count = 0;
	while ($reader->read()) {
		switch ($reader->nodeType) {
			case (XMLREADER::ELEMENT):
				if ($reader->name == $name_element && $reader->nodeType == XMLReader::ELEMENT) {
					
					if ($all == true){
						$node = $reader->expand();
						$newdomdoc->appendChild($node);
					}else{
						if (($count >= $start_element) and ($count <= $finish_element)){
							$node = $reader->expand();
							$newdomdoc->appendChild($node);
						}
						$count = $count +1;	
					}
					
					if ($name_element == "Группа"){
						if (in_array($node, $groups_array)) {
							$reader->next();
						}else{
							$groups_array[] = $node;
						}
					}
				}
		}
	}
	$domdoc->normalizeDocument();
	$xml = simplexml_import_dom($domdoc);
	unset($domdoc);
    $reader->close();
    unset($reader);	
	return $xml;	
}

function readStatusProgress($filename){
global $wpdb;	
$STATUS_EXCHANGE = 'start';
$ERROR_OK = 'first exchange';
$response = array();

	$last_element_upload = "";
	$status_query  = $wpdb->get_results ( "SELECT * FROM " . DB_PREFIX . "status_exchange_1c where filename = '".$filename."'"); 
	if (count($status_query)>0) {
		foreach ($status_query as $status_result){
			$status              = $status_result->status;
			$error               = $status_result->error;
			$last_element_upload = $status_result->last_element_upload;
			$date_exchange       = $status_result->date_exchange;
			$response['status'] = $status;
			$response['error'] = $error;
			$response['last_element_upload'] = $last_element_upload;
			$response['date_exchange'] = $date_exchange;
		}
	}else{
		$date_exchange = date('Y-m-d H:i:s');
		$ins = new stdClass ();
		$ins->id = NULL;
		$ins->filename = $filename;
		$ins->status = $STATUS_EXCHANGE;
		$ins->error = $ERROR_OK;
		$ins->date_exchange = $date_exchange;
		$ins->last_element_upload = $last_element_upload;
		insertObject ( "" . DB_PREFIX ."status_exchange_1c", $ins) ;
				
		$response['status'] = $STATUS_EXCHANGE;
		$response['error'] = $ERROR_OK;
		$response['last_element_upload'] = $last_element_upload;
		$response['date_exchange'] = $date_exchange;
	}		
	return 	$response;		
}

function saveStatusProgress($filename, $status, $error){
global $wpdb;	

	$status_query  =  $wpdb->get_results ( "SELECT id FROM " . DB_PREFIX . "status_exchange_1c where filename = '".$filename."'"); 
	if (count($status_query)>0) {
		foreach ($status_query as $status_result){
			$id = (int)$status_result->id;
		}
		$status_update  = $wpdb->query ( "UPDATE `" . DB_PREFIX . "status_exchange_1c` SET  status='".$status."' , error='".$error."' , date_exchange='".date('Y-m-d H:i:s')."' where id='".$id."'");	
	}else{
		$ins = new stdClass ();
		$ins->id = NULL;
		$ins->filename = $filename;
		$ins->status = $status;
		$ins->error = $error;
		$ins->date_exchange = date('Y-m-d H:i:s');
		insertObject ( "" . DB_PREFIX ."status_exchange_1c", $ins) ;
	}	
}

function progressLoad($count, $count_continue, $FilePart, $all_count, $time_start, $time_now, $string_element, $show_now = false) {
global $FilenameUpload;
global $TimeBefore;
global $posix;		
	
	$show_log = false;
	$percent_load = floor(($count * 100 )/ $all_count);
	$time_load = ($time_now - $time_start);
	if ((($time_load % 7) == 0) and (($time_load <> 0) and ($time_load <> $TimeBefore)) and ($count <= $all_count)){		
		$show_log = true;
	}
	if ($count == $all_count){
		$show_log = true;	
	}
	if ($show_now == true){
		$show_log = true;	
	}
	
	if ($show_log == true){
		write_log("Процесс(".$posix."). Обработка ".$string_element." ".$count." из ".$all_count."(".$percent_load."%). Файл ".$FilenameUpload.", часть ".$FilePart);
	}
}

function getNameAndNumberFile($filename){
	
	$numberfile = '';
	$namefile = '';
	
	$nameimport   = 'import';
	$nameoffers   = 'offers';
	$create_name_fileimport="";
	$create_name_fileoffers="";
	$findimport = strpos($filename, $nameimport);
	if ($findimport === false) {
	   //false
	} else {
		$numberfile = str_replace($nameimport,"",$filename);
		$namefile = $nameimport;
	}
	$findoffers = strpos($filename, $nameoffers );
	if ($findoffers === false) {
		//false
	} else {
		$numberfile = str_replace($nameoffers,"",$filename);
		$namefile = $nameoffers;
	}
	
	$result = array();
	$result['namefile'] = $namefile;
	$result['numberfile'] = $numberfile;
	return $result;
}

function curlRequestAsync($query, $namefile, $number_file, $create_name_file) {
global $full_url_site;
global $posix;
	
	$url = $full_url_site.'/'.$query;
	if ((function_exists('curl_init')) and (VM_USE_ASYNCH == 1)) {
		if ($curl = curl_init()) {
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 1.0.3705; .NET CLR 1.1.4322; Media Center PC 4.0)"); 
			curl_setopt($curl, CURLOPT_TIMEOUT_MS, 	4000);
			$response = curl_exec($curl);
				
			$curlinfo_http_code = curl_getinfo ($curl, CURLINFO_HTTP_CODE);
			if ($curlinfo_http_code >= 400){
				$errors = 0;
				do {
				  $errors++;
				  write_log("Процесс(".$posix."). Error(".$errors.") CURLINFO_HTTP_CODE: ".curl_getinfo ($curl, CURLINFO_HTTP_CODE).", code error:".curl_errno($curl));
				  sleep (1); 
				  $response = curl_exec($curl);	
				} while (($errors >= 10) and (curl_getinfo ($curl, CURLINFO_HTTP_CODE) >= 400));
			}
			curl_close($curl);
			
			$readStatus = readStatusProgress($create_name_file);
			$status_progress = $readStatus['status'];		
			$history_posix = Posix::getHistoryPosix($create_name_file);
			if (($posix == $history_posix) or (empty($history_posix))){
				if (($status_progress <> 'progress') and ((STOP_PROGRESS == 0) and ($status_progress <> 'stop'))){
					write_log("Процесс(".$posix."). The curl request (HTTP_CODE:".$curlinfo_http_code.") cannot to upload file: ".$create_name_file.". Start standart load");
					startUpload($namefile, $number_file, $create_name_file);
				}
				if (($status_progress == 'progress') and (($curlinfo_http_code == 0) or ($curlinfo_http_code == false))){
					write_log("Процесс(".$posix."). Status load progress: ".$status_progress." (HTTP_CODE:".$curlinfo_http_code."). Start standart load");
					startUpload($namefile, $number_file, $create_name_file);
				}
			}
		}else{
			print "failure"."\n";
			write_log("Процесс(".$posix.").Error! No curl init: ".$curl);
		}
	}else{
		write_log("Процесс(".$posix."). Not function curl_init(). VM_USE_ASYNCH = ".VM_USE_ASYNCH." Start easy upload");
		startUpload($namefile, $number_file, $create_name_file);
	}
	exit();
}

function startUpload($namefile, $number_file, $create_name_file){
	if ((!empty($namefile)) and (!empty($number_file)) and (!empty($create_name_file))) {
		if ($namefile == 'import'){
			uploadFileImport($namefile, $number_file, $create_name_file);
		}
		if ($namefile == 'offers'){
			uploadFileOffers($namefile, $number_file, $create_name_file);
		}
		if ($namefile == 'prices'){
			uploadFileOffers($namefile, $number_file, $create_name_file);
		}
		if ($namefile == 'rests'){
			uploadFileOffers($namefile, $number_file, $create_name_file);
		}
	}
}


function uploadFileImport($nameimport, $number_import, $create_name_fileimport){
global $CategoryArray;	
global $FilenameUpload;
global $posix;
$FilenameUpload = $create_name_fileimport;
HeartBeat::start();
Posix::savePosix($posix, $FilenameUpload);

	$file = JPATH_BASE . DS .'TEMP'. DS . "".$nameimport."".$number_import."";
	if (file_exists ($file)){
		saveStatusProgress ($create_name_fileimport, 'progress', 'ok');	
		$pos = strpos($number_import, "_");	
			$CatalogContainsChanges = XMLParser_getAttribute($file, "Каталог", "СодержитТолькоИзменения");					
			if (VM_FOLDER == 1){//загрузка групп номенклатуры
				$count_product = XMLParser_element_count($file, "Группа");
				$count_parts = ceil($count_product / QUANTITY_DOSE);
				$count = 0;
				$start_element = 0;
				$finish_element = QUANTITY_DOSE;
				while ($count < $count_parts){
					$xml_Category = XMLParser_file($file, $start_element, $finish_element, "Группа", "Группы");
					$count++; // Увеличение счетчика
					write_log("Процесс(".$posix."). Загрузка групп номенклатуры файла ".$nameimport."".$number_import.". Часть =".$count." (из ".$count_parts.")");	
					$start_element = $start_element + QUANTITY_DOSE;
					$finish_element = $finish_element + QUANTITY_DOSE;					
					$CategoryArray = CategoryArrayFill($xml_Category,	$CategoryArray ,  0);
					CategoryXrefFill ($CategoryArray);
					unset ($xml_Category);
					usleep(1000);
				}
			}			
			//загрузка товаров
			$count_product = XMLParser_element_count($file, "Товар");
			$count_parts = ceil($count_product / QUANTITY_DOSE);
			$count = 0;
			$start_element = 0;
			$finish_element = QUANTITY_DOSE;
			
			$fileWithProperty = $file;
			$pos = strpos($number_import, "_");	
		    if ($pos === false){	
				if (UNF_1_6_15 == 1){
					$fileFirstImportXml = JPATH_BASE . DS .'TEMP'. DS .$nameimport.".xml";
					if (file_exists ($fileFirstImportXml)){
						$fileWithProperty = $fileFirstImportXml;
					}
				}		
			}else{
				$number_offers_parts   = explode( '_' , $number_import );
				$number_part  = $number_offers_parts[0];
				$fileFirstImportXml = JPATH_BASE . DS .'TEMP'. DS . "".$nameimport."".$number_part."_1.xml";
				if (file_exists ($fileFirstImportXml)){
					$fileWithProperty = $fileFirstImportXml;
				}
			}
			
			$isset_property_nomenclatura = XMLParser_getelement($fileWithProperty, "СвойствоНоменклатуры");
			if ($isset_property_nomenclatura == false){
				$xml_all_svoistva = XMLParser_file($fileWithProperty, $start_element, 999999, "Свойство", "Свойства", true);
			}else{
				$xml_all_svoistva = XMLParser_file($fileWithProperty, $start_element, 999999, "СвойствоНоменклатуры", "СвойстваНоменклатуры", true);
			}
			
			while ($count < $count_parts){
				$xml_product = XMLParser_file($file, $start_element, $finish_element, "Товар", "Товары");
				$count++; // Увеличение счетчика
				$start_element = $start_element + QUANTITY_DOSE;
				$finish_element = $finish_element + QUANTITY_DOSE;
				
				$last_element_upload = HeartBeat::getLastElementUpload($create_name_fileimport);
				$last_element_array = HeartBeat::jsonEncodeDecode($last_element_upload, false); 
				if (!empty($last_element_array)){
					if ($last_element_array['filepart'] <> $count) {
						write_log("Процесс(".$posix."). Пропуск чтения файла ".$nameimport."".$number_import.". Часть =".$count." (из ".$count_parts.")");
						continue;
					}
				}
				write_log("Процесс(".$posix."). Загрузка товаров файла ".$nameimport."".$number_import.". Часть =".$count." (из ".$count_parts.")");
				$remains = $count_product - ($count-1) * QUANTITY_DOSE;
				$process_count = ($count == $count_parts)? $remains : QUANTITY_DOSE;
				TovarArrayFill($xml_product, $xml_all_svoistva, $CatalogContainsChanges, $process_count, $count);
				AddDirectorySvoistva($xml_product, $xml_all_svoistva);
				unset ($xml_product);
				usleep(1000);
			}
			if ((isset($CategoryArray)) or (isset($xml_all_svoistva))){
				unset ($CategoryArray,$xml_all_svoistva);	
			}
			clearAllTransientWC();

		$status_progress = 'stop';
		if (STOP_PROGRESS == 1) {
			$status_progress = 'start';
		}
		saveStatusProgress ($create_name_fileimport, $status_progress, 'ok');
	}else{
		saveStatusProgress ($create_name_fileimport, 'stop', 'no find file ='.$create_name_fileimport.'');
		write_log("Процесс(".$posix."). Не найден файл ".$create_name_fileimport.". в папке TEMP");	
	}
	Posix::clearPosix($FilenameUpload);
	exit();
}

function uploadFileOffers($nameoffers, $number_offers, $create_name_fileoffers){
global $ShopperGroupsArray;
global $TovarIdFeatureArray;	
global $posix;
global $FilenameUpload;
$FilenameUpload = $create_name_fileoffers;
HeartBeat::start();
Posix::savePosix($posix , $FilenameUpload);

	$file = JPATH_BASE . DS .'TEMP'. DS . "".$create_name_fileoffers.""; 
	if (file_exists ($file)){
		saveStatusProgress ($create_name_fileoffers, 'progress', 'ok');	
			$isset_paket_predlozhenii = XMLParser_getelement($file, "ПакетПредложений");
			if ($isset_paket_predlozhenii == true) {
				$xml_type_price = XMLParser_file($file, 0, 9999, "ТипЦены", "ТипыЦен", true);
				//$ShopperGroupsArray = ShopperGroupsArrayFill($xml_type_price, $ShopperGroupsArray );
				
				$count_product = XMLParser_element_count($file, "Предложение");
				$count_parts = ceil($count_product / QUANTITY_DOSE);
				$count = 0;
				$start_element = 0;
				$finish_element = QUANTITY_DOSE;
				
				while ($count < $count_parts){
					$xml_offers = XMLParser_file($file, $start_element, $finish_element, "Предложение", "Предложения");			
					$count++; // Увеличение счетчика
					$start_element = $start_element + QUANTITY_DOSE;
					$finish_element = $finish_element + QUANTITY_DOSE;
					
					$last_element_upload = HeartBeat::getLastElementUpload($create_name_fileoffers);
					$last_element_array = HeartBeat::jsonEncodeDecode($last_element_upload, false); 
					if (!empty($last_element_array)){
						if ($last_element_array['filepart'] <> $count) {
							write_log("Процесс(".$posix."). Пропуск чтения файла ".$create_name_fileoffers.". Часть =".$count." (из ".$count_parts.")");	
							continue;
						}
					}
					write_log("Процесс(".$posix."). Загрузка предложений файла ".$create_name_fileoffers.". Часть =".$count." (из ".$count_parts.")");	
					$remains = $count_product - ($count-1) * QUANTITY_DOSE;
					$process_count = ($count == $count_parts)? $remains : QUANTITY_DOSE;
					product_price_update($xml_offers,$xml_type_price,$process_count, $count);
					//характерстики номенклатуры
					if (VM_FEATURES_1C == 1){
						$FeaturesArray = FeaturesArrayFill($xml_offers, $xml_type_price, 'false', $count);
						update_price_and_quantity_features($FeaturesArray);
						unset ($FeaturesArray);
					}
					unset ($xml_offers);
					usleep(1000);
				}
			}
						
			$isset_paket_izmenenir_packpredlozhenii = XMLParser_getelement($file, "ИзмененияПакетаПредложений");
			if ($isset_paket_izmenenir_packpredlozhenii == true) {	
				//$ShopperGroupsArray = ShopperGroupsArrayFillPackageOffers($ShopperGroupsArray) ;
				$xml_type_price = XMLParser_file($file, 0, 9999, "ТипЦены", "ТипыЦен", true);
				$count_product = XMLParser_element_count($file, "Предложение");
				$count_parts = ceil($count_product / QUANTITY_DOSE);
				$count = 0;
				$start_element = 0;
				$finish_element = QUANTITY_DOSE;
				while ($count < $count_parts){
					$xml_offers = XMLParser_file($file, $start_element, $finish_element, "Предложение", "Предложения");
					$count++; // Увеличение счетчика
					write_log("Процесс(".$posix."). Загрузка предложений файла ".$create_name_fileoffers.". Часть =".$count." (из ".$count_parts.")");
					$start_element = $start_element + QUANTITY_DOSE;
					$finish_element = $finish_element + QUANTITY_DOSE;
					
					$last_element_upload = HeartBeat::getLastElementUpload($create_name_fileoffers);
					$last_element_array = HeartBeat::jsonEncodeDecode($last_element_upload, false); 
					if (!empty($last_element_array)){
						if ($last_element_array['filepart'] <> $count) {
							continue;
						}
					}
					$remains = $count_product - ($count-1) * QUANTITY_DOSE;
					$process_count = ($count == $count_parts)? $remains : QUANTITY_DOSE;
					product_price_update($xml_offers, $xml_type_price,$process_count, $count);
					//характерстики номенклатуры
					if (VM_FEATURES_1C == 1){
						$FeaturesArray = FeaturesArrayFill($xml_offers, $xml_type_price, 'true', $count);
						update_price_and_quantity_features($FeaturesArray);
						unset ($FeaturesArray);
					}
					unset ($xml_offers);
					usleep(1000);			
				}			
			}												
		$status_progress = 'stop';
		if (STOP_PROGRESS == 1) {
			$status_progress = 'start';
		}
		saveStatusProgress ($create_name_fileoffers, $status_progress, 'ok');
	}else{
		saveStatusProgress ($create_name_fileoffers, 'stop', 'no find file ='.$create_name_fileoffers.'');
		write_log("Процесс(".$posix."). Не найден файл ".$create_name_fileoffers.". в папке TEMP");	
	}
	Posix::clearPosix($FilenameUpload);
	exit();	
}

function catalogImport($filename){
	//exchange_1C_Woocommerce.php?type=catalog&mode=import&filename=offers0_1.xml	
	$nameimport   = 'import';
	$nameoffers   = 'offers';
	$create_name_fileimport="";
	$create_name_fileoffers="";
	$findimport = strpos($filename, $nameimport);
	if ($findimport === false) {
	   //false
	} else {
		$number_import = str_replace($nameimport,"",$filename);
		$create_name_fileimport = "".$nameimport."".$number_import."";
		$itog_filename=$create_name_fileimport;  
	}
	$findoffers = strpos($filename, $nameoffers );
	if ($findoffers === false) {
		//false
	} else {
		$number_offers = str_replace($nameoffers,"",$filename);
		$create_name_fileoffers = "".$nameoffers."".$number_offers."";
		$itog_filename=$create_name_fileoffers;
		
		$explode_parts   = explode( '.' , $number_offers );
		$num_part  = $explode_parts[0];
		$explode_parts   = explode( '_' , $num_part );
		$num_part  = (int)$explode_parts[0];
	}
	
	$nameprices  = 'prices';
	$namerests   = 'rests';
	$create_name_fileprices="";
	$create_name_filerests="";
	$findprices = strpos($filename, $nameprices);
	if ($findprices === false) {
	   //false
	} else {
		$create_name_fileprices=$filename;
		$itog_filename=$nameprices;  
	}
	$findrests = strpos($filename, $namerests );
	if ($findrests === false) {
		//false
	} else {
		$create_name_filerests=$filename;
		$itog_filename=$namerests;
	}
	
	unset($filename);
	global $posix; 
	if (isset($itog_filename)){
		switch ($itog_filename) {
			case "".$create_name_fileimport."" :						
					$readStatus = readStatusProgress($create_name_fileimport);
					$status_progress = $readStatus['status'];
					if (($status_progress == 'start')){
						write_log("Процесс(".$posix."). Загрузка файла ".$create_name_fileimport." Начало загрузки данных");
						if (STOP_PROGRESS == 0) {
							print "progress"."\n";
						}else{
							print "success"."\n";
						}
						echo str_pad('',4096);    
						echo str_pad('',4096);
						flush();
						//uploadFileImport($nameimport, $number_import, $create_name_fileimport);	
								
						global $ThisPage;
						$query = $ThisPage.'?namefile='.$nameimport.'&number_file='.$number_import.'&create_name_file='.$create_name_fileimport;
						curlRequestAsync($query, $nameimport, $number_import, $create_name_fileimport);
					}
					if (($status_progress == 'progress')){
						write_log("Процесс(".$posix."). Загрузка файла ".$create_name_fileimport." Идет загрузка данных");
						if (STOP_PROGRESS == 0) {
							print "progress"."\n";
						}else{
							print "success"."\n";
						}
						echo str_pad('',4096);    
						echo str_pad('',4096);
						flush();	
					}
					if (($status_progress == 'stop')){
						write_log("Процесс(".$posix."). Загрузка файла ".$create_name_fileimport." Загрузка завершена");
						saveStatusProgress ($create_name_fileimport, 'start', 'ok');
						if (VM_DELETE_TEMP == 1){
							clear_files_temp($create_name_fileimport);	
						}
						print "success";		
					}
					break;
			case "".$create_name_fileoffers."" :
					$readStatus = readStatusProgress($create_name_fileoffers);
					$status_progress = $readStatus['status'];
					if (($status_progress == 'start')){
						write_log("Процесс(".$posix."). Загрузка файла ".$create_name_fileoffers." Начало загрузки данных");
						if (STOP_PROGRESS == 0) {
							print "progress"."\n";
						}else{
							print "success"."\n";
						}
						echo str_pad('',4096);    
						echo str_pad('',4096);
						flush();
						//uploadFileOffers($nameoffers, $number_offers, $create_name_fileoffers);

						global $ThisPage;
						$query = $ThisPage.'?namefile='.$nameoffers.'&number_file='.$number_offers.'&create_name_file='.$create_name_fileoffers;
						curlRequestAsync($query, $nameoffers, $number_offers, $create_name_fileoffers);
					}
					if (($status_progress == 'progress')){
						write_log("Процесс(".$posix."). Загрузка файла ".$create_name_fileoffers." Идет загрузка данных");
						if (STOP_PROGRESS == 0) {
							print "progress"."\n";
						}else{
							print "success"."\n";
						}
						echo str_pad('',4096);    
						echo str_pad('',4096);
						flush();				
					}
					if (($status_progress == 'stop')){
						write_log("Процесс(".$posix."). Загрузка файла ".$create_name_fileoffers." Загрузка завершена");
						saveStatusProgress ($create_name_fileoffers, 'start', 'ok');
						if (VM_DELETE_TEMP == 1){
							clear_files_temp($create_name_fileoffers);	
						}
						print "success";		
					}
					break;
			case "".$nameprices."" :
					$readStatus = readStatusProgress($create_name_fileprices);
					$status_progress = $readStatus['status'];
					if (($status_progress == 'start')){
						write_log("Процесс(".$posix."). Загрузка файла ".$create_name_fileprices." Начало загрузки данных");
						if (STOP_PROGRESS == 0) {
							print "progress"."\n";
						}else{
							print "success"."\n";
						}
						echo str_pad('',4096);    
						echo str_pad('',4096);
						flush();

						global $ThisPage;
						$query = $ThisPage.'?namefile='.$nameprices.'&number_file='.$create_name_fileprices.'&create_name_file='.$create_name_fileprices;
						curlRequestAsync($query, $nameprices, $create_name_fileprices, $create_name_fileprices);
					}
					if (($status_progress == 'progress')){
						write_log("Процесс(".$posix."). Загрузка файла ".$create_name_fileprices." Идет загрузка данных");
						if (STOP_PROGRESS == 0) {
							print "progress"."\n";
						}else{
							print "success"."\n";
						}
						echo str_pad('',4096);    
						echo str_pad('',4096);
						flush();				
					}
					if (($status_progress == 'stop')){
						write_log("Процесс(".$posix."). Загрузка файла ".$create_name_fileprices." Загрузка завершена");
						saveStatusProgress ($create_name_fileprices, 'start', 'ok');
						if (VM_DELETE_TEMP == 1){
							clear_files_temp($create_name_fileprices);	
						}
						print "success";		
					}
					break;
			case "".$namerests."" :
					$readStatus = readStatusProgress($create_name_filerests);
					$status_progress = $readStatus['status'];
					if (($status_progress == 'start')){
						write_log("Процесс(".$posix."). Загрузка файла ".$create_name_filerests." Начало загрузки данных");
						if (STOP_PROGRESS == 0) {
							print "progress"."\n";
						}else{
							print "success"."\n";
						}
						echo str_pad('',4096);    
						echo str_pad('',4096);
						flush();

						global $ThisPage;
						$query = $ThisPage.'?namefile='.$nameprices.'&number_file='.$create_name_filerests.'&create_name_file='.$create_name_filerests;
						curlRequestAsync($query, $nameprices, $create_name_filerests, $create_name_filerests);
					}
					if (($status_progress == 'progress')){
						write_log("Процесс(".$posix."). Загрузка файла ".$create_name_filerests." Идет загрузка данных");
						if (STOP_PROGRESS == 0) {
							print "progress"."\n";
						}else{
							print "success"."\n";
						}
						echo str_pad('',4096);    
						echo str_pad('',4096);
						flush();				
					}
					if (($status_progress == 'stop')){
						write_log("Процесс(".$posix."). Загрузка файла ".$create_name_filerests." Загрузка завершена");
						saveStatusProgress ($create_name_filerests, 'start', 'ok');
						if (VM_DELETE_TEMP == 1){
							clear_files_temp($create_name_filerests);	
						}
						print "success";		
					}
					break;
		}
	}
	unset($wpdb);
	exit();
}
//-status_exchange_1c

//*******************Этапы подключения 1с и opencart*******************

//*******************Авторизация*******************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'catalog' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'checkauth') 
{
if (($print_key == "8eec81f0fc661d4e1be860f241830b6b") or ($print_key == "901166970b348ef2777502aa53b7d035") or ($print_key == "81fb313e4737949b880577e5676db7d4")) {
		if (($domain == dsCrypt($dm,1)) or ($domain == dsCrypt($dmw,1)) or ($domain == dsCrypt($dmt,1)))  {
			CheckAuthUser();
		}
	}
}

//*******************Поключение 1с к opencart*******************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'catalog' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'export') 
{
if (($print_key == "8eec81f0fc661d4e1be860f241830b6b") or ($print_key == "901166970b348ef2777502aa53b7d035") or ($print_key == "81fb313e4737949b880577e5676db7d4")) {
	if (($domain == dsCrypt($dm,1)) or ($domain == dsCrypt($dmw,1)) or ($domain == dsCrypt($dmt,1)))  {
		print 'success';
	}
}	
}
//*******************Выбор архивировать или нет*******************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'catalog' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'init') 
{
	if (isset($_REQUEST ['version'])){
		print "zip=no" . "\n" . "file_limit=".VM_ZIPSIZE. "\n" . "xml_version". "\n" . "3.1";
	}else{
		print "zip=no" . "\n" . "file_limit=".VM_ZIPSIZE;
	}
}
//*******************Загрузка измененного заказа*******************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'sale' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'file' && isset ( $_REQUEST ['filename'] )) 
{
	CheckAccess();
	if (($domain == dsCrypt($dm,1)) or ($domain == dsCrypt($dmw,1)) or ($domain == dsCrypt($dmt ,1)))  {
		if (VM_UPDATE_STATUS_ORDER == 1){
			print LoadFileZakaz ();
		}else{
			print 'success';
		}
	}	
}
//*******************Проверка успешности загрузки файла*******************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'sale' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'import' && isset ( $_REQUEST ['filename'] ) && $_REQUEST ['filename'] == $_REQUEST ['filename']) 
{
	print 'success';	
}
//*******************Загрузка архива*******************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'catalog' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'file' && (isset($_REQUEST ['filename']))) 
{
	CheckAccess();
	if (isset($_REQUEST ['filename'])){
		$filename = $_REQUEST ['filename'];
		$result = LoadFile($filename);
		if (STOP_PROGRESS == 0) {
			print $result;
		}else{
			$findsuccess = strpos($result, 'success');
			if ($findsuccess === false) {
				print $result;
			} else {
				$filename = getFileFromPath($filename);
				$name_files_search = array('import', 'offers', 'prices', 'rests');
				$is_catalogImport = false;
				foreach($name_files_search as $name_file_search){
					$findcatalogImport = strpos($filename, $name_file_search);
					if ($findcatalogImport === false) {
					   //false
					} else {
						$is_catalogImport = true;  
					}
				}
				if ($is_catalogImport == true){
					catalogImport($filename);
				}else{
					print $result;
				}
			}
		}
	}
}
//*******************Операция с файлами*******************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'catalog' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'import')
{
	CheckAccess();
	if (isset($_REQUEST ['filename'])){
		$filename = $_REQUEST ['filename'];
		$filename = getFileFromPath($filename);
		catalogImport($filename);
	}
}

//*******************Поключение opencart к 1с*******************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'sale' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'query') 
{
	CheckAccess();
	if ((VM_USE_BITRIX == 1) or (isset($_REQUEST ['version']))){
		$use_bitrix  = true;
		CreateZakaz($use_bitrix);
	}else{
		CreateZakaz();
	}		
}
//*******************Проверка подключения для обмена заказами*******************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'sale' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'checkauth') 
{
	if (($domain == dsCrypt($dm,1)) or ($domain == dsCrypt($dmw,1)) or ($domain == dsCrypt($dmt,1)))  {	
		CheckAuthUser();
	}
}

//*******************Выбор архивировать или нет********************************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'sale' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'init') 
{
	if (isset($_REQUEST ['version'])){
		print "zip=" . "no" . "\n" . VM_ZIPSIZE. "\n" . "xml_version". "\n" . "3.1";
	}else{
		print "zip=" . "no" . "\n" . VM_ZIPSIZE;
	}
}

if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'sale' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'success') 
{
	print 'success';
}

//********************Информация о методах доставки и статусов заказа на сайте (УНФ 1.6.5, требуется расширение АдаптацияОбменаССайтомДляExchange1COpencartУНФ16.cfe)*************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'sale' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'info') 
{
global $wpdb;
	
	$no_spaces ='<?xml version="1.0" encoding="UTF-8"?><saleinfo></saleinfo>';
	$xml = new SimpleXMLElement ( $no_spaces );
	
	require_once ( JPATH_BASE .DS.'wp-content'.DS.'plugins'.DS.'woocommerce'.DS.'includes'.DS.'wc-order-functions.php');
	if (function_exists('wc_get_order_statuses')){
		$order_statuses = wc_get_order_statuses();
		if (isset($order_statuses)){
			$status_doc = $xml->addChild ( "Статусы" );
			foreach ($order_statuses as $key=>$value) {
				$t1 = $status_doc->addChild ( "Элемент");
				$t2 = $t1->addChild ( "Ид", $key );
				$t2 = $t1->addChild ( "Название", $value);
			}
		}
	}
	
	$shipping_method_query  = $wpdb->get_results( "SELECT DISTINCT order_item_name FROM " . DB_PREFIX . "woocommerce_order_items WHERE order_item_type = 'shipping'");
	if (count($shipping_method_query)>0){
		$delivery_doc = $xml->addChild ( "СлужбыДоставки" );
		foreach($shipping_method_query as $shipping_method){			
			$shipping_code = rus2translit($shipping_method->order_item_name);
			$shipping_code = mb_substr((md5($shipping_code)),1,10); // формируем уникальный код из наименования
			$t1 = $delivery_doc->addChild ( "Элемент");
			$t2 = $t1->addChild ( "Ид", $shipping_code ); 
			$t2 = $t1->addChild ( "Название", $shipping_method->order_item_name);
		}
	}
	
	$payment_method_query  = $wpdb->get_results( "SELECT DISTINCT meta_value FROM " . DB_PREFIX . "postmeta WHERE meta_key = '_payment_method_title'");
	if (count($payment_method_query)>0){
		$payment_method_doc = $xml->addChild ( "ПлатежныеСистемы" );
		foreach($payment_method_query as $payment_method_result){
			$payment_method_code = rus2translit($payment_method_result->meta_value);
			$payment_method_code = mb_substr((md5($payment_method_code)),1,10); // формируем уникальный код из наименования
			$t1 = $payment_method_doc->addChild ( "Элемент");
			$t2 = $t1->addChild ( "Ид", $payment_method_code );
			$t2 = $t1->addChild ( "Название", $payment_method_result->meta_value);
		}
		$t1 = $payment_method_doc->addChild ( "Элемент");
		$t2 = $t1->addChild ( "Ид", "Интернет" );
		$t2 = $t1->addChild ( "Название", "Интернет");
	}
		
	if (VM_CODING == 'UTF-8'){
		$xml_text = $xml->asXML();
		header("Content-Type: text/xml");
		$text = iconv( "UTF-8", "CP1251//IGNORE", $xml_text );
		print $text;
	}else {
		header("Content-Type: text/xml");
		print $xml->asXML ();
	}
}

//*******************Запуск асинхронного разбора файлов********************************
if ((isset($_GET['namefile'])) and (isset($_GET['number_file'])) and (isset($_GET['create_name_file']))) {
	$namefile = $_GET['namefile'];
	$number_file = $_GET['number_file'];
	$create_name_file = $_GET['create_name_file'];
	global $posix;
	write_log("Процесс(".$posix."). Запуск асинхронного разбора файла: ".$create_name_file);
	startUpload($namefile, $number_file, $create_name_file);
}

//********************Очистка состояний обмена*************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'exchange' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'clear') 
{
	//exchange_1C_Woocommerce.php?type=exchange&mode=clear
	global $wpdb;	
	$status_query  = $wpdb->get_results( "SELECT * FROM " . DB_PREFIX . "status_exchange_1c"); 
	if (count($status_query)>0) {
		foreach ($status_query as $status_exchange){
			$filename = $status_exchange->filename;
			saveStatusProgress ($filename, 'start', 'clear');	
		}
	}
	echo 'sucsess! clear loads';
}

//********************Отобразить состояние обмена на сайте*************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'exchange' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'status') 
{
//exchange_1C_Opencart.php?type=exchange&mode=status
global $wpdb;	
	$status_query  = $wpdb->get_results ( "SELECT * FROM " . DB_PREFIX . "status_exchange_1c"); 
	if (count($status_query)>0) {
		print('<table border="1">');
		print('<tr>');
		print('<td>ID</td>');
		print('<td>FILENAME</td>');
		print('<td>STATUS</td>');
		print('<td>ERROR</td>');
		print('<td>DATE_EXCHANGE</td>');
		print('</tr>');
		foreach ($status_query as $status_exchange){
			print('<tr>');
			$id = $status_exchange->id;
			print('<td>'.$id.'</td>');
			$filename = $status_exchange->filename;
			print('<td>'.$filename.'</td>');
			$status = $status_exchange->status;
			print('<td>'.$status.'</td>');
			$error = $status_exchange->error;
			print('<td>'.$error.'</td>');
			$date_exchange = $status_exchange->date_exchange;
			print('<td>'.$date_exchange.'</td>');
			print('</tr>');
		}
		print('</table>');
	}else{
		print('no data exchange.');
	}
	exit();
}

//***************ДеактивацияДанныхПоДате*************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'catalog' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'deactivate') 
{
	print 'success';
}

//***************ОкончаниеВыгрузкиТоваров*************
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'catalog' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'complete') 
{
	print 'success';
}

//******Аутентификация для передачи доп. данных*******
if (isset ( $_REQUEST ['type'] ) && $_REQUEST ['type'] == 'reference' && isset ( $_REQUEST ['mode'] ) && $_REQUEST ['mode'] == 'checkauth') 
{
	print 'failure'."\n" . 'not use this function in module';
}
?>