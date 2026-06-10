<?php
/**
 *  WooCommerce Memberships plugin integration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'AWS_Woo_Memberships' ) ) :

    /**
     * Class
     */
    class AWS_Woo_Memberships {

        /**
         * Main AWS_Woo_Memberships Instance
         *
         * Ensures only one instance of AWS_Woo_Memberships is loaded or can be loaded.
         *
         * @static
         * @return AWS_Woo_Memberships - Main instance
         */
        protected static $_instance = null;

        private $data = array();

        /**
         * Main AWS_Woo_Memberships Instance
         *
         * Ensures only one instance of AWS_Woo_Memberships is loaded or can be loaded.
         *
         * @static
         * @return AWS_Woo_Memberships - Main instance
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

            // Filters
            add_filter( 'aws_admin_filter_rules', array( $this, 'aws_admin_filter_rules' ), 1 );
            add_filter( 'aws_filters_condition_rules', array( $this, 'condition_rules' ), 1 );

            // Restrict products
            add_filter( 'aws_search_query_array', array( $this, 'search_query_array' ), 1 );

            // Restrict product categories
            add_filter( 'aws_terms_search_query', array( $this, 'terms_search_query' ), 1, 2 );

            // Restrict products content
            add_filter( 'aws_search_pre_filter_products', array( $this, 'aws_search_pre_filter_products' ), 1 );

        }

        /*
         * Add new filter rules
         */
        public function aws_admin_filter_rules( $options ) {

            $options['product'][] = array(
                "name" => __( "Woo Memberships: Is product restricted for user", "advanced-woo-search" ),
                "id"   => "woo_memberships_is_product_restricted",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['term'][] = array(
                "name" => __( "Woo Memberships: Is term restricted for user", "advanced-woo-search" ),
                "id"   => "woo_memberships_is_term_restricted",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['user'][] = array(
                "name" => __( "Woo Memberships: User is member", "advanced-woo-search" ),
                "id"   => "woo_memberships_is_member",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => array($this, 'get_all_plans'),
                    'params'   => array()
                ),
            );

            $options['current_user'][] = array(
                "name" => __( "Woo Memberships: Current user is member", "advanced-woo-search" ),
                "id"   => "woo_memberships_is_current_member",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => array($this, 'get_all_plans'),
                    'params'   => array()
                ),
            );

            return $options;

        }

        /*
         * Get all membership plans
         */
        public function get_all_plans() {

            $options = array();

            $options['aws_any'] = __( "Any membership plan", "advanced-woo-search" );

            $plans = wc_memberships_get_membership_plans();

            if ( ! empty( $plans ) ) {
                foreach ( $plans as $plan ) {
                    $options[$plan->id] = $plan->name;
                }
            }

            return $options;

        }

        /*
         * Add custom condition rule method
         */
        public function condition_rules( $rules ) {
            $rules['woo_memberships_is_product_restricted'] = array( $this, 'woo_memberships_is_product_restricted' );
            $rules['woo_memberships_is_term_restricted'] = array( $this, 'woo_memberships_is_term_restricted' );
            $rules['woo_memberships_is_member'] = array( $this, 'woo_memberships_is_member' );
            $rules['woo_memberships_is_current_member'] = array( $this, 'woo_memberships_is_current_member' );
            return $rules;
        }

        /*
         * Condition: Is product restricted for current user
         */
        public function woo_memberships_is_product_restricted( $condition_rule ) {

            global $wpdb;

            $relation = $condition_rule['value'] === 'true' ? 'IN' : 'NOT IN';
            if ( $condition_rule['operator'] !== 'equal' ) {
                $relation = $relation === 'IN' ? 'NOT IN' : 'IN';
            }

            $restricted_posts = array( 0 );
            $restricted_posts_list = wc_memberships()->get_restrictions_instance()->get_user_restricted_posts();
            if ( $restricted_posts_list ) {
                $restricted_posts = $restricted_posts_list;
            }

            $restricted_posts_string = implode( ',', $restricted_posts );

            $string = "( id {$relation} ({$restricted_posts_string}) )";

            return $string;

        }

        /*
         * Condition: Is term restricted for current user
         */
        public function woo_memberships_is_term_restricted( $condition_rule ) {

            global $wpdb;

            $relation = $condition_rule['value'] === 'true' ? 'IN' : 'NOT IN';
            if ( $condition_rule['operator'] !== 'equal' ) {
                $relation = $relation === 'IN' ? 'NOT IN' : 'IN';
            }

            $restricted_terms = array( 0 );
            $conditions = wc_memberships()->get_restrictions_instance()->get_user_content_access_conditions();
            $conditions = isset( $conditions['restricted']['terms'] ) && is_array( $conditions['restricted']['terms'] ) ? $conditions['restricted']['terms'] : array();
            if ( ! empty( $conditions ) && isset( $conditions['product_cat'] ) && ! empty( $conditions['product_cat'] ) ) {
                $restricted_terms = $conditions['product_cat'];
            }

            $restricted_terms_string = implode( ',', $restricted_terms );

            $string = "( {$wpdb->terms}.term_id {$relation} ( {$restricted_terms_string} ) )";
            
            return $string;

        }

        /*
         * Condition: Is user is WooCommerce Memberships member
         */
        public function woo_memberships_is_member( $condition_rule ) {

            global $wpdb;

            $relation = $condition_rule['operator'] === 'equal' ? 'IN' : 'NOT IN';

            if ( $condition_rule['value'] === 'aws_any' ) {
                $members = $this->get_users_by_membership();
            } else {
                $members = $this->get_users_by_membership( $condition_rule['value'] );
            }

            $members_arr = array( 0 );
            if ( $members ) {
                $members_arr = $members;
            }
            $members_string = implode( ',', $members_arr );

            $string = "( ID {$relation} ( {$members_string} ) )";

            return $string;

        }

        /*
         * Condition: Is current user is WooCommerce Memberships member
         */
        public function woo_memberships_is_current_member( $condition_rule ) {

            if ( $condition_rule['value'] === 'aws_any' ) {
                $is_member = wc_memberships_is_user_active_member();
            } else {
                $is_member = wc_memberships_is_user_active_member( null, $condition_rule['value'] );
            }

            if ( $condition_rule['operator'] === 'equal' && $is_member ) {
                return "( 1=1 )";
            } elseif ( $condition_rule['operator'] === 'not_equal' && ! $is_member ) {
                return "( 1=1 )";
            } else {
                return "( 1=2 )";
            }

        }

        /*
         * Hide restricted products
         */
        public function search_query_array( $query ) {

            global $wp_query;

            if ( ! current_user_can( 'wc_memberships_access_all_restricted_content' ) && function_exists( 'wc_memberships' ) ) {
                $feed_is_restricted = $wp_query instanceof \WP_Query && $wp_query->is_feed() && ! wc_memberships()->get_restrictions_instance()->is_restriction_mode( 'hide_content' );
                if ( $feed_is_restricted || wc_memberships()->get_restrictions_instance()->is_restriction_mode('hide') ) {
                    $restricted_posts = wc_memberships()->get_restrictions_instance()->get_user_restricted_posts();
                    if ( ! empty( $restricted_posts ) ) {
                        $query['search'] .= sprintf( ' AND ( id NOT IN ( %s ) )', implode( ',', $restricted_posts ) );
                    }
                }
            }

            return $query;

        }

        /*
         * Hide restricted categories
         */
        public function terms_search_query( $sql, $taxonomy ) {

            global $wpdb, $wp_query;

            if ( ! current_user_can( 'wc_memberships_access_all_restricted_content' ) && function_exists( 'wc_memberships' ) ) {
                $feed_is_restricted = $wp_query instanceof \WP_Query && $wp_query->is_feed() && !wc_memberships()->get_restrictions_instance()->is_restriction_mode('hide_content');
                if ( $feed_is_restricted || wc_memberships()->get_restrictions_instance()->is_restriction_mode('hide') ) {

                    $conditions = wc_memberships()->get_restrictions_instance()->get_user_content_access_conditions();
                    $conditions = isset( $conditions['restricted']['terms'] ) && is_array( $conditions['restricted']['terms'] ) ? $conditions['restricted']['terms'] : array();

                    if ( ! empty( $conditions ) && isset( $conditions['product_cat'] ) && ! empty( $conditions['product_cat'] ) ) {
                        $sql_terms = "AND $wpdb->term_taxonomy.term_id NOT IN ( " . implode( ',', $conditions['product_cat'] ) . " )";
                        $sql = str_replace( 'WHERE 1 = 1', 'WHERE 1 = 1 ' . $sql_terms, $sql );
                    }

                }
            }

            return $sql;

        }

        /*
         * Filter restricted products content
         */
        public function aws_search_pre_filter_products( $products_array ) {
            if ( ! current_user_can( 'wc_memberships_access_all_restricted_content' ) && function_exists( 'wc_memberships' ) ) {
                if ( wc_memberships()->get_restrictions_instance()->is_restriction_mode( 'hide_content' ) ) {
                    $restricted_posts = wc_memberships()->get_restrictions_instance()->get_user_restricted_posts();
                    if ( ! empty( $restricted_posts ) ) {

                        $show_excerpts = 'yes' === get_option( 'wc_memberships_show_excerpts' );

                        foreach ( $products_array as $key => $product_item ) {
                            if ( array_search( $product_item['parent_id'], $restricted_posts ) !== false ) {
                                $products_array[$key]['image'] = wc_placeholder_img_src();
                                $products_array[$key]['excerpt'] = $show_excerpts ? $product_item['excerpt'] : '';
                                $products_array[$key]['price'] = '';
                                $products_array[$key]['categories'] = '';
                                $products_array[$key]['tags'] = '';
                                $products_array[$key]['brands'] = '';
                                $products_array[$key]['on_sale'] = '';
                                $products_array[$key]['sku'] = '';
                                $products_array[$key]['gtin'] = '';
                                $products_array[$key]['stock_status'] = '';
                                $products_array[$key]['rating'] = '';
                                $products_array[$key]['reviews'] = '';
                                $products_array[$key]['variations'] = '';
                                $products_array[$key]['add_to_cart'] = '';
                            }
                        }

                    }
                }
            }

            return $products_array;

        }

        /*
         * Get array of user IDs by membership plan ID
         */
        private function get_users_by_membership( $plan = 0 ) {

            global $wpdb;

            $users = array();

            $sql = "SELECT DISTINCT post_author 
                    FROM $wpdb->posts
                    WHERE post_type = 'wc_user_membership'
                    AND post_status = 'wcm-active'
            ";

            if ( $plan ) {
                $sql .= " AND post_parent = {$plan}";
            }

            $results = $wpdb->get_results( $sql );

            if ( ! empty( $results ) && !is_wp_error( $results ) ) {
                foreach ( $results as $result ) {
                    $users[] = $result->post_author;
                }
            }

            return $users;

        }

    }

endif;

AWS_Woo_Memberships::instance();