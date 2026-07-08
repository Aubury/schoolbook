<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWS_Admin_Ajax' ) ) :

    /**
     * Class for plugin admin ajax hooks
     */
    class AWS_Admin_Ajax {

        /*
         * Constructor
         */
        public function __construct() {

            add_action( 'wp_ajax_aws-renameForm', array( &$this, 'rename_form' ) );

            add_action( 'wp_ajax_aws-makeMainForm', array( &$this, 'make_main_form' ) );

            add_action( 'wp_ajax_aws-copyForm', array( &$this, 'copy_form' ) );

            add_action( 'wp_ajax_aws-deleteForm', array( &$this, 'delete_form' ) );

            add_action( 'wp_ajax_aws-addForm', array( &$this, 'add_form' ) );

            add_action( 'wp_ajax_aws-hideWelcomeNotice', array( $this, 'hide_welcome_notice' ) );

            add_action( 'wp_ajax_aws-getRuleGroup', array( $this, 'get_rule_group' ) );

            add_action( 'wp_ajax_aws-getSuboptionValues', array( $this, 'get_suboption_values' ) );

            add_action( 'wp_ajax_aws-getSelectOptionValues', array( $this, 'get_select_option_values' ) );

            add_action( 'wp_ajax_aws-termsSelect', array( $this, 'terms_select' ) );

            add_action( 'wp_ajax_aws-indexEnable', array( $this, 'index_enable' ) );

            add_action( 'wp_ajax_aws-indexDisabled', array( $this, 'index_disabled' ) );

        }

        /*
         * Ajax hook for form renaming
         */
        public function rename_form() {

            check_ajax_referer( 'aws_pro_admin_ajax_nonce' );

            $instance_id = sanitize_text_field( $_POST['id'] );
            $form_name   = sanitize_text_field( $_POST['name'] );

            $settings = $this->get_settings();

            $settings[$instance_id]['search_instance'] = $form_name;

            update_option( 'aws_pro_settings', $settings );

            wp_send_json_success( '1' );

        }

        /*
         * Ajax hook for making form main
         */
        public function make_main_form() {

            check_ajax_referer( 'aws_pro_admin_ajax_nonce' );

            $instance_id = sanitize_text_field( $_POST['id'] );
            $enabled = sanitize_text_field( $_POST['enabled'] );

            if ( $enabled === '1' ) {
                delete_option( 'aws_main_instance' );
            } else {
                update_option( 'aws_main_instance', $instance_id, true );
            }

            wp_send_json_success( '1' );

        }

        /*
         * Ajax hook for form coping
         */
        public function copy_form() {

            check_ajax_referer( 'aws_pro_admin_ajax_nonce' );

            $instance_id = sanitize_text_field( $_POST['id'] );

            $instances_number = get_option( 'aws_instances' );
            $instances_number++;

            $settings = $this->get_settings();
            $instance_settings = $settings[$instance_id];

            $instance_settings['search_instance'] = $instance_settings['search_instance'] . ' (copy)';

            $settings[$instances_number] = $instance_settings;

            update_option( 'aws_instances', $instances_number, 'no' );
            update_option( 'aws_pro_settings', $settings );

            /**
             * Fires after search form instance was create/copy/delete
             *
             * @since 1.33
             *
             * @param array $settings Array of plugin settings
             * @param string $ Action type
             * @param string $instance_id Form instance id
             */
            do_action( 'aws_form_changed', $settings, 'copy_form', $instance_id );

            wp_send_json_success( '1' );

        }

        /*
         * Ajax hook for form deleting
         */
        public function delete_form() {

            check_ajax_referer( 'aws_pro_admin_ajax_nonce' );

            $instance_id = sanitize_text_field( $_POST['id'] );

            $settings = $this->get_settings();

            unset( $settings[$instance_id] );

            update_option( 'aws_pro_settings', $settings );

            /**
             * Fires after search form instance was create/copy/delete
             *
             * @since 1.33
             *
             * @param array $settings Array of plugin settings
             * @param string $ Action type
             * @param string $instance_id Form instance id
             */
            do_action( 'aws_form_changed', $settings, 'delete_form', $instance_id );

            do_action( 'aws_cache_clear', $instance_id );

            wp_send_json_success( '1' );

        }

        /*
         * Ajax hook for form adding
         */
        public function add_form() {

            check_ajax_referer( 'aws_pro_admin_ajax_nonce' );

            $instances_number = get_option( 'aws_instances' );
            $instances_number++;

            $settings = $this->get_settings();

            $default_settings = AWS_Admin_Options::get_default_settings();

            $settings[$instances_number] = $default_settings;

            update_option( 'aws_instances', $instances_number, 'no' );
            update_option( 'aws_pro_settings', $settings );

            /**
             * Fires after search form instance was create/copy/delete
             *
             * @since 1.33
             *
             * @param array $settings Array of plugin settings
             * @param string $ Action type
             * @param string $instance_id Form instance id
             */
            do_action( 'aws_form_changed', $settings, 'add_form', $instances_number );

            wp_send_json_success( '1' );

        }

        /*
         * Hide plugin welcome notice
         */
        public function hide_welcome_notice() {

            check_ajax_referer( 'aws_pro_admin_ajax_nonce' );

            update_option( 'aws_hide_welcome_notice', 'true', false );

            wp_send_json_success( '1' );

        }

        /*
         * Ajax hook for rule groups
         */
        public function get_rule_group() {

            check_ajax_referer( 'aws_pro_admin_ajax_nonce' );

            $input_name = sanitize_text_field( $_POST['inputName'] );
            $name = sanitize_text_field( $_POST['name'] );
            $section = sanitize_text_field( $_POST['section'] );
            $group_id = sanitize_text_field( $_POST['groupID'] );
            $rule_id = sanitize_text_field( $_POST['ruleID'] );

            $rules = AWS_Admin_Options::include_filters();
            $html = array();

            foreach ( $rules as $rule_section => $section_rules ) {
                foreach ( $section_rules as $rule ) {
                    if ( $rule['id'] === $name ) {

                        $rule_obj = new AWS_Admin_Filters( $rule, $input_name, $section, $group_id, $rule_id );

                        $html['aoperators'] = $rule_obj->get_field( 'operator' );

                        if ( isset( $rule['suboption'] ) ) {
                            $html['asuboptions'] = $rule_obj->get_field( 'suboption' );
                        }

                        $html['avalues'] = $rule_obj->get_field( 'value' );

                        break;

                    }
                }
            }

            wp_send_json_success( $html );

        }

        /*
         * Ajax hook for suboption values
         */
        public function get_suboption_values() {

            check_ajax_referer( 'aws_pro_admin_ajax_nonce' );

            $param = sanitize_text_field( $_POST['param'] );
            $section = sanitize_text_field( $_POST['section'] );
            $suboption = sanitize_text_field( $_POST['suboption'] );
            $group_id = sanitize_text_field( $_POST['groupID'] );
            $rule_id = sanitize_text_field( $_POST['ruleID'] );
            $input_name = sanitize_text_field( $_POST['inputName'] );

            $rules = AWS_Admin_Options::include_filters();
            $html = array();

            foreach ( $rules as $rule_section => $section_rules ) {
                foreach ( $section_rules as $rule ) {
                    if ( $rule['id'] === $param ) {

                        $rule['choices']['params'] = array( $suboption );

                        $rule_obj = new AWS_Admin_Filters( $rule, $input_name, $section, $group_id, $rule_id );

                        $html = $rule_obj->get_field( 'value' );

                        break;

                    }
                }
            }

            wp_send_json_success( $html );

        }

        /*
         * Ajax hook to get values by calling callback function
         */
        public function get_select_option_values() {

            check_ajax_referer( 'aws_pro_admin_ajax_nonce' );

            if ( ! current_user_can( AWS_Admin_Helpers::user_admin_capability() ) ) {
                wp_send_json_error( 'Insufficient permissions.' );
            }

            $callback = sanitize_text_field( $_POST['callback'] );

            // make sure that the callback is legit
            $legit = false;
            $rules = AWS_Admin_Options::include_filters();
            foreach ( $rules as $rule_section => $section_rules ) {
                foreach ( $section_rules as $rule ) {
                    if ( ( isset( $rule['choices'] ) && isset( $rule['choices']['callback'] ) && $rule['choices']['callback'] === $callback ) ||
                        ( isset( $rule['suboption'] ) && isset( $rule['suboption']['callback'] ) && $rule['suboption']['callback'] === $callback )
                    ) {
                        $legit = true;
                        break 2;
                    }
                }
            }

            if ( ! $legit ) {
                wp_send_json_error( 'Invalid callback.' );
            }

            $callback_params = isset( $_POST['param'] ) ? (array) $_POST['param'] : array();
            $callback_params = array_map( 'sanitize_text_field', $callback_params );

            $term = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
            $term = (string) wc_clean( wp_unslash( $term ) );

            if ( $term ) {
                $callback_params = array( $term );
            }

            $values_arr = call_user_func_array( $callback, $callback_params );

            wp_send_json( array( 'results' => $values_arr ) );

        }

        /*
         * Ajax hook for terms select
         */
        public function terms_select() {

            check_ajax_referer( 'aws_pro_admin_ajax_nonce' );

            $index_options = AWS_Helpers::get_index_options();

            $instance_id = isset( $_REQUEST['instanceId'] ) ? sanitize_text_field( $_REQUEST['instanceId'] ) : 0;
            $option_id = isset( $_REQUEST['optionId'] ) ? sanitize_text_field( $_REQUEST['optionId'] ) : '';

            $name = sanitize_text_field( $_REQUEST['term'] );

            $results = array();
            $values = array();
            $index_values = array();

            switch ( $name ) {

                case 'attr':
                    $values = AWS_Helpers::get_attributes();
                    $index_values = $index_options['index']['attr_sources'];
                    break;

                case 'tax':
                    $values = AWS_Helpers::get_taxonomies();
                    $index_values = $index_options['index']['tax_sources'];
                    break;

                case 'meta':
                    $values = AWS_Helpers::get_custom_fields();
                    $index_values = $index_options['index']['meta_sources'];
                    break;

                case 'user':
                    $values = AWS_Helpers::get_user_roles();
                    break;

            }

            if ( ! empty( $values ) ) {
                foreach ( $values as $val => $label ) {

                    if ( $option_id === 'search_in' ) {
                        $is_indexed = empty( $index_values ) || ( isset( $index_values[$val] ) && ! $index_values[$val] ) || ! isset( $index_values[$val] ) ? false : true;
                    } else {
                        $is_indexed = true;
                    }

                    $results[] = array(
                        'id' => $val,
                        'text' => $label,
                        'index' => $is_indexed,
                    );

                }
            }

            wp_send_json( array( 'results' => $results ) );

        }

        /*
         * Enable needed index fields
         */
        public function index_enable() {

            check_ajax_referer( 'aws_pro_admin_ajax_nonce' );

            $field = isset( $_POST['field'] ) ? sanitize_text_field( $_POST['field'] ) : 0;
            $sub_field = isset( $_POST['subField'] ) ? sanitize_text_field( $_POST['subField'] ) : 0;

            if ( $field ) {

                $common_settings = AWS_PRO()->get_common_settings();

                if ( $common_settings && isset( $common_settings['index_sources'][$field] ) ) {
                    $common_settings['index_sources'][$field]['value'] = '1';

                    if ( $sub_field ) {
                        $common_settings['index_sources'][$field]['fields'][$sub_field]['value'] = '1';
                    }

                    update_option( 'aws_pro_common_opts', $common_settings );
                }

            }

            wp_send_json_success( '1' );

        }

        /*
         * Disabled search fields on index disable
         */
        public function index_disabled() {

            check_ajax_referer( 'aws_pro_admin_ajax_nonce' );

            $field = isset( $_POST['field'] ) ? sanitize_text_field( $_POST['field'] ) : 0;
            $sub_field = isset( $_POST['subField'] ) ? sanitize_text_field( $_POST['subField'] ) : 0;

            if ( $field ) {

                $settings = $this->get_settings();

                if ( $settings ) {

                    $update = false;

                    foreach ($settings as $search_instance_num => $search_instance_settings) {
                        if ( $search_instance_settings && isset( $search_instance_settings['search_in'][$field] ) ) {

                            if ( $settings[$search_instance_num]['search_in'][$field]['value'] === '1' ) {
                                $settings[$search_instance_num]['search_in'][$field]['value'] = '0';
                                $update = true;
                            }

                            if ( $sub_field && isset( $search_instance_settings['search_in'][$field]['fields'] ) && isset( $search_instance_settings['search_in'][$field]['fields'][$sub_field] ) ) {
                                $update = true;
                                unset( $settings[$search_instance_num]['search_in'][$field]['fields'][$sub_field] );
                            }

                        }
                    }

                    if ( $update ) {
                        update_option( 'aws_pro_settings', $settings );
                    }

                }

            }

            wp_send_json_success( '1' );

        }

        /*
         * Get plugin settings
         */
        private function get_settings() {
            $plugin_options = AWS_PRO()->get_settings();
            return $plugin_options;
        }

    }

endif;


new AWS_Admin_Ajax();