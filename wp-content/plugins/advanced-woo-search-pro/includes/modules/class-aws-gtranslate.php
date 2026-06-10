<?php

/**
 * Integration for GTranslate plugin
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('AWS_GTranslate')) :

    /**
     * Class for main plugin functions
     */
    class AWS_GTranslate {

        /**
         * @var AWS_GTranslate The single instance of the class
         */
        protected static $_instance = null;

        private $data = array();

        private $translatex_api_key = '';

        private $google_api_key = '';

        private $enable_integration = 'true';

        private $only_exact_match = 'false';

        private $disable_misspelling = 'false';

        /**
         * Main AWS_GTranslate Instance
         *
         * Ensures only one instance of AWS_GTranslate is loaded or can be loaded.
         *
         * @static
         * @return AWS_GTranslate - Main instance
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Constructor
         */
        public function __construct() {

            /**
             * Filters to totally disable GTranslate integration
             * @param bool $disable_integration Disable or not GTranslate integration
             * @since 3.37
             */
            $disable_integration = apply_filters( 'aws_disable_gtranslate_integration', false );
            if ( $disable_integration ) {
                return;
            }

            $common_settings = AWS_PRO()->get_common_settings();

            if ( $common_settings ) {

                if ( isset( $common_settings['gtranslate_enable'] ) ) {
                    $this->enable_integration = $common_settings['gtranslate_enable'];
                }

                if ( isset( $common_settings['gtranslate_exact_match'] ) ) {
                    $this->only_exact_match = $common_settings['gtranslate_exact_match'];
                }

                if ( isset( $common_settings['gtranslate_disable_misspelling'] ) ) {
                    $this->disable_misspelling = $common_settings['gtranslate_disable_misspelling'];
                }

                if ( isset( $common_settings['gtranslate_google_api_key'] ) ) {
                    $this->google_api_key = $common_settings['gtranslate_google_api_key'];
                }

                if ( isset( $common_settings['gtranslate_translatex_api_key'] ) ) {
                    $this->translatex_api_key = $common_settings['gtranslate_translatex_api_key'];
                }

            }

            if ( defined('AWS_GTRANSLATE_TRANSLATEX_API_KEY') ) {
                $this->translatex_api_key = AWS_GTRANSLATE_TRANSLATEX_API_KEY;
            }

            if ( defined('AWS_GTRANSLATE_GOOGLE_API_KEY') ) {
                $this->google_api_key = AWS_GTRANSLATE_GOOGLE_API_KEY;
            }

            add_filter( 'aws_pre_normalized_search_string', array( $this, 'aws_pre_normalized_search_string' ), 10, 2 );

            add_filter( 'aws_search_data_parameters', array( $this, 'aws_search_data_parameters' ) );

            add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );

            add_filter( 'aws_search_results_all', array( $this, 'aws_search_results_all' ), 999 );

            add_filter( 'aws_admin_page_options', array( $this, 'aws_admin_page_options' ) );

            add_action( 'admin_menu', array( &$this, 'add_admin_page' ), 11 );

            add_filter( 'submenu_file', array( $this, 'submenu_file' ), 10, 2 );

        }

        /*
         * Get current site language
         */
        private function get_current_language() {

            $current_lang = '';

            if ( isset( $_SERVER['HTTP_X_GT_LANG'] ) && $_SERVER['HTTP_X_GT_LANG'] ) {
                $current_lang = sanitize_text_field( $_SERVER['HTTP_X_GT_LANG'] );
            } elseif ( isset( $_COOKIE['googtrans'] ) && $_COOKIE['googtrans'] ) {
                $googtrans = $_COOKIE['googtrans'];
                $parts = explode('/', $googtrans);
                $current_lang = end($parts);
            } elseif ( isset( $_REQUEST['gTranslateLang'] ) && $_REQUEST['gTranslateLang'] ) {
                $current_lang = sanitize_text_field( $_REQUEST['gTranslateLang'] );
            }

            $current_lang = strtolower( $current_lang );

            return $current_lang;

        }

        /*
         * Detect current language and translate search string if needed
         */
        public function aws_pre_normalized_search_string( $s ) {

            if ( $this->enable_integration === 'false' ) {
                return $s;
            }

            $language_parts = explode( '_', get_locale() );
            $default_lang = $language_parts[0];

            $current_lang = $this->get_current_language();

            if ( trim( $s ) && $current_lang && $current_lang != $default_lang ) {

                $s_translated = false;

                // choose what API to use for translation
                if ( $this->google_api_key ) {
                    $s_translated = $this->google_translate( $s, $default_lang );
                }
                elseif ( $this->translatex_api_key ) {
                    $s_translated = $this->translatex_translate( $s, $default_lang, $current_lang );
                }

                if ( $s_translated && is_array( $s_translated ) ) {
                    $this->data['has_translation'] = true;
                    $s = implode( ' ', $s_translated );
                }

            }

            return $s;

        }

        /*
         * Change search parameters ( if needed )
         */
        public function aws_search_data_parameters( $data ) {

            if ( isset( $this->data['has_translation'] ) && $this->data['has_translation'] ) {

                if ( $this->only_exact_match === 'true' ) {
                    $data['search_exact'] = 'true';
                }

                if ( $this->disable_misspelling === 'true' ) {
                    $this->data['fuzzy'] = 'false';
                }

            }

            return $data;

        }

        /*
         * Detect current language on frontend and send via ajax event
         */
        public function wp_enqueue_scripts() {

            $script = '
                function aws_ajax_request_params( data, options ) {
                
                    function gtaws_get_cookie(name) {
                        const value = `; ${decodeURIComponent(document.cookie)}`;
                        const parts = value.split(`; ${name}=`);
                        if ( parts.length === 2 ) return parts.pop().split(";").shift();
                        return "";
                    }
                    
                    var current_language = gtaws_get_cookie("googtrans").split("/").pop() || document.documentElement.lang;

                    data.gTranslateLang = current_language;
                    data.gTranslatePage = window.location.href;
                    
                    return data;
                    
                }
                function aws_ajax_request_url( ajaxUrl, options ) {
                    if ( typeof options.ajaxData.gTranslateLang !== "undefined" && options.ajaxData.gTranslateLang && window.location.href.indexOf("/" + options.ajaxData.gTranslateLang + "/") !== -1 ) {
                        const absolutePattern = /^[a-z][a-z0-9+.-]*:|^\/\//i;
                        if ( ajaxUrl.indexOf("/" + options.ajaxData.gTranslateLang + "/") === -1 && ! absolutePattern.test( ajaxUrl ) ) {
                            ajaxUrl = "/" + options.ajaxData.gTranslateLang + "/" + ajaxUrl.replace(/^\/+/, "");
                        }
                    }
                    return ajaxUrl;
                }
                AwsHooks.add_filter( "aws_ajax_request_url", aws_ajax_request_url );
                AwsHooks.add_filter( "aws_ajax_request_params", aws_ajax_request_params );
            ';

            wp_add_inline_script( 'aws-pro-script', $script);

        }

        /*
         * Describe ajax json fields that need to be translated
         */
        public function aws_search_results_all( $results ) {

            $current_lang = $this->get_current_language();

            $current_page_url = isset( $_REQUEST['gTranslatePage'] ) ? sanitize_url( $_REQUEST['gTranslatePage'] ) : '';

            if ( $current_lang && $current_page_url &&
                ( strpos( $current_page_url, '/' . $current_lang . '/' ) !== false || strpos( $current_page_url, $current_lang . '.' ) !== false  )
            ) {

                $results['gt_translate_keys'] = array(
                    array( 'key' => 'products', 'format' => 'list' ),
                    array( 'key' => 'tax', 'format' => 'list' ),
                    array( 'key' => 'users', 'format' => 'list' ),
                );

                if ( isset( $results['products'] ) && is_array( $results['products'] ) ) {
                    foreach ( $results['products'] as $key => $product ) {
                        $results['products'][$key]['gt_translate_keys'] = array(
                            array( 'key' => 'title', 'format' => 'html' ),
                            array( 'key' => 'excerpt', 'format' => 'html' ),
                            array( 'key' => 'categories', 'format' => 'html' ),
                            array( 'key' => 'tags', 'format' => 'html' ),
                            array( 'key' => 'reviews', 'format' => 'html' ),
                            array( 'key' => 'link', 'format' => 'url' ),
                            array( 'key' => 'brands.#.name', 'format' => 'html' ),
                        );
                    }
                }

                if ( isset( $results['tax'] ) && is_array( $results['tax'] ) ) {
                    foreach ( $results['tax'] as $tax_name => $taxonomy ) {
                        foreach ( $taxonomy as $key => $tax_item ) {
                            $results['tax'][$tax_name][$key]['gt_translate_keys'] = array(
                                array( 'key' => 'name', 'format' => 'html' ),
                                array( 'key' => 'excerpt', 'format' => 'html' ),
                                array( 'key' => 'link', 'format' => 'url' ),
                            );
                        }
                    }
                }

                if ( isset( $results['users'] ) && is_array( $results['users'] ) ) {
                    foreach ( $results['users'] as $user_id => $user ) {
                        foreach ( $user as $key => $user_item ) {
                            $results['users'][$user_id][$key]['gt_translate_keys'] = array(
                                array( 'key' => 'name', 'format' => 'html' ),
                                array( 'key' => 'excerpt', 'format' => 'html' ),
                                array( 'key' => 'link', 'format' => 'url' ),
                            );
                        }
                    }
                }

            }

            return $results;

        }

        /*
         * Add new options related to GTranslate integration
         */
        public function aws_admin_page_options( $options ) {

            $disable_google_api_key = false;
            $disable_google_api_key_text = '';
            if ( defined('AWS_GTRANSLATE_GOOGLE_API_KEY') ) {
                $disable_google_api_key = true;
                $disable_google_api_key_text = '<br><span style="font-weight:600;">' . sprintf( __( 'Option is set via %s constant.', 'advanced-woo-search' ), 'AWS_GTRANSLATE_GOOGLE_API_KEY' ) . '</span>';
            }

            $disable_translatex_api_key = false;
            $disable_translatex_api_key_text = '';
            if ( defined('AWS_GTRANSLATE_TRANSLATEX_API_KEY') ) {
                $disable_translatex_api_key = true;
                $disable_translatex_api_key_text = '<br><span style="font-weight:600;">' . sprintf( __( 'Option is set via %s constant.', 'advanced-woo-search' ), 'AWS_GTRANSLATE_TRANSLATEX_API_KEY' ) . '</span>';
            }

            $options['gtranslate'][] = array(
                "name"    => __( "GTranslate integration", "advanced-woo-search" ),
                "desc"    => __( "Advanced integration with GTranslate plugin. Allows you to search for products in the current site language.", "advanced-woo-search" ) . ' <a href="https://advanced-woo-search.com/guide/gtranslate/?utm_source=wp-plugin&utm_medium=settings&utm_campaign=guide" target="_blank">' . __( 'Learn more.', 'advanced-woo-search' ) . '</a>',
                "type"    => "heading"
            );

            $options['gtranslate'][] = array(
                "name"  => __( "Enable integration", "advanced-woo-search" ),
                "desc"  => __( "Enable or not GTranslate plugin integration.", "advanced-woo-search" ),
                "id"    => "gtranslate_enable",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['gtranslate'][] = array(
                "name"  => __( "Only exact match", "advanced-woo-search" ),
                "desc"  => __( "Enable only full word matching for all searches with translated products. This rewrite 'Exact match' option for all search forms.", "advanced-woo-search" ),
                "id"    => "gtranslate_exact_match",
                "value" => 'false',
                "type"  => "toggler",
            );

            $options['gtranslate'][] = array(
                "name"  => __( "Disable misspelling fix", "advanced-woo-search" ),
                "desc"  => __( "Disable typo correction for all searches with translated products. This rewrite 'Misspelling fix' option for all search forms.", "advanced-woo-search" ),
                "id"    => "gtranslate_disable_misspelling",
                "value" => 'false',
                "type"  => "toggler",
            );

            $options['gtranslate'][] = array(
                "name"  => __( "Google API key", "advanced-woo-search" ),
                "desc"  => __( "API key to use Google translation service.", "advanced-woo-search" ) . ' <a href="https://advanced-woo-search.com/guide/gtranslate/?utm_source=wp-plugin&utm_medium=settings&utm_campaign=guide#how-to-get-google-api-key" target="_blank">' . __( 'How to get it.', 'advanced-woo-search' ) . '</a>' . $disable_google_api_key_text,
                "id"    => "gtranslate_google_api_key",
                "value" => '',
                "disabled" => $disable_google_api_key,
                "type"  => "text"
            );

            $options['gtranslate'][] = array(
                "name"    => __( "", "advanced-woo-search" ),
                "desc"    => __( "OR", "advanced-woo-search" ),
                "type"    => "heading"
            );

            $options['gtranslate'][] = array(
                "name"  => __( "TranslateX API key", "advanced-woo-search" ),
                "desc"  => __( "API key to use TranslateX translation service.", "advanced-woo-search" ) . ' <a href="https://advanced-woo-search.com/guide/gtranslate/?utm_source=wp-plugin&utm_medium=settings&utm_campaign=guide#how-to-get-translatex-api-key" target="_blank">' . __( 'How to get it.', 'advanced-woo-search' ) . '</a>' . $disable_translatex_api_key_text,
                "id"    => "gtranslate_translatex_api_key",
                "value" => '',
                "disabled" => $disable_translatex_api_key,
                "type"  => "text"
            );

            return $options;

        }

        /*
         * Add options page
         */
        public function add_admin_page() {
            add_submenu_page( 'aws-options', __( 'GTranslate', 'advanced-woo-search' ), __( 'GTranslate', 'advanced-woo-search' ), AWS_Admin_Helpers::user_admin_capability(), admin_url( 'admin.php?page=aws-options&tab=gtranslate' ) );
        }

        /*
         * Change current class for premium tab
         */
        public function submenu_file( $submenu_file, $parent_file ) {
            if ( $parent_file === 'aws-options' && isset( $_GET['tab'] ) && $_GET['tab'] === 'gtranslate' ) {
                $submenu_file = admin_url( 'admin.php?page=aws-options&tab=gtranslate' );
            }
            return $submenu_file;
        }

        /*
         * Send API request to Google translate
         */
        public function google_translate( $text, $to, $from = '' ) {

            $api_key = $this->google_api_key;

            $endpoint = 'https://translation.googleapis.com/language/translate/v2';

            $body = array(
                'q'      => is_array($text) ? $text : array( $text ),
                'target' => $to,
            );

            if ( $from ) {
                $body['source'] = $from;
            }

            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json; charset=utf-8',
                ),
                'body'    => wp_json_encode( $body ),
                'timeout' => 15,
            );

            $url = add_query_arg( 'key', $api_key, $endpoint );

            $response = wp_remote_post( $url, $args );

            if ( is_wp_error( $response ) ) {
                return new WP_Error( 'http_request_failed', $response->get_error_message() );
            }

            $code = wp_remote_retrieve_response_code( $response );
            $json = wp_remote_retrieve_body( $response );
            $data = json_decode( $json, true );

            if ( $code !== 200 ) {
                $message = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Unknown API error';
                return new WP_Error( 'api_error', $message );
            }

            $translations = wp_list_pluck( $data['data']['translations'], 'translatedText' );

            return $translations;

        }

        /*
         * Send API request to translatex
         */
        public function translatex_translate( $text, $to, $from ) {

            $url = "https://api.translatex.com/translate?sl={$from}&tl={$to}&key={$this->translatex_api_key}";

            $options = array(
                'http' => array(
                    'method'  => 'POST',
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'content' => 'text=' . rawurlencode( $text )
                )
            );

            $context = stream_context_create( $options );
            $response = @file_get_contents( $url, false, $context );

            $data = array();
            if ( $response !== false ) {
                $data = json_decode( $response, true );
                if ( json_last_error() !== JSON_ERROR_NONE ) {
                    $data = array();
                }
            }

            $data = isset( $data['translation'] ) ? $data['translation'] : array();

            return $data;

        }

    }

endif;

AWS_GTranslate::instance();