<?php
/**
 * MultiVendorX – WooCommerce multivendor marketplace plugin support
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'AWS_Multivendorx' ) ) :

    /**
     * Class
     */
    class AWS_Multivendorx {

        /**
         * Main AWS_Multivendorx Instance
         *
         * Ensures only one instance of AWS_Multivendorx is loaded or can be loaded.
         *
         * @static
         * @return AWS_Multivendorx - Main instance
         */
        protected static $_instance = null;

        public $form_id = 1;
        public $is_ajax = true;

        /**
         * Main AWS_Multivendorx Instance
         *
         * Ensures only one instance of AWS_Multivendorx is loaded or can be loaded.
         *
         * @static
         * @return AWS_Multivendorx - Main instance
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

            add_action( 'wp_after_insert_post', array( $this, 'wp_after_insert_post' ), 10, 4 );

            add_action( 'wp_head', array( $this, 'aws_shop_page_search' ), 1 );
            add_filter( 'aws_search_query_array', array( $this, 'aws_search_query_array' ), 1 );

            add_filter( 'aws_search_data_params', array( $this, 'wc_marketplace_filter' ), 10, 3 );
            add_filter( 'aws_search_pre_filter_products', array( $this, 'wc_marketplace_products_filter' ), 10, 2 );

            add_filter( 'aws_excerpt_search_result', array( $this, 'aws_excerpt_search_result' ), 1, 3 );

            add_filter( 'aws_search_users_results', array( $this, 'aws_search_users_results' ), 1, 3 );

            add_filter( 'aws_indexed_content', array( $this, 'aws_indexed_content' ), 1, 3 );

            add_filter( 'aws_admin_filter_rules', array( $this, 'aws_admin_filter_rules' ), 1 );

            add_filter( 'aws_filters_condition_rules', array( $this, 'condition_rules' ), 1 );

        }

        /*
         * Index product after it was approved
         */
        public function wp_after_insert_post( $post_id, $post, $update, $post_before ) {

            if ( $update && $post->post_type === 'product' && $post->post_status === 'publish' && $post_before && $post_before->post_status === 'pending' ) {
                do_action( 'aws_reindex_product', $post_id );
            }

        }

        /*
        * Limit search inside vendor shop
        */
        public function aws_shop_page_search() {

            $store_id = function_exists( 'mvx_find_shop_page_vendor' ) ? mvx_find_shop_page_vendor() : false;

            if ( ! $store_id ) {
                return;
            } ?>

            <script>
                document.addEventListener("awsLoaded", function() {
                    function aws_ajax_request_params( data, options ) {
                        let isShopForm = jQuery('.mvx-main-section .aws-search-form').hasClass('aws-form-active') || jQuery('.mvx-main-section .aws-search-form').hasClass('aws-processing');
                        if ( isShopForm ) {
                            data.aws_tax = 'store:<?php echo $store_id; ?>';
                        }
                        return data;
                    }
                    AwsHooks.add_filter( "aws_ajax_request_params", aws_ajax_request_params );
                });
            </script>

        <?php }

        /*
         * Limit search inside vendor shop
         */
        public function aws_search_query_array( $query ) {

            $vendor_id = false;

            if ( isset( $_REQUEST['aws_tax'] ) && $_REQUEST['aws_tax'] && strpos( $_REQUEST['aws_tax'], 'store:' ) !== false ) {
                $vendor_id = intval( str_replace( 'store:', '', $_REQUEST['aws_tax'] ) );
            } else {
                $vendor_id = function_exists( 'mvx_find_shop_page_vendor' ) ? mvx_find_shop_page_vendor() : false;
            }

            if ( $vendor_id ) {

                $store_products = get_posts( array(
                    'posts_per_page'      => -1,
                    'fields'              => 'ids',
                    'post_type'           => 'product',
                    'post_status'         => 'publish',
                    'ignore_sticky_posts' => true,
                    'suppress_filters'    => true,
                    'no_found_rows'       => 1,
                    'orderby'             => 'ID',
                    'order'               => 'DESC',
                    'lang'                => '',
                    'author'              => $vendor_id
                ) );

                if ( $store_products ) {
                    $query['search'] .= " AND ( id IN ( " . implode( ',', $store_products ) . " ) )";
                }

            }

            return $query;
        }

        /*
         * WC Marketplace plugin support
         */
        public function wc_marketplace_filter( $data, $post_id, $product ) {

            $wcmp_spmv_map_id = get_post_meta( $post_id, '_wcmp_spmv_map_id', true );

            if ( $wcmp_spmv_map_id ) {

                if ( isset( $data['wcmp_price'] ) && isset( $data['wcmp_price'][$wcmp_spmv_map_id] )  ) {

                    if ( $product->get_price() < $data['wcmp_price'][$wcmp_spmv_map_id] ) {
                        $data['wcmp_price'][$wcmp_spmv_map_id] = $product->get_price();
                        $data['wcmp_lowest_price_id'][$wcmp_spmv_map_id] = $post_id;
                    }

                } else {
                    $data['wcmp_price'][$wcmp_spmv_map_id] = $product->get_price();
                }

                $data['wcmp_spmv_product_id'][$wcmp_spmv_map_id][] = $post_id;

            }

            return $data;

        }

        /*
         * WC Marketplace plugin products filter
         */
        public function wc_marketplace_products_filter( $products_array, $data ) {

            $wcmp_spmv_exclude_ids = array();

            if ( isset( $data['wcmp_spmv_product_id'] ) ) {

                foreach( $data['wcmp_spmv_product_id'] as $wcmp_spmv_map_id => $wcmp_spmv_product_id ) {

                    if ( count( $wcmp_spmv_product_id ) > 1 ) {

                        if ( isset( $data['wcmp_lowest_price_id'] ) && isset( $data['wcmp_lowest_price_id'][$wcmp_spmv_map_id] ) ) {

                            foreach ( $wcmp_spmv_product_id as $wcmp_spmv_product_id_n ) {

                                if ( $wcmp_spmv_product_id_n === $data['wcmp_lowest_price_id'][$wcmp_spmv_map_id] ) {
                                    continue;
                                }

                                $wcmp_spmv_exclude_ids[] = $wcmp_spmv_product_id_n;

                            }

                        } else {

                            foreach ( $wcmp_spmv_product_id as $key => $wcmp_spmv_product_id_n ) {

                                if ( $key === 0 ) {
                                    continue;
                                }

                                $wcmp_spmv_exclude_ids[] = $wcmp_spmv_product_id_n;

                            }

                        }

                    }

                }

            }

            $new_product_array = array();

            foreach( $products_array as $key => $pr_arr ) {

                if ( ! in_array( $pr_arr['id'], $wcmp_spmv_exclude_ids ) ) {
                    $new_product_array[] = $pr_arr;
                }

            }

            return $new_product_array;

        }

        /*
         * Add store vendor badge inside search results
         */
        function aws_excerpt_search_result( $excerpt, $post_id, $product ) {
            global $product;

            $vendor = function_exists('get_mvx_product_vendors') ? get_mvx_product_vendors( $product->get_id() ) : false;
            $badge = '';

            if ( $vendor && apply_filters( 'aws_multivendorx_show_badge', true ) ) {

                $vendor_data = $this->get_vendor_data( $vendor );

                $badge .= '<div class="aws-MultiVendorX-badge aws-big-grid-center" style="display:flex;align-items:center;margin:10px 0 0 0;">';

                    $badge .= '<div style="margin-right: 10px" class="aws-MultiVendorX-vendor-image">';
                        $badge .= '<img style="border-radius: 50%;max-width: 30px;" src="' . esc_url( $vendor_data['store_image'] ) . '" alt="' . esc_attr( $vendor_data['store_name'] ) . '">';
                    $badge .= '</div>';

                    $badge .= '<div class="aws-MultiVendorX-vendor-name">';
                        $badge .= '<a href="' . esc_attr( $vendor_data['store_url'] ) . '"><span>' . esc_html( $vendor_data['store_name'] ) . '</span></a>';
                    $badge .= '</div>';

                $badge .= '</div>';

                $excerpt .= $badge;

            }

            return $excerpt;

        }

        /*
         * Update users results
         */
        public function aws_search_users_results( $result_array, $roles, $search_string ) {

            if ( array_search( 'dc_vendor', $roles ) !== false ) {
                foreach( $result_array as $user_id => $user_params ) {
                    $user_meta = get_userdata( $user_id );
                    $user_roles = $user_meta->roles;
                    if ( in_array( 'dc_vendor', $user_roles ) && function_exists( 'get_mvx_vendor' ) ) {
                        $vendor = get_mvx_vendor( $user_id );
                        if ( $vendor ) {
                            $vendor_data = $this->get_vendor_data($vendor);
                            $result_array[$user_id][0]['name'] = $vendor_data['store_name'];
                            $result_array[$user_id][0]['link'] = $vendor_data['store_url'];
                            $result_array[$user_id][0]['image'] = $vendor_data['store_image'];
                            $result_array[$user_id][0]['excerpt'] .= $vendor_data['store_rating'];
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
            $vendor = function_exists('get_mvx_product_vendors') ? get_mvx_product_vendors( $product->get_id() ) : false;
            if ( $vendor ) {
                $vendor_data = $this->get_vendor_data( $vendor );
                if ( isset( $vendor_data['store_name'] ) && $vendor_data['store_name'] ) {
                    $content .= ' ' . $vendor_data['store_name'];
                }
            }
            return $content;
        }

        /*
         * Add new filter rules
         */
        public function aws_admin_filter_rules( $options ) {

            $options['product'][] = array(
                "name" => __( "MultiVendorX: Is product sold by any vendor", "advanced-woo-search" ),
                "id"   => "product_multivendorx_is_sold_by_vendor",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['product'][] = array(
                "name" => __( "MultiVendorX: Product sold by", "advanced-woo-search" ),
                "id"   => "product_multivendorx_sold_by",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => array($this, 'get_all_vendors'),
                    'params'   => array()
                ),
            );

            $options['product'][] = array(
                "name" => __( "MultiVendorX: Store rating", "advanced-woo-search" ),
                "id"   => "product_multivendorx_store_rating",
                "type" => "number",
                "step" => "0.01",
                "operators" => "equals_compare",
            );

            $options['product'][] = array(
                "name" => __( "MultiVendorX: Store reviews count", "advanced-woo-search" ),
                "id"   => "product_multivendorx_store_reviews",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['product'][] = array(
                "name" => __( "MultiVendorX: Store products count", "advanced-woo-search" ),
                "id"   => "product_multivendorx_store_products",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['product'][] = array(
                "name" => __( "MultiVendorX: Store net sales", "advanced-woo-search" ),
                "id"   => "product_multivendorx_store_sales",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['product'][] = array(
                "name" => __( "MultiVendorX: Store items sold", "advanced-woo-search" ),
                "id"   => "product_multivendorx_store_solds",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['user'][] = array(
                "name" => __( "MultiVendorX: User is vendor", "advanced-woo-search" ),
                "id"   => "user_multivendorx_is_vendor",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['user'][] = array(
                "name" => __( "MultiVendorX: Store rating", "advanced-woo-search" ),
                "id"   => "user_multivendorx_store_rating",
                "type" => "number",
                "step" => "0.01",
                "operators" => "equals_compare",
            );

            $options['user'][] = array(
                "name" => __( "MultiVendorX: Store reviews count", "advanced-woo-search" ),
                "id"   => "user_multivendorx_store_reviews",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['user'][] = array(
                "name" => __( "MultiVendorX: Store products count", "advanced-woo-search" ),
                "id"   => "user_multivendorx_store_products",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['user'][] = array(
                "name" => __( "MultiVendorX: Store net sales", "advanced-woo-search" ),
                "id"   => "user_multivendorx_store_sales",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['user'][] = array(
                "name" => __( "MultiVendorX: Store items sold", "advanced-woo-search" ),
                "id"   => "user_multivendorx_store_solds",
                "type" => "number",
                "operators" => "equals_compare",
            );

            return $options;

        }

        /*
         * Add custom condition rule method
         */
        public function condition_rules( $rules ) {
            $rules['product_multivendorx_is_sold_by_vendor'] = array( $this, 'multivendorx_is_sold_by_vendor' );
            $rules['product_multivendorx_sold_by'] = array( $this, 'multivendorx_sold_by' );
            $rules['product_multivendorx_store_rating'] = array( $this, 'multivendorx_store_rating' );
            $rules['product_multivendorx_store_reviews'] = array( $this, 'multivendorx_store_reviews' );
            $rules['product_multivendorx_store_products'] = array( $this, 'multivendorx_store_products' );
            $rules['product_multivendorx_store_sales'] = array( $this, 'multivendorx_store_sales' );
            $rules['product_multivendorx_store_solds'] = array( $this, 'multivendorx_store_solds' );
            $rules['user_multivendorx_is_vendor'] = array( $this, 'user_multivendorx_is_vendor' );
            $rules['user_multivendorx_store_rating'] = array( $this, 'user_multivendorx_store_rating' );
            $rules['user_multivendorx_store_reviews'] = array( $this, 'user_multivendorx_store_reviews' );
            $rules['user_multivendorx_store_products'] = array( $this, 'user_multivendorx_store_products' );
            $rules['user_multivendorx_store_sales'] = array( $this, 'user_multivendorx_store_sales' );
            $rules['user_multivendorx_store_solds'] = array( $this, 'user_multivendorx_store_solds' );
            return $rules;
        }

        /*
         * Condition: Is product sold by any available vendor
         */
        public function multivendorx_is_sold_by_vendor( $condition_rule ) {
            global $wpdb;

            $relation = $condition_rule['operator'] === 'equal' ? 'IN' : 'NOT IN';
            $value_relation = $condition_rule['value'] === 'true' ? 'IN' : 'NOT IN';

            $vendors = array( 0 );
            $vendors_list = function_exists('get_mvx_vendors') ? get_mvx_vendors() : false;
            if ( $vendors_list ) {
                foreach ( $vendors_list as $vendors_list_i ) {
                    $vendors[] = $vendors_list_i->get_id();
                }
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
        public function multivendorx_sold_by( $condition_rule ) {
            global $wpdb;

            $value = $condition_rule['value'];

            $relation = $condition_rule['operator'] === 'equal' ? 'IN' : 'NOT IN';

            $string = "( id {$relation} (
                   SELECT $wpdb->posts.ID
                   FROM $wpdb->posts
                   WHERE $wpdb->posts.post_author = {$value}
                ))";

            return $string;
        }

        /*
         * Condition: Store rating for products
         */
        public function multivendorx_store_rating( $condition_rule ) {
            global $wpdb;

            $operator = $this->get_compare_operator( $condition_rule['operator'] );

            $vendors = array( 0 );

            $vendors_list = function_exists('get_mvx_vendors') ? get_mvx_vendors() : false;
            if ( $vendors_list ) {
                foreach ( $vendors_list as $vendors_list_i ) {
                    $rating_info = mvx_get_vendor_review_info($vendors_list_i->term_id);
                    $srore_rating_val = round($rating_info['avg_rating'], 2);
                    if ( version_compare( $srore_rating_val, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendors_list_i->get_id();
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
        public function multivendorx_store_reviews( $condition_rule ) {
            global $wpdb;

            $operator = $this->get_compare_operator( $condition_rule['operator'] );

            $vendors = array( 0 );
            $vendors_list = function_exists('get_mvx_vendors') ? get_mvx_vendors() : false;
            if ( $vendors_list ) {
                foreach ( $vendors_list as $vendors_list_i ) {
                    $rating_info = mvx_get_vendor_review_info($vendors_list_i->term_id);
                    $srore_rating_val = $rating_info['total_rating'] ? $rating_info['total_rating'] : '0';
                    if ( version_compare( $srore_rating_val, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendors_list_i->get_id();
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
         * Condition: Store products count for products
         */
        public function multivendorx_store_products( $condition_rule ) {
            global $wpdb;

            $operator = $this->get_compare_operator( $condition_rule['operator'] );

            $vendors = array( 0 );
            $vendors_list = function_exists('get_mvx_vendors') ? get_mvx_vendors() : false;
            if ( $vendors_list ) {
                foreach ( $vendors_list as $vendors_list_i ) {
                    $vendor_products = $vendors_list_i->get_products_ids();
                    $srore_products_val = $vendor_products ? count( $vendor_products ) : '0';
                    if ( version_compare( $srore_products_val, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendors_list_i->get_id();
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
         * Condition: Store net sales for products
         */
        public function multivendorx_store_sales( $condition_rule ) {
            global $wpdb;

            $operator = $this->get_compare_operator( $condition_rule['operator'] );

            $vendors = array( 0 );
            $vendors_list = function_exists('get_mvx_vendors') ? get_mvx_vendors() : false;
            if ( $vendors_list ) {
                foreach ( $vendors_list as $vendors_list_i ) {
                    $store_stats = $vendors_list_i->get_vendor_orders_reports_of();
                    $srore_sales_val = $store_stats && isset( $store_stats['sales_total'] ) ? $store_stats['sales_total'] : '0';
                    if ( version_compare( $srore_sales_val, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendors_list_i->get_id();
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
         * Condition: Store items sold for products
         */
        public function multivendorx_store_solds( $condition_rule ) {
            global $wpdb;

            $operator = $this->get_compare_operator( $condition_rule['operator'] );

            $vendors = array( 0 );
            $vendors_list = function_exists('get_mvx_vendors') ? get_mvx_vendors() : false;
            if ( $vendors_list ) {
                foreach ( $vendors_list as $vendors_list_i ) {
                    $store_stats = $vendors_list_i->get_vendor_orders_reports_of();
                    $srore_solds_val = $store_stats && isset( $store_stats['orders_no'] ) ? $store_stats['orders_no'] : '0';
                    if ( version_compare( $srore_solds_val, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendors_list_i->get_id();
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
         * Condition: Is user is vendor
         */
        public function user_multivendorx_is_vendor( $condition_rule ) {
            global $wpdb;

            $relation = $condition_rule['value'] === 'true' ? 'IN' : 'NOT IN';
            if ( $condition_rule['operator'] !== 'equal' ) {
                $relation = $relation === 'IN' ? 'NOT IN' : 'IN';
            }

            $vendors = array( 0 );
            $vendors_list = function_exists('get_mvx_vendors') ? get_mvx_vendors() : false;
            if ( $vendors_list ) {
                foreach ( $vendors_list as $vendors_list_i ) {
                    $vendors[] = $vendors_list_i->get_id();
                }
            }

            $vendors_string = implode( ',', $vendors );

            $string = "( ID {$relation} ( {$vendors_string} ) )";

            return $string;

        }

        /*
         * Condition: Store rating for user
         */
        public function user_multivendorx_store_rating( $condition_rule ) {
            global $wpdb;

            $operator = $this->get_compare_operator( $condition_rule['operator'] );

            $vendors = array( 0 );
            $vendors_list = function_exists('get_mvx_vendors') ? get_mvx_vendors() : false;
            if ( $vendors_list ) {
                foreach ( $vendors_list as $vendors_list_i ) {
                    $rating_info = mvx_get_vendor_review_info($vendors_list_i->term_id);
                    $srore_rating_val = round($rating_info['avg_rating'], 2);
                    if ( version_compare( $srore_rating_val, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendors_list_i->get_id();
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
        public function user_multivendorx_store_reviews( $condition_rule ) {
            global $wpdb;

            $operator = $this->get_compare_operator( $condition_rule['operator'] );

            $vendors = array( 0 );
            $vendors_list = function_exists('get_mvx_vendors') ? get_mvx_vendors() : false;
            if ( $vendors_list ) {
                foreach ( $vendors_list as $vendors_list_i ) {
                    $rating_info = mvx_get_vendor_review_info($vendors_list_i->term_id);
                    $srore_rating_val = $rating_info['total_rating'] ? $rating_info['total_rating'] : '0';
                    if ( version_compare( $srore_rating_val, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendors_list_i->get_id();
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
        public function user_multivendorx_store_products( $condition_rule ) {
            global $wpdb;

            $operator = $this->get_compare_operator( $condition_rule['operator'] );

            $vendors = array( 0 );
            $vendors_list = function_exists('get_mvx_vendors') ? get_mvx_vendors() : false;
            if ( $vendors_list ) {
                foreach ( $vendors_list as $vendors_list_i ) {
                    $vendor_products = $vendors_list_i->get_products_ids();
                    $srore_products_val = $vendor_products ? count( $vendor_products ) : '0';
                    if ( version_compare( $srore_products_val, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendors_list_i->get_id();
                    }
                }
            }

            $vendors_string = implode( ',', $vendors );

            $string = "( ID IN ( {$vendors_string} ) )";

            return $string;

        }

        /*
         * Condition: Store net sales
         */
        public function user_multivendorx_store_sales( $condition_rule ) {
            global $wpdb;

            $operator = $this->get_compare_operator( $condition_rule['operator'] );

            $vendors = array( 0 );
            $vendors_list = function_exists('get_mvx_vendors') ? get_mvx_vendors() : false;
            if ( $vendors_list ) {
                foreach ( $vendors_list as $vendors_list_i ) {
                    $store_stats = $vendors_list_i->get_vendor_orders_reports_of();
                    $srore_sales_val = $store_stats && isset( $store_stats['sales_total'] ) ? $store_stats['sales_total'] : '0';
                    if ( version_compare( $srore_sales_val, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendors_list_i->get_id();
                    }
                }
            }

            $vendors_string = implode( ',', $vendors );

            $string = "( ID IN ( {$vendors_string} ) )";

            return $string;

        }

        /*
         * Condition: Store items sold
         */
        public function user_multivendorx_store_solds( $condition_rule ) {
            global $wpdb;

            $operator = $this->get_compare_operator( $condition_rule['operator'] );

            $vendors = array( 0 );
            $vendors_list = function_exists('get_mvx_vendors') ? get_mvx_vendors() : false;
            if ( $vendors_list ) {
                foreach ( $vendors_list as $vendors_list_i ) {
                    $store_stats = $vendors_list_i->get_vendor_orders_reports_of();
                    $srore_solds_val = $store_stats && isset( $store_stats['orders_no'] ) ? $store_stats['orders_no'] : '0';
                    if ( version_compare( $srore_solds_val, $condition_rule['value'], $operator ) ) {
                        $vendors[] = $vendors_list_i->get_id();
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
        public function get_all_vendors( $args = array() ) {

            $options = array();

            $vendors_list = function_exists('get_mvx_vendors') ? get_mvx_vendors() : false;

            if ( $vendors_list ) {
                foreach ( $vendors_list as $vendor ) {
                    $options[$vendor->get_id()] = $vendor->page_title ? $vendor->page_title : 'ID: ' . $vendor->get_id();
                }
            }

            return $options;

        }

        /*
         * Get compare operator value
         */
        private function get_compare_operator( $operator_text ) {

            switch ( $operator_text ) {
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

            return $operator;

        }

        /*
         * Get vendor shop data
         */
        private function get_vendor_data( $vendor ) {

            $data = array();

            $data['store_name'] = $vendor->page_title;
            $data['store_url'] = $vendor->get_permalink();
            $data['store_image'] = $vendor->get_image() ? $vendor->get_image('image', array(125, 125)) : $MVX->plugin_url . 'assets/images/WP-stdavatar.png';

            $rating_info = mvx_get_vendor_review_info($vendor->term_id);
            $rating = round($rating_info['avg_rating'], 2);
            $count = intval($rating_info['total_rating']);
            $data['store_rating'] = '';

            if ( $count > 0) {

                $data['store_rating'] = '
                    <span class="aws_rating">
                        <span class="aws_votes">
                            <span class="aws_current_votes" style="width:' . (( $rating / 5 ) * 100) . '%"></span>
                        </span>
                        <span class="aws_review">' . sprintf( _n( '%s Review', '%s Reviews', $count, 'advanced-woo-search' ), number_format_i18n( $count ) ) . '</span>
                    </span>';

            }

            return $data;

        }

    }

endif;

AWS_Multivendorx::instance();