<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWS_Admin_Fields' ) ) :

    /**
     * Class for plugin admin ajax hooks
     */
    class AWS_Admin_Fields {

        /**
         * @var AWS_Admin_Fields The array of options that is need to be generated
         */
        private $options_array;

        /**
         * @var AWS_Admin_Fields Current plugin instance options
         */
        private $plugin_options;

        private $tab_name;

        private $is_disabled;

        private $active_section = false;

        private $current_opt_value;

        private $depends_on = array();

        /*
         * Constructor
         */
        public function __construct( $tab_name, $plugin_options ) {

            $options = AWS_Admin_Options::options_array( $tab_name );

            $this->options_array = $options[$tab_name];
            $this->plugin_options = $plugin_options;
            $this->tab_name = $tab_name;

            $this->generate_dependencies_array();

            $this->generate_fields();

        }

        /*
         * Generate options fields and print
         */
        private function generate_fields() {

            if ( empty( $this->options_array ) ) {
                return;
            }

            echo '<div data-tab="'. esc_attr( $this->tab_name  ) .'">';

            $this->generate_sections_tabs();

            echo '<table class="form-table">';

                echo '<tbody>';

                foreach ( $this->options_array as $k => $field ) {

                    if ( isset( $field['depends'] ) && ! $field['depends'] ) {
                        continue;
                    }

                    // Generate fields
                    echo $this->generate_field( $field );

                }

                echo '</tbody>';

            echo '</table>';

            echo '<p class="submit"><input name="Submit" type="submit" class="button-primary" value="' . esc_attr__( 'Save Changes', 'advanced-woo-search' ) . '" /></p>';

            echo '</div>';

        }

        /*
         * Generate specific field
         * @return string
         */
        private function generate_field( $field ) {

            $plugin_options = $this->plugin_options;

            $id = isset( $field['id'] ) && $field['id'] ? $field['id'] : '';
            $parent_id = isset( $field['parent_id'] ) && $field['parent_id'] ? $field['parent_id'] : $id;
            $is_child_opt = isset( $field['is_child_opt'] ) && $field['is_child_opt'] ? $field['is_child_opt'] : false;

            $this->current_opt_value = '';
            if ( isset( $field['current_opt_value'] ) && $field['current_opt_value'] ) {
                $this->current_opt_value = $field['current_opt_value'];
            } elseif ( $id && isset( $plugin_options[$id] ) ) {
                $this->current_opt_value = $plugin_options[$id];
            } elseif ( isset( $field['value'] ) ) {
                $this->current_opt_value = $field['value'];
            }

            $section = '';
            $is_hidden = '';

            if ( ! $is_child_opt ) {
                $section = isset( $field['section'] ) && $field['section'] ? ' data-section="'. esc_attr( $field['section'] ) .'"' : ' data-section="main"';
                $is_hidden = isset( $field['section'] ) && $this->active_section && $this->active_section !== $field['section'] ? ' style="display:none;"' : '';
            }

            // is field is hidden because of dependencies
            $hidden_by_dependency = $this->is_field_depends( $field ) || $field['type'] === 'hidden' ? ' data-aws-hidden="true"' : '';

            $this->is_disabled = isset( $field['disabled'] ) && $field['disabled'] ? 'disabled' : '';

            $depends_on = '';
            if ( $parent_id && isset( $this->depends_on[$parent_id] ) ) {
                $depends_on = ' data-dependencies="'. esc_attr( json_encode( $this->depends_on[$parent_id] ) ) .'"';
            }

            $heading_before = '';
            $heading_after = '';

            if ( $field['type'] === 'heading' ) {
                $heading_tag = isset( $field['heading_type'] ) && $field['heading_type'] === 'text' ? 'span' : 'h3';
                $idval = $id ? 'id="' . $id . '"' : '';
                $heading_before = '<'. $heading_tag .' '. $idval .' class="aws-heading">';
                $heading_after = '</'. $heading_tag .'>';
            }

            $td_colspan = isset( $field['colspan'] ) && $field['colspan'] ? ' colspan="'.esc_attr( $field['colspan'] ).'"' : '';

            $html = '';

            $html .= '<tr'. $section .' data-option="'. esc_attr( $parent_id ) .'"' . $depends_on . $is_hidden . $hidden_by_dependency . '>';

                $html .= '<th scope="row">';
                    $html .= $heading_before;
                    $html .= '<span class="aws-row-name">';
                        $html .= wp_kses_post( $field['name'] );
                    $html .= '</span>';
                    if ( isset( $field['tip'] ) && $field['tip'] ) {
                        $html .= '<span class="aws-help-tip aws-tip" data-tip-text="'. esc_attr( $field['tip'] ) .'" data-tip="'. esc_attr( $field['tip'] ) .'"></span>';
                    }
                    $html .= $heading_after;
                $html .= '</th>';

                $html .= '<td'.$td_colspan.'>';

                    // html for specific field type
                    $html .= $this->call_field( $field );

                    if ( isset( $field['desc'] ) && $field['desc'] ) {
                        $html .= '<p class="description">'. wp_kses_post( $field['desc'] ) .'</p>';
                    }

                $html .= '</td>';

            $html .= '</tr>';

            return $html;

        }

        /*
         * Call field type method
         * @param field array Field params
         * @return string
         */
        private function call_field( $field ) {
            $field_type = str_replace( '-', '_', $field['type'] );
            $method = 'get_field_' . $field_type;
            if ( method_exists( $this, $method ) ) {
                return call_user_func_array( array( $this, $method ), array( $field ) );
            } else {
                return '';
            }
        }

        /*
         * Text field type html markup
         * @return string
         */
        private function get_field_text( $field ) {

            $html = '';

            $html .= '<input ' . $this->is_disabled . ' type="text" name="'. esc_attr( $field['id'] ) .'" class="regular-text" value="'. esc_attr( stripslashes( $this->current_opt_value ) ) .'">';

            return $html;

        }

        /*
         * Hidden field type html markup
         * @return string
         */
        private function get_field_hidden( $field ) {

            $html = '';

            $html .= '<input ' . $this->is_disabled . ' type="hidden" name="'. esc_attr( $field['id'] ) .'" class="regular-text" value="'. esc_attr( stripslashes( $this->current_opt_value ) ) .'">';

            return $html;

        }

        /*
         * Number field type html markup
         * @return string
         */
        private function get_field_number( $field ) {

            $params = '';
            $params .= isset($field['step']) ? ' step="' . $field['step'] . '"' : '';
            $params .= isset($field['min']) ? ' min="' . $field['min'] . '"' : '';
            $params .= isset($field['max']) ? ' max="' . $field['max'] . '"' : '';

            $html = '';

            $html .= '<input '. $this->is_disabled .' type="number" '. $params .' name="'. esc_attr( $field['id'] ) .'" class="regular-text" value="'. esc_attr( stripslashes( $this->current_opt_value ) ) .'">';

            return $html;

        }

        /*
         * Textarea field type html markup
         * @return string
         */
        private function get_field_textarea( $field ) {

            $textarea_cols = isset( $field['cols'] ) ? $field['cols'] : "55";
            $textarea_rows = isset( $field['rows'] ) ? $field['rows'] : "3";
            $textarea_output = isset( $field['allow_tags'] ) ? wp_kses( $this->current_opt_value, AWS_Admin_Helpers::get_kses( $field['allow_tags'] ) ) : esc_html( stripslashes( $this->current_opt_value ) );

            $html = '';

            $html .= '<textarea '. $this->is_disabled .' id="'. esc_attr( $field['id'] ) .'" name="'. esc_attr( $field['id'] ) .'" cols="'. $textarea_cols .'" rows="'. $textarea_rows .'">'. $textarea_output .'</textarea>';

            return $html;

        }

        /*
         * Image field type html markup
         * @return string
         */
        private function get_field_image( $field ) {

            $full_class = $this->current_opt_value ? ' full' : '';

            $html = '';

            $html .= '<img class="image-preview'. $full_class .'" src="'. esc_url( stripslashes( $this->current_opt_value ) ) .'"  />';
            $html .= '<input type="hidden" size="40" name="'. esc_attr( $field['id'] ) .'" class="image-hidden-input" value="'. esc_attr( stripslashes( $this->current_opt_value ) ) .'" />';
            $html .= '<input '. $this->is_disabled .' class="button image-upload-btn" type="button" value="Upload Image" data-size="'. esc_attr( $field['size'] ) .'" />';

            $html .= '<input '. $this->is_disabled .' class="button image-remove-btn" type="button" value="Remove Image" />';

            return $html;

        }

        /*
         * Checkbox field type html markup
         * @return string
         */
        private function get_field_checkbox( $field ) {

            $checkbox_options = $this->current_opt_value;

            $html = '';

            foreach ( $field['choices'] as $val => $label ) {

                $html .= '<div class="aws-input-checkbox">';
                    $html .= '<input '. $this->is_disabled .' type="checkbox" name="'. esc_attr( $field['id'] . '[' . $val . ']' ) .'" id="'. esc_attr( $field['id'] . '_' . $val ) .'" value="1" '. checked( $checkbox_options[$val], '1', false ) .'> <label for="'. esc_attr( $field['id'] . '_' . $val ) .'">'. $label .'</label>';
                $html .= '</div>';

            }

            return $html;

        }

        /*
         * Radio field type html markup
         * @return string
         */
        private function get_field_radio( $field ) {

            $html = '';

            foreach ( $field['choices'] as $val => $label ) {

                $html .= '<div class="aws-input-radio">';
                    $html .= '<input '. $this->is_disabled .' class="radio" type="radio" name="'. esc_attr( $field['id'] ) .'" id="' . esc_attr( $field['id'] . $val ) . '" value="'. esc_attr( $val ) .'" '. checked( $this->current_opt_value, $val, false ) .'> <label for="'. esc_attr( $field['id'] . $val ) .'">'. $label .'</label>';
                $html .= '</div>';

            }

            return $html;

        }

        /*
         * Toggler field type html markup
         * @return string
         */
        private function get_field_toggler( $field ) {

            $active = $this->current_opt_value == 'true' ? ' checked="checked"' : '';

            $html = '';

            $html .= '<label class="aws-toggle-label aws-toggler-field">';
                $html .= '<input '. $this->is_disabled .' class="aws-toggler" type="checkbox" name="'. esc_attr( $field['id'] ) .'" '. $active .'>';
            $html .= '</label>';

            return $html;

        }

        /*
         * Radio image field type html markup
         * @return string
         */
        private function get_field_radio_image( $field ) {

            $direction = isset( $field['direction'] ) && $field['direction'] === 'horizontal' ? 'flex-direction: row;' : '';
            $style = isset( $field['styles'] ) ? ' style="' . $field['styles'] . ';'.$direction.'"' : ' style="'.$direction.'"';

            $html = '';

            $html .= '<ul class="img-select"'. $style .'>';

                foreach ( $field['choices'] as $val => $params ) {

                    $img = is_array( $params) && isset( $params['img'] ) ? $params['img'] : $params;
                    $desc = is_array( $params) && isset( $params['desc'] ) ? $params['desc'] : '';

                    $html .= '<li class="option">';

                        $html .= '<input '. $this->is_disabled .' class="radio" type="radio" name="'. esc_attr( $field['id'] ) .'" value="'. esc_attr( $val ) .'" '. checked( $this->current_opt_value, $val, false ) .'>';

                        $html .= '<img src="'.  esc_url( AWS_PRO_URL . 'assets/img/' . $img ) .'">';

                        if ( $desc ) {
                            $html .= '<span class="caption">'. $desc .'</span>';
                        }

                    $html .= '</li>';

                }

            $html .= '</ul>';

            return $html;

        }

        /*
         * Select field type html markup
         * @return string
         */
        private function get_field_select( $field ) {

            $init_select2 = isset( $field['choices'] ) && is_array( $field['choices'] ) && count( $field['choices'] ) > 20 ? ' class="aws-select2-main"' : '';

            $html = '';

            $html .= '<select'. $init_select2 .' '. $this->is_disabled .' name="'. $field['id'] .'">';

                foreach ( $field['choices'] as $val => $label ) {
                    $html .= '<option value="'. esc_attr( $val ) .'" '. selected( $this->current_opt_value, $val, false ) .'>'. esc_html( $label ) .'</option>';
                }

            $html .= '</select>';

            return $html;

        }

        /*
         * Select ajax field type html markup
         * @return string
         */
        private function get_field_select_ajax( $field ) {

            $action = $field['ajax']['action'];
            $ajax_callback = $field['ajax']['callback'];
            $value_callback = isset( $field['ajax']['value_callback'] ) ? $field['ajax']['value_callback'] : '';
            $min_ajax_input = isset( $field['ajax']['input'] ) ? intval( $field['ajax']['input'] ) : 0;
            $placeholder = isset( $field['ajax']['placeholder'] ) ? $field['ajax']['placeholder'] : '';

            $displayed_val = $this->current_opt_value;
            if ( $this->current_opt_value && $value_callback ) {
                $displayed_val = call_user_func_array( $value_callback, array( $this->current_opt_value ) );
            }

            $html = '';

            $html .= '<select '. $this->is_disabled .' data-ajax="'. esc_attr( $action ) .'" data-ajax-callback="'. esc_attr( $ajax_callback ) .'" data-input="'. esc_attr( $min_ajax_input ) .'" data-placeholder="'. esc_attr( $placeholder ) .'" name="' . esc_attr( $field['id'] ) . '" class="aws-select2-ajax"><option value="' . esc_attr( $this->current_opt_value ) . '">' . esc_attr( $displayed_val ) . '</option></select>';

            return $html;

        }

        /*
         * Sortable field type html markup
         * @return string
         */
        private function get_field_sortable( $field ) {

            $labels = $field['choices'];

            if ( is_array( $this->current_opt_value ) ) {
                $sortable_items = $this->current_opt_value;
            } else {
                $sortable_items = $field['value'];
            }

            $disabled_class = '';
            if ( $this->is_disabled ) {
                $disabled_class = ' aws-disabled';
            }

            $html = '';

            $html .= '<script>
                jQuery(function($){
                    const ulSel = "#'. $field['id'] .'sort";
                    $(ulSel).sortable({
                        axis: "y",
                        items: ".aws-sotable-item",
                    }).disableSelection();
                });
            </script>';


            $html .= '<div class="sortable-container'. $disabled_class .'">';

                $html .= '<ul id="'. esc_attr( $field['id'] ) .'sort" class="aws-sotable">';

                foreach ( $sortable_items as $button_id => $button_value ) {

                    $active = intval( $button_value ) === 1 ? ' checked="checked"' : '';
                    $button_name = isset( $labels[ $button_id ] ) ? $labels[ $button_id ] : 'Def';

                    $html .= '<li id="' . esc_attr( $button_id ) . '" class="aws-sotable-item">';

                        $html .= '<span class="aws-sortable-check">';

                            $html .= '<label class="aws-toggle-label">';
                                $html .= '<input type="hidden" name="'. $field['id'] .'['.$button_id.']" value="0">';
                                $html .= '<input class="aws-toggler" type="checkbox" name="'. $field['id'] .'['.$button_id.']" '. $active .' value="1">';
                            $html .= '</label>';

                            $html .= '<span class="aws-sortable-name">';
                                $html .= $button_name;
                            $html .= '</span>';

                        $html .= '</span>';

                    $html .= '</li>';

                }

                $html .= '</ul>';

            $html .= '</div>';

            return $html;

        }

        /*
         * Terms select field type html markup
         * @return string
         */
        private function get_field_terms_select( $field ) {

            $table_options = $this->current_opt_value;
            $parent_id = isset( $field['parent_id'] ) && strpos( $field['parent_id'], '[' ) !== false ? preg_replace('/\[.*?\]/', '', $field['parent_id'] ) : $field['id'];

            $show_weight_column = isset( $field['columns'] ) && array_search( 'weight', $field['columns'] ) !== false;

            $html = '';

            $html .= '<script id="awsTermsTableItemTemplate" type="text/html">';
                $html .= '<div class="aws-terms-table-item" data-term="%val">';
                    $html .= '<input type="hidden" value="1" name="'. esc_attr( $field['id'] ) .'[%val][value]">';
                    $html .= '<div class="name">';
                        $html .= '%label';
                    $html .= '</div>';

                    if ( $show_weight_column ) {
                        $html .= '<div class="weight">';
                            $html .= '<input class="aws-term-weight" type="number" value="" name="'. esc_attr( $field['id'] ) .'[%val][weight]">';
                        $html .= '</div>';
                    }

                    $html .= '<div class="delete">';
                        $html .= '<a href="#" class="button delete" data-delete></a>';
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</script>';

            $html .= '<div class="aws-group aws-terms-sources-group">';
                $html .= '<select data-field="'. esc_attr( $field['field_id'] ) .'" data-option="'. esc_attr( $parent_id ) .'" data-ajax="'. esc_attr( $field['field_id'] ) .'" class="aws-terms-sources-select aws-select2 aws-group-option" name="'. $field['id'] .'"></select>';
                $html .= '<input class="aws-group-button button" type="button" value="'. esc_attr( __( "Add", "advanced-woo-search" ) ) .'" data-add />';
            $html .= '</div>';


            $html .= '<div class="aws-terms-table">';

                $html .= '<div class="aws-terms-table-item aws-terms-table-head">';

                    $html .= '<div class="name">';
                        $html .= esc_html( __( "Active field", "advanced-woo-search" ) );
                    $html .= '</div>';

                    if ( $show_weight_column ) {
                        $html .= '<div class="weight">';
                            $html .= esc_html( __( "Weight", "advanced-woo-search" ) ) . '<span class="aws-help-tip aws-tip" data-tip="'. esc_attr( __( "Leave empty to inherit value from the parent source.", "advanced-woo-search" ) ) .'"></span>';
                        $html .= '</div>';
                    }

                    $html .= '<div class="delete">';
                    $html .= '</div>';

                $html .= '</div>';

                if ( is_array( $table_options ) ) {

                    foreach ( $table_options as $id => $params ) {

                        if ( ! isset( $params['value'] ) || ! $params['value'] ) continue;

                        $html .= '<div class="aws-terms-table-item" data-term="'. $id .'">';

                            $html .= '<input type="hidden" value="1" name="'. esc_attr( $field['id'] ) .'['. esc_attr( $id ) .'][value]">';

                            $html .= '<div class="name">';
                                $html .= preg_replace('/^(meta|attr|tax)_/', '', $id);
                            $html .= '</div>';

                            if ( $show_weight_column ) {
                                $weight = isset( $params['weight'] ) && $params['weight'] ? $params['weight'] : '';
                                $html .= ' <div class="weight">';
                                    $html .= '<input class="aws-term-weight" min="0" max="999" type="number" value="'. esc_attr( $weight ) .'" name="'. esc_attr( $field['id'] ) .'['. esc_attr( $id ) .'][weight]">';
                                $html .= '</div>';
                            }

                            $html .= ' <div class="delete">';
                                $html .= '<a href="#" class="button delete" data-delete></a>';
                            $html .= '</div>';

                        $html .= '</div>';

                    }

                }

            $html .= '</div>';

            return $html;

        }

        /*
         * Filter Rules field type html markup
         * @return string
         */
        private function get_field_filter_rules( $field ) {

            $filter_section = isset( $field['filter'] ) ? $field['filter'] : 'product';
            $id = $field['id'];

            $html = '';

            $filters = $this->current_opt_value;
            $rules = AWS_Admin_Options::include_filters();
            $default_rule = new AWS_Admin_Filters( $rules[$filter_section][0], $id, $filter_section );
            $rules_container_class = $filters && ! empty($filters) && isset( $filters[$filter_section] ) && ! empty( $filters[$filter_section] ) ? '' : ' aws-rules-empty';

            $disabled_class = '';
            if ($this->is_disabled ) {
                $disabled_class = ' aws-disabled';
            }

            $html = '';

            $html .= '<div class="aws-rules aws-select2-pending' . $rules_container_class . $disabled_class . '">';

            $html .= '<script id="awsRulesTemplate" type="text/html">';

            $html .= $default_rule->get_rule();

            $html .= '</script>';

            if ( $filters && ! empty( $filters ) && isset( $filters[$filter_section] ) ) {

                $incorrect_rules = AWS_Admin_Helpers::check_for_incorrect_filtering_rules( $filters[$filter_section] );

                if ( $incorrect_rules ) {
                    $html .= '<div class="aws-rules-notices">';
                        $html .= __( 'Warning: you set some filter rules incorrectly. You can\'t have several Product or User conditions inside one AND condition group.', 'advanced-woo-search'  ) . '<br>';
                        $html .= __( 'Incorrect rules:', 'advanced-woo-search' ) . '<br>';
                        $html .= '<code>' . $incorrect_rules . '</code>' . '<br>';
                        $html .= __( 'Divide them into several OR condition groups or just delete some.', 'advanced-woo-search' );
                    $html .= '</div>';
                }

                foreach( $filters[$filter_section] as $group_id => $group_rules ) {

                    $group_id = is_string( $group_id ) ? str_replace( 'group_', '', $group_id ) : $group_id;

                    $html .= '<table class="aws-rules-table" data-aws-group="' . esc_attr( $group_id ) . '">';
                        $html .= '<tbody>';

                        foreach( $group_rules as $rule_id => $rule_values ) {

                            $rule_id = is_string( $rule_id ) ? str_replace( 'rule_', '', $rule_id ) : $rule_id;

                            if ( isset( $rule_values['param'] ) ) {
                                $current_rule = new AWS_Admin_Filters( AWS_Admin_Filters_Helpers::include_filter_rule_by_id( $rule_values['param'] ), $id, $filter_section, $group_id, $rule_id, $rule_values );
                                $html .= $current_rule->get_rule();
                            }

                        }

                        $html .= '</tbody>';
                    $html .= '</table>';

                }

            }

            $html .= '<a href="#" class="button add-first-filter" data-aws-add-first-filter>' . esc_html( $field['button'] ) . '</a>';

            $html .= '<a href="#" class="button add-rule-group" data-aws-add-group>' . __( "Add 'or' group", "advanced-woo-search" ) . '</a>';

            $html .= '</div>';

            return $html;

        }

        /*
         * Dynamic Table field type html markup
         * @return string
         */
        private function get_field_dynamic_table( $field ) {

            $table_head = isset( $field['table_head'] ) && $field['table_head'] ? $field['table_head'] : __( 'Search Source', 'advanced-woo-search' );
            $table_options = isset( $this->plugin_options[ $field['id'] ] ) ? $this->plugin_options[ $field['id'] ] : array();
            $button_name = isset( $field['button_name'] ) && $field['button_name'] ? $field['button_name'] : __( 'Add', 'advanced-woo-search' );
            $suboptions = isset( $field['opts'] ) && $field['opts'] ? $field['opts'] : array();
            $placeholder_img = isset( $field['placeholder_image'] ) && $field['placeholder_image'] ?  AWS_PRO_URL . 'assets/img/' . $field['placeholder_image'] : '';
            $table_empty_class = empty( $table_options ) ? ' aws-dynamic-table-empty' : '';
            $field_name = $field['id'];

            $tip_delete = esc_html__( 'Delete', 'advanced-woo-search' );
            $tip_edit = esc_html__( 'Edit', 'advanced-woo-search' );

            $table_action_buttons_html = '';
            $table_action_buttons_html .= '<div class="aws-dynamic-table-item-actions">';
                $table_action_buttons_html .='<a class="button alignright delete aws-tip" data-delete data-tip-text="' . $tip_delete . '" data-tip="' . $tip_delete . '" href="#">' . $tip_delete . '</a>';
                $table_action_buttons_html .='<a class="button alignright edit aws-tip" data-edit data-tip-text="' . $tip_edit . '" data-tip="' . $tip_edit . '" href="#">' . $tip_edit . '</a>';
            $table_action_buttons_html .= '</div>';

            $disabled_class = '';
            if ($this->is_disabled ) {
                $disabled_class = ' aws-disabled';
            }

            $html = '';
            $template_html = '';

            // template for table item
            $template_html .= '<div data-dynamic-table-template="'. esc_attr( $field_name ) .'" style="display:none;">';

                $template_html .= '<div class="aws-dynamic-table-item">';

                    $template_html .= '<div class="aws-dynamic-table-item-sort"></div>';

                    $template_html .= '<div class="aws-dynamic-table-item-name">';
                        $template_html .= '<a href="#">New Filter</a>';
                    $template_html .= '</div>';

                    $template_html .= $table_action_buttons_html;

                    $template_html .= '<div class="aws-dynamic-table-item-settings" style="width:100%;">';

                        $template_html .= '<table class="aws-settings-inner-table">';

                            $template_html .= '<colgroup>';
                                $template_html .= '<col style="width:25%;">';
                                $template_html .= '<col style="width:75%;">';
                            $template_html .= '</colgroup>';

                            $template_html .= '<tbody>';

                            if ( $suboptions ) {
                                foreach ( $suboptions as $suboption ) {
                                    $subid = $suboption['id'];
                                    $suboption['id'] = $field_name . '[{unque_id}][' . $subid . ']';
                                    $suboption['parent_id'] = $field_name . '[' . $subid . ']';
                                    $suboption['is_child_opt'] = true;
                                    $template_html .= $this->generate_field( $suboption );
                                }
                            }

                            $template_html .= '</tbody>';

                        $template_html .= '</table>';

                        $template_html .= '<span class="aws-dynamic-table-item-id">ID: {unque_id}</span>';

                    $template_html .= '</div>';

                $template_html .= '</div>';

            $template_html .= '</div>';

            $template_html = str_replace( ' name', ' aws-input-name', $template_html );
            //

            $html .= $template_html;

            $html .= '<div data-table-name="'. esc_attr( $field_name ) .'" class="aws-dynamic-table'.$table_empty_class.$disabled_class.'">';

                if ( $table_head ) {
                    $html .= '<div class="aws-dynamic-table-head">';
                        $html .= '<span style="width:10%;"></span>';
                        $html .= $table_head;
                    $html .= '</div>';
                }

                $html .= '<div class="aws-dynamic-table-body">';

                    if ( $placeholder_img ) {
                        $html .= '<div class="aws-dynamic-table-placeholder">';
                            $html .= '<img src="'. esc_url( $placeholder_img ) . '" class="aws-placeholder-image" />';
                        $html .= '</div>';
                    }

                    if ( $table_options && is_array( $table_options ) ) {

                        foreach ( $table_options as $option_unique_id => $option ) {

                            if ( ! is_array( $option ) ) {
                                continue;
                            }

                            $table_item_name = isset( $option['item_name'] ) ? $option['item_name'] : '';

                            $html .= '<div class="aws-dynamic-table-item">';

                                $html .= '<div class="aws-dynamic-table-item-sort"></div>';

                                $html .= '<div class="aws-dynamic-table-item-name">';
                                    $html .= '<a href="#">'. esc_html( $table_item_name ) .'</a>';
                                $html .= '</div>';

                                $html .= $table_action_buttons_html;

                                $html .= '<div class="aws-dynamic-table-item-settings" style="width:100%;">';

                                    $html .= '<table class="aws-settings-inner-table">';

                                        $html .= '<colgroup>';
                                            $html .= '<col style="width:25%;">';
                                            $html .= '<col style="width:75%;">';
                                        $html .= '</colgroup>';

                                        $html .= '<tbody>';

                                        if ( $suboptions ) {
                                            foreach ( $suboptions as $suboption ) {
                                                $subid = $suboption['id'];
                                                $suboption['id'] = $field_name . '['. $option_unique_id .'][' . $subid . ']';
                                                $suboption['current_opt_value'] = isset( $option[$subid] ) ? $option[$subid] : '';
                                                $suboption['parent_id'] = $field_name . '[' . $subid . ']';
                                                $suboption['is_child_opt'] = true;
                                                $html .= $this->generate_field( $suboption );
                                            }
                                        }

                                        $html .= '</tbody>';

                                    $html .= '</table>';

                                    $html .= '<span class="aws-dynamic-table-item-id">ID: '.$option_unique_id.'</span>';

                                $html .= '</div>';

                            $html .= '</div>';

                        }
                    }

                $html .= '</div>';

            $html .= '</div>';

            $html .= '<div class="aws-dynamic-table-btn'. $disabled_class .'">';
                $html .= '<button data-add="'. esc_attr( $field_name ) .'" class="button">' . esc_html( $button_name ) . '</button>';
            $html .= '</div>';

            return $html;

        }

        /*
         * Table field type html markup
         * @return string
         */
        private function get_field_table( $field ) {

            $table_head = isset( $field['table_head'] ) && $field['table_head'] ? $field['table_head'] : __( 'Search Source', 'advanced-woo-search' );
            $table_options = isset( $this->plugin_options[ $field['id'] ] ) ? $this->plugin_options[ $field['id'] ] : array();

            $html = '';

            $html .= '<div class="aws-table-sources aws-table-sources-sortable">';

                $html .= '<div class="aws-table-sources-body">';

                if ( is_array( $table_options ) ) {
                    foreach ( $field['choices'] as $val => $fchoices ) {

                        $current_val = isset( $table_options[$val] ) && isset( $table_options[$val]['value'] ) ? $table_options[$val]['value'] : '0';
                        $active_class = isset( $table_options[$val] ) && $table_options[$val] ? 'active' : '';
                        $label = is_array( $fchoices ) ? $fchoices['label'] : $fchoices;
                        $suboptions = is_array( $fchoices ) && isset( $fchoices['suboptions'] ) ? $fchoices['suboptions'] : false;

                        $show_weight_column = $suboptions && isset( $suboptions['weight'] );
                        $weight_val = $show_weight_column && isset( $table_options[$val] ) && isset( $table_options[$val]['weight'] ) ? $table_options[$val]['weight'] : '';

                        $show_selected_column = $suboptions && isset( $suboptions['fields'] );
                        if ( $show_selected_column ) {
                            $selected_column_val = 0;
                            $tax_fields = isset( $table_options[$val] ) && isset( $table_options[$val]['fields'] ) ? $table_options[$val]['fields'] : array();
                            foreach ( $tax_fields as $tax_field ) {
                                if ( $tax_field && isset( $tax_field['value'] ) && $tax_field['value'] == '1' ) {
                                    $selected_column_val++;
                                }
                            }
                        }

                        $disabled_class = '';
                        if ( strpos( $val, ':disabled' ) !== false || ( isset( $field['pro_choices'] ) && array_search( $val, $field['pro_choices'] ) !== false ) ) {
                            $disabled_class = ' aws-disabled';
                        }

                        $field_name = $field['id'] .'['. $val .']';

                        $html .= '<div class="aws-table-sources-item'. $disabled_class .'">';

                            $html .= '<div class="aws-name">';

                                $html .= '<label class="aws-toggle-label aws-toggler-field">';
                                    $html .= '<input data-field="'.$val.'" value="0" type="hidden" name="'. $field_name .'[value]">';
                                    $html .= '<input data-field="'.$val.'" class="aws-toggler" value="1" type="checkbox" name="'. $field_name .'[value]" '. checked( $current_val, '1', false ) .'>';
                                $html .= '</label>';

                                $html .= $label;

                                if ( $show_weight_column ) {
                                    $html .= '<span class="aws-field-weight">';
                                        $html .= esc_html__( 'weight', 'advanced-woo-search' ) . ': ' . '<span data-item-weight>' . esc_html( $weight_val ) . '</span>';
                                    $html .= '</span>';
                                }

                                if ( $show_selected_column ) {
                                    $html .= '<span class="aws-field-weight aws-field-selected-num">';
                                        $html .= esc_html__( 'selected', 'advanced-woo-search' ) . ': ' . '<span data-item-count>' . esc_html( $selected_column_val ) . '</span>';
                                    $html .= '</span>';
                                }

                            $html .= '</div>';

                            $html .= '<div class="aws-actions">';
                            if ( $suboptions ) {
                                 $html .= '<a data-edit class="button alignright tips edit" title="'. esc_attr__( 'Edit', 'advanced-woo-search' ) .'" href="#">'. esc_attr__( 'Edit', 'advanced-woo-search' ) .'</a>';
                            }
                            $html .= '</div>';

                            if ( $suboptions ) {

                                $html .= '<div class="aws-table-sources-item-settings" style="width:100%;">';

                                    $html .= '<table class="aws-settings-inner-table">';

                                        $html .= '<colgroup>';
                                            $html .= '<col style="width:25%;">';
                                            $html .= '<col style="width:75%;">';
                                        $html .= '</colgroup>';

                                        $html .= '<tbody>';

                                            foreach ( $suboptions as $suboption ) {
                                                $subid = $suboption['id'];
                                                $suboption['current_opt_value'] = isset( $table_options[$val] ) && isset( $table_options[$val][$subid] ) ? $table_options[$val][$subid] : '';
                                                $suboption['id'] = $field_name . '[' . $subid . ']';
                                                $suboption['parent_id'] = $field_name . '[' . $subid . ']';
                                                $suboption['is_child_opt'] = true;
                                                $html .= $this->generate_field( $suboption );
                                            }

                                        $html .= '</tbody>';

                                    $html .= '</table>';

                                $html .= '</div>';

                            }

                        $html .= '</div>';

                    }
                }

                $html .= '</div>';

            $html .= '</div>';

            return $html;

        }

        /*
         * HTML field type html markup
         * @return string
         */
        private function get_field_html( $field ) {

            $html = '';

            $html .= $field['html'];

            return $html;

        }

        /*
         * Grab all options dependencies
         * @return void
         */
        public function generate_dependencies_array() {

            // grab all options dependencies
            foreach (  $this->options_array as $field ) {

                if ( isset( $field['depends_on'] ) && is_array( $field['depends_on'] ) ) {
                    foreach ( $field['depends_on'] as $name => $value ) {
                        $this->depends_on[$name][$value][] = $field['id'];
                    }
                }

                if ( isset( $field['opts'] ) && is_array( $field['opts'] ) ) {
                    foreach ( $field['opts'] as $name => $suboption ) {
                        if ( isset( $suboption['depends_on'] ) && is_array( $suboption['depends_on'] ) ) {
                            foreach ( $suboption['depends_on'] as $name => $value ) {
                                $subname = $field['id'] . '[' . $name . ']';
                                $this->depends_on[$subname][$value][] = $field['id'] . '[' . $suboption['id'] . ']';
                            }
                        }
                    }
                }

                if ( isset( $field['type'] ) && $field['type'] === 'table' && isset( $field['choices'] ) ) {
                    foreach ( $field['choices'] as $choices_name => $suboption ) {
                        if ( $suboption && is_array( $suboption ) && isset( $suboption['suboptions'] ) ) {
                            foreach ( $suboption['suboptions'] as $name => $suboption ) {
                                if ( isset( $suboption['depends_on'] ) && is_array( $suboption['depends_on'] ) ) {
                                    foreach ( $suboption['depends_on'] as $name => $value ) {
                                        $subname = $field['id'] . '[' . $choices_name . '][' . $name . ']';
                                        $this->depends_on[$subname][$value][] = $field['id'] . '['.$choices_name.'][' . $suboption['id'] . ']';
                                    }
                                }
                            }
                        }
                    }
                }

            }

        }

        /*
         * Check if current field is hidden due to dependincies
         * @return bool
         */
        public function is_field_depends( $field ) {

            $plugin_options = $this->plugin_options;

            $is_hidden = false;
            if ( isset( $field['depends_on'] ) && $field['depends_on'] ) {

                $is_hidden = true;

                $check_options = $plugin_options;

                // if it is subfield
                if ( strpos( $field['id'], '[' ) !== false ) {
                    preg_match_all('/[^\[\]]+/', $field['id'], $matches);
                    if ( $matches ) {
                        $array_path = $matches[0];
                        if ( $array_path && count( $array_path ) > 1 ) {
                            array_pop($array_path );
                            foreach ($array_path as $key) {
                                if (is_array($check_options) && array_key_exists($key, $check_options)) {
                                    $check_options = $check_options[$key];
                                } else {
                                    $check_options = false;
                                }
                            }
                        }
                    }
                }

                foreach ( $field['depends_on'] as $name => $value ) {
                    $opt_value = '';
                    if ( $check_options && is_array( $check_options ) && isset( $check_options[$name] ) ) {
                        $opt_value = $check_options[$name];
                    } elseif ( $this->options_array ) {
                        // get default option array if no option found
                        foreach ( $this->options_array as $option_item ) {
                            if ( isset( $option_item['id'] ) && $option_item['id'] === $name && isset( $option_item['value'] ) ) {
                                $opt_value = $option_item['value'];
                                break;
                            }
                            if ( isset( $option_item['opts'] ) && $option_item['opts'] ) {
                                foreach ( $option_item['opts'] as $subopt_value ) {
                                    if ( isset( $subopt_value['id'] ) && $subopt_value['id'] === $name && isset( $subopt_value['value'] ) ) {
                                        $opt_value = $subopt_value['value'];
                                        break;
                                    }
                                }
                            }
                            if ( isset( $option_item['type'] ) && $option_item['type'] === 'table' && isset( $option_item['choices'] ) ) {
                                foreach ( $option_item['choices'] as $choices_name => $subopt_value ) {
                                    if ( is_array( $subopt_value ) && isset( $subopt_value['suboptions'] ) ) {
                                        foreach ( $subopt_value['suboptions'] as $subopt_value ) {
                                            if ( isset( $subopt_value['id'] ) && $subopt_value['id'] === $name && isset( $subopt_value['value'] ) ) {
                                                $opt_value = $subopt_value['value'];
                                                break;
                                            }
                                        }
                                    }

                                }
                            }
                        }
                    }
                    if ( $opt_value === $value ) {
                        $is_hidden = false;
                    }
                }
            }

            return $is_hidden;

        }

        /*
         * Generate sections tabs and print
         */
        private function generate_sections_tabs() {

            $section_names = AWS_Admin_Options::get_sections_names();

            $sections = array();
            foreach ( $this->options_array as $k => $field ) {
                if ( isset( $field['section'] ) && $field['section'] ) {
                    $section_id = $field['section'];
                    if ( ! isset( $sections[$section_id] ) ) {
                        $section_name = isset( $section_names[$section_id] ) ? $section_names[$section_id] : $section_id;
                        $sections[$section_id] = $section_name;
                    }
                }
            }

            if ( $sections && count( $sections ) > 1 ) {

                echo '<ul class="aws-admin-sections">';

                $num = 0;

                foreach ( $sections as $section_id => $section_name ) {

                    if ( ! $this->active_section ) {
                        $this->active_section = $section_id;
                    }

                    $is_active = '';

                    if ( ! $num ) {
                        $is_active = ' class="aws-active"';
                    }

                    echo '<li>';
                        echo '<a'. $is_active .' href="#" data-section-name="'. $section_id .'">'. $section_name .'</a>';
                    echo '</li>';

                    $num++;

                }

                echo '</ul>';

            }

        }

    }

endif;
