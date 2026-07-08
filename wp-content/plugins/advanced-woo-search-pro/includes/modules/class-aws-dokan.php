<?php
/**
 * Dokan – WooCommerce Multivendor Marketplace Solution plugin support
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'AWS_Dokan' ) ) :

    /**
     * Class
     */
    class AWS_Dokan {
        
        /**
         * Main AWS_Dokan Instance
         *
         * Ensures only one instance of AWS_Dokan is loaded or can be loaded.
         *
         * @static
         * @return AWS_Dokan - Main instance
         */
        protected static $_instance = null;

        public $form_id = 1;
        public $is_ajax = true;

        /**
         * Main AWS_Dokan Instance
         *
         * Ensures only one instance of AWS_Dokan is loaded or can be loaded.
         *
         * @static
         * @return AWS_Dokan - Main instance
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

            add_action( 'aws_search_start', array( $this, 'search_start' ), 10, 2  );

            if ( get_option( 'aws_pro_seamless' ) && get_option( 'aws_pro_seamless' ) === 'true' && apply_filters( 'aws_dokan_shop_seamless', true ) ) {
                add_filter( 'aws_js_seamless_selectors', array( $this, 'js_seamless_selectors' ) );
                add_filter( 'aws_js_seamless_searchbox_markup', array( $this, 'aws_js_seamless_searchbox_markup' ), 1 );
            }

            add_action( 'wp_head', array( $this, 'aws_js_seamless_searchboxstyles' ), 1 );
            add_filter( 'aws_search_query_array', array( $this, 'aws_search_query_array' ), 1 );

            add_filter( 'aws_products_order_by', array( $this, 'aws_products_order_by' ), 1 );

            add_filter( 'aws_admin_page_options', array( $this, 'aws_admin_page_options' ) );

            add_filter( 'aws_excerpt_search_result', array( $this, 'aws_excerpt_search_result' ), 1, 3 );

            add_filter( 'aws_search_users_results', array( $this, 'aws_search_users_results' ), 1, 3 );

            add_filter( 'aws_indexed_content', array( $this, 'aws_indexed_content' ), 1, 3 );

            add_filter( 'aws_users_search_query', array( $this, 'aws_users_search_query' ), 1, 3 );

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
                                "name"  => __( "Show Dokan vendor info?", "advanced-woo-search" ),
                                "desc"  => __( "Show or not Dokan vendor information all products inside search results.", "advanced-woo-search" ),
                                "id"    => "show_dokan_badge",
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
        function aws_excerpt_search_result( $excerpt, $post_id, $product ) {
            global $product;

            $show_vendor_info = AWS_PRO()->get_settings( 'show_dokan_badge', $this->form_id );

            if ( $show_vendor_info === 'off' ) {
                return $excerpt;
            }

            $vendor = function_exists('dokan_get_vendor_by_product') ? dokan_get_vendor_by_product( $product ) : false;
            $badge = '';

            if ( $vendor ) {

                $store_info   = $vendor->get_shop_info();
                $store_rating = $vendor->get_rating();

                if ( isset( $store_info['store_name'] ) && $store_info['store_name'] ) {

                    if ( $show_vendor_info === 'full' ) {

                        ob_start();

                        echo '<div class="aws-dokan-badge" style="max-width: 300px;">';

                        dokan_get_template_part(
                            'vendor-store-info',
                            '',
                            [
                                'vendor'       => $vendor,
                                'store_info'   => $store_info,
                                'store_rating' => $store_rating,
                            ]
                        );

                        echo '</div>';

                        $badge = ob_get_contents();
                        ob_end_clean();

                    } else {

                        $badge .= '<div class="aws-dokan-badge aws-big-grid-center" style="display:flex;align-items:center;margin:10px 0 0 0;">';

                            $badge .= '<div style="margin-right: 10px" class="aws-dokan-vendor-image">';
                                $badge .= '<img style="border-radius: 50%;max-width: 30px;" src="' . esc_url( $vendor->get_avatar() ) . '" alt="' . esc_attr( $store_info['store_name'] ) . '">';
                            $badge .= '</div>';

                            $badge .= '<div class="aws-dokan-vendor-name">';
                                $badge .= '<a href="' . esc_attr( $vendor->get_shop_url() ) . '"><h5>' . esc_html( $store_info['store_name'] ) . '</h5></a>';
                                apply_filters( 'dokan_product_single_after_store_name', $vendor );
                            $badge .= '</div>';

                        $badge .= '</div>';

                    }

                    $excerpt .= $badge;

                }

            }

            return $excerpt;

        }

        /*
         * Selector filter of js seamless
         */
        public function js_seamless_selectors( $selectors ) {
            $selectors[] = '#dokan-content .dokan-store-products-ordeby';
            return $selectors;
        }

        /*
         * Update search form markup for js seamless integration
         */
        public function aws_js_seamless_searchbox_markup( $markup ) {

            $orderby_options = dokan_store_product_catalog_orderby();
            $store_id = $this->get_dokan_store_id();

            if ( $store_id ) {

                if ( is_array( $orderby_options['catalogs'] ) && isset( $orderby_options['orderby'] ) ) {

                    $markup .= '<form style="display: none;" class="dokan-store-products-ordeby" method="get">';

                        $markup .= '<select name="product_orderby" class="orderby orderby-search" aria-label="' . esc_attr( 'Shop order', 'dokan-lite' ) .'" onchange=\'if(this.value != 0) { this.form.submit(); }\'>';
                            foreach ( $orderby_options['catalogs'] as $id => $name ) {
                                $markup .= '<option value="' . esc_attr( $id ) . '" ' . selected( $orderby_options['orderby'], $id, false ) . '>' . esc_html( $name ) . '</option>';
                            }
                        $markup .= '</select><input type="hidden" name="paged" value="1" />';

                        if ( isset( $_GET['s'] ) && isset( $_GET['type_aws'] ) ) {
                            foreach ( $_GET as $get_name => $get_val ) {
                                if ( $get_name === 'product_orderby' ) {
                                    continue;
                                }
                                $markup .= '<input type="hidden" name="' . esc_attr( $get_name ) . '" value="' . esc_attr( $get_val ) . '" />';
                            }
                        }

                    $markup .= '</form>';

                }

            }

            return $markup;

        }

        /*
         * Update search form styles for js seamless integration
         */
        public function aws_js_seamless_searchboxstyles() {

            $store_id = $this->get_dokan_store_id();

            if ( ! $store_id ) {
                return;
            } ?>

            <style>
                #dokan-content .aws-container {
                    float: left;
                    width: 350px;
                }
                .dokan-store-products-ordeby {
                    display: none;
                }
                #dokan-content .aws-container + .dokan-store-products-ordeby {
                    display: block !important;
                }
            </style>

            <script>
                document.addEventListener("awsLoaded", function() {
                    function aws_ajax_request_params( data, options ) {
                        let isShopForm = jQuery('#dokan-content .aws-search-form').hasClass('aws-form-active') || jQuery('#dokan-content .aws-search-form').hasClass('aws-processing') || jQuery('#dokan-secondary .aws-search-form').hasClass('aws-form-active') || jQuery('#dokan-secondary .aws-search-form').hasClass('aws-processing');
                        if ( isShopForm ) {
                            data.aws_tax = 'store:<?php echo $store_id; ?>';
                        }
                        return data;
                    }
                    jQuery('#dokan-content .aws-search-form').attr( 'action', '' );
                    AwsHooks.add_filter( "aws_ajax_request_params", aws_ajax_request_params );
                });
            </script>

        <?php }

        /*
         * Limit search inside vendor shop
         */
        public function aws_search_query_array( $query ) {

            global $wpdb;

            $vendor_id = false;

            if ( isset( $_REQUEST['aws_tax'] ) && $_REQUEST['aws_tax'] && strpos( $_REQUEST['aws_tax'], 'store:' ) !== false ) {
                $vendor_id = intval( str_replace( 'store:', '', $_REQUEST['aws_tax'] ) );
            } else {
                $vendor_id = $this->get_dokan_store_id();
            }

            if ( $vendor_id ) {

                $query['search'] .= " AND ( id IN ( SELECT {$wpdb->posts}.ID FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_author = {$vendor_id} ) )";

            }

            return $query;

        }

        /*
         * Fix order by option inside vedor store
         */
        public function aws_products_order_by( $order_by ) {

            $store_id = $this->get_dokan_store_id();

            if ( $store_id && isset( $_GET['product_orderby'] ) && isset( $_GET['type_aws'] ) ) {
                $order_by = $_GET['product_orderby'];
            }

            return $order_by;

        }

        /*
         * Update users results
         */
        public function aws_search_users_results( $result_array, $roles, $search_string ) {

            if ( array_search( 'seller', $roles ) !== false ) {
                foreach( $result_array as $user_id => $user_params ) {
                    $user_meta = get_userdata( $user_id );
                    $user_roles = $user_meta->roles;
                    if ( in_array( 'seller', $user_roles ) && function_exists('dokan') ) {
                        $vendor = dokan()->vendor->get( $user_id );
                        if ( $vendor ) {

                            $store_info = $vendor->get_shop_info();
                            $store_rating = $vendor->get_rating();
                            $store_url = $vendor->get_shop_url();
                            $store_avatar = $vendor->get_avatar();

                            $result_array[$user_id][0]['name'] = $store_info['store_name'];
                            $result_array[$user_id][0]['link'] = $store_url;
                            $result_array[$user_id][0]['image'] = $store_avatar;

                            $rating = '';

                            $rating .= '<span class="dokan-vendor-info-wrap" style="border:none;margin:0;padding:0;">';
                                $rating .= '<span class="dokan-vendor-info">';

                                    $rating .= '<span class="dokan-vendor-rating">';
                                        if ( $store_rating['count'] ) {
                                            $rating .= '<span>' . esc_html( $store_rating['rating'] ) . '</span>';
                                        }
                                        $rating .= wp_kses_post( dokan_generate_ratings( $store_rating['rating'], 5 ) );
                                    $rating .= '</span>';

                                    if ( $store_rating['count'] ) {
                                        $rating .= '<span class="dokan-ratings-count">' . sprintf( _n( "%d Review", "%d Reviews", $store_rating["count"], "dokan-lite" ), $store_rating["count"] ) . '</span>';
                                    }
        
                                    $result_array[$user_id][0]['excerpt'] .= $rating;
                                $rating .= '</span>';
                            $rating .= '</span>';

                        }
                    }
                }
            }

            return $result_array;

        }

        /*
         * Add product vendor data inside index table
         */
        public function aws_indexed_content( $content, $id, $product ) {
            if ( apply_filters( 'aws_dokan_index_store_name', true ) ) {
                $vendor = function_exists('dokan_get_vendor_by_product') ? dokan_get_vendor_by_product( $product ) : false;
                if ( $vendor ) {
                    $store_info = $vendor->get_shop_info();
                    if ( isset( $store_info['store_name'] ) && $store_info['store_name'] ) {
                        $content .= ' ' . $store_info['store_name'];
                    }
                }
            }
            return $content;
        }

        /*
         * Search for vendors via vendor store name
         */
        public function aws_users_search_query( $sql, $roles, $search_query ) {
            $sql = str_replace( "'last_name'", "'last_name', 'dokan_store_name'", $sql );
            return $sql;
        }

        /*
        * Add new filter rules
        */
        public function aws_admin_filter_rules( $options ) {

            $options['product'][] = array(
                "name" => __( "Dokan: Is product sold by any vendor", "advanced-woo-search" ),
                "id"   => "product_dokan_is_sold_by_vendor",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['product'][] = array(
                "name" => __( "Dokan: Product sold by", "advanced-woo-search" ),
                "id"   => "product_dokan_sold_by",
                "type" => "callback",
                "operators" => "lists",
                "multiple" => true,
                "choices" => array(
                    'callback' => array($this, 'get_all_vendors'),
                    'params'   => array()
                ),
            );

            $options['product'][] = array(
                "name" => __( "Dokan: Store is featured", "advanced-woo-search" ),
                "id"   => "product_dokan_is_featured",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['product'][] = array(
                "name" => __( "Dokan: Store rating", "advanced-woo-search" ),
                "id"   => "product_dokan_store_rating",
                "type" => "number",
                "step" => "0.01",
                "operators" => "equals_compare",
            );

            $options['product'][] = array(
                "name" => __( "Dokan: Store reviews count", "advanced-woo-search" ),
                "id"   => "product_dokan_store_reviews",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['product'][] = array(
                "name" => __( "Dokan: Product views", "advanced-woo-search" ),
                "id"   => "product_dokan_views",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['user'][] = array(
                "name" => __( "Dokan: User is vendor", "advanced-woo-search" ),
                "id"   => "user_dokan_is_vendor",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['user'][] = array(
                "name" => __( "Dokan: Store is featured", "advanced-woo-search" ),
                "id"   => "user_dokan_is_featured",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['user'][] = array(
                "name" => __( "Dokan: Store rating", "advanced-woo-search" ),
                "id"   => "user_dokan_store_rating",
                "type" => "number",
                "step" => "0.01",
                "operators" => "equals_compare",
            );

            $options['user'][] = array(
                "name" => __( "Dokan: Store reviews count", "advanced-woo-search" ),
                "id"   => "user_dokan_store_reviews",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['user'][] = array(
                "name" => __( "Dokan: Store products number", "advanced-woo-search" ),
                "id"   => "user_dokan_store_products",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['user'][] = array(
                "name" => __( "Dokan: Store visitors", "advanced-woo-search" ),
                "id"   => "user_dokan_store_visitors",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['user'][] = array(
                "name" => __( "Dokan: Store items sold", "advanced-woo-search" ),
                "id"   => "user_dokan_store_solds",
                "type" => "number",
                "operators" => "equals_compare",
            );

            return $options;

        }

        /*
         * Add custom condition rule method
         */
        public function condition_rules( $rules ) {
            $rules['product_dokan_is_sold_by_vendor'] = array( $this, 'dokan_is_sold_by_vendor' );
            $rules['product_dokan_sold_by'] = array( $this, 'dokan_sold_by' );
            $rules['product_dokan_is_featured'] = array( $this, 'dokan_is_featured' );
            $rules['product_dokan_store_rating'] = array( $this, 'dokan_store_rating' );
            $rules['product_dokan_store_reviews'] = array( $this, 'dokan_store_reviews' );
            $rules['product_dokan_views'] = array( $this, 'dokan_views' );
            $rules['user_dokan_is_vendor'] = array( $this, 'user_dokan_is_vendor' );
            $rules['user_dokan_is_featured'] = array( $this, 'user_dokan_is_featured' );
            $rules['user_dokan_store_rating'] = array( $this, 'user_dokan_store_rating' );
            $rules['user_dokan_store_reviews'] = array( $this, 'user_dokan_store_reviews' );
            $rules['user_dokan_store_products'] = array( $this, 'user_dokan_store_products' );
            $rules['user_dokan_store_visitors'] = array( $this, 'user_dokan_store_visitors' );
            $rules['user_dokan_store_solds'] = array( $this, 'user_dokan_store_solds' );
            return $rules;
        }

        /*
         * Condition: Is product sold by any available vendor
         */
        public function dokan_is_sold_by_vendor( $condition_rule ) {
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
        public function dokan_sold_by( $condition_rule ) {
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
         * Condition: Is product sold by featured vendor
         */
        public function dokan_is_featured( $condition_rule ) {
            global $wpdb;

            $relation = $condition_rule['operator'] === 'equal' ? 'IN' : 'NOT IN';
            $value_relation = $condition_rule['value'] === 'true' ? 'IN' : 'NOT IN';

            $vendors = array( 0 );
            $vendors_list = $this->get_all_vendors( array( 'featured' => 'yes', 'number' => 999, 'fields' => 'ids' ) );
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
        public function dokan_store_rating( $condition_rule ) {
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
            if ( $vendors_list && function_exists('dokan') ) {
                foreach ( $vendors_list as $vendor_id => $vendor_data ) {
                    $vendor = dokan()->vendor->get( $vendor_id );
                    $store_rating = $vendor->get_rating();
                    $srore_rating_val = ! empty( $store_rating['count'] ) ? $store_rating['rating'] : '0';
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
        public function dokan_store_reviews( $condition_rule ) {
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
            if ( $vendors_list && function_exists('dokan') ) {
                foreach ( $vendors_list as $vendor_id => $vendor_data ) {
                    $vendor = dokan()->vendor->get( $vendor_id );
                    $store_rating = $vendor->get_rating();
                    $srore_rating_val = ! empty( $store_rating['count'] ) ? $store_rating['count'] : '0';
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
         * Condition: Product views count
         */
        public function dokan_views( $condition_rule ) {
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

            $views = intval( $condition_rule['value'] );

            $string = "( id IN (
                   SELECT $wpdb->postmeta.post_id
                   FROM $wpdb->postmeta
                   WHERE $wpdb->postmeta.meta_key = 'pageview' AND $wpdb->postmeta.meta_value {$operator} {$views} 
                ))";

            return $string;

        }

        /*
         * Condition: Is user is Dokan vendor
         */
        public function user_dokan_is_vendor( $condition_rule ) {
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
         * Condition: Is user is Dokan featured vendor
         */
        public function user_dokan_is_featured( $condition_rule ) {
            global $wpdb;

            $relation = $condition_rule['value'] === 'true' ? 'IN' : 'NOT IN';
            if ( $condition_rule['operator'] !== 'equal' ) {
                $relation = $relation === 'IN' ? 'NOT IN' : 'IN';
            }

            $vendors = array( 0 );
            $vendors_list = $this->get_all_vendors( array( 'featured' => 'yes', 'number' => 999, 'fields' => 'ids' ) );
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
        public function user_dokan_store_rating( $condition_rule ) {
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
            if ( $vendors_list && function_exists('dokan') ) {
                foreach ( $vendors_list as $vendor_id => $vendor_data ) {
                    $vendor = dokan()->vendor->get( $vendor_id );
                    $store_rating = $vendor->get_rating();
                    $srore_rating_val = ! empty( $store_rating['count'] ) ? $store_rating['rating'] : '0';
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
        public function user_dokan_store_reviews( $condition_rule ) {
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
            if ( $vendors_list && function_exists('dokan') ) {
                foreach ( $vendors_list as $vendor_id => $vendor_data ) {
                    $vendor = dokan()->vendor->get( $vendor_id );
                    $store_rating = $vendor->get_rating();
                    $srore_rating_val = ! empty( $store_rating['count'] ) ? $store_rating['count'] : '0';
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
        public function user_dokan_store_products( $condition_rule ) {
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
            if ( $vendors_list && function_exists('dokan') ) {
                foreach ( $vendors_list as $vendor_id => $vendor_data ) {
                    $vendor = dokan()->vendor->get( $vendor_id );
                    $store_products = $vendor->get_published_products();
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
        public function user_dokan_store_visitors( $condition_rule ) {
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
            if ( $vendors_list && function_exists('dokan') ) {
                foreach ( $vendors_list as $vendor_id => $vendor_data ) {
                    $vendor = dokan()->vendor->get( $vendor_id );
                    $store_views = strval( $vendor->get_product_views() );
                    if ( version_compare( $store_views, $condition_rule['value'], $operator ) ) {
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
        public function user_dokan_store_solds( $condition_rule ) {
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
            if ( $vendors_list && function_exists('dokan') ) {
                foreach ( $vendors_list as $vendor_id => $vendor_data ) {
                    $vendor = dokan()->vendor->get( $vendor_id );
                    $store_sales = strval( $vendor->get_total_sales() );
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
         * Condition callback: get all available vendors
         */
        public function get_all_vendors( $args = array( 'number' => 999, 'fields' => 'ids' ) ) {

            $options = array();

            if ( function_exists('dokan') ) {
                $vendors = dokan()->vendor;
                if ( $vendors ) {
                    foreach ( $vendors->get_vendors( $args ) as $vendor ) {
                        $vendor_id = is_object( $vendor ) ? $vendor->id : $vendor;
                        $store_name = get_user_meta( $vendor_id, 'dokan_store_name', true );
                        $options[$vendor_id] = $store_name ? $store_name : 'ID: ' . $vendor_id ;
                    }
                }
            }

            return $options;

        }

        /*
         * Get current vendor store ID
         */
        private function get_dokan_store_id() {

//            $store_products = function_exists('dokan_get_option') ? dokan_get_option( 'store_products', 'dokan_appearance' ) : false;
//
//            if ( ! empty( $store_products['hide_product_filter'] ) ) {
//                return false;
//            }

            $store_user = function_exists('dokan') ? dokan()->vendor->get( get_query_var( 'author' ) ) : false;
            $store_id  = $store_user ? $store_user->get_id() : false;

            return $store_id;

        }

    }

endif;

AWS_Dokan::instance();
