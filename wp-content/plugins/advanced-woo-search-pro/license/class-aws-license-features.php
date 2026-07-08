<?php

/*
 * PRO plugin features under license
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWS_License_Features' ) ) :

    /**
     * Class for plugin admin panel
     */
    class AWS_License_Features {

        /**
         * @var AWS_License_Features The single instance of the class
         */
        protected static $_instance = null;

        /**
         * @var AWS_License_Notices License key
         */
        private $license_key = false;

        /**
         * Main AWS_License_Features Instance
         *
         * @static
         * @return AWS_License_Features - Main instance
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /*
         * Constructor
         */
        public function __construct() {

            add_filter( 'aws_admin_page_options', array( $this, 'aws_admin_page_options' ), 999 );

        }

        /*
         * Block/ublock premium plugin settings
         */
        public function aws_admin_page_options( $options ) {

            $license_key = $this->get_license_key();

            foreach ( $options as $options_group => $options_arr ) {
                foreach ( $options_arr as $option_key => $option_value ) {

                    if ( $license_key && isset( $option_value['pro_choices'] ) ) {
                        $options[$options_group][$option_key]['pro_choices'] = array();
                    }

                    if ( ! $license_key && isset( $option_value['only_pro'] ) ) {
                        $options[$options_group][$option_key]['disabled'] = true;
                        $options[$options_group][$option_key]['name'] = $option_value['name'] . ' <a href="'. esc_url( admin_url('admin.php?page=aws-options-updates') ) .'">' . __( "(Pro)", "advanced-woo-search" ) . '</a>';
                    }

                    if ( ! $license_key && isset( $option_value['pro_choices'] ) && isset( $option_value['choices'] ) ) {
                        foreach ( $option_value['choices'] as $choice_id => $choice ) {
                            if ( array_search( $choice_id, $option_value['pro_choices'] ) !== false && isset( $choice['label'] ) ) {
                                $current_label = $choice['label'];
                                $options[$options_group][$option_key]['choices'][$choice_id]['label'] = $current_label . ' <a href="'. esc_url( admin_url('admin.php?page=aws-options-updates') ) .'">' . __( "(Pro)", "advanced-woo-search" ) . '</a>';
                            }
                        }
                    }

                }
            }

            return $options;

        }


        /*
         * Get plugin license key
         */
        private function get_license_key() {
            if ( ! $this->license_key ) {
                $this->license_key = AWS_PRO()->license->get_license_key();
            }
            return $this->license_key;
        }

    }

endif;

AWS_License_Features::instance();