<?php
//***************************************************************
//****Модуль интеграции WooCommerce и 1С Управление торговлей****
//***********************Версия 2.2.2****************************
//*******************Установка и настройка БД********************
//**********************Copyright 2024***************************
//*****************Файл настроек и установки*********************
//***************************************************************
//***************************************************************
if (!isset($_COOKIE)) { print('COOKIE is not to work on this website.'); exit(); }
error_reporting(E_ALL);
ini_set('display_errors', 'On');
header ( "Content-type: text/HTML; charset=utf-8" );
define( '_JEXEC', 1 );
$ver = (float)phpversion();
if ($ver < 5.3) {
    echo 'Для продолжения установки, необходимо обновить версию PHP на хостинге до 5.3 или выше.';
	exit();
}
if(!defined('DS')){
   define('DS',DIRECTORY_SEPARATOR);
}
$directory = search_dir();
define ( 'JPATH_BASE', dirname ( __FILE__ )); //Путь к директории где установлен движок Wordpress.
$directory_logs = JPATH_BASE.DS.'logs';
if (!file_exists($directory_logs)) {
	mkdir($directory_logs, 0755);
}
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

//****************Запуск удаленной установки*******************
if ((isset($_GET['setup']))) {
	UpdateDB();
	CreateNewSetting();
	CreateUpdateJSONStatus();
	validatePhpInfo();
	exit;
}

if (isset($_POST['submit'])){ // Отлавливаем нажатие кнопки "Отправить"
	if (empty($_POST['login'])){ // Если поле логин пустое
		echo '<script>alert("Поле логин не заполненно");</script>'; // То выводим сообщение об ошибке
	}elseif (empty($_POST['password'])){ // Если поле пароль пустое	
		echo '<script>alert("Поле пароль не заполненно");</script>'; // То выводим сообщение об ошибке
	}else{   // Иначе если все поля заполненны   
		$username = $_POST['login']; // Записываем логин в переменную 
		$password = $_POST['password']; // Записываем пароль в переменную  
		
		require_once(JPATH_BASE.'/wp-includes/class-phpass.php');
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
		
		if (!empty($check)) {
			$id_module_1c = time();
			setcookie("id_module_1c", (string)$id_module_1c, time()+3600);
			setcookie("login_module_1c", $username, time()+3600);        
		}else{
			echo '<script>alert("Неверные Логин или Пароль");</script>'; // Значит такой пользователь не существует или не верен пароль
		}
	}
	$page_redirect = $_SERVER['REQUEST_URI'];
	$page_redirect = str_replace("?exit", "", $page_redirect);
	echo '<meta http-equiv="refresh" content="0;URL='.$page_redirect.'">';
} 
?>
<style>
html, body, div, span, applet, object, iframe,
h1, h2, h3, h4, h5, h6, p, blockquote, pre,
a, abbr, acronym, address, big, cite, code,
del, dfn, em, img, ins, kbd, q, s, samp,
small, strike, strong, sub, sup, tt, var,
b, u, i, center,
dl, dt, dd, ol, ul, li,
fieldset, form, label, legend, caption, tbody, tfoot, thead, tr, th, td,
article, aside, canvas, details, embed,
figure, figcaption, footer, header, hgroup,
menu, nav, output, ruby, section, summary,
time, mark, audio, video {
  margin: 0;
  padding: 0;
  border: 0;
  font-size: 100%;
  font: inherit;
}
article, aside, details, figcaption, figure,
footer, header, hgroup, menu, nav, section {
  display: block;
}

body {
  line-height: 1;
}

ol, ul {
  list-style: none;
}

blockquote, q {
  quotes: none;
}

blockquote:before, blockquote:after,
q:before, q:after {
  content: '';
  content: none;
}

table {
  border-collapse: collapse;
  border-spacing: 0;
}

body, .login-submit, .login-submit:before, .login-submit:after {
  background: #373737 0 0 repeat;
}

body {
  font: 14px/20px 'Helvetica Neue', Helvetica, Arial, sans-serif;
  color: rgb(135, 133, 133);
}

a {
  color: #00a1d2;
  text-decoration: none;
}
a:hover {
  text-decoration: underline;
}

.login {
  position: relative;
  margin: 200px auto;
  width: 400px;
  padding-right: 32px;
  font-weight: 300;
  color: #a8a7a8;
  text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.8);
}
.login p {
  margin: 0 0 10px;
}

input, button, label {
  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
  font-size: 15px;
  font-weight: 300;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}

input[type=checkbox], input[type=hidden]{
  padding: 0 10px;
  width: 50px;
  height: 40px;
  color: #bbb;
  text-shadow: 1px 1px 1px black;
  background: rgba(0, 0, 0, 0.16);
  border: 0;
  border-radius: 5px;
  -webkit-box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.3), 0 1px rgba(255, 255, 255, 0.06);
  box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.3), 0 1px rgba(255, 255, 255, 0.06);
}

select {
	margin: 5px 0;
  padding: 0 10px;
  width: auto;
  height: 40px;
  color: #bbb;
  text-shadow: 1px 1px 1px black;
  background: rgba(0, 0, 0, 0.16);
  border: 0;
  border-radius: 5px;
  -webkit-box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.3), 0 1px rgba(255, 255, 255, 0.06);
  box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.3), 0 1px rgba(255, 255, 255, 0.06);
}

input[type=radio]{
  vertical-align: bottom;
  padding: 0 10px;
  width: 20px;
  height: 20px;
  color: #bbb;
  text-shadow: 1px 1px 1px black;
  background: rgba(0, 0, 0, 0.16);
  border: 0;
  border-radius: 5px;
  -webkit-box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.3), 0 1px rgba(255, 255, 255, 0.06);
  box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.3), 0 1px rgba(255, 255, 255, 0.06);
}

input[type=text], input[type=password] {
  padding: 0 10px;
  width: 300px;
  height: 40px;
  color: #bbb;
  text-shadow: 1px 1px 1px black;
  background: rgba(0, 0, 0, 0.16);
  border: 0;
  border-radius: 5px;
  -webkit-box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.3), 0 1px rgba(255, 255, 255, 0.06);
  box-shadow: inset 0 1px 4px rgba(0, 0, 0, 0.3), 0 1px rgba(255, 255, 255, 0.06);
}
input[type=text]:focus, input[type=password]:focus {
  color: white;
  background: rgba(0, 0, 0, 0.1);
  outline: 0;
}

label {
  float: left;
  width: 100px;
  line-height: 40px;
  padding-right: 10px;
  font-weight: 100;
  text-align: right;
  letter-spacing: 1px;
}

.labeltext {
  float: left;
  width: 400px;
  line-height: 40px;
  padding-right: 10px;
  font-weight: 100;
  text-align: right;
  letter-spacing: 1px;
}

.forgot-password {
  padding-left: 100px;
  font-size: 13px;
  font-weight: 100;
  letter-spacing: 1px;
}

.login-submit {
  position: absolute;
  top: 12px;
  right: 0;
  width: 48px;
  height: 48px;
  padding: 8px;
  border-radius: 32px;
  -webkit-box-shadow: 0 0 4px rgba(0, 0, 0, 0.35);
  box-shadow: 0 0 4px rgba(0, 0, 0, 0.35);
}
.login-submit:before, .login-submit:after {
  content: '';
  z-index: 1;
  position: absolute;
}
.login-submit:before {
  top: 28px;
  left: -4px;
  width: 4px;
  height: 10px;
  -webkit-box-shadow: inset 0 1px rgba(255, 255, 255, 0.06);
  box-shadow: inset 0 1px rgba(255, 255, 255, 0.06);
}
.login-submit:after {
  top: -4px;
  bottom: -4px;
  right: -4px;
  width: 36px;
}

.login-button {
  position: relative;
  z-index: 2;
  width: 70px;
  height: 48px;
  padding: 0 0 48px;
  /* Fix wrong positioning in Firefox 9 & older (bug 450418) */
  white-space: nowrap;
  overflow: hidden;
  background: none;
  border: 0;
  border-radius: 24px;
  cursor: pointer;
  -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.2), 0 1px rgba(255, 255, 255, 0.1);
  box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.2), 0 1px rgba(255, 255, 255, 0.1);
  /* Must use another pseudo element for the gradient background because Webkit */
  /* clips the background incorrectly inside elements with a border-radius.     */
}
.login-button:before {
  content: '';
  position: absolute;
  top: 5px;
  bottom: 5px;
  left: 5px;
  right: 5px;
  background: #00a2d3;
  border-radius: 24px;
  background-image: -webkit-linear-gradient(top, #00a2d3, #0d7796);
  background-image: -moz-linear-gradient(top, #00a2d3, #0d7796);
  background-image: -o-linear-gradient(top, #00a2d3, #0d7796);
  background-image: linear-gradient(to bottom, #00a2d3, #0d7796);
  -webkit-box-shadow: inset 0 0 0 1px #00a2d3, 0 0 0 5px rgba(0, 0, 0, 0.16);
  box-shadow: inset 0 0 0 1px #00a2d3, 0 0 0 5px rgba(0, 0, 0, 0.16);
}
.login-button:active:before {
  background: #0591ba;
  background-image: -webkit-linear-gradient(top, #0591ba, #00a2d3);
  background-image: -moz-linear-gradient(top, #0591ba, #00a2d3);
  background-image: -o-linear-gradient(top, #0591ba, #00a2d3);
  background-image: linear-gradient(to bottom, #0591ba, #00a2d3);
}
.login-button:after {
  content: 'Войти';
  position: absolute;
  top: 15px;
  left: 12px;
  width: 25px;
  height: 19px;
}

::-moz-focus-inner {
  border: 0;
  padding: 0;
}

.lt-ie9 input[type=text], .lt-ie9 input[type=password] {
  line-height: 40px;
  background: #282828;
}
.lt-ie9 .login-submit {
  position: absolute;
  top: 12px;
  right: -28px;
  padding: 4px;
}
.lt-ie9 .login-submit:before, .lt-ie9 .login-submit:after {
  display: none;
}
.lt-ie9 .login-button {
  line-height: 48px;
}
.lt-ie9 .about {
  background: #313131;
}

.buttonsave{
	width: 90%;
	margin-left: 10px;
	padding-left: 20px;
	padding-right: 20px;
	height: 50px;
	margin-top: 7px;
	color: #fff;
	font-size: 18px;
	font-weight: bold;
	text-shadow: 0px -1px 0px #5b6ddc;
	outline: none;
	border: 1px solid rgba(0, 0, 0, .49);
	background-clip: padding-box;
	border-radius: 6px;
	background-color: #5466da;
	background-image: -webkit-linear-gradient(bottom, #5466da 0%, #768ee4 100%);
	background-image: -moz-linear-gradient(bottom, #5466da 0%, #768ee4 100%);
	background-image: -o-linear-gradient(bottom, #5466da 0%, #768ee4 100%);
	background-image: -ms-linear-gradient(bottom, #5466da 0%, #768ee4 100%);
	background-image: linear-gradient(bottom, #5466da 0%, #768ee4 100%);
	box-shadow: inset 0px 1px 0px #9ab1ec;
	cursor: pointer;
	transition: all .1s ease-in-out;
}

.buttondelete{
	width: 90%;
	margin-left:10px;
	height: 50px;
	margin-top: 7px;
	color: #fff;
	font-size: 18px;
	font-weight: bold;
	text-shadow: 0px -1px 0px #5b6ddc;
	outline: none;
	border: 1px solid rgba(0, 0, 0, .49);
	background-clip: padding-box;
	border-radius: 6px;
	background-color: #da5468;
	box-shadow: inset 0px 1px 0px #9ab1ec;
	cursor: pointer;
	transition: all .1s ease-in-out;
}

.block1 {
  margin: 20px auto;
  padding: 20px;
  font-weight: 300;
  color: #a8a7a8;
  text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.8);
  box-shadow: -2px 23px 42px -26px #000000;
}

.help {
    display: inline-block; /* Строчно-блочный элемент */
    position: relative; /* Относительное позиционирование */
   }
.help:hover::after {
    content: attr(data-title); /* Выводим текст */
    position: absolute; /* Абсолютное позиционирование */
    left: 0; right: 0; bottom: 5px; /* Положение подсказки */
    z-index: 1; /* Отображаем подсказку поверх других элементов */
    background: rgba(0,42,167,0.6); /* Полупрозрачный цвет фона */
    color: #fff; /* Цвет текста */
    text-align: center; /* Выравнивание текста по центру */
    font-family: Arial, sans-serif; /* Гарнитура шрифта */
    font-size: 11px; /* Размер текста подсказки */
    padding: 5px 10px; /* Поля */
    border: 1px solid #333; /* Параметры рамки */
	width: 200px;
   }
   
.labelradio {
	display: inline-block;
	cursor: pointer;
	position: relative;
	padding-left: 25px;
	font-size: 13px;
}
.blockproperty {
  padding-top: 8px;
}

.elementproperty {
  margin: 5px auto;
}
</style>
<?php
if ((!isset($_COOKIE['login_module_1c'])) or (!isset($_COOKIE['id_module_1c']))){ // если  не загружены логин и id
?>
<div align="center" >
<form id="autorizate" method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>" class="login">
    <p>
      <label for="login">Логин:</label>
      <input type="text" name="login" id="login" value="login">
    </p>

    <p>
      <label for="password">Пароль:</label>
      <input type="password" name="password" id="password" value="pass">
    </p>

    <p class="login-submit">
      <button name="submit" type="submit" class="login-button">Войти</button>
    </p>
 </form>
 </div>
<?php
}
if (isset($_GET['exit'])) { // если вызвали переменную "exit"
	setcookie('id_module_1c', null, -1, '/'); 
	setcookie('login_module_1c', null, -1, '/'); 
	$page_redirect = $_SERVER['REQUEST_URI'];
	$page_redirect = str_replace("?exit", "", $page_redirect);
	echo '<meta http-equiv="refresh" content="0;URL='.$page_redirect.'">';
} 

if ((isset($_COOKIE['login_module_1c'])) && (isset($_COOKIE['id_module_1c'])) ){ // если  загружены логин и id
	echo '<div align="center"><br>Вы успешно вошли в систему: '.$_COOKIE['login_module_1c'].'</div>'; // Выводим сообщение что пользователь авторизирован       
	echo '<div align="center"><a href="'.$_SERVER['REQUEST_URI'].'?exit">Выход</a></div>';
//********************************************
//+main code
	UpdateDB();
	if ((null !== VERSION_MODULE)){
		$version_output = '';
		$version_query = $wpdb->get_results("SELECT * FROM " . DB_PREFIX ."options where option_name = 'woocommerce_version'" );	
		if ((count($version_query) > 0)) {
			foreach($version_query as $version_result){
				$version_output = $version_result->option_value;	
			}
		}
		echo ('<span style="float:left; margin-left:20px;">Version: '.VERSION_MODULE.' for WooCommerce '.$version_output.'</span><br>');
	}
	validatePhpInfo();
	printAddInfo();
	CreateNewSetting();
	CreateUpdateJSONStatus();
	//read setting in db
	$search_setting = $wpdb->get_results("SELECT * FROM " . DB_PREFIX . "setting_exchange_1c ORDER BY 'type'"); 
	$array_type = array();
	if (count($search_setting)>0){
		foreach($search_setting as $type){
			$array_type[] = $type->type;
		}
		$array_type = array_unique($array_type);
	}
?>
<br>
<form id="save" method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">
<?php
foreach ($array_type as $type){
	print '<div class="block1">'.$type.'</div>';
	print '<table>';
	foreach ($search_setting as $setting){
		$name_setting = $setting->name_setting;
		$value_setting = $setting->value;
		$name_rus = $setting->name_rus;
		$comment = $setting->comment;
		$typest = $setting->type;
		if ($type == $typest){
			print '<tr>';
			print '<td>';
			print '<label for="'.$name_setting.'" class="labeltext">'.$name_rus.'</label>';
			print '</td>';
			if (($value_setting == '1') and ($name_setting <> 'VM_ORDER_STATUS_PROCESSING')){
				print '<td>';
				print '<input type="checkbox" checked name="'.$name_setting.'" value="'.$value_setting.'">';
				print '</td>';
			}elseif(($value_setting == '0') and ($name_setting <> 'VM_ORDER_STATUS_PROCESSING')){
				print '<td>';
				print '<input type="checkbox" name="'.$name_setting.'" value="'.$value_setting.'">';
				print '</td>';
			}else{
				if ($name_setting == 'VM_CODING' ){
					print '<td style="vertical-align: midlle; padding-bottom: 10px;">';
					if ($value_setting == 'UTF-8'){
							print '<input type="radio" name="'.$name_setting.'" value="Auto">Auto</input>
								   <input type="radio" name="'.$name_setting.'" value="'.$value_setting.'" checked>'.$value_setting.'</input>
								   <input type="radio" name="'.$name_setting.'" value="Default">Default</input>';
						}elseif($value_setting == 'Default'){
							print '<input type="radio" name="'.$name_setting.'" value="Auto">Auto</input>
								   <input type="radio" name="'.$name_setting.'" value="UTF-8">UTF-8</input>
								   <input type="radio" name="'.$name_setting.'" value="'.$value_setting.'" checked>'.$value_setting.'</input>';
						}else{						
							print '<input type="radio" name="'.$name_setting.'" value="'.$value_setting.'" checked>'.$value_setting.'</input>
							       <input type="radio" name="'.$name_setting.'" value="UTF-8">UTF-8</input>
								   <input type="radio" name="'.$name_setting.'" value="Default">Default</input>';
						}
					print '</td>';
				//VM_DELETE_MARK_PRODUCT
				}elseif($name_setting == 'VM_DELETE_MARK_PRODUCT' ){
					print '<td style="vertical-align: midlle; padding-bottom: 10px;">';
					if ($value_setting == 'HIDE'){
							print '<input type="radio" name="'.$name_setting.'" value="'.$value_setting.'" checked>'.$value_setting.'</input>
								   <input type="radio" name="'.$name_setting.'" value="DELETE">DELETE</input>';
						}else{						
							print '<input type="radio" name="'.$name_setting.'" value="HIDE">HIDE</input>
							<input type="radio" name="'.$name_setting.'" value="'.$value_setting.'" checked>'.$value_setting.'</input>';
						}
					print '</td>';	
				}elseif($name_setting == 'QUANTITY_DOSE' ){	
					print '<td>';
					print '<input type="text" name="'.$name_setting.'" value="'.$value_setting.'">';
					print '</td>';
				}elseif($name_setting == 'VM_TIME_LIMIT' ){
					foreach ($search_setting as $setting1){
						$name_setting1 = $setting1->name_setting;
						$value_setting1= $setting1->value;
						if ($name_setting1 == 'USE_HEARBEAT') {
							$visible = $value_setting1;
						}
					}
					unset($name_setting1, $value_setting1);
					if ($visible == '1'){
						print '<td>';
						print '<input type="text" name="'.$name_setting.'" value="'.$value_setting.'">';
						print '</td>';
					}else{
						print '<td>';
						print '<input type="hidden" name="'.$name_setting.'" value="'.$value_setting.'">';
						print '</td>';
					}					
				}elseif ($name_setting == 'VM_STATUS_EXCHANGE' ){
					print '<td>';
					$std_status_setting = json_decode($value_setting, false);
					foreach($std_status_setting as $status_setting){
						$status_id = $status_setting->status_id;
						$name = rus2translit($status_setting->name);
						$name = str_replace(" ", "_", $name);
						$enable_exchange = $status_setting->enable_exchange;
						print '<div class="block2">'; 
						if ($enable_exchange == '1') {
							print '<input type="checkbox" name="'.$name.$status_id.'" value="1" checked>'.$status_setting->name.'</input><br>';
						}else{
							print '<input type="checkbox" name="'.$name.$status_id.'" value="0">'.$status_setting->name.'</input><br>';
						}
						print '</div>';
					}
					print '</td>';
				}elseif ($name_setting == 'VM_ORDER_DATE_LOAD' ){
					print '<td>';
					print '<input type="date" name="'.$name_setting.'" value="'.$value_setting.'" style="padding-left: 8; margin-left: 8;">';
					print '</td>';
				}elseif ($name_setting == 'VM_STOCK_STATUS' ){
					$stock_status_array = getStockStatus();
					if (!empty($stock_status_array)){
						print '<td><select id="'.$name_setting.'" name="'.$name_setting.'">';
						foreach($stock_status_array as $id_stock => $value_stock){
							if ($id_stock == $value_setting){
								print '<option value="'.$id_stock.'" selected>'.$value_stock.'</option>';
							}else{
								print '<option value="'.$id_stock.'">'.$value_stock.'</option>';
							}
						}
						print '</select>';
						print '</td>';
					}
				}elseif ($name_setting == 'VM_ORDER_STATUS_PROCESSING' ){
					print '<td>';
					PrintOrderStatuses($value_setting, 'VM_ORDER_STATUS_PROCESSING');
					print '</td>';
				}elseif ($name_setting == 'VM_ORDER_STATUS_PAID' ){
					print '<td>';
					PrintOrderStatuses($value_setting, 'VM_ORDER_STATUS_PAID');
					print '</td>';
				}elseif ($name_setting == 'VM_ORDER_STATUS_COMPLETE' ){
					print '<td>';
					PrintOrderStatuses($value_setting, 'VM_ORDER_STATUS_COMPLETE');
					print '</td>';
				}elseif ($name_setting == 'VM_PROPERTY_STOP_LIST' ){
					print '<td>';
					print '<div class="blockproperty">'; 
					createPropertyList('VM_PROPERTY_STOP_LIST');
					print '</div>';
					print '</td>';
				}elseif ($name_setting == 'VM_PROPERTY_VARIATION_STOP_LIST' ){
					print '<td>';
					print '<div class="blockproperty">'; 
					createPropertyList('VM_PROPERTY_VARIATION_STOP_LIST');
					print '</div>';
					print '</td>';
				}else{
					print '<td>';
					print '<input type="text" name="'.$name_setting.'" value="'.$value_setting.'">';
					print '</td>';
				}
			}
			print '<td style="vertical-align: bottom; padding-left: 20px; padding-bottom: 10px">';
			print '<div class="help" data-title="'.$comment.'"><a href="">?</a></div>';
			print '</td>';
			print '</tr>';
		}
	}
	print '</table>';
}	
?>
 <br>
 <table>
 <tr>
 <td><button name="save" class="buttonsave" type="submit">СОХРАНИТЬ</button></td>
 <td><button name="delete_all" class="buttondelete" type="submit">УДАЛИТЬ МОДУЛЬ</button></td>
 <tr>
 </table>
</form>
<br>
<?php 
	if (isset($_POST['save'])){
		foreach ($search_setting as $setting){
		$name_setting = $setting->name_setting;
		$value_setting = $setting->value;
		$name_rus = $setting->name_rus;
			if (($name_setting <> 'VM_STATUS_EXCHANGE') and ($name_setting <> 'VM_PROPERTY_STOP_LIST') and ($name_setting <> 'VM_PROPERTY_VARIATION_STOP_LIST')){
				if ($name_setting == 'QUANTITY_DOSE'){	
					$set_value = 100;
					if (isset ($_POST[$name_setting])){
						$new_value_setting = $_POST[$name_setting];
						$success_convert = settype($new_value_setting, "integer");
						if (($success_convert) and ($new_value_setting > 1)){
							$set_value = $new_value_setting;
						}
					}
					$wpdb->query("UPDATE " . DB_PREFIX . "setting_exchange_1c SET value='".$set_value."' where name_setting='".$name_setting."'");
				}elseif($name_setting == 'VM_TIME_LIMIT'){
					$max_execution_time = ini_get('max_execution_time');
					$timeLimit = ((int)$max_execution_time <= 1) ? 30 : (int)$max_execution_time;
					$set_value = $timeLimit;
					if (isset ($_POST[$name_setting])){
						$new_value_setting = $_POST[$name_setting];
						$success_convert = settype($new_value_setting, "integer");
						if (($success_convert) and ($new_value_setting > 1)){
							$set_value = $new_value_setting;
						}
					}
					$wpdb->query("UPDATE " . DB_PREFIX . "setting_exchange_1c SET value='".$set_value."' where name_setting='".$name_setting."'");
				}elseif($name_setting == 'VM_STOCK_STATUS'){
					if (isset ($_POST[$name_setting])){
						$new_value_setting = $_POST[$name_setting];	
						$wpdb->query("UPDATE " . DB_PREFIX . "setting_exchange_1c SET value='".$new_value_setting."' where name_setting='".$name_setting."'");
					}
				}elseif($name_setting == 'VM_ORDER_STATUS_PROCESSING'){
					if (isset ($_POST[$name_setting])){
						$new_value_setting = $_POST[$name_setting];	
						$wpdb->query("UPDATE " . DB_PREFIX . "setting_exchange_1c SET value='".$new_value_setting."' where name_setting='".$name_setting."'");
					}
				}else{
						if (isset ($_POST[$name_setting])){
							$new_value_setting = $_POST[$name_setting];
							if ($_POST[$name_setting] == '0'){
								$wpdb->query("UPDATE " . DB_PREFIX . "setting_exchange_1c SET value='1' where name_setting='".$name_setting."'");
							}else{
								$wpdb->query("UPDATE " . DB_PREFIX . "setting_exchange_1c SET value='".$new_value_setting."' where name_setting='".$name_setting."'");
							}
						}
						if ((!isset ($_POST[$name_setting]))){
							$wpdb->query("UPDATE " . DB_PREFIX . "setting_exchange_1c SET value='0' where name_setting='".$name_setting."'");
						}
				}
			}		
		}
		
		//+сохранение статусов	
		$search_query = $wpdb->get_results("SELECT value FROM " . DB_PREFIX . "setting_exchange_1c WHERE name_setting = 'VM_STATUS_EXCHANGE'"); 
		foreach ($search_query as $value){
			$search_setting = $value->value;
		}
		//$search_setting = $search_query->row['value'];
					
		if (isset($search_setting)){
			$array_status_setting = json_decode($search_setting, true);
			foreach ($array_status_setting as $k => $v) {
				if (isset($array_status_setting[$k]['enable_exchange'])){
					$array_status_setting[$k]['enable_exchange']='0';
				}
								
			}				
			$array_to_save = array();
			$std_status_setting = json_decode($search_setting, false);
			foreach($std_status_setting as $status_setting){
				$name = rus2translit($status_setting->name);
				$name = str_replace(" ", "_", $name);
				$status_id = $status_setting->status_id;
				$name_setting = $name.$status_id;
				if (isset ($_POST[$name_setting]) ){
					$array_to_save[($status_id)] = array(
							  "status_id" => $status_id,
							  "name" => $status_setting->name,
							  "enable_exchange" => '1'
							);			
				}
			}
			$result = array_replace($array_status_setting, $array_to_save);			
			$encoded = json_encode($result);
			$json_statuses = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
				return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
			}, $encoded);
			$wpdb->query("UPDATE " . DB_PREFIX . "setting_exchange_1c SET value='".$json_statuses."' where name_setting='VM_STATUS_EXCHANGE'");
		}
		//-сохранение статусов

		//+сохранение VM_PROPERTY_STOP_LIST
		$array_to_save = array();	
		foreach ($_POST as $post_key=>$post_value){
			$findPropertyStop = strpos($post_key, 'property_stop_name_');
			if ($findPropertyStop === false) {
			} else {	
				if (!empty($post_value)){
					$post_value = formatString($post_value, 1);
					$input_id = str_replace("property_stop_name_","",$post_key);
					$array_to_save[$input_id] = array(
						"input_id" => $input_id,	
						"input_name" => $post_key,
						"input_value" => $post_value
						);
				}
			}
		}
		$encoded = json_encode($array_to_save);
		$json_result = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
			return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
		}, $encoded);
		$wpdb->query("UPDATE " . DB_PREFIX . "setting_exchange_1c SET value='".$json_result."' where name_setting='VM_PROPERTY_STOP_LIST'");
		//-сохранение VM_PROPERTY_STOP_LIST
		
		//+сохранение VM_PROPERTY_VARIATION_STOP_LIST
		$array_to_save = array();	
		foreach ($_POST as $post_key=>$post_value){	
			$findPropertyStop = strpos($post_key, 'property_variation_stop_name_');
			if ($findPropertyStop === false) {
			} else {	
				if (!empty($post_value)){			
					$post_value = formatString($post_value, 1);
					$input_id = str_replace("property_variation_stop_name_","",$post_key);
					$array_to_save[$input_id] = array(
						"input_id" => $input_id,	
						"input_name" => $post_key,
						"input_value" => $post_value
						);
				}
			}
		}
		$encoded = json_encode($array_to_save);
		$json_result = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
			return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
		}, $encoded);
		$wpdb->query("UPDATE " . DB_PREFIX . "setting_exchange_1c SET value='".$json_result."' where name_setting='VM_PROPERTY_VARIATION_STOP_LIST'");
		//-сохранение VM_PROPERTY_VARIATION_STOP_LIST
		
		echo 'Сохранение настроек... Пожалуйста, подождите.';
		echo '<script>alert("Сохранение настроек... Пожалуйста, подождите.");</script>';
		$page_redirect = $_SERVER['REQUEST_URI'];
		$page_redirect = str_replace("?exit", "", $page_redirect);
		echo '<meta http-equiv="refresh" content="0;URL='.$page_redirect.'">';
	}
	
	if (isset($_POST['delete_all'])){
		$sqlDeleteTable = "DROP TABLE IF EXISTS ".DB_PREFIX."setting_exchange_1c";
		$wpdb->query($sqlDeleteTable);
		
		echo 'Удаление модуля интерации с сайта... Пожалуйста, подождите.';
		$page_redirect = $_SERVER['REQUEST_URI'];
		$page_redirect = str_replace("?exit", "", $page_redirect);
		echo '<meta http-equiv="refresh" content="0;URL='.$page_redirect.'">';
	}

}	
//-main code
//*******************************************************
?>
<?php
//function sector
function search_dir() {
	$dir_file = dirname(__FILE__);
	$dir_dir = dirname ( __DIR__ );
	$directory_public_html_1 = str_replace($dir_dir,"",$dir_file);
	$directory_public_html_2 = str_replace("/","",$directory_public_html_1);
	$directory_public_html_cc = stripcslashes($directory_public_html_2);
	return $directory_public_html_cc;
} 

function UpdateDB() {
global $wpdb;
$prefixtable = $wpdb->prefix;//определяем префикс таблиц
$database = $wpdb->dbname; //определяем наименование базы данных 
	
	//создание колонки 	category_1c_id в таблице wp_terms
	$column_name_query = $wpdb->get_results ("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_name =  '".$prefixtable."terms' AND table_schema =  '".$database."' AND COLUMN_NAME = 'category_1c_id'"); 
	if (count($column_name_query)>0) {
		//echo "Column category_1c_id already exists. Not create.<br />";	
	}else{ 
		//создаем новую колонку
		$wpdb->query("ALTER TABLE ".$prefixtable."terms ADD category_1c_id VARCHAR(100) NULL");
		echo "Create column category_1c_id... Success. <br />";
	}

	//создание колонки 	product_1c_id в таблице wp_posts
	$column_name_query = $wpdb->get_results ("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_name =  '".$prefixtable."posts' AND table_schema =  '".$database."' AND COLUMN_NAME = 'product_1c_id' ");
	if (count($column_name_query)>0) {
		//echo "Column product_1c_id already exists. Not create.<br />";
	}else{ 
		//создаем новую колонку
		$wpdb->query("ALTER TABLE ".$prefixtable."posts ADD product_1c_id VARCHAR(100) NULL");
		echo "Create column product_1c_id... Success. <br />";
	}
	
	//создание колонки 	attribute_1c_id в таблице woocommerce_attribute_taxonomies
	$column_name_query = $wpdb->get_results ("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_name =  '".$prefixtable."woocommerce_attribute_taxonomies' AND table_schema =  '".$database."' AND COLUMN_NAME = 'attribute_1c_id' ");
	if (count($column_name_query)>0) {
		//echo "Column product_1c_id already exists. Not create.<br />";
	}else{ 
		//создаем новую колонку
		$wpdb->query("ALTER TABLE ".$prefixtable."woocommerce_attribute_taxonomies ADD attribute_1c_id VARCHAR(100) NULL");
		echo "Create column attribute_1c_id... Success. <br />";
	}
	
	//Создание таблицы прогресса загрузки данных
	$sqlCreateTable = "CREATE TABLE IF NOT EXISTS ".$prefixtable."status_exchange_1c( 
		   id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
		   filename VARCHAR(100) NOT NULL, 
		   status VARCHAR(40) NOT NULL, 
		   error VARCHAR(40) NOT NULL, 
		   date_exchange DATETIME,
		   last_element_upload TEXT NULL,
		   posix VARCHAR(50) NULL) DEFAULT CHARACTER SET cp1251 COLLATE cp1251_bin";
	$sqlCreateTable_query = $wpdb->query($sqlCreateTable);
	
	//Создание таблицы настроек
	$sqlCreateTable = "CREATE TABLE IF NOT EXISTS ".$prefixtable."setting_exchange_1c( 
			id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
			name_setting VARCHAR(100) NULL, 
			value TEXT NULL, 
			name_rus VARCHAR(150) NULL,
			comment TEXT NULL,
			type VARCHAR (150),
			date_update DATETIME ) DEFAULT CHARACTER SET cp1251 COLLATE cp1251_bin";
	$sqlCreateTable_query = $wpdb->query($sqlCreateTable);
	
	//создание колонки 	last_element_upload в таблице status_exchange_1c
	$column_name_query = $wpdb->get_results ("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_name =  '".$prefixtable."status_exchange_1c' AND table_schema =  '".$database."' AND COLUMN_NAME = 'last_element_upload' ");
	if (count($column_name_query)>0) {
		//echo "Column last_element_upload already exists. Not create.<br />";				
	}else{ 
		//создаем новую колонку
		$wpdb->query("ALTER TABLE ".$prefixtable."status_exchange_1c ADD last_element_upload TEXT NULL");
		echo "Create column last_element_upload... Success. <br />";
	}
	
	//создание колонки 	posix в таблице status_exchange_1c
	$column_name_query = $wpdb->get_results ("SELECT COLUMN_NAME FROM information_schema.columns WHERE table_name =  '".$prefixtable."status_exchange_1c' AND table_schema =  '".$database."' AND COLUMN_NAME = 'posix' ");
	if (count($column_name_query)>0) {
		//echo "Column posix already exists. Not create.<br />";				
	}else{ 
		//создаем новую колонку
		$wpdb->query("ALTER TABLE ".$prefixtable."status_exchange_1c ADD posix VARCHAR(50) NULL");
		echo "Create column posix... Success. <br />";
	}
}

function CreateUpdateJSONStatus() {
global $wpdb;
	$prefixtable = DB_PREFIX;//определяем префикс таблиц
	
  $order_status_ozhidanie  = OrderStatusReturn ('wc-on-hold');
	$order_status_dostavleno = OrderStatusReturn ('wc-completed');
	$order_status_otmeneno   = OrderStatusReturn ('wc-cancelled');
	$order_status_oplacheno  = OrderStatusReturn ('wc-pending');
	$order_status_vobrabotke = OrderStatusReturn ('wc-processing');
	
	//$order_status = $db->query ("SELECT * FROM ".$prefixtable."order_status WHERE language_id = '".LANGUAGE_ID."' ORDER BY 'order_status_id'"); 
	$array_status_id_jshopping = array();
	$array_to_json = array();
	$status = 'wc-processing';
	require_once ( JPATH_BASE .'/wp-content/plugins/woocommerce/includes/wc-order-functions.php');
	$order_statuses = wc_get_order_statuses();
	if (isset($order_statuses)){
		foreach ($order_statuses as $statuskey=>$NameStatus) {
			$array_status_id_jshopping[] = $statuskey;
			if (($statuskey)== $order_status_dostavleno){
				$array_to_json[($statuskey)] = array(
				  "status_id" => ($statuskey),
				  "name" => ($NameStatus),
				  "enable_exchange" => "0"
				);
			}else{
				$array_to_json[($statuskey)] = array(
				  "status_id" => ($statuskey),
				  "name" => ($NameStatus),
				  "enable_exchange" => "1"
				);
			}
		}
	}
	
	$search_setting = $wpdb->get_results("SELECT value FROM `".$prefixtable."setting_exchange_1c` WHERE name_setting = 'VM_STATUS_EXCHANGE'"); 	
	if (count($search_setting)>0){
		foreach($search_setting as $setting){
			$value_setting = $setting->value;
		}
		
		$array_status_setting = json_decode($value_setting, true);
		$std_status_setting = json_decode($value_setting, false);
		$array_enable_exchange = array();
		$array_status_id_setting = array();		
		foreach($std_status_setting as $status_setting){
			$status_id = $status_setting->status_id;
			$array_status_id_setting[] = $status_id; 
			$enable_exchange = $status_setting->enable_exchange;
			$array_enable_exchange[$status_id] = $enable_exchange;
		}
		$result = array_replace($array_status_setting, $array_to_json);
		
		foreach ($result as $k => $v) {
			if (isset($array_enable_exchange[$k])){
				$result[$k]['enable_exchange']=$array_enable_exchange[$k];
			}	
		}
		
		$array_diff = array_diff($array_status_id_setting, $array_status_id_jshopping);
		foreach ($array_diff as $status_delete){
			if (isset($result[$status_delete])){
				unset($result[$status_delete]);
			}
		}		
		$encoded = json_encode($result);
		$json_statuses = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
			return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
		}, $encoded);
		$wpdb->query("UPDATE `".$prefixtable."setting_exchange_1c` SET value='".$json_statuses."' WHERE name_setting='VM_STATUS_EXCHANGE'"); 	
	}else{
		$encoded = json_encode($array_to_json);
		$json_statuses = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
			return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
		}, $encoded);
		
		$ins = new stdClass ();
		$ins->id = NULL;
		$ins->name_setting = "VM_STATUS_EXCHANGE";
		$ins->value = $json_statuses;
		$ins->name_rus = "Загружать заказы в статусах";
		$ins->comment = "Загружать заказы в 1С находящихся в  след. статусах (указать галками и сохранить настройки)";
		$ins->type = "Обмен заказами";
		$ins->date_update = date("Y-m-d G:i:s",time());
		insertObject("" .$prefixtable."setting_exchange_1c", $ins);
	}
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

function PrintOrderStatuses($status_selected_code, $select_id){//VM_ORDER_STATUS_PROCESSING
global $wpdb;	
	require_once ( JPATH_BASE .'/wp-content/plugins/woocommerce/includes/wc-order-functions.php');
	$order_statuses = wc_get_order_statuses();
	if (isset($order_statuses)){
		print '<select id="'.$select_id.'" name="'.$select_id.'">';	
		if (empty($status_selected_code)){
			print '<option value="" selected></option>';	
		}else{
			print '<option value=""></option>';
		}
		foreach ($order_statuses as $statuskey=>$NameStatus) {
			$status_name = $NameStatus; 
			$status_code = $statuskey;
			if ($status_selected_code == $status_code){
				print '<option selected value="'.$status_code.'">'.$status_name.'</option>';
			}else{
				print '<option value="'.$status_code.'">'.$status_name.'</option>';
			}
		}
		print '</select>';
	}
}

function ReadSetting($name_setting) {
global $wpdb;
	if (isset($name_setting)){
		$value_setting = $wpdb->get_results ("SELECT value FROM ".DB_PREFIX."setting_exchange_1c where name_setting = '".$name_setting."'"); 
		if (count($value_setting) > 0){
				foreach ($value_setting as $vs){
					$value = $vs->value;
				}	
			return $value;
		}else{
			return '';
		}		
	}else{
		return '';
	}
}

function getStockStatus() {
	
	$stock_status_array =  array(
		"outofstock" => "Не разрешать предзаказы",	
		"onbackorder_notify" => "Разрешить предзаказы, но уведомить клиента",
		"onbackorder_yes" => "Разрешить предзаказы"
	);

	return ($stock_status_array);	
}

function createPropertyList($name_setting) {
global $wpdb;
$prefixtable = DB_PREFIX;//определяем префикс таблиц

	$search_setting = $wpdb->get_results ("SELECT value FROM `".$prefixtable."setting_exchange_1c` WHERE name_setting = '".$name_setting."'"); 
	if (count($search_setting) > 0){
		foreach ($search_setting as $setting_result){
			$array_setting = json_decode($setting_result->value, true);
		}		
		$result = $array_setting;		
		$encoded = json_encode($array_setting);
		$json_statuses = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
			return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
		}, $encoded);
		$wpdb->query ("UPDATE `".$prefixtable."setting_exchange_1c` SET value='".$json_statuses."' WHERE name_setting='".$name_setting."'");
	}else{
		$array_to_json = array();
		if ($name_setting == 'VM_PROPERTY_STOP_LIST'){
			$array_to_json[1] = array(
						"input_id" => "1",	
						"input_name" => "property_stop_name_1",
						"input_value" => "Производитель"
						);
			$array_to_json[2] = array(
						"input_id" => "2",	
						"input_name" => "property_stop_name_2",
						"input_value" => "Выгружать на сайт"
						);
		}
		if ($name_setting == 'VM_PROPERTY_VARIATION_STOP_LIST'){
			$array_to_json[1] = array(
						"input_id" => "1",	
						"input_name" => "property_variation_stop_name_1",
						"input_value" => "Артикул"
						);
		}
		$result = $array_to_json;
		$encoded = json_encode($array_to_json);
		$json_statuses = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
			return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
		}, $encoded);
		
		$ins = new stdClass ();
		$ins->id = NULL;
		$ins->name_setting = $name_setting;
		$ins->value = $json_statuses;
		$ins->name_rus = "Запретить создание свойств";
		if ($name_setting == 'VM_PROPERTY_STOP_LIST'){
			$ins->comment = "Запретить создание свойств доп. реквизитов номенклатуры 1С из указанного списка. В списке указать ТОЧНОЕ наименование доп. реквизита или свойства созданного в 1С";
		}
		if ($name_setting == 'VM_PROPERTY_VARIATION_STOP_LIST'){
			$ins->comment = "Запретить создание свойств доп. реквизитов характеристик номенклатуры 1С из указанного списка. В списке указать ТОЧНОЕ наименование доп. реквизита или свойства созданного в характеристике номенклатуры 1С";
		}
		$ins->type = "Настройки загрузки реквизитов";
		$ins->date_update = date("Y-m-d G:i:s",time());
		insertObject("" .$prefixtable."setting_exchange_1c", $ins);
	}
	if ($name_setting == 'VM_PROPERTY_STOP_LIST'){
		print ('<div id="parentId">');
		foreach ($result as $input){
			print ('<div class="elementproperty">');
			print ('<input class="property_stop_name" id="'.$input['input_id'].'" name="'.$input['input_name'].'" type="text" value="'.$input['input_value'].'"/> ');
			print ('<a onclick="return deleteField(this)" href="#">[X]</a>');	
			print ('</div>');
		}
		print ('</div>');
		print ('<a onclick="return addField()" href="#">+ добавить свойство</a>');
	}
	if ($name_setting == 'VM_PROPERTY_VARIATION_STOP_LIST'){
		print ('<div id="parentIdVariation">');
		foreach ($result as $input){
			print ('<div class="elementproperty">');
			print ('<input class="property_variation_stop_name" id="'.$input['input_id'].'" name="'.$input['input_name'].'" type="text" value="'.$input['input_value'].'"/> ');
			print ('<a onclick="return deleteField(this)" href="#">[X]</a>');	
			print ('</div>');
		}
		print ('</div>');
		print ('<a onclick="return addFieldVariation()" href="#">+ добавить свойство</a>');
	}
 
?>
	<script>
	var curFieldNameId = getLastIdElement('property_stop_name');
	var curFieldNameIdVariation = getLastIdElement('property_variation_stop_name'); 	
	function deleteField(a) {
		 var contDiv = a.parentNode;
		 contDiv.parentNode.removeChild(contDiv);
		 return false;
	}
	function addField() {
		 getLastIdElement('property_stop_name');
		 curFieldNameId++;
		 var div = document.createElement("div");
		 div.classList.add("elementproperty");
		 div.innerHTML = "<input class=\"property_stop_name\" id=\""+ curFieldNameId +"\" name=\"property_stop_name_" + curFieldNameId + "\" type=\"text\" /> <a onclick=\"return deleteField(this)\" href=\"#\">[X]</a>";
		 document.getElementById("parentId").appendChild(div);
		 return false;
	}
	function addFieldVariation() {
		 getLastIdElement('property_variation_stop_name');
		 curFieldNameIdVariation++;
		 var div = document.createElement("div");
		 div.classList.add("elementproperty");
		 div.innerHTML = "<input class=\"property_variation_stop_name\" id=\""+ curFieldNameIdVariation +"\" name=\"property_variation_stop_name_" + curFieldNameIdVariation + "\" type=\"text\" value=\"\" /> <a onclick=\"return deleteField(this)\" href=\"#\">[X]</a>";
		 document.getElementById("parentIdVariation").appendChild(div);
		 return false;
	}
	function getLastIdElement(name){
		  var lastId = 0;
		  let inputs = document.getElementsByClassName(name);
		  for (let input of inputs) {
			lastId = input.id;
		  }
		  return lastId;
	}
	</script>
<?php
}

function validatePhpInfo(){
	$STATUS_INSTALL = 'success';
	$warning_str = "";
	$extensions_array = array();
	
	ob_start();
	phpinfo();
	$p=ob_get_contents();
	ob_end_clean();
	preg_match_all('|<tr>(.+)</tr>|',$p,$m1);
	$phpinfo_array=array();
	foreach ($m1[0] as $m2){
		preg_match_all('|<td(.+?)>(.+?)</td>|',$m2,$m3);
		$phpinfo_array[]=$m3[2];
	}
	foreach ($phpinfo_array as $phpinfo_result){
		if (isset($phpinfo_result[0]) and isset($phpinfo_result[1])){
			$name_value = trim(strtolower($phpinfo_result[0]));
			$phpinfo_value = $name_value.'='.$phpinfo_result[1];
			if ($name_value == 'server api'){
				print('<span style="float:left; margin-left:20px;">');
				print($phpinfo_value);
				print('</span>');
				print('<br>');
				$extensions_array[$name_value] = trim(strtolower($phpinfo_result[1]));	
			}
			if ($name_value == 'curl support'){
				print('<span style="float:left; margin-left:20px;">');
				print($phpinfo_value);
				print('</span>');
				print('<br>');
				$extensions_array[$name_value] = trim(strtolower($phpinfo_result[1]));
			}
			if ($name_value == 'curl'){
				print('<span style="float:left; margin-left:20px;">');
				print($phpinfo_value);
				print('</span>');
				print('<br>');
				$extensions_array[$name_value] = trim(strtolower($phpinfo_result[1]));
			}
		}			
	}
	$loaded_extensions_array = get_loaded_extensions();

	foreach ($loaded_extensions_array as $loaded_extension){
		$loaded_extension = trim(strtolower($loaded_extension));
		$extensions_array[$loaded_extension] = $loaded_extension;
	}
	if (!isset($extensions_array['xmlreader'])){
		$STATUS_INSTALL = 'fail';
		$warning_str .= "no install module xmlreader;\n";
	}
	if (!isset($extensions_array['simplexml'])) {
		$STATUS_INSTALL = 'fail';
		$warning_str .= "no install module simplexml;\n";
	}
	if (!isset($extensions_array['posix'])) {
		$STATUS_INSTALL = 'fail';
		$warning_str .= "no install module posix;\n";
	}
	if (isset($extensions_array['curl support'])) {
		if ($extensions_array['curl support'] <> 'enabled'){
			$STATUS_INSTALL = 'fail';
			$warning_str .= "no curl support;\n";
		}	
	}
	if (isset($extensions_array['litespeed'])) {
		$use_noabort = false;
		$htaccess = realpath(dirname(__FILE__)) . '/.htaccess';
		if (file_exists($htaccess)){
			$htaccess = file($htaccess);
			foreach ($htaccess as $line) {
				$pattern = "@^RewriteRule (.*)$@";
				$line_update = trim($line); 
				if(preg_match($pattern,$line_update,$matches)){
					if (isset($matches[1])){
						$htaccess_value = strtolower($matches[1]);
						$findnoabort = strpos($htaccess_value, 'noabort:1' );
						if ($findnoabort !== false) {
							$use_noabort = true;
						} 
						$findnoabort = strpos($htaccess_value, 'noabort: 1' );
						if ($findnoabort !== false) {
							$use_noabort = true;
						} 
						$findnoabort = strpos($htaccess_value, 'noabort : 1' );
						if ($findnoabort !== false) {
							$use_noabort = true;
						} 
					}
				}		
			}
			if($use_noabort == false){
				$STATUS_INSTALL = 'fail';
				$warning_str .= "set in .htaccess: RewriteRule .* - [E=noabort:1] \n";
			}
		}else{
			$STATUS_INSTALL = 'fail';
			$warning_str .= "not was find .htaccess;\n";
		}
	}
	if (isset($extensions_array['server api'])) {
		$findcgi = strpos($extensions_array['server api'], 'cgi' );
		if ($findcgi !== false) {
			$use_HTTP_Auth = false;
			$htaccess = realpath(dirname(__FILE__)) . '/.htaccess';
			if (file_exists($htaccess)){
				$htaccess = file($htaccess);
				foreach ($htaccess as $line) {
					$pattern = "@^RewriteRule (.*)$@";
					$line_update = trim($line); 
					if(preg_match($pattern,$line_update,$matches)){
						if (isset($matches[1])){
							$htaccess_value = strtolower($matches[1]);
							$findHTTP_Auth = strpos($htaccess_value, 'http:authorization' );
							if ($findHTTP_Auth !== false) {
								$use_HTTP_Auth = true;
							} 
						}
					}		
				}
				if($use_HTTP_Auth == false){
					$STATUS_INSTALL = 'fail';
					$warning_str .= "set in .htaccess: RewriteRule .* - [E=REMOTE_USER:%{HTTP:Authorization},L] \n";
				}
			}else{
				$STATUS_INSTALL = 'fail';
				$warning_str .= "not was find .htaccess;\n";
			}
		}
	}
	echo ('<span style="float:left; margin-left:20px;">STATUS INSTALL: '.$STATUS_INSTALL.'</span><br>');
	if (!empty($warning_str)){
		echo ('<span style="float:left; margin-left:20px;">Warning: '.$warning_str.'</span><br>');
	}
}

function printAddInfo(){	
	$full_url_site = getFullUrlSite();
	$url_module = $full_url_site.'/exchange_1C_Woocommerce.php';
	echo ('</br>'); 
	echo ('<span style="float:left; margin-left:20px;">Logs path: '.DIR_LOGS.'</span><br>');
	echo ('<span style="float:left; margin-left:20px;">Строка подлючения в 1С: '.$url_module.'</span><br>');	
}


function CreateNewSetting() {
global $wpdb;

	$VM_STOCK_STATUS = 'outofstock';

	$array_to_json = array();	
	$array_to_json[1] = array(
		"input_id" => "1",	
		"input_name" => "property_stop_name_1",
		"input_value" => "Производитель"
	);
	$array_to_json[2] = array(
		"input_id" => "2",	
		"input_name" => "property_stop_name_2",
		"input_value" => "Выгружать на сайт"
	);
	$encoded = json_encode($array_to_json);
	$json_property_stop_default = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
		return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
	}, $encoded);
	
	$array_to_json = array();	
	$array_to_json[1] = array(
		"input_id" => "1",	
		"input_name" => "property_variation_stop_name_1",
		"input_value" => "Артикул"
	);
	$encoded = json_encode($array_to_json);
	$json_property_variation_stop_default = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
		return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
	}, $encoded);
	
	$date_now = date('Y-m-d');
	$max_execution_time = ini_get('max_execution_time');
	$timeLimit = ((int)$max_execution_time <= 1) ? 30 : (int)$max_execution_time;

	$order_status_dostavleno = OrderStatusReturn ('wc-completed');
	$order_status_oplacheno  = OrderStatusReturn ('wc-pending');

	$setting_array = array(
	array("UT_10_3", 1 , "Используется УТ 10.3", "Модуль интеграции используется для 1С Управление торговлей ред. 10.3 (отличная обработка свойств номенклатуры, отключение удаления картинок при выгрузке только изменений)", "Системные настройки"),
	array("BUH_3", 0 , "Используется Бухгалтерия 3.0", "Модуль интеграции используется для 1С Бухгалтерия предприятия 3.х (дополнительные параметры выгрузки НДС по товару)", "Системные настройки"),	
	array("UNF_1_6_15", 0 , "Используется УНФ 1.6.15", "Модуль интеграции используется для 1С УНФ 1.6.15.х Решение проблемы с загрузкой онлайн оплат", "Системные настройки"),
	array("VM_USE_ASYNCH", 1 , "Использовать асинхронный режим", "Вкл или Выкл использование асинхронной загрузки данных из 1С", "Системные настройки"),
	array("STOP_PROGRESS", 0 , "Отключить статус ожидания загрузки progress", "Отключение передачи статуса ожидания загрузки progress в 1С. Если значение 1, то модуль будет всегда передавать в 1С статус загрузки success. Используется в 1С Розница 2.3.х.", "Системные настройки"),
	array("VM_PASSWORD", 1 , "Включить проверку пароля", "Вкл или Выкл проверку пароля при авторизации 1С", "Системные настройки"),	
	array("USE_HEARBEAT", 1 , "Включить контроль времени исполнения", "Используется пошаговая обработки выгрузки: полученные из 1С файлы обрабатываются с контролем времени исполнения", "Системные настройки"),
	array("VM_TIME_LIMIT", $timeLimit , "Время выполнения скрипта", "Ограничение времени исполнения скрипта в секундах. В случае превышения данного времени, текущий процесс чтения завершается и запускается новый процесс. (Значение max_execution_time на хостинге: ".$max_execution_time.")", "Системные настройки"),		
	array("QUANTITY_DOSE", 1000 , "Количество в порции", "Количество объектов к обработке в порции, по умолчанию: 1000", "Системные настройки"),	
	array("USE_PROTECTION", 0 , "Использовать усиленную защиту", "Использовать усиленную защиту соединения между сайтом и 1С. При установленной галке проверяется sessid при каждом подключении 1С к сайту", "Системные настройки"),		
	array("USE_FUNCTION_WOOCOMMERCE", 0 , "Частичное исп. функций WooCommerce", "Включение или отключение использования частичных функций WooCommerce. В случае возникновения ошибок сторонних плагинов, данную настройку отключить", "Системные настройки"),
	array("VM_DELETE_TEMP", 0 , "Очищать папку TEMP после обмена", "Удалять все файлы обмена в папке TEMP после завершения обмена", "Системные настройки"),	
	//********
	array("VM_SALE_PRICE_1C", "Цена распродажи" , "Наименование цены распродажи", "Тип цен номенклатуры 1С для отображение цены в поле (Цена распродажи) у товара на сайте", "Настройки цен"),	
	//***********
	array("VM_CREATE_PRODUCT", 1 , "Загружать новые товары", "Создавать новые товары по данным 1С. Эта настройка позволяет запретить создание новых (не сопоставленных товаров) на сайте по данным выгрузки 1С", "Настройки загрузки реквизитов"),
	array("IMAGE_LOAD", 1 , "Загружать изображения товаров", "Режим загрузки изображений товаров на сайт из 1С: 1 -загружать из 1С для товаров из 1С, 0 - изображения не загружаются из 1С", "Настройки загрузки реквизитов"),	
	array("VM_SVOISTVA_1C", 1 , "Загружать свойства номенклатуры", "Использовать и отображать доп. реквизиты номенклатуры из 1С", "Настройки загрузки реквизитов"),	
	array("VM_GROUP_ATTRIBUTE", 1 , "Использовать групповые атрибуты", "Использовать и отображать атрибуты как групповые, если значение 0 - при обмене будут создаваться только индивидуальные атрибуты. Для WooCommerce старше 3.2.0", "Настройки загрузки реквизитов"),	
	array("VM_MANUFACTURER_1C", 1 , "Загружать производителей", "Режим загрузки изображений в 1С: вкл-выкл", "Настройки загрузки реквизитов"),
	array("VM_KRATKOE_OPISANIE", 1 , "Загрузка краткого описания", "Вкл или выкл загрузка краткого описания для товара. Для выгрузки краткого описания из 1С необходимо создать в 1С доп. реквизит или свойство для номенклатуры с именем <b>Краткое описание<\b> ", "Настройки загрузки реквизитов"),
	array("VM_PRODUCT_VIEW_PRICE0", 1 , "Отображать товары c нулевой ценой", "Отображать товары на сайте у которых цена = 0 либо еще не загружена. Если настройка отключена, то товары без цены будут иметь статус на сайте Отключено", "Настройки загрузки реквизитов"),
	array("VM_PRODUCT_VIEW_COUNT0", 1 , "Отображать товары c нулевым остатком", "Отображать товары на сайте у которых остаток = 0 либо еще остаток не загружен. Если настройка отключена, то товары с нулевым остатком будут иметь статус на сайте Отключено", "Настройки загрузки реквизитов"),
	array("VM_DELETE_MARK_PRODUCT", "HIDE" , "Скрывать(удалять) товары удаленные в 1С", "Товары которые помечены на удаление в 1С:HIDE - скрыть видимость, DELETE - удалить на сайте", "Настройки загрузки реквизитов"),
	array("VM_ALLNAMEUSE", 0 , "Использовать полное наименование", "Использовать полное наименование номенклатуры (наименование для печати) при установке наименования товара по данным 1С", "Настройки загрузки реквизитов"),
	array("VM_STOCK_STATUS", $VM_STOCK_STATUS , "Статус отсутствия на складе", "Статус, показываемый, когда у товара остаток равен 0", "Настройки загрузки реквизитов"),
	array("VM_PROPERTY_STOP_LIST", $json_property_stop_default , "Запретить создание свойств", "Запретить создание свойств доп. реквизитов номенклатуры 1С из указанного списка. В списке указать ТОЧНОЕ наименование доп. реквизита или свойства созданного в 1С", "Настройки загрузки реквизитов"),
	//********
	array("VM_FOLDER", 1 , "Загружать группы номенклатуры", "Загружать группы номенклатуры-категории на сайт", "Настройки загрузки категорий"),
	array("VM_HIERARCHY_FOLDER", 1 , "Загружать иерархию групп номенклатуры", "Загружать-изменять иерархию групп номенклатуры-категорий на сайт из 1С", "Настройки загрузки категорий"),
	array("VM_PRODUCT_LOAD_IN_PARENTCATEGORY", 1 , "Отображать товары в родительских категориях", "Отображать товары в каждой родительской категории товара", "Настройки загрузки категорий"),
	//********
	array("VM_UPDATE_CATEGORY", 1 , "Обновлять категории у товаров", "Обновлять категории у существующих товаров на сайте", "Обновление данных у сущ.товаров на сайте"),
	array("VM_UPDATE_NAME", 1 , "Обновлять наименование у товаров", "Обновлять наименование у существующих товаров на сайте", "Обновление данных у сущ.товаров на сайте"),
	array("VM_UPDATE_ARTIKUL", 1 , "Обновлять артикул у товаров", "Обновлять артикул у существующих товаров на сайте", "Обновление данных у сущ.товаров на сайте"),
	array("VM_UPDATE_DESC", 1 , "Обновлять описание у товаров", "Обновлять описание у существующих товаров на сайте", "Обновление данных у сущ.товаров на сайте"),
	array("VM_UPDATE_META", 1 , "Обновлять мета-инф. у товаров", "Обновлять мета-информацию у существующих товаров на сайте", "Обновление данных у сущ.товаров на сайте"),			
	array("VM_UPDATE_MANUFACTURE", 1 , "Обновлять производителя у товаров", "Обновлять производителя у существующих товаров на сайте", "Обновление данных у сущ.товаров на сайте"),	
	array("VM_UPDATE_IMAGE", 1 , "Обновлять картинки у товаров", "Обновлять картинки у существующих товаров на сайте", "Обновление данных у сущ.товаров на сайте"),		
	array("VM_UPDATE_SVOISTVA", 1 , "Обновлять свойства у товаров", "Обновлять свойства-атрибуты у существующих товаров на сайте", "Обновление данных у сущ.товаров на сайте"),
	array("VM_UPDATE_PRICE", 1 , "Обновлять цены у товаров", "Обновлять цены у существующих товаров на сайте", "Обновление данных у сущ.товаров на сайте"),
	array("VM_UPDATE_COUNT", 1 , "Обновлять количество у товаров", "Обновлять количество у существующих товаров на сайте", "Обновление данных у сущ.товаров на сайте"),		
	//********
	array("VM_FEATURES_1C", 1 , "Использовать характеристики номенклатуры", "Использовать загрузку данных из справочника характерстики номенклатуры 1С", "Настройки обмена характеристик номенклатуры"),	
	array("VM_NAME_OPTION", "" , "Наименование поля опции товара", "Наименование поля опции товара. Если значение пустое, то модуль будет искать и подставлять названия свойств справочника Характеристики номенклатура", "Настройки обмена характеристик номенклатуры"),	
	array("VM_PRICE_PARENT_FEATURES", 1 , "Обновлять цену родителя товара", "Подставлять минимальную цену характерстики номенклатуры для родителя (основного) товара  1С: вкл - выкл. Если выкл - цена будет подставляться та, которая установлена в 1С", "Настройки обмена характеристик номенклатуры"),	
	array("VM_COUNT_PARENT_FEATURES", 1 , "Вычислять количество товара по опциям", "Вычислять количество товара суммируя количество в опциях товара: 1 - вкл, 0 - выкл", "Настройки обмена характеристик номенклатуры"),
	array("VM_DELETE_FEATURES", 1 , "Удалять опции на сайте по данным 1С", "Удалять опции на сайте которых нет в 1С: 1 - вкл, 0 - выкл", "Настройки обмена характеристик номенклатуры"),
	array("VM_PROPERTY_VARIATION_STOP_LIST", $json_property_variation_stop_default , "Запретить создание свойств", "Запретить создание свойств доп. реквизитов характеристик номенклатуры 1С из указанного списка. В списке указать ТОЧНОЕ наименование доп. реквизита или свойства созданного для характеристики номенклатуры 1С", "Настройки обмена характеристик номенклатуры"),
	//********
	array("VM_CODING", "Auto" , "Кодировка заказа", "Для использования другой кодировки в XML заказах с сайта используйте значение UTF-8 или значение Default(для УНФ 1.6). При значении Auto кодировка подбирается автоматически", "Обмен заказами"),
	array("VM_CURRENCY", 1 , "Использовать валюты в заказе", "Выгружать валюту для заказа с сайта", "Обмен заказами"),
	array("VM_NAME_CURRENCY_DEFAULT", "RUB" , "Наименование валюты по умол.", "Наименование валюты в заказе с сайта по умолчанию", "Обмен заказами"),
	array("VM_DEFAULT_COUNTRY", "" , "Наименование страны по умол.", "Наименование страны в заказе с сайта по умолчанию. Если не заполнено, то страна подставляется по данным адреса заказа", "Обмен заказами"),
	array("VM_USE_SERVICE_IN_ORDER", 1 , "Выгружать доставку как услугу в заказе", "Выгружать наименование и стоимость доставки отдельной услугой в заказе с сайта", "Обмен заказами"),
	array("VM_CALC_SALE", 1 , "Рассчитывать скидки в заказе", "Рассчитывать скидки в заказе с сайта для установки скидок в заказе 1С. Скидки рассчитываются пропорционально по каждому товару и услуге доставки", "Обмен заказами"),
	array("VM_NDS", 0 , "Добавлять НДС к цене товара", "Учитывать НДС в цене товара в заказе с сайта", "Обмен заказами"),	
	array("VM_UPDATE_STATUS_ORDER", 1 , "Обновлять статус заказа по данным 1С", "Обновлять статус заказа на сайте по данным из 1С: статус заказа на сайте будет обновлен в случае ввода на основании заказа документов отгрузки или оплаты", "Обмен заказами"),	
	array("VM_UPDATE_ORDERDATA1C", 0 , "Обновлять данные о товарах по данным 1С", "Обновлять информацию в заказе на сайте об изменениях товара по данным 1С", "Обмен заказами"),
	array("VM_ORDER_DATE_LOAD", $date_now , "Дата начала загрузки заказов", "Заказы созданные начиная с указанной даты, будут выгружаться в 1С", "Обмен заказами"),
	array("VM_USE_BITRIX", 0 , "Используется расширение обмена 1С-Битрикс", "Используется расширение обмена 1С-Битрикс установленное в 1С", "Обмен заказами"),
	array("VM_ORDER_STATUS_PROCESSING", "" , "Установить статус заказа после выгрузки в 1С", "Указать статус заказа, который будет установен на сайте сразу после выгрузки его в 1С. Если статус не указан(пустой), то изменение статуса после выгрузки в 1С не произойдет", "Обмен заказами"),
	array("VM_ORDER_STATUS_PAID", $order_status_oplacheno , "Значение статуса Оплачено", "Указать статус заказа, который будет установен на сайте при получении оплаты из 1С", "Обмен заказами"),		
	array("VM_ORDER_STATUS_COMPLETE", $order_status_dostavleno , "Значение статуса Завершено", "Указать статус заказа, который будет установен на сайте при получении документа отгрузки из 1С", "Обмен заказами")
	);
	

	$db_setting = array();
	$db_query = $wpdb->get_results( "SELECT name_setting FROM " . DB_PREFIX . "setting_exchange_1c " );
	foreach ( $db_query as $name ) {
		$db_setting[] = $name->name_setting;
	}
	
	for ($row = 0; $row < count($setting_array); $row++) {
		
		$name_setting = $setting_array[$row][0];
		if (!in_array($name_setting, $db_setting)){
			$ins = new stdClass ();
			$ins->id = NULL;
			$ins->name_setting = $name_setting;
			$ins->value = $setting_array[$row][1];
			$ins->name_rus = $setting_array[$row][2];
			$ins->comment = $setting_array[$row][3];
			$ins->type = $setting_array[$row][4];
			$ins->date_update = date("Y-m-d G:i:s",time());
			insertObject ( "" . DB_PREFIX ."setting_exchange_1c", $ins );
		}
	}
}
?>