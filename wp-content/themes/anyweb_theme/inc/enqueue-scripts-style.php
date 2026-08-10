<?php
if (!defined("ABSPATH")) {
  exit;
}

/**
 * Enqueue styles.
 */

function anyweb_styles() {
	wp_enqueue_style("bootstrap",get_bloginfo('stylesheet_directory').'/assets/css/bootstrap.min.css');
    wp_enqueue_style(
        'photoswipe-css',
        get_template_directory_uri() . '/assets/js/photoswipe/dist/photoswipe.css'
    );
	wp_enqueue_style( 'anyweb-style', get_stylesheet_uri(), array(),'1.0' );
	wp_enqueue_style( 'so-font', get_template_directory_uri() . '/assets/fonts/fonts.css', array(), null, 'all' );
	wp_enqueue_style("font-awesome",get_bloginfo('stylesheet_directory').'/assets/css/font-awesome.min.css');
	wp_enqueue_style("slick-theme",get_bloginfo('stylesheet_directory').'/assets/css/slick-theme.css');
	wp_enqueue_style("slick",get_bloginfo('stylesheet_directory').'/assets/css/slick.css');
	wp_enqueue_style("template_styles",get_bloginfo('stylesheet_directory').'/assets/css/template_styles.css');
    wp_enqueue_style("anyweb-style-scss",get_bloginfo('stylesheet_directory').'/assets/css/scss-style.css');
	// wp_enqueue_style("owl_carousel_min",get_stylesheet_directory_uri() ."/assets/css/owl.carousel.min.css");
	// wp_enqueue_style("owl_theme_default_min",get_stylesheet_directory_uri() ."//assets/css/owl.theme.default.min.css");
}
add_action( 'wp_enqueue_scripts', 'anyweb_styles' );

/**
 * Enqueue scripts
 */
add_action( 'wp_enqueue_scripts', 'anyweb_scripts' );
function anyweb_scripts() {
	// wp_enqueue_script( 'jqur', '//code.jquery.com/jquery-3.7.1.min.js', null, true );
	// wp_enqueue_script( 'migrate', '//code.jquery.com/jquery-migrate-3.4.1.min.js', null, true );



	wp_enqueue_script( 'jqur', '//code.jquery.com/jquery-3.6.0.min.js', null, true );
	wp_enqueue_script( 'migrate', '//code.jquery.com/jquery-migrate-3.3.2.min.js', null, true );
    wp_enqueue_script("so-bootstrap",get_template_directory_uri() . "/assets/js/bootstrap.bundle.min.js",array('jqur', 'migrate'),_S_VERSION, true);

    wp_enqueue_script(
        'photoswipe-core',
        get_template_directory_uri() . '/assets/js/photoswipe/dist/umd/photoswipe.umd.min.js',
        array(),
        null,
        true
    );

    wp_enqueue_script(
        'photoswipe-lightbox',
        get_template_directory_uri() . '/assets/js/photoswipe/dist/umd/photoswipe-lightbox.umd.min.js',
        array('photoswipe-core'),
        null,
        true
    );

    wp_enqueue_script("so-formValidate",get_template_directory_uri() . "/assets/js/formValidate/validate.js",array('jqur', 'migrate'),_S_VERSION, true);
    wp_enqueue_script("so-app",get_template_directory_uri() . "/assets/js/app.js",array('jqur', 'migrate'),_S_VERSION, true);
    wp_enqueue_script("so-navigation",get_template_directory_uri() . "/assets/js/navigation.js",array('jqur', 'migrate'),_S_VERSION, true);
    // wp_enqueue_script("so-cutomizer",get_template_directory_uri() . "/assets/js/customizer.js",array('jqur', 'migrate'),_S_VERSION, true);
    wp_enqueue_script( 'slickminjs', get_stylesheet_directory_uri() . '/assets/js/slick.min.js', array('jqur', 'migrate'), null, true );
    wp_enqueue_script( 'slickjs', get_stylesheet_directory_uri() . '/assets/js/slick.js', array('jqur', 'migrate'), null, true );
    wp_enqueue_script( 'mCustomScrollbar', get_stylesheet_directory_uri() . '/assets/js/mCustomScrollbar/jquery.mCustomScrollbar.js', array('jqur', 'migrate'), null, true );
	wp_enqueue_script( 'formstyler', get_stylesheet_directory_uri() . '/assets/js/formstyler/jquery.formstyler.js', array('jqur', 'migrate', 'mCustomScrollbar'), null, true );
    wp_enqueue_script("so-sliders",get_template_directory_uri() . "/assets/js/sliders.js",array('jqur', 'migrate'),_S_VERSION, true);

    $data = [
		'directory_uri' => get_stylesheet_directory_uri()
	];
	
	wp_add_inline_script( 'so-app', 'const myScriptData = ' . wp_json_encode( $data ), 'before' );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
	wp_localize_script('so-app', 'soJsLet',  array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('so_creator_nonce')
	));

    $nonce = wp_create_nonce('add_more-nonce');

    wp_localize_script('so-app',
        'add_more_object',
        array(
            'url' => admin_url( 'admin-ajax.php' ),
            'nonce' => $nonce
        )
    );
	
	 wp_localize_script('aws-script', 'aws_vars', array(
        'sale'       => __('Розпродаж!', 'advanced-woo-search'),
         'showmore'   => __('Переглянути всі результати', 'advanced-woo-search'),
         'noresults'  => __('Нічого не знайдено', 'advanced-woo-search'),
        ));

    // Магазин (архив товаров)
    $is_shop = is_shop() || is_post_type_archive('product');

    // Категории/метки товаров
    $is_product_tax = is_product_taxonomy() || is_product_category() || is_product_tag();

    if ( $is_shop || $is_product_tax ) {
        wp_enqueue_script('shop-helper', get_stylesheet_directory_uri() . '/assets/js/shop-helper.js', ['jquery'], null, true);

        // Можно передать в JS полезные данные
        wp_localize_script('shop-helper', 'SHOP_CTX', [
                'isShop' => true,
                'shopUrl' => get_permalink( wc_get_page_id('shop') ),
        ]);


    }
}

/**
* Enqueue admin panel styles.
*/
add_action('admin_head', 'anyweb_admin_styles');
function anyweb_admin_styles(){
	wp_enqueue_style("style-admin",get_bloginfo('stylesheet_directory')."/assets/css/adminstyle.css");
	}

/**
 * Подключение скрипта admin-custom-script.js
 */
add_action('admin_enqueue_scripts', 'custom_admin_script');
function custom_admin_script() {
    wp_enqueue_script('admin-custom-script', get_stylesheet_directory_uri() . '/assets/js/admin-custom-script.js', array('jquery'), '1.0', true);
}





