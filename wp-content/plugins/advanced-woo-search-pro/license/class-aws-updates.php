<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWS_Updater' ) ) :

    /**
     * Class for pro plugin updates
     */
    class AWS_Updater {

        /**
         * The plugin current version
         * @var string
         */
        public $current_version;

        /**
         * The plugin remote update path
         * @var string
         */
        public $update_path;

        /**
         * Plugin Slug (plugin_directory/plugin_file.php)
         * @var string
         */
        public $plugin_slug;

        /**
         * Plugin name (plugin_file)
         * @var string
         */
        public $slug;

        /**
         * Name for transient metadata option
         * @var string
         */
        public $remote_data_option;

        /**
         * Name for transient metadata option for last checked time
         * @var string
         */
        public $remote_data_check_option;


        /**
         * Initialize a new instance of the WordPress Auto-Update class
         * @param array $conf Config
         */
        function __construct( $conf ) {

            // Set the class public variables
            $this->current_version            = $conf['current_version'];
            $this->update_path                = $conf['update_path'];
            $this->plugin_slug                = $conf['plugin_slug'];
            $this->slug                       = $conf['slug'];

            $this->remote_data_option         = $conf['remote_data'];
            $this->remote_data_check_option   = $conf['remote_data_check'];

            add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );

            add_filter( 'plugins_api', array( $this, 'check_info' ), 10, 3 );

            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 4 );

        }


        /**
         * Add our self-hosted autoupdate plugin to the filter transient
         *
         * @param $transient
         * @return object $ transient
         */
        public function check_update( $transient ) {

            if ( empty( $transient->checked ) ) {
                return $transient;
            }

            // Get the remote version
            $remote_version = $this->get_plugin_remote_version();

            // If a newer version is available, add the update
            if ( $remote_version && version_compare( $this->current_version, $remote_version, '<' ) ) {

                // Get the remote information
                $information = $this->get_plugin_remote_info_option();

                if ( ! $information ) {
                    return $transient;
                }

                $obj = new stdClass();
                $obj->slug = $this->slug;
                $obj->new_version = $remote_version;
                $obj->url = $information->homepage;

                if ( $information->download_url ) {
                    $obj->package = $information->download_url;
                }

                $transient->response[$this->plugin_slug] = $obj;

            }

            return $transient;

        }

        /**
         * Add self-hosted description to the filter
         *
         * @param boolean $false
         * @param array $action
         * @param object $arg
         * @return bool|object
         */
        public function check_info( $false, $action, $arg ) {

            if ( property_exists( $arg, 'slug' ) && $arg->slug && $arg->slug === $this->slug ) {

                $information = $this->get_plugin_remote_info_option();

                return $information;

            }

            return $false;

        }

        /*
         * Get plugin metadata
         */
        public function get_plugin_info() {

            $information = get_option( $this->remote_data_option );

            return $information;

        }

        /*
         * Get plugin remote version
         */
        public function get_plugin_remote_version() {

            $information = $this->get_plugin_remote_info_option();

            if ( $information && $information->version ) {
                return $information->version;
            }

            return false;

        }

        /*
         * Return plugin info from remote server. Check option expiration time
         */
        public function get_plugin_remote_info_option() {

            $now = time();
            $expiration = 60 * 60 * 12;

            $ts = (int) get_option( $this->remote_data_check_option );
            $information = get_option( $this->remote_data_option );

            if ( ! $information || $ts + $expiration < $now ) {

                $information = $this->getRemote_information();

                if ( $information ) {
                    update_option( $this->remote_data_option, $information );
                }

                update_option( $this->remote_data_check_option, $now  );

            }

            return $information;


        }

        /**
         * Get plugin remote information
         * @return bool|object
         */
        public function getRemote_information() {

            $request = wp_remote_post( $this->update_path, array( 'timeout' => 30, 'sslverify' => $this->verify_ssl(), 'body' => array(
                'action' => 'get_metadata',
                'slug' => $this->slug,
                'installed_version' => $this->current_version,
                'license' => AWS_PRO()->license->get_license_key(),
                'ismu' => is_multisite()
            ) ) );


            if ( ! is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) === 200 ) {

                $response = is_array($request) && isset($request['body']) && $request['body'] ? @unserialize( $request['body'] ) : false;

                if ( $response ) {

                    $response = apply_filters( 'aws_remote_information', $response );

                    return $response;

                }

            }

            return false;

        }

        /**
         * Return the status of the plugin licensing
         * @param string $license_key
         * @return array $remote_license
         */
        public function get_remote_license( $license_key = '' ) {

            $request = wp_remote_post( $this->update_path, array( 'timeout' => 30, 'sslverify' => $this->verify_ssl(), 'body' => array(
                'action' => 'license',
                'slug' => $this->slug,
                'license' => $license_key,
                'installed_version' => $this->current_version,
                'ismu' => is_multisite()
            ) ) );

            $response_text = 'Invalid';
            if ( is_array( $request ) && isset( $request['body'] ) && $request['body'] && preg_match( '/<p>(.+?)<\/p>/', $request['body'], $matches ) ) {
                $response_text = $matches[1];
            }

            if ( ! is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) === 200 ) {
                return array( 'success' => true, 'text' => $response_text );
            }

            return array( 'success' => false, 'text' => $response_text );

        }

        /**
         * Remove plugin license and clear domain
         * @param string $license_key
         * @return boolean $remote_license
         */
        public function remove_license( $license_key = '' ) {

            $request = wp_remote_post( $this->update_path, array( 'timeout' => 30, 'sslverify' => $this->verify_ssl(), 'body' => array(
                'action' => 'license_remove',
                'slug' => $this->slug,
                'license' => $license_key,
                'installed_version' => $this->current_version,
                'ismu' => is_multisite()
            ) ) );

            if ( ! is_wp_error( $request ) && wp_remote_retrieve_response_code( $request ) === 200 ) {
                return true;
            }

            return false;

        }

        /*
         * Add 'View details' link to the plugins admin page list
         * @return array $plugin_meta Meta rows
         */
        public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data, $status ) {

            if ( $plugin_file == AWS_PRO_BASENAME && ! isset( $plugin_data['slug'] ) && current_user_can( 'install_plugins' ) ) {

                $plugin_name = $plugin_data['Name'];

                $plugin_meta[] = sprintf(
                    '<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
                    esc_url(
                        network_admin_url(
                            'plugin-install.php?tab=plugin-information&plugin=' . $this->slug .
                            '&TB_iframe=true&width=600&height=550'
                        )
                    ),
                    /* translators: %s: Plugin name. */
                    esc_attr( sprintf( __( 'More information about %s' ), $plugin_name ) ),
                    esc_attr( $plugin_name ),
                    __( 'View details' )
                );

            }

            return $plugin_meta;

        }

        /**
         * Returns if the SSL of the store should be verified.
         *
         * @since 2.60
         * @return bool
         */
        private function verify_ssl() {
            return (bool) apply_filters( 'aws_api_request_verify_ssl', true, $this );
        }

    }


endif;