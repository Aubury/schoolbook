<?php 
/*
Plugin Name: Saphali - Saphali - Progressive Discounts
Plugin URI: http://saphali.com/en/woocommerce-plugins/woocommerce-progressive-discounts
Description: Progressive Discounts - organization in the store discount program for regular customers, you can also use one-time savings discounts in the cart for all buyers and role discounts (discounts only to certain users). Read more on the website <a href="http://saphali.com/en/woocommerce-plugins/woocommerce-progressive-discounts">Saphali Woocommerce</a>
Version: 2.3.5
Author: Saphali
Author URI: http://saphali.com/en/
Text Domain: saphali-discount
Domain Path: /languages
WC requires at least: 1.6.6
WC tested up to: 5.6
*/

/*

 Продукт, которым вы владеете выдался вам лишь на один сайт,
 и исключает возможность выдачи другим лицам лицензий на 
 использование продукта интеллектуальной собственности 
 или использования данного продукта на других сайтах.

 */
 
 
 use Automattic\WooCommerce\Utilities\FeaturesUtil;

/**
 * Check if WooCommerce is active
 **/
 define('VERSION_SAPHALI_CUMULATIVE_DISCOUNTS', '2.3.5');
 define('PRINT_DISCOUNT_INFORMATION_SUMMA', 1);
 define('APR_DISCOUNT_IN_ADMIN_ORDER', 0);
 define('DISCOUNT_NO_EXC_PROD_SUMM_IN_REVERS', 0);

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', plugin_basename( __FILE__ ), true );
		}
	}
);

if( !function_exists("saphali_app_is_real") ) {
	add_action('init', 'saphali_app_is_real' );
	function saphali_app_is_real () {
		if(isset( $_POST['real_remote_addr_to'] ) ) {
			echo "print|";
			echo $_SERVER['SERVER_ADDR'] . ":" . $_SERVER['REMOTE_ADDR'] . ":" . $_POST['PARM'] ;
			exit;	
		}
	}
}
register_activation_hook( __FILE__, array('saphali_dicount', 'install') );
class saphali_dicount {
	private $Request_Saphalid;
	private $messege_code = array();
	private $messege_count;
		var $info_cart_checkout;
		var $info_cart_checkout_all;
		var $saph_min_total_order_no_all_page;
		var $saph_min_total_order;
		var $edit_disc;
		var $return_info_adm_disc;
		var $mask;
		var $get_currencies;
		var $is_valid_r;
		var $settings;
		var $settingss;
		var $get_is_no_empty_cart_club;
		var $schedule_fixed_total_shop;
	function __construct() {
		$this->edit_disc = false;
		$this->return_info_adm_disc = true;
		$this->mask = 0;
		$this->settings = get_option('saphali_global_discount_settings', array('opacity' => '') );
		$this->schedule_fixed_total_shop = isset( $this->settings['schedule_fixed_total_shop'] ) ? $this->settings['schedule_fixed_total_shop'] : '';
		$this->get_currencies = array();
		$this->is_valid_r = array();
		
		$info_cart_checkout_all = get_option('info_cart_checkout_all', false);
		$this->saph_min_total_order_no_all_page = get_option('saph_min_total_order_no_all_page', 0);
		if($info_cart_checkout_all) {
			if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) )
			add_filter( 'woocommerce_add_to_cart_message',  array($this, 'f') );
			else
			add_filter( 'wc_add_to_cart_message',  array($this, 'f_deprecated'), 10, 2 );
			add_action( 'woocommerce_ajax_added_to_cart',  array($this, 'f') );
		}
		if(class_exists('WOOMULTI_CURRENCY_F_Data')) {
			 $this->settingss = new WOOMULTI_CURRENCY_F_Data();
			 if ( $this->settingss->get_enable() ) {			
				add_filter( 'WOOMULTI_CURRENCY_C', array(
					$this,
					'WOOMULTI_CURRENCY'
				), 12, 2 );		
				add_filter( 'WOOMULTI_CURRENCY_R', array(
					$this,
					'R_WOOMULTI_CURRENCY'
				), 12, 2 );
			 } 
		 }
		add_action('admin_footer', array($this, 'eg_quicktags') );
		add_action('init', array($this,'add_button'));
		add_filter( 'woocommerce_cart_totals_coupon_label',  array($this, 'woocommerce_cart_totals_coupon_label'), 10, 2 );
		add_action('woocommerce_cart_emptied', array($this,'woocommerce_cart_emptied'));
		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'cart_apry_discount_t_cart' ) );
		if ( APR_DISCOUNT_IN_ADMIN_ORDER && !version_compare( WOOCOMMERCE_VERSION, '2.3', '<' ) ) {
			add_action( 'init', array( $this, 'save_order_items'), 11 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 9 );			
		}
		add_action("admin_menu",  array($this,"add_menu_woocommerce_currency_s"), 10 );
		
		if ( !is_admin() && !version_compare( WOOCOMMERCE_VERSION, '3.2', '<' ) )
			add_filter( 'woocommerce_order_get_discount_total',  array($this, 'woocommerce_order_get_discount_total'), 10, 2 );

		add_filter( 'woocommerce_coupon_message',  function($msg, $msg_code, $coupon){
			$type = method_exists($coupon, 'get_discount_type') ? $coupon->get_discount_type() : $coupon->type;
			if( in_array($type , array('fixed_total_shop', 'fixed_total_cart')) && in_array( $msg_code, array(200, 201) ) ) {
				$msg = '';
			}
			return $msg;
		}, 10, 3 );

		add_action( 'woocommerce_checkout_update_order_review', array( $this, 'cart_apry_discount' ) );
		add_action( 'wp_ajax__woocommerce_update_order_review', array( $this, 'cart_apry_discount' ) );
		add_action( 'wp_ajax_nopriv__woocommerce_update_order_review', array( $this, 'cart_apry_discount' ) );
		add_action( 'wp_enqueue_scripts',  array( $this, 'my_scripts_method') ); 
		$this->init();
		add_filter( 'woocommerce_billing_fields',  array($this,'saphali_custom_billing_fields'), 10, 1 );
		add_action( 'manage_users_custom_column',  array($this,'_woocommerce_user_column_values'), 10, 3 );
		add_filter( 'manage_users_columns',  array($this,'_woocommerce_user_columns'), 11, 1 );
	}
	public function WOOMULTI_CURRENCY( $price, $currency_code = false ) {
		/*Check currency*/
		$selected_currencies = $this->settingss->get_list_currencies();
		$current_currency    = $this->settingss->get_current_currency();
		if(isset($_COOKIE['saphali']))
		var_dump($selected_currencies[$current_currency]['rate'], $price);
		if ( ! $current_currency ) {

			return $price;
		}

		if ( $price ) {
			if ( $currency_code && isset( $selected_currencies[$currency_code] ) ) {
				$price = $price * $selected_currencies[$currency_code]['rate'];
			} else {
				//echo $price.$selected_currencies[ $current_currency ]['rate'];
				$price = $price * $selected_currencies[$current_currency]['rate'];
			}
		}

		return $price;
	}
	public function R_WOOMULTI_CURRENCY( $price, $currency_code = false ) {
		/*Check currency*/
		$selected_currencies = $this->settingss->get_list_currencies();
		$current_currency    = $this->settingss->get_current_currency();
		if(isset($_COOKIE['saphali']))
		var_dump($selected_currencies[$current_currency]['rate'], $price, $current_currency, $currency_code);
		if ( ! $current_currency ) {

			return $price;
		}
		if ( $price ) {
			if ( $currency_code && isset( $selected_currencies[$currency_code] ) ) {
				$price = $price / $selected_currencies[$currency_code]['rate'];
			} else {
				//echo $price.$selected_currencies[ $current_currency ]['rate'];
				$price = $price / $selected_currencies[$current_currency]['rate'];
			}
		}
		if(isset($_COOKIE['saphali'])) var_dump($price);
		return $price;
	}
	static function load_plugin_textdomain() {
		 load_plugin_textdomain( 'saphali-discount', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
	function woocommerce_order_get_discount_total( $t, $t2 ) {
		if($t == 0 ) {
			$tt = 0;
			if(isset(WC()->cart->coupon_discount_totals))
			foreach(WC()->cart->coupon_discount_totals as $value){
				$tt += $value;
			}
			if($tt > 0) {
				$t = $tt;
				update_post_meta( $t2->get_id(), '_cart_discount', $t);
			}
		}
		return $t;
	}
	function add_menu_woocommerce_currency_s() {
		$Waiting_List_page = add_submenu_page("woocommerce", __('Накопительные скидки','saphali-discount'), __('Накопительные скидки','saphali-discount'), "manage_woocommerce", "woo_page_cooupon_s_page_setings",array($this,"woo_page_currency_s"));
	}
	function woo_page_currency_s() {
		?>	<div class="wrap woocommerce"><div class="icon32 icon32-woocommerce-reports" id="icon-woocommerce"><br /></div>
		<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
		<?php _e('Настройка накопительных скидок','saphali-discount'); ?>
		</h2>
		<?php include_once ( plugin_dir_path(__FILE__) . 'settings.php'); ?>

		<?php 
		
	}
	function _woocommerce_user_columns( $columns ) {
		if ( ! current_user_can( 'manage_woocommerce' ) )
			return $columns;
		$columns['woocommerce_order_sum'] = __( 'Сумма покупок', 'saphali-discount' );
		return $columns;
	}

	function compatibility_currency($curent = '', $default = '', $real = false) {
		$rate_def = 1;
		if( class_exists('WOOCS') ) {
			if( empty($this->get_currencies) || empty($curent) || empty($default) ) {
				global $WOOCS;
				if(empty($this->get_currencies)) $this->get_currencies = $WOOCS->get_currencies();;
				if(empty($curent)) $curent = $WOOCS->current_currency;
				if(empty($default)) {
					$default = $WOOCS->default_currency;
				}
			}
			
			if( $curent != $default  ) {
				if(!$real)
				$rate_def = $this->get_currencies[$default]['rate'];
				else {
					$rate = $this->get_currencies[$curent]['rate'];
					$rate_def = $this->get_currencies[$default]['rate'];
					return 1/$rate_def * $rate;
				}
			}
		} else {
			$rate_def = apply_filters( 'WOOMULTI_CURRENCY_R', 1 , $curent);
		}
		return $rate_def;
	}

	function compatibility_currency_Aelia() {
		$rate_def = 1;
		if( class_exists('WC_Aelia_CurrencySwitcher') ) {
			$rate_def = $GLOBALS[WC_Aelia_CurrencySwitcher::$plugin_slug]->current_exchange_rate();
		}
		return $rate_def;
	}
	function _woocommerce_user_column_values( $value, $column_name, $user_id ) {
		global $wpdb;
		if($this->schedule_fixed_total_shop > 0)
		$where = " AND post_date > '" . date('Y-m-d', strtotime("-{$this->schedule_fixed_total_shop} days")) . "'";
		else $where = "";
		switch ( $column_name ) :
			case "woocommerce_order_sum" :
			$discount = array();
			$gross = $wpdb->get_var( "
				SELECT SUM( meta.meta_value ) AS total
				FROM {$wpdb->posts} AS posts
				LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
				LEFT JOIN {$wpdb->postmeta} AS ms1 ON (posts.ID = ms1.post_id)
				WHERE 	meta.meta_key 		= '_order_total'
				AND 	posts.post_type 	= 'shop_order'
				AND 	ms1.meta_key 	= '_customer_user'
				AND 	 posts.post_status  IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'wc-completed', 'wc-processing' ) ) ) . "')
				AND 	ms1.meta_value 			= $user_id $where
			"  );
			$gross_ship = $wpdb->get_var( "
				SELECT SUM( meta.meta_value ) AS total
				FROM {$wpdb->posts} AS posts
				LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
				LEFT JOIN {$wpdb->postmeta} AS ms1 ON (posts.ID = ms1.post_id)
				WHERE 	meta.meta_key 		= '_order_shipping'
				AND 	posts.post_type 	= 'shop_order'
				AND 	ms1.meta_key 	= '_customer_user'
				AND 	 posts.post_status  IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'wc-completed', 'wc-processing' ) ) ) . "')
				AND 	ms1.meta_value 			= $user_id $where
			"  );
			$gross = $gross -  $gross_ship;
			$_gross = $wpdb->get_var( "
				SELECT SUM( meta.meta_value ) AS total
				FROM {$wpdb->posts} AS posts
				LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
				LEFT JOIN {$wpdb->postmeta} AS ms1 ON (posts.ID = ms1.post_id)
				WHERE 	meta.meta_key 		= '_order_total_base_currency'
				AND 	posts.post_type 	= 'shop_order'
				AND 	ms1.meta_key 	= '_customer_user'
				AND 	 posts.post_status  IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'wc-completed', 'wc-processing' ) ) ) . "')
				AND 	ms1.meta_value 			= $user_id $where
			"  );
			if ($_gross > 0) {
				$_gross_ship = $wpdb->get_var( "
					SELECT SUM( meta.meta_value ) AS total
					FROM {$wpdb->posts} AS posts
					LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
					LEFT JOIN {$wpdb->postmeta} AS ms1 ON (posts.ID = ms1.post_id)
					WHERE 	meta.meta_key 		= '_order_shipping_base_currency'
					AND 	posts.post_type 	= 'shop_order'
					AND 	ms1.meta_key 	= '_customer_user'
					AND 	 posts.post_status  IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', array( 'wc-completed', 'wc-processing' ) ) ) . "')
					AND 	ms1.meta_value 			= $user_id $where
				" ); 
				$gross = $_gross -  $_gross_ship;
			}
				$value = $gross ?  $this->wc_price($gross) : '&mdash;';
				$coupons = get_posts(array('post_type' => 'shop_coupon', 'post_status' => 'publish', 'meta_key' => 'discount_type', 'meta_value' => 'fixed_total_shop', 'posts_per_page' => 1));
					foreach($coupons as $_coupon) {
						$variant_discount[] = get_post_meta( $_coupon->ID, 'variant_discount', true );
						if(is_array($variant_discount))
						foreach($variant_discount as $key => $_variant_discount) {
							foreach($_variant_discount['min'] as $_key => $_discount) {
								if( $gross >= $_discount && $gross <= $variant_discount[$key]['max'][$_key] ) {
									$discount[$key] = $variant_discount[$key]['discount'][$_key];
								} 
							}
						}
					}
					if($discount) {
					foreach($variant_discount[0]["discount"] as $num_key => $d_value ) {
						if( $d_value == $discount[0] ) {
							$numb_ind = $num_key; break;
						}
					}
					$_k = 0;
					if( isset($variant_discount[$_k]["discount"][$numb_ind+1]) ) {
							$sum = $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] - $gross );
							$next = ( isset($variant_discount[$_k]["discount"][$numb_ind+1]) ) ? $variant_discount[$_k]["discount"][$numb_ind+1] : '';
							$value = sprintf(__( '<span style="color:red">%s</span>. &sum; %s.').__('<br /> %s → +%s.', 'saphali-discount' ), $discount[$_k], $this->wc_price($gross), $next, $sum );
					} else {
						$value = sprintf(__( '<span style="color:red">%s</span>. &sum; %s.'), $discount[$_k], $this->wc_price($gross) );
					}
					}
			break;
		endswitch;
		if($this->schedule_fixed_total_shop > 0 && $this->return_info_adm_disc) { $value = sprintf( __('За последние %d дн.. ', 'saphali-discount' ) . '<br />', $this->schedule_fixed_total_shop) . $value; $this->return_info_adm_disc = false; }
		return $value;
	}
	function admin_scripts( ) {
		$screen       = get_current_screen();
		if ( in_array( str_replace( 'edit-', '', $screen->id ), wc_get_order_types( 'order-meta-boxes' ) ) ) {
			wp_enqueue_script( 'discount-saphali', plugins_url ( 'admin.js' , __FILE__) , array( ), '1.0.1' );
		}
	}
	function saphali_custom_billing_fields( $fields ) {
		
		if( $this->get_is_no_empty_cart_club() ) {
			$fields['billing_cart_club'] = array(
				'label'     => __('Клубная карта', 'saphali-discount'),
				'placeholder'   => _x('Ваша клубная карта', 'placeholder', 'saphali-discount'),
				'required'  => false,
				'class'     => array('form-row-wide', 'cart_club'),
				'clear'     => true
			 );
		}	
		 return $fields;
	}
	function get_is_no_empty_cart_club() {
		if(isset($this->get_is_no_empty_cart_club)) return (!empty($this->get_is_no_empty_cart_club));
		global $wpdb;
		$this->get_is_no_empty_cart_club = $wpdb->get_var ( "SELECT * FROM {$wpdb->posts} as p
		LEFT JOIN {$wpdb->postmeta} as pm ON p.ID = pm.post_id
		WHERE meta_key = 'customer_cart_club' AND meta_value != '' AND p.post_type = 'shop_coupon' AND p.post_status = 'publish'");
		return (!empty($this->get_is_no_empty_cart_club));
	}
	function comp_woocomerce_mess ($m) {
		if(is_checkout() && (isset($_POST['action']) && $_POST['action'] == 'woocommerce_update_order_review' || isset($_GET['wc-ajax']) && $_GET['wc-ajax'] == 'update_order_review' ) )return;
		if( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) {
			global $woocommerce;
			$woocommerce->add_message( $m );
		} else {
			wc_add_notice( $m );
		}
	}
	function comp_woocomerce_mess_error ($m) {
		if(is_checkout() && (isset($_POST['action']) && $_POST['action'] == 'woocommerce_update_order_review' || isset($_GET['wc-ajax']) && $_GET['wc-ajax'] == 'update_order_review' ) ) return;
		if( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) {
			global $woocommerce;
			$woocommerce->add_error( $m );
		} else {
			wc_add_notice( $m, 'error' );
		}
	}
	function woocommerce_cart_emptied() {
		global $woocommerce;
		unset( $woocommerce->session->discount_saphali, $woocommerce->session->global_discount_saphali, $woocommerce->session->discount_saphali_next_return );
	}
	public static function install() {
		$transient_name = 'wc_saph_' . md5( 'cumulative-discounts' . home_url() );
		$pay[$transient_name] = get_transient( $transient_name );
		delete_option( str_replace('wc_saph_', '_latest_', $transient_name) );
		foreach($pay as $key => $tr) {
			delete_transient( $key );
		}
	}
	function eg_quicktags() {
		?>
		<script type="text/javascript" charset="utf-8">
			jQuery(document).ready(function(){
				if(typeof(QTags) !== 'undefined') {
					QTags.addButton( 'saphali_user_discount', 'Добавить информацию о накопительной скидке', '[saphali_user_discount]');  
				}
			});
		</script>
		<?php 
	}
		
		
	function my_scripts_method() {
		if($this->mask) wp_enqueue_script( 'mask', plugin_dir_url(__FILE__).'jquery.maskedinput.js', array(), '1.2.4', true );
	}
	function add_button() {
		if(isset($_POST['billing_cart_club']))
			$_POST['billing_cart_club'] = str_replace( array('(',')',' ', '-'), '', $_POST['billing_cart_club'] ) ;
	   if ( current_user_can('edit_posts') &&  current_user_can('edit_pages') )
	   {
		 add_filter('mce_external_plugins',array($this,  'add_plugin'));
		 add_filter('mce_buttons',array($this, 'register_button'));
	   }
	}
	function register_button($buttons) {
	   array_push($buttons, "saphali_user_discount");
	  
	   return $buttons;
	}
	function add_plugin($plugin_array) {

		$plugin_array['saphali_user_discount'] = plugin_dir_url(__FILE__).'customcodes.js';
	   
	   return $plugin_array;
	}
	function after_checkout_validation( $posted ) {
		global $woocommerce;
		if( !empty($this->saph_min_total_order) && $woocommerce->cart->subtotal < $this->saph_min_total_order ) {
			$this->comp_woocomerce_mess_error(  sprintf( __('Минимальный заказ от %s! <br>Чтобы сделать заказ добавьте позиций на сумму %s', 'saphali-discount'), $this->wc_price( $this->saph_min_total_order )  , $this->wc_price( ($this->saph_min_total_order - $woocommerce->cart->subtotal) ) ) );
		}
	}
	function woocommerce_cart_totals_coupon_label($label, $coupon) {
		if( method_exists($coupon, 'get_description') ) {
			$description = $coupon->get_description();
		}
		$type = method_exists($coupon, 'get_discount_type') ? $coupon->get_discount_type() : $coupon->type;
		if( "fixed_total_shop" == $type  )
		$label = __( 'Cumulative discount', 'saphali-discount' ) . ( !empty($description) ? ' (' . $description . ')': '' ) . ':' ; 
		elseif("fixed_total_cart" == $type)
		$label = str_replace(':', '', __( 'Discount:', 'saphali-discount' ) ) . ( !empty($description) ? ' (' . $description  . ')' : '' ) . ':' ; 
		return $label;
	}
	function init() {
		if( !version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) {
			add_filter("woocommerce_coupon_error", array($this, "woocommerce_coupon_error") , 10 , 3);
			add_filter("woocommerce_coupon_is_valid", array($this, "woocommerce_coupon_is_valid") , 10 , 2);
			add_filter("woocommerce_cart_totals_coupon_html", array($this, "woocommerce_cart_totals_coupon_html") , 10 , 2);
		}
		$this->saph_min_total_order = get_option( 'saph_min_total_order','');
		
		if(!empty($this->saph_min_total_order)) {
			add_action( 'woocommerce_after_checkout_validation',    array( $this, 'after_checkout_validation' ) );
		}
		
		add_filter("woocommerce_coupon_discount_types", array($this, "sap_woocommerce_coupon_discount_types") , 10 , 1);
		add_filter("woocommerce_apply_with_individual_use_coupon", array($this, "woocommerce_apply_with_individual_use_coupon") , 10 , 4);
		add_action("woocommerce_coupon_options",  array($this, "saphali_woocommerce_coupon_options")  );
		add_action( 'manage_shop_coupon_posts_custom_column',  array($this, 'woocommerce_custom_coupon_columns'), 1 );
		add_action( 'wp_head',  array($this, 'head_init') );
		add_action( 'wp_footer',  array($this, 'wp_footer') );
		add_action( 'init',  array($this, '_head_init') );
		if(is_admin())
		 add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array($this, 'plugin_manage_link') , 10, 4 );
		add_shortcode('saphali_user_discount', array($this, 'shortcode') );
		include_once( plugin_dir_path(__FILE__) . 'saphali-plugins.php');
		add_action( 'wp_ajax_nopriv_discount_saphali_hide_ex', array($this, 'discount_saphali_hide_ex') );
		add_action( 'wp_ajax_discount_saphali_hide_ex', array($this, 'discount_saphali_hide_ex') );
		
		add_action( 'wp_ajax_nopriv_added_to_cart_discount_reload', array($this, 'added_to_cart_discount_reload') );
		add_action( 'wp_ajax_added_to_cart_discount_reload', array($this, 'added_to_cart_discount_reload') );
	}
	function wp_footer() {
		if( isset( $this->settings['button_set'] ) && $this->settings['button_set'] ) {
			$image 			= plugin_dir_url( __FILE__ ) . 'img/discount.gif';
			$thumbnail_id 	= $this->settings['thumbnail_id'];
			if ($thumbnail_id) 
				$image = wp_get_attachment_url( $thumbnail_id );
		?>
		<div id="discount-saphali-icons_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo $image; ?>" width="60px" height="60px" /></div>
		<div class="discount-saphali-info">
			<?php echo $this->user_cart_apry_discount(); ?>
		</div>
		<style>
		.discount-saphali-info {display:none;}
		.mfp-content .discount-saphali-info {display:block;}
		.discount-saphali-info { background: #fff none repeat scroll 0 0;    border: 2px solid;    border-radius: 5px;    box-shadow: 10px 5px 25px;    left: 20%;    margin: 0 auto;    padding: 9px;    position: fixed;    top: 33%;    width: 60%;}
		#discount-saphali-icons_thumbnail img {opacity: <?php echo $this->settings['opacity'] ? $this->settings['opacity'] : 1; ?>;border-radius: 30px; box-shadow: 1px 0 10px;}
		div#discount-saphali-icons_thumbnail {  z-index: 9999999999;  border-radius: 30px;    position: fixed;  cursor: pointer;  <?php 
		switch($this->settings['button_position']) {
			case 'lt': echo ' left: 10px; top: 10px;';
			break;
			
			case 'cb': echo ' left: 50%; bottom: 10px;';
			break;
			
			case 'ct': echo ' left: 50%; top: 10px;';
			break;
			
			case 'rc': echo ' top: 50%; right: 10px;' ;
			break;
			
			case 'rb': echo ' bottom: 10px; right: 10px;';
			break;
			
			case 'rt': echo ' top: 10px; right: 10px;';
			break;
			
			case 'lc': echo ' top: 50%; left: 10px;';
			break;
			
			case 'lb': echo ' bottom: 10px; left: 10px;';
			break;
			
			default: echo ' left: 10px; top: 10px;';
			break;
			
		}
		
		?> }
		</style>
		<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__); ?>js/magnific-popup.css?v=0.1">
		<script src="<?php echo plugin_dir_url(__FILE__); ?>js/jquery.magnific-popup.min.js"></script>
		<script type='text/javascript'>
			jQuery(function(){ jQuery('div#discount-saphali-icons_thumbnail').magnificPopup({ items: { src: '.discount-saphali-info', type: 'inline' } }); });
		</script>
		<?php
		}
	}
	function woocommerce_cart_totals_coupon_html($value, $coupon) {
		$type = method_exists($coupon, 'get_discount_type') ? $coupon->get_discount_type() : $coupon->type;
		if (  ( "fixed_total_shop" == $type || "fixed_total_cart" == $type ) ) {
			$value = preg_replace('~<a ([^>]*)>' . str_replace(array('[', ']'), array('\\[', '\\]'), __( '[Remove]', 'woocommerce' )) . '</a>~', '', $value);
		}
		$_v = trim( $value ); 
		if(empty($_v)) {
			return __('скидка отсутствует', 'saphali-discount');
		}
		return $value;
	}
	function woocommerce_coupon_is_valid($valid, $coupon) {
		$type = method_exists($coupon, 'get_discount_type') ? $coupon->get_discount_type() : $coupon->type;
		if( "fixed_total_shop" == $type || "fixed_total_cart" == $type ) {
			if(!$valid)
				$valid = $this->is_valid($coupon);		
		}
		return $valid;
	}
	function plugin_manage_link( $actions, $plugin_file, $plugin_data, $context ) {
		return array_merge( array( 'configure' => '<a href="' . admin_url( 'admin.php?page=woo_page_cooupon_s_page_setings' ) . '">' . __( 'Settings' ) . '</a>', 'configure-1' => '<a href="' . admin_url( 'edit.php?post_type=shop_coupon' ) . '">' . __( 'Coupons', 'woocommerce' ) . ' WC</a>'), 
		$actions );
	}
	function woocommerce_coupon_error($err, $err_code, $coupon) {
		$type = method_exists($coupon, 'get_discount_type') ? $coupon->get_discount_type() : $coupon->type;
		if( "fixed_total_shop" == $type || "fixed_total_cart" == $type ) {
			$code = method_exists($coupon, 'get_code') ? $coupon->get_code() : $coupon->code;
			$c_id = method_exists($coupon, 'get_id') ? $coupon->get_id() : $coupon->id;
			$err = str_replace(array('"' . $code . '"', $code), '' , $err);
			
			if( !empty($err) && ( isset($this->is_valid_r[$c_id]) && $this->is_valid_r[$c_id] || !isset($this->is_valid_r[$c_id]) ) && in_array( $err_code, array(101, 109, 110, 113, 114) ) ) {
				$valid = $this->is_valid($coupon);
				if($valid) {
					return '';
				}
			}
			global $woocommerce;
			unset($woocommerce->session->global_discount_saphali);
		}
		
		return $err;
	}
	
	function added_to_cart_discount_reload() {
		global $woocommerce;
		$this->saph_min_total_order = get_option( 'saph_min_total_order','');
		if( !empty($this->saph_min_total_order) && $woocommerce->cart->subtotal < $this->saph_min_total_order && !$this->saph_min_total_order_no_all_page) {
			//$this->comp_woocomerce_mess_error(  sprintf( __('Минимальный заказ от %s! <br>Чтобы сделать заказ добавьте позиций на сумму %s', 'saphali-discount'), $this->wc_price( $this->saph_min_total_order )  , $this->wc_price( (//$this->saph_min_total_order - $woocommerce->cart->subtotal) ) ) );
			$woocommerce_add_error = sprintf( __('Минимальный заказ от %s! <br>Чтобы сделать заказ добавьте позиций на сумму %s', 'saphali-discount'), $this->wc_price( $this->saph_min_total_order )  , $this->wc_price( ($this->saph_min_total_order - $woocommerce->cart->subtotal) ) );
			
		} else $woocommerce_add_error = false;
		if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {
			if( isset($_SESSION['discount_saphali_next_return']) && $_SESSION['discount_saphali'] === $_SESSION['discount_saphali_next']) 
			unset( $_SESSION['discount_saphali_next_return'] );		
		
		} else {
			if( isset($woocommerce->session->discount_saphali_next_return) && $woocommerce->session->discount_saphali === $woocommerce->session->discount_saphali_next) 
			unset( $woocommerce->session->discount_saphali_next_return );		
		}
		if( !version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) && empty($woocommerce->session->global_discount_saphali) || version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) && empty($_SESSION['global_discount_saphali']) ) die( json_encode( array('html' => '', 'msg' => $woocommerce_add_error )) );
		if( !version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) && !(isset($woocommerce->session->discount_saphali_hide) && $woocommerce->session->discount_saphali_hide) ||  version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) && !(isset($_SESSION['discount_saphali_hide']) && $_SESSION['discount_saphali_hide']) ) {
			if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
				if(isset($_SESSION['discount_saphali_next_return'])) $discount_saphali_next_return =  '<br />'.$woocommerce->session->discount_saphali_next_return; else $discount_saphali_next_return = '';
			} else {
				if(isset($woocommerce->session->discount_saphali_next_return))  $discount_saphali_next_return = '<br />'.$woocommerce->session->discount_saphali_next_return; else $discount_saphali_next_return = '';
			}
			die(  json_encode( array('html' => sprintf(__( 'Ваша накопительная скидка по текущей закупке составляет %s', 'saphali-discount' ), $woocommerce->session->global_discount_saphali) . $discount_saphali_next_return . '<span class="close" title="Закрыть"></span>', 'msg' => $woocommerce_add_error )  ) );
		} else die( json_encode( array('html' => '', 'msg' => $woocommerce_add_error )) );
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) $woocommerce->clear_messages();
		else
		wc_clear_notices();
	}
	function _head_init() {
		if( is_cart() ) {
			global $woocommerce;
			if(isset($_GET['empty-cart'])) {
				unset($woocommerce->session->global_discount_saphali);
			}
		}
	}
	function wc_print_notices() {
		echo '<div class="woocommerce-message" style="display: none;"> <span title="Закрыть" class="close"> &nbsp;</span></div>';
	}
	function head_init() {
		global $woocommerce;
		if( is_cart() || ( is_checkout() && !isset($_GET['key']))  || ( ( is_singular(array('product')) || is_tax(array("product_cat", "product_tag", "brands")) || is_post_type_archive('product') ) && !$this->saph_min_total_order_no_all_page ) ) {
			$this->saph_min_total_order = get_option( 'saph_min_total_order','');
			if( !empty($this->saph_min_total_order) && $woocommerce->cart->subtotal < $this->saph_min_total_order ) {
				$this->comp_woocomerce_mess_error(  sprintf( __('Минимальный заказ от %s! <br>Чтобы сделать заказ добавьте позиций на сумму %s', 'saphali-discount'), $this->wc_price( $this->saph_min_total_order )  , $this->wc_price( ($this->saph_min_total_order - $woocommerce->cart->subtotal) ) ) );
			}
			
		}
		if( is_checkout() ) {
			if( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) )
				$woocommerce->add_inline_js("jQuery('body').delegate('input#billing_cart_club', 'focusin', function(){
						jQuery('input#billing_cart_club').bind( 'focusout', function(){
						jQuery( 'body' ).trigger( 'update_checkout' );
						jQuery('input#billing_cart_club').unbind( 'focusout' );
					});
				});
				if(". $this->mask .") jQuery('#billing_cart_club').mask(\"+7(xxx) xxx-xxxx\");
				");
				else 
					wc_enqueue_js("
				jQuery('body').delegate('input#billing_cart_club', 'focusin', function(){
						jQuery('input#billing_cart_club').bind( 'focusout', function(){
						jQuery( 'body' ).trigger( 'update_checkout' );
						jQuery('input#billing_cart_club').unbind( 'focusout' );
					});
				});
				if(". $this->mask .") jQuery('#billing_cart_club').mask(\"+7(xxx) xxx-xxxx\");
				");
					
		}
		if( ( is_singular(array('product')) || is_tax(array("product_cat", "product_tag", "brands")) || is_post_type_archive('product') ) && !(is_cart() || is_checkout()) ) {
			
			if( !version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) && empty($woocommerce->session->global_discount_saphali) || version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) && empty($_SESSION['global_discount_saphali']) ) {
				//$this->comp_woocomerce_mess( ' <span class="close" title="Закрыть"> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
				add_action( 'woocommerce_before_shop_loop', array($this, 'wc_print_notices'), 10 );
				add_action('wp_footer', array($this, 'add_footer_style') );	
				if( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) )
				$woocommerce->add_inline_js( "jQuery('.woocommerce-message span.close').click( function() {
											var t_his = jQuery(this).parent(); jQuery(this).hide('slow');
											"
											.
											
														"
														if( jQuery(this).attr('title') == 'Закрыть ') {
															var data = {action: 'discount_saphali_hide_ex', discount_saphali_next: 'true'};
														} else 
														var data = {action: 'discount_saphali_hide_ex', discount_saphali: 'true'};
														jQuery.ajax({
					type: 'POST',
					dataType: 'json',
					url: '" . site_url('wp-admin') . "/admin-ajax.php',
					data: data, 
					success: function(msg)
					{	
						if(typeof msg.ok != 'undefined'  && msg.ok == true ) {
							t_his.hide('slow');
						}

					},
					error: function() {}
				});
								"			.
											"
											
				});
				jQuery('div.woocommerce-message span.close').parent().hide();
				if(typeof jQuery('ul.woocommerce-error li').text() == 'undefined' || (jQuery('ul.woocommerce-error li').text() === ''))
				jQuery('div.woocommerce-message').before('<ul class=\"woocommerce-error\" style=\"display:none\"><li> </li></ul>');
				jQuery('body').on('added_to_cart', function(){
					var data = {action: 'added_to_cart_discount_reload'};
					jQuery.ajax({
						type: 'POST',
						dataType: 'json',
						url: '" . site_url('wp-admin') . "/admin-ajax.php',
						data: data, 
						success: function(msg)
						{
							if( typeof msg.msg != 'undefined' ) {
								if(!msg.msg) {
									jQuery('ul.woocommerce-error').hide('slow');
								} else {
									jQuery('ul.woocommerce-error li').html( msg.msg );
									jQuery('ul.woocommerce-error').show('slow');
								}
							}
							if( typeof msg.html != 'undefined'  && msg.html != '' ) {
								jQuery('div.woocommerce-message span.close').parent().html( msg.html ).show();
							} else {
								jQuery('div.woocommerce-message span.close').parent().hide('slow');
							}
						},
						error: function() {

						}
					});
				});
				" );
				else 
				wc_enqueue_js( "jQuery('.woocommerce-message span.close').click( function() {
											var t_his = jQuery(this).parent(); jQuery(this).hide('slow');
											"
											.
											
														"
														if( jQuery(this).attr('title') == 'Закрыть ') {
															var data = {action: 'discount_saphali_hide_ex', discount_saphali_next: 'true'};
														} else 
														var data = {action: 'discount_saphali_hide_ex', discount_saphali: 'true'};
														jQuery.ajax({
					type: 'POST',
					dataType: 'json',
					url: '" . site_url('wp-admin') . "/admin-ajax.php',
					data: data, 
					success: function(msg)
					{	
						if(typeof msg.ok != 'undefined'  && msg.ok == true ) {
							t_his.hide('slow');
						}

					},
					error: function() {}
				});
								"			.
											"
											
				});
				jQuery('div.woocommerce-message span.close').parent().hide();
				if(typeof jQuery('ul.woocommerce-error li').text() == 'undefined' || (jQuery('ul.woocommerce-error li').text() === ''))
				jQuery('div.woocommerce-message').before('<ul class=\"woocommerce-error\" style=\"display:none\"><li> </li></ul>');
				jQuery('body').on('added_to_cart', function(){
					var data = {action: 'added_to_cart_discount_reload'};
					jQuery.ajax({
						type: 'POST',
						dataType: 'json',
						url: '" . site_url('wp-admin') . "/admin-ajax.php',
						data: data, 
						success: function(msg)
						{
							if( typeof msg.msg != 'undefined' ) {
								if(!msg.msg) {
									jQuery('ul.woocommerce-error').hide('slow');
								} else {
									jQuery('ul.woocommerce-error li').html( msg.msg );
									jQuery('ul.woocommerce-error').show('slow');
								}
							}
							if( typeof msg.html != 'undefined'  && msg.html != '' ) {
								jQuery('div.woocommerce-message span.close').parent().html( msg.html ).show();
							} else {
								jQuery('div.woocommerce-message span.close').parent().hide('slow');
							}
						},
						error: function() {

						}
					});
				});	
				" );
				return;
			}
			if(   !version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) && !(isset($woocommerce->session->discount_saphali_hide) && $woocommerce->session->discount_saphali_hide) ||  version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) && !(isset($_SESSION['discount_saphali_hide']) && $_SESSION['discount_saphali_hide']) ) {
				if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
					if(isset($_SESSION['discount_saphali_next_return'])) $discount_saphali_next_return =  '<br />'.$woocommerce->session->discount_saphali_next_return; else $discount_saphali_next_return = '';
				} else {
					if(isset($woocommerce->session->discount_saphali_next_return))  $discount_saphali_next_return = '<br />'.$woocommerce->session->discount_saphali_next_return; else $discount_saphali_next_return = '';
				}
				$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по текущей закупке составляет %s', 'saphali-discount' ), $woocommerce->session->global_discount_saphali) . $discount_saphali_next_return . '<span class="close" title="Закрыть"> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
				add_action('wp_footer', array($this, 'add_footer_style') );	
				if( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) )
				$woocommerce->add_inline_js( "jQuery('.woocommerce-message span.close').click( function() {
											var t_his = jQuery(this).parent(); jQuery(this).hide('slow');
											"
											.
											
														"
														if( jQuery(this).attr('title') == 'Закрыть ') {
															var data = {action: 'discount_saphali_hide_ex', discount_saphali_next: 'true'};
														} else 
														var data = {action: 'discount_saphali_hide_ex', discount_saphali: 'true'};
														jQuery.ajax({
					type: 'POST',
					dataType: 'json',
					url: '" . site_url('wp-admin') . "/admin-ajax.php',
					data: data, 
					success: function(msg)
					{	
						if(typeof msg.ok != 'undefined'  && msg.ok == true ) {
							t_his.hide('slow');
						}

					},
					error: function() {}
				});
								"			.
											"
											
				});
				if(typeof jQuery('ul.woocommerce-error li').text() == 'undefined' || (jQuery('ul.woocommerce-error li').text() === ''))
				jQuery('div.woocommerce-message').before('<ul class=\"woocommerce-error\" style=\"display:none\"><li> </li></ul>');
				jQuery('body').on('added_to_cart', function(){
					var data = {action: 'added_to_cart_discount_reload'};
					jQuery.ajax({
						type: 'POST',
						dataType: 'json',
						url: '" . site_url('wp-admin') . "/admin-ajax.php',
						data: data, 
						success: function(msg)
						{	
							if( typeof msg.msg != 'undefined' ) {
								if(!msg.msg) {
									jQuery('ul.woocommerce-error').hide('slow');
								} else {
									jQuery('ul.woocommerce-error li').html( msg.msg );
									jQuery('ul.woocommerce-error').show('slow');
								}
							}
							if( typeof msg.html != 'undefined'  && msg.html != '' ) {
								jQuery('div.woocommerce-message span.close').parent().html( msg.html ).show();
							} else jQuery('div.woocommerce-message span.close').parent().hide('slow');
						},
						error: function() {

						}
					});
				});
				" );
				else 
				wc_enqueue_js( "jQuery('.woocommerce-message span.close').click( function() {
											var t_his = jQuery(this).parent(); jQuery(this).hide('slow');
											"
											.
											
														"
														if( jQuery(this).attr('title') == 'Закрыть ') {
															var data = {action: 'discount_saphali_hide_ex', discount_saphali_next: 'true'};
														} else 
														var data = {action: 'discount_saphali_hide_ex', discount_saphali: 'true'};
														jQuery.ajax({
					type: 'POST',
					dataType: 'json',
					url: '" . site_url('wp-admin') . "/admin-ajax.php',
					data: data, 
					success: function(msg)
					{	
						if(typeof msg.ok != 'undefined'  && msg.ok == true ) {
							t_his.hide('slow');
						}

					},
					error: function() {}
				});
								"			.
											"
											
				});
				if(typeof jQuery('ul.woocommerce-error li').text() == 'undefined' || (jQuery('ul.woocommerce-error li').text() === ''))
				jQuery('div.woocommerce-message').before('<ul class=\"woocommerce-error\" style=\"display:none\"><li> </li></ul>');
				jQuery('body').on('added_to_cart', function(){
					var data = {action: 'added_to_cart_discount_reload'};
					jQuery.ajax({
						type: 'POST',
						dataType: 'json',
						url: '" . site_url('wp-admin') . "/admin-ajax.php',
						data: data, 
						success: function(msg)
						{	
							if( typeof msg.msg != 'undefined' ) {
								if(!msg.msg) {
									jQuery('ul.woocommerce-error').hide('slow');
								} else {
									jQuery('ul.woocommerce-error li').html( msg.msg );
									jQuery('ul.woocommerce-error').show('slow');
								}
							}
							if( typeof msg.html != 'undefined'  && msg.html != '' ) {
								jQuery('div.woocommerce-message span.close').parent().html( msg.html ).show();
							} else jQuery('div.woocommerce-message span.close').parent().hide('slow');
						},
						error: function() {

						}
					});
				});
				" );
			}
		} elseif( (is_cart() || is_checkout()) ) {
			if( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) )
			$woocommerce->add_inline_js("
			if( jQuery('tr.order-discount').length > 1) {
				jQuery('tr.order-discount').find('td').each(function(i,e) {
					if(jQuery(this).text() == '') { jQuery(this).parent().hide(); }
					if(jQuery(this).text() == ' ".__('[Remove]','woocommerce')."') { jQuery(this).parent().hide(); }
				});
			}
			jQuery('body').bind('updated_checkout', function() {
				if( jQuery('tr.order-discount').length > 1) {
					jQuery('tr.order-discount').find('td').each(function(i,e) {
						if(jQuery(this).text() == '') { jQuery(this).parent().hide(); }
						if(jQuery(this).text() == ' ".__('[Remove]','woocommerce')."') { jQuery(this).parent().hide(); }
					});
				}
			});
			jQuery('body').bind('updated_shipping_method', function() {
				if( jQuery('tr.order-discount').length > 1) {
					jQuery('tr.order-discount').find('td').each(function(i,e) {
						if(jQuery(this).text() == '') { jQuery(this).parent().hide(); }
						if(jQuery(this).text() == ' ".__('[Remove]','woocommerce')."') { jQuery(this).parent().hide(); }
					});
				}
			});
			");
			else
			wc_enqueue_js("
			if( jQuery('tr.order-discount').length > 1) {
				jQuery('tr.order-discount').find('td').each(function(i,e) {
					if(jQuery(this).text() == '') { jQuery(this).parent().hide(); }
					if(jQuery(this).text() == ' ".__('[Remove]','woocommerce')."') { jQuery(this).parent().hide(); }
				});
			}
			jQuery('body').bind('updated_checkout', function() {
				if( jQuery('tr.order-discount').length > 1) {
					jQuery('tr.order-discount').find('td').each(function(i,e) {
						if(jQuery(this).text() == '') { jQuery(this).parent().hide(); }
						if(jQuery(this).text() == ' ".__('[Remove]','woocommerce')."') { jQuery(this).parent().hide(); }
					});
				}
			});
			jQuery('body').bind('updated_shipping_method', function() {
				if( jQuery('tr.order-discount').length > 1) {
					jQuery('tr.order-discount').find('td').each(function(i,e) {
						if(jQuery(this).text() == '') { jQuery(this).parent().hide(); }
						if(jQuery(this).text() == ' ".__('[Remove]','woocommerce')."') { jQuery(this).parent().hide(); }
					});
				}
			});
			");
		}
	}
	function f_deprecated($message, $product_id) {
		$this->f($product_id);
		return $message;
	}
	function f($product_id) {
		if( $product_id ) {
		global $woocommerce;
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) $woocommerce->nocache();
		$coupons = get_posts(array('post_type' => 'shop_coupon', 'post_status' => 'publish', 'meta_key' => 'discount_type', 'meta_value' => 'fixed_total_cart', 'posts_per_page' => -1));
		
		foreach($coupons as $rev_key => $_coupon) {
			$coupon_code[$rev_key] = $_coupon->post_title;
			$variant_discount[$rev_key] = get_post_meta( $_coupon->ID, 'variant_discount', true );
			$customer_email = get_post_meta( $_coupon->ID, 'customer_email', true );
			$customer_cart_club = get_post_meta( $_coupon->ID, 'customer_cart_club', true );
			$exclude_revers_items_product = ( get_post_meta( $_coupon->ID, 'exclude_revers_items_product', true ) == 'yes' ) ? true : false;
            $filya = true;
			if($filya) {
				$coupone_customer_role = get_post_meta( $_coupon->ID, 'saphali_coupone_customer_role', true );
				$coupone_customer_no_role = get_post_meta( $_coupon->ID, 'saphali_coupone_customer_no_role', true );
				if ( !empty($coupone_customer_role) && is_array($coupone_customer_role) ) {
                    $ob = is_object($ob) ? $ob : wp_get_current_user();
					if (!( isset($ob->roles[0]) && in_array( $ob->roles[0], $coupone_customer_role) )) {
						$remove_coupon_code[] = $_coupon->post_title;
						$filya = false;
					}
				}
				if ( $filya && !empty($coupone_customer_no_role) && is_array($coupone_customer_no_role) ) {
					$ob = is_object($ob) ? $ob : wp_get_current_user();
					if (isset($ob->roles[0]) && in_array( $ob->roles[0], $coupone_customer_no_role)) {
						$remove_coupon_code[] = $_coupon->post_title;
					}
				}
			}
			$revers_items_product[$rev_key] = 0;
			if($exclude_revers_items_product) {
				if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
						$exclude_product_ids = method_exists($_coupon, 'get_excluded_product_ids') ? $_coupon->get_excluded_product_ids() : $_coupon->exclude_product_ids;
						if(!is_array($exclude_product_ids) ) $exclude_product_ids = explode(',', $exclude_product_ids );
						$exclude_product_ids = array_map('trim',$exclude_product_ids);
						if($exclude_product_ids)
						$exclude_product_ids = array_filter($exclude_product_ids);
						if($_coupon->exclude_sale_items == 'yes') 
							$product_ids_on_sale = woocommerce_get_product_ids_on_sale();
						if(is_null($product_ids_on_sale)) $product_ids_on_sale = array();
						$i_on_s = array_search(0, $product_ids_on_sale);
						if($i_on_s !== false) unset($product_ids_on_sale[$i_on_s]);
						foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
							$ex = 1;
							if ( is_array($exclude_product_ids)  && (in_array( $cart_item['product_id'], $exclude_product_ids ) || in_array( $cart_item['variation_id'], $exclude_product_ids ) || in_array( $cart_item['data']->get_parent(), $exclude_product_ids ) ) ) {
								$revers_items_product[$rev_key] = $revers_items_product[$rev_key] + ($cart_item['data']->get_price() * $cart_item['quantity']);
								$ex = 0;
							} elseif(is_array($exclude_product_ids) && !empty($exclude_product_ids) ) {
								$product_cats = wp_get_post_terms( $cart_item['product_id'], 'product_cat', array( "fields" => "ids" ) );
								if( sizeof( array_intersect( $product_cats, $exclude_product_ids ) ) > 0 ) {
									$revers_items_product[$rev_key] = $revers_items_product[$rev_key] + ($cart_item['data']->get_price() * $cart_item['quantity']);
									$ex = 0;
								}
							} 
							if ( $ex && !(isset($cart_item['variation_id']) && $cart_item['variation_id']>0) && in_array( $cart_item['product_id'], $product_ids_on_sale, true ) || in_array( $cart_item['variation_id'], $product_ids_on_sale, true ) || in_array( $cart_item['data']->get_parent(), $product_ids_on_sale, true ) ) {
								$revers_items_product[$rev_key] = $revers_items_product[$rev_key] + ($cart_item['data']->get_price() * $cart_item['quantity']);
							}
						}
				}
			}
		}
		if(isset($coupon_code))
		foreach($coupon_code as $r_key => $r_value) {
			if(isset($remove_coupon_code) && in_array($r_value, $remove_coupon_code)) {
				unset($coupon_code[$r_key], $variant_discount[$r_key]);
			} 
		}
		if(!empty($coupon_code)) {
			//fix wpml
			$coupon_code = array_unique($coupon_code);
			$add_coupon = true;
			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				if(!empty($customer_email)) {
					$add_coupon = false;
				} elseif(!empty($customer_cart_club)) {
					$add_coupon = false;
				}
				$check_emails[] = $current_user->user_email;
				$check_emails[] = get_user_meta($current_user->ID, 'billing_email', true);

				$check_emails = array_unique($check_emails);
				
				if(is_array($check_emails)) {
					foreach($check_emails as $user_email) {
						if(!empty($customer_email)) {
							if(!empty($user_email))
							if(in_array( $user_email, $customer_email ))
							{
								$add_coupon = true;
								break;
							}
						} else {

						}
					}
				}
				if(!$add_coupon) {
					$check_ps[] = get_user_meta($current_user->ID, 'billing_cart_club', true);
					if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
						if(isset($_SESSION['d_phone']))
						$post_data["billing_cart_club"] = $_SESSION['d_phone'];
					} else {
						if(isset($woocommerce->session->d_phone))
							$post_data["billing_cart_club"] = $woocommerce->session->d_phone;
					}
					if(isset($post_data["billing_cart_club"])) {
						$check_ps[] = $post_data["billing_cart_club"];
					}

					$check_ps = array_unique($check_ps);
					//f
					if(is_array($check_ps)) {
						foreach($check_ps as $user_p) {
							if(!empty($customer_cart_club)) {
								if(!empty($user_p))
								if(in_array( $user_p, $customer_cart_club ))
								{
									$add_coupon = true;
									break;
								}
								$b = 0;
								foreach($customer_cart_club as $phone) {
									if( strpos( $phone, str_replace('+', '', $user_p) ) !== false && mb_strlen($user_p, 'utf-8') > 7) {
										$add_coupon = true;
										$b = 1; break;
									}
								}
								if($b) break;	
							} else {

							}
						}
					}
				}
			} else {
				
				if(!empty($customer_email)) {
					$add_coupon = false;
				}
				// funct f
				if(!empty($customer_cart_club)) {
					$add_coupon = false;
					$check_ps = array();
					if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
						if(isset($_SESSION['d_phone']))
						$post_data["billing_cart_club"] = $_SESSION['d_phone'];
					} else {
						if(isset($woocommerce->session->d_phone))
							$post_data["billing_cart_club"] = $woocommerce->session->d_phone;
					}
					if(isset($post_data["billing_cart_club"])) {
						$check_ps[] = $post_data["billing_cart_club"]; 
						$check_ps = array_unique($check_ps);
					}
					//f
					if($check_ps) {
						foreach($check_ps as $user_p) {
							if(!empty($customer_cart_club)) {
								if(!empty($user_p)) {
									if( in_array( $user_p, $customer_cart_club ) )
									{
										$add_coupon = true;
										break;
									}
									$b = 0;
									foreach($customer_cart_club as $phone) {
										if( strpos( $phone, str_replace('+', '', $user_p) ) !== false && mb_strlen($user_p, 'utf-8') > 7) {
											$add_coupon = true;
											$b = 1; break;
										}
									}
									if($b) break;	
								}
								
							} else {

							}
						}
					}
				}
			}
			if( $add_coupon  ) {
				$discount = array();
				if(is_array($variant_discount))
				foreach($variant_discount as $key => $_variant_discount) {
					if( DISCOUNT_NO_EXC_PROD_SUMM_IN_REVERS ) {
						$revers_items_product[$key] = 0;
					}
					$cart_contents_total = $woocommerce->cart->subtotal - $revers_items_product[$key];
					$cart_contents_total = $cart_contents_total / $this->compatibility_currency_Aelia();
					$cart_contents_total = apply_filters( 'WOOMULTI_CURRENCY_R', $cart_contents_total );
					if(isset($_COOKIE['saphali'])) var_dump($cart_contents_total, __LINE__);
					foreach($_variant_discount['min'] as $_key => $_discount) {
						if( $cart_contents_total >= $_discount && $cart_contents_total <= $variant_discount[$key]['max'][$_key] ) {
							$discount[$key] = $variant_discount[$key]['discount'][$_key];
						}
					}
				}
			}
			if( $add_coupon && $discount  ) {
				foreach($woocommerce->cart->applied_coupons as $_c_ )
				$coupon__ = new WC_Coupon( $_c_ );
				if( !( "fixed_total_shop" == $coupon__->type || "fixed_total_cart" == $coupon__->type ) && isset($coupon__->individual_use) && $coupon__->individual_use == 'yes' ) return;
				
				foreach($coupon_code as $_k => $code) {
					$numb_ind = array_search( $discount[$_k], $variant_discount[$_k]["discount"] );
					if(empty($discount[$_k])) {
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
							foreach($woocommerce->cart->applied_coupons as $key => $_code) {
								if($code == $_code) {
									$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка (%s) по текущему заказу аннулирована', 'saphali-discount' ), $_SESSION['discount_saphali'][$key] ) );
									unset( $_SESSION['discount_saphali'][$key], $_SESSION['discount_saphali_next'][$key] , $_SESSION['global_discount_saphali'] ); 
									unset($woocommerce->cart->applied_coupons[$key]);
									
								}
							}
							$_SESSION['coupons'] = $woocommerce->cart->applied_coupons;
						} else {
							foreach($woocommerce->cart->applied_coupons as $key => $_code) {
								if($code == $_code) {
									
									$this->comp_woocomerce_mess(  sprintf(__( 'Ваша накопительная скидка (%s) по текущему заказу аннулирована', 'saphali-discount' ), $woocommerce->session->discount_saphali[$key] ) );
									
									if(empty($woocommerce->session->discount_saphali)) $woocommerce->session->discount_saphali = array();
									if(empty($woocommerce->session->discount_saphali_next)) $woocommerce->session->discount_saphali_next = array();
									$session = $woocommerce->session->discount_saphali;
									$session_next = $woocommerce->session->discount_saphali_next;
									unset($session[$key],$session_next[$key] , $woocommerce->session->global_discount_saphali, $woocommerce->session->discount_saphali_hide_next , $woocommerce->session->discount_saphali_hide );
									$woocommerce->session->discount_saphali = $session;
									$woocommerce->session->discount_saphali_next = $session_next;
									unset($woocommerce->cart->applied_coupons[$key]);
								}
							}
							$woocommerce->session->coupon_codes = $woocommerce->cart->applied_coupons;
						}
						continue;
					}
					if(isset($woocommerce->cart->coupon_discount_amounts[$code]) && !isset($woocommerce->cart->applied_coupons[$_k]) )
						$woocommerce->cart->applied_coupons[$_k] = $code;
					$woocommerce->cart->applied_coupons = array_unique($woocommerce->cart->applied_coupons);
					sort($woocommerce->cart->applied_coupons);
					if(is_array($woocommerce->cart->applied_coupons)) {
						$status_add_code = false;
						if( !in_array($code, $woocommerce->cart->applied_coupons)   ) {
							$__coupon = new WC_Coupon( $code );
							if($this->is_valid($__coupon))
							$status_add_code = $this->add_discount( $code );
						} else {
							
							if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								
								foreach($woocommerce->cart->applied_coupons as $key => $_code) {
									if($code == $_code) {  
										if($discount[$_k] != $_SESSION['discount_saphali'][$key] ) {
											$coupon = new WC_Coupon($code);
											$this->info_cart_checkout = get_post_meta( $coupon->id, 'info_cart_checkout', true );
											$this->info_cart_checkout_all = get_post_meta( $coupon->id, 'info_cart_checkout_all', true );
											if ( strstr( $discount[$_k], '%' ) )  $_discount_str = $discount[$_k];
												else  $_discount_str = $this->wc_price($discount[$_k]);
											
											if($this->info_cart_checkout_all == 'yes')
											$_SESSION['global_discount_saphali']= $_discount_str;
											else {
												if(isset($_SESSION['global_discount_saphali']) ) unset($_SESSION['global_discount_saphali']);
											}
											if ( strstr( $_SESSION['discount_saphali'][$key], '%' ) )  $_discount_strs = $_SESSION['discount_saphali'][$key];
												else  $_discount_strs = $this->wc_price($_SESSION['discount_saphali'][$key]);
											if($this->info_cart_checkout != 'yes') {
												$this->comp_woocomerce_mess( sprintf(__( 'Изменена накопительная скидка с %s на %s.', 'saphali-discount' ), $_discount_strs , $_discount_str ) );
												$this->edit_disc = true;
											}
											else {
												$this->comp_woocomerce_mess( sprintf(__( 'Изменена накопительная скидка с %s на %s.', 'saphali-discount' ), $_discount_strs , $_discount_str ) );
												
												$this->edit_disc = true;
											}
											unset($_SESSION['discount_saphali_hide_next'] , $_SESSION['discount_saphali_hide'] );
										}
										$_SESSION['discount_saphali'][$key] = $discount[$_k]; 
										if( isset( $variant_discount[$_k]["discount"][$numb_ind+1] ) ) 
										$_SESSION['discount_saphali_next'][$key] = $variant_discount[$_k]["discount"][$numb_ind+1]; 
									}
								}
							} else {

								foreach($woocommerce->cart->applied_coupons as $key => $_code) {
									if($code == $_code) {  
									
										if($discount[$_k] != $woocommerce->session->discount_saphali[$key] ) {
											$coupon = new WC_Coupon($code);

											$this->info_cart_checkout = get_post_meta( $coupon->id, 'info_cart_checkout', true );
											$this->info_cart_checkout_all = get_post_meta( $coupon->id, 'info_cart_checkout_all', true );
											if ( strstr( $discount[$_k], '%' ) )  $_discount_str = $discount[$_k];
												else  $_discount_str = $this->wc_price($discount[$_k]);
											if($this->info_cart_checkout_all == 'yes')
											$woocommerce->session->global_discount_saphali = $_discount_str;
											else {
												if(isset($woocommerce->session->global_discount_saphali) ) unset($woocommerce->session->global_discount_saphali);
											}
											
											if ( strstr( $woocommerce->session->discount_saphali[$key], '%' ) )  $_discount_strs = $woocommerce->session->discount_saphali[$key];
												else  $_discount_strs = $this->wc_price($woocommerce->session->discount_saphali[$key]);
												
											if($this->info_cart_checkout != 'yes') {
												$this->comp_woocomerce_mess( sprintf(__( 'Изменена накопительная скидка с %s на %s.', 'saphali-discount' ), $_discount_strs , $_discount_str) );
												$this->edit_disc = true;
											}
											else {
												$this->comp_woocomerce_mess( sprintf(__( 'Изменена накопительная скидка с %s на %s.', 'saphali-discount' ), $_discount_strs , $_discount_str) );
												$this->edit_disc = true;
											}
											unset($woocommerce->session->discount_saphali_hide_next , $woocommerce->session->discount_saphali_hide );
										}
										if(empty($woocommerce->session->discount_saphali)) $woocommerce->session->discount_saphali = array();
										if(empty($woocommerce->session->discount_saphali_next)) $woocommerce->session->discount_saphali_next = array();
										$session = $woocommerce->session->discount_saphali;
										$session[$key] = $discount[$_k];
										$woocommerce->session->discount_saphali = $session;
										
										$session_next = $woocommerce->session->discount_saphali_next;
										$session_next = isset($variant_discount[$_k]["discount"][$numb_ind+1]) ?  array($key => $variant_discount[$_k]["discount"][$numb_ind+1]) + $session_next : $session_next;
										$woocommerce->session->discount_saphali_next = $session_next;
									}
								}
								//echo '<br />'; var_dump($code, $woocommerce->cart->applied_coupons, $woocommerce->session->discount_saphali);
							}
						}
					} else {
							$__coupon = new WC_Coupon( $code );
							if($this->is_valid($__coupon))
							$status_add_code = $this->add_discount( $code );
							else $status_add_code = false;
					}
					if($status_add_code) {
						foreach($woocommerce->cart->applied_coupons as $key => $_code) {
							if($code == $_code) { $index = $key; if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) unset($_SESSION['discount_saphali_hide'], $_SESSION['discount_saphali_hide_next'] ); else unset($woocommerce->session->discount_saphali_hide,$woocommerce->session->discount_saphali_hide_next); }
						}
						
						if ( strstr( $discount[$_k], '%' ) )  $_discount_str = $discount[$_k];
						 else  $_discount_str = $this->wc_price($discount[$_k]);
						 
						//$this->messege_code = array(sprintf(__( 'Ваша накопительная скидка по текущему заказу составила %s', 'saphali-discount' ), $_discount_str) ) + $this->messege_code;
						//add_filter("woocommerce_add_message", array($this,"woocommerce_add_message"), 10 , 1);
						
						$coupon = new WC_Coupon($code);
						$this->info_cart_checkout_all = get_post_meta( $coupon->id, 'info_cart_checkout_all', true );
						if ( $coupon->individual_use == 'yes' ) {
							unset($woocommerce->session->discount_saphali);
							$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по текущему заказу составила %s', 'saphali-discount' ), $_discount_str) . __(' <br />В заказе используется только эта скидка.', 'saphali-discount'). '<span class="close" title="Закрыть"> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
						} else {
							$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по текущему заказу составила %s', 'saphali-discount' ), $_discount_str). '<span class="close" title="Закрыть"> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
							
						}
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {

						   $_SESSION['discount_saphali'][$index] = $discount[$_k];
						   if($this->info_cart_checkout_all == 'yes')
						   $_SESSION['global_discount_saphali']= $_discount_str;
						   else {
									if(isset($_SESSION['global_discount_saphali']) ) unset($_SESSION['global_discount_saphali']);
								}
						} else {
							if(empty($woocommerce->session->discount_saphali)) $woocommerce->session->discount_saphali = array();
							if(empty($woocommerce->session->discount_saphali_next)) $woocommerce->session->discount_saphali_next = array();
							if($this->info_cart_checkout_all == 'yes')
							$woocommerce->session->global_discount_saphali = $_discount_str;
							else {
									if(isset($woocommerce->session->global_discount_saphali) ) unset($woocommerce->session->global_discount_saphali);
								}
							
							$session = $woocommerce->session->discount_saphali;
							$session = array($index => $discount[$_k]) + $session ;
							$woocommerce->session->discount_saphali = $session;
							
							$session_next = $woocommerce->session->discount_saphali_next;
							$session_next = isset($variant_discount[$_k]["discount"][$numb_ind+1]) ? array($index => $variant_discount[$_k]["discount"][$numb_ind+1]) + $session_next : $session_next;
							$woocommerce->session->discount_saphali_next = $session_next;
						}
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								if($_SESSION['discount_saphali'][$index] != $_SESSION['discount_saphali_next'][$index] && isset($_SESSION['discount_saphali_next'][$index]) && !(isset($_SESSION['discount_saphali_hide_next']) && $_SESSION['discount_saphali_hide_next'])  ) {
									if ( strstr( $_SESSION['discount_saphali_next'][$index], '%' ) )  $_discount_str_ = $_SESSION['discount_saphali_next'][$index];
										 else  $_discount_str_ = $this->wc_price($_SESSION['discount_saphali_next'][$index]);
										 $_SESSION['discount_saphali_next_return'] = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
									$this->comp_woocomerce_mess( $_SESSION['discount_saphali_next_return'] . '<span class="close" title="Закрыть "> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
									
								}							
						} else {
								while( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
									$numb_ind++;
								}
								if( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && !isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
									$no_next = false;
									$woocommerce->session->discount_saphali_next_return = '';
									if( isset( $woocommerce->session->discount_saphali_next[$index] ) ) {$ni = $woocommerce->session->discount_saphali_next; unset($ni[$index]); $woocommerce->session->discount_saphali_next = $ni;}
								} elseif( !isset($no_next) ) $no_next = true;
								if( $no_next && $woocommerce->session->discount_saphali[$index] != $woocommerce->session->discount_saphali_next[$index] && isset($woocommerce->session->discount_saphali_next[$index]) && !(isset($woocommerce->session->discount_saphali_hide_next) && $woocommerce->session->discount_saphali_hide_next)  ) {
									if ( strstr( $woocommerce->session->discount_saphali_next[$index], '%' ) )  $_discount_str_ = $woocommerce->session->discount_saphali_next[$index];
										 else  $_discount_str_ = $this->wc_price($woocommerce->session->discount_saphali_next[$index]);
										$woocommerce->session->discount_saphali_next_return = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
									$this->comp_woocomerce_mess( $woocommerce->session->discount_saphali_next_return . '<span class="close" title="Закрыть "> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
								}
						}

					} elseif( in_array($code, $woocommerce->cart->applied_coupons ) && isset($discount[$_k]) &&!empty($discount[$_k])) {
						if ( strstr( $discount[$_k], '%' ) )  $_discount_str = $discount[$_k];
						 else  $_discount_str = $this->wc_price($discount[$_k]);
						$coupon = new WC_Coupon($code);
						$this->info_cart_checkout = get_post_meta( $coupon->id, 'info_cart_checkout', true );
						$this->info_cart_checkout_all = get_post_meta( $coupon->id, 'info_cart_checkout_all', true );

						if( $this->info_cart_checkout == 'yes' && !$this->edit_disc ) {
							foreach($woocommerce->cart->applied_coupons as $key => $_code) {
								if($code == $_code) $index = $key;
							}						
							if ( $coupon->individual_use == 'yes' ) {
								unset($woocommerce->session->discount_saphali);
								
								if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								if(  !(isset($_SESSION['discount_saphali_hide']) && $_SESSION['discount_saphali_hide']) )
								$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по текущему заказу составила %s', 'saphali-discount' ), $_discount_str) . __(' <br />В заказе используется только эта скидка.', 'saphali-discount') . '<span class="close" title="Закрыть"> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
								
								   
								   
								   $_SESSION['discount_saphali'][$index] = $discount[$_k];
								   $_SESSION['discount_saphali_next'][$index] = ( isset($variant_discount[$_k]["discount"][$numb_ind+1])  ) ? $variant_discount[$_k]["discount"][$numb_ind+1] : '' ;
								} else {
								if(  !(isset($woocommerce->session->discount_saphali_hide) && $woocommerce->session->discount_saphali_hide)) {
								
								$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по текущему заказу составила %s', 'saphali-discount' ), $_discount_str) . __(' <br />В заказе используется только эта скидка.', 'saphali-discount') . '<span class="close" title="Закрыть"> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' ); 
								
								}
									if(empty($woocommerce->session->discount_saphali)) $woocommerce->session->discount_saphali = array();
									if(empty($woocommerce->session->discount_saphali_next)) $woocommerce->session->discount_saphali_next = array();
									$session = $woocommerce->session->discount_saphali;
									$session_next = $woocommerce->session->discount_saphali_next;
									$session = array($index => $discount[$_k]) + $session ;
									$session_next = isset($variant_discount[$_k]["discount"][$numb_ind+1]) ?  array($index => $variant_discount[$_k]["discount"][$numb_ind+1]) + $session_next : $session_next;
									$woocommerce->session->discount_saphali = $session;
									
									$woocommerce->session->discount_saphali_next = $session_next;
								}
								$no_next = true;
							} else {
								if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
									if($_SESSION['discount_saphali'][$index] != $_SESSION['discount_saphali_next'][$index] && isset($_SESSION['discount_saphali_next'][$index]) && !(isset($_SESSION['discount_saphali_hide_next']) && $_SESSION['discount_saphali_hide_next'])  ) {
										if ( strstr( $_SESSION['discount_saphali_next'][$index], '%' ) )  $_discount_str_ = $_SESSION['discount_saphali_next'][$index];
											 else  $_discount_str_ = $this->wc_price($_SESSION['discount_saphali_next'][$index]);
											 $_SESSION['discount_saphali_next_return'] = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
										$discount_saphali_next = '<br />' . $_SESSION['discount_saphali_next_return'];
									} else $discount_saphali_next = '';
								} else {
									while( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
										$numb_ind++;
									}
									if( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && !isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
										$no_next = false;
										$woocommerce->session->discount_saphali_next_return = '';
										if( isset( $woocommerce->session->discount_saphali_next[$index] ) ) {$ni = $woocommerce->session->discount_saphali_next; unset($ni[$index]); $woocommerce->session->discount_saphali_next = $ni;}
									} elseif( !isset($no_next) ) $no_next = true;
									if( $no_next && isset($woocommerce->session->discount_saphali_next[$index]) && $woocommerce->session->discount_saphali[$index] != $woocommerce->session->discount_saphali_next[$index] && isset($woocommerce->session->discount_saphali_next[$index]) && !(isset($woocommerce->session->discount_saphali_hide_next) && $woocommerce->session->discount_saphali_hide_next)  ) {
										if ( strstr( $woocommerce->session->discount_saphali_next[$index], '%' ) )  $_discount_str_ = $woocommerce->session->discount_saphali_next[$index];
											 else  $_discount_str_ = $this->wc_price($woocommerce->session->discount_saphali_next[$index]);
										$discount_saphali_next = $woocommerce->session->discount_saphali_next_return = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
										
									} else $discount_saphali_next = '';
								}
								if(  version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) && !(isset($_SESSION['discount_saphali_hide']) && $_SESSION['discount_saphali_hide'])  || ! version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) && !(isset($woocommerce->session->discount_saphali_hide) && $woocommerce->session->discount_saphali_hide) ) {
								
								$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по текущему заказу составила %s', 'saphali-discount' ), $_discount_str) . '<br />' . $discount_saphali_next . '<span class="close" title="Закрыть"> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
								
								}
								
								$no_next = false;
							}
							if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								if( $no_next && $_SESSION['discount_saphali'][$index] != $_SESSION['discount_saphali_next'][$index] && isset($_SESSION['discount_saphali_next'][$index]) && !(isset($_SESSION['discount_saphali_hide_next']) && $_SESSION['discount_saphali_hide_next'])  ) {
									if ( strstr( $_SESSION['discount_saphali_next'][$index], '%' ) )  $_discount_str_ = $_SESSION['discount_saphali_next'][$index];
										 else  $_discount_str_ = $this->wc_price($_SESSION['discount_saphali_next'][$index]);
									$_SESSION['discount_saphali_next_return'] = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
									$this->comp_woocomerce_mess( $_SESSION['discount_saphali_next_return'] . '<span class="close" title="Закрыть "> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
								}
								if($this->info_cart_checkout_all == 'yes')
								$_SESSION['global_discount_saphali'] = $_discount_str;
								else {
									if(isset($_SESSION['global_discount_saphali']) ) unset($_SESSION['global_discount_saphali']);
								}
							} else {
								while( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
									$numb_ind++;
								}
								if( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && !isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
									$no_next = false;
									$woocommerce->session->discount_saphali_next_return = '';
									if( isset( $woocommerce->session->discount_saphali_next[$index] ) ) {$ni = $woocommerce->session->discount_saphali_next; unset($ni[$index]); $woocommerce->session->discount_saphali_next = $ni;}
								} elseif( !isset($no_next) ) $no_next = true;
								
								if( $no_next && $woocommerce->session->discount_saphali[$index] != $woocommerce->session->discount_saphali_next[$index] && isset($woocommerce->session->discount_saphali_next[$index]) && !(isset($woocommerce->session->discount_saphali_hide_next) && $woocommerce->session->discount_saphali_hide_next)  ) {
									if ( strstr( $woocommerce->session->discount_saphali_next[$index], '%' ) )  $_discount_str_ = $woocommerce->session->discount_saphali_next[$index];
										 else  $_discount_str_ = $this->wc_price($woocommerce->session->discount_saphali_next[$index]);
									$woocommerce->session->discount_saphali_next_return = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
									$this->comp_woocomerce_mess( $woocommerce->session->discount_saphali_next_return . '<span class="close" title="Закрыть "> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
									
								}
								if($this->info_cart_checkout_all == 'yes')
								$woocommerce->session->global_discount_saphali = $_discount_str;
								else {
									if(isset($woocommerce->session->global_discount_saphali) ) unset($woocommerce->session->global_discount_saphali);
								}
							}

						} elseif( $this->info_cart_checkout == 'yes' ) {
							foreach($woocommerce->cart->applied_coupons as $key => $_code) {
								if($code == $_code) $index = $key;
							}
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								if($_SESSION['discount_saphali'][$index] != $_SESSION['discount_saphali_next'][$index] && isset($_SESSION['discount_saphali_next'][$index]) && !(isset($_SESSION['discount_saphali_hide_next']) && $_SESSION['discount_saphali_hide_next'])  ) {
									if ( strstr( $_SESSION['discount_saphali_next'][$index], '%' ) )  $_discount_str_ = $_SESSION['discount_saphali_next'][$index];
										 else  $_discount_str_ = $this->wc_price($_SESSION['discount_saphali_next'][$index]);
									$_SESSION['discount_saphali_next_return'] = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
									$this->comp_woocomerce_mess( $_SESSION['discount_saphali_next_return'] . '<span class="close" title="Закрыть "> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
									
								}		
							} else {
								while( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
									$numb_ind++;
								}
								if( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && !isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
									$no_next = false;
									$woocommerce->session->discount_saphali_next_return = '';
									if( isset( $woocommerce->session->discount_saphali_next[$index] ) ) {$ni = $woocommerce->session->discount_saphali_next; unset($ni[$index]); $woocommerce->session->discount_saphali_next = $ni;}
								} elseif( !isset($no_next) ) $no_next = true;
								
								if( $no_next && $woocommerce->session->discount_saphali[$index] != $woocommerce->session->discount_saphali_next[$index] && isset($woocommerce->session->discount_saphali_next[$index]) && !(isset($woocommerce->session->discount_saphali_hide_next) && $woocommerce->session->discount_saphali_hide_next)  ) {
									if ( strstr( $woocommerce->session->discount_saphali_next[$index], '%' ) )  $_discount_str_ = $woocommerce->session->discount_saphali_next[$index];
										 else  $_discount_str_ = $this->wc_price($woocommerce->session->discount_saphali_next[$index]);
									$woocommerce->session->discount_saphali_next_return = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k])  ) );
									$this->comp_woocomerce_mess( $woocommerce->session->discount_saphali_next_return . '<span class="close" title="Закрыть "> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
								}
							}
						}

					}
				}
			} elseif(empty($discount)) { //$discount
				foreach($coupon_code as $_k => $code) {
					if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
						if(is_array($woocommerce->cart->applied_coupons))
						foreach($woocommerce->cart->applied_coupons as $key => $_code) {
							if($code == $_code) {
								$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка (%s) по текущему заказу аннулирована', 'saphali-discount' ), $_SESSION['discount_saphali'][$key] ) );
								unset($_SESSION['discount_saphali'][$key]); 
								unset($_SESSION['discount_saphali_next'][$key]); 
								unset($woocommerce->cart->applied_coupons[$key]);
								if(isset($woocommerce->session->global_discount_saphali) ) unset($woocommerce->session->global_discount_saphali);
							}
						}
						$_SESSION['coupons'] = $woocommerce->cart->applied_coupons;
					} else {
						if(is_array($woocommerce->cart->applied_coupons))
						foreach($woocommerce->cart->applied_coupons as $key => $_code) {
							if($code == $_code) {
								$this->comp_woocomerce_mess(  sprintf(__( 'Ваша накопительная скидка (%s) по текущему заказу аннулирована', 'saphali-discount' ) , $woocommerce->session->discount_saphali[$key] )  );
								if(empty($woocommerce->session->discount_saphali)) $woocommerce->session->discount_saphali = array();
								if(empty($woocommerce->session->discount_saphali_next)) $woocommerce->session->discount_saphali_next = array();
								$session = $woocommerce->session->discount_saphali;
								$session_next = $woocommerce->session->discount_saphali_next;
								unset($session[$key], $session_next[$key]);
								$woocommerce->session->discount_saphali = $session;
								$woocommerce->session->discount_saphali_next = $session_next;
								unset($woocommerce->cart->applied_coupons[$key]);
								if(isset($woocommerce->session->global_discount_saphali) ) unset($woocommerce->session->global_discount_saphali);
							}
						}
						$woocommerce->session->coupon_codes = $woocommerce->cart->applied_coupons;
					}
				}
			}
			
			if( !isset($woocommerce->session->discount_saphali_next[$_k]) )   $woocommerce->session->discount_saphali_next_return = '';
		}
		
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) $woocommerce->clear_messages();
		else
		wc_clear_notices();
	}

		return $product_id;  
	}
	function shortcode($atts, $content = null) {
		return do_shortcode($this->saphali_discount_shortcode());
	}
	function saphali_discount_shortcode() {
		return $this->user_cart_apry_discount();
	}
	function woocommerce_custom_coupon_columns( $column ) {
		global $post;
		switch ( $column ) {
			case "amount" :
			$discount_type = get_post_meta( $post->ID, 'discount_type', true );
			if( $discount_type == "fixed_total_shop" || $discount_type == "fixed_total_cart" ) {
				$variant_discount = get_post_meta( $post->ID, 'variant_discount', true );
				echo '<div class="amount">';
				foreach($variant_discount['min'] as $key => $discount) 
				echo sprintf(__("от %s до %s &ndash; %s", 'saphali-discount' ), $discount, $variant_discount['max'][$key], $variant_discount['discount'][$key]) . '<br />';
				echo '</div>';
			}
			break;
		}
	}
	function woocommerce_apply_with_individual_use_coupon( $seting , $the_coupon, $existing_coupon, $applied_coupons ) {
		global $woocommerce;
		$type = method_exists($existing_coupon, 'get_discount_type') ? $existing_coupon->get_discount_type() : $existing_coupon->type;
		switch ( $type ) {

			case "fixed_total_shop" :
				$_type = method_exists($the_coupon, 'get_discount_type') ? $the_coupon->get_discount_type() : $the_coupon->type;
				if($_type == "fixed_total_cart") {
				$code = method_exists($existing_coupon, 'get_code') ? $existing_coupon->get_code() : $existing_coupon->code;
				$add_message = __( 'Накопительная скидка по текущему заказу отключена, т.к. учитывается накопительна скидка по всем заказам.', 'saphali-discount' );
				$return = false;
				$this->messege_code = array($code => $add_message) + $this->messege_code;
				add_filter("woocommerce_add_message", array($this,"woocommerce_add_message"), 10 , 1);
				} else $return = false;
				
			break;
			case "fixed_total_cart" :
				$_type = method_exists($the_coupon, 'get_discount_type') ? $the_coupon->get_discount_type() : $the_coupon->type;
				if($_type == "fixed_total_shop") {
					if($the_coupon->individual_use == "no") {
						$add_message = __( 'Накопительная скидка по по всем заказам отключена, т.к. учитывается накопительна скидка по текущему заказу.', 'saphali-discount' );
						$return = false;
						$code = method_exists($existing_coupon, 'get_code') ? $existing_coupon->get_code() : $existing_coupon->code;
						$this->messege_code = array($code => $add_message) + $this->messege_code;
						add_filter("woocommerce_add_message", array($this,"woocommerce_add_message"), 10 , 1);
					}
				} else $return = false;
				
			break;
			default: break;
		}
		return $return;
	}
	function woocommerce_add_message($mess) {
		
		foreach($this->messege_code as $key => $value) {
			if($mess == sprintf( __( 'Sorry, coupon <code>%s</code> has already been applied and cannot be used in conjunction with other coupons.', 'woocommerce' ), $key )) {
				$mess = $value;
			}
			if($mess == __( 'Coupon code applied successfully.', 'woocommerce' ) && $this->messege_count != $key ) {//WC > = 2.0
				$mess = $value;$this->messege_count = $key;
			}elseif($mess == __( 'Discount code applied successfully.', 'woocommerce' && $this->messege_count != $key) ) { //WC < 2.0
				$mess = $value;$this->messege_count = $key;
			}
		}
		return $mess;
	}
	function after_tax_fixed_total_shop($coupon) {//woocommerce_cart_discount_after_tax_fixed_total_shop

		global $woocommerce;
		
		if ( !$this->apply_before_tax($coupon) && $this->is_valid($coupon) ) {
			$type = method_exists($coupon, 'get_discount_type') ? $coupon->get_discount_type() : $coupon->type;
			switch ( $type ) {
				case "fixed_total_shop" :
				$_code = method_exists($coupon, 'get_code') ? $coupon->get_code() : $coupon->code;
				if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
					foreach($woocommerce->cart->applied_coupons as $key => $code){
						if($code == $_code)
						 $_amount = $_SESSION['discount_saphali_shop'][$key];
					}
				} else {
					foreach($woocommerce->cart->applied_coupons as $key => $code){
						if($code == $_code)
						 $_amount = $woocommerce->session->discount_saphali_shop[$key];
					}
				}
				$c_id = method_exists($coupon, 'get_id') ? $coupon->get_id() : $coupon->id;
				$exclude_revers_items_product = (get_post_meta( $c_id, 'exclude_revers_items_product', true ) == 'yes') ? true : false;	
				if($exclude_revers_items_product) {
					if ( strstr( $_amount, '%' ) ) {
							$exclude_product_ids = method_exists($coupon, 'get_excluded_product_ids') ? $coupon->get_excluded_product_ids() : $coupon->exclude_product_ids;
							$exclude_product_categories = method_exists($coupon, 'get_excluded_product_categories') ? $coupon->get_excluded_product_categories() : $coupon->exclude_product_categories;
							$exclude_sale_items = method_exists($coupon, 'get_exclude_sale_items') ? $coupon->get_exclude_sale_items() : $coupon->exclude_sale_items;
							if ( sizeof( $exclude_product_ids ) > 0 || sizeof( $exclude_product_categories ) > 0 || $exclude_sale_items == 'yes' )  {
								if($exclude_sale_items == 'yes') 
									$product_ids_on_sale = woocommerce_get_product_ids_on_sale();
								if(is_null($product_ids_on_sale)) $product_ids_on_sale = array();
								$i_on_s = array_search(0, $product_ids_on_sale);
								if($i_on_s !== false) unset($product_ids_on_sale[$i_on_s]);
								if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
									foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
										$ex = 1;
										if(	sizeof( $exclude_product_categories ) > 0 ) {
											$product_cats = wp_get_post_terms( $cart_item['product_id'], 'product_cat', array( "fields" => "ids" ) );
											if ( sizeof( array_intersect( $product_cats, $exclude_product_categories ) ) > 0 ) {
												if( isset( $line_tax [$cart_item['product_id']] ) ) unset( $line_tax [$cart_item['product_id']] );
												if( isset( $line_total [$cart_item['product_id']] ) ) unset( $line_total [$cart_item['product_id']] );
												$ex = 0;
											} else{
												if(	sizeof( $exclude_product_ids ) > 0 ) {
													if ( in_array( $cart_item['product_id'], $exclude_product_ids ) || in_array( $cart_item['variation_id'], $exclude_product_ids ) || in_array( $cart_item['data']->get_parent(), $exclude_product_ids ) ) {
														if( isset( $line_tax [$cart_item['product_id']] ) ) unset( $line_tax [$cart_item['product_id']] );
														if( isset( $line_total [$cart_item['product_id']] ) ) unset( $line_total [$cart_item['product_id']] );
														$ex = 0;
													} else {
														$line_tax [$cart_item['product_id']]= $cart_item['line_tax'];
														$line_total [$cart_item['product_id']]= $cart_item['line_total'];
													}
												} else {
													$line_tax [$cart_item['product_id']]= $cart_item['line_tax'];
													$line_total [$cart_item['product_id']]= $cart_item['line_total'];
												}
											}
										} else {
											if(	sizeof( $exclude_product_ids ) > 0 ) {
												if ( in_array( $cart_item['product_id'], $exclude_product_ids ) || in_array( $cart_item['variation_id'], $exclude_product_ids ) || in_array( $cart_item['data']->get_parent(), $exclude_product_ids ) ) {
												if( isset( $line_tax [$cart_item['product_id']] ) ) unset( $line_tax [$cart_item['product_id']] );
												if( isset( $line_total [$cart_item['product_id']] ) ) unset( $line_total [$cart_item['product_id']] );
												$ex = 0;
											} else {
												$line_tax [$cart_item['product_id']]= $cart_item['line_tax'];
												$line_total [$cart_item['product_id']]= $cart_item['line_total'];
											}
											} else {
												$line_tax [$cart_item['product_id']]= $cart_item['line_tax'];
												$line_total [$cart_item['product_id']]= $cart_item['line_total'];
											}
										}
										if($ex && !(isset($cart_item['variation_id']) && $cart_item['variation_id']>0) && in_array( $cart_item['product_id'], $product_ids_on_sale, true ) || in_array( $cart_item['variation_id'], $product_ids_on_sale, true ) || in_array( $cart_item['data']->get_parent(), $product_ids_on_sale, true ) )  {
												if( isset( $line_tax [$cart_item['product_id']] ) ) unset( $line_tax [$cart_item['product_id']] );
												if( isset( $line_total [$cart_item['product_id']] ) ) unset( $line_total [$cart_item['product_id']] );
											}
									}
								}
							}
							if(isset($line_tax) && is_array($line_tax)) {
								$cart_contents_total = 0;
								foreach($line_tax as $key => $val) {
									$cart_contents_total += $line_total[$key];
									$tax_total += $val;
								}
							} else {
								$cart_contents_total = $woocommerce->cart->subtotal;
								$tax_total = $woocommerce->cart->tax_total;							
							}
						
						$percent_discount = ( round( $cart_contents_total + $tax_total, $woocommerce->cart->dp ) / 100 ) * str_replace(array('%',','),array('','.'),$_amount);
						$woocommerce->cart->discount_total = $woocommerce->cart->discount_total + round( $percent_discount, $woocommerce->cart->dp );
						$woocommerce->cart->coupon_discount_amounts[ $_code ] = round( $percent_discount, $woocommerce->cart->dp );
						
					} else {
						$valid_for_cart = true;
							$product_categories = method_exists($coupon, 'get_product_categories') ? $coupon->get_product_categories() : $coupon->product_categories;
							if ( sizeof( $product_categories ) > 0 ) {
								$valid_for_cart = false;
								if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
									foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {

										$product_cats = wp_get_post_terms($cart_item['product_id'], 'product_cat', array("fields" => "ids"));

										if ( sizeof( array_intersect( $product_cats, $product_categories ) ) > 0 ) {
												$valid_for_cart = true; break;
											}
									}
								}
							}elseif ( $valid_for_cart && sizeof( $coupon->exclude_product_categories ) > 0) {
								$valid_for_cart = false;
								if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
									foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
										
										
										$product_cats = wp_get_post_terms( $cart_item['product_id'], 'product_cat', array( "fields" => "ids" ) );
										if ( sizeof( array_intersect( $product_cats, $coupon->exclude_product_categories ) ) > 0 )
											{ $valid_for_cart = false; } else {$valid_for_cart = true; break;}
									}
								}
							}
						$exclude_product_ids = method_exists($coupon, 'get_excluded_product_ids') ? $coupon->get_excluded_product_ids() : $coupon->exclude_product_ids;
						if ( $valid_for_cart && sizeof( $exclude_product_ids ) > 0 ) {
							if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
								foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
									if ( in_array( $cart_item['product_id'], $exclude_product_ids ) || in_array( $cart_item['variation_id'], $exclude_product_ids ) || in_array( $cart_item['data']->get_parent(), $exclude_product_ids ) ) {
										$valid_for_cart = false;
									} else {$valid_for_cart = true; break;}
								}
							}
						}
						
						if($valid_for_cart) {
							$woocommerce->cart->discount_total = $woocommerce->cart->discount_total + $_amount;
							$woocommerce->cart->coupon_discount_amounts[ $_code ] = $_amount;
						}
					}
				} else {
					if ( strstr( $_amount, '%' ) ) {
						$percent_discount = ( round( $woocommerce->cart->subtotal + $woocommerce->cart->tax_total, $woocommerce->cart->dp ) / 100 ) * $_amount;
						$woocommerce->cart->discount_total = $woocommerce->cart->discount_total + round( $percent_discount, $woocommerce->cart->dp );
						$woocommerce->cart->coupon_discount_amounts[ $_code ] = round( $percent_discount, $woocommerce->cart->dp );
						
					} else {
						$woocommerce->cart->discount_total = $woocommerce->cart->discount_total + $_amount;
						$woocommerce->cart->coupon_discount_amounts[ $_code ] = $_amount;
					}
				}
				break;
				default: break;
			}
		}
		
	}
	public function apply_before_tax($coupon) {
		if ( version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) )
			return $coupon->apply_before_tax();
		return true;
	}
	function after_tax_fixed_total_cart($coupon) {//woocommerce_cart_discount_after_tax_fixed_total_cart
		global $woocommerce;
		
		if ( !$this->apply_before_tax($coupon) && $this->is_valid($coupon) ) {
			
			switch ( $coupon->type ) {

				case "fixed_total_cart" :
				
				if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
					foreach($woocommerce->cart->applied_coupons as $key => $code){
						if($code == $coupon->code)
						 $_amount = $_SESSION['discount_saphali'][$key];
					}
						 
				} else {
					foreach($woocommerce->cart->applied_coupons as $key => $code){
						if($code == $coupon->code) {
							$_amount = $woocommerce->session->discount_saphali[$key];
						}
						 
					}
				}
				if ( strstr( $_amount, '%' ) ) {
					$exclude_revers_items_product = (get_post_meta( $coupon->id, 'exclude_revers_items_product', true ) == 'yes') ? true : false;
					
					if($exclude_revers_items_product) {
						if ( sizeof( $this->exclude_product_ids($coupon) ) > 0 || sizeof( $coupon->exclude_product_categories ) > 0 || $coupon->exclude_sale_items == 'yes' )  {
							if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
								foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
									$on_sale_rem = true;
									if(	sizeof( $coupon->exclude_product_categories ) > 0 ) {
										$product_cats = wp_get_post_terms( $cart_item['product_id'], 'product_cat', array( "fields" => "ids" ) );
										if ( sizeof( array_intersect( $product_cats, $coupon->exclude_product_categories ) ) > 0 ) {
											if( isset( $line_tax [$cart_item['product_id']] ) ) unset( $line_tax [$cart_item['product_id']] );
											if( isset( $line_total [$cart_item['product_id']] ) ) unset( $line_total [$cart_item['product_id']] );
											$on_sale_rem = false;
										} else{
											if(	sizeof( $this->exclude_product_ids($coupon) ) > 0 ) {
												if ( in_array( $cart_item['product_id'], $this->exclude_product_ids($coupon) ) || in_array( $cart_item['variation_id'], $this->exclude_product_ids($coupon) ) || in_array( $cart_item['data']->get_parent(), $this->exclude_product_ids($coupon) ) ) {
													if( isset( $line_tax [$cart_item['product_id']] ) ) unset( $line_tax [$cart_item['product_id']] );
													if( isset( $line_total [$cart_item['product_id']] ) ) unset( $line_total [$cart_item['product_id']] );
													$on_sale_rem = false;
												} else {
													$line_tax [$cart_item['product_id']]= $cart_item['line_tax'];
													$line_total [$cart_item['product_id']]= $cart_item['line_total'];
												}
											} else {
												$line_tax [$cart_item['product_id']]= $cart_item['line_tax'];
												$line_total [$cart_item['product_id']]= $cart_item['line_total'];
											}
										}
									} else {
										if(	sizeof( $this->exclude_product_ids($coupon) ) > 0 ) {
											if ( in_array( $cart_item['product_id'], $this->exclude_product_ids($coupon) ) || in_array( $cart_item['variation_id'], $this->exclude_product_ids($coupon) ) || in_array( $cart_item['data']->get_parent(), $this->exclude_product_ids($coupon) ) ) {
											if( isset( $line_tax [$cart_item['product_id']] ) ) unset( $line_tax [$cart_item['product_id']] );
											if( isset( $line_total [$cart_item['product_id']] ) ) unset( $line_total [$cart_item['product_id']] );
											$on_sale_rem = false;
										} else {
											$line_tax [$cart_item['product_id']]= $cart_item['line_tax'];
											$line_total [$cart_item['product_id']]= $cart_item['line_total'];
										}
										} else {
											$line_tax [$cart_item['product_id']]= $cart_item['line_tax'];
											$line_total [$cart_item['product_id']]= $cart_item['line_total'];
									}
								}
									if( $on_sale_rem && ( $coupon->exclude_sale_items == 'yes' && $cart_item['data']->is_on_sale() ) ) {
										if( isset( $line_tax [$cart_item['product_id']] ) ) unset( $line_tax [$cart_item['product_id']] );
										if( isset( $line_total [$cart_item['product_id']] ) ) unset( $line_total [$cart_item['product_id']] );
									} else {
										$line_tax [$cart_item['product_id']]= $cart_item['line_tax'];
										$line_total [$cart_item['product_id']]= $cart_item['line_total'];
									}
								}
							}
						}
					}
					
					if(isset($line_tax) && is_array($line_tax)) {
						$cart_contents_total = 0;
						foreach($line_tax as $key => $val) {
							$cart_contents_total += $line_total[$key];
							$tax_total += $val;
						}
					} else {
						$cart_contents_total = $woocommerce->cart->subtotal;
						$tax_total = $woocommerce->cart->tax_total;							
					}					
					$percent_discount = ( round( $cart_contents_total + $tax_total, $woocommerce->cart->dp ) / 100 ) * str_replace(array('%',','),array('','.'),$_amount);
					$woocommerce->cart->discount_total = $woocommerce->cart->discount_total + round( $percent_discount, $woocommerce->cart->dp );
					$woocommerce->cart->coupon_discount_amounts[ $coupon->code ] = round( $percent_discount, $woocommerce->cart->dp );
                    
				} else {
					$exclude_revers_items_product = (get_post_meta( $coupon->id, 'exclude_revers_items_product', true ) == 'yes') ? true : false;
					$valid_for_cart = true;
					if($exclude_revers_items_product) {
						if ( sizeof( $this->get_product_categories($coupon) ) > 0 ) {
							$valid_for_cart = false;
							if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
								foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {

									$product_cats = wp_get_post_terms($cart_item['product_id'], 'product_cat', array("fields" => "ids"));

									if ( sizeof( array_intersect( $product_cats, $this->get_product_categories($coupon) ) ) > 0 ) {
											$valid_for_cart = true; break;
										}
								}
							}
						}
						if ( $valid_for_cart && sizeof( $coupon->exclude_product_categories ) > 0) {
							$valid_for_cart = false;
							if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
								foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
									$product_cats = wp_get_post_terms( $cart_item['product_id'], 'product_cat', array( "fields" => "ids" ) );
									if ( sizeof( array_intersect( $product_cats, $coupon->exclude_product_categories ) ) > 0 )
										{ $valid_for_cart = false; } else {$valid_for_cart = true; break;}
								}
							}
						}
						if ( $valid_for_cart && sizeof( $this->exclude_product_ids($coupon) ) > 0 ) {
							if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
								foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
									if ( in_array( $cart_item['product_id'], $this->exclude_product_ids($coupon) ) || in_array( $cart_item['variation_id'], $this->exclude_product_ids($coupon) ) || in_array( $cart_item['data']->get_parent(), $this->exclude_product_ids($coupon) ) ) {
										$valid_for_cart = false;
									} else {$valid_for_cart = true; break;}
								}
							}
						}
					}
					if($valid_for_cart) {
						$woocommerce->cart->discount_total = $woocommerce->cart->discount_total + $_amount;
						$woocommerce->cart->coupon_discount_amounts[ $coupon->code ] = $_amount;
					} 
                }

				break;
				default: break;
			}
		}
	}
	function get_product_categories($coupon) {
		if ( version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) 
			return $coupon->product_categories;
		else
			return $coupon->get_product_categories();
	}
	function exclude_product_ids($coupon) {
		if ( version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) 
			return $coupon->exclude_product_ids;
		else
			return method_exists($coupon, 'get_excluded_product_ids') ? $coupon->get_excluded_product_ids() : $coupon->exclude_product_ids;
	}
	function before_tax_fixed_total_shop( $price, $values, $cart) {//woocommerce_get_discounted_price
		global $woocommerce;
		if ( ! $price ) return $price;
		if ( ! empty( $cart->applied_coupons ) ) { 
			foreach ( $cart->applied_coupons as $code ) {
				$coupon = new WC_Coupon( $code );
				$_amount = 0;
				$type = method_exists($coupon, 'get_discount_type') ? $coupon->get_discount_type() : $coupon->type;
				$c_code = method_exists($coupon, 'get_code') ? $coupon->get_code() : $coupon->code;
				if(! in_array($type, array("fixed_total_shop","fixed_total_cart" ))) continue;
				if( $type == "fixed_total_shop" ) {
					if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
						foreach($cart->applied_coupons as $key => $_code){
							if($_code == $c_code) {
								 $_amount = $_SESSION['discount_saphali_shop'][$key];
								 $code = $_code;
							 }
						}
					} else {
						foreach($cart->applied_coupons as $key => $_code){
							if($_code == $c_code) {
							 if( isset($woocommerce->session->discount_saphali_shop[$key]) )
							 $_amount = $woocommerce->session->discount_saphali_shop[$key];
							 $code = $_code;
							 }
						}
					}				
				} else {
					if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
						foreach($cart->applied_coupons as $key => $_code){
							if($_code == $c_code) {
								 $_amount = $_SESSION['discount_saphali'][$key];
								 $code = $_code;
							 }
						}
					} else {
						foreach($cart->applied_coupons as $key => $_code){
							if($_code == $c_code) {
							 $_amount = $woocommerce->session->discount_saphali[$key];
							 $code = $_code;
							 }
						}
					}				
				}
			//	var_dump($_amount, $coupon->type);
				if ( $this->apply_before_tax($coupon) && $this->is_valid($coupon) ) {
					$valid_for_cart = true;
					switch ( $type ) {
						case "fixed_total_shop" :
							$_id = method_exists($coupon, 'get_id') ? $coupon->get_id() : $coupon->id;
							$exclude_revers_items_product = (get_post_meta( $_id, 'exclude_revers_items_product', true ) == 'yes') ? true : false;
							
							if($exclude_revers_items_product) {
								$exclude_product_ids = method_exists($coupon, 'get_excluded_product_ids') ? $coupon->get_excluded_product_ids() : $coupon->exclude_product_ids;
								if ( sizeof( $this->get_product_categories($coupon) ) > 0 ) {

									$product_cats = wp_get_post_terms($values['product_id'], 'product_cat', array("fields" => "ids"));

									if ( sizeof( array_intersect( $product_cats, $this->get_product_categories($coupon) ) ) > 0 ) {
										$valid_for_cart = true;
									} else { $valid_for_cart = false; }
								}
								if(isset($_COOKIE['saphali'])) {
									var_dump($valid_for_cart, wp_get_post_terms($values['product_id'], 'product_cat', array("fields" => "ids")), $coupon->exclude_product_categories, __LINE__);
								}
								if ( $valid_for_cart && sizeof( $coupon->exclude_product_categories ) > 0 ) {

									$product_cats = wp_get_post_terms( $values['product_id'], 'product_cat', array( "fields" => "ids" ) );
									if ( sizeof( array_intersect( $product_cats, $coupon->exclude_product_categories ) ) > 0 )
									{ $valid_for_cart = false; } else {
										$valid_for_cart = true;
									}
								}
								
								if ( $valid_for_cart && sizeof( $this->exclude_product_ids($coupon) ) > 0 ) {
									if ( in_array( $values['product_id'], $this->exclude_product_ids($coupon) ) || in_array( $values['variation_id'], $this->exclude_product_ids($coupon) ) || in_array( $values['data']->get_parent(), $this->exclude_product_ids($coupon) ) ) {
										$valid_for_cart = false;
									} else {$valid_for_cart = true; }
								}
						
								if ( $valid_for_cart && $coupon->exclude_sale_items == 'yes' ) {
									if($coupon->exclude_sale_items == 'yes') 
									if( !isset($product_ids_on_sale) ) $product_ids_on_sale = woocommerce_get_product_ids_on_sale();
									if(is_null($product_ids_on_sale)) $product_ids_on_sale = array();
									$i_on_s = array_search(0, $product_ids_on_sale);
									if($i_on_s !== false) unset($product_ids_on_sale[$i_on_s]);
									if(  in_array( $values['product_id'], $product_ids_on_sale, true ) || in_array( $values['variation_id'], $product_ids_on_sale, true ) || in_array( $values['data']->get_parent(), $product_ids_on_sale, true ) ) {
										$valid_for_cart = false;
									} else {$valid_for_cart = true; }
								}
								
								if($valid_for_cart) {
									$get_exclude_sale_items = method_exists($coupon, 'get_exclude_sale_items') ? $coupon->get_exclude_sale_items() : $coupon->exclude_sale_items;
									if( !( $get_exclude_sale_items == 'yes' && $values['data']->is_on_sale() ) ) {
										if ( !strstr( $_amount, '%' ) ) {
											if ( $cart->subtotal_ex_tax ) {
												if(version_compare( WOOCOMMERCE_VERSION, '3.0', '<' )) $et = $values['data']->get_price_excluding_tax(); else $et = wc_get_price_excluding_tax( $values['data'] );
												$discount_percent = ( $et*$values['quantity'] ) / $cart->subtotal_ex_tax;
											} else 
												$discount_percent = 0;

											// Use pence to help prevent rounding errors
											$coupon_amount_pence = $_amount * 100;

											// Work out the discount for the row
											$item_discount = $coupon_amount_pence * $discount_percent;

											// Work out discount per item
											$item_discount = $item_discount / $values['quantity'];

											// Pence
											$price = ( $price * 100 );

											// Check if discount is more than price
											if ( $price < $item_discount )
												$discount_amount = $price;
											else
												$discount_amount = $item_discount;

											// Take discount off of price (in pence)
											$price = $price - $discount_amount;

											// Back to pounds
											$price = $price / 100;

											// Cannot be below 0
											if ( $price < 0 ) $price = 0;

											// Add coupon to discount total (once, since this is a fixed cart discount and we don't want rounding issues)
											$cart->discount_cart = $cart->discount_cart + ( ( $discount_amount * $values['quantity'] ) / 100 );
											$cart->coupon_discount_amounts[ $c_code ] = $cart->coupon_discount_amounts[ $c_code ] + ( ( $discount_amount * $values['quantity'] ) / 100 );
										} else {
											$__amount = (float) str_replace('%', '', $_amount);
											$percent_discount = round( ( $values['data']->get_price() * $values['quantity'] / 100 ) * $__amount, 2 );
											$cart->discount_cart = $cart->discount_cart + ( $percent_discount );
											$cart->coupon_discount_amounts[ $c_code ] = $cart->coupon_discount_amounts[ $c_code ] + ( $percent_discount  );
											if($price == $values['data']->get_price()) {
												$price = $price - round( ( $values['data']->get_price() / 100 ) * $__amount, 2 );
											} else {
												$price = $price - round( ( $values['data']->get_price() / 100 ) * ($__amount * $values['quantity']), 2 );
											}
											
											
										}
									}
								}
								
							} else {
								if ( !strstr( $_amount, '%' ) ) {
										if ( $cart->subtotal_ex_tax ) {
											if(version_compare( WOOCOMMERCE_VERSION, '3.0', '<' )) $et = $values['data']->get_price_excluding_tax(); else $et = wc_get_price_excluding_tax( $values['data'] );
											$discount_percent = ( $et*$values['quantity'] ) / $cart->subtotal_ex_tax;
										}
										else
											$discount_percent = 0;

										// Use pence to help prevent rounding errors
										$coupon_amount_pence = $_amount * 100;

										// Work out the discount for the row
										$item_discount = $coupon_amount_pence * $discount_percent;

										// Work out discount per item
										$item_discount = $item_discount / $values['quantity'];

										// Pence
										$price = ( $price * 100 );

										// Check if discount is more than price
										if ( $price < $item_discount )
											$discount_amount = $price;
										else
											$discount_amount = $item_discount;

										// Take discount off of price (in pence)
										$price = $price - $discount_amount;

										// Back to pounds
										$price = $price / 100;

										// Cannot be below 0
										if ( $price < 0 ) $price = 0;

										// Add coupon to discount total (once, since this is a fixed cart discount and we don't want rounding issues)
										$cart->discount_cart = $cart->discount_cart + ( ( $discount_amount * $values['quantity'] ) / 100 );
										if(isset($cart->coupon_discount_amounts[ $c_code ]))
										$cart->coupon_discount_amounts[ $c_code ] = $cart->coupon_discount_amounts[ $c_code ] + ( ( $discount_amount * $values['quantity'] ) / 100 );
										else
										$cart->coupon_discount_amounts[ $c_code ] = ( ( $discount_amount * $values['quantity'] ) / 100 );
										
								} else {
									$__amount = str_replace('%', '', $_amount);
									$percent_discount = round( ( $values['data']->get_price() * $values['quantity'] / 100 ) * $__amount, 2 );
									$cart->discount_cart = $cart->discount_cart + ( $percent_discount );
									if( isset($cart->coupon_discount_amounts[ $c_code ]) )
										$cart->coupon_discount_amounts[ $c_code ] = $cart->coupon_discount_amounts[ $c_code ] + ( $percent_discount  );
									else 
										$cart->coupon_discount_amounts[ $c_code ] = ( $percent_discount );
									if($price == $values['data']->get_price()) {
										$price = $price - round( ( $values['data']->get_price() / 100 ) * $__amount, 2 );
									} else {
										$price = $price - round( ( $values['data']->get_price() / 100 ) * $__amount * $values['quantity'], 2 );
									}
									
									if( isset($_GET['deb']) ) {
										var_dump($price, $values['quantity'], $_amount);
									}
								}
							}
							
						break;
						
						case "fixed_total_cart" :
							$_id = method_exists($coupon, 'get_id') ? $coupon->get_id() : $coupon->id;
							$exclude_revers_items_product = (get_post_meta( $_id, 'exclude_revers_items_product', true ) == 'yes') ? true : false;
							if($exclude_revers_items_product) {
								if ( sizeof( $this->get_product_categories($coupon) ) > 0 ) {

									$product_cats = wp_get_post_terms($values['product_id'], 'product_cat', array("fields" => "ids"));

									if ( sizeof( array_intersect( $product_cats, $this->get_product_categories($coupon) ) ) > 0 ) {
										$valid_for_cart = true;
									} else { $valid_for_cart = false; }
								}
								if ( $valid_for_cart && sizeof( $coupon->exclude_product_categories ) > 0 ) {

									$product_cats = wp_get_post_terms( $values['product_id'], 'product_cat', array( "fields" => "ids" ) );
									if ( sizeof( array_intersect( $product_cats, $coupon->exclude_product_categories ) ) > 0 )
									{ $valid_for_cart = false; } else {
										$valid_for_cart = true;
									}
								}
								if ( $valid_for_cart && sizeof( $this->exclude_product_ids($coupon) ) > 0 ) {
									if ( in_array( $values['product_id'], $this->exclude_product_ids($coupon) ) || in_array( $values['variation_id'], $this->exclude_product_ids($coupon) ) || in_array( $values['data']->get_parent(), $this->exclude_product_ids($coupon) ) ) {
										$valid_for_cart = false;
									} else {$valid_for_cart = true; }
								}
								
								if($valid_for_cart) {
                                    $get_exclude_sale_items = method_exists($coupon, 'get_exclude_sale_items') ? $coupon->get_exclude_sale_items() : $coupon->exclude_sale_items;
                                    $exclude_product_categories = method_exists($coupon, 'get_excluded_product_categories') ? $coupon->get_excluded_product_categories() : $coupon->exclude_product_categories;
                                    $no_apply = true;
                                    if(	sizeof( $exclude_product_categories ) > 0 ) {
                                        $product_cats = wp_get_post_terms( $values['product_id'], 'product_cat', array( "fields" => "ids" ) );
                                        if ( sizeof( array_intersect( $product_cats, $exclude_product_categories ) ) > 0 ) {
                                            $no_apply = false;
                                        }
                                    }
									if( !( $get_exclude_sale_items == 'yes' && $values['data']->is_on_sale() ) && $no_apply ) {
										if ( !strstr( $_amount, '%' ) ) {
											if ( $cart->subtotal_ex_tax ) {
												if(version_compare( WOOCOMMERCE_VERSION, '3.0', '<' )) $et = $values['data']->get_price_excluding_tax(); else $et = wc_get_price_excluding_tax( $values['data'] );
												$discount_percent = ( $et*$values['quantity'] ) / $cart->subtotal_ex_tax;
											}
											else
												$discount_percent = 0;

											// Use pence to help prevent rounding errors
											$coupon_amount_pence = $_amount * 100;

											// Work out the discount for the row
											$item_discount = $coupon_amount_pence * $discount_percent;

											// Work out discount per item
											$item_discount = $item_discount / $values['quantity'];

											// Pence
											$price = ( $price * 100 );

											// Check if discount is more than price
											if ( $price < $item_discount )
												$discount_amount = $price;
											else
												$discount_amount = $item_discount;

											// Take discount off of price (in pence)
											$price = $price - $discount_amount;

											// Back to pounds
											$price = $price / 100;

											// Cannot be below 0
											if ( $price < 0 ) $price = 0;

											// Add coupon to discount total (once, since this is a fixed cart discount and we don't want rounding issues)
											$cart->discount_cart = $cart->discount_cart + ( ( $discount_amount * $values['quantity'] ) / 100 );
											$cart->coupon_discount_amounts[ $code ] = $cart->coupon_discount_amounts[ $code ] + ( ( $discount_amount * $values['quantity'] ) / 100 );
										} else {
												$__amount = str_replace('%', '', $_amount);
												$percent_discount = round( ( $values['data']->get_price() * $values['quantity'] / 100 ) * $__amount, 2 );
												$cart->discount_cart = $cart->discount_cart + ( $percent_discount );
												$cart->coupon_discount_amounts[ $code ] = $cart->coupon_discount_amounts[ $code ] +( $percent_discount );
												
												$price = $price - $percent_discount;											
										}
									}
                                }
							} else {
								if ( !strstr( $_amount, '%' ) ) {
										if ( $cart->subtotal_ex_tax ) {
											if(version_compare( WOOCOMMERCE_VERSION, '3.0', '<' )) $et = $values['data']->get_price_excluding_tax(); else $et = wc_get_price_excluding_tax( $values['data'] );
											$discount_percent = ( $et*$values['quantity'] ) / $cart->subtotal_ex_tax;
										}											
										else
											$discount_percent = 0;

										// Use pence to help prevent rounding errors
										$coupon_amount_pence = $_amount * 100;

										// Work out the discount for the row
										$item_discount = $coupon_amount_pence * $discount_percent;

										// Work out discount per item
										$item_discount = $item_discount / $values['quantity'];

										// Pence
										$price = ( $price * 100 );

										// Check if discount is more than price
										if ( $price < $item_discount )
											$discount_amount = $price;
										else
											$discount_amount = $item_discount;

										// Take discount off of price (in pence)
										$price = $price - $discount_amount;

										// Back to pounds
										$price = $price / 100;

										// Cannot be below 0
										if ( $price < 0 ) $price = 0;

										// Add coupon to discount total (once, since this is a fixed cart discount and we don't want rounding issues)
										$cart->discount_cart = $cart->discount_cart + ( ( $discount_amount * $values['quantity'] ) / 100 );
										$cart->coupon_discount_amounts[ $code ] = $cart->coupon_discount_amounts[ $code ] + ( ( $discount_amount * $values['quantity'] ) / 100 );
								} else {
									$__amount = str_replace('%', '', $_amount);
									$percent_discount = round( ( $values['data']->get_price() * $values['quantity'] / 100 ) * $__amount, 2 );
									$cart->discount_cart = $cart->discount_cart + ( $percent_discount  );
									$cart->coupon_discount_amounts[ $code ] = $cart->coupon_discount_amounts[ $code ] + $percent_discount ;
									$price = $price - $percent_discount;
								}
                            }
							break;
							default: break;
					}
				}
			}
		}
		return $price;
	}

	function save_order_items() {
		// woocommerce_calc_line_taxes
		if( !( isset($_POST['action']) && ($_POST['action'] == 'woocommerce_save_order_items' || $_POST['action'] == 'woocommerce_calc_line_taxes') ) ) return;
		$items = array();
			parse_str( $_POST['items'], $items );
		
		//$items["customer_user_ajax"];
		if(!isset($items["customer_user_ajax"]) && isset($_POST['order_id'])) {
			$items["customer_user_ajax"] = get_post_meta($_POST['order_id'], '_customer_user', true);
		}
		$items["line_subtotal"] = $items["line_subtotal"];
		$items["line_total"]    = $items["line_total"];
		
		$p = $_POST['items'];
		$_items = $this->ffs ( $items );
		$_POST['items'] = http_build_query( $_items, '', '&' );
	}
	function ffs ($items) {
		$customer_user_ajax = isset( $items["customer_user_ajax"] ) ? $items["customer_user_ajax"]: 0;
		$coupons = get_posts(array('post_type' => 'shop_coupon', 'post_status' => 'publish', 'meta_key' => 'discount_type', 'meta_value' => 'fixed_total_shop', 'posts_per_page' => -1));
		$__order_total = 0;
		if( isset( $items['line_subtotal'] ) )
		foreach ( $items['line_subtotal'] as $item_id )
			$__order_total     += wc_format_decimal(  $item_id  );
		$current_user = wp_get_current_user();
		$check_emails = array();
		$check_emails[] = $current_user->user_email;
		$check_emails[] = get_user_meta($current_user->ID, 'billing_email', true);
		$check_emails = array_unique($check_emails);	
		foreach($coupons as $_coupon) {
			
			$customer_email = get_post_meta( $_coupon->ID, 'customer_email', true );
			$customer_cart_club = get_post_meta( $_coupon->ID, 'customer_cart_club', true );
			if ( is_array( $customer_email ) && 0 < count( $customer_email ) && ! $this->is_coupon_emails_allowed( $check_emails, $customer_email ) ) {
				
			} else {
			$coupon_code[] = $_coupon->post_title;
			$variant_discount[] = get_post_meta( $_coupon->ID, 'variant_discount', true );
			}
		}

		if(!empty($coupon_code)) {
			//fix wpml
			$coupon_code = array_unique($coupon_code);
			$add_coupon = false;
			add_filter( 'posts_where', array($this, 'filter_where') );
			add_action( 'parse_query', array( $this, 'parse_query' ), 5 );
			if ( $customer_user_ajax ) {
				$current_user = get_user_by( 'id', $customer_user_ajax ); 
				$add_coupon = true;
				if(!empty($customer_email)) {
					$add_coupon = false;
				} elseif(!empty($customer_cart_club)) {
					$add_coupon = false;
				}
				
				if(is_array($check_emails)) {
					foreach($check_emails as $user_email) {
						if(!empty($customer_email)) {
							if(!empty($user_email))
							if(in_array( $user_email, $customer_email ))
							{
								$add_coupon = true;
								if( ! version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) )
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => array( 'wc-completed', 'wc-processing' ), 'posts_per_page' => -1,   
									
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
								else
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => 'publish', 'posts_per_page' => -1,   
									'tax_query' => array(
									   array(
											'taxonomy' => 'shop_order_status',
											'field' => 'slug',
											'terms' => array( 'completed', 'processing' )
										)
								   ),
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
							$_order_shipping = $_order_discount = $_order_total = 0;
							if( $orders->have_posts() ) {
								while ( $orders->have_posts() ) {
									$orders->the_post();
									$_order = $orders->post;
									
									$order_currency = get_post_meta( $_order->ID, '_order_currency', true );
									$rate_def = $this->compatibility_currency($order_currency);
									if( $rate_def != 1 ) {
										$amount = get_post_meta( $_order->ID, '_order_total', true );
										$_order_shipping = $_order_shipping + number_format( get_post_meta( $_order->ID, '_order_shipping', true )  * $rate_def, 2, '.', '');
										$price_order = number_format(  $amount * $rate_def, 2, '.', '');
									} else {
										if(get_post_meta( $_order->ID, '_order_total_base_currency', true ) ) {
											$price_order = get_post_meta( $_order->ID, '_order_total_base_currency', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping_base_currency', true );
										} else {
											$price_order = get_post_meta( $_order->ID, '_order_total', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping', true );
										}
									}
										
									
									//$_order_tax = $_order_tax + get_post_meta( $_order->ID, '_order_tax', true );
									$_order_total = $_order_total + $price_order;							
								}
							}
							$total = $_order_total - $_order_shipping;
								break;
							}
						} else {
							if(!empty($user_email))
							if( ! version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) )
							$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => array( 'wc-completed', 'wc-processing'), 'posts_per_page' => -1, 
									
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
							));
							else
							$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => 'publish', 'posts_per_page' => -1, 
									'tax_query' => array(
									   array(
											'taxonomy' => 'shop_order_status',
											'field' => 'slug',
											'terms' => array( 'completed', 'processing' )
										)
								   ),
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
							));
							$_order_shipping = $_order_discount = $_order_total = 0;
							if( $orders->have_posts() ) {
								while ( $orders->have_posts() ) {
									$orders->the_post();
									$_order = $orders->post;
									
									$order_currency = get_post_meta( $_order->ID, '_order_currency', true );
									$rate_def = $this->compatibility_currency($order_currency);
									if( $rate_def != 1 ) {
										$amount = get_post_meta( $_order->ID, '_order_total', true );
										$_order_shipping = $_order_shipping + number_format( get_post_meta( $_order->ID, '_order_shipping', true )  * $rate_def, 2, '.', '');
										$price_order = number_format(  $amount * $rate_def, 2, '.', '');
									} else {
										if(get_post_meta( $_order->ID, '_order_total_base_currency', true ) ) {
											$price_order = get_post_meta( $_order->ID, '_order_total_base_currency', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping_base_currency', true );
										} else {
											$price_order = get_post_meta( $_order->ID, '_order_total', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping', true );
										}
									}
										
									
									//$_order_tax = $_order_tax + get_post_meta( $_order->ID, '_order_tax', true );
									$_order_total = $_order_total + $price_order;							
								}
							}
							$total = $_order_total - $_order_shipping;
						}
					}
				}
				if(!$add_coupon) {
					$check_ps[] = get_user_meta($current_user->ID, 'billing_cart_club', true);
					$check_ps = array_unique($check_ps);
					
					if(is_array($check_ps)) {
						foreach($check_ps as $user_p) {
							if(!empty($customer_cart_club)) {
								if(!empty($user_p))
								if(in_array( $user_p, $customer_cart_club ))
								{
									$add_coupon = true;
									if( ! version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) 
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => array( 'wc-processing', 'wc-completed' ), 'posts_per_page' => -1,   
									
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
								else
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => 'publish', 'posts_per_page' => -1,   
									'tax_query' => array(
									   array(
											'taxonomy' => 'shop_order_status',
											'field' => 'slug',
											'terms' => array( 'completed', 'processing' )
										)
								   ),
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
							$_order_shipping = $_order_discount = $_order_total = 0;
							if( $orders->have_posts() ) {
								while ( $orders->have_posts() ) {
									$orders->the_post();
									$_order = $orders->post;
									
									$order_currency = get_post_meta( $_order->ID, '_order_currency', true );
									$rate_def = $this->compatibility_currency($order_currency);
									if( $rate_def != 1 ) {
										$amount = get_post_meta( $_order->ID, '_order_total', true );
										$_order_shipping = $_order_shipping + number_format( get_post_meta( $_order->ID, '_order_shipping', true )  * $rate_def, 2, '.', '');
										$price_order = number_format(  $amount * $rate_def, 2, '.', '');
									} else {
										if(get_post_meta( $_order->ID, '_order_total_base_currency', true ) ) {
											$price_order = get_post_meta( $_order->ID, '_order_total_base_currency', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping_base_currency', true );
										} else {
											$price_order = get_post_meta( $_order->ID, '_order_total', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping', true );
										}
									}
										
									
									//$_order_tax = $_order_tax + get_post_meta( $_order->ID, '_order_tax', true );
									$_order_total = $_order_total + $price_order;							
								}
							}
							$total = $_order_total - $_order_shipping;
									break;
								}
								$b = 0;
								foreach($customer_cart_club as $phone) {
									if( strpos( $phone, str_replace('+', '', $user_p) ) !== false && mb_strlen($user_p, 'utf-8') > 7) {
										$add_coupon = true;
										$b = 1; 
										if( ! version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) 
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => array( 'wc-processing', 'wc-completed' ), 'posts_per_page' => -1,   
									
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
								else
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => 'publish', 'posts_per_page' => -1,   
									'tax_query' => array(
									   array(
											'taxonomy' => 'shop_order_status',
											'field' => 'slug',
											'terms' => array( 'completed', 'processing' )
										)
								   ),
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
							$_order_shipping = $_order_discount = $_order_total = 0;
							if( $orders->have_posts() ) {
								while ( $orders->have_posts() ) {
									$orders->the_post();
									$_order = $orders->post;
									
									$order_currency = get_post_meta( $_order->ID, '_order_currency', true );
									$rate_def = $this->compatibility_currency($order_currency);
									if( $rate_def != 1 ) {
										$amount = get_post_meta( $_order->ID, '_order_total', true );
										$_order_shipping = $_order_shipping + number_format( get_post_meta( $_order->ID, '_order_shipping', true )  * $rate_def, 2, '.', '');
										$price_order = number_format(  $amount * $rate_def, 2, '.', '');
									} else {
										if(get_post_meta( $_order->ID, '_order_total_base_currency', true ) ) {
											$price_order = get_post_meta( $_order->ID, '_order_total_base_currency', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping_base_currency', true );
										} else {
											$price_order = get_post_meta( $_order->ID, '_order_total', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping', true );
										}
									}
										
									
									//$_order_tax = $_order_tax + get_post_meta( $_order->ID, '_order_tax', true );
									$_order_total = $_order_total + $price_order;							
								}
							}
							$total = $_order_total - $_order_shipping;
										break;
									}
								}
								if($b) break;
							} else {

							}
						}
					}
				}
			}
			
			if( $add_coupon  ) {
				// $total = apply_filters( 'WOOMULTI_CURRENCY_R', $total );
				if(isset($_COOKIE['saphali'])) var_dump($total, __LINE__);
				$discount = array();
				if(is_array($variant_discount))
				foreach($variant_discount as $key => $_variant_discount) {
					foreach($_variant_discount['min'] as $_key => $_discount) {
						if( $total >= $_discount && $total <= $variant_discount[$key]['max'][$_key] ) {
							$discount[$key] = $variant_discount[$key]['discount'][$_key];
						} 
					}
				}
			}
			//var_dump($items, $variant_discount,$discount );
			if( $add_coupon && $discount  ) {
				foreach($coupons as $_k => $code) {
					//include_once( WC()->plugin_path() . 'includes/wc-coupon-functions.php' );
					$__coupon = new WC_Coupon( $code->post_title );
					if(!isset($order))
					$order = wc_get_order($_POST['order_id']);
					$_items = array();
					foreach ($order->get_items( 'line_item' ) as $item) {
						$product = $order->get_product_from_item( $item );
						$_items["order_item_id"][] = ( isset( $product->variation_id ) ) ? $product->variation_id : $product->id;
						$_items["price"][] = ( isset( $product->variation_id ) ) ? $product->get_price() : $product->get_price();
						//$count +=
					}
					
					
					$coupon_item_id_curent = 0;
					$discount_curent = 0;
					foreach($order->get_items( array( 'coupon' ) ) as $coupon_item_id => $value) {
						if( $value["name"] == $code->post_title ) {
							$coupon_item_id_curent = $coupon_item_id;
							$discount_curent = $value['discount_amount'];
							break;
						}
					}
					$seve_discount_amount = isset($discount_amount) ?  (isset($seve_discount_amount) ? $seve_discount_amount : 0) + $discount_amount : 0;
					
					$discount_amount = 0;
					
					if( $this->is_valid_admin($__coupon, $_items) ) {
						if ( !strstr( $discount[$_k], '%' ) ) {
							//$discount[$_k]; 
							$qw_ = $qty = $order_total = 0;
							foreach( $items["line_total"]  as $key_i =>  $val_price) {
								$data = get_metadata( 'order_item', $key_i, '', false );
								if($data ["_variation_id"][0]) $id_c = $data ["_variation_id"][0];
								else $id_c = $data ["_product_id"][0];
								 
								if($this->is_valid_admin_product($__coupon, $id_c ) )
								$qw_++;
							}
							
							foreach( $items["order_item_qty"]  as $_qty) {
								$qty +=$_qty;
							}
							$qty =$qty - $qw_;
							$discount_amount = round( $discount[$_k], 2 );
							
							foreach( $items["line_total"]  as $key_i =>  $val_price) {
								if( !isset($items["discount"][$key_i]) ) $items["discount"][$key_i] = 0;
								$data = get_metadata( 'order_item', $key_i, '', false );
								if($data ["_variation_id"][0]) $id_c = $data ["_variation_id"][0];
								else $id_c = $data ["_product_id"][0];
								 
								if($this->is_valid_admin_product($__coupon, $id_c ) ) {
							
								$items["line_total"][$key_i] = $items["line_subtotal"][$key_i] - round( $discount[$_k] /$qty , 2)  - $items["discount"][$key_i];
								$items["discount"][$key_i] += round( $discount[$_k] /$qty , 2); 
								}
								
								//else
								///$items["line_total"][$key_i] = $items["line_subtotal"][$key_i] - round( $seve_discount_amount * ($items["line_total"][$key_i]/$items["_order_total"])  , 2) ;
								$order_total += $items["line_total"][$key_i];
							}
							
							
							$items["_order_total"] = $order_total;
						} else {
							$line_total_ = 0;
							foreach( $items["line_total"]  as $key_i =>  $val_price) {
								$data = get_metadata( 'order_item', $key_i, '', false );
								if($data ["_variation_id"][0]) $id_c = $data ["_variation_id"][0];
								else $id_c = $data ["_product_id"][0];
								 
								if($this->is_valid_admin_product($__coupon, $id_c ) )
								$line_total_ += $items["line_total"][$key_i];
							}
							$__discount = str_replace('%', '', $discount[$_k]);
							
							foreach( $items["line_total"]  as $key_i =>  $val_price) {
								if( !isset($items["discount"][$key_i]) ) $items["discount"][$key_i] = 0;
								$data = get_metadata( 'order_item', $key_i, '', false );
								if($data ["_variation_id"][0]) $id_c = $data ["_variation_id"][0];
								else $id_c = $data ["_product_id"][0];
								
								if($this->is_valid_admin_product($__coupon, $id_c ) ) {
									$discount_amount += round( ( $items["line_subtotal"][$key_i] / 100 ) * $__discount, 2 );
									$items["line_total"][$key_i] = $items["line_subtotal"][$key_i]  - round( ( $items["line_subtotal"][$key_i] / 100 ) * $__discount, 2 ) - (float)$items["discount"][$key_i];
									$items["discount"][$key_i] += $discount_amount;
								}
								
								$order_total += $items["line_total"][$key_i];
							}
							$items["_order_total"] = $order_total;
						}
						if(isset( $discount_amount) )  {
							if(!$coupon_item_id_curent)
							$status_add_code = $order->add_coupon( $code->post_title,  $discount_amount ) ;
							else {
								if ( isset( $code->post_title ) ) {
									$coupon_args['code'] = $code->post_title;
								}

								if ( isset( $discount_amount ) ) {
									$coupon_args['discount_amount'] = floatval( $discount_amount );
								}

								$coupon_id = $order->update_coupon( $coupon_item_id_curent, $coupon_args );
							}
						}
					}					
					else $status_add_code = false;
	
					if($status_add_code) $pr[] = $discount[$_k];
				}
			}
			remove_filter( 'posts_where', array($this, 'filter_where') );
			remove_action( 'parse_query', array( $this, 'parse_query' ), 5 );
		}
		
		$coupons_cart = get_posts(array('post_type' => 'shop_coupon', 'post_status' => 'publish', 'meta_key' => 'discount_type', 'meta_value' => 'fixed_total_cart', 'posts_per_page' => -1));
		$coupon_code = $check_emails = $variant_discount = array();
		foreach($coupons_cart as $rev_key => $_coupon) {
			$coupon_code[$rev_key] = $_coupon->post_title;
			$variant_discount[$rev_key] = get_post_meta( $_coupon->ID, 'variant_discount', true );
			$customer_email = get_post_meta( $_coupon->ID, 'customer_email', true );
			$customer_cart_club = get_post_meta( $_coupon->ID, 'customer_cart_club', true );
		}
		$exclude_revers_items_product = ( get_post_meta( $_coupon->ID, 'exclude_revers_items_product', true ) == 'yes' ) ? true : false;
			
			$revers_items_product = 0;
			if($exclude_revers_items_product) {
				if(is_null($order)) 
					$order = wc_get_order($_POST['order_id']);
				if ( sizeof( $order->get_items( 'line_item' ) ) > 0 ) {
						$exclude_product_ids = $this->exclude_product_ids($_coupon);
						if(!is_array($exclude_product_ids) ) $exclude_product_ids = explode(',', $exclude_product_ids );
						$exclude_product_ids = array_map('trim',$exclude_product_ids);
						
						if($exclude_product_ids)
						$exclude_product_ids = array_filter($exclude_product_ids);
						foreach ($order->get_items( 'line_item' ) as $k => $item) {
						$product = $order->get_product_from_item( $item );
							if ( is_array($exclude_product_ids) && (in_array( $product->product_id, $exclude_product_ids ) || in_array( $product->variation_id, $exclude_product_ids ) ) ) {
								$revers_items_product = $revers_items_product + ($product->get_price() * $items["order_item_qty"]['$k']);
							} else {
								if(is_array($exclude_product_ids) && !empty($exclude_product_ids) ) {
									$product_cats = wp_get_post_terms( $product->product_id, 'product_cat', array( "fields" => "ids" ) );
									if( sizeof( array_intersect( $product_cats, $exclude_product_ids ) ) > 0 ) {
										$revers_items_product = $revers_items_product + ($product->get_price() * $items["order_item_qty"]['$k']);
									}
								}
							}
						}
				}
			}
			
		if(!empty($coupon_code)) {
			//fix wpml
			$coupon_code = array_unique($coupon_code);
			$add_coupon = true;
			if ( $customer_user_ajax ) {
				$current_user = get_user_by( 'id', $customer_user_ajax ); 
				if(!empty($customer_email)) {
					$add_coupon = false;
				} elseif(!empty($customer_cart_club)) {
					$add_coupon = false;
				}
				$check_emails[] = $current_user->user_email;
				$check_emails[] = get_user_meta($current_user->ID, 'billing_email', true);

				$check_emails = array_unique($check_emails);
				
				if(is_array($check_emails)) {
					foreach($check_emails as $user_email) {
						if(!empty($customer_email)) {
							if(!empty($user_email))
							if(in_array( $user_email, $customer_email ))
							{
								$add_coupon = true;
								break;
							}
						} else {

						}
					}
				}
			} else {
				if(!empty($customer_email)) {
					$add_coupon = false;
				}
			}
			if( $add_coupon  ) {
				$discount = array();
				if(is_array($variant_discount))
				foreach($variant_discount as $key => $_variant_discount) {
					$cart_contents_total = $__order_total - $revers_items_product;
					$cart_contents_total = apply_filters( 'WOOMULTI_CURRENCY_R', $cart_contents_total );
					if(isset($_COOKIE['saphali'])) var_dump($cart_contents_total, __LINE__);
					foreach($_variant_discount['min'] as $_key => $_discount) {
						if( $cart_contents_total >= $_discount && $cart_contents_total <= $variant_discount[$key]['max'][$_key] ) {
							$discount[$key] = $variant_discount[$key]['discount'][$_key];
						}
					}
				}
			}
			
			if( $add_coupon && $discount  ) {
				foreach($coupons_cart as $_k => $code) {
					$__coupon = new WC_Coupon( $code->post_title );
					if(!isset($order) ) $order = wc_get_order($_POST['order_id']);
					$_items = array();
					foreach ($order->get_items( 'line_item' ) as $item) {
						$product = $order->get_product_from_item( $item );
						$_items["order_item_id"][] = ( isset( $product->variation_id ) ) ? $product->variation_id : $product->id;
						$_items["price"][] = ( isset( $product->variation_id ) ) ? $product->get_price() : $product->get_price();
						//$count +=
					}
					$coupon_item_id_curent = 0;
					$discount_curent = 0;
					foreach($order->get_items( array( 'coupon' ) ) as $coupon_item_id => $value) {
						if( $value["name"] == $code->post_title ) {
							$coupon_item_id_curent = $coupon_item_id;
							$discount_curent = $value['discount_amount'];
							break;
						}
					}
					if( isset($discount_amount) ) $seve_discount_amount += $discount_amount;
					else $seve_discount_amount = 0;
					
					$discount_amount = 0;
					
					if( $this->is_valid_admin($__coupon, $_items) ) {
						if ( !strstr( $discount[$_k], '%' ) ) {
							//$discount[$_k]; 
							$qw_ = $qty = $order_total = 0;
							foreach( $items["line_total"]  as $key_i =>  $val_price) {
								$data = get_metadata( 'order_item', $key_i, '', false );
								if($data ["_variation_id"][0]) $id_c = $data ["_variation_id"][0];
								else $id_c = $data ["_product_id"][0];
								 
								if($this->is_valid_admin_product($__coupon, $id_c ) )
								$qw_++;
							}
							
							foreach( $items["order_item_qty"]  as $_qty) {
								$qty +=$_qty;
							}
							$qty =$qty - $qw_;
							
							$discount_amount = round( $discount[$_k], 2 );
							
							foreach( $items["line_total"] as $key_i => $val_price ) {
								if( !isset($items["discount"][$key_i]) ) $items["discount"][$key_i] = 0;
								$data = get_metadata( 'order_item', $key_i, '', false );
								if($data ["_variation_id"][0]) $id_c = $data ["_variation_id"][0];
								else $id_c = $data ["_product_id"][0];
								
								if($this->is_valid_admin_product($__coupon, $id_c ) ) {

								$items["line_total"][$key_i] = $items["line_subtotal"][$key_i] - round( $discount[$_k] /$qty , 2) - $items["discount"][$key_i];
								$items["discount"][$key_i] += round( $discount[$_k] /$qty , 2);
									
								}
								
								
								$order_total += $items["line_total"][$key_i];
							}
							
							
							$items["_order_total"] = $order_total;
						} else {
							
							$_order_total = $items["_order_total"];
							$line_total_ = 0;
							foreach( $items["line_total"]  as $key_i =>  $val_price) {
								$data = get_metadata( 'order_item', $key_i, '', false );
								if($data ["_variation_id"][0]) $id_c = $data ["_variation_id"][0];
								else $id_c = $data ["_product_id"][0];
								 
								if($this->is_valid_admin_product($__coupon, $id_c )  )
								$line_total_ += $items["line_total"][$key_i];
							}
							$__discount = str_replace('%', '', $discount[$_k]);
							$order_total = 0;
							
							foreach( $items["line_total"]  as $key_i =>  $val_price) {
								if( !isset($items["discount"][$key_i]) ) $items["discount"][$key_i] = 0;
								$data = get_metadata( 'order_item', $key_i, '', false );
								if($data ["_variation_id"][0]) $id_c = $data ["_variation_id"][0];
								else $id_c = $data ["_product_id"][0];
								 //var_dump($this->is_valid_admin_product($__coupon, $id_c ));
								 
								if($this->is_valid_admin_product($__coupon, $id_c ) ) {
									$discount_amount += round( ( $items["line_subtotal"][$key_i] / 100 ) * $__discount, 2 );
									
									$items["line_total"][$key_i] = $items["line_subtotal"][$key_i]  - round( ( $items["line_subtotal"][$key_i] / 100 ) * $__discount, 2 ) - (float)$items["discount"][$key_i];
									$items["discount"][$key_i] += $discount_amount;
									
								} 
								
								
								$order_total += $items["line_total"][$key_i];
							}
							
							$items["_order_total"] = $order_total;
						}
						if(isset( $discount_amount) )  {
							
							if(!$coupon_item_id_curent)
							$status_add_code = $order->add_coupon( $code->post_title,  $discount_amount ) ;
							else {
								if ( isset( $code->post_title ) ) {
									$coupon_args['code'] = $code->post_title;
								}

								if ( isset( $discount_amount ) ) {
									$coupon_args['discount_amount'] = floatval( $discount_amount );
								}

								$coupon_id = $order->update_coupon( $coupon_item_id_curent, $coupon_args );
							}
						}
					}					
					else $status_add_code = false;
	
					if($status_add_code) $pr = $discount[$_k];
				}
			}
		}
		return $items;
	}
	function is_valid_admin_product($coupon, $item) {
		$valid      = true;
		$_id = method_exists($coupon, 'get_id') ? $coupon->get_id() : $coupon->id;
		if ( $_id ) {
			$product_ids = method_exists($coupon, 'get_product_ids') ? $coupon->get_product_ids() : $coupon->product_ids;
			if ( sizeof( $product_ids ) > 0 ) {
				$valid_for_cart = false;
				
				if ( !empty( $item ) ) {
						if (  ('product_variation' === get_post_type( $item ) || 'product' === get_post_type( $item ) ) ) {
							$_product = wc_get_product( $item );
						} else {
							$_product = 0;
						}
						
						if ( in_array( $item, $product_ids ) || is_object($_product) && isset($_product->variation_id) && ($_product->id !=  $cart_item) && in_array( $_product->id, $product_ids ) )
							$valid_for_cart = true;
					
				}
				if ( ! $valid_for_cart ) {
					$valid = false;
				}
			}

			// Category ids - If a product included is found in the cart then its valid
			$product_categories = method_exists($coupon, 'get_product_categories') ? $coupon->get_product_categories() : $coupon->product_categories;
			if ( sizeof( $product_categories ) > 0 ) {
				
					$valid_for_cart = false;
					if ( !empty( $item ) ) {
					
						if ( ('product_variation' === get_post_type( $item ) || 'product' === get_post_type( $item ) ) ) {
								$_product = wc_get_product( $item );
							} else {
								$_product = 0;
							}
							if( is_object($_product) ) $id = method_exists($_product, 'get_id') ? $_product->get_id() : $_product->id;
							else $id = 0;
							
							$product_cats = wp_get_post_terms($id, 'product_cat', array("fields" => "ids"));

							if ( sizeof( array_intersect( $product_cats, $product_categories ) ) > 0 )
								$valid_for_cart = true;
						
					}
					if ( ! $valid_for_cart ) {
						$valid = false;
					}
			}
			$type = method_exists($coupon, 'get_discount_type') ? $coupon->get_discount_type() : $coupon->type;
			// Cart discounts cannot be added if non-eligble product is found in cart
			if ( $type != 'fixed_product' && $type != 'percent_product' ) {
				
				// Exclude Products
				$exclude_product_ids = method_exists($coupon, 'get_excluded_product_ids') ? $coupon->get_excluded_product_ids() : $coupon->exclude_product_ids;
				if ( sizeof( $exclude_product_ids ) > 0 ) {
					$valid_for_cart = true;
						if ( !empty( $item ) ) {
								if (  ('product_variation' === get_post_type( $item ) || 'product' === get_post_type( $item ) ) ) {
									$_product = wc_get_product( $item );
								} else {
									$_product = 0;
								}
								
								if ( in_array( $item, $exclude_product_ids ) || is_object($_product) && isset($_product->variation_id) && ($_product->id !=  $item) && in_array( $_product->id, $exclude_product_ids ) ) {
									$valid_for_cart = false;
								}
						}
						if ( ! $valid_for_cart ) {
							$valid = false;
						}
					
				}

				// Exclude Sale Items
				$exclude_sale_items = method_exists($coupon, 'get_exclude_sale_items') ? $coupon->get_exclude_sale_items() : $coupon->exclude_sale_items;
				if ( $exclude_sale_items == 'yes' ) {
					$valid_for_cart = true;
					$product_ids_on_sale = woocommerce_get_product_ids_on_sale();
					if(is_null($product_ids_on_sale)) $product_ids_on_sale = array();
					$i_on_s = array_search(0, $product_ids_on_sale);
					if($i_on_s !== false) unset($product_ids_on_sale[$i_on_s]);
					if ( !empty( $item ) ) {
						if ( in_array( $item, $product_ids_on_sale, true ) ) {
							$valid_for_cart = false;
						}
						
					}
					if ( ! $valid_for_cart ) {
						$valid = false;
					}
				}

				// Exclude Categories
				$exclude_product_categories = method_exists($coupon, 'get_excluded_product_categories') ? $coupon->get_excluded_product_categories() : $coupon->exclude_product_categories;
				if ( sizeof( $exclude_product_categories ) > 0 ) {

						$valid_for_cart = true;
						if ( !empty( $item ) ) {
							
								if (  ('product_variation' === get_post_type( $item ) || 'product' === get_post_type( $item ) ) ) {
								$_product = wc_get_product( $item );
							} else {
								$_product = 0;
							}
							if( is_object($_product) ) $id = method_exists($_product, 'get_id') ? $_product->get_id() : $_product->id;
							else $id = 0;
								$product_cats = wp_get_post_terms( $id, 'product_cat', array( "fields" => "ids" ) );

								if ( sizeof( array_intersect( $product_cats, $exclude_product_categories ) ) > 0 )
									$valid_for_cart = false;
							
						}
						if ( ! $valid_for_cart ) {
							$valid = false;
						}	
				}
			}

			$valid = apply_filters( 'woocommerce_coupon_is_validate', $valid, $coupon );

			if ( $valid ) {
				return true;
			} else return false;
		}
	}
	public function is_valid_admin($coupon, $items) {
		global $woocommerce;

		$error_code = null;
		$valid      = true;
		$error      = false;
		$_id = method_exists($coupon, 'get_id') ? $coupon->get_id() : $coupon->id;
		if ( $_id ) {
			$type = method_exists($coupon, 'get_discount_type') ? $coupon->get_discount_type() : $coupon->type;
			$coupone_customer_role = get_post_meta( $_id, 'saphali_coupone_customer_role', true );
			$coupone_customer_no_role = get_post_meta( $_id, 'saphali_coupone_customer_no_role', true );
			if ( !empty($coupone_customer_role) && is_array($coupone_customer_role) ) {
				$customer_user_ajax = isset( $items["customer_user_ajax"] ) ? $items["customer_user_ajax"]: 0;
				$ob = get_user_by( 'id', $customer_user_ajax ); 
				if (!( isset($ob->roles[0]) && in_array( $ob->roles[0], $coupone_customer_role) )) {
					$valid = false;
					$error_code = __('Вы не состоите в группе пользователей, для которой применим данный купон.', 'saphali-discount');
				}
			}
			if ( !empty($coupone_customer_no_role) && is_array($coupone_customer_no_role) ) {
				$customer_user_ajax = isset( $items["customer_user_ajax"] ) ? $items["customer_user_ajax"]: 0;
				$ob = get_user_by( 'id', $customer_user_ajax ); 
				if (isset($ob->roles[0]) && in_array( $ob->roles[0], $coupone_customer_no_role)) {
					$valid = false;
					$error_code = __('Вы не можете воспользоваться данным купоном.', 'saphali-discount');
					
				}
			}
			// Usage Limit
			$usage_limit = method_exists($coupon, 'get_usage_limit') ? $coupon->get_usage_limit() : $coupon->usage_limit;
			if ( $usage_limit > 0 ) {
				$usage_count = method_exists($coupon, 'get_usage_count') ? $coupon->get_usage_count() : $coupon->usage_count;
				if ( $usage_count >= $usage_limit ) {
					$valid = false;
					if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) $error_code = __( 'Coupon usage limit has been reached.', 'woocommerce' ); else $error_code = WC_Coupon::E_WC_COUPON_USAGE_LIMIT_REACHED;
				}
			}
			// Minimum spend
			$minimum_amount = method_exists($coupon, 'get_minimum_amount') ? $coupon->get_minimum_amount() : $coupon->minimum_amount;
			if ( $minimum_amount > 0 ) {
				if ( $minimum_amount > $woocommerce->cart->subtotal ) {
					$valid = false;
					if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) $error_code = sprintf( __( 'The minimum spend for this coupon is %s.', 'woocommerce' ), $minimum_amount ); else $error_code = WC_Coupon::E_WC_COUPON_MIN_SPEND_LIMIT_NOT_MET;
				}
			}
			$product_ids = method_exists($coupon, 'get_product_ids') ? $coupon->get_product_ids() : $coupon->product_ids;
			if ( sizeof( $product_ids ) > 0 ) {
				$valid_for_cart = false;
				if ( sizeof( $items["order_item_id"] ) > 0 ) {
					
					foreach( $items["order_item_id"] as $cart_item_key => $cart_item ) {
						
						if ( ! empty( $cart_item ) && ('product_variation' === get_post_type( $cart_item ) || 'product' === get_post_type( $cart_item ) ) ) {
							$_product = wc_get_product( $cart_item );
						} else {
							$_product = 0;
						}
						if ( in_array( $cart_item, $product_ids ) || is_object($_product) && isset($_product->variation_id) && ($_product->id !=  $cart_item) && in_array( $_product->id, $product_ids ) )
							$valid_for_cart = true;
					}
				}
				if ( ! $valid_for_cart ) {
					$valid = false;
					if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
				}
			}
			// Category ids - If a product included is found in the cart then its valid
			$product_categories = method_exists($coupon, 'get_product_categories') ? $coupon->get_product_categories() : $coupon->product_categories;
			if ( sizeof( $product_categories ) > 0 ) {
				$exclude_revers_items_product = (get_post_meta( $_id, 'exclude_revers_items_product', true ) == 'yes') ? true : false;
				if($exclude_revers_items_product) {
					$valid_for_cart = false;
					if ( sizeof( $items["order_item_id"] ) > 0 ) {
					foreach( $items["order_item_id"] as $cart_item_key => $cart_item ) {
							if ( ! empty( $cart_item ) && ('product_variation' === get_post_type( $cart_item ) || 'product' === get_post_type( $cart_item ) ) ) {
								$_product = wc_get_product( $cart_item );
							} else {
								$_product = 0;
							}
							if( is_object($_product) ) $id = method_exists($_product, 'get_id') ? $_product->get_id() : $_product->id;
							else $id = 0;
							
							$product_cats = wp_get_post_terms($id, 'product_cat', array("fields" => "ids"));

							if ( sizeof( array_intersect( $product_cats, $product_categories ) ) > 0 ) {
									$valid_for_cart = true; break;
								}
						}
					}
					if ( ! $valid_for_cart ) {
						$valid = false;
						if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
					}
				} else {
					$valid_for_cart = false;
					if ( sizeof( $items["order_item_id"] ) > 0 ) {
					foreach( $items["order_item_id"] as $cart_item_key => $cart_item ) {
						if ( ! empty( $cart_item ) && ('product_variation' === get_post_type( $cart_item ) || 'product' === get_post_type( $cart_item ) ) ) {
								$_product = wc_get_product( $cart_item );
							} else {
								$_product = 0;
							}
							if( is_object($_product) ) $id = method_exists($_product, 'get_id') ? $_product->get_id() : $_product->id;
							else $id = 0;
							
							$product_cats = wp_get_post_terms($id, 'product_cat', array("fields" => "ids"));

							if ( sizeof( array_intersect( $product_cats, $product_categories ) ) > 0 )
								$valid_for_cart = true;
						}
					}
					if ( ! $valid_for_cart ) {
						$valid = false;
						if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
					}
				}
			}

			// Cart discounts cannot be added if non-eligble product is found in cart
			if ( $type != 'fixed_product' && $type != 'percent_product' ) {

				// Exclude Products
				$exclude_product_ids = method_exists($coupon, 'get_excluded_product_ids') ? $coupon->get_excluded_product_ids() : $coupon->exclude_product_ids;
				if ( sizeof( $exclude_product_ids ) > 0 ) {
					$exclude_revers_items_product = ( get_post_meta( $_id, 'exclude_revers_items_product', true ) == 'yes' ) ? true : false;
					if($exclude_revers_items_product) {
						$valid_for_cart = true;
						if ( sizeof( $items["order_item_id"] ) > 0 ) {
							foreach( $items["order_item_id"] as $cart_item_key => $cart_item ) {
						if ( ! empty( $cart_item ) && ('product_variation' === get_post_type( $cart_item ) || 'product' === get_post_type( $cart_item ) ) ) {
							$_product = wc_get_product( $cart_item );
						} else {
							$_product = 0;
						}
						
						if ( in_array( $cart_item, $exclude_product_ids ) || is_object($_product) && isset($_product->variation_id) && ($_product->id !=  $cart_item) && in_array( $_product->id, $exclude_product_ids ) ) {
									$valid_for_cart = false;
								} else {$valid_for_cart = true; break;}
							}
						}
						
						if ( ! $valid_for_cart ) {
							$valid = false;
							if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
						}
					} else {
						$valid_for_cart = true;
						if ( sizeof( $items["order_item_id"] ) > 0 ) {
							foreach( $items["order_item_id"] as $cart_item_key => $cart_item ) {
								if ( ! empty( $cart_item ) && ('product_variation' === get_post_type( $cart_item ) || 'product' === get_post_type( $cart_item ) ) ) {
									$_product = wc_get_product( $cart_item );
								} else {
									$_product = 0;
								}
								
								if ( in_array( $cart_item, $exclude_product_ids ) || is_object($_product) && isset($_product->variation_id) && ($_product->id !=  $cart_item) && in_array( $_product->id, $exclude_product_ids ) ) {
									$valid_for_cart = false;
								}
							}
						}
						if ( ! $valid_for_cart ) {
							$valid = false;
							if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
						}
					}
				}

				// Exclude Sale Items
				$exclude_sale_items = method_exists($coupon, 'get_exclude_sale_items') ? $coupon->get_exclude_sale_items() : $coupon->exclude_sale_items;
				if ( $exclude_sale_items == 'yes' ) {
					$valid_for_cart = true;
					$product_ids_on_sale = woocommerce_get_product_ids_on_sale();
					if(is_null($product_ids_on_sale)) $product_ids_on_sale = array();
					$i_on_s = array_search(0, $product_ids_on_sale);
					if($i_on_s !== false) unset($product_ids_on_sale[$i_on_s]);
					if ( sizeof( $items["order_item_id"] ) > 0 ) {
						foreach( $items["order_item_id"] as $cart_item_key => $cart_item ) {
							if ( in_array( $cart_item, $product_ids_on_sale, true ) ) {
								$valid_for_cart = false;
							}
						}
					}
					if ( ! $valid_for_cart ) {
						$valid = false;
						if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not valid for sale items.', 'woocommerce' ); } else $error_code = WC_Coupon::E_WC_COUPON_NOT_VALID_SALE_ITEMS;
					}
				}

				// Exclude Categories
				$exclude_product_categories = method_exists($coupon, 'get_excluded_product_categories') ? $coupon->get_excluded_product_categories() : $coupon->exclude_product_categories;
				if ( sizeof( $exclude_product_categories ) > 0 ) {
					$exclude_revers_items_product = (get_post_meta( $_id, 'exclude_revers_items_product', true ) == 'yes') ? true : false;
					if($exclude_revers_items_product) {
						$valid_for_cart = true;
						if ( sizeof( $items["order_item_id"] ) > 0 ) {
							foreach( $items["order_item_id"] as $cart_item_key => $cart_item ) {

								if ( ! empty( $cart_item ) && ('product_variation' === get_post_type( $cart_item ) || 'product' === get_post_type( $cart_item ) ) ) {
								$_product = wc_get_product( $cart_item );
							} else {
								$_product = 0;
							}
							if( is_object($_product) ) $id = method_exists($_product, 'get_id') ? $_product->get_id() : $_product->id;
							else $id = 0;
								$product_cats = wp_get_post_terms( $id, 'product_cat', array( "fields" => "ids" ) );

								if ( sizeof( array_intersect( $product_cats, $exclude_product_categories ) ) > 0 ) {
									$valid_for_cart = false;
								} else {$valid_for_cart = true; break;}
							}
						}
						if ( ! $valid_for_cart ) {
							$valid = false;
							if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
						}
					} else {
						$valid_for_cart = true;
						if ( sizeof( $items["order_item_id"] ) > 0 ) {
							foreach( $items["order_item_id"] as $cart_item_key => $cart_item ) {
								if ( ! empty( $cart_item ) && ('product_variation' === get_post_type( $cart_item ) || 'product' === get_post_type( $cart_item ) ) ) {
								$_product = wc_get_product( $cart_item );
							} else {
								$_product = 0;
							}
							if( is_object($_product) ) $id = method_exists($_product, 'get_id') ? $_product->get_id() : $_product->id;
							else $id = 0;
								$product_cats = wp_get_post_terms( $id, 'product_cat', array( "fields" => "ids" ) );

								if ( sizeof( array_intersect( $product_cats, $exclude_product_categories ) ) > 0 )
									$valid_for_cart = false;
							}
						}
						if ( ! $valid_for_cart ) {
							$valid = false;
							if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce' ); } else $error_code = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
						}					
					}
				}
			}
			// Expired
			$expiry_date = method_exists($coupon, 'get_date_expires') ? $coupon->get_date_expires(): $coupon->expiry_date;
			if ( $expiry_date ) {
				if( !version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) {
					$expiry_date = $expiry_date->getTimestamp();
				} 
				if ( current_time( 'timestamp' ) > $expiry_date ) {
					$valid = false;
					if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) $error_code = __( 'This coupon has expired.', 'woocommerce' ); else $error_code = WC_Coupon::E_WC_COUPON_EXPIRED;
				}
			}
			$valid = apply_filters( 'woocommerce_coupon_is_validate', $valid, $coupon );

			if ( $valid ) {
				return true;
			} else {
				if ( is_null( $error_code ) )
					if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Coupon is not valid.', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_INVALID_FILTERED;
			}

		} else {
			if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Invalid coupon', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_NOT_EXIST;
		}

		if ( $error_code ) {
			if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ))
			return new WP_Error( 'coupon_error', apply_filters( 'woocommerce_coupon_error_end', $error_code, $coupon ) );
			else 
			$coupon->error_message = $coupon->get_coupon_error( $error_code );
		}

		return $coupon->get_coupon_error( $error_code );
	}
	function is_coupon_emails_allowed( $check_emails, $restrictions ) {
		foreach ( $check_emails as $check_email ) {
			if ( in_array( $check_email, $restrictions, true ) ) {
				return true;
			}
			foreach ( $restrictions as $restriction ) {
				$regex = '/^' . str_replace( '*', '(.+)?', $restriction ) . '$/';
				preg_match( $regex, $check_email, $match );
				if ( ! empty( $match ) ) {
					return true;
				}
			}
		}
		return false;
	}
	
	function cart_apry_discount() {
	global $woocommerce;
	add_filter( 'posts_where', array($this, 'filter_where') );
	add_action( 'parse_query', array( $this, 'parse_query' ), 5 );
	$action_ac = (isset($_POST['action']) && $_POST['action'] == '_woocommerce_update_order_review');
	if($action_ac) {
		if ( !version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) && !isset($woocommerce->session->d_phone) ) $woocommerce->session->set_customer_session_cookie( true );
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) $woocommerce->nocache();
		$coupons = get_posts(array('post_type' => 'shop_coupon', 'post_status' => 'publish', 'meta_key' => 'discount_type', 'meta_value' => 'fixed_total_shop', 'posts_per_page' => -1));
		foreach($coupons as $_coupon) {
			$customer_cart_club = get_post_meta( $_coupon->ID, 'customer_cart_club', true );
		}
		$add_coupon = false;
		if( true ) {
					if(isset($_POST['post_data'])) {
						parse_str ($_POST['post_data'], $post_data);
						if(isset($post_data["billing_cart_club"])) {
							$post_data["billing_cart_club"] = str_replace( array('(',')',' ', '-'), '', $post_data["billing_cart_club"] ) ;
							$check_ps[] = $post_data["billing_cart_club"];
							if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								$_SESSION['d_phone'] = $post_data["billing_cart_club"];
							} else {
								$woocommerce->session->d_phone = $post_data["billing_cart_club"];
							}
						}
					}
					$check_ps = array_unique($check_ps);
					if(is_array($check_ps)) {
						foreach($check_ps as $user_p) {
							if(!empty($customer_cart_club)) {
								if(!empty($user_p))
								if(in_array( $user_p, $customer_cart_club ))
								{
									$add_coupon = true;
									if( ! version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) {
									$arg = array('post_type' => 'shop_order', 'post_status' => array( 'wc-processing', 'wc-completed' ), 'posts_per_page' => -1,   
									
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_billing_cart_club',
									   'value' => $user_p,
									   'compare' => 'LIKE',
									   )
								   )
								);
								$orders = new WP_Query($arg);
								} else {
								$arg = array('post_type' => 'shop_order', 'post_status' => 'publish', 'posts_per_page' => -1,   
									'tax_query' => array(
									   array(
											'taxonomy' => 'shop_order_status',
											'field' => 'slug',
											'terms' => array( 'completed', 'processing' )
										)
								   ),
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_billing_cart_club',
									   'value' => $user_p,
									   'compare' => 'LIKE',
									   )
								   )
								);
								$orders = new WP_Query($arg); 
							}
							$_order_shipping = $_order_discount = $_order_total = 0;
							if( $orders->have_posts() ) {
								while ( $orders->have_posts() ) {
									$orders->the_post();
									$_order = $orders->post;
									
									$order_currency = get_post_meta( $_order->ID, '_order_currency', true );
									$rate_def = $this->compatibility_currency($order_currency);
									if( $rate_def != 1 ) {
										$amount = get_post_meta( $_order->ID, '_order_total', true );
										$_order_shipping = $_order_shipping + number_format( get_post_meta( $_order->ID, '_order_shipping', true )  * $rate_def, 2, '.', '');
										$price_order = number_format(  $amount * $rate_def, 2, '.', '');
									} else {
										if(get_post_meta( $_order->ID, '_order_total_base_currency', true ) ) {
											$price_order = get_post_meta( $_order->ID, '_order_total_base_currency', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping_base_currency', true );
										} else {
											$price_order = get_post_meta( $_order->ID, '_order_total', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping', true );
										}
									}
										
									
									//$_order_tax = $_order_tax + get_post_meta( $_order->ID, '_order_tax', true );
									$_order_total = $_order_total + $price_order;							
								}
							}
							$total = $_order_total - $_order_shipping;
							
									break;
								}
							} else {

							}
						}
					}
				}
				if(isset($total) && $total) {
					die( json_encode(array( 'result' => true, 'phone' => $post_data["billing_cart_club"], 's' => $total)) );
				} else die(json_encode(array( 'result' => false, 'phone' => $post_data["billing_cart_club"] )));
	}
	 if( ( is_cart() || is_checkout() || (isset($_POST['action']) && ($_POST['action'] == 'woocommerce_update_order_review' || $action_ac ) ) ) && empty($_POST['coupon_code'])) {
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) $woocommerce->nocache();
		$coupons = get_posts(array('post_type' => 'shop_coupon', 'post_status' => 'publish', 'meta_key' => 'discount_type', 'meta_value' => 'fixed_total_shop', 'posts_per_page' => -1));
		$current_user = wp_get_current_user();
		$check_emails = array();
		$check_emails[] = $current_user->user_email;
		$check_emails[] = get_user_meta($current_user->ID, 'billing_email', true);
		$check_emails = array_unique($check_emails);
		foreach($coupons as $_coupon) {
			
			$customer_email = get_post_meta( $_coupon->ID, 'customer_email', true );
			$customer_cart_club = get_post_meta( $_coupon->ID, 'customer_cart_club', true );
					
			if ( is_array( $customer_email ) && 0 < count( $customer_email ) && ! $this->is_coupon_emails_allowed( $check_emails, $customer_email ) ) {

			} else {
			$coupon_code[] = $_coupon->post_title;
			$variant_discount[] = get_post_meta( $_coupon->ID, 'variant_discount', true );
			}
				
		}

		if(!empty($coupon_code)) {
			//fix wpml
			$coupon_code = array_unique($coupon_code);
			$add_coupon = false;
			if ( is_user_logged_in() ) {
				
				$add_coupon = true;
				if(!empty($customer_email)) {
					$add_coupon = false;
				} elseif(!empty($customer_cart_club)) {
					$add_coupon = false;
				}
				
				if(is_array($check_emails)) {
					foreach($check_emails as $user_email) {
						if(!empty($customer_email)) {
							if(!empty($user_email))
							if(in_array( $user_email, $customer_email ))
							{
								$add_coupon = true;
								if( ! version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) )
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => array( 'wc-completed', 'wc-processing' ), 'posts_per_page' => -1,   
									
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
								else
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => 'publish', 'posts_per_page' => -1,   
									'tax_query' => array(
									   array(
											'taxonomy' => 'shop_order_status',
											'field' => 'slug',
											'terms' => array( 'completed', 'processing' )
										)
								   ),
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
							$_order_shipping = $_order_discount = $_order_total = 0;
							if( $orders->have_posts() ) {
								while ( $orders->have_posts() ) {
									$orders->the_post();
									$_order = $orders->post;
									
									$order_currency = get_post_meta( $_order->ID, '_order_currency', true );
									$rate_def = $this->compatibility_currency($order_currency);
									if(isset($_COOKIE['saphali'])) var_dump($order_currency, __LINE__);
									if( $rate_def != 1 ) {
										$amount = get_post_meta( $_order->ID, '_order_total', true );
										$_order_shipping = $_order_shipping + number_format( get_post_meta( $_order->ID, '_order_shipping', true )  * $rate_def, 2, '.', '');
										$price_order = number_format(  $amount * $rate_def, 2, '.', '');
									} else {
										if(get_post_meta( $_order->ID, '_order_total_base_currency', true ) ) {
											$price_order = get_post_meta( $_order->ID, '_order_total_base_currency', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping_base_currency', true );
										} else {
											$price_order = get_post_meta( $_order->ID, '_order_total', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping', true );
										}
									}
										
									
									//$_order_tax = $_order_tax + get_post_meta( $_order->ID, '_order_tax', true );
									$_order_total = $_order_total + $price_order;							
								}
							}
							$total = $_order_total - $_order_shipping;
								break;
							}
							
						} else {
							if(!empty($user_email))
							if( ! version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) )
							$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => array( 'wc-completed', 'wc-processing'), 'posts_per_page' => -1, 
									
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
							));
							else
							$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => 'publish', 'posts_per_page' => -1, 
									'tax_query' => array(
									   array(
											'taxonomy' => 'shop_order_status',
											'field' => 'slug',
											'terms' => array( 'completed', 'processing' )
										)
								   ),
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
							));
							$_order_shipping = $_order_discount = $_order_total = 0;
							if( is_object($orders) && method_exists($orders, 'have_posts') && $orders->have_posts() ) {
								while ( $orders->have_posts() ) {
									$orders->the_post();
									$_order = $orders->post;
									$order_currency = get_post_meta( $_order->ID, '_order_currency', true );
									$rate_def = $this->compatibility_currency($order_currency);
									if(isset($_COOKIE['saphali'])) var_dump($order_currency, __LINE__);
									if( $rate_def != 1 ) {
										$amount = get_post_meta( $_order->ID, '_order_total', true );
										$_order_shipping = $_order_shipping + number_format( get_post_meta( $_order->ID, '_order_shipping', true )  * $rate_def, 2, '.', '');
										$price_order = number_format(  $amount * $rate_def, 2, '.', '');
									} else {
										if(get_post_meta( $_order->ID, '_order_total_base_currency', true ) ) {
											$price_order = get_post_meta( $_order->ID, '_order_total_base_currency', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping_base_currency', true );
										} else {
											$price_order = get_post_meta( $_order->ID, '_order_total', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping', true );
										}
									}
										
									
									//$_order_tax = $_order_tax + get_post_meta( $_order->ID, '_order_tax', true );
									$_order_total = $_order_total + $price_order;							
								}
							}
							$total = $_order_total - $_order_shipping;
							
						}
					}
				}
				if(!$add_coupon) {
					$check_ps[] = get_user_meta($current_user->ID, 'billing_cart_club', true);
					if(isset($_POST['post_data'])) {
						 parse_str ($_POST['post_data'], $post_data);
						 if(isset($post_data["billing_cart_club"])) {
							$post_data["billing_cart_club"] = str_replace( array('(',')',' ', '-'), '', $post_data["billing_cart_club"] ) ;
							$check_ps[] = $post_data["billing_cart_club"];
							if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								$_SESSION['d_phone'] = $post_data["billing_cart_club"];
							} else {
								$woocommerce->session->d_phone = $post_data["billing_cart_club"];
							}
						 }
					 }
					$check_ps = array_unique($check_ps);
					
					if(is_array($check_ps)) {
						foreach($check_ps as $user_p) {
							if(!empty($customer_cart_club)) {
								if(!empty($user_p))
								if(in_array( $user_p, $customer_cart_club ))
								{
									$add_coupon = true;
									if( ! version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) 
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => array( 'wc-processing', 'wc-completed' ), 'posts_per_page' => -1,   
									
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
								else
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => 'publish', 'posts_per_page' => -1,   
									'tax_query' => array(
									   array(
											'taxonomy' => 'shop_order_status',
											'field' => 'slug',
											'terms' => array( 'completed', 'processing' )
										)
								   ),
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
							$_order_shipping = $_order_discount = $_order_total = 0;
							if( $orders->have_posts() ) {
								while ( $orders->have_posts() ) {
									$orders->the_post();
									$_order = $orders->post;
									
									$order_currency = get_post_meta( $_order->ID, '_order_currency', true );
									$rate_def = $this->compatibility_currency($order_currency);
									if(isset($_COOKIE['saphali'])) var_dump($order_currency, __LINE__);
									if( $rate_def != 1 ) {
										$amount = get_post_meta( $_order->ID, '_order_total', true );
										$_order_shipping = $_order_shipping + number_format( get_post_meta( $_order->ID, '_order_shipping', true )  * $rate_def, 2, '.', '');
										$price_order = number_format(  $amount * $rate_def, 2, '.', '');
									} else {
										if(get_post_meta( $_order->ID, '_order_total_base_currency', true ) ) {
											$price_order = get_post_meta( $_order->ID, '_order_total_base_currency', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping_base_currency', true );
										} else {
											$price_order = get_post_meta( $_order->ID, '_order_total', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping', true );
										}
									}
										
									
									//$_order_tax = $_order_tax + get_post_meta( $_order->ID, '_order_tax', true );
									$_order_total = $_order_total + $price_order;							
								}
							}
							$total = $_order_total - $_order_shipping;
									break;
								}
								$b = 0;
								foreach($customer_cart_club as $phone) {
									if( strpos( $phone, str_replace('+', '', $user_p) ) !== false && mb_strlen($user_p, 'utf-8') > 7) {
										$add_coupon = true;
										$b = 1; 
										if( ! version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) 
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => array( 'wc-processing', 'wc-completed' ), 'posts_per_page' => -1,   
									
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
								else
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => 'publish', 'posts_per_page' => -1,   
									'tax_query' => array(
									   array(
											'taxonomy' => 'shop_order_status',
											'field' => 'slug',
											'terms' => array( 'completed', 'processing' )
										)
								   ),
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
							$_order_shipping = $_order_discount = $_order_total = 0;
							if( $orders->have_posts() ) {
								while ( $orders->have_posts() ) {
									$orders->the_post();
									$_order = $orders->post;
									$order_currency = get_post_meta( $_order->ID, '_order_currency', true );
									$rate_def = $this->compatibility_currency($order_currency);
									if(isset($_COOKIE['saphali'])) var_dump($order_currency, __LINE__);
									if( $rate_def != 1 ) {
										$amount = get_post_meta( $_order->ID, '_order_total', true );
										$_order_shipping = $_order_shipping + number_format( get_post_meta( $_order->ID, '_order_shipping', true )  * $rate_def, 2, '.', '');
										$price_order = number_format(  $amount * $rate_def, 2, '.', '');
									} else {
										if(get_post_meta( $_order->ID, '_order_total_base_currency', true ) ) {
											$price_order = get_post_meta( $_order->ID, '_order_total_base_currency', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping_base_currency', true );
										} else {
											$price_order = get_post_meta( $_order->ID, '_order_total', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping', true );
										}
									}
										
									
									//$_order_tax = $_order_tax + get_post_meta( $_order->ID, '_order_tax', true );
									$_order_total = $_order_total + $price_order;							
								}
							}
							$total = $_order_total - $_order_shipping;
										break;
									}
								}
								if($b) break;
							} else {

							}
						}
					}
				}
			}  else {
				if(!$add_coupon ) {
					if(isset($_POST['post_data'])) {
						parse_str ($_POST['post_data'], $post_data);
						if(isset($post_data["billing_cart_club"]) && !empty($post_data["billing_cart_club"]) ) {
							$post_data["billing_cart_club"] = str_replace( array('(',')',' ', '-'), '', $post_data["billing_cart_club"] ) ;
							$check_ps[] = $post_data["billing_cart_club"];
							if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								$_SESSION['d_phone'] = $post_data["billing_cart_club"];
							} else {
								$woocommerce->session->d_phone = $post_data["billing_cart_club"];
							}
						} else {
							if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								if(isset($_SESSION['d_phone']))
								$post_data["billing_cart_club"] = $_SESSION['d_phone'];
							} else {
								if(isset($woocommerce->session->d_phone))
									$post_data["billing_cart_club"] = $woocommerce->session->d_phone;
							}
							if(isset($post_data["billing_cart_club"]))
								$check_ps[] = $post_data["billing_cart_club"];
						}
					} else {
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
							if(isset($_SESSION['d_phone']))
							$post_data["billing_cart_club"] = $_SESSION['d_phone'];
						} else {
							if(isset($woocommerce->session->d_phone))
								$post_data["billing_cart_club"] = $woocommerce->session->d_phone;
						}
						if(isset($post_data["billing_cart_club"]))
							$check_ps[] = $post_data["billing_cart_club"];
					}
					if( isset($check_ps) )
					$check_ps = array_unique($check_ps);
				
					if( isset($check_ps) && is_array($check_ps) ) {
						foreach($check_ps as $user_p) {
							if(!empty($customer_cart_club)) {
								if(!empty($user_p))
								if(in_array( $user_p, $customer_cart_club ))
								{
									$add_coupon = true;
									if( ! version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) {
									$arg = array('post_type' => 'shop_order', 'post_status' => array( 'wc-processing', 'wc-completed' ), 'posts_per_page' => -1,   
									
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_billing_cart_club',
									   'value' => $user_p,
									   'compare' => 'LIKE',
									   )
								   )
								);
								$orders = new WP_Query($arg);
								} else {
								$arg = array('post_type' => 'shop_order', 'post_status' => 'publish', 'posts_per_page' => -1,   
									'tax_query' => array(
									   array(
											'taxonomy' => 'shop_order_status',
											'field' => 'slug',
											'terms' => array( 'completed', 'processing' )
										)
								   ),
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_billing_cart_club',
									   'value' => $user_p,
									   'compare' => 'LIKE',
									   )
								   )
								);
								$orders = new WP_Query($arg); 
							}
							$_order_shipping = $_order_discount = $_order_total = 0;
							if( $orders->have_posts() ) {
								while ( $orders->have_posts() ) {
									$orders->the_post();
									$_order = $orders->post;
									
									$order_currency = get_post_meta( $_order->ID, '_order_currency', true );
									$rate_def = $this->compatibility_currency($order_currency);
									if( $rate_def != 1 ) {
										$amount = get_post_meta( $_order->ID, '_order_total', true );
										$_order_shipping = $_order_shipping + number_format( get_post_meta( $_order->ID, '_order_shipping', true )  * $rate_def, 2, '.', '');
										$price_order = number_format(  $amount * $rate_def, 2, '.', '');
									} else {
										if(get_post_meta( $_order->ID, '_order_total_base_currency', true ) ) {
											$price_order = get_post_meta( $_order->ID, '_order_total_base_currency', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping_base_currency', true );
										} else {
											$price_order = get_post_meta( $_order->ID, '_order_total', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping', true );
										}
									}
										
									
									//$_order_tax = $_order_tax + get_post_meta( $_order->ID, '_order_tax', true );
									$_order_total = $_order_total + $price_order;							
								}
							}
							$total = $_order_total - $_order_shipping;
									break;
								}
							} else {

							}
						}
					}
				}
			}
			$discount = array();
			if( $add_coupon  ) {
				// $total = apply_filters( 'WOOMULTI_CURRENCY_R', $total );
				if(isset($_COOKIE['saphali'])) var_dump($total, __LINE__);
				// var_dump($total);
				if(is_array($variant_discount))
				foreach($variant_discount as $key => $_variant_discount) {
					foreach($_variant_discount['min'] as $_key => $_discount) {
						
						if( $total >= $_discount && $total <= $variant_discount[$key]['max'][$_key] ) {
							$discount[$key] = $variant_discount[$key]['discount'][$_key];
						} 
					}
				}
			}
			
			if( $add_coupon && $discount  ) {
				//
				//$count=0;
				
				foreach($woocommerce->cart->applied_coupons as $_c_ )
				$coupon__ = new WC_Coupon( $_c_ );
				if( isset($coupon__) ) {
					$type = method_exists($coupon__, 'get_discount_type') ? $coupon__->get_discount_type() : $coupon__->type;
					$individual_use = method_exists($coupon__, 'get_individual_use') ? $coupon__->get_individual_use() : $coupon__->individual_use;
					if( !( "fixed_total_shop" == $type || "fixed_total_cart" == $type ) && $individual_use == 'yes' ) return;
				}
				
				foreach($coupon_code as $_k => $code) {

					if(empty($discount[$_k])) {
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
							foreach($woocommerce->cart->applied_coupons as $key => $_code) {
								if($code == $_code) {
									$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка (%s) по всем Вашим заказам аннулирована', 'saphali-discount' ), $_SESSION['discount_saphali_shop'][$key] ) );
									unset($_SESSION['discount_saphali_shop'][$key]); 
									unset($woocommerce->cart->applied_coupons[$key]);
								}
							}
							$_SESSION['coupons'] = $woocommerce->cart->applied_coupons;
						} else {
							foreach($woocommerce->cart->applied_coupons as $key => $_code) {
								if($code == $_code) {
									$this->comp_woocomerce_mess(  sprintf(__( 'Ваша накопительная скидка (%s) по всем Вашим заказам аннулирована', 'saphali-discount' ), $woocommerce->session->discount_saphali_shop[$key] ) );
									if(empty($woocommerce->session->discount_saphali_shop)) $woocommerce->session->discount_saphali_shop = array();
									$session = $woocommerce->session->discount_saphali_shop;
									unset($session[$key]);
									$woocommerce->session->discount_saphali_shop = $session;
									unset($woocommerce->cart->applied_coupons[$key]);
								}
								
							}
							$woocommerce->session->coupon_codes = $woocommerce->cart->applied_coupons;
						}
						continue;
					}
					$status_add_code = false;
					if(isset($woocommerce->cart->coupon_discount_amounts[$code]) && !isset($woocommerce->cart->applied_coupons[$_k]) )
						$woocommerce->cart->applied_coupons[$_k] = $code;
					$woocommerce->cart->applied_coupons = array_unique($woocommerce->cart->applied_coupons);
					sort($woocommerce->cart->applied_coupons);
					if(is_array($woocommerce->cart->applied_coupons)) {
						if( !in_array($code, $woocommerce->cart->applied_coupons) ) {
							$__coupon = new WC_Coupon( $code );
							if($this->is_valid($__coupon))
							$status_add_code = $this->add_discount( $code );
							else $status_add_code = false;
							//$count++;
						} else {
							$coupon = new WC_Coupon($code);
							$id = method_exists($coupon, 'get_id') ? $coupon->get_id() : $coupon->id;
							$this->info_cart_checkout = get_post_meta( $id, 'info_cart_checkout', true );
							
								if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
									foreach($woocommerce->cart->applied_coupons as $key => $_code) {
										if($code == $_code) {  
											if($discount[$_k] != $_SESSION['discount_saphali_shop'][$key] ) {
												if( $this->info_cart_checkout != 'yes' ) {
													$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по всем Вашим заказам составила %s', 'saphali-discount' ), $discount[$_k]) );
												}
												
											}
											$_SESSION['discount_saphali_shop'][$key] = $discount[$_k]; 
										}
									}
								} else {
									foreach($woocommerce->cart->applied_coupons as $key => $_code) {
										if($code == $_code) {  
											if($discount[$_k] != $woocommerce->session->discount_saphali_shop[$key] ) {
											if( $this->info_cart_checkout != 'yes' ) {
												
												$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по всем Вашим заказам составила %s', 'saphali-discount' ), $discount[$_k]) );
												
											}
											
											}
											if(empty($woocommerce->session->discount_saphali_shop)) $woocommerce->session->discount_saphali_shop = array();
											$session = $woocommerce->session->discount_saphali_shop;
											$session[$key] = $discount[$_k];
											$woocommerce->session->discount_saphali_shop = $session;
										}
									}
								}
							
						}
					} else {
							$__coupon = new WC_Coupon( $code );
							
							if($this->is_valid($__coupon))
							$status_add_code = $this->add_discount( $code );
							else $status_add_code = false;
						//$count++;
					}
					if($status_add_code) {
						//if($count === 1) unset($woocommerce->session->discount_saphali_shop);
						foreach($woocommerce->cart->applied_coupons as $key => $_code) {
							if($code == $_code) $index = $key;
						}
						if ( strstr( $discount[$_k], '%' ) )  $_discount_str = $discount[$_k];
						 else  $_discount_str = $this->wc_price($discount[$_k]);
						//$this->messege_code = array(sprintf(__( 'Ваша накопительная скидка по всем Вашим заказам составила %s', 'saphali-discount' ), $_discount_str) ) + $this->messege_code;
						//add_filter("woocommerce_add_message", array($this,"woocommerce_add_message"), 10 , 1);
						
						$coupon = new WC_Coupon($code);
						$individual_use = method_exists($coupon, 'get_individual_use') ? $coupon->get_individual_use() : $coupon->individual_use;
						if ( $individual_use == 'yes' ) {
							unset($woocommerce->session->discount_saphali_shop);
							
							$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по всем Вашим заказам составила %s', 'saphali-discount' ), $_discount_str) . __(' <br />В заказе используется только эта скидка.', 'saphali-discount') ); 
						} else {
							
							$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по всем Вашим заказам составила %s', 'saphali-discount' ), $_discount_str) );
							
						}
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
						   $_SESSION['discount_saphali_shop'][$index] = $discount[$_k];
						} else {
							if(empty($woocommerce->session->discount_saphali_shop)) $woocommerce->session->discount_saphali_shop = array();
							$session = $woocommerce->session->discount_saphali_shop;
							$session = $session + array($index => $discount[$_k]);
							$woocommerce->session->discount_saphali_shop = $session;
						}
					} elseif(  in_array($code, $woocommerce->cart->applied_coupons ) && isset($discount[$_k]) &&!empty($discount[$_k])) {
						foreach($woocommerce->cart->applied_coupons as $key => $_code) {
							if($code == $_code) $index = $key;
						}
						if ( strstr( $discount[$_k], '%' ) )  $_discount_str = $discount[$_k];
						 else  $_discount_str = $this->wc_price($discount[$_k]);
						 if(!is_object($coupon)) {
							$coupon = new WC_Coupon($code);
							$id = method_exists($coupon, 'get_id') ? $coupon->get_id() : $coupon->id;
							$this->info_cart_checkout = get_post_meta( $id, 'info_cart_checkout', true );
						}
						if( $this->info_cart_checkout == 'yes' ) {
							$individual_use = method_exists($coupon, 'get_individual_use') ? $coupon->get_individual_use() : $coupon->individual_use;
							if ( $individual_use == 'yes' ) {
								if ( !version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) unset($woocommerce->session->discount_saphali_shop);
								else unset( $_SESSION['discount_saphali_shop'] );
								$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по всем Вашим заказам составила %s', 'saphali-discount' ), $_discount_str) . __(' <br />В заказе используется только эта скидка.', 'saphali-discount') );
							} else {
								
								$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по всем Вашим заказам составила %s', 'saphali-discount' ), $_discount_str) );
								
							}
							if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
							   $_SESSION['discount_saphali_shop'][$index] = $discount[$_k];
							} else {
								if(empty($woocommerce->session->discount_saphali_shop)) $woocommerce->session->discount_saphali_shop = array();
								$session = $woocommerce->session->discount_saphali_shop;
								$session = $session + array($index => $discount[$_k]);
								$woocommerce->session->discount_saphali_shop = $session;
							}
						}
					}
					unset($coupon);
					$this->info_cart_checkout = false;
				}
			}  else {
			
				  foreach($coupon_code as $_k => $code) {
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
							foreach($woocommerce->cart->applied_coupons as $key => $_code) {
								if($code == $_code) {
									$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка (%s) по всем Вашим заказам аннулирована', 'saphali-discount' ), $_SESSION['discount_saphali_shop'][$key] ) );
									unset($_SESSION['discount_saphali_shop'][$key]); 
									unset($woocommerce->cart->applied_coupons[$key]);
								}
							}
							$_SESSION['coupons'] = $woocommerce->cart->applied_coupons;
						} else {
							foreach($woocommerce->cart->applied_coupons as $key => $_code) {
								if($code == $_code) {
									$d = isset($woocommerce->session->discount_saphali_shop[$key]) ? '('. $woocommerce->session->discount_saphali_shop[$key] . ')' : '' ;
									$this->comp_woocomerce_mess(  sprintf(__( 'Ваша накопительная скидка %s по всем Вашим заказам аннулирована', 'saphali-discount' ), $d ) );
									if(empty($woocommerce->session->discount_saphali_shop)) $woocommerce->session->discount_saphali_shop = array();
									$session = $woocommerce->session->discount_saphali_shop;
									unset($session[$key]);
									$woocommerce->session->discount_saphali_shop = $session;
									unset($woocommerce->cart->applied_coupons[$key]);
								}
								
							}
							$woocommerce->session->coupon_codes = $woocommerce->cart->applied_coupons;
						}
					}
				}
		}
		
		//$woocommerce->cart->remove_coupons( 2 );
		if($action_ac) {
			if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) $woocommerce->clear_messages();
			else
			wc_clear_notices();
		}
	  }
	  remove_filter( 'posts_where', array($this, 'filter_where') );
	  remove_action( 'parse_query', array( $this, 'parse_query' ), 5 );
	}
	
	public function add_discount( $coupon_code ) {
		global $woocommerce;

		// Coupons are globally disabled
		if( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
			if ( get_option('woocommerce_enable_coupons') == 'no' ) return false;
		} elseif( version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) {
		
			if ( ! $woocommerce->cart->coupons_enabled() )
			return false;
		// Sanitize coupon code
		$coupon_code = apply_filters( 'woocommerce_coupon_code', $coupon_code );
		} else {
			if ( ! wc_coupons_enabled() ) {
				return false;
			}
			$coupon_code = wc_format_coupon_code( $coupon_code );
		}

		// Get the coupon
		$the_coupon = new WC_Coupon( $coupon_code );
		
		if ( !version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) && $the_coupon->get_code() !== $coupon_code ) {
			$the_coupon->set_code( $coupon_code );
			$the_coupon->add_coupon_message( WC_Coupon::E_WC_COUPON_NOT_EXIST );
			return false;
		}
		$id = method_exists($the_coupon, 'get_id') ? $the_coupon->get_id() : $the_coupon->id;
		if ( $id ) {

		// Check it can be used with cart
		if ( ! $this->is_valid($the_coupon) ) {
			$this->comp_woocomerce_mess_error( $the_coupon->get_error_message() );
			return false;
		}

		// Check if applied
		if ( $woocommerce->cart->has_discount( $coupon_code ) ) {
			if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) $this->comp_woocomerce_mess_error( __('Discount code already applied!', 'woocommerce') ); else $the_coupon->add_coupon_message( WC_Coupon::E_WC_COUPON_ALREADY_APPLIED );
			return false;
		}
		$individual_use = method_exists($the_coupon, 'get_individual_use') ? $the_coupon->get_individual_use() : $the_coupon->individual_use;
		// If its individual use then remove other coupons
		if ( $individual_use == 'yes' ) {
			$woocommerce->cart->applied_coupons = apply_filters( 'woocommerce_apply_individual_use_coupon', array(), $the_coupon, $woocommerce->cart->applied_coupons );
		}

		if ( $woocommerce->cart->applied_coupons ) {
			foreach ( $woocommerce->cart->applied_coupons as $code ) {

			$existing_coupon = new WC_Coupon( $code );
			$individual_use = method_exists($existing_coupon, 'get_individual_use') ? $existing_coupon->get_individual_use() : $existing_coupon->individual_use;
			if ( $individual_use == 'yes' && false === apply_filters( 'woocommerce_apply_with_individual_use_coupon', false, $the_coupon, $existing_coupon, $woocommerce->cart->applied_coupons ) ) {

				// Reject new coupon
				// if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) { $this->comp_woocomerce_mess_error( sprintf( __( 'Sorry, coupon "%s" has already been applied and cannot be used in conjunction with other coupons.', 'woocommerce' ), '' ) ); $woocommerce->cart->applied_coupons = array(); } else $existing_coupon->add_coupon_message( WC_Coupon::E_WC_COUPON_ALREADY_APPLIED_INDIV_USE_ONLY );
					
				return false;
			}
			}
		}

		$woocommerce->cart->applied_coupons[] = $coupon_code;
		if( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
			$woocommerce->cart->set_session();
		} else {
			// Choose free shipping
			if ( version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) && $the_coupon->enable_free_shipping() || !version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) && $the_coupon->get_free_shipping() ) {
				$woocommerce->session->chosen_shipping_method = 'free_shipping';
			}

			$woocommerce->cart->calculate_totals();
		}

		if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) $this->comp_woocomerce_mess( __('Discount code applied successfully.', 'woocommerce') ); else $the_coupon->add_coupon_message( WC_Coupon::WC_COUPON_SUCCESS );

		do_action( 'woocommerce_applied_coupon', $coupon_code );

		return true;

		} else {
			if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) $this->comp_woocomerce_mess_error( __('Coupon does not exist!', 'woocommerce') ); else $the_coupon->add_coupon_message( WC_Coupon::E_WC_COUPON_NOT_EXIST );
		return false;
		}
		return false;
	}
	public function get_product_from_item( $item ) {
		if ( ! empty( $item['variation_id'] ) && 'product_variation' === get_post_type( $item['variation_id'] ) ) {
			$_product = wc_get_product( $item['variation_id'] );
		} elseif ( ! empty( $item['product_id']  ) ) {
			$_product = wc_get_product( $item['product_id'] );
		} else {
			$_product = false;
		}

		return apply_filters( 'woocommerce_get_product_from_item_this', $_product, $item );
	}
	public function is_valid($coupon) {
		global $woocommerce;

		$error_code = null;
		$valid      = true;
		$error      = false;
		$id = method_exists($coupon, 'get_id') ? $coupon->get_id() : $coupon->id;
		if ( $id ) {
			
			$coupone_customer_role = get_post_meta( $id, 'saphali_coupone_customer_role', true );
			$coupone_customer_no_role = get_post_meta( $id, 'saphali_coupone_customer_no_role', true );
			if ( !empty($coupone_customer_role) && is_array($coupone_customer_role) ) {
				$ob = wp_get_current_user();
				if (!( isset($ob->roles[0]) && in_array( $ob->roles[0], $coupone_customer_role) )) {
					$valid = false;
					$error_code = __('Вы не состоите в группе пользователей, для которой применим данный купон.', 'saphali-discount');
				}
			}
			if ( !empty($coupone_customer_no_role) && is_array($coupone_customer_no_role) ) {
				$ob = wp_get_current_user();
				if (isset($ob->roles[0]) && in_array( $ob->roles[0], $coupone_customer_no_role)) {
					$valid = false;
					$error_code = __('Вы не можете воспользоваться данным купоном.', 'saphali-discount');
					
				}
			}
			// Usage Limit
			$usage_limit = method_exists($coupon, 'get_usage_limit') ? $coupon->get_usage_limit() : $coupon->usage_limit;
			if ( $usage_limit > 0 ) {
				$usage_count = method_exists($coupon, 'get_usage_count') ? $coupon->get_usage_count() : $coupon->usage_count;
				if ( $usage_count >= $usage_limit ) {
					$valid = false;
					if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) $error_code = __( 'Coupon usage limit has been reached.', 'woocommerce' ); else $error_code = WC_Coupon::E_WC_COUPON_USAGE_LIMIT_REACHED;
				}
			}

			// Expired
			
			$expiry_date = method_exists($coupon, 'get_date_expires') ? $coupon->get_date_expires() : $coupon->expiry_date;
			if ( $expiry_date ) {
				if( !version_compare( WOOCOMMERCE_VERSION, '3.0', '<' ) ) {
					$expiry_date = $expiry_date->getTimestamp();
				} 
				if ( current_time( 'timestamp' ) > $expiry_date ) {
					$valid = false;
					if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) $error_code = __( 'This coupon has expired.', 'woocommerce' ); else $error_code = WC_Coupon::E_WC_COUPON_EXPIRED;
				}
			}

			// Minimum spend
			$minimum_amount = method_exists($coupon, 'get_minimum_amount') ? $coupon->get_minimum_amount() : $minimum_amount;
			if ( $minimum_amount > 0 ) {
				if ( $minimum_amount > $woocommerce->cart->subtotal ) {
					$valid = false;
					if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) $error_code = sprintf( __( 'The minimum spend for this coupon is %s.', 'woocommerce' ), $minimum_amount ); else $error_code = WC_Coupon::E_WC_COUPON_MIN_SPEND_LIMIT_NOT_MET;
				}
			}

			// Product ids - If a product included is found in the cart then its valid
			$product_ids = method_exists($coupon, 'get_product_ids') ? $coupon->get_product_ids() : $coupon->product_ids;
			if ( sizeof( $product_ids ) > 0 ) {
				$valid_for_cart = false;
				if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
					foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {

						if ( !(isset($cart_item['variation_id']) && $cart_item['variation_id']>0) && in_array( $cart_item['product_id'], $product_ids ) || in_array( $cart_item['variation_id'], $product_ids ) || in_array( $cart_item['data']->get_parent(), $product_ids ) )
							$valid_for_cart = true;
					}
				}
				if ( ! $valid_for_cart ) {
					$valid = false;
					if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
				}
			}
			
			// Category ids - If a product included is found in the cart then its valid
			$product_categories = method_exists($coupon, 'get_product_categories') ? $coupon->get_product_categories() : $coupon->product_categories;
			if ( sizeof( $product_categories ) > 0 ) {
				$exclude_revers_items_product = (get_post_meta( $id, 'exclude_revers_items_product', true ) == 'yes') ? true : false;
				if($exclude_revers_items_product) {
					$valid_for_cart = false;
					if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
						foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {

							$product_cats = wp_get_post_terms($cart_item['product_id'], 'product_cat', array("fields" => "ids"));

							if ( sizeof( array_intersect( $product_cats, $product_categories ) ) > 0 ) {
									$valid_for_cart = true; break;
								}
						}
					}
					if ( ! $valid_for_cart ) {
						$valid = false;
						if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
					}
				} else {
					$valid_for_cart = false;
					if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
						foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {

							$product_cats = wp_get_post_terms($cart_item['product_id'], 'product_cat', array("fields" => "ids"));

							if ( sizeof( array_intersect( $product_cats, $product_categories ) ) > 0 )
								$valid_for_cart = true;
						}
					}
					if ( ! $valid_for_cart ) {
						$valid = false;
						if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
					}
				}
			}

			// Cart discounts cannot be added if non-eligble product is found in cart
			$type = method_exists($coupon, 'get_discount_type') ? $coupon->get_discount_type() : $coupon->type;
			if ( $type != 'fixed_product' && $type != 'percent_product' ) {

				// Exclude Products
				$exclude_product_ids = method_exists($coupon, 'get_excluded_product_ids') ? $coupon->get_excluded_product_ids() : $coupon->exclude_product_ids;
				if ( sizeof( $exclude_product_ids ) > 0 ) {
					$i_on_s = array_search(0, $exclude_product_ids);
					if($i_on_s !== false) unset($exclude_product_ids[$i_on_s]);
					
					$exclude_revers_items_product = ( get_post_meta( $id, 'exclude_revers_items_product', true ) == 'yes' ) ? true : false;
					if($exclude_revers_items_product) {
						$valid_for_cart = true;
						if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
							foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
								if ( in_array( $cart_item['product_id'], $exclude_product_ids ) || in_array( $cart_item['variation_id'], $exclude_product_ids ) || in_array( $cart_item['data']->get_parent(), $exclude_product_ids ) ) {
									$valid_for_cart = false;
								} else {$valid_for_cart = true; break;}
							}
						}
						
						if ( ! $valid_for_cart ) {
							$valid = false;
							if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
						}
					} else {
						$valid_for_cart = true;
						if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
							foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
								if ( in_array( $cart_item['product_id'], $exclude_product_ids ) || in_array( $cart_item['variation_id'], $exclude_product_ids ) || in_array( $cart_item['data']->get_parent(), $exclude_product_ids ) ) {
									$valid_for_cart = false;
								}
							}
						}
						if ( ! $valid_for_cart ) {
							$valid = false;
							if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
						}
					}
				}

				// Exclude Sale Items
				$exclude_sale_items = method_exists($coupon, 'get_exclude_sale_items') ? $coupon->get_exclude_sale_items() : $coupon->exclude_sale_items;
				if ( $exclude_sale_items == 'yes' ) {
					if(!isset($exclude_revers_items_product) ) $exclude_revers_items_product = ( get_post_meta( $id, 'exclude_revers_items_product', true ) == 'yes' ) ? true : false;
					if($exclude_revers_items_product) {
						$valid_for_cart = true;
						$product_ids_on_sale = woocommerce_get_product_ids_on_sale();
						if(is_null($product_ids_on_sale)) $product_ids_on_sale = array();
						
						$i_on_s = array_search(0, $product_ids_on_sale);
						if($i_on_s !== false) unset($product_ids_on_sale[$i_on_s]);
						if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
							foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
								if ( !(isset($cart_item['variation_id']) && $cart_item['variation_id']>0) && in_array( $cart_item['product_id'], $product_ids_on_sale, true ) || in_array( $cart_item['variation_id'], $product_ids_on_sale, true ) || in_array( $cart_item['data']->get_parent(), $product_ids_on_sale, true ) ) {
									$valid_for_cart = false;
								} else { $valid_for_cart = true; break;}
							}
						}
						if ( ! $valid_for_cart ) {
							$valid = false;
							if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not valid for sale items.', 'woocommerce' ); } else $error_code = WC_Coupon::E_WC_COUPON_NOT_VALID_SALE_ITEMS;
						}
					} else {
						$valid_for_cart = true;
						$product_ids_on_sale = woocommerce_get_product_ids_on_sale();
						if(is_null($product_ids_on_sale)) $product_ids_on_sale = array();
						$i_on_s = array_search(0, $product_ids_on_sale);
						if($i_on_s !== false) unset($product_ids_on_sale[$i_on_s]);
						if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
							foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
								if ( !(isset($cart_item['variation_id']) && $cart_item['variation_id']>0) && in_array( $cart_item['product_id'], $product_ids_on_sale, true ) || in_array( $cart_item['variation_id'], $product_ids_on_sale, true ) || in_array( $cart_item['data']->get_parent(), $product_ids_on_sale, true ) ) {
									$valid_for_cart = false;
								}
							}
						}
						if ( ! $valid_for_cart ) {
							$valid = false;
							if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not valid for sale items.', 'woocommerce' ); } else $error_code = WC_Coupon::E_WC_COUPON_NOT_VALID_SALE_ITEMS;
						}
					}
				}

				// Exclude Categories
				$exclude_product_categories = method_exists($coupon, 'get_excluded_product_categories') ? $coupon->get_excluded_product_categories() : $coupon->exclude_product_categories;
				if ( sizeof( $exclude_product_categories ) > 0 ) {
					$exclude_revers_items_product = (get_post_meta( $id, 'exclude_revers_items_product', true ) == 'yes') ? true : false;
					if($exclude_revers_items_product) {
						$valid_for_cart = true;
						if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
							foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {

								$product_cats = wp_get_post_terms( $cart_item['product_id'], 'product_cat', array( "fields" => "ids" ) );

								if ( sizeof( array_intersect( $product_cats, $exclude_product_categories ) ) > 0 ) {
									$valid_for_cart = false;
								} else {$valid_for_cart = true; break;}
							}
						}
						if ( ! $valid_for_cart ) {
							$valid = false;
							if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
						}
					} else {
						$valid_for_cart = true;
						if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
							foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {

								$product_cats = wp_get_post_terms( $cart_item['product_id'], 'product_cat', array( "fields" => "ids" ) );

								if ( sizeof( array_intersect( $product_cats, $exclude_product_categories ) ) > 0 )
									$valid_for_cart = false;
							}
						}
						if ( ! $valid_for_cart ) {
							$valid = false;
							if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Sorry, this coupon is not applicable to your cart contents.', 'woocommerce' ); } else $error_code = WC_Coupon::E_WC_COUPON_NOT_APPLICABLE;
						}					
					}
				}
			}

			$valid = apply_filters( 'woocommerce_coupon_is_validate', $valid, $coupon );
			$this->is_valid_r[$id] = $valid;
			if ( $valid ) {
				return true;
			} else {
				if ( is_null( $error_code ) )
					if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Coupon is not valid.', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_INVALID_FILTERED;
			}

		} else {
			if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' )) {$error_code = __( 'Invalid coupon', 'woocommerce' );} else $error_code = WC_Coupon::E_WC_COUPON_NOT_EXIST;
		}

		if ( $error_code ) {
			if(version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ))
			return new WP_Error( 'coupon_error', apply_filters( 'woocommerce_coupon_error_end', $error_code, $coupon ) );
			else 
			$coupon->error_message = $coupon->get_coupon_error( $error_code );
		}

		return false;
	}
	
	function cart_apry_discount_t_cart() {
	global $woocommerce;
	 if( (is_cart() || is_checkout() || (isset($_POST['action']) && $_POST['action'] == 'woocommerce_update_order_review' || isset($_GET['wc-ajax']) && $_GET['wc-ajax'] == 'update_order_review' ) ) && empty($_POST['coupon_code']) ) {
		 
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) $woocommerce->nocache();
		$coupons = get_posts(array('post_type' => 'shop_coupon', 'post_status' => 'publish', 'meta_key' => 'discount_type', 'meta_value' => 'fixed_total_cart', 'posts_per_page' => -1));
		$revers_items_product = array();
		foreach($coupons as $rev_key => $_coupon) {
			$coupon_code[$rev_key] = $_coupon->post_title;
			$variant_discount[$rev_key] = get_post_meta( $_coupon->ID, 'variant_discount', true );
			$customer_email = get_post_meta( $_coupon->ID, 'customer_email', true );
			$customer_cart_club = get_post_meta( $_coupon->ID, 'customer_cart_club', true );
			
            $exclude_revers_items_product = ( get_post_meta( $_coupon->ID, 'exclude_revers_items_product', true ) == 'yes' ) ? true : false;
            $filya = true;
			if($filya) {
				$coupone_customer_role = get_post_meta( $_coupon->ID, 'saphali_coupone_customer_role', true );
				$coupone_customer_no_role = get_post_meta( $_coupon->ID, 'saphali_coupone_customer_no_role', true );
				if ( !empty($coupone_customer_role) && is_array($coupone_customer_role) ) {
                    $ob = is_object($ob) ? $ob : wp_get_current_user();
					if (!( isset($ob->roles[0]) && in_array( $ob->roles[0], $coupone_customer_role) )) {
						$remove_coupon_code[] = $_coupon->post_title;
						$filya = false;
					}
				}
				if ( $filya && !empty($coupone_customer_no_role) && is_array($coupone_customer_no_role) ) {
					$ob = is_object($ob) ? $ob : wp_get_current_user();
					if (isset($ob->roles[0]) && in_array( $ob->roles[0], $coupone_customer_no_role)) {
						$remove_coupon_code[] = $_coupon->post_title;
					}
				}
			}
			$revers_items_product[$rev_key] = 0;
			if($exclude_revers_items_product) {
				if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
						$exclude_product_ids = method_exists($_coupon, 'get_excluded_product_ids') ? $_coupon->get_excluded_product_ids() : $_coupon->exclude_product_ids;
                        $exclude_product_categories = method_exists($_coupon, 'get_excluded_product_categories') ? $_coupon->get_excluded_product_categories() : $_coupon->exclude_product_categories;
						if(!is_array($exclude_product_ids) ) $exclude_product_ids = explode(',', $exclude_product_ids );
						$exclude_product_ids = array_map('trim',$exclude_product_ids);
						if($exclude_product_ids)
						$exclude_product_ids = array_filter($exclude_product_ids);
						if($_coupon->exclude_sale_items == 'yes') 
							$product_ids_on_sale = woocommerce_get_product_ids_on_sale();
						
						if(is_null($product_ids_on_sale)) $product_ids_on_sale = array();
						$i_on_s = array_search(0, $product_ids_on_sale);
						if($i_on_s !== false) unset($product_ids_on_sale[$i_on_s]);
						foreach( $woocommerce->cart->get_cart() as $cart_item_key => $cart_item ) {
							$ex = 1;
							if ( is_array($exclude_product_ids)  && (in_array( $cart_item['product_id'], $exclude_product_ids ) || in_array( $cart_item['variation_id'], $exclude_product_ids ) || in_array( $cart_item['data']->get_parent(), $exclude_product_ids ) ) ) {
								$revers_items_product[$rev_key] = $revers_items_product[$rev_key] + ($cart_item['data']->get_price() * $cart_item['quantity']);
								$ex = 0;
							} elseif(is_array($exclude_product_ids) && !empty($exclude_product_ids) ) {
								$product_cats = wp_get_post_terms( $cart_item['product_id'], 'product_cat', array( "fields" => "ids" ) );
								if( sizeof( array_intersect( $product_cats, $exclude_product_ids ) ) > 0 ) {
									$revers_items_product[$rev_key] = $revers_items_product[$rev_key] + ($cart_item['data']->get_price() * $cart_item['quantity']);
									$ex = 0;
								}
							} elseif ( is_array($exclude_product_categories) && sizeof( $exclude_product_categories ) > 0 ) {
                                $product_cats = wp_get_post_terms( $cart_item['product_id'], 'product_cat', array( "fields" => "ids" ) );
            
                                if ( is_array(array_intersect( $product_cats, $exclude_product_categories )) && sizeof( array_intersect( $product_cats, $exclude_product_categories ) ) > 0 ) {
                                    $revers_items_product[$rev_key] = $revers_items_product[$rev_key] + ($cart_item['data']->get_price() * $cart_item['quantity']);
									$ex = 0;
                                }
                                
							}
							if ( $ex && !(isset($cart_item['variation_id']) && $cart_item['variation_id']>0) && in_array( $cart_item['product_id'], $product_ids_on_sale, true ) || in_array( $cart_item['variation_id'], $product_ids_on_sale, true ) || in_array( $cart_item['data']->get_parent(), $product_ids_on_sale, true ) ) {
								$revers_items_product[$rev_key] = $revers_items_product[$rev_key] + ($cart_item['data']->get_price() * $cart_item['quantity']);
							}
						}
                }
                if(isset($_COOKIE['deb'])) {
                    // var_dump( $revers_items_product, $_coupon->post_title, "LINE: " . __LINE__);
                }
			}
        }
        if(isset($coupon_code))
		foreach($coupon_code as $r_key => $r_value) {
			if(isset($remove_coupon_code) && in_array($r_value, $remove_coupon_code)) {
				unset($coupon_code[$r_key], $variant_discount[$r_key]);
			} 
		}
		
		if(!empty($coupon_code)) {
			//fix wpml
			$coupon_code = array_unique($coupon_code);
			$add_coupon = true;
			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				if(!empty($customer_email)) {
					$add_coupon = false;
				} elseif(!empty($customer_cart_club)) {
					$add_coupon = false;
				}
				$check_emails[] = $current_user->user_email;
				$check_emails[] = get_user_meta($current_user->ID, 'billing_email', true);

				$check_emails = array_unique($check_emails);
				
				if(is_array($check_emails)) {
					foreach($check_emails as $user_email) {
						if(!empty($customer_email)) {
							if(!empty($user_email))
							if(in_array( $user_email, $customer_email ))
							{
								$add_coupon = true;
								break;
							}
						} else {

						}
					}
				}
				if(!$add_coupon) {
					
					$check_ps[] = get_user_meta($current_user->ID, 'billing_cart_club', true);
					if(isset($_POST['post_data'])) {
						 parse_str ($_POST['post_data'], $post_data);
						  if(isset($post_data["billing_cart_club"])) {
							$post_data["billing_cart_club"] = str_replace( array('(',')',' ', '-'), '', $post_data["billing_cart_club"] ) ;
							$check_ps[] = $post_data["billing_cart_club"];
							if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								$_SESSION['d_phone'] = $post_data["billing_cart_club"];
							} else {
								$woocommerce->session->d_phone = $post_data["billing_cart_club"];
							}
						 }
					} elseif ( is_cart() ) {
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
						if(isset($_SESSION['d_phone']))
						$post_data["billing_cart_club"] = $_SESSION['d_phone'];
						} else {
							if(isset($woocommerce->session->d_phone))
								$post_data["billing_cart_club"] = $woocommerce->session->d_phone;
						}
						if(isset($post_data["billing_cart_club"]))
						$check_ps[] = $post_data["billing_cart_club"];
					}
					$check_ps = array_unique($check_ps);
					
					if(is_array($check_ps)) {
						foreach($check_ps as $user_p) {
							if(!empty($customer_cart_club)) {
								if(!empty($user_p))
								if(in_array( $user_p, $customer_cart_club ))
								{
									$add_coupon = true;
									break;
								}
								$b = 0;
								foreach($customer_cart_club as $phone) {
									if( strpos( $phone, str_replace('+', '', $user_p) ) !== false && mb_strlen($user_p, 'utf-8') > 7) {
										$add_coupon = true;
										$b = 1; break;
									}
								}
								if($b) break;
							} else {

							}
						}
					}
				}
			} else {
				if(!empty($customer_email)) {
					$add_coupon = false;
				}
				if(!empty($customer_cart_club)) {
					$add_coupon = false;
					$check_ps = array();
					if(isset($_POST['post_data'])) {
						 parse_str ($_POST['post_data'], $post_data);
						 if(isset($post_data["billing_cart_club"])) {
							$post_data["billing_cart_club"] = str_replace( array('(',')',' ', '-'), '', $post_data["billing_cart_club"] ) ;
							$check_ps[] = $post_data["billing_cart_club"];
							if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								$_SESSION['d_phone'] = $post_data["billing_cart_club"];
							} else {
								$woocommerce->session->d_phone = $post_data["billing_cart_club"];
							}
						 }
					 } elseif ( is_cart() ) {
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
						if(isset($_SESSION['d_phone']))
						$post_data["billing_cart_club"] = $_SESSION['d_phone'];
						} else {
							if(isset($woocommerce->session->d_phone))
								$post_data["billing_cart_club"] = $woocommerce->session->d_phone;
						}
						if(isset($post_data["billing_cart_club"]))
						$check_ps[] = $post_data["billing_cart_club"];
					}
					$check_ps = array_unique($check_ps);
					if($check_ps) {
						foreach($check_ps as $user_p) {
							if(!empty($customer_cart_club)) {
								if(!empty($user_p)) {
									if( in_array( $user_p, $customer_cart_club ) )
									{
										$add_coupon = true;
										break;
									}
									$b = 0;
									foreach($customer_cart_club as $phone) {
										if( strpos( $phone, str_replace('+', '', $user_p) ) !== false && mb_strlen($user_p, 'utf-8') > 7) {
											$add_coupon = true;
											$b = 1; break;
										}
									}
									if($b) break;	
								}
								
							} else {

							}
						}
					}
				}
			}
			if( $add_coupon  ) {
				$discount = array();
				if(is_array($variant_discount)) {
					$woocommerce->cart->calculate_totals();
					foreach($variant_discount as $key => $_variant_discount) {
						if(DISCOUNT_NO_EXC_PROD_SUMM_IN_REVERS) {
							$revers_items_product[$key]	= 0;
						}
						$cart_contents_total = $woocommerce->cart->subtotal - $revers_items_product[$key];
						$cart_contents_total = $cart_contents_total / $this->compatibility_currency_Aelia();
						$cart_contents_total = apply_filters( 'WOOMULTI_CURRENCY_R', $cart_contents_total );
						if(isset($_COOKIE['saphali'])) var_dump($cart_contents_total, $woocommerce->cart->subtotal , $revers_items_product[$key],  __LINE__);
						foreach($_variant_discount['min'] as $_key => $_discount) {
							if( $cart_contents_total >= $_discount && $cart_contents_total <= $variant_discount[$key]['max'][$_key] ) {
								$discount[$key] = $variant_discount[$key]['discount'][$_key];
							}
                        }
					}
				}
			}
			if( $add_coupon && $discount  ) {
				foreach($woocommerce->cart->applied_coupons as $_c_ )
				$coupon__ = new WC_Coupon( $_c_ );
				if(isset($coupon__) && is_object($coupon__)) {
					$type = method_exists($coupon__, 'get_discount_type') ? $coupon__->get_discount_type() : $coupon__->type;
					
					if( !( "fixed_total_shop" == $type || "fixed_total_cart" == $type ) ) {
						$individual_use = method_exists($coupon__, 'get_individual_use') ? $coupon__->get_individual_use() : $coupon__->individual_use;
						if( $individual_use == 'yes' ) return;
					}
						
				}
				
				
				foreach($coupon_code as $_k => $code) {
					foreach($variant_discount[$_k]["discount"] as $num_key => $d_value ) {
						if( isset($discount[$_k]) && $d_value == $discount[$_k] ) {
							$numb_ind = $num_key; break;
						}
					}
					if(empty($discount[$_k])) {
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
							foreach($woocommerce->cart->applied_coupons as $key => $_code) {
								if($code == $_code) {
									$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка (%s) по текущему заказу аннулирована', 'saphali-discount' ), $_SESSION['discount_saphali'][$key] ) );
									unset( $_SESSION['discount_saphali'][$key], $_SESSION['discount_saphali_next'][$key] , $_SESSION['global_discount_saphali'] ); 
									unset($woocommerce->cart->applied_coupons[$key]);
									
								}
							}
							$_SESSION['coupons'] = $woocommerce->cart->applied_coupons;
						} else {
							foreach($woocommerce->cart->applied_coupons as $key => $_code) {
								if($code == $_code) {
									
									$this->comp_woocomerce_mess(  sprintf(__( 'Ваша накопительная скидка (%s) по текущему заказу аннулирована', 'saphali-discount' ), $woocommerce->session->discount_saphali[$key] ) );
									
									if(empty($woocommerce->session->discount_saphali)) $woocommerce->session->discount_saphali = array();
									if(empty($woocommerce->session->discount_saphali_next)) $woocommerce->session->discount_saphali_next = array();
									$session = $woocommerce->session->discount_saphali;
									$session_next = $woocommerce->session->discount_saphali_next;
									unset($session[$key],$session_next[$key] , $woocommerce->session->global_discount_saphali, $woocommerce->session->discount_saphali_hide_next , $woocommerce->session->discount_saphali_hide );
									$woocommerce->session->discount_saphali = $session;
									$woocommerce->session->discount_saphali_next = $session_next;
									unset($woocommerce->cart->applied_coupons[$key]);
								}
							}
							$woocommerce->session->coupon_codes = $woocommerce->cart->applied_coupons;
						}
						continue;
					}
					if(isset($woocommerce->cart->coupon_discount_amounts[$code]) && !isset($woocommerce->cart->applied_coupons[$_k]) )
						$woocommerce->cart->applied_coupons[$_k] = $code;
					$woocommerce->cart->applied_coupons = array_unique($woocommerce->cart->applied_coupons);
					sort($woocommerce->cart->applied_coupons);
					if(is_array($woocommerce->cart->applied_coupons)) {
						$status_add_code = false;
						if( !in_array($code, $woocommerce->cart->applied_coupons)   ) {
							$__coupon = new WC_Coupon( $code );
							if($this->is_valid($__coupon))
							$status_add_code = $this->add_discount( $code );
						} else {
							
							if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								
								foreach($woocommerce->cart->applied_coupons as $key => $_code) {
									if($code == $_code) {  
										if($discount[$_k] != $_SESSION['discount_saphali'][$key] ) {
											$coupon = new WC_Coupon($code);
											$coupon_id = method_exists($coupon, 'get_id') ? $coupon->get_id() : $coupon->id;
											$this->info_cart_checkout = get_post_meta( $$coupon_id, 'info_cart_checkout', true );
											$this->info_cart_checkout_all = get_post_meta( $$coupon_id, 'info_cart_checkout_all', true );
											if ( strstr( $discount[$_k], '%' ) )  $_discount_str = $discount[$_k];
												else  $_discount_str = $this->wc_price($discount[$_k]);
											
											if($this->info_cart_checkout_all == 'yes')
											$_SESSION['global_discount_saphali']= $_discount_str;
											else {
												if(isset($_SESSION['global_discount_saphali']) ) unset($_SESSION['global_discount_saphali']);
											}
											if ( strstr( $_SESSION['discount_saphali'][$key], '%' ) )  $_discount_strs = $_SESSION['discount_saphali'][$key];
												else  $_discount_strs = $this->wc_price($_SESSION['discount_saphali'][$key]);
											if($this->info_cart_checkout != 'yes') {
												$this->comp_woocomerce_mess( sprintf(__( 'Изменена накопительная скидка с %s на %s.', 'saphali-discount' ), $_discount_strs , $_discount_str ) );
												$this->edit_disc = true;
											}
											else {
												
												$this->comp_woocomerce_mess( sprintf(__( 'Изменена накопительная скидка с %s на %s.', 'saphali-discount' ), $_discount_strs , $_discount_str ) );
												
												$this->edit_disc = true;
											}
											unset($_SESSION['discount_saphali_hide_next'] , $_SESSION['discount_saphali_hide'] );
										}
										$_SESSION['discount_saphali'][$key] = $discount[$_k]; 
										if( isset( $variant_discount[$_k]["discount"][$numb_ind+1] ) ) 
										$_SESSION['discount_saphali_next'][$key] = $variant_discount[$_k]["discount"][$numb_ind+1]; 
									}
								}
							} else {

								foreach($woocommerce->cart->applied_coupons as $key => $_code) {
									if($code == $_code) {  
									
										if($discount[$_k] != $woocommerce->session->discount_saphali[$key] ) {
											$coupon = new WC_Coupon($code);
											$coupon_id = method_exists($coupon, 'get_id') ? $coupon->get_id() : $coupon->id;
											$this->info_cart_checkout = get_post_meta( $coupon_id, 'info_cart_checkout', true );
											$this->info_cart_checkout_all = get_post_meta( $coupon_id, 'info_cart_checkout_all', true );
											if ( strstr( $discount[$_k], '%' ) )  $_discount_str = $discount[$_k];
												else  $_discount_str = $this->wc_price($discount[$_k]);
											if($this->info_cart_checkout_all == 'yes')
											$woocommerce->session->global_discount_saphali = $_discount_str;
											else {
												if(isset($woocommerce->session->global_discount_saphali) ) unset($woocommerce->session->global_discount_saphali);
											}
											
											if ( strstr( $woocommerce->session->discount_saphali[$key], '%' ) )  $_discount_strs = $woocommerce->session->discount_saphali[$key];
												else  $_discount_strs = $this->wc_price($woocommerce->session->discount_saphali[$key]);
												
											if($this->info_cart_checkout != 'yes') {
												$this->comp_woocomerce_mess( sprintf(__( 'Изменена накопительная скидка с %s на %s.', 'saphali-discount' ), $_discount_strs , $_discount_str) );
												$this->edit_disc = true;
											}
											else {
												$this->comp_woocomerce_mess( sprintf(__( 'Изменена накопительная скидка с %s на %s.', 'saphali-discount' ), $_discount_strs , $_discount_str) );
												$this->edit_disc = true;
											}
											unset($woocommerce->session->discount_saphali_hide_next , $woocommerce->session->discount_saphali_hide );
										}
										if(empty($woocommerce->session->discount_saphali)) $woocommerce->session->discount_saphali = array();
										if(empty($woocommerce->session->discount_saphali_next)) $woocommerce->session->discount_saphali_next = array();
										$session = $woocommerce->session->discount_saphali;
										$session[$key] = $discount[$_k];
										$woocommerce->session->discount_saphali = $session;
										
										$session_next = $woocommerce->session->discount_saphali_next;
										$session_next = isset($variant_discount[$_k]["discount"][$numb_ind+1]) ?  array($key => $variant_discount[$_k]["discount"][$numb_ind+1]) + $session_next : $session_next;
										$woocommerce->session->discount_saphali_next = $session_next;
									}
								}
								//echo '<br />'; var_dump($code, $woocommerce->cart->applied_coupons, $woocommerce->session->discount_saphali);
							}
						}
					} else {
							$__coupon = new WC_Coupon( $code );
							if($this->is_valid($__coupon))
							$status_add_code = $this->add_discount( $code );
							else $status_add_code = false;
					}
					if($status_add_code) {
						foreach($woocommerce->cart->applied_coupons as $key => $_code) {
							if($code == $_code) { $index = $key; if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) unset($_SESSION['discount_saphali_hide'], $_SESSION['discount_saphali_hide_next'] ); else unset($woocommerce->session->discount_saphali_hide,$woocommerce->session->discount_saphali_hide_next); }
						}
						
						if ( strstr( $discount[$_k], '%' ) )  $_discount_str = $discount[$_k];
						 else  $_discount_str = $this->wc_price($discount[$_k]);
						 
						//$this->messege_code = array(sprintf(__( 'Ваша накопительная скидка по текущему заказу составила %s', 'saphali-discount' ), $_discount_str) ) + $this->messege_code;
						//add_filter("woocommerce_add_message", array($this,"woocommerce_add_message"), 10 , 1);
						
						$coupon = new WC_Coupon($code);
						$coupon_id = method_exists($coupon, 'get_id') ? $coupon->get_id() : $coupon->id;
						$this->info_cart_checkout_all = get_post_meta( $coupon_id, 'info_cart_checkout_all', true );
						if ( $coupon->individual_use == 'yes' ) {
							unset($woocommerce->session->discount_saphali);
							$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по текущему заказу составила %s', 'saphali-discount' ), $_discount_str) . __(' <br />В заказе используется только эта скидка.', 'saphali-discount'). '<span class="close" title="Закрыть"> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
						} else {
							
							$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по текущему заказу составила %s', 'saphali-discount' ), $_discount_str). '<span class="close" title="Закрыть"> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
							
							
						}
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {

						   $_SESSION['discount_saphali'][$index] = $discount[$_k];
						   if($this->info_cart_checkout_all == 'yes')
						   $_SESSION['global_discount_saphali']= $_discount_str;
						   else {
									if(isset($_SESSION['global_discount_saphali']) ) unset($_SESSION['global_discount_saphali']);
								}
						} else {
							if(empty($woocommerce->session->discount_saphali)) $woocommerce->session->discount_saphali = array();
							if(empty($woocommerce->session->discount_saphali_next)) $woocommerce->session->discount_saphali_next = array();
							if($this->info_cart_checkout_all == 'yes')
							$woocommerce->session->global_discount_saphali = $_discount_str;
							else {
									if(isset($woocommerce->session->global_discount_saphali) ) unset($woocommerce->session->global_discount_saphali);
								}
							
							$session = $woocommerce->session->discount_saphali;
							$session = array($index => $discount[$_k]) + $session ;
							$woocommerce->session->discount_saphali = $session;
							
							$session_next = $woocommerce->session->discount_saphali_next;
							$session_next = isset($variant_discount[$_k]["discount"][$numb_ind+1]) ? array($index => $variant_discount[$_k]["discount"][$numb_ind+1]) + $session_next : $session_next;
							$woocommerce->session->discount_saphali_next = $session_next;
						}
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								if($_SESSION['discount_saphali'][$index] != $_SESSION['discount_saphali_next'][$index] && isset($_SESSION['discount_saphali_next'][$index]) && !(isset($_SESSION['discount_saphali_hide_next']) && $_SESSION['discount_saphali_hide_next'])  ) {
									if ( strstr( $_SESSION['discount_saphali_next'][$index], '%' ) )  $_discount_str_ = $_SESSION['discount_saphali_next'][$index];
										 else  $_discount_str_ = $this->wc_price($_SESSION['discount_saphali_next'][$index]);
										 $_SESSION['discount_saphali_next_return'] = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
									$this->comp_woocomerce_mess( $_SESSION['discount_saphali_next_return'] . '<span class="close" title="Закрыть "> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
									
								}							
						} else {
								while( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
									$numb_ind++;
								}
								
								if( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && !isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
									$no_next = false;
									$woocommerce->session->discount_saphali_next_return = '';
									if( isset( $woocommerce->session->discount_saphali_next[$index] ) ) {$ni = $woocommerce->session->discount_saphali_next; unset($ni[$index]); $woocommerce->session->discount_saphali_next = $ni;}
								} elseif( !isset($no_next) ) $no_next = true;
								if(  $no_next && isset($woocommerce->session->discount_saphali_next[$index]) && $woocommerce->session->discount_saphali[$index] != $woocommerce->session->discount_saphali_next[$index] && !(isset($woocommerce->session->discount_saphali_hide_next) && $woocommerce->session->discount_saphali_hide_next)  ) {
									if ( strstr( $woocommerce->session->discount_saphali_next[$index], '%' ) )  $_discount_str_ = $woocommerce->session->discount_saphali_next[$index];
										 else  $_discount_str_ = $this->wc_price($woocommerce->session->discount_saphali_next[$index]);
										$woocommerce->session->discount_saphali_next_return = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
									$this->comp_woocomerce_mess( $woocommerce->session->discount_saphali_next_return . '<span class="close" title="Закрыть "> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
								}
						}

					} elseif( in_array($code, $woocommerce->cart->applied_coupons ) && isset($discount[$_k]) &&!empty($discount[$_k])) {
						if ( strstr( $discount[$_k], '%' ) )  $_discount_str = $discount[$_k];
						 else  $_discount_str = $this->wc_price($discount[$_k]);
						$coupon = new WC_Coupon($code);
						$coupon_id = method_exists($coupon, 'get_id') ? $coupon->get_id() : $coupon->id;
						$this->info_cart_checkout = get_post_meta( $coupon_id, 'info_cart_checkout', true );
						$this->info_cart_checkout_all = get_post_meta( $coupon_id, 'info_cart_checkout_all', true );

						if( $this->info_cart_checkout == 'yes' && !$this->edit_disc ) {
							foreach($woocommerce->cart->applied_coupons as $key => $_code) {
								if($code == $_code) $index = $key;
							}						
							if ( $coupon->individual_use == 'yes' ) {
								unset($woocommerce->session->discount_saphali);
								
								if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								if(  !(isset($_SESSION['discount_saphali_hide']) && $_SESSION['discount_saphali_hide']) )
								$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по текущему заказу составила %s', 'saphali-discount' ), $_discount_str) . __(' <br />В заказе используется только эта скидка.', 'saphali-discount') . '<span class="close" title="Закрыть"> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
								
								   
								   
								   $_SESSION['discount_saphali'][$index] = $discount[$_k];
								   $_SESSION['discount_saphali_next'][$index] = ( isset($variant_discount[$_k]["discount"][$numb_ind+1])  ) ? $variant_discount[$_k]["discount"][$numb_ind+1] : '' ;
								} else {
								if(  !(isset($woocommerce->session->discount_saphali_hide) && $woocommerce->session->discount_saphali_hide)) {
								
								$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по текущему заказу составила %s', 'saphali-discount' ), $_discount_str) . __(' <br />В заказе используется только эта скидка.', 'saphali-discount') . '<span class="close" title="Закрыть"> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' ); 
								
								}
									if(empty($woocommerce->session->discount_saphali)) $woocommerce->session->discount_saphali = array();
									if(empty($woocommerce->session->discount_saphali_next)) $woocommerce->session->discount_saphali_next = array();
									$session = $woocommerce->session->discount_saphali;
									$session_next = $woocommerce->session->discount_saphali_next;
									$session = array($index => $discount[$_k]) + $session ;
									$session_next = isset($variant_discount[$_k]["discount"][$numb_ind+1]) ?  array($index => $variant_discount[$_k]["discount"][$numb_ind+1]) + $session_next : $session_next;
									$woocommerce->session->discount_saphali = $session;
									
									$woocommerce->session->discount_saphali_next = $session_next;
								}
								$no_next = true;
							} else {
								if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
									if($_SESSION['discount_saphali'][$index] != $_SESSION['discount_saphali_next'][$index] && isset($_SESSION['discount_saphali_next'][$index]) && !(isset($_SESSION['discount_saphali_hide_next']) && $_SESSION['discount_saphali_hide_next'])  ) {
										if ( strstr( $_SESSION['discount_saphali_next'][$index], '%' ) )  $_discount_str_ = $_SESSION['discount_saphali_next'][$index];
											 else  $_discount_str_ = $this->wc_price($_SESSION['discount_saphali_next'][$index]);
											 $_SESSION['discount_saphali_next_return'] = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
										$discount_saphali_next = '<br />' . $_SESSION['discount_saphali_next_return'];
									} else $discount_saphali_next = '';
								} else {
									while( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
										$numb_ind++;
									}
									if( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && !isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
										$no_next = false;
										$woocommerce->session->discount_saphali_next_return = '';
										if( isset( $woocommerce->session->discount_saphali_next[$index] ) ) {$ni = $woocommerce->session->discount_saphali_next; unset($ni[$index]); $woocommerce->session->discount_saphali_next = $ni;}
									} elseif( !isset($no_next) ) $no_next = true;
									if( $no_next && isset($woocommerce->session->discount_saphali_next[$index]) && $woocommerce->session->discount_saphali[$index] != $woocommerce->session->discount_saphali_next[$index] && isset($woocommerce->session->discount_saphali_next[$index]) && !(isset($woocommerce->session->discount_saphali_hide_next) && $woocommerce->session->discount_saphali_hide_next)  ) {
										if ( strstr( $woocommerce->session->discount_saphali_next[$index], '%' ) )  $_discount_str_ = $woocommerce->session->discount_saphali_next[$index];
											 else  $_discount_str_ = $this->wc_price($woocommerce->session->discount_saphali_next[$index]);
										$discount_saphali_next = $woocommerce->session->discount_saphali_next_return = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
										
									} else $discount_saphali_next = '';
								}
								if(  version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) && !(isset($_SESSION['discount_saphali_hide']) && $_SESSION['discount_saphali_hide'])  || ! version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) && !(isset($woocommerce->session->discount_saphali_hide) && $woocommerce->session->discount_saphali_hide) ) {
								
								$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка по текущему заказу составила %s', 'saphali-discount' ), $_discount_str) . '<br />' . $discount_saphali_next . '<span class="close" title="Закрыть"> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
								
								}
								
								$no_next = false;
							}
							if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								if( $no_next && $_SESSION['discount_saphali'][$index] != $_SESSION['discount_saphali_next'][$index] && isset($_SESSION['discount_saphali_next'][$index]) && !(isset($_SESSION['discount_saphali_hide_next']) && $_SESSION['discount_saphali_hide_next'])  ) {
									if ( strstr( $_SESSION['discount_saphali_next'][$index], '%' ) )  $_discount_str_ = $_SESSION['discount_saphali_next'][$index];
										 else  $_discount_str_ = $this->wc_price($_SESSION['discount_saphali_next'][$index]);
									$_SESSION['discount_saphali_next_return'] = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
									$this->comp_woocomerce_mess( $_SESSION['discount_saphali_next_return'] . '<span class="close" title="Закрыть "> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
								}
								if($this->info_cart_checkout_all == 'yes')
								$_SESSION['global_discount_saphali'] = $_discount_str;
								else {
									if(isset($_SESSION['global_discount_saphali']) ) unset($_SESSION['global_discount_saphali']);
								}
							} else {
								while( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
									$numb_ind++;
								}
								if( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && !isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
									$no_next = false;
									$woocommerce->session->discount_saphali_next_return = '';
									if( isset( $woocommerce->session->discount_saphali_next[$index] ) ) {$ni = $woocommerce->session->discount_saphali_next; unset($ni[$index]); $woocommerce->session->discount_saphali_next = $ni;}
								} elseif( !isset($no_next) ) $no_next = true;
								if( $no_next && $woocommerce->session->discount_saphali[$index] != $woocommerce->session->discount_saphali_next[$index] && isset($woocommerce->session->discount_saphali_next[$index]) && !(isset($woocommerce->session->discount_saphali_hide_next) && $woocommerce->session->discount_saphali_hide_next)  ) {
									if ( strstr( $woocommerce->session->discount_saphali_next[$index], '%' ) )  $_discount_str_ = $woocommerce->session->discount_saphali_next[$index];
										 else  $_discount_str_ = $this->wc_price($woocommerce->session->discount_saphali_next[$index]);
									$woocommerce->session->discount_saphali_next_return = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
									$this->comp_woocomerce_mess( $woocommerce->session->discount_saphali_next_return . '<span class="close" title="Закрыть "> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
									
								}
								if($this->info_cart_checkout_all == 'yes')
								$woocommerce->session->global_discount_saphali = $_discount_str;
								else {
									if(isset($woocommerce->session->global_discount_saphali) ) unset($woocommerce->session->global_discount_saphali);
								}
							}

						} elseif( $this->info_cart_checkout == 'yes' ) {
							foreach($woocommerce->cart->applied_coupons as $key => $_code) {
								if($code == $_code) $index = $key;
							}
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
								if($_SESSION['discount_saphali'][$index] != $_SESSION['discount_saphali_next'][$index] && isset($_SESSION['discount_saphali_next'][$index]) && !(isset($_SESSION['discount_saphali_hide_next']) && $_SESSION['discount_saphali_hide_next'])  ) {
									if ( strstr( $_SESSION['discount_saphali_next'][$index], '%' ) )  $_discount_str_ = $_SESSION['discount_saphali_next'][$index];
										 else  $_discount_str_ = $this->wc_price($_SESSION['discount_saphali_next'][$index]);
									$_SESSION['discount_saphali_next_return'] = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
									$this->comp_woocomerce_mess( $_SESSION['discount_saphali_next_return'] . '<span class="close" title="Закрыть "> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
									
								}							
							} else {
								while( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
									$numb_ind++;
								}
								if( ( $variant_discount[$_k]["min"][$numb_ind+1] - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) < 0 && !isset($variant_discount[$_k]["min"][$numb_ind+2]) ) {
									$no_next = false;
									$woocommerce->session->discount_saphali_next_return = '';
									if( isset( $woocommerce->session->discount_saphali_next[$index] ) ) {$ni = $woocommerce->session->discount_saphali_next; unset($ni[$index]); $woocommerce->session->discount_saphali_next = $ni;}
								} elseif( !isset($no_next) ) $no_next = true;
								if( $no_next && $woocommerce->session->discount_saphali[$index] != $woocommerce->session->discount_saphali_next[$index] && isset($woocommerce->session->discount_saphali_next[$index]) && !(isset($woocommerce->session->discount_saphali_hide_next) && $woocommerce->session->discount_saphali_hide_next)  ) {
									if ( strstr( $woocommerce->session->discount_saphali_next[$index], '%' ) )  $_discount_str_ = $woocommerce->session->discount_saphali_next[$index];
										 else  $_discount_str_ = $this->wc_price($woocommerce->session->discount_saphali_next[$index]);
									$woocommerce->session->discount_saphali_next_return = sprintf(__( 'Для получения скидки на %s добавьте позиций на сумму %s', 'saphali-discount' ), $_discount_str_, $this->wc_price( $variant_discount[$_k]["min"][$numb_ind+1] * $this->compatibility_currency_Aelia() - ($woocommerce->cart->subtotal - $revers_items_product[$_k]) ) );
									$this->comp_woocomerce_mess( $woocommerce->session->discount_saphali_next_return . '<span class="close" title="Закрыть "> '. __( '&nbsp;', 'saphali-discount' ) .'</span>' );
									

								}
							}

						}

					}
					add_action('wp_footer', array($this, 'add_footer_style') );	
					if( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) )
					$woocommerce->add_inline_js( "jQuery('.woocommerce-message span.close').click( function() {
										var t_his = jQuery(this).parent();
										"
										.
										
													"
													if( jQuery(this).attr('title') == 'Закрыть ') {
														var data = {action: 'discount_saphali_hide_ex', discount_saphali_next: 'true'};
													} else 
													var data = {action: 'discount_saphali_hide_ex', discount_saphali: 'true'};
													jQuery.ajax({
				type: 'POST',
				dataType: 'json',
				url: '" . site_url('wp-admin') . "/admin-ajax.php',
				data: data, 
				success: function(msg)
				{	
					if(typeof msg.ok != 'undefined'  && msg.ok == true ) {
						
					} 

				},
				error: function() {}
			});
							"			.
										"
										t_his.hide('slow');
									});" );
				else
					wc_enqueue_js( "jQuery('.woocommerce-message span.close').click( function() {
										var t_his = jQuery(this).parent();
										"
										.
										
													"
													if( jQuery(this).attr('title') == 'Закрыть ') {
														var data = {action: 'discount_saphali_hide_ex', discount_saphali_next: 'true'};
													} else 
													var data = {action: 'discount_saphali_hide_ex', discount_saphali: 'true'};
													jQuery.ajax({
				type: 'POST',
				dataType: 'json',
				url: '" . site_url('wp-admin') . "/admin-ajax.php',
				data: data, 
				success: function(msg)
				{	
					if(typeof msg.ok != 'undefined'  && msg.ok == true ) {
						
					} 

				},
				error: function() {}
			});
							"			.
										"
										t_his.hide('slow');
									});" );				

				}
			} elseif( isset($_k) && empty($discount[$_k])) { //$discount
				foreach($coupon_code as $_k => $code) {
					if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
						if(is_array($woocommerce->cart->applied_coupons))
						foreach($woocommerce->cart->applied_coupons as $key => $_code) {
							if($code == $_code) {
								$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка (%s) по текущему заказу аннулирована', 'saphali-discount' ), $_SESSION['discount_saphali'][$key] ) );
								unset($_SESSION['discount_saphali'][$key]); 
								unset($_SESSION['discount_saphali_next'][$key]); 
								unset($woocommerce->cart->applied_coupons[$key]);
								if(isset($woocommerce->session->global_discount_saphali) ) unset($woocommerce->session->global_discount_saphali);
							}
						}
						$_SESSION['coupons'] = $woocommerce->cart->applied_coupons;
					} else {
						if(is_array($woocommerce->cart->applied_coupons))
						foreach($woocommerce->cart->applied_coupons as $key => $_code) {
							if($code == $_code) {
								$this->comp_woocomerce_mess(  sprintf(__( 'Ваша накопительная скидка (%s) по текущему заказу аннулирована', 'saphali-discount' ) , $woocommerce->session->discount_saphali[$key] )  );
								if(empty($woocommerce->session->discount_saphali)) $woocommerce->session->discount_saphali = array();
								if(empty($woocommerce->session->discount_saphali_next)) $woocommerce->session->discount_saphali_next = array();
								$session = $woocommerce->session->discount_saphali;
								$session_next = $woocommerce->session->discount_saphali_next;
								unset($session[$key], $session_next[$key]);
								$woocommerce->session->discount_saphali = $session;
								$woocommerce->session->discount_saphali_next = $session_next;
								unset($woocommerce->cart->applied_coupons[$key]);
								if(isset($woocommerce->session->global_discount_saphali) ) unset($woocommerce->session->global_discount_saphali);
							}
						}
						$woocommerce->session->coupon_codes = $woocommerce->cart->applied_coupons;
					}
				}
			} elseif( $add_coupon && !$discount && isset($coupon_code)) {
				
				if(is_array($coupon_code))
				foreach($coupon_code as $val) {
					$k = array_search($val, $woocommerce->cart->applied_coupons);
					if($k !== false) {
						if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
							$this->comp_woocomerce_mess( sprintf(__( 'Ваша накопительная скидка (%s) по текущему заказу аннулирована', 'saphali-discount' ), $_SESSION['discount_saphali'][$k] ) );
									unset($_SESSION['discount_saphali'][$k]); 
									unset($_SESSION['discount_saphali_next'][$k]); 
									unset($woocommerce->cart->applied_coupons[$k]);
									if(isset($woocommerce->session->global_discount_saphali) ) unset($woocommerce->session->global_discount_saphali);
						} else {
							$this->comp_woocomerce_mess(  sprintf(__( 'Ваша накопительная скидка (%s) по текущему заказу аннулирована', 'saphali-discount' ) , $woocommerce->session->discount_saphali[$k] )  );
									if(empty($woocommerce->session->discount_saphali)) $woocommerce->session->discount_saphali = array();
									if(empty($woocommerce->session->discount_saphali_next)) $woocommerce->session->discount_saphali_next = array();
									$session = $woocommerce->session->discount_saphali;
									$session_next = $woocommerce->session->discount_saphali_next;
									unset($session[$k], $session_next[$k]);
									$woocommerce->session->discount_saphali = $session;
									$woocommerce->session->discount_saphali_next = $session_next;
									unset($woocommerce->cart->applied_coupons[$k]);
									if(isset($woocommerce->session->global_discount_saphali) ) unset($woocommerce->session->global_discount_saphali);
						}
					}
				}
			}
		}
		//$woocommerce->cart->remove_coupons( 2 );

	  }
	}
	function discount_saphali_hide_ex() {
		global $woocommerce;
		if(isset($_POST['discount_saphali_next'])) {
			if ( !version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) && !(isset($woocommerce->session->discount_saphali_hide) || isset($woocommerce->session->discount_saphali_hide_next) ) ) $woocommerce->session->set_customer_session_cookie( true );
			if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) $_SESSION['discount_saphali_hide_next'] = true; else $woocommerce->session->discount_saphali_hide_next = true;
		} else {
			if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) $_SESSION['discount_saphali_hide'] = true; else $woocommerce->session->discount_saphali_hide = true;
		}
		echo json_encode( array('ok' => true ) );
		exit();
	} 
	function  add_footer_style() {
	?>
	<style type="text/css">
	.woocommerce-message span.close {
		background: url("<?php  echo plugin_dir_url(__FILE__) . "img/button_close.png"; ?>") no-repeat scroll 0 0 transparent;
		color: #FFFFFF;
		display: block;
		margin: 7px 3px 0 5px;
		padding: 2px 7px;
		position: absolute;
		right: 0;
		top: 0;
		cursor: pointer;
	}
	.woocommerce-message  {
		position: relative;
	}
	</style>
	<?php 
	}
	function sap_woocommerce_coupon_discount_types($coupon_discount_types) {
		$coupon_discount_types['fixed_total_shop'] = __( 'Накопительная по магазину', 'saphali-discount' );
		$coupon_discount_types['fixed_total_cart'] = __( 'Накопительная в корзине', 'saphali-discount' );
		//$coupon_discount_types['fixed_count_buy'] = __( 'Накопительная по количеству', 'saphali-discount' );
		return  $coupon_discount_types;
	}
	function saphali_woocommerce_coupon_options() {
		global $post,$woocommerce;
		$variant_discount = get_post_meta( $post->ID, 'variant_discount', true );
		$discount_type = get_post_meta( $post->ID, 'discount_type', true );
		$coupone_customer_role = get_post_meta( $post->ID, 'saphali_coupone_customer_role', true );
		$coupone_customer_no_role = get_post_meta( $post->ID, 'saphali_coupone_customer_no_role', true );

	if($discount_type == 'fixed_total_shop' || $discount_type == 'fixed_total_cart' ) {
	$customer_email = get_post_meta( $post->ID, 'customer_email', true );
	//$customer_login = get_post_meta( $post->ID, 'customer_login', true );
	$customer_cart_club = get_post_meta( $post->ID, 'customer_cart_club', true );
	$exclude_revers_items_product = get_post_meta( $post->ID, 'exclude_revers_items_product', true );
	$this->info_cart_checkout = get_post_meta( $post->ID, 'info_cart_checkout', true );
	$this->info_cart_checkout_all = get_post_meta( $post->ID, 'info_cart_checkout_all', true );
	$this->saph_min_total_order = get_option( 'saph_min_total_order','');
					?>
<div class="options_group">
	<p class="form-field customer_email_field_saphali ">
		<label for="customer_email"><?php _e('Эл. почта клиентов', 'saphali-discount');?></label>
		<?php $args['exclude'] = array(1);
					$args['orderby'] = 'email';
					$args['fields'] = array('ID', 'user_email', 'user_login');
					$all_users = get_users( $args );
					//var_dump(   $customer_email, $post->ID ); ?>
		<select id="customer_email" style="min-width:130px" name="customer_email[]" class="chosen_select" multiple="multiple" data-placeholder="<?php  _e( 'Any customer', 'woocommerce' ); ?>">
				<?php 
					if ( is_array($all_users) )  {
						$check_emails = array();
						foreach ( $all_users as $user ) {
							$check_emails[] = $user->user_email;
						}
						//$check_emails[] = get_user_meta($user->ID, 'billing_email', true);
						$check_emails = array_unique($check_emails);
						foreach($check_emails as $email) {
							$_user_login = '';//isset($user->user_login) ? ' (' .$user->user_login . ')' : '';
							if(!empty($email))
							echo '<option value="' . esc_attr( $email ) . '"' . selected( in_array( $email, (array)$customer_email ), true, false ) . '><!--email_off-->' . esc_html( $email .  $_user_login ) . '<!--email_off--></option>';
						}
						unset($check_emails);
					}
					
				?>
		</select> 
		<img class="help_tip" data-tip='<?php  _e( 'Здесь указываются адреса электронной почты для создания специальных условий использования этого купона только с указанными пользовательским email-ми.', 'saphali-discount' ) ?>' src="<?php  echo $woocommerce->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	</p>
</div>
<div class="options_group">
	<p class="form-field customer_cart_club_field_saphali ">
		<label for="customer_cart_club"><?php _e('Клубные карты клиентов', 'saphali-discount');?></label>
		<select id="customer_cart_club" style="min-width:130px" name="customer_cart_club[]" class="chosen_select" multiple="multiple" data-placeholder="<?php  _e( 'Любой покупатель, без учета клубных карт', 'saphali-discount' ); ?>">
				<?php 
					$t = false;
					if ( is_array($all_users) ) foreach ( $all_users as $user ) {
						$phone = get_user_meta($user->ID, 'billing_cart_club', true);
						$name = get_user_meta($user->ID, 'billing_first_name', true) . ' ' . get_user_meta($user->ID, 'billing_last_name', true);
						//$check_logins = array_unique($check_logins);
						if(!empty($phone)) {
							$t = true;
							echo '<option value="' . esc_attr( $phone ) . '"' . selected( in_array( $phone, $customer_cart_club ), true, false ) . '>' . esc_html( $phone .  " ({$user->user_email} - {$name})" ) . '</option>';
							$index = array_search(trim($phone), $customer_cart_club);
							if( is_integer( $index ) )
							unset( $customer_cart_club[$index] );
						}
					}
					if( isset($customer_cart_club) && is_array($customer_cart_club) )
					foreach($customer_cart_club as $phone) {
						echo '<option value="' . esc_attr( $phone ) . '"' . selected( true, true, false ) . '>' . esc_html( $phone ) . '</option>';
					}
					if($t) {
				?>
				<option value='0'><?php  _e('Добавить еще клубные карты', 'saphali-discount' ); ?>.</option>
					<?php } else {
						?><option value='0'><?php  _e('Указать клубные карты через запятую', 'saphali-discount' ); ?>.</option><?php
					} ?>
		</select> 
		<img class="help_tip" data-tip='<?php  _e( 'Здесь указываются клубные карты для покупателей. Если указать их то, скидка будет предоставляться исключительно по ним.', 'saphali-discount' ) ?>' src="<?php  echo $woocommerce->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	</p>
</div>
<div class="options_group">
	<p class="form-field customer_cat_field_saphali ">
		<label for="customer_cat"><?php  _e('Задействование', 'saphali-discount');?></label>
		
		<input type="checkbox" value="yes" id="exclude_revers_items_product" <?php  checked('yes', $exclude_revers_items_product); ?> name="exclude_revers_items_product" class="checkbox">
		<span class="description"><?php  _e('Задействовать скидку в любом случае, минуя товары, которые не подлежат задействованию скидки', 'saphali-discount');?></span>
		<img class="help_tip" data-tip='<?php  _e( 'Дает возможность не аннулировать полностью скидку, если в в корзине находится позиция товара, не подлежащая скидки, т.о. предоставляет возможность задействовать скидку в любом случае, минуя товары, которые не подлежат задействованию скидки, с учетом опции "Исключить категории" или "Категории товара".', 'saphali-discount' ) ?>' src="<?php  echo $woocommerce->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	</p>
</div>
<div class="options_group">
	<p class="form-field customer_view_field_saphali ">
		<label><?php  _e('Информация о скидке', 'saphali-discount');?></label>
		
		<input type="checkbox" value="yes" id="info_cart_checkout" <?php  checked('yes', $this->info_cart_checkout); ?> name="info_cart_checkout" class="checkbox">
		<label style="width: 100%; float: none; line-height: 1; font-size: 14px; font-style: italic;" class="description" for="info_cart_checkout"><?php  _e('Отображать всегда информацию о задействованной скидке', 'saphali-discount');?></label>
		<img class="help_tip" data-tip='<?php  _e( 'Дает возможность отображать информацию о задействованной скидке на страницах Корзины и Оплаты.', 'saphali-discount' ) ?>' src="<?php  echo $woocommerce->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /> <br /> <br />
		<label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
		<input type="checkbox" value="yes" id="info_cart_checkout_all" <?php  checked('yes', $this->info_cart_checkout_all); ?> name="info_cart_checkout_all" class="checkbox">
		<label style="width: 100%; float: none; line-height: 1; font-size: 14px; font-style: italic;" class="description" for="info_cart_checkout_all"><?php  _e('Отображать информацию на всех страницах магазина', 'saphali-discount');?></label>
		<img class="help_tip" data-tip='<?php  _e( 'Дает возможность отображать информацию о задействованной скидке не только на страницах Корзины и Оплаты, но и на всех страницах магазина (каталог, категории, метки, карточка товара).', 'saphali-discount' ) ?>' src="<?php  echo $woocommerce->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /> 
	</p>
</div>
<div class="options_group">
	<p class="form-field customer_cat_field_saphali ">
		<label for="saph_min_total_order"><?php  _e('Сумма для минимальной закупки', 'saphali-discount');?></label>
		
		<input type="text" value="<?php  echo $this->saph_min_total_order; ?>"  name="saph_min_total_order" id="saph_min_total_order">
		<span class="description"><?php  _e('Если заполнено, то отобразится сообщение об необходимости добавить больше позиций к заказу.', 'saphali-discount');?></span>
		<br />
		<input type="checkbox" value="1"  name="saph_min_total_order_no_all_page" id="saph_min_total_order_no_all_page" <?php  $saph_min_total_order_no_all_page = get_option('saph_min_total_order_no_all_page', 0); checked('1', $saph_min_total_order_no_all_page ); ?>><label class="description" style="width: 100%; float: none; line-height: 1; font-size: 14px; font-style: italic;" for="saph_min_total_order_no_all_page"><?php  _e('Отображать только на страницах корзины и оформления заказа', 'saphali-discount');?></label>
		
		
	</p>
</div>
<?php } ?>
<!--  -->
<div class="options_group">
<?php  
			global $wp_roles;

			if ( class_exists('WP_Roles') )
				if ( ! isset( $wp_roles ) )
					$wp_roles = new WP_Roles();
					//var_dump($wp_roles->roles);
					foreach($wp_roles->roles as $role => $_role_value) {
						$search_role[] = $role;
						$search_role_name[] = $_role_value['name'];
					} 
					
					?>
					<?php 
						$name = '';
						foreach($search_role_name as $k => $v) {
							if( is_array($coupone_customer_role) && in_array( $search_role[$k], $coupone_customer_role) ) $_checked = checked( 1, 1, false ); else $_checked = '';
							$name .= '<label for="add_customer_role'.$k.'" class="group_customer"><input '.$_checked.' type="checkbox" name="_customer_role[]" id="add_customer_role'.$k.'" value="'.$search_role[$k].'" /> '. $v . '</label>'; 
						}
						
					?>
	<p class="form-field customer_view_field_saphali ">
		<label for="customer_view"><?php  _e('Применить скидку только со следующими группами пользователей', 'saphali-discount');?></label>
		<?php  echo $name; ?>
		<span class="clear" style="display: block;">&nbsp;</span>
		<span class="description"><?php  _e('Укажите группу, чтобы ограничить круг пользователей, для которых этот купон предоставляется.', 'saphali-discount');?></span>
		<img class="help_tip" data-tip='<?php  _e( 'Если ничего не выбрать, то в использовании скидки данная опция не учитывается, т.е. если остальные условия соблюдается для получения скидки, то покупатель воспользуется скидкой.', 'saphali-discount' ) ?>' src="<?php  echo $woocommerce->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	</p>

</div>
<div class="options_group">
	<?php 
		$name = '';
		foreach($search_role_name as $k => $v) {
			if( is_array($coupone_customer_no_role) && in_array( $search_role[$k], $coupone_customer_no_role) ) $_checked = checked( 1, 1, false ); else $_checked = '';
				$name .= '<label for="add_customer_no_role'.$k.'" class="group_customer"><input '.$_checked.' type="checkbox" name="_customer_role_no[]" id="add_customer_no_role'.$k.'" value="'.$search_role[$k].'" /> '. $v . '</label>'; 
		}
						
	?>
	<p class="form-field customer_view_field_saphali ">
		<label for="customer_view"><?php  _e('Не применять скидку со следующими группами пользователей', 'saphali-discount');?></label>
		<?php  echo $name; ?>
		<span class="clear" style="display: block;">&nbsp;</span>
		<span class="description"><?php  _e('Укажите группу, чтобы ограничить круг пользователей, для которых этот купон не предоставляется.', 'saphali-discount');?></span>
		<img class="help_tip" data-tip='<?php  _e( 'Если ничего не выбрать, то в использовании скидки данная опция не учитывается, т.е. если остальные условия соблюдается для получения скидки, то покупатель воспользуется скидкой.', 'saphali-discount' ) ?>' src="<?php  echo $woocommerce->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
	</p>
	<style>
	#wpbody .woocommerce_options_panel  label.group_customer {margin:0 12px 0 0;padding:0;width:auto;}
	.woocommerce_options_panel  input, #wpbody p label input[type="checkbox"]  {width: auto;margin-right: 5px;margin-top: 5px;}
	.min_variant_discount.short, .max_variant_discount.short {
		float: none !important;
		max-width: 115px;
		width: auto !important;
	}
	</style>
</div>
<div class="options_group">
		<div class="form-field variant_discount_field">
		<label for="variant_discount[min][]" style="padding: 9px  0 0 9px; margin: 0 12px 0 0;"><?php _e('Задействовать скидку в ценовом интервале', 'saphali-discount');?></label>
		<?php 
		if(!isset($variant_discount['min'])) {
		?>
			<p class="variant_discount_field" style="padding: 16px 0px 0px;">
				<input type="number" min="0" step="1" placeholder="<?php _e('мин. значение', 'saphali-discount');?>" value="" id="variant_discount[min][]" name="variant_discount[min][]" class="min_variant_discount short"> <input type="text" placeholder="<?php _e('макс. значение', 'saphali-discount');?>" value="" id="variant_discount[max][]" name="variant_discount[max][]" class="max_variant_discount short"> 
				<input type="text" placeholder="<?php _e('значение скидки', 'saphali-discount');?>" value="" id="variant_discount[discount][]" name="variant_discount[discount][]" class="max_variant_discount short" style="width: 105px" />
				<span class="description" <?php  if ( !version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) {echo ' style="width: 150px;position: absolute;right: -150px;background:#FFFFFF;display: block;"';} ?>><?php _e('Задайте ценовой интервал, в пределах которого задействуется скидка.', 'saphali-discount');?></span>
			</p>		
		<?php		
		} else {
		foreach($variant_discount['min'] as $key => $discount) {
			if($key == 0) {
			?>
			<p class="variant_discount_field" style="padding: 16px 0px 0px;">
				<input type="number" min="0" step="1" placeholder="<?php _e('мин. значение', 'saphali-discount');?>" value="<?php  echo $discount;?>" id="variant_discount[min][]" name="variant_discount[min][]" class="min_variant_discount short"> 
				<input type="text" placeholder="<?php _e('макс. значение', 'saphali-discount');?>" value="<?php  echo $variant_discount['max'][$key];?>" id="variant_discount[max][]" name="variant_discount[max][]" class="max_variant_discount short"> 
				<input type="text" placeholder="<?php _e('значение скидки', 'saphali-discount');?>" value="<?php  echo $variant_discount['discount'][$key];?>" id="variant_discount[discount][]" name="variant_discount[discount][]" class="max_variant_discount short" style="width: 105px" />
				<span class="description" <?php  if ( !version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) {echo ' style="width: 150px;position: absolute;background:#FFFFFF;right: -150px;display: block;"';} ?>><?php _e('Задайте ценовой интервал, в пределах которого задействуется скидка.', 'saphali-discount');?></span>
			</p>
		<?php } else {
		?>
			<p class="variant_discount_field">
			<label for="variant_discount[min][]"<?php  if ( !version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) {echo ' style="margin: 0 0 0 -68px;"';} ?>>&nbsp;</label>
			<input type="number" min="0" step="1" placeholder="<?php _e('мин. значение', 'saphali-discount');?>" value="<?php  echo $discount;?>" id="variant_discount[min][]" name="variant_discount[min][]" class="min_variant_discount short"> 
			<input type="text" placeholder="<?php _e('макс. значение', 'saphali-discount');?>" value="<?php  echo $variant_discount['max'][$key];?>" id="variant_discount[max][]" name="variant_discount[max][]" class="max_variant_discount short"> 
			<input type="text" placeholder="<?php _e('значение скидки', 'saphali-discount');?>" value="<?php  echo $variant_discount['discount'][$key];?>" id="variant_discount[discount][]" name="variant_discount[discount][]" class="max_variant_discount short" style="width: 105px" /> <span style="color: red" class="button-secondary remove"><?php _e('Удалить', 'saphali-discount');?></span>
			</p>
		<?php
		}
		}
	}?>
		<div class="add_variant_discount button-primary" style="margin: 5px 0 12px 160px; text-align: center;"><?php _e('Добавить ценовой интервал', 'saphali-discount');?></div>
		<div class="clear"></div>
		<script type="text/javascript">
		jQuery(window).ready(function(){
			jQuery(".options_group").delegate('.add_variant_discount', 'click', function() {
				jQuery("div.variant_discount_field .variant_discount_field:last").after('\
				<p class="variant_discount_field">\
				<label for="variant_discount[min][]"<?php  if ( !version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) {echo ' style="margin: 0 0 0 -68px;"';} ?>>&nbsp;</label>\
				<input type="number" min="0" step="1" placeholder="<?php _e('мин. значение', 'saphali-discount');?>" value="" id="variant_discount[min][]" name="variant_discount[min][]" class="min_variant_discount short"> <input type="text" placeholder="<?php _e('макс. значение', 'saphali-discount');?>" value="" id="variant_discount[max][]" name="variant_discount[max][]" class="max_variant_discount short">\
				<input type="text" placeholder="<?php _e('значение скидки', 'saphali-discount');?>" value="" id="variant_discount[discount][]" name="variant_discount[discount][]" class="max_variant_discount short" style="width: 105px" />\
				<span style="color: red" class="button-secondary remove"><?php _e('Удалить', 'saphali-discount');?></span>\
				</p>\
				');
				jQuery("p.variant_discount_field").delegate( 'span.remove', 'click', function() {
					jQuery(this).parent().remove();
				});
			});
			
			jQuery( '.button.generate-coupon-code' ).on( 'click', function( e ) {
				e.preventDefault();
				if(jQuery('#discount_type').val() == "fixed_total_shop" || jQuery('#discount_type').val() == "fixed_total_cart") {
					var $coupon_code_field = jQuery( '#title' );
					setTimeout( function($coupon_code_field) { $coupon_code_field.val( $coupon_code_field.val().toLowerCase().replace(/\s/, '-'));}, 100, $coupon_code_field );
				}
			});
			jQuery("p.variant_discount_field").delegate( 'span.remove', 'click', function() {
				jQuery(this).parent().remove();
			});
			jQuery("#customer_cart_club").change(function() {
				var customer_cart_club = jQuery(this).val() + '';
				var array_cus_p =  customer_cart_club.split(',');
				if( array_cus_p !== null ) {
					var p = array_cus_p.pop();
					if( p == '' ||  p == '0')
					{
						if(jQuery("input#customer_cart_club_text").length == 0) {
						setTimeout(function() {jQuery("input#customer_cart_club_text").focus();}, 100);
						jQuery(this).after('<input type="text" style="min-width: 150px;width:100%;" value="' + customer_cart_club.replace(/,0$/,',') + '" placeholder="Введите клубные карты через запятую" name="customer_cart_club_text" id="customer_cart_club_text" />');
						}
						else {
							if(jQuery("input#customer_cart_club_text").val() == '' || jQuery("input#customer_cart_club_text").val() == '') {
								jQuery("input#customer_cart_club_text").val(customer_cart_club.replace(/,0$/,','));
								
							}
						}
					} else {
						jQuery("input#customer_cart_club_text").remove();
					}
				}
			});
			var usage_limit_per_user, customer_email_field, _customer_email_field;
			
			jQuery("#discount_type").change(function() {
				if(jQuery(this).val() == 'fixed_total_shop' || jQuery(this).val() == 'fixed_total_cart') {
					var $coupon_code_field = jQuery( '#title' );
					$coupon_code_field.val( $coupon_code_field.val().toLowerCase().replace(/\s/, '-') );
				}
				if( (jQuery(".form-field.customer_email_field_saphali").length > 0 && jQuery(".form-field.customer_email_field").length > 0) && !(jQuery(this).val() == 'fixed_total_shop' || jQuery(this).val() == 'fixed_total_cart')
				) {
					jQuery(".form-field.customer_email_field_saphali").parent().addClass('saph__email_field').hide();
					_customer_email_field = jQuery(".form-field.customer_email_field_saphali").detach();
				} else {
					if(_customer_email_field && (jQuery(this).val() == 'fixed_total_shop' || jQuery(this).val() == 'fixed_total_cart') ) {
						_customer_email_field.appendTo( ".saph__email_field" ); 
						jQuery(".saph__email_field").show();
						jQuery(".saph__email_field").removeClass('saph__email_field');
						_customer_email_field = null;
					}
				}
				if( (jQuery(".form-field.customer_email_field_saphali").length > 0 && <?php  if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) { ?> jQuery(".form-field.customer_email_field").length > 0 <?php  } else { ?>
				jQuery("#usage_limit_per_user").length > 0 || jQuery(".form-field.customer_email_field").length > 0 <?php  } ?>) && (jQuery(this).val() == 'fixed_total_shop' || jQuery(this).val() == 'fixed_total_cart')
				) {
					<?php  if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) { ?> customer_email_field = jQuery(".form-field.customer_email_field").parent().detach(); <?php  } else { ?>
					usage_limit_per_user = jQuery("#usage_limit_per_user").parent().detach();
					customer_email_field = jQuery(".form-field.customer_email_field").parent().detach(); 
					<?php  } ?>
				} else {
					if(customer_email_field && !(jQuery(this).val() == 'fixed_total_shop' || jQuery(this).val() == 'fixed_total_cart') ) {
						<?php  if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) { ?> customer_email_field.appendTo( "#usage_restriction_coupon_data" ); customer_email_field = null; <?php  } else { ?>
						customer_email_field.appendTo( "#usage_restriction_coupon_data" ); 
						customer_email_field = null;
						usage_limit_per_user.appendTo( "#usage_limit_coupon_data div.options_group" );
						usage_limit_per_user = null;
					<?php  } ?>
					}
				}
				
				if(jQuery(this).val() == 'fixed_total_shop' || jQuery(this).val() == 'fixed_total_cart') {
					if(jQuery(".form-field.coupon_amount_field").css('display') != 'none')
					jQuery(".form-field.coupon_amount_field").hide('slow');
					if(jQuery(".options_group div.form-field.variant_discount_field").parent().css('display') == 'none')
					jQuery(".options_group div.form-field.variant_discount_field").parent().show('slow');
					jQuery(".customer_view_field_saphali").parent().show('slow');
					if(jQuery(this).val() == 'fixed_total_shop') jQuery("div.fixed_total_shop").show('slow'); 
					else jQuery("div.fixed_total_shop").hide('slow');
				} else {
					if(jQuery(".form-field.coupon_amount_field").css('display') == 'none')
					jQuery(".customer_view_field_saphali").parent().show('slow');
					if(jQuery(".form-field.coupon_amount_field").css('display') == 'none')
				  jQuery(".form-field.coupon_amount_field").show('slow');
				  if(jQuery(".options_group div.form-field.variant_discount_field").parent().css('display') != 'none')
				  jQuery(".customer_view_field_saphali").parent().hide('slow');
				  if(jQuery(".options_group div.form-field.variant_discount_field").parent().css('display') != 'none')
				  jQuery(".options_group div.form-field.variant_discount_field").parent().hide('slow');
				  jQuery("div.fixed_total_shop").hide('slow');
				}
			});

			jQuery('#discount_type').trigger('change');
			function unique(arr) {
			  var result = [];

			  nextInput:
				for (var i = 0; i < arr.length; i++) {
				  var str = arr[i]; // для каждого элемента
				  for (var j = 0; j < result.length; j++) { // ищем, был ли он уже?
					if (result[j] == str) continue nextInput; // если да, то следующий
				  }
				  result.push(str);
				}

			  return result;
			}
		});
		</script>
		<style>
		div.variant_discount_field p.variant_discount_field {
			margin: 0;
		}
		div.variant_discount_field p.variant_discount_field:first-child {
			margin: 9px 0 0;
		}
		span.remove.button-secondary {
			margin-left: 12px;
		}
	<?php if( !version_compare( WOOCOMMERCE_VERSION, '2.3', '<' ) ) { ?>
	div.variant_discount_field p.variant_discount_field {
		margin: 0 0 0 80px;
	}
	<?php } ?>
		</style>
		<span class="description"><?php _e('Значение скидки можно задать фиксированной суммой или процентом (150 или 5%).', 'saphali-discount');?></span>
	</div>
</div>
		<?php
	}
	
	function saphali_woocommerce_coupon_options_no_valid() {
	?>
	<div class="options_group">
		<div class="inline error"><p><strong><?php  _e( 'Накопи'.'тельные ски'.'дки от'.'кл'.'юч'.'ены', 'saphali-discount' ); ?></strong>: <?php  _e( 'Ha'.'p'.'y'.'шe'.'ни'.'e л'.'иц'.'eнз'.'ии'.'. Д'.'л'.'я pa'.'бo'.'ты св'.'яж'.'ит'.'ес'.'ь с p'.'азp'.'aб'.'oт'.'чи'.'кo'.'м.', 'saphali-discount' ); ?></p></div>
	</div>
	<?php
	}
	function woocommerce_process_shop_coupon_meta($post_id, $post) {
		if(isset($_POST['variant_discount'])) update_post_meta( $post_id, 'variant_discount', $_POST['variant_discount'] );
		if(empty($_POST['exclude_revers_items_product'] )) $_POST['exclude_revers_items_product'] = '';
		if(empty($_POST['info_cart_checkout'] ) ) $_POST['info_cart_checkout'] = '';
		if(empty($_POST['info_cart_checkout_all'] ) ) $_POST['info_cart_checkout_all'] = '';
		if(empty($_POST['saph_min_total_order'] ) ) $_POST['saph_min_total_order'] = '';
		if(empty($_POST['customer_cart_club'] ) ) $_POST['customer_cart_club'] = '';
		
		update_post_meta( $post_id, 'exclude_revers_items_product', $_POST['exclude_revers_items_product'] );
		update_post_meta( $post_id, 'info_cart_checkout', $_POST['info_cart_checkout'] );
		update_post_meta( $post_id, 'info_cart_checkout_all', $_POST['info_cart_checkout_all'] );
		
		if(!update_option( 'info_cart_checkout_all', $_POST['info_cart_checkout_all'] )) add_option( 'info_cart_checkout_all', $_POST['info_cart_checkout_all'] );
		if( isset($_POST['customer_cart_club_text']) ) {
			if( empty($_POST['customer_cart_club_text'] ) ) { 
				$customer_cart_club_text = ''; 
			} else {
				$customer_cart_club_text = explode(',', $_POST['customer_cart_club_text']);
				$customer_cart_club_text = array_map('trim', $customer_cart_club_text);
				foreach($customer_cart_club_text as $k => $v) {
					if(empty($v)) unset($customer_cart_club_text[$k]);
				}
			}
			update_post_meta( $post_id, 'customer_cart_club', $customer_cart_club_text );
		}
		else
		update_post_meta( $post_id, 'customer_cart_club', $_POST['customer_cart_club'] );
	
		update_option( 'saph_min_total_order', $_POST['saph_min_total_order'] );
		// update_post_meta( $post_id, 'schedule_fixed_total_shop', (int) $_POST['schedule_fixed_total_shop'] );
		if(isset( $_POST['saph_min_total_order_no_all_page'] )) {
		 update_option( 'saph_min_total_order_no_all_page', $_POST['saph_min_total_order_no_all_page'] );
		}
		else {
		 update_option( 'saph_min_total_order_no_all_page', 0 );
		}
		
		if(isset($_POST['_customer_role']) && is_array($_POST['_customer_role'])) {
			update_post_meta( $post_id, 'saphali_coupone_customer_role', ( is_array($_POST['_customer_role']) ? array_map('strtolower', $_POST['_customer_role']) : $_POST['_customer_role']) );
		} else {
			delete_post_meta( $post_id, 'saphali_coupone_customer_role' );
		}
		if(isset($_POST['_customer_role_no']) && is_array($_POST['_customer_role_no'])) {
			update_post_meta( $post_id, 'saphali_coupone_customer_no_role', $_POST['_customer_role_no'] );
		} else delete_post_meta( $post_id, 'saphali_coupone_customer_no_role');
		
		if(isset($_POST['customer_email']) && is_array($_POST['customer_email'])) {
			$customer_email =  array_map( 'trim', $_POST['customer_email']  );
			update_post_meta( $post_id, 'customer_email', $customer_email );
			$_POST['customer_email'] = implode(',', $customer_email);
		} else delete_post_meta( $post_id, 'customer_email');
	}
	function filter_where( $where = '' ) {
		// за последние X дней
		if($this->schedule_fixed_total_shop > 0)
		$where .= " AND post_date > '" . date('Y-m-d', strtotime("-{$this->schedule_fixed_total_shop} days")) . "'";
		return $where;
	}
	function wc_price($price) {
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) return woocommerce_price($price);
		else return wc_price($price);
	}
	function parse_query($q) {
		$q->query_vars['lang'] = '';
	}
	function user_cart_apry_discount() {
	global $woocommerce;
		$content = '';
		if ( version_compare( WOOCOMMERCE_VERSION, '2.1.0', '<' ) ) $woocommerce->nocache();
		$coupons = get_posts(array('post_type' => 'shop_coupon', 'post_status' => 'publish', 'meta_key' => 'discount_type', 'meta_value' => 'fixed_total_shop', 'posts_per_page' => -1));
		$ob = wp_get_current_user();
		$remove_coupon_code = $coupon_code = array();
		$current_user = wp_get_current_user();
		$check_emails = array();
		$check_emails[] = $current_user->user_email;
		$check_emails[] = get_user_meta($current_user->ID, 'billing_email', true);
		$check_emails = array_unique($check_emails);
		
		foreach($coupons as $_coupon) {
			if( in_array( $_coupon->post_title, $coupon_code) ) continue;
			$coupon_code[] = $_coupon->post_title;	
			$variant_discount[] = get_post_meta( $_coupon->ID, 'variant_discount', true );
			$customer_email = get_post_meta( $_coupon->ID, 'customer_email', true );
			$filya = true;
			if ( is_array( $customer_email ) && 0 < count( $customer_email ) && ! $this->is_coupon_emails_allowed( $check_emails, $customer_email ) ) {
				$remove_coupon_code[] = $_coupon->post_title;
				$filya = false;
			}
				
			$customer_cart_club = get_post_meta( $_coupon->ID, 'customer_cart_club', true );
			if($filya) {
				$coupone_customer_role = get_post_meta( $_coupon->ID, 'saphali_coupone_customer_role', true );
				$coupone_customer_no_role = get_post_meta( $_coupon->ID, 'saphali_coupone_customer_no_role', true );
				if ( !empty($coupone_customer_role) && is_array($coupone_customer_role) ) {
					if (!( isset($ob->roles[0]) && in_array( $ob->roles[0], $coupone_customer_role) )) {
						$remove_coupon_code[] = $_coupon->post_title;
						$filya = false;
					}
				}
				if ( $filya && !empty($coupone_customer_no_role) && is_array($coupone_customer_no_role) ) {
					$ob = wp_get_current_user();
					if (isset($ob->roles[0]) && in_array( $ob->roles[0], $coupone_customer_no_role)) {
						$remove_coupon_code[] = $_coupon->post_title;
					}
				}
			}
		}
		
		foreach($coupon_code as $r_key => $r_value) {
			if(in_array($r_value, $remove_coupon_code)) {
				unset($coupon_code[$r_key], $variant_discount[$r_key]);
			} 
		}
		if(!empty($coupon_code)) {
			add_filter( 'posts_where', array($this, 'filter_where') );
			add_action( 'parse_query', array( $this, 'parse_query' ), 5 );
			//fix wpml
			$coupon_code = array_unique($coupon_code);
			$add_coupon = false;
			if ( is_user_logged_in() ) {
				
				$add_coupon = true;
				if(!empty($customer_email)) {
					$add_coupon = false;
				} elseif(!empty($customer_cart_club)) {
					$add_coupon = false;
				}
				
				if(is_array($check_emails)) {
					foreach($check_emails as $user_email) {
						if(!empty($customer_email)) {
							if(!empty($user_email))
							if(in_array( $user_email, $customer_email ))
							{
								$add_coupon = true;
								if( ! version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) 
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => array( 'wc-processing', 'wc-completed' ), 'posts_per_page' => -1,   
									
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
								else
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => 'publish', 'posts_per_page' => -1,   
									'tax_query' => array(
									   array(
											'taxonomy' => 'shop_order_status',
											'field' => 'slug',
											'terms' => array( 'completed', 'processing' )
										)
								   ),
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
							$_order_shipping = $_order_discount = $_order_total = 0;
							
							if( $orders->have_posts() ) {
								while ( $orders->have_posts() ) {
									$orders->the_post();
									$_order = $orders->post;
									
									$order_currency = get_post_meta( $_order->ID, '_order_currency', true );
									$rate_def = $this->compatibility_currency($order_currency);
									if( $rate_def != 1 ) {
										$amount = get_post_meta( $_order->ID, '_order_total', true );
										$_order_shipping = $_order_shipping + number_format( get_post_meta( $_order->ID, '_order_shipping', true )  * $rate_def, 2, '.', '');
										$price_order = number_format(  $amount * $rate_def, 2, '.', '');
									} else {
										if(get_post_meta( $_order->ID, '_order_total_base_currency', true ) ) {
											$price_order = get_post_meta( $_order->ID, '_order_total_base_currency', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping_base_currency', true );
										} else {
											$price_order = get_post_meta( $_order->ID, '_order_total', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping', true );
										}
									}
										
									
									//$_order_tax = $_order_tax + get_post_meta( $_order->ID, '_order_tax', true );
									$_order_total = $_order_total + $price_order;							
								}
							}
							$total = $_order_total - $_order_shipping;
							
								break;
							}
						} else {
							if(!empty($user_email))
							if( ! version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) {
								$arg = array('post_type' => 'shop_order', 'post_status' => array( 'wc-processing', 'wc-completed' ), 'posts_per_page' => -1,
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								);
								$orders = new WP_Query($arg);
							}
							else
							$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => 'publish', 'posts_per_page' => -1, 
									'tax_query' => array(
									   array(
											'taxonomy' => 'shop_order_status',
											'field' => 'slug',
											'terms' => array( 'completed', 'processing' )
										)
								   ),
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
							));
							$_order_shipping = $_order_discount = $_order_total = 0;
							if( $orders->have_posts() ) {
								while ( $orders->have_posts() ) {
									$orders->the_post();
									$_order = $orders->post;
									$order_currency = get_post_meta( $_order->ID, '_order_currency', true );
									$rate_def = $this->compatibility_currency($order_currency);
									if( $rate_def != 1 ) {
										$amount = get_post_meta( $_order->ID, '_order_total', true );
										$_order_shipping = $_order_shipping + number_format( get_post_meta( $_order->ID, '_order_shipping', true )  * $rate_def, 2, '.', '');
										$price_order = number_format(  $amount * $rate_def, 2, '.', '');
									} else {
										if(get_post_meta( $_order->ID, '_order_total_base_currency', true ) ) {
											$price_order = get_post_meta( $_order->ID, '_order_total_base_currency', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping_base_currency', true );
										} else {
											$price_order = get_post_meta( $_order->ID, '_order_total', true );
											$_order_shipping = $_order_shipping + (float)get_post_meta( $_order->ID, '_order_shipping', true );
										}
									}
										
									
									//$_order_tax = $_order_tax + get_post_meta( $_order->ID, '_order_tax', true );
									$_order_total = $_order_total + $price_order;							
								}
							}
							// var_dump($orders);
							$total = $_order_total - $_order_shipping;
							
						}
					}
				}
				if(!$add_coupon) {
					
					$check_ps[] = get_user_meta($current_user->ID, 'billing_cart_club', true);
					if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
						if(isset($_SESSION['d_phone']))
						$post_data["billing_cart_club"] = $_SESSION['d_phone'];
					} else {
						if(isset($woocommerce->session->d_phone))
							$post_data["billing_cart_club"] = $woocommerce->session->d_phone;
					}
					if(isset($post_data["billing_cart_club"])) {
						$check_ps[] = $post_data["billing_cart_club"];
					}
					$check_ps = array_unique($check_ps);
					if(is_array($check_ps)) {
						foreach($check_ps as $user_p) {
							if(!empty($customer_cart_club)) {
								if(!empty($user_p))
								if(in_array( $user_p, $customer_cart_club ))
								{
									$add_coupon = true;
									if( ! version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) 
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => array( 'wc-processing', 'wc-completed' ), 'posts_per_page' => -1,   
									
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
								else
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => 'publish', 'posts_per_page' => -1,   
									'tax_query' => array(
									   array(
											'taxonomy' => 'shop_order_status',
											'field' => 'slug',
											'terms' => array( 'completed', 'processing' )
										)
								   ),
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_customer_user',
									   'value' => $current_user->ID,
									   'compare' => '=',
									   )
								   )
								));
							$_order_shipping = $_order_discount = $_order_total = 0;
							if( $orders->have_posts() ) {
								while ( $orders->have_posts() ) {
									$orders->the_post();
									$_order = $orders->post;
									
									$order_currency = get_post_meta( $_order->ID, '_order_currency', true );
									$rate_def = $this->compatibility_currency($order_currency);
									if( $rate_def != 1 ) {
										$amount = get_post_meta( $_order->ID, '_order_total', true );
										$_order_shipping = $_order_shipping + number_format( get_post_meta( $_order->ID, '_order_shipping', true )  * $rate_def, 2, '.', '');
										$price_order = number_format(  $amount * $rate_def, 2, '.', '');
									} else {
										if(get_post_meta( $_order->ID, '_order_total_base_currency', true ) ) {
											$price_order = get_post_meta( $_order->ID, '_order_total_base_currency', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping_base_currency', true );
										} else {
											$price_order = get_post_meta( $_order->ID, '_order_total', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping', true );
										}
									}
										
									
									//$_order_tax = $_order_tax + get_post_meta( $_order->ID, '_order_tax', true );
									$_order_total = $_order_total + $price_order;							
								}
							}
							$total = $_order_total - $_order_shipping;
									break;
								}
							} else {

							}
						}
					}
				}
			} else {
				if(!$add_coupon && (isset($woocommerce->session->d_phone) && $woocommerce->session->d_phone) ) {
					if ( version_compare( WOOCOMMERCE_VERSION, '2.0', '<' ) ) {
						if(isset($_SESSION['d_phone']))
						$post_data["billing_cart_club"] = $_SESSION['d_phone'];
					} else {
						if(isset($woocommerce->session->d_phone))
							$post_data["billing_cart_club"] = $woocommerce->session->d_phone;
					}
					if(isset($post_data["billing_cart_club"])) {
						$check_ps[] = $post_data["billing_cart_club"];
					}
					$check_ps = array_unique($check_ps);
					if(is_array($check_ps)) {
						foreach($check_ps as $user_p) {
							if(!empty($customer_cart_club)) {
								if(!empty($user_p))
								if(in_array( $user_p, $customer_cart_club ))
								{
									$add_coupon = true;
									if( ! version_compare( WOOCOMMERCE_VERSION, '2.2', '<' ) ) 
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => array( 'wc-processing', 'wc-completed' ), 'posts_per_page' => -1,   
									
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_billing_cart_club',
									   'value' => $user_p,
									   'compare' => 'LIKE',
									   )
								   )
								));
								else
								$orders = new WP_Query(array('post_type' => 'shop_order', 'post_status' => 'publish', 'posts_per_page' => -1,   
									'tax_query' => array(
									   array(
											'taxonomy' => 'shop_order_status',
											'field' => 'slug',
											'terms' => array( 'completed', 'processing' )
										)
								   ),
								   'meta_query' => array(
									   'relation' => 'AND',
									   array(
									   'key' => '_billing_cart_club',
									   'value' => $user_p,
									   'compare' => 'LIKE',
									   )
								   )
								));
							$_order_shipping = $_order_discount = $_order_total = 0;
							if( $orders->have_posts() ) {
								while ( $orders->have_posts() ) {
									$orders->the_post();
									$_order = $orders->post;
									
									$order_currency = get_post_meta( $_order->ID, '_order_currency', true );
									$rate_def = $this->compatibility_currency($order_currency);
									if( $rate_def != 1 ) {
										$amount = get_post_meta( $_order->ID, '_order_total', true );
										$_order_shipping = $_order_shipping + number_format( get_post_meta( $_order->ID, '_order_shipping', true )  * $rate_def, 2, '.', '');
										$price_order = number_format(  $amount * $rate_def, 2, '.', '');
									} else {
										if(get_post_meta( $_order->ID, '_order_total_base_currency', true ) ) {
											$price_order = get_post_meta( $_order->ID, '_order_total_base_currency', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping_base_currency', true );
										} else {
											$price_order = get_post_meta( $_order->ID, '_order_total', true );
											$_order_shipping = $_order_shipping + get_post_meta( $_order->ID, '_order_shipping', true );
										}
									}
										
									
									//$_order_tax = $_order_tax + get_post_meta( $_order->ID, '_order_tax', true );
									$_order_total = $_order_total + $price_order;							
								}
							}
							$total = $_order_total - $_order_shipping;
									break;
								}
							} else {

							}
						}
					}
				}
			}
			
			if( $add_coupon  ) {
				$discount = array();
				if(is_array($variant_discount))
				foreach($variant_discount as $key => $_variant_discount) {
					foreach($_variant_discount['min'] as $_key => $_discount) {
						if( $total >= $_discount && $total <= $variant_discount[$key]['max'][$_key] ) {
							$discount[$key] = $variant_discount[$key]['discount'][$_key];
						} 
					}
				}
			}
			if ( PRINT_DISCOUNT_INFORMATION_SUMMA ) {
				$summ_info = __('Общая сумма всех Ваших покупок %s.', 'saphali-discount');
				$_total = apply_filters( 'WOOMULTI_CURRENCY_C', $total );
				if(class_exists('WOOCS') ) {
					$rate = $this->compatibility_currency('', '', true);
					$_total = number_format(  $total * $rate , 2, '.', '');
				}
				$_total_text = $_total . ' '. get_woocommerce_currency_symbol();
			} else {
				$summ_info = '%s';
				$_total_text = '';
			}
			if( $add_coupon && $discount  ) {
				//$count=0;
				
				foreach($coupon_code as $_k => $code) {
					foreach($variant_discount[$_k]["discount"] as $num_key => $d_value ) {
						if( $d_value == $discount[$_k] ) {
							$numb_ind = $num_key; break;
						}
					}
					if(empty($discount[$_k])) {
						if(!empty($total)) $p_t = sprintf(__(' ' . $summ_info , 'saphali-discount' ), $_total_text );
						else $p_t = '';
						$content .=  __( '<h3>У Вас нет накопительной скидки</h3>','saphali-discount' ) . $p_t;
						
						continue;
					}
					if( isset($variant_discount[$_k]["discount"][$numb_ind+1]) ) {
						$sum = $this->wc_price( apply_filters( 'WOOMULTI_CURRENCY_C', ($variant_discount[$_k]["min"][$numb_ind+1] - $total) ) );
						$next = ( isset($variant_discount[$_k]["discount"][$numb_ind+1]) ) ? $variant_discount[$_k]["discount"][$numb_ind+1] : '';
						$content .= sprintf(__( '<h3>Ваша накопительная скидка по всем Вашим заказам составляет <span style="color:red">%s</span>. ', 'saphali-discount'  ). $summ_info . __('<br /> Для получения скидки на %s нужно совершить покупку на %s.</h3>', 'saphali-discount' ), $discount[$_k], $_total_text, $next, $sum );
					} else {
						$content .= sprintf(__( '<h3>Ваша накопительная скидка по всем Вашим заказам составляет <span style="color:red">%s</span>. %s</h3>', 'saphali-discount'), $discount[$_k], $_total_text, $summ_info );
					}
					if( isset( $content ) && $content && $this->schedule_fixed_total_shop > 0 )
					$content = str_replace( array('накопительная скидка','Cumulative discount','накопичувальна знижка'), array( 'накопительная скидка' . sprintf( __(' за последние %d дн. ', 'saphali-discount' ), $this->schedule_fixed_total_shop), 'Cumulative discount' . sprintf( __(' за последние %d дн. ', 'saphali-discount' ), $this->schedule_fixed_total_shop), 'накопичувальна знижка' . sprintf( __(' за последние %d дн. ', 'saphali-discount' ), $this->schedule_fixed_total_shop) ), $content);
				}
			} else {
				if ( is_user_logged_in() ) {
					if(!empty($total)) $p_t = sprintf(__(' ' . $summ_info . '</h3>', 'saphali-discount' ), $_total_text );
					else $p_t = '';
					$content .=  __( '<h3>У Вас нет накопительной скидки</h3>', 'saphali-discount' ) . $p_t;
					if($add_coupon && isset($variant_discount[0]['min'][0]) ) $content .= sprintf(__('<h3> Для получения скидки на %s нужно совершить покупку на %s.</h3>', 'saphali-discount' ), $variant_discount[0]['discount'][0], $this->wc_price( apply_filters( 'WOOMULTI_CURRENCY_C', ($variant_discount[0]['min'][0] - $total) ) ) );
				}
				else {
					if(!empty($customer_cart_club)) $p_ = '<span class="billing_cart_clubblock"><input type="text" value="" placeholder="" id="billing_cart_club" name="billing_cart_club" class="input-text "> <button>OK</button></span>'; else $p_ = '';
					$content .=  sprintf(__( '<h3>У Вас нет накопительной скидки. Пожалуйста, <a href="%s">авторизируйтесь</a>, чтобы узнать Вашу текущую накопительную скидку или идентифицируйтесь введя номер телефона %s </h3>', 'saphali-discount' ), get_permalink(get_option('woocommerce_myaccount_page_id')), $p_ );
				
				}
			}
			
			if( isset( $content ) && $content ) {
				$content = '<div class="discount">'. $content .'</div>';
			}
			
			if( !empty($check_ps ) || !(isset($woocommerce->session->d_phone) && $woocommerce->session->d_phone) ) {
			if( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) )
				$woocommerce->add_inline_js("jQuery('body').delegate('input#billing_cart_club', 'focusin', function(){
						jQuery('input#billing_cart_club').bind( 'focusout', function(){
						var billing_cart_club = 'billing_cart_club=' + jQuery('#billing_cart_club').val();
						
						jQuery('span.billing_cart_clubblock').css({opacity: .3}).attr('rel', 'disabled');
						var count_request = 0;
						if(count_request < 15 && jQuery('span.billing_cart_clubblock').attr('rel') + '' == 'disabled')
						jQuery.ajax({
							type:		'POST',
							url:		'/wp-admin/admin-ajax.php',
							dataType: 'json',
							data:		{'post_data' : billing_cart_club, action: '_woocommerce_update_order_review', security: '" . wp_create_nonce( 'update-order-review' ) . "'},
							error:	function( data ) { jQuery('span.billing_cart_clubblock').attr('rel', ''); jQuery('span.billing_cart_clubblock').css({opacity: 1}); },
							success:	function( data ) {
								jQuery('span.billing_cart_clubblock').attr('rel', '');
								jQuery('span.billing_cart_clubblock').css({opacity: 1});
								count_request++;
								if( data.result  ) {window.location.reload(); } 
								else
								{ if(count_request >= 15) alert('Достаточно много попыток ввода номера. ' +count_request );}
							}
						});
						else
						alert('Достаточно много попыток ввода номера.');
						jQuery('input#billing_cart_club').unbind( 'focusout' );
					});
				});
				
				");
				else 
					wc_enqueue_js("
				jQuery('body').delegate('input#billing_cart_club', 'focusin', function(){
						jQuery('input#billing_cart_club').bind( 'focusout', function(){
						var billing_cart_club = 'billing_cart_club=' + jQuery('#billing_cart_club').val();
						
						jQuery('span.billing_cart_clubblock').css({opacity: .3, cursor: 'wait'}).attr('rel', 'disabled');
						var count_request = 0;
						if(count_request < 15 && jQuery('span.billing_cart_clubblock').attr('rel') + '' == 'disabled')
						jQuery.ajax({
							type:		'POST',
							url:		'/wp-admin/admin-ajax.php',
							dataType: 'json',
							data:		{'post_data' : billing_cart_club, action: '_woocommerce_update_order_review', security: '" . wp_create_nonce( 'update-order-review' ) . "'},
							error:	function( data ) { jQuery('span.billing_cart_clubblock').attr('rel', ''); jQuery('span.billing_cart_clubblock').css({opacity: 1, cursor: 'default'}); },
							success:	function( data ) {
								jQuery('span.billing_cart_clubblock').attr('rel', '');
								jQuery('span.billing_cart_clubblock').css({opacity: 1, cursor: 'default'});
								count_request++;
								if( data.result  ) {window.location.reload(); } 
								else
								{ if(count_request >= 15) alert('Достаточно много попыток ввода номера. ' +count_request );}
							}
						});
						else
						alert('Достаточно много попыток ввода номера.');
						jQuery('input#billing_cart_club').unbind( 'focusout' );
					});
				});
				");
					
		}
		remove_filter( 'posts_where', array($this, 'filter_where') );
		remove_action( 'parse_query', array( $this, 'parse_query' ), 5 );
		} else{
			$content .=  __( '<h3>В данном магазине нет дисконтной программы.</h3>', 'saphali-discount' );
		}
		//$woocommerce->cart->remove_coupons( 2 );
		return $content;
	}
}
add_action('plugins_loaded', 'saphali_dicount_load', 0);
add_action('init', array( 'saphali_dicount', 'load_plugin_textdomain'), 0);
function saphali_dicount_load() {
	new saphali_dicount();
}

}