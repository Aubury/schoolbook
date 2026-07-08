<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWS_Remote_Notices' ) ) :

    /**
     * Class for pro plugin updates
     */
    class AWS_Remote_Notices {

        /**
         * @var AWS_Remote_Notices The single instance of the class
         */
        protected static $_instance = null;

        /*
         * Custom data
         */
        private $data = array();

        /**
         * Main AWS_Remote_Notices Instance
         *
         * Ensures only one instance of AWS_Remote_Notices is loaded or can be loaded.
         *
         * @static
         * @return AWS_Remote_Notices - Main instance
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

            // Get remote information
            add_filter( 'aws_remote_information', array( $this, 'remote_information' ) );

            // Show notices
            add_action( 'admin_notices', array( $this, 'add_remote_notices' ) );

            // Hide notices
            add_action( 'admin_init', array( $this, 'hide_notices' ) );

        }

        /*
         * Show remote admin notices
         */
        public function add_remote_notices() {

            $notices = $this->get_remote_notices();
            $filtered_notices = array();

            if ( $notices && is_array( $notices ) ) {

                $hide_option = $this->get_hiden_remote_notices();
                $has_one_notice = false;

                // check transitent. notices has cooldown period
                if ( get_transient('aws_pro_remote_notices_cooldown' ) ) {
                    return;
                }

                foreach ( $notices as $notice ) {

                    $notice_id = $notice['id'];

                    if ( ! current_user_can( 'manage_options' ) ) {
                        continue;
                    }

                    // allow to show only one notice per time
                    if ( $has_one_notice ) {
                        break;
                    }

                    // hide already dissmissed notices
                    if ( $hide_option && is_array( $hide_option ) && isset( $hide_option[$notice_id] ) ) {
                        // hide if its was dismissed less than 6 months ago
                        $notice_dismiss_time = intval( $hide_option[$notice_id] );
                        if ( ! $notice_dismiss_time || ( current_time( 'timestamp' ) - $notice_dismiss_time ) < 60 * 60 * 24 * 180 ) {
                            continue;
                        }
                    }

                    // check notice display conditions ( if exists )
                    if ( isset( $notice['conditions'] ) ) {
                        $exclude = $this->check_notice_conditions( $notice['conditions'] );
                        if ( $exclude ) {
                            continue;
                        }
                    }

                    if ( isset( $notice['title'] ) ) {
                        $notice['title'] = $this->set_text_vars( $notice['title'] );
                    }

                    if ( isset( $notice['message'] ) ) {
                        $notice['message'] = $this->set_text_vars( $notice['message'] );
                    }

                    $filtered_notices[] = $notice;
                    $has_one_notice = true;

                }
            }

            $html = AWS_License_Helpers::generate_global_notice_html( $filtered_notices );

            echo $html;

        }

        /*
         * Check is notice is meet its display conditions
         */
        private function check_notice_conditions( $conditions ) {

            $exclude = false;

            if ( isset( $conditions['get_page_include'] ) && is_array( $conditions['get_page_include'] ) ) {
                if ( ! isset( $_GET['page'] ) || ! in_array( $_GET['page'], $conditions['get_page_include'] ) ) {
                    $exclude = true;
                }
            }

            if ( isset( $conditions['get_page_exclude'] ) && is_array( $conditions['get_page_exclude'] ) ) {
                if ( isset( $_GET['page'] ) && in_array( $_GET['page'], $conditions['get_page_exclude'] ) ) {
                    $exclude = true;
                }
            }

            if ( isset( $conditions['screen_id_include'] ) && is_array( $conditions['screen_id_include'] ) ) {
                $screen = get_current_screen();
                if ( ! ( $screen && in_array( $screen->id, $conditions['screen_id_include'] ) ) ) {
                    $exclude = true;
                }
            }

            if ( isset( $conditions['post_type'] ) && is_array( $conditions['post_type'] ) ) {
                $screen = get_current_screen();
                if ( ! ( $screen && $screen->post_type && in_array( $screen->post_type, $conditions['post_type'] ) ) ) {
                    $exclude = true;
                }
            }

            if ( isset( $conditions['lic_status_include'] ) && is_array( $conditions['lic_status_include'] ) ) {
                $plugin_info = AWS_PRO()->license->updater->get_plugin_info();
                if ( ! ( $plugin_info && $plugin_info->license_status && in_array( $plugin_info->license_status, $conditions['lic_status_include'] ) ) ) {
                    $exclude = true;
                }
            }

            if ( isset( $conditions['is_lic_active'] ) && $conditions['is_lic_active'] ) {
                $license_key = AWS_PRO()->license->get_license_key();
                if ( ( $conditions['is_lic_active'] === 'true' && ! $license_key ) || ( $conditions['is_lic_active'] === 'false' && $license_key ) ) {
                    $exclude = true;
                }
            }

            if ( isset( $conditions['class_exists'] ) && $conditions['class_exists'] ) {
                if ( ! class_exists( $conditions['class_exists'] ) ) {
                    $exclude = true;
                }
            }

            if ( isset( $conditions['defined'] ) && $conditions['defined'] ) {
                if ( ! defined( $conditions['defined'] ) ) {
                    $exclude = true;
                }
            }

            return $exclude;

        }

        /*
         * Set text vars for notice text
         */
        private function set_text_vars( $text ) {

            $plugin_info = AWS_PRO()->license->updater->get_plugin_remote_info_option();

            $woo_version = '';
            if ( function_exists( 'WC' ) && WC() ) {
                $woo_version = WC()->version;
            }

            $wp_version = '';
            if ( function_exists( 'get_bloginfo' ) ) {
                $wp_version = get_bloginfo( 'version' );
            }

            $plugin_installed_ver = AWS_PRO_VERSION;

            $latest_ver = '';
            if ( $plugin_info  && property_exists( $plugin_info, 'new_version' ) ) {
                $latest_ver = $plugin_info->new_version;

            }

            $missed_updates = '';
            if ( $plugin_info && property_exists( $plugin_info, 'updates_missed' ) ) {
                $missed_updates = intval( $plugin_info->updates_missed );
            }

            $missed_months = '';
            if ( $missed_updates ) {
                $missed_months = round( $missed_updates * 14 / 30, 0 );
            }

            $theme_name = '';
            if ( function_exists( 'wp_get_theme' ) ) {
                $theme = wp_get_theme();
                if ( $theme ) {
                    $theme_name = $theme->get( 'Name' );
                }
            }

            $possible_plugins_issues = $this->get_possible_plugins_issues_list();

            $text_vars = array(
                '{woo_version}' => $woo_version,
                '{wp_version}' => $wp_version,
                '{plugin_installed_ver}' => $plugin_installed_ver,
                '{latest_ver}' => $latest_ver,
                '{missed_updates}' => $missed_updates,
                '{missed_months}' => $missed_months,
                '{theme_name}' => $theme_name,
                '{possible_plugins_issues}' => $possible_plugins_issues,
            );

            foreach ( $text_vars as $key => $val ) {
                $text = str_replace( $key, $val, $text );
            }

            return $text;

        }

        /*
         * Get list of plugins with possible compatibility issues
         */
        private function get_possible_plugins_issues_list() {

            if ( isset( $data['active_plugins'] ) ) {
                $active_plugins = $data['active_plugins'];
            } else {
                $active_plugins = get_option( 'active_plugins', array() );
                if ( is_multisite() ) {
                    $network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
                    $active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
                }
                $data['active_plugins'] = $active_plugins;
            }

            $text = '';
            $plugins_list = array();

            if ( class_exists('ACF') ) {
                $plugins_list[] = 'Advanced Custom Fields ( ACF )';
            }
            if ( class_exists( 'WCFMmp' ) ) {
                $plugins_list[] = 'WCFM Multivendor Marketplace';
            }
            if ( class_exists( 'YITH_WCWL' ) ) {
                $plugins_list[] = 'YITH WooCommerce Wishlist';
            }
            if ( class_exists( 'WooCommerceWholeSalePrices' ) ) {
                $plugins_list[] = 'Wholesale Prices';
            }
            if ( class_exists( 'UM_Functions' ) ) {
                $plugins_list[] = 'Ultimate Member';
            }
            if ( class_exists( 'PWB_PLUGIN_VERSION' ) ) {
                $plugins_list[] = 'Perfect Brands for WooCommerce';
            }
            if ( defined( 'TINVWL_FVERSION' ) ) {
                $plugins_list[] = 'TI WooCommerce Wishlist';
            }
            if ( class_exists( 'WeDevs_Dokan' ) ) {
                $plugins_list[] = 'Dokan – WooCommerce Multivendor Marketplace';
            }
            if ( defined( 'WCMp_PLUGIN_VERSION' ) || defined( 'MVX_PLUGIN_VERSION' ) ) {
                $plugins_list[] = 'MultiVendorX – WooCommerce Multivendor Marketplace';
            }
            if ( class_exists( 'WC_Memberships' ) ) {
                $plugins_list[] = 'WooCommerce Memberships';
            }
            if ( class_exists( 'WC_Memberships' ) ) {
                $plugins_list[] = 'WooCommerce Memberships';
            }
            if ( in_array( 'wc-vendors/class-wc-vendors.php', $active_plugins ) ) {
                $plugins_list[] = 'WC Vendors';
            }
            if ( in_array( 'gtranslate/gtranslate.php', $active_plugins ) ) {
                $plugins_list[] = 'GTranslate';
            }
            if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
                $plugins_list[] = 'WPML';
            }
            if ( defined( 'WOOF_PLUGIN_NAME' ) ) {
                $plugins_list[] = 'WOOF - WooCommerce Products Filter';
            }
            if ( defined( 'BeRocket_AJAX_filters_version' ) ) {
                $plugins_list[] = 'BeRocket WooCommerce AJAX Products Filter';
            }
            if ( class_exists( 'Alg_WC_EAN' ) ) {
                $plugins_list[] = 'EAN for WooCommerce';
            }

            if ( ! empty( $plugins_list ) ) {
                $plugins_list_text = implode( ', ', $plugins_list );
                $text = '<br><br>Possible compatibility issues with: <b>' . $plugins_list_text . '</b><br>';
            }

            return $text;

        }

        /*
         * Hide admin notices
         */
        public function hide_notices() {

            if ( isset( $_GET['aws_hide_remote_notices'] ) && $_GET['aws_hide_remote_notices'] ) {

                $user_id = $this->get_current_admin_user_id();

                if ( $user_id ) {

                    $notice_id = sanitize_text_field( $_GET['aws_hide_remote_notices'] );
                    $option_current = $this->get_hiden_remote_notices();
                    $option = array();

                    // if notice id already exists - its time will be updated

                    $option[$notice_id] = current_time( 'timestamp' );
                    $option = $option_current && is_array( $option_current ) ? array_merge( $option_current, $option ) : $option;

                    update_user_meta( $user_id, 'aws_pro_hide_remote_notices', $option );

                    // set transient - show remote notices with 6 hours delay
                    set_transient('aws_pro_remote_notices_cooldown', true, 60*60*6 );

                }
            }

        }

        /*
         * Find remote notices data inside the remote response
         */
        public function remote_information( $response ) {

            if ( $response && property_exists( $response, 'notices' ) && is_array( $response->notices ) && ! empty( $response->notices ) ) {
                update_option( 'aws_pro_remote_notices', $response->notices );
            } else {
                delete_option( 'aws_pro_remote_notices' );
            }

            return $response;

        }

        /*
         * Get current admin user ID
         */
        private function get_current_admin_user_id() {
            $user_id = get_current_user_id();
            return $user_id && current_user_can( 'manage_options' ) ? $user_id : null;
        }

        /*
         * Get saved hidden remote notices
         */
        private function get_hiden_remote_notices() {
            $user_id = $this->get_current_admin_user_id();
            if ( ! $user_id ) {
                return array();
            }
            return get_user_meta( $user_id, 'aws_pro_hide_remote_notices', true );
        }

        /*
         * Get saved remote notices
         */
        private function get_remote_notices() {
            return get_option( 'aws_pro_remote_notices' );
        }

    }

endif;

add_action( 'init', 'AWS_Remote_Notices::instance' );