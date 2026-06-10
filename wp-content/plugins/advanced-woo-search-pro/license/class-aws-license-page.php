<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWS_License_Page' ) ) :

    /**
     * Class for pro plugin license manager
     */
    class AWS_License_Page {

        /**
         * @var AWS_License_Page The single instance of the class
         */
        protected static $_instance = null;

        /**
         * @var AWS_License_Page License key
         */
        private $license_key = false;

        /**
         * Main AWS_License_Page Instance
         *
         * Ensures only one instance of AWS_License_Page is loaded or can be loaded.
         *
         * @static
         * @return AWS_License_Page - Main instance
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
        function __construct() {

            $this->license_key = AWS_PRO()->license->get_license_key();

            add_action( 'admin_init', array( $this, 'admin_init' ) );

            add_action( 'admin_menu', array( $this, 'add_admin_page' ) );

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

        }

        /*
         * On page init
         */
        public function admin_init() {
            if ( isset( $_GET['aws-ssl'] ) ) {
                add_filter( 'aws_api_request_verify_ssl', '__return_false' );
                delete_transient( 'advanced_woo_search_pro_info' );
            }
        }

        /*
         * Add options page
         */
        public function add_admin_page() {
            add_submenu_page( 'aws-options', esc_html__('License','advanced-woo-search'), esc_html__('License','advanced-woo-search'), AWS_Admin_Helpers::user_admin_capability(), 'aws-options-updates', array( $this,'updates_page' ) );
        }

        /*
         * License page content
         */
        public function updates_page() {

            echo AWS_Admin_Meta_Boxes::get_header();

            echo '<div class="wrap">';

                echo '<h1></h1>';

                echo '<div class="aws-box-wrap">';

                    do_action( 'aws_updater_page_top' );

                    $this->license_block();

                    $this->info_block();

                echo '</div>';

            echo '</div>';

        }

        /*
         * Block with license info
         */
        private function license_block() {

            $license_key = $this->license_key ? '***********************' : '';
            $valid_class = $this->license_key ? 'valid' : '';
            $deactivate_input = $this->license_key ? ' disabled' : '';

            $btn_html = $this->license_key ?
                '<button id="activate-license" data-is-active="active" class="button button-primary">' . esc_html__( 'Deactivate License', 'advanced-woo-search' ) . '</button>' :
                '<button id="activate-license" data-is-active="inactive" class="button button-primary">' . esc_html__( 'Activate License', 'advanced-woo-search' ) . '</button>';


            echo '<div class="aws-box">';

                echo '<div class="title">';
                    echo '<h3>' . esc_html__( 'License Information', 'advanced-woo-search' ) . '</h3>';
                echo '</div>';

                echo '<div class="inner">';

                    echo '<p>';
                        echo __( 'To unlock updates, please enter your license key below. You can find it inside an email that you receive after the purchase.', 'advanced-woo-search' ) . '<br>';
                        echo sprintf( esc_html__( 'Also, to manage your account information and license activations please visit %s. If you don\'t have a licence key, please visit %s.', 'advanced-woo-search' ), '<a target="_blank" href="https://portal.advanced-woo-search.com/">' . esc_html__( 'user portal', 'advanced-woo-search' ) . '</a>', '<a target="_blank" href="https://advanced-woo-search.com/pricing/">' . esc_html__( 'plugin page', 'advanced-woo-search' ) . '</a>' );
                        echo '<br>';
                    echo '</p>';

                    echo '<form data-license-form action="" name="aws_form" id="aws_form" method="post" class="'. $valid_class .'">';

                        echo '<table class="form-table">';
                            echo '<tbody>';

                                echo '<tr valign="top">';

                                    echo '<th scope="row">'. esc_html__('License Key', 'advanced-woo-search' ) . '</th>';

                                    echo '<td>';
                                        echo '<input' . $deactivate_input . ' type="text" name="license_key" class="regular-text" value="'.esc_attr( $license_key ).'">';
                                    echo '</td>';

                                    echo '<td>';
                                        echo '<div class="aws-license-btn-wrap">';
                                            echo $btn_html;
                                            echo '<div class="aws-loader"></div>';
                                        echo '</div>';
                                    echo '</td>';

                                echo '</tr>';

                            echo '</tbody>';
                        echo '</table>';

                        echo '<div class="license-error">' . esc_html__( 'Sorry, your license key is not valid.', 'advanced-woo-search' ) . '</div>';
                        echo '<div class="license-valid">' . esc_html__( 'Your license key is active.', 'advanced-woo-search' ) . '</div>';

                    echo '</form>';

                echo '</div>';

            echo '</div>';

        }

        /*
         * Block with plugin info
         */
        private function info_block() {

            $plugin_info = AWS_PRO()->license->updater->get_plugin_remote_info_option();
            $license_key = $this->license_key ? $this->license_key : '';

            if ( ! $plugin_info ) {
                echo esc_html__( 'Something goes wrong while getting plugin data.', 'advanced-woo-search' );
                echo '<br><br><a id="change-ssl-check" class="button button-secondary" href="' . esc_url( admin_url( 'admin.php?page=aws-options-updates&aws-ssl=false' ) ) . '">' . esc_html__( 'Try to reconnect', 'advanced-woo-search' ) . '</a></div>';
                return;
            }

            $plugin_latest_version = $plugin_info->new_version;

            if ( version_compare( AWS_PRO_VERSION, $plugin_latest_version, '<' ) ) {

                if ( $license_key ) {
                    $plugin_update_available = esc_html__( 'Yes', 'advanced-woo-search' ) . '<a id="update-plugin" class="button button-primary aws-update" href="' . admin_url('plugins.php?s=Advanced+Woo+Search+Pro') . '">' . esc_html__( 'Update Plugin', 'advanced-woo-search' ) . '</a><div class="aws-loader"></div>';
                } else {
                    $plugin_update_available = esc_html__( 'Yes', 'advanced-woo-search' ) . '<a class="button aws-update" disabled="disabled" href="#">' . esc_html__( 'Please enter your license key to unlock updates', 'advanced-woo-search' ) . '</a>';
                }

                $plugin_changelog = $plugin_info->sections['changelog'];

                if ( $plugin_changelog ) {

                    preg_match( '/(<h4[\S\s]*?)<h4>'.AWS_PRO_VERSION.'.*?<\/h4>/i', $plugin_changelog, $matches );

                    if ( $matches && isset( $matches[1] ) ) {
                        $plugin_changelog = $matches[1];
                    }

                }

            } else {
                $plugin_update_available = esc_html__( 'No', 'advanced-woo-search' );
                $plugin_changelog ='';
            }


            // Update info
            echo '<div class="aws-box">';

                echo '<div class="title">';
                    echo '<h3>' . esc_html__( 'Update Information', 'advanced-woo-search' ) . '</h3>';
                echo '</div>';

                echo '<div class="inner">';

                    echo '<table class="form-table">';
                        echo '<tbody>';

                            echo '<tr valign="top">';

                                echo '<th scope="row">'. esc_html__('Refresh', 'advanced-woo-search' ) . '</th>';

                                echo '<td class="aws-license-btn-wrap">';
                                    echo '<a id="refresh-plugin-info" class="button button-secondary" href="#">' . esc_html__( 'Refresh Plugin Info', 'advanced-woo-search' ) . '</a><div class="aws-loader"></div>';
                                echo '</td>';

                            echo '</tr>';

                            echo '<tr valign="top">';

                                echo '<th scope="row">'. esc_html__('Current Version', 'advanced-woo-search' ) . '</th>';

                                echo '<td>';
                                    echo AWS_PRO_VERSION;
                                echo '</td>';

                            echo '</tr>';

                            echo '<tr valign="top">';

                                echo '<th scope="row">'. esc_html__('Latest Version', 'advanced-woo-search' ) . '</th>';

                                echo '<td>';
                                    echo $plugin_latest_version;
                                echo '</td>';

                            echo '</tr>';

                            echo '<tr valign="top">';

                                echo '<th scope="row">'. esc_html__('Update Available', 'advanced-woo-search' ) . '</th>';

                                echo '<td class="aws-license-btn-wrap">';
                                    echo $plugin_update_available;
                                echo '</td>';

                            echo '</tr>';

                            echo '<tr valign="top">';

                                echo '<th scope="row">'. esc_html__('Changelog', 'advanced-woo-search' ) . '</th>';

                                echo '<td>';

                                    echo $plugin_changelog;

                                    echo '<a href="https://advanced-woo-search.com/guide/premium-version/" target="_blank">' . esc_html__( 'View all changelog', 'advanced-woo-search' ) . '</a>';

                                echo '</td>';

                            echo '</tr>';

                        echo '</tbody>';
                    echo '</table>';

                echo '</div>';

            echo '</div>';

        }

        /*
         * Enqueue admin scripts and styles
         */
        public function admin_enqueue_scripts() {

            if ( isset( $_GET['page'] ) && $_GET['page'] == 'aws-options-updates' ) {

                wp_enqueue_style( 'plugin-admin-updates-style', AWS_PRO_URL . 'license/assets/css/admin-updates.css' );

                wp_enqueue_script( 'jquery' );

                wp_enqueue_script( 'aws-admin-updates', AWS_PRO_URL . 'license/assets/js/admin-updates.js', array('jquery') );
                wp_localize_script( 'aws-admin-updates', 'aws_vars', array(
                    'ajaxurl'    => admin_url('admin-ajax.php' ),
                    'ajax_nonce' => wp_create_nonce( 'aws_pro_admin_ajax_nonce' ),
                ) );

            }

        }

    }


endif;

add_action( 'init', 'AWS_License_Page::instance' );