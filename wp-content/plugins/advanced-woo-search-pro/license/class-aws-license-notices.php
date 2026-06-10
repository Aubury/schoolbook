<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWS_License_Notices' ) ) :

    /**
     * Class for pro plugin updates
     */
    class AWS_License_Notices {

        /**
         * @var AWS_License_Notices The single instance of the class
         */
        protected static $_instance = null;

        /**
         * @var AWS_License_Notices License key
         */
        private $license_key = false;

        /**
         * @var AWS_License_Notices Plugin data
         */
        private $plugin_info = false;

        /**
         * Main AWS_License_Notices Instance
         *
         * Ensures only one instance of AWS_License_Notices is loaded or can be loaded.
         *
         * @static
         * @return AWS_License_Notices - Main instance
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
        private function __construct() {

            if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                return;
            }

            /**
             * Disable all plugin license notices
             * @since 2.42
             * @param boolean
             */
            $disable = apply_filters( 'aws_license_disable_all_notices', false );

            if ( $disable ) {
                return;
            }

            add_action( 'admin_init', array( $this, 'add_menu_notices' ) );

            add_action( 'aws_updater_page_top', array( $this, 'add_updater_messages' ) );

            add_action( 'admin_notices', array( $this, 'add_global_notices' ) );

            add_action( 'admin_init', array( $this, 'hide_global_notices' ) );

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

        /*
         * Get plugin info data
         */
        private function get_plugin_info() {
            if ( ! $this->plugin_info  ) {
                $this->plugin_info = AWS_PRO()->license->updater->get_plugin_info();
            }
            return $this->plugin_info;
        }

        /*
         * Add updater page messages
         */
        public function add_updater_messages() {

            $license_key = $this->get_license_key();
            $plugin_info = $this->get_plugin_info();

            $html = '';

            // show when license key field is empty
            if ( ! $license_key ) {
                $html .= '<div class="aws-license-notice">';
                    $html .= '<div class="aws-license-notice--content">';
                        $html .= '<h2>' . __( 'License key inactive', 'advanced-woo-search' ) . '</h2>';
                        $html .= '<p>' . sprintf( __( "Please activate your license key in the box below to receive plugin updates. If you don't know your license key - please %s.", 'advanced-woo-search' ), '<a target="_blank" href="https://advanced-woo-search.com/contact/?utm_source=wp-plugin&utm_medium=updater&utm_campaign=license">' . __( 'contact support', 'advanced-woo-search' ) . '</a>' ) . '</p>';
                    $html .= '</div>';
                $html .= '</div>';
            }

            // show when license key expired ( license inactive )
            if ( $license_key && $plugin_info && $plugin_info->license_status && $plugin_info->license_status === 'expired' ) {
                $renew_link = property_exists( $plugin_info, 'renewal_link' ) && $plugin_info->renewal_link ? $plugin_info->renewal_link : 'https://portal.advanced-woo-search.com/';
                $html .= '<div class="aws-license-notice">';
                    $html .= '<div class="aws-license-notice--content">';
                        $html .= '<h2>' . __( 'License key expired', 'advanced-woo-search' ) . '</h2>';
                        $html .= '<p>' . __( "Looks like your license is expired. You still can use this plugin but the new plugin updates are not available for you. Please renew your license to continue receiving the latest updates.", 'advanced-woo-search' ) . '</p>';
                        $html .= '<a href="' . $renew_link . '" target="_blank" class="button button-primary">' . __( 'Renew License', 'advanced-woo-search' ) . '</a>';
                        $html .= '&nbsp;&nbsp;<a href="https://advanced-woo-search.com/contact/?utm_source=wp-plugin&utm_medium=updater&utm_campaign=license" target="_blank" class="button button-primary">' . __( 'Contact Support', 'advanced-woo-search' ) . '</a>';
                    $html .= '</div>';
                $html .= '</div>';
            }

            // show when missed 5+ plugin updates
            if ( $plugin_info && property_exists( $plugin_info, 'updates_missed' ) && intval( $plugin_info->updates_missed ) > 5 ) {
                $html .= '<div class="aws-license-notice">';
                    $html .= '<div class="aws-license-notice--content">';
                        $html .= '<h2>' . __( 'Important plugin updates missed', 'advanced-woo-search' ) . '</h2>';
                        $html .= '<p>' . sprintf( __( 'Looks like you missed %s plugin updates. Please update the plugin to the latest version. It is very important to always use the latest plugin version as it can contain bugs/compatibility fixes and new cool features.', 'advanced-woo-search' ), '<strong style="color: #ff0000;">'.$plugin_info->updates_missed.'</strong>' ) . '</p>';
                    $html .= '</div>';
                $html .= '</div>';
            }

            echo $html;

        }

        /*
         * Add notices dots for admin menu items
         */
        public function add_menu_notices() {

            global $submenu, $menu, $pagenow;

            $notices_num = $this->calculate_notices_num();

            if ( ! $notices_num ) {
                return;
            }

            $license_action_html = '<span class="aws-updater-actions update-plugins" style="background-color:#ff0000;margin-left: 5px;"><span class="aws-updater-actions-count">' . $notices_num . '</span></span>';

            if ( current_user_can( AWS_Admin_Helpers::user_admin_capability() ) ) {

                if ( $menu ) {
                    foreach ( $menu as $menu_key => $menu_item ) {
                        if ( $menu_item[2] === 'aws-options' ) {
                            $menu[$menu_key][0] = '<div style="position:relative;white-space:nowrap;">' .  $menu[$menu_key][0] . '<span style="position:absolute;right:4px;">' . $license_action_html . '</span></div>';
                        }
                    }
                }

                if ( $submenu ) {
                    foreach ( $submenu as $submenu_key => $submenu_items ) {
                        if ( $submenu_key === 'aws-options' && $submenu_items ) {
                            foreach ( $submenu_items as $submenu_item_key => $submenu_item ) {
                                if ( $submenu_item[2] === 'aws-options-updates' ) {
                                    $submenu[$submenu_key][$submenu_item_key][0] .= $license_action_html;
                                }
                            }
                        }
                    }
                }

            }

        }

        /*
         * Add global admin notices
         */
        public function add_global_notices() {

            if ( isset( $_GET['page'] ) && $_GET['page'] === 'aws-options-updates' ) {
                return;
            }

            if ( ! current_user_can( AWS_Admin_Helpers::user_admin_capability() ) ) {
                return;
            }

            $license_key = $this->get_license_key();
            $plugin_info = $this->get_plugin_info();

            $notices = array();

            $is_license_expired = $license_key && $plugin_info && $plugin_info->license_status && $plugin_info->license_status === 'expired';
            $renew_link = $license_key && $plugin_info && property_exists( $plugin_info, 'renewal_link' ) && $plugin_info->renewal_link ? $plugin_info->renewal_link : 'https://portal.advanced-woo-search.com/';

            // show when license key expired ( license inactive )
            if ( $is_license_expired ) {

                $notices[] = array(
                    'id' => 'lic_expired',
                    'title' => __( 'License key expired', 'advanced-woo-search' ),
                    'message' => __( "Looks like your license is expired. You still can use this plugin but the new plugin updates are not available for you. Please renew your license to continue receiving the latest updates.", 'advanced-woo-search' ),
                    'type' => 'error',
                    'buttons' => array(
                        array(
                            'text' => __( 'Renew License', 'advanced-woo-search' ) ,
                            'link' => $renew_link,
                            'target' => '_blank'
                        ),
                        array(
                            'text' => __( 'Open Plugin Page', 'advanced-woo-search' ),
                            'link' => esc_url( admin_url('admin.php?page=aws-options-updates') ),
                        ),
                    )
                );

            }

            // show when missed 5+, 10+, 15+ plugin updates and license is empty or expired
            if ( ( $is_license_expired || ! $license_key ) && $plugin_info && property_exists( $plugin_info, 'updates_missed' ) && intval( $plugin_info->updates_missed ) > 5  ) {

                $num_missed_updates = intval( $plugin_info->updates_missed );

                if ( $num_missed_updates >= 15 ) {
                    $updates_option_cur_value = '15';
                } elseif ( $num_missed_updates > 10 ) {
                    $updates_option_cur_value = '10';
                } else {
                    $updates_option_cur_value = '5';
                }

                $updates_option = false;
                foreach ( array( '5', '10', '15' ) as $updates_option_val ) {
                    $updates_option = get_option('aws_hide_global_msg_missed_updates_' . $updates_option_val) ? $updates_option_val : $updates_option;
                }

                if ( ! $updates_option || ( intval( $updates_option ) + 5 < $num_missed_updates && intval( $updates_option ) <= 15  ) ) {

                    $buttons = array();
                    $bottom_msg = '';

                    if ( $is_license_expired ) {

                        $bottom_msg = '<p>' .__('Looks like your license key is expired. Please renew first.', 'advanced-woo-search') . '</p>';
                        $buttons = array(
                            array(
                                'text' => __( 'Renew License', 'advanced-woo-search' ) ,
                                'link' => $renew_link,
                                'target' => '_blank'
                            ),
                            array(
                                'text' => __( 'Open Plugin Page', 'advanced-woo-search' ),
                                'link' => esc_url(admin_url('admin.php?page=aws-options-updates'))
                            ),
                        );

                    } elseif ( ! $license_key ) {

                        $bottom_msg = '<p>' . __('Your license key is inactive. Please activate it to start receiving plugin updates.', 'advanced-woo-search') . '</p>';
                        $buttons = array(
                            array(
                                'text' => __( 'Activate License', 'advanced-woo-search' ) ,
                                'link' => esc_url(admin_url('admin.php?page=aws-options-updates'))
                            ),
                        );

                    }

                    $notices[] = array(
                        'id' => 'missed_updates_' . $updates_option_cur_value,
                        'title' => __( 'Important plugin updates missed', 'advanced-woo-search' ),
                        'message' => sprintf(__('Looks like you missed %s plugin updates. Please update the plugin to the latest version. It is very important to always use the latest plugin version as it can contain bugs/compatibility fixes and new cool features.', 'advanced-woo-search'), '<strong style="color: #ff0000;">'.$num_missed_updates.'</strong>') . '<br>' . $bottom_msg,
                        'type' => 'error',
                        'buttons' => $buttons
                    );

                }

            }

            // plugin custom global notices
            if ( $plugin_info && property_exists( $plugin_info, 'custom_messages' ) && is_array( $plugin_info->custom_messages ) && isset( $plugin_info->custom_messages['global'] ) ) {

                $custom_messages = is_array( $plugin_info->custom_messages['global'] ) ? $plugin_info->custom_messages['global'] : array();

                foreach ( $custom_messages as $custom_message ) {
                    $notices[] = $custom_message;
                }

            }

            $html = $this->generate_global_notice_html( $notices );

            echo $html;

        }

        /*
         * Hide global admin notices
         */
        public function hide_global_notices() {

            foreach ( $_GET as $get_param_name => $get_param_val ) {
                if ( strpos( $get_param_name, 'aws_hide_global_msg_' ) !== false ) {
                    update_option( $get_param_name, 'true', false );
                }
            }

        }

        /*
         * Generate global notices html
         */
        private function generate_global_notice_html( $notices ) {

            $current_page_url = function_exists('wc_get_current_admin_url') ? wc_get_current_admin_url() : esc_url( admin_url('admin.php?page=aws-options'));
            $dismiss_link = strpos( $current_page_url, '?' ) === false ? $current_page_url . '?' : $current_page_url . '&';

            $html = '';

            if ( is_array( $notices ) ) {
                foreach( $notices as $notice ) {

                    $id = isset( $notice['id'] ) ? 'aws_hide_global_msg_' . $notice['id'] : '';

                    if ( get_option( $id ) ) {
                        continue;
                    }

                    $buttons_html = '';

                    if ( isset( $notice['buttons'] ) && is_array( $notice['buttons'] ) ) {
                        foreach ( $notice['buttons'] as $button_props ) {
                            $target = isset( $button_props['target'] ) ? 'target="' . $button_props['target'] . '"' : '';
                            $buttons_html .= '<a href="' . $button_props['link'] . '" ' . $target . ' class="button button-primary">' . $button_props['text'] . '</a>&nbsp;&nbsp;';
                        }
                    }

                    $type = isset( $notice['type'] ) ? 'notice-' . $notice['type'] : 'notice-error';

                    $html .= '<div class="aws-license-notice notice ' . $type . '" style="position:relative;">';
                        $html .= '<div class="aws-license-notice--content">';
                            $html .= '<h2>Advanced Woo Search PRO: ' . $notice['title'] . '</h2>';
                            $html .= '<p>' . $notice['message'] . '</p>';
                            $html .= $buttons_html;
                            $html .= '<div style="margin-bottom:15px;"></div>';
                            $html .= '<a href="' . $dismiss_link . $id . '" title="' . __( 'Dismiss', 'advanced-woo-search'  ) . '" style="color:#787c82;text-decoration:none;font-size:16px;position:absolute;top:0;right:1px;border:none;margin:0;padding:9px;background:0 0;cursor:pointer;"><span style="font-size:16px;" class="dashicons dashicons-dismiss"></span></a>';
                        $html .= '</div>';
                    $html .= '</div>';

                }
            }

            echo $html;

        }

        /*
         * Calculate number of license notices
         */
        private function calculate_notices_num() {

            $license_key = $this->get_license_key();
            $plugin_info = $this->get_plugin_info();

            $num = 0;

            if ( ! $license_key ) {
                $num++;
            }

            if ( $license_key && $plugin_info && $plugin_info->license_status && $plugin_info->license_status === 'expired' ) {
                $num++;
            }

            if ( $plugin_info && property_exists( $plugin_info, 'updates_missed' ) && intval( $plugin_info->updates_missed ) > 5 ) {
                $num++;
            }

            return $num;

        }

    }

endif;

add_action( 'init', 'AWS_License_Notices::instance' );