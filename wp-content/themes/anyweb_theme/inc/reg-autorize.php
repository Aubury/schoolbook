<?php
if (!defined("ABSPATH")) {
  exit;
}


add_action( 'login_head', 'my_custom_login_logo' );
function my_custom_login_logo(){

	echo '
	<style type="text/css">
	h1 a {  background-image:url('.get_bloginfo('template_directory').'/assets/image/logo_mobile.png) !important; background-size:contain !important;  }
	</style>
	';
}


// add_filter('site_url', 'wplogin_filter', 10, 3);
// function wplogin_filter( $url, $path, $orig_scheme )
// {
// $old = array( "/(wp-login\.php)/");
// $new = array( "book-admin.php");
// return preg_replace( $old, $new, $url, 1);
// }


add_shortcode( 'wc_login_form_bbloomer', 'bbloomer_separate_login_form' );
  
function bbloomer_separate_login_form() {
   if ( is_user_logged_in() ) return '<p>You are already logged in</p>'; 
   ob_start();
   do_action( 'woocommerce_before_customer_login_form' );
   woocommerce_login_form( array( 'redirect' => wc_get_page_permalink( 'myaccount' ) ) );
   return ob_get_clean();
}


add_action( 'woocommerce_register_form_start', 'truemisha_form_registration_fields', 25 );
 
function truemisha_form_registration_fields() {
 
	// поле "Имя"
	$billing_first_name = ! empty( $_POST[ 'billing_first_name' ] ) ? $_POST[ 'billing_first_name' ] : '';
	echo '<p class="form-row form-row-first">
		<label for="kind_of_name">Имя <span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_first_name" id="kind_of_name" value="' . esc_attr( $billing_first_name ) . '" />
	</p>';
 
	// поле "Фамилия"
	$billing_last_name = ! empty( $_POST[ 'billing_last_name' ] ) ? $_POST[ 'billing_last_name' ] : '';
	echo '<p class="form-row form-row-last">
		<label for="kind_of_l_name">Фамилия <span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_last_name" id="kind_of_l_name" value="' . esc_attr( $billing_last_name ) . '" />
	</p>';
 
	// чтобы всё не съехало, ведь у нас "на флоатах"
	echo '<div class="clear"></div>';
 
}

add_filter( 'woocommerce_registration_errors', 'truemisha_validate_registration', 25 );
 
function truemisha_validate_registration( $errors ) {
 
	// если хотя бы одно из полей не заполнено
	if ( empty( $_POST[ 'billing_first_name' ] ) || empty( $_POST[ 'billing_last_name' ] ) ) {
		$errors->add( 'name_err', '<strong>Ошибка</strong>: Заполните Имя и Фамилию плз.' );
	}
 
	return $errors;
 
}

add_action( 'woocommerce_created_customer', 'truemisha_save_fields', 25 );
 
function truemisha_save_fields( $user_id ) {
 
	// сохраняем Имя
	if ( isset( $_POST[ 'billing_first_name' ] ) ) {
		update_user_meta( $user_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
		update_user_meta( $user_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
	}
	// сохраняем Фамилию
	if ( isset( $_POST[ 'billing_last_name' ] ) ) {
		update_user_meta( $user_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
		update_user_meta( $user_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
	}
 
}

/*
 * Добавляем шорткод, его можно использовать в содержимом любой статьи или страницы, вставив [misha_custom_login]
 */
add_shortcode( 'misha_custom_login', 'misha_render_login' );
 
function misha_render_login() {
 
	// проверяем, если пользователь уже авторизован, то выводим соответствующее сообщение и ссылку "Выйти"
	if ( is_user_logged_in() ) {
		return sprintf( "Ви вже авторизовані на сайті. <a href='%s'>Выйти</a>.", wp_logout_url() );
	}
 
	// присваиваем содержимое формы переменной и затем возвращаем её, выводить через echo() мы не можем, так как это шорткод
	// $return = '<div class="login-form-container"><h2>Увійти до сайту</h2>';
	$return = '<div class="login-form-container">';
 
	// если возникли какие-либо ошибки, отображаем их
	if ( isset( $_REQUEST['errno'] ) ) {
		$error_codes = explode( ',', $_REQUEST['errno'] );
 
		foreach ( $error_codes as $error_code ) {
			switch ( $error_code ) {
				case 'empty_username':
					$return .= '<p class="errno">Будь-ласка вкажіть свій email або ім`я користувача?</p>';
					break;
				case 'empty_password':
					$return .= '<p class="errno">Будь-ласка, введіть пароль.</p>';
					break;
				case 'invalid_username':
					$return .= '<p class="errno">Користувача з таким ім`ям не існує</p>';
					break;
				case 'incorrect_password':
					$return .= sprintf( "<p class='errno'>Невірний пароль. <a href='%s'>Відновити</a>?</p>", wp_lostpassword_url() );
					break;
				case 'confirm':
					$return .= '<p class="errno success">Инструкція по відновленню пароля відправлена на ваш email.</p>';
					break;
				case 'changed':
					$return .= '<p class="errno success">Пароль успішно змінено.</p>';
					break;
				case 'expiredkey':
				case 'invalidkey':
					$retun .= '<p class="errno">Недійсний ключ.</p>';
					break;
			}
		}
	}
 
	// используем wp_login_form() для вывода формы (но можете сделать это и на чистом HTML)
	$return .= wp_login_form(
		array(
			'echo' => false, // не выводим, а возвращаем
			'redirect' => site_url('/account/'), // куда редиректить пользователя после входа
		)
	);
 
	$return .= '<a class="forgot-password" href="' . wp_lostpassword_url() . '">Не пам`ятаю пароль</a></div>';
 
	// и наконец возвращаем всё, что получилось
	return $return;
 
}


/*
 * Редиректы обратно на кастомную форму входа в случае ошибки
 */
add_filter( 'authenticate', 'misha_redirect_at_authenticate', 101, 3 );
 
function misha_redirect_at_authenticate( $user, $username, $password ) {
 
	if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
		if ( is_wp_error( $user ) ) {
			$error_codes = join( ',', $user->get_error_codes() );
 
			$login_url = home_url( '/autorize/' );
			$login_url = add_query_arg( 'errno', $error_codes, $login_url );
 
			wp_redirect( $login_url );
			exit;
		}
	}
 
	return $user;
}
 
/*
 * Редиректы после выхода с сайта
 */
add_action( 'wp_logout', 'misha_logout_redirect', 5 );
 
function misha_logout_redirect(){
	wp_safe_redirect( site_url( '/' ) );
	exit;
}