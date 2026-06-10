<?php
/**
 * WooCommerce Show Single Variations by Iconic plugin integration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'AWS_Single_Variations' ) ) :

    /**
     * Class
     */
    class AWS_Single_Variations {

        /**
         * Main AWS_Single_Variations Instance
         *
         * Ensures only one instance of AWS_Single_Variations is loaded or can be loaded.
         *
         * @static
         * @return AWS_Single_Variations - Main instance
         */
        protected static $_instance = null;

        /**
         * Main AWS_Single_Variations Instance
         *
         * Ensures only one instance of AWS_Single_Variations is loaded or can be loaded.
         *
         * @static
         * @return AWS_Single_Variations - Main instance
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

            add_filter( 'aws_indexed_data', array( $this, 'aws_indexed_data' ), 1, 2 );

            add_filter( 'aws_search_pre_filter_single_product', array( $this, 'aws_search_pre_filter_single_product' ), 1, 3  );

            add_action( 'woocommerce_product_set_visibility', array( $this, 'woocommerce_product_set_visibility' ), 99, 2 );

        }

        /*
         * Update index table on bulk visibility change
         */
        public function woocommerce_product_set_visibility( $id, $terms ) {

            if ( is_ajax() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'iconic_wssv_process_product_visibility' ) {

                $sync = AWS_PRO()->get_common_settings('autoupdates');

                if ( $terms && $sync !== 'false' ) {

                    $post_type = get_post_type( $id );
                    $new_visibility = '';

                    if ( $post_type && $post_type === 'product_variation' ) {
                        $visibility = get_post_meta( $id, '_visibility', true );
                        if ( $visibility && is_array( $visibility ) ) {
                            $new_visibility = $this->normalize_visibility( $visibility );
                        }
                    } else {
                        $new_visibility = $terms;
                    }

                    if ( $new_visibility && ! AWS_PRO()->option_vars->is_index_table_not_exists() ) {

                        global $wpdb;

                        $table_name = $wpdb->prefix . AWS_INDEX_TABLE_NAME;

                        $new_visibility = AWS_PRO()->table_updates->get_visibility_code( $new_visibility );

                        $wpdb->update( $table_name, array( 'visibility' => $new_visibility ), array( 'id' => $id ) );

                        do_action('aws_cache_clear');

                    }

                }

            }

        }

        /*
         * Filter indexed data
         */
        public function aws_indexed_data( $data, $id ) {
            if ( $data['type'] === 'child' || $data['type'] === 2 ) {

                $child_visibility  = get_post_meta( $id, '_visibility', true );
                $manage_categories = get_post_meta( $id, '_manage_product_cat', true );
                $manage_tags       = get_post_meta( $id, '_manage_product_tag', true );

                if ( $child_visibility && is_array( $child_visibility ) ) {

                    $new_visibility = $this->normalize_visibility( $child_visibility );

                    if ( $new_visibility ) {
                        $new_visibility = AWS_PRO()->table_updates->get_visibility_code( $new_visibility );
                        $data['visibility'] = $new_visibility;
                    }

                }

                if ( $manage_categories ) {
                    $child_categories = get_post_meta( $id, '_jck_wssv_variation_product_cat', true );
                    if ( $child_categories && ! empty( $child_categories ) ) {
                        foreach ( $child_categories as $child_category_id ) {
                            $term = get_term( $child_category_id, 'product_cat' );
                            if ( $term && ! is_wp_error( $term ) ) {
                                $source = 'category%' . $child_category_id . '%';
                                $data['terms'][$source] = AWS_Helpers::extract_terms( $term->name, 'cat' );
                            }
                        }
                    }

                }

                if ( $manage_tags ) {
                    $child_tags = get_post_meta( $id, '_jck_wssv_variation_product_tag', true );
                    if ( $child_tags && ! empty( $child_tags ) ) {
                        foreach ( $child_tags as $child_tag_id ) {
                            $term = get_term( $child_tag_id, 'product_tag' );
                            if ( $term && ! is_wp_error( $term ) ) {
                                $source = 'tag%' . $child_tag_id . '%';
                                $data['terms'][$source] = AWS_Helpers::extract_terms( $term->name, 'tag' );
                            }
                        }
                    }
                }

            }

            return $data;

        }

        /*
         * Change variation display title
         */
        public function aws_search_pre_filter_single_product( $result, $post_id, $product ) {
            if ( $product->is_type( 'variation' ) ) {
                $variation_title = $product->get_title();
                $variation_custom_title = get_post_meta( $post_id, '_jck_wssv_display_title', true );
                $result['title'] = ( $variation_custom_title ) ? $variation_custom_title : $variation_title;
            }
            return $result;
        }

        /*
         * Normalize visibility value
         */
        private function normalize_visibility( $visibility ) {

            $new_visibility = '';

            if ( array_search( 'catalog', $visibility ) !== false && array_search( 'search', $visibility ) !== false ) {
                $new_visibility = 'visible';
            } elseif ( array_search( 'catalog', $visibility ) !== false ) {
                $new_visibility = 'catalog';
            } elseif ( array_search( 'search', $visibility ) !== false ) {
                $new_visibility = 'search';
            }

            $new_visibility = AWS_PRO()->table_updates->get_visibility_code( $new_visibility );

            return $new_visibility;

        }

    }

endif;

AWS_Single_Variations::instance();