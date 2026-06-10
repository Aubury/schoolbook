<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWS_License' ) ) :

    /**
     * Class for pro plugin updates
     */
    class AWS_License {

        /**
         * Plugin license config
         * @var string
         */
        private $conf;

        /**
         * Plugin license key
         * @var string
         */
        private $license_key;

        /**
         * Plugin updater object
         * @var object
         */
        public $updater;

        /**
         * Initialize a new instance of the WordPress license class
         * @param string $current_version
         * @param string $update_path
         * @param string $plugin_slug
         */
        function __construct( $current_version, $update_path, $plugin_slug ) {

            // Set the class public variables
            list ($t1, $t2) = explode('/', $plugin_slug);
            $slug = str_replace('.php', '', $t2);

            $this->conf = array(
                'current_version'        => $current_version,
                'update_path'            => $update_path,
                'plugin_slug'            => $plugin_slug,
                'slug'                   => $slug,
                'remote_data'            => str_replace( '-', '_', $slug ) . '_remote_data',
                'remote_data_check'      => str_replace( '-', '_', $slug ) . '_remote_data_check',
            );

            $this->includes();
            $this->init();

            add_action( 'wp_ajax_wpunit-aws-ajax-actions', array( $this, 'ajax_actions' ) );

            add_filter( 'aws_remote_information', array( $this, 'remote_information' ) );

        }

        /**
         * Include required core files used in admin and on the frontend.
         */
        private function includes() {
            include_once( 'class-aws-updates.php' );
            include_once( 'class-aws-license-page.php' );
            include_once( 'class-aws-license-notices.php' );
        }

        /*
         * Init plugin classes
         */
        private function init() {
            $this->updater = new AWS_Updater( $this->conf );

            if ( is_admin() ) {
                add_action( 'in_plugin_update_message-' . $this->conf['plugin_slug'], array( $this, 'modify_plugin_update_message' ), 10, 2 );
            }

        }

        /**
         * Displays an update message for plugin list screens.
         */
        function modify_plugin_update_message( $plugin_data, $response ) {

            if ( $this->get_license_key() ) return;

            echo '<br />' . sprintf( esc_html__('To enable updates, please enter your license key on the %s updates %s page. If you don\'t have a licence key, please visit  %s plugin page %s.', 'advanced-woo-search'), '<a href="'.admin_url('admin.php?page=aws-options-updates').'">', '</a>', '<a href="https://advanced-woo-search.com/" target="_blank">', '</a>' );

        }

        /**
         * License ajax actions
         *
         * @return array $response
         */
        public function ajax_actions() {

            check_ajax_referer( 'aws_pro_admin_ajax_nonce' );

            $action_type = sanitize_text_field( $_POST['type'] );
            $response = array();

            if ( $action_type === 'verify-license' ) {

                $license_key = sanitize_text_field( $_POST['license'] );
                $license_response = $this->updater->get_remote_license( $license_key );

                if ( $license_response['success'] ) {
                    $response = array( 'type' => 'valid', 'text' => $license_response['text'] );
                    $this->update_license_key( $license_key );
                    $this->remove_transient();
                } else {
                    $response = array( 'type' => 'invalid', 'text' => $license_response['text'] );
                }

            }

            if ( $action_type === 'deactivate-license' ) {

                $license_key = $this->get_license_key();

                $this->updater->remove_license( $license_key );

                $this->remove_license_key();

                $this->remove_transient();

                $response = array( 'type' => 'deactivated', 'text' => '' );

            }

            if ( $action_type === 'clear-cache' ) {
                delete_site_transient( 'update_plugins' );
                $response = array( 'type' => 'cache cleared', 'text' => '' );
            }

            if ( $action_type === 'refresh-plugin-info' ) {

                delete_option( $this->conf['remote_data'] );
                delete_option( $this->conf['remote_data_check'] );

                $response = array( 'type' => 'plugin info updated', 'text' => '' );

            }

            wp_send_json_success( $response );

        }

        /*
         * Filter plugin remote information
         */
        public function remote_information( $response ) {
            if ( $response && property_exists( $response, 'domain_removed' ) && $response->domain_removed ) {
                $this->remove_license_key();
            }
            return $response;
        }

        /*
         * Get currently active license key
         */
        public function get_license_key() {
            $option_name = $this->get_license_option_name();
            return get_option( $option_name );
        }

        /*
         * Update currently active license key
         * @param string $license_key New license key
         */
        private function update_license_key( $license_key ) {
            $option_name = $this->get_license_option_name();
            update_option( $option_name, $license_key );
        }

        /*
         * Remove currently active license key
         */
        private function remove_license_key() {
            $option_name = $this->get_license_option_name();
            return delete_option( $option_name );
        }

        /*
         * Get option name for license key
         */
        public function get_license_option_name() {
            return trim( str_replace( '-', '_', $this->conf['slug'] ) );
        }

        /*
         * Remove plugin transient data
         */
        private function remove_transient() {
            if ( function_exists( 'wp_clean_plugins_cache' ) ) {
                wp_clean_plugins_cache();
            }
            delete_option( $this->conf['remote_data'] );
            delete_option( $this->conf['remote_data_check'] );
        }

    }

endif;