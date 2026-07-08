<?php
/**
 * WC Vendors plugin support
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'AWS_WC_Vendors' ) ) :

    /**
     * Class
     */
    class AWS_WC_Vendors {

        /**
         * Main AWS_WC_Vendors Instance
         *
         * Ensures only one instance of AWS_WC_Vendors is loaded or can be loaded.
         *
         * @static
         * @return AWS_WC_Vendors - Main instance
         */
        protected static $_instance = null;

        public $form_id = 1;
        public $is_ajax = true;

        /**
         * Main AWS_WC_Vendors Instance
         *
         * Ensures only one instance of AWS_WC_Vendors is loaded or can be loaded.
         *
         * @static
         * @return AWS_WC_Vendors - Main instance
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Constructor
         */
        public function __construct() {

            add_action( 'aws_search_start', array( $this, 'search_start' ), 10, 2 );

            add_filter( 'aws_admin_page_options', array( $this, 'aws_admin_page_options' ) );

            add_filter( 'aws_search_pre_filter_single_product',  array( $this, 'aws_search_pre_filter_single_product' ), 1, 3 );

            add_filter( 'aws_indexed_content', array( $this, 'aws_indexed_content' ), 1, 3 );

            add_filter( 'aws_search_users_results', array( $this, 'aws_search_users_results' ), 1, 3 );

            add_filter( 'aws_users_search_query', array( $this, 'aws_users_search_query' ), 1, 3 );

            add_filter( 'aws_front_data_parameters', array( $this, 'aws_front_data_parameters' ), 1, 2 );
            add_filter( 'aws_searchbox_markup', array( $this, 'aws_searchbox_markup' ), 1, 2  );
            add_filter( 'aws_search_query_array', array( $this, 'aws_search_query_array' ), 1 );

            add_filter( 'aws_admin_filter_rules', array( $this, 'aws_admin_filter_rules' ), 1 );

            add_filter( 'aws_filters_condition_rules', array( $this, 'condition_rules' ), 1 );

        }

        /*
         * On search start
         */
        public function search_start( $s, $form_id ) {
            $this->form_id = $form_id;
            $this->is_ajax = isset( $_GET['type_aws'] ) ? false : true;
        }

        /*
         * Add admin options
         */
        public function aws_admin_page_options( $options ) {

            $new_options = array();

            if ( $options ) {
                foreach ( $options as $section_name => $section ) {
                    foreach ( $section as $values ) {

                        $new_options[$section_name][] = $values;

                        if ( isset( $values['id'] ) && $values['id'] === 'show_stock_quantity' ) {

                            $new_options[$section_name][] = array(
                                "name"  => __( "Show WC Vendors vendor info?", "advanced-woo-search" ),
                                "desc"  => __( "Show or not WC Vendors vendor information all products inside search results.", "advanced-woo-search" ),
                                "id"    => "show_wcvendors_badge",
                                "inherit" => "true",
                                "value" => 'true',
                                "type"  => "radio",
                                'choices' => array(
                                    'full'  => __( 'Show full', 'advanced-woo-search' ),
                                    'part' => __( 'Show partial ', 'advanced-woo-search' ),
                                    'off' => __( 'Off', 'advanced-woo-search' )
                                ),
                                "section" => "content",
                            );

                            $new_options[$section_name][] = array(
                                "name"  => __( "Limit results inside the vendor's store?", "advanced-woo-search" ),
                                "desc"  => __( "Force search to show only current vendor products is searching inside the vendor store page.", "advanced-woo-search" ),
                                "id"    => "wcvendors_limit",
                                "inherit" => "true",
                                "value" => 'true',
                                "type"  => "toggler",
                                "section" => "content",
                            );

                        }

                    }
                }

                return $new_options;

            }

            return $options;

        }

        /*
         * Add store vendor badge inside search results
         */
        public function aws_search_pre_filter_single_product( $result, $post_id, $product ) {

            $show_vendor_info = AWS_PRO()->get_settings( 'show_wcvendors_badge', $this->form_id );

            if ( $show_vendor_info === 'off' ) {
                return $result;
            }

            $vendor_id = class_exists('WCV_Vendors') ? WCV_Vendors::get_vendor_from_product( $result['parent_id'] ) : 0;

            if ( $vendor_id && $vendor_id !== -1 ) {

                $store_icon = $this->get_store_icon( $vendor_id );
                $sold_by = WCV_Vendors::get_vendor_sold_by( $vendor_id );
                $shop_page = WCV_Vendors::get_vendor_shop_page( $vendor_id );

                $badge = '';

                if ( $show_vendor_info === 'full' && $sold_by && $shop_page && $store_icon ) {

                    $badge .= '<div class="aws-wcvendors-badge" style="display:flex;align-items:center;margin:10px 0 0 0;">';

                        $badge .= '<div style="margin-right: 10px" class="aws-wcvendors-vendor-image">';
                            $badge .= '<img style="border-radius: 50%;max-width: 25px;" src="' . esc_url( $store_icon ) . '" alt="' . esc_attr( $sold_by ) . '">';
                        $badge .= '</div>';

                        $badge .= '<div class="aws-wcvendors-vendor-name">';
                            $badge .= '<a href="' . esc_attr( $shop_page ) . '"><span>' . esc_html( $sold_by ) . '</span></a>';
                        $badge .= '</div>';

                    $badge .= '</div>';

                } elseif ( $sold_by && $shop_page ) {

                    $sold_by_label     = __( get_option( 'wcvendors_label_sold_by' ), 'wc-vendors' );
                    $sold_by_separator = __( get_option( 'wcvendors_label_sold_by_separator' ), 'wc-vendors' );

                    $badge .= '<div class="aws-wcvendors-badge" style="margin:10px 0 0 0;">' . $sold_by_label . $sold_by_separator . ' <a href="' . esc_attr( $shop_page ) . '">' . esc_html( $sold_by ) . '</a></div>';

                }

                $result['excerpt'] .= $badge;

            }

            return $result;

        }

        /*
         * Add product vendor data inside index table
         */
        public function aws_indexed_content( $content, $id, $product ) {
            if ( apply_filters( 'aws_wcvendors_index_store_name', true ) && class_exists( 'WCV_Vendors' ) ) {
                $vendor_id = WCV_Vendors::get_vendor_from_product( $id );
                if ( $vendor_id && $vendor_id !== -1 ) {
                    $sold_by = WCV_Vendors::get_vendor_sold_by( $vendor_id );
                    if ( $sold_by ) {
                        $content .= ' ' . $sold_by;
                    }
                }
            }
            return $content;
        }

        /*
         * Update users results
         */
        public function aws_search_users_results( $result_array, $roles, $search_string ) {

            if ( array_search( 'vendor', $roles ) !== false ) {
                foreach( $result_array as $vendor_id => $user_params ) {
                    $user_meta = get_userdata( $vendor_id );
                    $user_roles = $user_meta->roles;
                    if ( in_array( 'vendor', $user_roles ) && class_exists('WCV_Vendors') && class_exists('WCVendors_Pro_Ratings_Controller') ) {

                        $store_icon = $this->get_store_icon( $vendor_id );
                        $sold_by = WCV_Vendors::get_vendor_sold_by( $vendor_id );
                        $shop_page = WCV_Vendors::get_vendor_shop_page( $vendor_id );
                        $ratings_count = WCVendors_Pro_Ratings_Controller::get_ratings_count( $vendor_id, true );
                        $ratings_average = WCVendors_Pro_Ratings_Controller::get_ratings_average( $vendor_id, true );
                        $ratings_average = $ratings_average && $ratings_count ? intval($ratings_average ) * 20 : 100;

                        $reviews = sprintf( _nx( '1 Review', '%1$s Reviews', $ratings_count, 'product reviews', 'advanced-woo-search' ), number_format_i18n( $ratings_count ) );

                        if ( $sold_by && $shop_page ) {

                            $result_array[$vendor_id][0]['name'] = $sold_by;
                            $result_array[$vendor_id][0]['link'] = $shop_page;
                            $result_array[$vendor_id][0]['image'] = $store_icon;

                            $rating = '';

                            $rating .= '<span class="wcvendors-vendor-info-wrap" style="border:none;margin:0;padding:0;">';
                                $rating .= '<span class="wcvendors-vendor-info">';

                                    $rating .= '<span class="wcvendors-vendor-rating">';
                                        if ( $ratings_count ) {
                                            $rating .= '<span class="aws_rating">';
                                                $rating .= '<span class="aws_votes">';
                                                    $rating .= '<span class="aws_current_votes" style="width: '. $ratings_average .'%;"></span>';
                                                $rating .= '</span>';
                                            $rating .= '</span>';
                                        }
                                    $rating .= '</span>';

                                    if ( $ratings_count ) {
                                        $rating .= '<span class="wcvendors-ratings-count">' . $reviews . '</span>';
                                    }

                                    $result_array[$vendor_id][0]['excerpt'] .= $rating;
                                $rating .= '</span>';
                            $rating .= '</span>';

                        }

                    }
                }
            }

            return $result_array;

        }

        /*
         * Search for vendors via vendor store name
         */
        public function aws_users_search_query( $sql, $roles, $search_query ) {
            $sql = str_replace( "'last_name'", "'last_name', 'pv_shop_name'", $sql );
            return $sql;
        }

        /*
         * Add vendor ID as search parameter
         */
        public function aws_front_data_parameters( $params, $form_id ) {

            $vendor_id = $this->get_store_id();

            if ( $vendor_id  ) {
                $params['data-tax'] = 'store:' . $vendor_id;
            }

            return $params;

        }

        /*
         * Add vendor ID as hidden input
         */
        public function aws_searchbox_markup( $markup, $params ) {

            $vendor_id = $this->get_store_id();
            $hidden = '<input type="hidden" name="type_aws" value="true">';

            if ( $vendor_id  ) {
                $new_fields = '<input type="hidden" name="aws_tax" value="store:'.$vendor_id.'">';
                $markup = str_replace( $hidden, $hidden . $new_fields, $markup );
            }

            return $markup;

        }

        /*
         * Limit search inside vendor shop
         */
        public function aws_search_query_array( $query ) {

            global $wpdb;

            $limit_results = AWS_PRO()->get_settings( 'wcvendors_limit', $this->form_id );
            if ( $limit_results === 'false' ) {
                return $query;
            }

            $vendor_id = false;

            if ( isset( $_REQUEST['aws_tax'] ) && $_REQUEST['aws_tax'] && strpos( $_REQUEST['aws_tax'], 'store:' ) !== false ) {
                $vendor_id = intval( str_replace( 'store:', '', sanitize_text_field( $_REQUEST['aws_tax'] ) ) );
            } else {
                $vendor_id = $this->get_store_id();
            }

            if ( $vendor_id ) {

                $query['search'] .= " AND ( id IN ( SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_author = {$vendor_id} ) )";

            }

            return $query;

        }

        /*
         * Add new filter rules
         */
        public function aws_admin_filter_rules( $options ) {

            $options['product'][] = array(
                "name" => __( "WC Vendors: Is product sold by any vendor", "advanced-woo-search" ),
                "id"   => "product_wcvendors_is_sold_by_vendor",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['product'][] = array(
                "name" => __( "WC Vendors: Product sold by", "advanced-woo-search" ),
                "id"   => "product_wcvendors_sold_by",
                "type" => "callback",
                "operators" => "lists",
                "multiple" => true,
                "choices" => array(
                    'callback' => array($this, 'get_all_vendors'),
                    'params'   => array()
                ),
            );

            $options['product'][] = array(
                "name" => __( "WC Vendors: Sold by verified vendor", "advanced-woo-search" ),
                "id"   => "product_wcvendors_is_verified",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['product'][] = array(
                "name" => __( "WC Vendors: Sold by trusted vendor", "advanced-woo-search" ),
                "id"   => "product_wcvendors_is_trusted",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['product'][] = array(
                "name" => __( "WC Vendors: Store rating", "advanced-woo-search" ),
                "id"   => "product_wcvendors_store_rating",
                "type" => "number",
                "step" => "0.01",
                "operators" => "equals_compare",
            );

            $options['product'][] = array(
                "name" => __( "WC Vendors: Store reviews count", "advanced-woo-search" ),
                "id"   => "product_wcvendors_store_reviews",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['user'][] = array(
                "name" => __( "WC Vendors: User is vendor", "advanced-woo-search" ),
                "id"   => "user_wcvendors_is_vendor",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['user'][] = array(
                "name" => __( "WC Vendors: User is verified vendor", "advanced-woo-search" ),
                "id"   => "user_wcvendors_is_verified_vendor",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['user'][] = array(
                "name" => __( "WC Vendors: User is trusted vendor", "advanced-woo-search" ),
                "id"   => "user_wcvendors_is_trusted_vendor",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['user'][] = array(
                "name" => __( "WC Vendors: Store rating", "advanced-woo-search" ),
                "id"   => "user_wcvendors_store_rating",
                "type" => "number",
                "step" => "0.01",
                "operators" => "equals_compare",
            );

            $options['user'][] = array(
                "name" => __( "WC Vendors: Store reviews count", "advanced-woo-search" ),
                "id"   => "user_wcvendors_store_reviews",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['user'][] = array(
                "name" => __( "WC Vendors: Store products number", "advanced-woo-search" ),
                "id"   => "user_wcvendors_store_products",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['user'][] = array(
                "name" => __( "WC Vendors: Store total sales", "advanced-woo-search" ),
                "id"   => "user_wcvendors_store_solds",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['current_user'][] = array(
                "name" => __( "WC Vendors: Current user is vendor", "advanced-woo-search" ),
                "id"   => "current_user_wcvendors_is_vendor",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['current_page'][] = array(
                "name" => __( "WC Vendors: Current page is vendor store", "advanced-woo-search" ),
                "id"   => "current_page_wcvendors_store",
                "type" => "bool",
                "operators" => "equals",
            );

            return $options;

        }

        /*
         * Add custom condition rule method
         */
        public function condition_rules( $rules ) {
            $rules['product_wcvendors_is_sold_by_vendor'] = array( $this, 'wcvendors_is_sold_by_vendor' );
            $rules['product_wcvendors_sold_by'] = array( $this, 'wcvendors_sold_by' );
            $rules['product_wcvendors_is_verified'] = array( $this, 'wcvendors_is_verified' );
            $rules['product_wcvendors_is_trusted'] = array( $this, 'wcvendors_is_trusted' );
            $rules['product_wcvendors_store_rating'] = array( $this, 'wcvendors_store_rating' );
            $rules['product_wcvendors_store_reviews'] = array( $this, 'wcvendors_store_reviews' );
            $rules['user_wcvendors_is_vendor'] = array( $this, 'user_wcvendors_is_vendor' );
            $rules['user_wcvendors_is_verified_vendor'] = array( $this, 'user_wcvendors_is_verified_vendor' );
            $rules['user_wcvendors_is_trusted_vendor'] = array( $this, 'user_wcvendors_is_trusted_vendor' );
            $rules['user_wcvendors_store_rating'] = array( $this, 'user_wcvendors_store_rating' );
            $rules['user_wcvendors_store_reviews'] = array( $this, 'user_wcvendors_store_reviews' );
            $rules['user_wcvendors_store_products'] = array( $this, 'user_wcvendors_store_products' );
            $rules['user_wcvendors_store_solds'] = array( $this, 'user_wcvendors_store_solds' );
            $rules['current_user_wcvendors_is_vendor'] = array( $this, 'current_user_wcvendors_is_vendor' );
            $rules['current_page_wcvendors_store'] = array( $this, 'current_page_wcvendors_store' );
            return $rules;
        }

        /*
         * Condition: Is product sold by any available vendor
         */
        public function wcvendors_is_sold_by_vendor( $condition_rule ) {
            global $wpdb;

            $relation = $condition_rule['operator'] === 'equal' ? 'IN' : 'NOT IN';
            $value_relation = $condition_rule['value'] === 'true' ? 'IN' : 'NOT IN';

            $vendors = array( 0 );
            $vendors_list = $this->get_all_vendors();
            if ( $vendors_list ) {
                $vendors = array_keys( $vendors_list );
            }

            $vendors_string = implode( ',', $vendors );

            $string = "( id {$relation} (
                   SELECT $wpdb->posts.ID
                   FROM $wpdb->posts
                   WHERE $wpdb->posts.post_author {$value_relation} ({$vendors_string})
                ))";

            return $string;

        }

        /*
         * Condition: Is product sold by vendor
         */
        public function wcvendors_sold_by( $condition_rule ) {
            global $wpdb;

            $values = isset( $condition_rule['value'] ) ? $condition_rule['value'] : array();
            $values = is_array( $values ) ? $values : array( $values );
            $values = array_map( 'intval', $values );
            $values = array_filter( $values );

            $relation = in_array( $condition_rule['operator'], array( 'equal', 'in_list' ), true ) ? 'IN' : 'NOT IN';

            if ( empty( $values ) ) {
                return 'IN' === $relation ? '( 1=2 )' : '( 1=1 )';
            }

            $values_string = implode( ',', array_unique( $values ) );

            $string = "( id {$relation} (
                   SELECT $wpdb->posts.ID
                   FROM $wpdb->posts
                   WHERE $wpdb->posts.post_author IN ({$values_string})
                ))";

            return $string;
        }

        /*
         * Condition: Is product sold by verified vendor
         */
        public function wcvendors_is_verified( $condition_rule ) {
            global $wpdb;

            $relation = $condition_rule['operator'] === 'equal' ? 'IN' : 'NOT IN';
            $value_relation = $condition_rule['value'] === 'true' ? 'IN' : 'NOT IN';

            $vendors = array( 0 );
            $vendors_list = $this->get_all_vendors(
                array (
                    'meta_query' => array(
                        array(
                            'key' => '_wcv_verified_vendor',
                            'value' => 'yes',
                            'compare' => '=='
                        ),
                    )
                )
            );

            if ( $vendors_list ) {
                $vendors = array_keys( $vendors_list );
            }

            $vendors_string = implode( ',', $vendors );

            $string = "( id {$relation} (
                   SELECT $wpdb->posts.ID
                   FROM $wpdb->posts
                   WHERE $wpdb->posts.post_author {$value_relation} ({$vendors_string})
                ))";

            return $string;

        }

        /*
         * Condition: Is product sold by trusted vendor
         */
        public function wcvendors_is_trusted( $condition_rule ) {
            global $wpdb;

            $relation = $condition_rule['operator'] === 'equal' ? 'IN' : 'NOT IN';
            $value_relation = $condition_rule['value'] === 'true' ? 'IN' : 'NOT IN';

            $vendors = array( 0 );
            $vendors_list = $this->get_all_vendors(
                array (
                    'meta_query' => array(
                        array(
                            'key' => '_wcv_trusted_vendor',
                            'value' => 'yes',
                            'compare' => '=='
                        ),
                    )
                )
            );

            if ( $vendors_list ) {
                $vendors = array_keys( $vendors_list );
            }

            $vendors_string = implode( ',', $vendors );

            $string = "( id {$relation} (
                   SELECT $wpdb->posts.ID
                   FROM $wpdb->posts
                   WHERE $wpdb->posts.post_author {$value_relation} ({$vendors_string})
                ))";

            return $string;

        }

        /*
         * Condition: Store rating for products
         */
        public function wcvendors_store_rating( $condition_rule ) {
            global $wpdb;

            switch ( $condition_rule['operator'] ) {
                case 'equal':
                    $operator = '=';
                    break;
                case 'not_equal':
                    $operator = '!=';
                    break;
                case 'greater':
                    $operator = '>=';
                    break;
                default:
                    $operator = '<=';
            }

            $vendors = array( 0 );
            $vendors_list = $this->get_all_vendors();
            if ( $vendors_list && class_exists('WCVendors_Pro_Ratings_Controller') ) {

                foreach ( $vendors_list as $vendor_id => $vendor_data ) {

                    $ratings_count = WCVendors_Pro_Ratings_Controller::get_ratings_count( $vendor_id, true );
                    $ratings_average = WCVendors_Pro_Ratings_Controller::get_ratings_average( $vendor_id, true );

                    $srore_rating_val = $ratings_count ? $ratings_average : '0';

                    if ( version_compare( $srore_rating_val, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendor_id;
                    }

                }

            }

            $vendors_string = implode( ',', $vendors );

            $string = "( id IN (
                   SELECT $wpdb->posts.ID
                   FROM $wpdb->posts
                   WHERE $wpdb->posts.post_author IN ({$vendors_string})
                ))";

            return $string;

        }

        /*
         * Condition: Store reviews count for products
         */
        public function wcvendors_store_reviews( $condition_rule ) {
            global $wpdb;

            switch ( $condition_rule['operator'] ) {
                case 'equal':
                    $operator = '=';
                    break;
                case 'not_equal':
                    $operator = '!=';
                    break;
                case 'greater':
                    $operator = '>=';
                    break;
                default:
                    $operator = '<=';
            }

            $vendors = array( 0 );
            $vendors_list = $this->get_all_vendors();
            if ( $vendors_list && class_exists('WCVendors_Pro_Ratings_Controller') ) {
                foreach ( $vendors_list as $vendor_id => $vendor_data ) {
                    $ratings_count = WCVendors_Pro_Ratings_Controller::get_ratings_count( $vendor_id, true );
                    $srore_rating_val = $ratings_count ? $ratings_count : '0';
                    if ( version_compare( $srore_rating_val, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendor_id;
                    }
                }
            }

            $vendors_string = implode( ',', $vendors );

            $string = "( id IN (
                   SELECT $wpdb->posts.ID
                   FROM $wpdb->posts
                   WHERE $wpdb->posts.post_author IN ({$vendors_string})
                ))";

            return $string;

        }

        /*
         * Condition: Is user is WC Vendors vendor
         */
        public function user_wcvendors_is_vendor( $condition_rule ) {
            global $wpdb;

            $relation = $condition_rule['value'] === 'true' ? 'IN' : 'NOT IN';
            if ( $condition_rule['operator'] !== 'equal' ) {
                $relation = $relation === 'IN' ? 'NOT IN' : 'IN';
            }

            $vendors = array( 0 );
            $vendors_list = $this->get_all_vendors();
            if ( $vendors_list ) {
                $vendors = array_keys( $vendors_list );
            }

            $vendors_string = implode( ',', $vendors );

            $string = "( ID {$relation} ( {$vendors_string} ) )";

            return $string;

        }

        /*
         * Condition: Is user is WC Vendors verified vendor
         */
        public function user_wcvendors_is_verified_vendor( $condition_rule ) {
            global $wpdb;

            $relation = $condition_rule['value'] === 'true' ? 'IN' : 'NOT IN';
            if ( $condition_rule['operator'] !== 'equal' ) {
                $relation = $relation === 'IN' ? 'NOT IN' : 'IN';
            }

            $vendors = array( 0 );
            $vendors_list = $this->get_all_vendors(
                array (
                    'meta_query' => array(
                        array(
                            'key' => '_wcv_verified_vendor',
                            'value' => 'yes',
                            'compare' => '=='
                        ),
                    )
                )
            );

            if ( $vendors_list ) {
                $vendors = array_keys( $vendors_list );
            }

            $vendors_string = implode( ',', $vendors );

            $string = "( ID {$relation} ( {$vendors_string} ) )";

            return $string;

        }

        /*
         * Condition: Is user is WC Vendors trusted vendor
         */
        public function user_wcvendors_is_trusted_vendor( $condition_rule ) {
            global $wpdb;

            $relation = $condition_rule['value'] === 'true' ? 'IN' : 'NOT IN';
            if ( $condition_rule['operator'] !== 'equal' ) {
                $relation = $relation === 'IN' ? 'NOT IN' : 'IN';
            }

            $vendors = array( 0 );
            $vendors_list = $this->get_all_vendors(
                array (
                    'meta_query' => array(
                        array(
                            'key' => '_wcv_trusted_vendor',
                            'value' => 'yes',
                            'compare' => '=='
                        ),
                    )
                )
            );

            if ( $vendors_list ) {
                $vendors = array_keys( $vendors_list );
            }

            $vendors_string = implode( ',', $vendors );

            $string = "( ID {$relation} ( {$vendors_string} ) )";

            return $string;

        }

        /*
         * Condition: Store rating
         */
        public function user_wcvendors_store_rating( $condition_rule ) {
            global $wpdb;

            switch ( $condition_rule['operator'] ) {
                case 'equal':
                    $operator = '=';
                    break;
                case 'not_equal':
                    $operator = '!=';
                    break;
                case 'greater':
                    $operator = '>=';
                    break;
                default:
                    $operator = '<=';
            }

            $vendors = array( 0 );
            $vendors_list = $this->get_all_vendors();
            if ( $vendors_list && class_exists('WCVendors_Pro_Ratings_Controller') ) {
                foreach ( $vendors_list as $vendor_id => $vendor_data ) {
                    $ratings_count = WCVendors_Pro_Ratings_Controller::get_ratings_count( $vendor_id, true );
                    $ratings_average = WCVendors_Pro_Ratings_Controller::get_ratings_average( $vendor_id, true );
                    $srore_rating_val = $ratings_count ? $ratings_average : '0';
                    if ( version_compare( $srore_rating_val, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendor_id;
                    }
                }
            }

            $vendors_string = implode( ',', $vendors );

            $string = "( ID IN ( {$vendors_string} ) )";

            return $string;

        }

        /*
         * Condition: Store reviews count
         */
        public function user_wcvendors_store_reviews( $condition_rule ) {
            global $wpdb;

            switch ( $condition_rule['operator'] ) {
                case 'equal':
                    $operator = '=';
                    break;
                case 'not_equal':
                    $operator = '!=';
                    break;
                case 'greater':
                    $operator = '>=';
                    break;
                default:
                    $operator = '<=';
            }

            $vendors = array( 0 );
            $vendors_list = $this->get_all_vendors();
            if ( $vendors_list && class_exists('WCVendors_Pro_Ratings_Controller') ) {
                foreach ( $vendors_list as $vendor_id => $vendor_data ) {
                    $ratings_count = WCVendors_Pro_Ratings_Controller::get_ratings_count( $vendor_id, true );
                    $srore_rating_val = $ratings_count ? $ratings_count : '0';
                    if ( version_compare( $srore_rating_val, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendor_id;
                    }
                }
            }

            $vendors_string = implode( ',', $vendors );

            $string = "( ID IN ( {$vendors_string} ) )";

            return $string;

        }

        /*
         * Condition: Store products count
         */
        public function user_wcvendors_store_products( $condition_rule ) {
            global $wpdb;

            switch ( $condition_rule['operator'] ) {
                case 'equal':
                    $operator = '=';
                    break;
                case 'not_equal':
                    $operator = '!=';
                    break;
                case 'greater':
                    $operator = '>=';
                    break;
                default:
                    $operator = '<=';
            }

            $vendors = array( 0 );
            $vendors_list = $this->get_all_vendors();
            if ( $vendors_list && class_exists('WCV_Vendors') ) {
                foreach ( $vendors_list as $vendor_id => $vendor_data ) {
                    $store_products = WCV_Vendors::get_vendor_products( $vendor_id );
                    $store_products_count = $store_products ? count( $store_products ) : '0';
                    if ( version_compare( $store_products_count, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendor_id;
                    }
                }
            }

            $vendors_string = implode( ',', $vendors );

            $string = "( ID IN ( {$vendors_string} ) )";

            return $string;

        }

        /*
         * Condition: Store products count
         */
        public function user_wcvendors_store_solds( $condition_rule ) {
            global $wpdb;

            switch ( $condition_rule['operator'] ) {
                case 'equal':
                    $operator = '=';
                    break;
                case 'not_equal':
                    $operator = '!=';
                    break;
                case 'greater':
                    $operator = '>=';
                    break;
                default:
                    $operator = '<=';
            }

            $vendors = array( 0 );
            $vendors_list = $this->get_all_vendors();
            if ( $vendors_list && class_exists('WCVendors_Pro_Vendor_Controller') ) {
                foreach ( $vendors_list as $vendor_id => $vendor_data ) {
                    $total_sales = apply_filters( 'wcv_store_total_sales_count', WCVendors_Pro_Vendor_Controller::get_vendor_sales_count( $vendor_id ), $vendor_id );
                    $store_sales = strval( $total_sales );
                    if ( version_compare( $store_sales, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendor_id;
                    }
                }
            }

            $vendors_string = implode( ',', $vendors );

            $string = "( ID IN ( {$vendors_string} ) )";

            return $string;

        }

        /*
         * Condition: Current user is vendor
         */
        public function current_user_wcvendors_is_vendor( $condition_rule ) {

            $value = $condition_rule['value'];

            if ( is_user_logged_in() ) {
                $current_user_id = get_current_user_id();
            } else {
                $current_user_id = 0;
            }

            $match = class_exists('WCV_Vendors') ? WCV_Vendors::is_vendor( $current_user_id ) : false;

            if ( $condition_rule['value'] !== 'true' ) {
                $match = ! $match;
            }

            if ( $condition_rule['operator'] === 'equal' && $match ) {
                return "( 1=1 )";
            } elseif ( $condition_rule['operator'] === 'not_equal' && ! $match ) {
                return "( 1=1 )";
            } else {
                return "( 1=2 )";
            }

        }

        /*
        * Condition: Current page is vendor store
        */
        public function current_page_wcvendors_store( $condition_rule ) {

            $value = $condition_rule['value'];

            $match = isset( $_REQUEST['aws_tax'] ) && $_REQUEST['aws_tax'] && strpos( $_REQUEST['aws_tax'], 'store:' ) !== false;

            if ( $condition_rule['value'] !== 'true' ) {
                $match = ! $match;
            }

            if ( $condition_rule['operator'] === 'equal' && $match ) {
                return "( 1=1 )";
            } elseif ( $condition_rule['operator'] === 'not_equal' && ! $match ) {
                return "( 1=1 )";
            } else {
                return "( 1=2 )";
            }

        }

        /*
         * Get all shop vendors
         */
        public function get_all_vendors( $users_args = array() ) {

            $users_array = array();
            $users_args['role__in'] = 'vendor';

            $users = get_users( $users_args );

            if ( !is_wp_error( $users ) && $users && !empty( $users ) ) {
                foreach( $users as $user ) {
                    $name = class_exists('WCV_Vendors') && WCV_Vendors::get_vendor_sold_by( $user->ID ) ? WCV_Vendors::get_vendor_sold_by( $user->ID ) : $user->display_name;
                    $users_array[$user->ID] = $name;
                }
            }

            return $users_array;

        }

        /*
         * Get current vendor store ID
         */
        private function get_store_id() {
            $vendor_shop = urldecode( get_query_var( 'vendor_shop' ) );
            $vendor_id   = class_exists('WCV_Vendors') ? WCV_Vendors::get_vendor_id( $vendor_shop ) : 0;
            return $vendor_id;
        }

        /*
         * Get vendors stor icon
         */
        private function get_store_icon( $vendor_id ) {

            $store_icon_src = wp_get_attachment_image_src(
                get_user_meta( $vendor_id, '_wcv_store_icon_id', true ),
                array( 50,150 )
            );

            if ( is_array( $store_icon_src ) ) {
                $store_icon =  $store_icon_src[0];
            } else {
                $store_icon = get_avatar_url( $vendor_id, array( 'size' => 150 ) );
            }

            return $store_icon;

        }

    }

endif;

AWS_WC_Vendors::instance();
