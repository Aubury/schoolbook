<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! class_exists( 'AWS_Admin_Helpers' ) ) :

    /**
     * Class for plugin help methods
     */
    class AWS_Admin_Helpers {

        /**
         * Get array of allowed tags for wp_kses function
         * @param array $allowed_tags Tags that is allowed to display
         * @return array $tags
         */
        static public function get_kses( $allowed_tags = array() ) {

            $tags = array(
                'a' => array(
                    'href' => array(),
                    'title' => array()
                ),
                'br' => array(),
                'em' => array(),
                'strong' => array(),
                'b' => array(),
                'code' => array(),
                'blockquote' => array(
                    'cite' => array(),
                ),
                'p' => array(),
                'i' => array(),
                'h1' => array(),
                'h2' => array(),
                'h3' => array(),
                'h4' => array(),
                'h5' => array(),
                'h6' => array(),
                'img' => array(
                    'alt' => array(),
                    'src' => array()
                )
            );

            if ( is_array( $allowed_tags ) && ! empty( $allowed_tags ) ) {
                foreach ( $tags as $tag => $tag_arr ) {
                    if ( array_search( $tag, $allowed_tags ) === false ) {
                        unset( $tags[$tag] );
                    }
                }

            }

            return $tags;

        }

        /**
         * Get array of default allowed tags for textarea
         * @return array $tags
         */
        static public function kses_textarea_allowed_tags() {
            return array( 'a', 'br', 'em', 'strong', 'b', 'code', 'blockquote', 'p', 'i' );
        }

        /*
         * Check for incorrect filtering rules and return them
         * @return string
         */
        static public function check_for_incorrect_filtering_rules( $filters ) {

            $incorrect_rules_string = '';
            $check_rules = array( 'product', 'current_user', 'current_user_role', 'current_user_device', 'current_page', 'current_page_template', 'current_page_type', 'current_page_archives' );

            if ( $filters && ! empty( $filters ) ) {
                foreach ( $filters as $cond_group ) {

                    $maybe_wrong_rules = array();

                    foreach ( $cond_group as $cond_rule ) {
                        $rule_name = $cond_rule['param'];
                        if ( array_search( $rule_name, $check_rules ) !== false && $cond_rule['operator'] === 'equal' ) {
                            $maybe_wrong_rules[$rule_name][] = $cond_rule;
                        }
                        if ( isset( $maybe_wrong_rules[$rule_name] ) && count( $maybe_wrong_rules[$rule_name] ) > 1 ) {
                            foreach ( $maybe_wrong_rules[$rule_name] as $rule ) {
                                $rule_value = isset( $rule['value']  ) ? $rule['value'] : '';
                                $incorrect_rules_string .= $rule['param'] . ' -> ' . 'equal to' . ' -> ' . $rule_value .  '<br>';
                            }
                            break;
                        }
                    }

                    if ( $incorrect_rules_string ) {
                        break;
                    }

                }
            }

            return $incorrect_rules_string;

        }

        /*
         * Check for incorrect filtering rules and return them
         * @return string
         */
        static public function user_admin_capability() {

            /**
             * What capability current user must have to view settings page
             * @since 2.99
             * @param string $capability Minimal capability required to view plugin settings page
             */
            return apply_filters( 'aws_admin_capability', 'manage_options' );

        }

        /*
         * Check if current array of fields are all disabled
         * @return bool
         */
        static public function is_all_fields_disabled( $arr ) {

            $disabled = true;

            if ( ! empty( $arr ) ) {
                foreach ( $arr as $field ) {
                    if ( $field ) {
                        $disabled = false;
                        break;
                    }
                }
            }

            return $disabled;

        }

        /*
         * Order one array according to order from the second array
         * @return array
         */
        static public function order_by_array( $choices, $post_array ) {

            if ( $post_array && is_array( $post_array ) ) {

                $order   = $post_array;
                $ordered = array();

                foreach ( $order as $key => $val ) {
                    if ( isset($choices[$key]) ) {
                        $ordered[$key] = $choices[$key];
                    }
                }

                // Add back any missing keys (if $_POST didn't include them)
                foreach ( $choices as $key => $val ) {
                    if ( ! isset($ordered[$key]) ) {
                        $ordered[$key] = $val;
                    }
                }

                $choices = $ordered;

            }

            return $choices;

        }

        /*
         * Get value from multidimension array
         * @return bool|array|string
         */
        static public function array_get( array $array, array $path ) {
            foreach ( $path as $step ) {
                if ( ! $step ) {
                    break;
                }
                if ( ! isset( $array[$step] ) ) {
                    return false;
                }
                $array = $array[$step];
            }
            return $array;
        }

        /*
         * Get new option value from $_POST array
         * @return array|string
         */
        static public function get_new_option_value( $field, $key = false, $subkey = false ) {

            $post_value = AWS_Admin_Helpers::array_get( $_POST, array( $field['id'], $key, $subkey ) );

            switch ( $field['type'] ) {
                case 'toggler':
                    $new_value = $post_value ? 'true' : 'false';
                    break;
                case 'checkbox':
                    $new_value = $post_value ? '1' : '0';
                    break;
                case 'terms_select':
                    $new_value = $post_value ? $post_value : array();
                    break;
                default:
                    $new_value = $post_value ? $post_value : '';
            }

            return $new_value;

        }

        /*
         * Prepare options values before saving to db
         * @return array|string
         */
        static public function prepare_settings( $field, $value = '' ) {

            if ( isset( $field['type'] ) ) {

                switch ( $field['type'] ) {

                    case 'textarea':

                        if ( isset( $field['allow_tags'] ) ) {
                            $value = (string) addslashes( wp_kses( stripslashes( html_entity_decode( $value ) ), AWS_Admin_Helpers::get_kses( $field['allow_tags'] ) ) );
                        } else {
                            if ( function_exists('sanitize_textarea_field') ) {
                                $value = (string) sanitize_textarea_field( $value );
                            } else {
                                $value = (string) str_replace( "<\n", "&lt;\n", wp_strip_all_tags( $value ) );
                            }
                        }

                        break;

                    case 'terms_select':
                    case 'filter_rules':
                        $value = (array) $value;
                        break;

                    default:
                        $value = sanitize_text_field( $value );

                }

            }

            return $value;

        }

        /*
         * Get current option value and all its sub values ( if exists )
         * @param $field Array of option parameters
         * @param $default Return option default values or new values
         * @return array|string|num
         */
        static public function get_current_option( $field, $default = false ) {
            
            $current_option = array();

            if ( isset( $field['type'] ) && $field['type'] === 'dynamic_table' && isset( $_POST[$field['id']] ) && is_array( $_POST[$field['id']] ) ) {
                $opts = isset( $field['opts'] ) ? $field['opts'] : array();
                foreach ( $_POST[$field['id']] as $dynamic_table_id => $dynamic_table_opts ) {
                    foreach ( $opts as $current_field ) {
                        $opt_id = $current_field['id'];
                        $current_field['id'] = $field['id'];
                        $new_value = $default ? $current_field['value'] : AWS_Admin_Helpers::get_new_option_value( $current_field, $dynamic_table_id, $opt_id );
                        $current_option[$dynamic_table_id][$opt_id] = AWS_Admin_Helpers::prepare_settings( $current_field, $new_value );
                    }
                }
                return $current_option;
            }

            if ( isset( $field['choices'] ) && $field['value'] && is_array( $field['value'] ) ) {

                $keep_order = ( isset( $field['type'] ) && $field['type'] = 'sortable' ) || ( isset( $field['sortable'] ) && $field['sortable'] );
                $choices = $keep_order && isset( $_POST[$field['id']] ) ? AWS_Admin_Helpers::order_by_array( $field['choices'], $_POST[$field['id']] ) : $field['choices'] ;

                // get options values inside 'choices' array
                foreach ( $choices as $key => $val ) {

                    if ( $default && ! isset( $field['value'][$key] ) ) {
                        continue;
                    }

                    if ( strpos( $key, ':disabled' ) !== false ) {
                        continue;
                    }

                    if ( isset( $val['suboptions'] ) && is_array( $val['suboptions'] ) ) {

                        $new_value = $default ? $field['value'][$key] : AWS_Admin_Helpers::get_new_option_value( $field, $key, 'value' );
                        $current_option[$key]['value'] = sanitize_text_field( $new_value );

                        // get options values inside 'suboptions' array
                        foreach ( $val['suboptions'] as $suboption_key => $suboption_val ) {
                            $new_value = $default ? $suboption_val['value'] : AWS_Admin_Helpers::get_new_option_value( $field, $key, $suboption_val['id'] );
                            $current_option[$key][$suboption_val['id']] = AWS_Admin_Helpers::prepare_settings( $suboption_val, $new_value );
                        }

                    } else {

                        $new_value = $default ? $field['value'][$key] : AWS_Admin_Helpers::get_new_option_value( $field, $key );
                        $current_option[$key] = AWS_Admin_Helpers::prepare_settings( $field, $new_value );

                    }

                }

            } else {

                // get all other single options
                $new_value = $default ? $field['value'] : AWS_Admin_Helpers::get_new_option_value( $field );
                $current_option = AWS_Admin_Helpers::prepare_settings( $field, $new_value );

            }

            return $current_option;

        }

        /*
         * Get all available products taxonomies and attributes
         * @return array
         */
        static public function get_all_product_taxonomies() {

            $options = array();

            $taxonomy_objects = get_object_taxonomies( 'product', 'objects' );

            foreach( $taxonomy_objects as $taxonomy_object ) {
                if ( in_array( $taxonomy_object->name, array( 'product_type', 'product_visibility', 'product_shipping_class' ) ) ) {
                    continue;
                }

                $tax_slug = $taxonomy_object->name;
                $tax_name = $taxonomy_object->label;

                $options[$tax_slug] = $tax_name;

            }

            return $options;

        }

    }

endif;