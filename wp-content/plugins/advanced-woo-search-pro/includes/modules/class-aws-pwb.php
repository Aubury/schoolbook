<?php
/**
 * Perfect Brands for WooCommerce support
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'AWS_PWB' ) ) :

    /**
     * Class
     */
    class AWS_PWB {

        /**
         * Main AWS_PWB Instance
         *
         * Ensures only one instance of AWS_PWB is loaded or can be loaded.
         *
         * @static
         * @return AWS_PWB - Main instance
         */
        protected static $_instance = null;

        /**
         * Main AWS_PWB Instance
         *
         * Ensures only one instance of AWS_PWB is loaded or can be loaded.
         *
         * @static
         * @return AWS_PWB - Main instance
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

            add_filter( 'aws_search_results_products', array( $this, 'pwb_add_brands' ) );

            add_filter( 'aws_search_tax_results', array( $this, 'pwb_tax_results' ), 10, 2 );

            add_filter( 'aws_search_page_filters', array( $this, 'search_page_filters' ) );

        }

        /*
         * Add brands to results
         */
        public function pwb_add_brands( $products ) {
            foreach( $products as $key => $product) {
                if ( isset( $product['brands'] ) ) {
                    $id = $product['id'];
                    $terms = get_the_terms( $id, 'pwb-brand' );
                    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {

                        $brands_array = array();

                        foreach ( $terms as $term ) {
                            $thumb_id = get_term_meta( $term->term_id, 'pwb_brand_image', 1 );
                            $thumb_src = wp_get_attachment_image_src( $thumb_id, 'thumbnail' );
                            $brands_array[] = array(
                                'name'  => $term->name,
                                'image' => $thumb_src ? $thumb_src[0] : ''
                            );
                        }

                        if ( $product['brands'] && is_array( $product['brands'] ) ) {
                            foreach ( $brands_array as $brand ) {
                                $products[$key]['brands'][] = $brand;
                            }
                        } else {
                            $products[$key]['brands'] = $brands_array;
                        }

                    }
                }
            }
            return $products;
        }

        /*
         * Add brands image to archive pages results
         */
        public function pwb_tax_results( $result_array, $taxonomy ) {
            if ( isset( $result_array['pwb-brand'] ) && ! empty( $result_array['pwb-brand'] ) ) {
                foreach ( $result_array['pwb-brand'] as $key => $brand ) {
                    $thumb_id = get_term_meta( $brand['id'], 'pwb_brand_image', 1 );
                    $thumb_src = wp_get_attachment_image_src( $thumb_id, 'thumbnail' );
                    if ( $thumb_src ) {
                        $result_array['pwb-brand'][$key]['image'] = $thumb_src[0];
                    }
                }
            }
            return $result_array;
        }

        /*
         * Update filter widget for pwb-brand taxonomy
         */
        public function search_page_filters( $filters ) {

            if ( isset( $_GET['pwb-brand-filter'] ) ) {

                $terms_arr = explode( ',', $_GET['pwb-brand-filter'] );

                $filters['tax']['pwb-brand'] = array(
                    'terms' => $terms_arr,
                    'operator' => 'OR'
                );

            }

            return $filters;

        }

    }

endif;

AWS_PWB::instance();