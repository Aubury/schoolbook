<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! class_exists( 'AWS_Admin_Options' ) ) :

    /**
     * Class for plugin admin options methods
     */
    class AWS_Admin_Options {

        /*
          * Get default settings values
          * @param string $tab Tab name
          * @return array
          */
        static public function get_default_settings( $tab = false, $sec = 'none' ) {

            $options = self::options_array( $tab, $sec );
            $default_settings = array();

            foreach ( $options as $section_name => $section ) {

                // store options from 'performance' tab separatly
                if ( ! $tab && $section_name === 'performance' ) {
                    continue;
                }

                foreach ($section as $values) {

                    if ( isset( $values['type'] ) && $values['type'] === 'heading' ) {
                        continue;
                    }

                    if ( isset( $values['type'] ) && $values['type'] === 'html' ) {
                        continue;
                    }

                    if ( isset( $values['disabled'] ) && $values['disabled'] ) {
                        continue;
                    }

                    $current_option = AWS_Admin_Helpers::get_current_option( $values, true );

                    $default_settings[$values['id']] = $current_option;

                }

            }

            return $default_settings;

        }

        /*
         * Update plugin settings
         */
        static public function update_settings() {

            $options = self::options_array( false, 'none' );
            $settings = self::get_settings();

            $current_page = isset( $_GET['page']  ) ? sanitize_text_field( $_GET['page'] ) : 'aws-options';
            $current_options = $current_page === 'aws-performance' ? array_intersect_key( $options, array('performance' => '') ) : array_diff_key( $options, array('performance' => '') ) ;
            $instance_id = isset( $_GET['aws_id'] ) ? (int) sanitize_text_field( $_GET['aws_id'] ) : 0;
            $instance_settings = $settings[$instance_id];

            foreach ( $current_options as $current_tab ) {
                foreach ( $current_tab as $values ) {

                    if ( isset( $values['type'] ) && $values['type'] === 'heading' ) {
                        continue;
                    }

                    if ( isset( $values['type'] ) && $values['type'] === 'html' ) {
                        continue;
                    }

                    if ( isset( $values['disabled'] ) && $values['disabled'] ) {
                        continue;
                    }

                    $current_option = AWS_Admin_Helpers::get_current_option( $values );

                    $instance_settings[ $values['id'] ] = $current_option;

                }

            }

            $settings[$instance_id] = $instance_settings;

            update_option( 'aws_pro_settings', $settings );

            do_action( 'aws_settings_saved', $settings );

            do_action( 'aws_cache_clear' );

        }

        /*
         * Update common plugin settings
         */
        static public function update_common_settings() {

            $options = self::options_array( false );
            $settings = self::get_common_settings();
            $current_page = isset( $_GET['page']  ) ? sanitize_text_field( $_GET['page'] ) : 'aws-options';
            $current_tab = empty( $_GET['tab'] ) ? 'general' : sanitize_text_field( $_GET['tab'] );
            if ( $current_page === 'aws-performance' ) {
                $current_tab = 'performance';
            }

            foreach ( $options[$current_tab] as $values ) {

                if ( ! isset( $values['type'] ) ) {
                    continue;
                }

                if ( $values['type'] === 'heading' || $values['type'] === 'html' ) {
                    continue;
                }

                if ( isset( $values['disabled'] ) && $values['disabled'] ) {
                    continue;
                }

                $current_option = AWS_Admin_Helpers::get_current_option( $values );

                $settings[ $values['id'] ] = $current_option;

            }

            update_option( 'aws_pro_common_opts', $settings );

            do_action( 'aws_settings_saved', $settings );

            do_action( 'aws_cache_clear' );

        }

        /*
         * Get plugin settings
         * @return array
         */
        static public function get_settings() {
            $plugin_options = get_option( 'aws_pro_settings' );
            return $plugin_options;
        }

        /*
         * Get plugin settings
         * @return array
         */
        static public function get_common_settings() {
            $plugin_options = get_option( 'aws_pro_common_opts' );
            return $plugin_options;
        }

        /*
         * Get options array
         *
         * @param string $tab Tab name
         * @param string $section Section name
         * @return array
         */
        static public function options_array( $tab = false, $section = false ) {

            $options = self::include_options();
            $options_arr = array();

            foreach ( $options as $tab_name => $tab_options ) {

                if ( $tab && $tab !== $tab_name ) {
                    continue;
                }

                foreach ( $tab_options as $option ) {

                    if ( isset( $option['value'] ) && isset( $option['value']['callback'] ) ) {
                        $option['value'] = call_user_func_array( $option['value']['callback'], $option['value']['params'] );
                    }

                    if ( isset( $option['choices'] ) && isset( $option['choices']['callback'] ) ) {
                        $option['choices'] = call_user_func_array( $option['choices']['callback'], $option['choices']['params'] );
                    }

                    if ( isset( $option['opts'] ) && is_array( $option['opts'] ) ) {
                        foreach ( $option['opts'] as $opt_name => $opt_itemt ) {
                            if ( isset( $opt_itemt['value'] ) && isset( $opt_itemt['value']['callback'] ) ) {
                                $option['opts'][$opt_name]['value'] = call_user_func_array( $opt_itemt['value']['callback'], $opt_itemt['value']['params'] );
                            }
                            if ( isset( $opt_itemt['choices'] ) && isset( $opt_itemt['choices']['callback'] ) ) {
                                $option['opts'][$opt_name]['choices'] = call_user_func_array( $opt_itemt['choices']['callback'], $opt_itemt['choices']['params'] );
                            }
                        }
                    }

                    $options_arr[$tab_name][] = $option;

                }


            }

            /**
             * Filter admin page options for current page
             * @since 2.23
             * @param array $options_arr Array of options
             * @param bool|string $tab Current settings page tab
             * @param bool|string $section Current settings page section
             */
            $options_arr = apply_filters( 'aws_admin_page_options_current', $options_arr, $tab, $section );

            return $options_arr;

        }

        /*
         * Include options array
         * @return array
         */
        static public function include_options() {

            $product_stock_status = 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ? array( 'in_stock' => 1, 'out_of_stock' => 0, 'on_backorder' => 0 ) : array( 'in_stock' => 1, 'out_of_stock' => 1, 'on_backorder' => 1 );

            $attr_index_empty = '';
            $tax_index_empty = '';
            $meta_index_empty = '';

            $child_search_notice = '';
            $index_variations_option = AWS_PRO()->get_common_settings( 'index_variations' );
            if ( $index_variations_option && $index_variations_option === 'false' ) {
                $child_search_notice = '<br><span style="color:#CC0606;">' . sprintf(esc_html__( "In order to show product variations please %s first.", "advanced-woo-search" ), '<a href="' . esc_url( admin_url('admin.php?page=aws-options&tab=performance#index_variationstrue') ) . '">' . __( "enable child products index", "advanced-woo-search" ) . '</a>' ) . '</span>';
            }

            $suggestions_disabled = false;
            $suggestion_index_notice = '';
            $is_title_enabled = '';
            $is_category_enabled = '';

            // if suggestions page
            $index_options = AWS_Helpers::get_index_options();

            $reindex_version = $index_options['reindex_version'];
            $title_indexed = $index_options['index']['title'];
            $category_indexed = $index_options['index']['category'];

            if ( $reindex_version && version_compare( $reindex_version, '3.21', '<' ) ) {
                $suggestions_disabled = true;
                $suggestion_index_notice = '<br><span style="color:#CC0606;">' . sprintf(esc_html__( "It looks like you have an outdated plugin index table. Please %s and then return to this page.", "advanced-woo-search" ), '<a href="' . esc_url( admin_url('admin.php?page=aws-options') ) . '">' . __( "Reindex Table", "advanced-woo-search" ) . '</a>' ) . '</span>';
            }

            if ( ! $title_indexed ) {
                $is_title_enabled = ' <span style="color:#CC0606;"> ' . esc_html__( "(not indexed)", "advanced-woo-search" ) . '</span>';
            }

            if ( ! $category_indexed ) {
                $is_category_enabled = ' <span style="color:#CC0606;">' . esc_html__( "(not indexed)", "advanced-woo-search" ) . '</span>';
            }

            $options = array();

            $options['general'][] = array(
                "name"    => __( "", "advanced-woo-search" ),
                "id"    => "search_instance",
                "value" => "Search Form",
                "type"    => "hidden"
            );

            $options['performance'][] = array(
                "name"    => __( "Search options", "advanced-woo-search" ),
                "type"    => "heading"
            );

            $options['performance'][] = array(
                "name"  => __( "Search rule", "advanced-woo-search" ),
                "desc"  => __( "Search rule that will be used for terms search.", "advanced-woo-search" ),
                "id"    => "search_rule",
                "inherit" => "true",
                "value" => 'contains',
                "type"  => "radio",
                'choices' => array(
                    'contains' => '%s% ' . __( "( contains )", "advanced-woo-search" ) . ' <span class="aws-help-tip aws-tip" data-tip="'. esc_attr( __( "Search query can be inside any part of the product words ( beginning, end, middle ). Slow.", "advanced-woo-search" ) ) .'"></span>',
                    'begins'   => 's% ' . __( "( begins )", "advanced-woo-search" ) . ' <span class="aws-help-tip aws-tip" data-tip="'. esc_attr( __( "Search query can be only at the beginning of the product words. Fast.", "advanced-woo-search" ) ) .'"></span>',
                )
            );

            $options['performance'][] = array(
                "name"  => __( "AJAX timeout", "advanced-woo-search" ),
                "desc"  => __( "Timeout for ajax search event.", "advanced-woo-search" ),
                "tip"  => __( "Time after user input that script is waiting before sending a search event to the server, ms.", "advanced-woo-search" ),
                "id"    => "search_timeout",
                "inherit" => "true",
                "value" => 300,
                'min'   => 100,
                "type"  => "number"
            );

            $options['performance'][] = array(
                "name"  => __( "Search words number", "advanced-woo-search" ),
                "tip"  => __( "The maximum number of words allowed for the search. All extra words will be removed from the search query.", "advanced-woo-search" ),
                "desc"  => __( "Maximum allowed search words.", "advanced-woo-search" ),
                "id"    => "search_words_num",
                "inherit" => "true",
                "value" => 6,
                'min'   => 1,
                "type"  => "number"
            );

            $options['performance'][] = array(
                "name"  => __( "Stop words list", "advanced-woo-search" ),
                "desc"  => __( "List of ignored search words.", "advanced-woo-search" ),
                "tip"  => __( "Comma separated list of words that will be excluded from search.", "advanced-woo-search" ) . '<br>' . __( "Re-index required on change.", "advanced-woo-search" ),
                "id"    => "stopwords",
                "inherit" => "true",
                "value" => "a, also, am, an, and, are, as, at, be, but, by, call, can, co, con, de, do, due, eg, eight, etc, even, ever, every, for, from, full, go, had, has, hasnt, have, he, hence, her, here, his, how, ie, if, in, inc, into, is, it, its, ltd, me, my, no, none, nor, not, now, of, off, on, once, one, only, onto, or, our, ours, out, over, own, part, per, put, re, see, so, some, ten, than, that, the, their, there, these, they, this, three, thru, thus, to, too, top, un, up, us, very, via, was, we, well, were, what, when, where, who, why, will",
                "cols"  => "85",
                "rows"  => "3",
                "type"  => "textarea"
            );

            $options['performance'][] = array(
                "name"  => __( "Synonyms", "advanced-woo-search" ),
                "desc"  => __( "List of synonyms words.", "advanced-woo-search" ),
                "tip"  => __( "Comma separated list of synonym words. Each group of synonyms must be on separated text line.", "advanced-woo-search" ) . '<br>' . __( "Re-index required on change.", "advanced-woo-search" ),
                "id"    => "synonyms",
                "inherit" => "true",
                "value" => "buy, pay, purchase, acquire&#13;&#10;box, housing, unit, package",
                "cols"  => "85",
                "rows"  => "3",
                "type"  => "textarea"
            );

            $options['performance'][] = array(
                "name"    => __( "Cache options", "advanced-woo-search" ),
                "type"    => "heading"
            );

            $options['performance'][] = array(
                "name"  => __( "Cache results", "advanced-woo-search" ),
                "desc"  => __( "Cache search results to increase search speed.", "advanced-woo-search" ),
                "tip"  => __( "Turn off if you have old data in the search results after the content of products was changed.", "advanced-woo-search" ),
                "id"    => "cache",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['performance'][] = array(
                "name"    => __( "Clear cache", "advanced-woo-search" ),
                "type"    => "html",
                "desc"    =>__( "Clear cache for all search results.", "advanced-woo-search" ),
                "html"    => '<div id="aws-clear-cache"><input class="button" type="button" value="' . esc_attr__( 'Clear cache', 'advanced-woo-search' ) . '"></div>',
            );

            $options['performance'][] = array(
                "name"    => __( "Index table options", "advanced-woo-search" ),
                "id"      => "index_sources",
                "type"    => "heading"
            );

            $options['performance'][] = array(
                "name"         => __( "Overview", "advanced-woo-search" ),
                'heading_type' => 'text',
                'desc'         => __( "Set an options related to plugin index table.", "advanced-woo-search" ) .'<br><b>' . __( "Note:", "advanced-woo-search" ) . '</b> ' . __( "Reindex is required after options changes.", "advanced-woo-search" ),
                'tip'          => __( 'To perform the search plugin use a special index table. This table contains normalized words of all your products from all available sources.', "advanced-woo-search" ) . '<br>' .
                    __( 'Sometimes when there are too many products in your store index table can be very large and that can reflect on search speed.', "advanced-woo-search" ) . '<br>' .
                    __( 'In this section you can use several options to change the table size by disabling some unused product data.', "advanced-woo-search" ),
                "type"         => "heading"
            );

            $options['performance'][] = array(
                "name"       => __( "Data to index", "advanced-woo-search" ),
                "tip"        => __( "Choose what products data to add inside the plugin index table.", "advanced-woo-search" ),
                "table_head" => __( 'What to index', 'advanced-woo-search' ),
                "id"         => "index_sources",
                "inherit"    => "true",
                "value" => array(
                    'title'    => 1,
                    'content'  => 1,
                    'sku'      => 1,
                    'gtin'     => 1,
                    'excerpt'  => 1,
                    'brand'    => 1,
                    'category' => 1,
                    'tag'      => 1,
                    'id'       => 1,
                    'attr'     => 0,
                    'tax'      => 0,
                    'meta'     => 0,
                ),
                "choices" => array(
                    "title"    => array(
                        'label'      => __( "Title", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "content"    => array(
                        'label'      => __( "Content", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "sku"    => array(
                        'label'      => __( "SKU", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "gtin"    => array(
                        'label'      => __( "GTIN, UPC, EAN, ISBN", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "excerpt"    => array(
                        'label'      => __( "Short description", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "brand"    => array(
                        'label'      => __( "Brand", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "category"    => array(
                        'label'      => __( "Category", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "tag"    => array(
                        'label'      => __( "Tag", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "id"    => array(
                        'label'      => __( "ID", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "attr"     => array(
                        'label'      => __( "Attributes", "advanced-woo-search" ),
                        'suboptions' => array(
                            'fields' => array(
                                "name"    => __( "Attributes", "advanced-woo-search" ),
                                "desc"    => '',
                                "tip"    => __( "Choose product attributes that must be added to the index.", "advanced-woo-search" ),
                                "id"      => "fields",
                                "field_id"    => "attr",
                                "value"   => array(),
                                "type"    => "terms_select"
                            ),
                        ),
                    ),
                    "tax"     => array(
                        'label'      => __( "Taxonomies", "advanced-woo-search" ),
                        'suboptions' => array(
                            'fields' => array(
                                "name"    => __( "Taxonomies", "advanced-woo-search" ),
                                "desc"    => '',
                                "tip"    => __( "Choose product taxonomies that must be added to the index.", "advanced-woo-search" ),
                                "id"      => "fields",
                                "field_id"    => "tax",
                                "value"   => array(),
                                "type"    => "terms_select"
                            ),
                        ),
                    ),
                    "meta"     => array(
                        'label'      => __( "Custom Fields", "advanced-woo-search" ),
                        'suboptions' => array(
                            'fields' => array(
                                "name"    => __( "Fields", "advanced-woo-search" ),
                                "desc"    => '',
                                "tip"    => __( "Choose product custom fields that must be added to the index.", "advanced-woo-search" ),
                                "id"      => "fields",
                                "field_id"    => "meta",
                                "value"   => array(),
                                "type"    => "terms_select"
                            ),
                        ),
                    ),
                ),
                "type"    => "table"
            );

            $options['performance'][] = array(
                "name"  => __( "Index variations", "advanced-woo-search" ),
                "desc"  => __( "Index or not content of product variations.", "advanced-woo-search" ),
                "id"    => "index_variations",
                "inherit" => "true",
                "value" => 'false',
                "type"  => "toggler",
            );

            $options['performance'][] = array(
                "name"  => __( "Sync index table", "advanced-woo-search" ),
                "desc"  => __( "Automatically sync index table.", "advanced-woo-search" ),
                "tip"  => __( "Automatically update plugin index table when product content was changed. This means that in search there will be always latest product data.", "advanced-woo-search" ) . '<br>' .
                    __( "Turn this off if you have any problems with performance.", "advanced-woo-search" ),
                "id"    => "autoupdates",
                "value" => 'true',
                "inherit" => "true",
                "type"  => "toggler",
            );

            $options['performance'][] = array(
                "name"  => __( "Run shortcodes", "advanced-woo-search" ),
                "desc"  => __( "Execute shortcodes inside product content.", "advanced-woo-search" ),
                "id"    => "index_shortcodes",
                "value" => 'true',
                "inherit" => "true",
                "type"  => "toggler",
            );

            $options['form'][] = array(
                "name"    => __( "Search Bar Settings", "advanced-woo-search" ),
                "id"      => "bar",
                "type"    => "heading",
                "section" => "main",
            );

            $options['form'][] = array(
                "name"  => __( "Text for search field", "advanced-woo-search" ),
                "desc"  => __( "Text for search field placeholder.", "advanced-woo-search" ),
                "id"    => "search_field_text",
                "inherit" => "true",
                "value" => __( "Search", "advanced-woo-search" ),
                "type"  => "text",
                "section" => "main",
            );

            $options['form'][] = array(
                "name"  => __( "Nothing found field", "advanced-woo-search" ),
                "desc"  => __( "Text when there is no search results.", "advanced-woo-search" ),
                "tip"   => __( "HTML tags are allowed.", "advanced-woo-search" ),
                "id"    => "not_found_text",
                "inherit" => "true",
                "value" => __( "Nothing found", "advanced-woo-search" ),
                "type"  => "textarea",
                'allow_tags' => AWS_Admin_Helpers::kses_textarea_allowed_tags(),
                "section" => "main",
            );

            $options['form'][] = array(
                "name"  => __( "Minimum number of characters", "advanced-woo-search" ),
                "desc"  => __( "Minimum number of characters required to run ajax search.", "advanced-woo-search" ),
                "id"    => "min_chars",
                "inherit" => "true",
                "value" => 1,
                "min" => 1,
                "type"  => "number",
                "section" => "main",
            );

            $options['form'][] = array(
                "name"  => __( "AJAX search", "advanced-woo-search" ),
                "desc"  => __( "Use or not live search feature.", "advanced-woo-search" ),
                "id"    => "enable_ajax",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "toggler",
                "section" => "main",
            );

            $options['form'][] = array(
                "name"  => __( "Show loader", "advanced-woo-search" ),
                "desc"  => __( "Show loader animation while searching.", "advanced-woo-search" ),
                "id"    => "show_loader",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "toggler",
                "section" => "main",
            );

            $options['form'][] = array(
                "name"  => __( "Clear button", "advanced-woo-search" ),
                "desc"  => __( "Show 'Clear search string' button.", "advanced-woo-search" ),
                "tip"  => __( "Only for desktop devices. For mobile devices it is always visible.", "advanced-woo-search" ),
                "id"    => "show_clear",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "toggler",
                "section" => "main",
            );

            $options['form'][] = array(
                "name"  => __( "'View All Results' button", "advanced-woo-search" ),
                "desc"  => __( "Show link to the search results page.", "advanced-woo-search" ),
                "tip"  => __( "Displayed at the bottom of live search results block.", "advanced-woo-search" ),
                "id"    => "show_more",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "toggler",
                "section" => "main",
            );

            $options['form'][] = array(
                "name"  => '<span class="dashicons dashicons-editor-break aws-subopts-icon"></span>' . __( "'View All Results' text", "advanced-woo-search" ),
                "desc"  => __( "Text for 'View All Results' link.", "advanced-woo-search" ),
                "id"    => "show_more_text",
                "inherit" => "true",
                "value" => __( "View all results", "advanced-woo-search" ),
                "type"  => "text",
                "depends_on" => array( "show_more" => "true" ),
                "section" => "main",
            );

            $options['form'][] = array(
                "name"  => __( "Mobile full screen", "advanced-woo-search" ),
                "desc"  => __( "Full screen search on focus.", "advanced-woo-search" ),
                "tip"  => __( "Works only on mobile devices. Will not work if the search form is inside the block with position: fixed.", "advanced-woo-search" ),
                "id"    => "mobile_overlay",
                "inherit" => "true",
                "value" => 'false',
                "type"  => "toggler",
                "section" => "main",
            );

            $options['form'][] = array(
                "name"  => __( "Show title in input", "advanced-woo-search" ),
                "desc"  => __( "Show title of hovered product.", "advanced-woo-search" ),
                "tip"  => __( "Title is displayed inside search input field.", "advanced-woo-search" ),
                "id"    => "show_addon",
                "value" => 'false',
                "type"  => "toggler",
                "section" => "main",
            );

            $options['form'][] = array(
                "name"  => __( "Form Styling", "advanced-woo-search" ),
                "desc"  => __( "Choose search form layout", "advanced-woo-search" ),
                "tip"  =>  __( "Filter button will be visible only if you have more than one active filter for current search form instance.", "advanced-woo-search" ),
                "id"    => "buttons_order",
                "inherit" => "true",
                "value" => '2',
                "type"  => "radio-image",
                'choices' => array(
                    '1' => 'btn-layout1.png',
                    '2' => 'btn-layout2.png',
                    '3' => 'btn-layout3.png',
                    '4' => 'btn-layout4.png',
                    '5' => 'btn-layout5.png',
                    '6' => 'btn-layout6.png',
                ),
                "section" => "main",
            );


            $options['form'][] = array(
                "name"    => __( "Quick Filters", "advanced-woo-search" ),
                "id"      => "quick_filters",
                "desc"    => __( "Create pre-defined search bar filters.", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/features/filters-button/">'. __( "Learn more.", "advanced-woo-search" ) .'</a>',
                "type"    => "heading",
                "section" => "quick_filters",
            );

            $options['form'][] = array(
                "name"    => __( "Quick Filters", "advanced-woo-search" ),
                "desc"    => __( "", "advanced-woo-search" ),
                "table_head" => __( 'Filter Name', 'advanced-woo-search' ),
                "button_name" => __( 'Add New Filter', 'advanced-woo-search' ),
                "id"      => "quick_filters",
                "value" => '',
                "placeholder_image" => 'quick-filters.png',
//                "colspan" => 2,
                "opts"  => array(
                    'item_name' => array(
                        "name"  => __( "Filter name", "advanced-woo-search" ),
                        "desc"  => __( "Name for current filter.", "advanced-woo-search" ),
                        "id"    => "item_name",
                        "value" => "New Filter",
                        "type"  => "text"
                    ),
                    'filter_type' => array(
                        "name"  => __( "Filter type", "advanced-woo-search" ),
                        "tip"  => __( "Set the type of current filter.", "advanced-woo-search" ),
                        "id"    => "filter_type",
                        "value" => 'simple',
                        "type"  => "radio-image",
                        "direction" => "horizontal",
                        "styles" => "max-width:100%",
                        'choices' => array(
                            'simple' => array(
                                'img' => 'filters-1.png',
                                'desc' => __( "Single filter.", "advanced-woo-search" ) . '<br>' . __( "Set filtering rules manually.", "advanced-woo-search" ),
                            ),
                            'complex' => array(
                                'img' => 'filters-2.png',
                                'desc' => __( "Miltiple filters.", "advanced-woo-search" ) . '<br>' . __( "Auto generated by specific parameters.", "advanced-woo-search" ),
                            ),
                        )
                    ),
                    'filter_by' => array(
                        "name"  => __( "Filter by", "advanced-woo-search" ),
                        "desc"  => __( "By what product taxonomy to create filtering choices.", "advanced-woo-search" ),
                        "id"    => "filter_by",
                        "value" => 'product_cat',
                        "type"  => "select_ajax",
                        'ajax' => array(
                            'callback' => 'AWS_Admin_Filters_Helpers::get_all_tax',
                            'params'   => array(),
                            "action" => "aws-getSelectOptionValues",
                            "placeholder" => __( "Taxonomy name", "advanced-woo-search" ),
                            "value_callback"  => "AWS_Admin_Filters_Helpers::get_tax_name_by_slug",
                            "input" => 0,
                        ),
                        "depends_on" => array( "filter_type" => "complex" ),
                    ),
                    'max_number' => array(
                        "name"  => __( "Max number", "advanced-woo-search" ),
                        "desc"  => __( "Maximal number of generated filter choices.", "advanced-woo-search" ),
                        "tip"   => __( "Empty or 0 - no limit.", "advanced-woo-search" ),
                        "id"    => "max_number",
                        "value" => '',
                        "min" => 0,
                        "type"  => "number",
                        "depends_on" => array( "filter_type" => "complex" ),
                    ),
                    'only_parents' => array(
                        "name"  => __( "Only top level", "advanced-woo-search" ),
                        "desc"  => __( "For hierarchical taxonomies - show only top level terms as filtering options.", "advanced-woo-search" ),
                        "id"    => "only_parents",
                        "value" => 'true',
                        "type"  => "toggler",
                        "depends_on" => array( "filter_type" => "complex" ),
                    ),
                    'hide_empty' => array(
                        "name"  => __( "Hide empty", "advanced-woo-search" ),
                        "desc"  => __( "Hide terms without products.", "advanced-woo-search" ),
                        "id"    => "hide_empty",
                        "value" => 'true',
                        "type"  => "toggler",
                        "depends_on" => array( "filter_type" => "complex" ),
                    ),
                    'heading' => array(
                        "name"         => __( "Filtering Rules", "advanced-woo-search" ),
                        'heading_type' => 'text',
                        "id" => "heading",
                        "desc"         => __( "Filtering rules for search results.", "advanced-woo-search" ) . ' ' . __( "Overwrites standard filters for search results.", "advanced-woo-search" ),
                        "tip"         => __( "Filter search results. You can include/exclude search results based on different rules.", "advanced-woo-search" ) . '<br>' .
                            __( "Combine filter rules to AND or OR logical blocks to create advanced filter logic.", "advanced-woo-search" ) . '<br>' .
                            __( "Please try not to use too many filters overwise this can impact on search speed.", "advanced-woo-search" ),
                        "type"         => "heading",
                        "depends_on" => array( "filter_type" => "simple" ),
                    ),
                    'products_filter' => array(
                        "name"    => __( "Products results", "advanced-woo-search" ),
                        "desc"    => '',
                        "button"  => __( "Filter products search results", "advanced-woo-search" ),
                        "id"      => "adv_filters",
                        "filter"  => "product",
                        "value"   => '',
                        "type"    => "filter_rules",
                        "depends_on" => array( "filter_type" => "simple" ),
                    ),
                    'terms_filter' => array(
                        "name"    => __( "Terms results", "advanced-woo-search" ),
                        "desc"    => '',
                        "button"  => __( "Filter taxonomies archive pages results", "advanced-woo-search" ),
                        "id"      => "adv_filters",
                        "filter"  => "term",
                        "value"   => '',
                        "type"    => "filter_rules",
                        "depends_on" => array( "filter_type" => "simple" ),
                    ),
                    'users_filter' => array(
                        "name"    => __( "Users results", "advanced-woo-search" ),
                        "desc"    => '',
                        "button"  => __( "Filter users archive pages search results", "advanced-woo-search" ),
                        "id"      => "adv_filters",
                        "filter"  => "user",
                        "value"   => '',
                        "type"    => "filter_rules",
                        "depends_on" => array( "filter_type" => "simple" ),
                    ),
                ),
                "type"    => "dynamic_table",
                "section" => "quick_filters",
            );

            $options['suggestions'][] = array(
                "name"    => __( "Search Query Suggestions", "advanced-woo-search" ),
                "id"      => "suggestions",
                "type"    => "heading",
                "section" => "main",
            );

            $options['suggestions'][] = array(
                "name"  => __( "Enable suggestions", "advanced-woo-search" ),
                "desc"  => __( "Enable search terms automatic suggestions.", "advanced-woo-search" ) . $suggestion_index_notice,
                "id"    => "enable_suggestions",
                "inherit" => "true",
                "value" => 'false',
                "type"  => "toggler",
                "disabled" => $suggestions_disabled,
                "section" => "main",
            );

            $options['suggestions'][] = array(
                "name"  => __( "Display layout", "advanced-woo-search" ),
                "desc"  => __( "Search suggestions layout.", "advanced-woo-search", "advanced-woo-search" ),
                "id"    => "suggestions_layout",
                "inherit" => "true",
                "value" => '2',
                "type"  => "radio-image",
                'choices' => array(
                    '2' => 'suggestion-2.png',
                    '1' => 'suggestion-1.png',
                ),
                "section" => "main",
            );

            $options['suggestions'][] = array(
                "name"  => __( "Max number of suggestions", "advanced-woo-search" ),
                "desc"  => __( "Maximum search suggestions per query.", "advanced-woo-search" ),
                "id"    => "suggestions_max_number",
                "inherit" => "true",
                "value" => 6,
                "min" => 1,
                "type"  => "number",
                "section" => "main",
            );

            $options['suggestions'][] = array(
                "name"  => __( "Max words per suggestion", "advanced-woo-search" ),
                "desc"  => __( "Maximal number of words per one search suggestion.", "advanced-woo-search" ),
                "id"    => "suggestions_words_max",
                "inherit" => "true",
                "value" => 5,
                "min" => 1,
                "type"  => "number",
                "section" => "main",
            );

            $options['suggestions'][] = array(
                "name"  => __( "Sources", "advanced-woo-search" ),
                "desc"  => __( "Generate suggestions based on the following sources.", "advanced-woo-search" ),
                "id"    => "suggestions_sources",
                "value" => array( 'title' => 1, 'category' => 0 ),
                "type"  => "checkbox",
                'choices' => array(
                    'title'     => __( 'Product title', 'advanced-woo-search' ) . $is_title_enabled,
                    'category' => __( 'Product category', 'advanced-woo-search' ) . $is_category_enabled,
                ),
                "section" => "main",
            );

            $options['suggestions'][] = array(
                "name"  => __( "Show only when no results", "advanced-woo-search" ),
                "desc"  => __( "Show suggestions only if current search has no results.", "advanced-woo-search" ),
                "id"    => "suggestions_no_results",
                "inherit" => "true",
                "value" => 'false',
                "type"  => "toggler",
                "section" => "main",
            );

            $options['suggestions'][] = array(
                "name"  => __( "On suggestion click", "advanced-woo-search" ),
                "desc"  => __( "Behavior when user clicks on search suggestions item.", "advanced-woo-search" ),
                "tip"  => __( "Any of the chosen values ( ajax, search page ) need to be enabled.", "advanced-woo-search" ),
                "id"    => "suggestions_on_click",
                "inherit" => "true",
                "value" => 'ajax',
                "type"  => "select",
                'choices' => array(
                    'ajax'  => __( 'Trigger AJAX search', 'advanced-woo-search' ),
                    'page' => __( 'Redirect to the search page', 'advanced-woo-search' ),
                ),
                "section" => "main",
            );

            $options['results'][] = array(
                "name"    => __( "Live Results Settings", "advanced-woo-search" ),
                "id"      => "general",
                "type"    => "heading",
                "section" => "main",
            );

            $options['results'][] = array(
                "name"  => __( "Style", "advanced-woo-search" ),
                "desc"  => __( "Set style for search results output.", "advanced-woo-search" ),
                "id"    => "style",
                "value" => 'style-inline',
                "type"  => "select",
                'choices' => array(
                    'style-inline'   => __( "Inline Style", "advanced-woo-search" ),
                    'style-grid'     => __( "Grid Style", "advanced-woo-search" ),
                    'style-big-grid' => __( "Big Grid Style", "advanced-woo-search" ),
                ),
                "section" => "main",
            );

            $options['results'][] = array(
                "name"  => __( "Description content", "advanced-woo-search" ),
                "desc"  => __( "What to show in product description?", "advanced-woo-search" ),
                "id"    => "mark_words",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "select",
                'choices' => array(
                    'true'  => __( "Smartly scrape matching sentences from descriptions.", "advanced-woo-search" ),
                    'false' => __( "First N words of product description.", "advanced-woo-search" ),
                ),
                "section" => "main",
            );

            $options['results'][] = array(
                "name"  => __( "Description length", "advanced-woo-search" ),
                "desc"  => __( "Maximum product description length ( in words ).", "advanced-woo-search" ),
                "id"    => "excerpt_length",
                "inherit" => "true",
                "value" => 20,
                "min" => 0,
                "type"  => "number",
                "section" => "main",
            );

            $options['results'][] = array(
                "name"  => __( "Products number", "advanced-woo-search" ),
                "desc"  => __( "Maximum displayed search results.", "advanced-woo-search" ),
                "id"    => "results_num",
                "inherit" => "true",
                "value" => 10,
                "min" => 0,
                "type"  => "number",
                "section" => "main",
            );

            $options['results'][] = array(
                "name"  => __( "Variable products", "advanced-woo-search" ),
                "desc"  => __( "How to show variable products.", "advanced-woo-search" ) . $child_search_notice,
                "id"    => "var_rules",
                "value" => 'parent',
                "type"  => "select",
                'choices' => array(
                    'parent' => __( 'Show only parent products', 'advanced-woo-search' ),
                    'both'   => __( 'Show parent and child products', 'advanced-woo-search' ),
                    'child'  => __( 'Show only child products', 'advanced-woo-search' ),
                ),
                "section" => "main",
            );

            $options['results'][] = array(
                "name"  => __( "Products sale status", "advanced-woo-search" ),
                "desc"  => __( "Search only for products with selected sale status", "advanced-woo-search" ),
                "id"    => "on_sale",
                "value" => 'true',
                "type"  => "select",
                'choices' => array(
                    'true'  => __( "Show on-sale and not on-sale products", "advanced-woo-search" ),
                    'false' => __( "Show only on-sale products", "advanced-woo-search" ),
                    'not'   => __( "Show only not on-sale products", "advanced-woo-search" ),
                ),
                "section" => "main",
            );

            $options['results'][] = array(
                "name"  => __( "Products stock status", "advanced-woo-search" ),
                "desc"  => __( "Search only for products with selected stock status", "advanced-woo-search" ),
                "id"    => "product_stock_status",
                "value" => $product_stock_status,
                "type"  => "checkbox",
                'choices' => array(
                    'in_stock'     => __( 'In stock', 'advanced-woo-search' ),
                    'out_of_stock' => __( 'Out of stock', 'advanced-woo-search' ),
                    'on_backorder' => __( 'On backorder', 'advanced-woo-search' ),
                ),
                "section" => "main",
            );

            $options['results'][] = array(
                "name"  => __( "Products visibility", "advanced-woo-search" ),
                "desc"  => __( "Search only products with this visibilities.", "advanced-woo-search" ),
                "id"    => "product_visibility",
                "value" => array(
                    'visible'  => 1,
                    'search'   => 1,
                    'catalog'  => 0,
                    'hidden'   => 0,
                ),
                "type"  => "checkbox",
                'choices' => array(
                    'visible'  => __( 'Catalog/search', 'advanced-woo-search' ),
                    'search'   => __( 'Search', 'advanced-woo-search' ),
                    'catalog'  => __( 'Catalog', 'advanced-woo-search' ),
                    'hidden'   => __( 'Hidden', 'advanced-woo-search' ),
                ),
                "section" => "main",
            );

            $options['results'][] = array(
                "name"    => __( "Non-Products Search Results", "advanced-woo-search" ),
                "id"      => "archives",
                "type"    => "heading",
                "section" => "non_products",
            );

            $options['results'][] = array(
                "name"    => __( "Archive pages", "advanced-woo-search" ),
                "desc"    => __( "Enable needed taxonomies / users search.", "advanced-woo-search" ),
                "tip"    => __( "Search for taxonomies and displayed their archive pages in search results.", "advanced-woo-search" ),
                "table_head" => __( 'Archive Pages', 'advanced-woo-search' ),
                "id"      => "search_archives",
                "inherit" => "true",
                "value" => array(
                    'archive_category' => 0,
                    'archive_tag'      => 0,
                    'archive_brand'    => 0,
                    'archive_tax'      => 0,
                    'archive_attr'     => 0,
                    'archive_users'    => 0,
                ),
                "choices" => array(
                    "archive_category"    => array(
                        'label'      => __( "Category", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "archive_tag"    => array(
                        'label'      => __( "Tag", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "archive_brand"    => array(
                        'label'      => __( "Brand", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "archive_tax"     => array(
                        'label'      => __( "Taxonomies", "advanced-woo-search" ),
                        'suboptions' => array(
                            'fields' => array(
                                "name"    => __( "Taxonomies", "advanced-woo-search" ),
                                "desc"    => '',
                                "tip"    => __( "Choose taxonomies archive pages that must be searchable.", "advanced-woo-search" ),
                                "id"      => "fields",
                                "field_id"    => "tax",
                                "value"   => array(),
                                "type"    => "terms_select"
                            ),
                        ),
                    ),
                    "archive_attr"     => array(
                        'label'      => __( "Attributes", "advanced-woo-search" ),
                        'suboptions' => array(
                            'fields' => array(
                                "name"    => __( "Attributes", "advanced-woo-search" ),
                                "desc"    => '',
                                "tip"    => __( "Choose attributes archive pages that must be searchable.", "advanced-woo-search" ),
                                "id"      => "fields",
                                "field_id"    => "attr",
                                "value"   => array(),
                                "type"    => "terms_select"
                            ),
                        ),
                    ),
                    "archive_users"     => array(
                        'label'      => __( "Users", "advanced-woo-search" ),
                        'suboptions' => array(
                            'fields' => array(
                                "name"    => __( "User Roles", "advanced-woo-search" ),
                                "desc"    => '',
                                "tip"    => __( "Choose user roles that will be available for search.", "advanced-woo-search" ),
                                "id"      => "fields",
                                "field_id"    => "user",
                                "value"   => array(),
                                "type"    => "terms_select"
                            ),
                        ),
                    ),
                ),
                "type"    => "table",
                "section" => "non_products",
            );

            $options['results'][] = array(
                "name"  => __( "Archive pages number", "advanced-woo-search" ),
                "desc"  => __( "Maximum displayed archive search results.", "advanced-woo-search" ),
                "id"    => "pages_results_num",
                "inherit" => "true",
                "value" => 10,
                "min" => 0,
                "type"  => "number",
                "section" => "non_products",
            );

            $options['results'][] = array(
                "name"    => __( "What Product Data to Display", "advanced-woo-search" ),
                "id"      => "view",
                "type"    => "heading",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Highlight words", "advanced-woo-search" ),
                "desc"  => __( "Highlight search words inside products content.", "advanced-woo-search" ),
                "id"    => "highlight",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Show image", "advanced-woo-search" ),
                "desc"  => __( "Show product image for each search result.", "advanced-woo-search" ),
                "id"    => "show_image",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Show description", "advanced-woo-search" ),
                "desc"  => __( "Show product description for each search result.", "advanced-woo-search" ),
                "id"    => "show_excerpt",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Show categories for results", "advanced-woo-search" ),
                "desc"  => __( "Include categories in products search results.", "advanced-woo-search" ),
                "id"    => "show_result_cats",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Show rating", "advanced-woo-search" ),
                "desc"  => __( "Show product rating.", "advanced-woo-search" ),
                "id"    => "show_rating",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Show featured", "advanced-woo-search" ),
                "desc"  => __( "Show featured badge near product title.", "advanced-woo-search" ),
                "id"    => "show_featured",
                "inherit" => "true",
                "value" => 'false',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Show variations attributes", "advanced-woo-search" ),
                "desc"  => __( "Show attributes for parent variable products.", "advanced-woo-search" ),
                "id"    => "show_variations",
                "inherit" => "true",
                "value" => 'false',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Show price", "advanced-woo-search" ),
                "desc"  => __( "Show product price for each search result.", "advanced-woo-search" ),
                "id"    => "show_price",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Show price for out of stock", "advanced-woo-search" ),
                "desc"  => __( "Show product price for out of stock products.", "advanced-woo-search" ),
                "id"    => "show_outofstock_price",
                "value" => 'true',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Show sale badge", "advanced-woo-search" ),
                "desc"  => __( "Show sale badge for products in search results.", "advanced-woo-search" ),
                "id"    => "show_sale",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Show product SKU", "advanced-woo-search" ),
                "desc"  => __( "Show product SKU in search results.", "advanced-woo-search" ),
                "id"    => "show_sku",
                "inherit" => "true",
                "value" => 'false',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Show product brand", "advanced-woo-search" ),
                "desc"  => __( "Show product brand values in search results.", "advanced-woo-search" ),
                "id"    => "show_brand",
                "value" => 'false',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Show product GTIN, UPC, EAN or ISBN", "advanced-woo-search" ),
                "desc"  => __( "Show product global unique id.", "advanced-woo-search" ),
                "id"    => "show_gtin",
                "value" => 'false',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Show 'Add to cart'", "advanced-woo-search" ),
                "desc"  => __( "Show 'Add to cart' button for each search result.", "advanced-woo-search" ),
                "id"    => "show_cart",
                "value" => 'false',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => '<span class="dashicons dashicons-editor-break aws-subopts-icon"></span>' . __( "Show quantity box", "advanced-woo-search" ),
                "desc"  => __( "Show 'Add to cart' button with quantity box.", "advanced-woo-search" ),
                "id"    => "show_cart_quantity",
                "value" => 'false',
                "type"  => "toggler",
                "depends_on" => array( "show_cart" => "true" ),
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => __( "Show stock status", "advanced-woo-search" ),
                "desc"  => __( "Show stock status for every product in search results.", "advanced-woo-search" ),
                "id"    => "show_stock",
                "inherit" => "true",
                "value" => 'false',
                "type"  => "toggler",
                "section" => "content",
            );

            $options['results'][] = array(
                "name"  => '<span class="dashicons dashicons-editor-break aws-subopts-icon"></span>' . __( "Show quantity", "advanced-woo-search" ),
                "desc"  => __( "Show quantity value near stock status.", "advanced-woo-search" ),
                "id"    => "show_stock_quantity",
                "inherit" => "true",
                "value" => 'false',
                "type"  => "toggler",
                "depends_on" => array( "show_stock" => "true" ),
                "section" => "content",
            );

            $options['results'][] = array(
                "name"    => __( "Filter Results", "advanced-woo-search" ),
                "id"      => "excludeinclude",
                "type"    => "heading",
                "section" => "filters",
            );

            $options['results'][] = array(
                "name"         => __( "Overview", "advanced-woo-search" ),
                'heading_type' => 'text',
                "desc"         => __( "Filtering rules for search results.", "advanced-woo-search" ),
                "tip"         => __( "Filter search results. You can include/exclude search results based on different rules.", "advanced-woo-search" ) . '<br>' .
                    __( "Combine filter rules to AND or OR logical blocks to create advanced filter logic.", "advanced-woo-search" ) . '<br>' .
                    __( "Please try not to use too many filters overwise this can impact on search speed.", "advanced-woo-search" ),
                "type"         => "heading",
                "section" => "filters",
            );

            $options['results'][] = array(
                "name"    => __( "Products results", "advanced-woo-search" ),
                "desc"    => '',
                "button"  => __( "Filter products search results", "advanced-woo-search" ),
                "id"      => "adv_filters",
                "filter"  => "product",
                "value"   => '',
                "type"    => "filter_rules",
                "section" => "filters",
            );

            $options['results'][] = array(
                "name"    => __( "Terms results", "advanced-woo-search" ),
                "desc"    => '',
                "button"  => __( "Filter taxonomies archive pages results", "advanced-woo-search" ),
                "id"      => "adv_filters",
                "filter"  => "term",
                "value"   => '',
                "type"    => "filter_rules",
                "section" => "filters",
            );

            $options['results'][] = array(
                "name"    => __( "Users results", "advanced-woo-search" ),
                "desc"    => '',
                "button"  => __( "Filter users archive pages search results", "advanced-woo-search" ),
                "id"      => "adv_filters",
                "filter"  => "user",
                "value"   => '',
                "type"    => "filter_rules",
                "section" => "filters",
            );

            $options['general'][] = array(
                "name"    => __( "Search Engine Settings", "advanced-woo-search" ),
                "type"    => "heading",
                'id' => 'search_engine',
                "section" => "engine",
            );

            $options['general'][] = array(
                "name"    => __( "Search in", "advanced-woo-search" ),
                "desc"    => '',
                "tip"    =>  __( "Product fields to search in.", "advanced-woo-search" ) . __( "Click on the status icon to enable or disable the search source for products search.", "advanced-woo-search" ),
                "id"      => "search_in",
                "inherit" => "true",
                "value" => array(
                    'title'    => 1,
                    'content'  => 1,
                    'sku'      => 1,
                    'gtin'     => 1,
                    'excerpt'  => 1,
                    'brand'    => 1,
                    'category' => 0,
                    'tag'      => 0,
                    'id'       => 0,
                    'attr'     => 0,
                    'tax'      => 0,
                    'meta'     => 0,
                ),
                "choices" => array(
                    "title"    => array(
                        'label'      => __( "Title", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "content"  => array(
                        'label'      => __( "Content", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "sku"      => array(
                        'label'      => __( "SKU", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "gtin"  => array(
                        'label'      => __( "GTIN, UPC, EAN, ISBN", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "excerpt"  => array(
                        'label'      => __( "Short description", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "brand"    => array(
                        'label'      => __( "Brand", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "category"     => array(
                        'label'      => __( "Category", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "tag"     => array(
                        'label'      => __( "Tag", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "id"     => array(
                        'label'      => __( "ID", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "attr"     => array(
                        'label'      => __( "Attributes", "advanced-woo-search" ),
                        'suboptions' => array(
                            'fields' => array(
                                "name"    => __( "Attributes", "advanced-woo-search" ),
                                "desc"    => '',
                                "tip"    => __( "Choose product attributes that must be searchable.", "advanced-woo-search" ),
                                "id"      => "fields",
                                "field_id"    => "attr",
                                "value"   => array(),
                                "type"    => "terms_select"
                            ),
                        ),
                    ),
                    "tax"      => array(
                        'label'      => __( "Taxonomies", "advanced-woo-search" ),
                        'suboptions' => array(
                            'fields' => array(
                                "name"    => __( "Taxonomies", "advanced-woo-search" ),
                                "tip"    => __( "Choose product taxonomies that must be searchable.", "advanced-woo-search" ),
                                "id"      => "fields",
                                "field_id"    => "tax",
                                "value"   => array(),
                                "type"    => "terms_select"
                            ),
                        ),
                    ),
                    "meta"     => array(
                        'label'      => __( "Custom Fields", "advanced-woo-search" ),
                        'suboptions' => array(
                            'fields' => array(
                                "name"    => __( "Fields", "advanced-woo-search" ),
                                "tip"     => __( "Choose product custom fields that must be searchable.", "advanced-woo-search" ),
                                "id"      => "fields",
                                "field_id" => "meta",
                                "value"   => array(),
                                "type"    => "terms_select"
                            ),
                        ),
                    ),
                ),
                "type"    => "table",
                "section" => "engine",
            );

            $options['general'][] = array(
                "name"  => __( "Search logic", "advanced-woo-search" ),
                "desc"  => __( "Search rules.", "advanced-woo-search" ),
                "id"    => "search_logic",
                "value" => 'or',
                "type"  => "select",
                'choices' => array(
                    'or'  => __( 'OR. Show result if at least one word exists in product.', 'advanced-woo-search' ),
                    'and'  => __( 'AND. Show result if only all words exists in product.', 'advanced-woo-search' ),
                    //'exact'  => __( 'EXACT MATCH', 'Show result if product contains exact same phrase.' )
                ),
                "section" => "engine",
            );

            $options['general'][] = array(
                "name"  => __( "Exact match", "advanced-woo-search" ),
                "desc"  => __( "Whole or partial search words matching.", "advanced-woo-search" ),
                "tip" => __( "Search only for full word matching or display results even if they match only part of word.", "advanced-woo-search" ),
                "id"    => "search_exact",
                "value" => 'false',
                "type"  => "select",
                'choices' => array(
                    'true'  => __( 'Yes. Search only for full words matching.', 'advanced-woo-search' ),
                    'false'  => __( 'No. Partial words match search.', 'advanced-woo-search' ),
                ),
                "section" => "engine",
            );

            $options['general'][] = array(
                "name"  => __( "Misspelling fix", "advanced-woo-search" ),
                "desc"  => sprintf( __( "Fix typos inside search words %s.", "advanced-woo-search" ), '( lapto<b>t</b> -> lapto<b>p</b> )' ) . '<br>' . __( "Applied if the current search query returns no results.", "advanced-woo-search" ),
                "id"    => "fuzzy",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "select",
                'choices' => array(
                    'true'  => __( 'On. Automatically search for fixed terms.', 'advanced-woo-search' ),
                    'true_text'  => __( "On. Additionally show text \"Showing results for ...\" with a list of fixed terms at the top of search results.", 'advanced-woo-search' ),
                    'false'  => __( 'Off. Totally disable misspelling fix.', 'advanced-woo-search' ),
                    'false_text'  => __( "Off. Instead show text \"Did you mean ...\" with a clickable list of fixed terms at the top of search results.", 'advanced-woo-search' ),
                ),
                "section" => "engine",
            );

            $options['general'][] = array(
                "name"  => __( "Description source", "advanced-woo-search" ),
                "desc"  => __( "From where to take product description.", "advanced-woo-search" ),
                "tip" => __( "If first source is empty data will be taken from other sources.", "advanced-woo-search" ),
                "id"    => "desc_source",
                "inherit" => "true",
                "value" => 'content',
                "type"  => "select",
                'choices' => array(
                    'content'  => __( 'Content', 'advanced-woo-search' ),
                    'excerpt'  => __( 'Short description', 'advanced-woo-search' ),
                ),
                "section" => "engine",
            );

            $options['general'][] = array(
                "name"  => __( "Open product in new tab", "advanced-woo-search" ),
                "desc"  => __( "Open new window on product click.", "advanced-woo-search" ),
                "id"    => "target_blank",
                "inherit" => "true",
                "value" => 'false',
                "type"  => "toggler",
                "section" => "engine",
            );

            $options['general'][] = array(
                "name"  => __( "Image source", "advanced-woo-search" ),
                "desc"  =>  __( "Priority order for product image sources.", "advanced-woo-search" ),
                "tip"   => __( "Source of image that will be shown with search results. Position of fields show the priority of each source.", "advanced-woo-search" ),
                "id"    => "image_source",
                "value" => array(
                    'featured'    => 1,
                    'gallery'     => 1,
                    'content'     => 1,
                    'description' => 1,
                    'default'     => 0,
                ),
                "choices" => array(
                    "featured"    => __( "Featured image", "advanced-woo-search" ),
                    "gallery"     => __( "Gallery first image", "advanced-woo-search" ),
                    "content"     => __( "Content first image", "advanced-woo-search" ),
                    "description" => __( "Description first image", "advanced-woo-search" ),
                    "default"     => __( "Default image", "advanced-woo-search" )
                ),
                "type"  => "sortable",
                "section" => "engine",
            );

            $options['general'][] = array(
                "name"  => __( "Default image", "advanced-woo-search" ),
                "desc"  => __( "Default image for search results.", "advanced-woo-search" ),
                "tip"   => __( "Value for Default Image from previous option.", "advanced-woo-search" ),
                "id"    => "default_img",
                "value" => "",
                "type"  => "image",
                'size'  => "thumbnail",
                "section" => "engine",
            );

            $options['general'][] = array(
                "name"  => __( "Use Google Analytics", "advanced-woo-search" ),
                "desc"   => __( "Enable Google Analytics integration.", "advanced-woo-search" ) . ' <a href="https://advanced-woo-search.com/guide/google-analytics/" target="_blank">' . __( 'More info', 'advanced-woo-search' ) . '</a>',
                "tip"  => __( "Use google analytics to track searches. You need google analytics to be installed on your site.", "advanced-woo-search" ) .
                    '<br>' . __( "Data will be visible inside Google Analytics 'Site Search' report. Need to activate 'Site Search' feature inside GA.", "advanced-woo-search" ) .
                    '<br>' . __( "Also will send event with category - 'AWS search', action - 'AWS Search Form {form_id}' and label of value of search term.", "advanced-woo-search" ),
                "id"    => "use_analytics",
                "inherit" => "true",
                "value" => 'false',
                "type"  => "toggler",
                "section" => "engine",
            );

            $options['general'][] = array(
                "name"    => __( "Search Results Page", "advanced-woo-search" ),
                "type"    => "heading",
                "id" => 'search_page',
                "section" => "page",
            );

            $options['general'][] = array(
                "name"  => __( "Enable results page", "advanced-woo-search" ),
                "desc"  => __( "Search results page support.", "advanced-woo-search" ),
                "tip" => __( "Show plugin search results on a separated search results page. Will use your current theme products search results page template.", "advanced-woo-search" ),
                "id"    => "search_page",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "toggler",
                "section" => "page",
            );

            $options['general'][] = array(
                "name"  => __( "Max number of results", "advanced-woo-search" ),
                "tip" => __( "Applied only for search results page, not AJAX live results. Larger values can lead to slower search speed.", "advanced-woo-search" ),
                "desc"  => __( "Max total number of results for results page.", "advanced-woo-search" ),
                "id"    => "search_page_res_num",
                "inherit" => "true",
                "value" => 100,
                "min" => 0,
                "type"  => "number",
                "section" => "page",
            );

            $options['general'][] = array(
                "name"  => __( "Results per page", "advanced-woo-search" ),
                "desc"  => __( "Number of search results per results page.", "advanced-woo-search" ),
                "tip"   => __( "Empty or 0 - use theme default value.", "advanced-woo-search" ),
                "id"    => "search_page_res_per_page",
                "inherit" => "true",
                "value" => '',
                "min" => 0,
                "type"  => "number",
                "section" => "page",
            );

            $options['general'][] = array(
                "name"  => __( "Change query hook", "advanced-woo-search" ),
                "desc"  => __( "Default query hook for results page.", "advanced-woo-search" ),
                "tip"  => __( "If you have any problems with correct products results on the search results page - try to change this option.", "advanced-woo-search" ),
                "id"    => "search_page_query",
                "inherit" => "true",
                "value" => 'default',
                "type"  => "select",
                'choices' => array(
                    'default' => __( 'Default', 'advanced-woo-search' ),
                    'posts_pre_query' => __( 'posts_pre_query', 'advanced-woo-search' ),
                ),
                "section" => "page",
            );

            $options['general'][] = array(
                "name"  => __( "Highlight words", "advanced-woo-search" ),
                "desc"  => __( "Highlight search terms inside results page.", "advanced-woo-search" ),
                "id"    => "search_page_highlight",
                "inherit" => "true",
                "value" => 'false',
                "type"  => "toggler",
                "section" => "page",
            );

            $options = self::inherit_free_options( $options );

            /**
             * Filter admin page options
             * @since 2.15
             * @param array $options Array of options
             */
            $options = apply_filters( 'aws_admin_page_options', $options );

            return $options;

        }

        /*
         * Include filter conditions
         * @return array
         */
        static public function include_filters() {

            $options = array();

            $options['product'][] = array(
                "name" => __( "Product", "advanced-woo-search" ),
                "id"   => "product",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    "callback" => "AWS_Admin_Filters_Helpers::search_for_products",
                    'params' => array(),
                    'ajax' => array(
                        "action" => "aws-getSelectOptionValues",
                        "input" => 3,
                        "placeholder" => __( "Search for a product...", "advanced-woo-search" ),
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_product",
                    ),
                ),
            );

            $options['product'][] = array(
                "name" => __( "Product type", "advanced-woo-search" ),
                "id"   => "product_type",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_product_types',
                    'params'   => array()
                ),
            );

            $options['product'][] = array(
                "name" => __( "Product stock status", "advanced-woo-search" ),
                "id"   => "product_stock_status",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_stock_statuses',
                    'params'   => array()
                ),
            );

            $options['product'][] = array(
                "name" => __( "Product is on sale", "advanced-woo-search" ),
                "id"   => "product_sale_status",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['product'][] = array(
                "name" => __( "Product is featured", "advanced-woo-search" ),
                "id"   => "product_featured",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['product'][] = array(
                "name" => __( "Product is in cart", "advanced-woo-search" ),
                "id"   => "product_is_in_cart",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['product'][] = array(
                "name" => __( "Product has image", "advanced-woo-search" ),
                "id"   => "product_has_image",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['product'][] = array(
                "name" => __( "Product has gallery", "advanced-woo-search" ),
                "id"   => "product_has_gallery",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['product'][] = array(
                "name" => __( "Product SKU", "advanced-woo-search" ),
                "id"   => "product_sku",
                "type" => "text",
                "placeholder" => __( "Product SKU", "advanced-woo-search" ),
                "operators" => "equals_contains",
            );

            $options['product'][] = array(
                "name" => __( "Product GTIN, UPC, EAN, or ISBN", "advanced-woo-search" ),
                "id"   => "product_gtin",
                "type" => "text",
                "placeholder" => __( "Product GTIN, UPC, EAN, or ISBN", "advanced-woo-search" ),
                "operators" => "equals_contains",
            );

            $options['product'][] = array(
                "name" => __( "Product visibility", "advanced-woo-search" ),
                "id"   => "product_visibility",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_visibilities',
                    'params'   => array()
                ),
            );

            $options['product'][] = array(
                "name" => __( "Product brand", "advanced-woo-search" ),
                "id"   => "product_brand",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_tax_terms',
                    'params'   => array( 'product_brand' ),
                    'ajax'     => array(
                        "action" => "aws-getSelectOptionValues",
                        "placeholder" => __( "Name", "advanced-woo-search" ),
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_tax_term",
                        "input" => 0,
                    ),
                ),
            );

            $options['product'][] = array(
                "name" => __( "Product category", "advanced-woo-search" ),
                "id"   => "product_category",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_tax_terms',
                    'params'   => array( 'product_cat' ),
                    'ajax'     => array(
                        "action" => "aws-getSelectOptionValues",
                        "placeholder" => __( "Name", "advanced-woo-search" ),
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_tax_term",
                        "input" => 0,
                    ),
                ),
            );

            $options['product'][] = array(
                "name" => __( "Product tag", "advanced-woo-search" ),
                "id"   => "product_tag",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_tax_terms',
                    'params'   => array( 'product_tag' ),
                    'ajax'     => array(
                        "action" => "aws-getSelectOptionValues",
                        "placeholder" => __( "Name", "advanced-woo-search" ),
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_tax_term",
                        "input" => 0,
                    ),
                ),
            );

            $options['product'][] = array(
                "name" => __( "Product taxonomy", "advanced-woo-search" ),
                "id"   => "product_taxonomy",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_tax_terms',
                    'params'   => array(),
                    'ajax'     => array(
                        "action" => "aws-getSelectOptionValues",
                        "placeholder" => __( "Name", "advanced-woo-search" ),
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_tax_term",
                        "input" => 0,
                    ),
                ),
                "suboption" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_tax',
                    'params'   => array(),
                    'ajax'     => array(
                        "action" => "aws-getSelectOptionValues",
                        "placeholder" => __( "Taxonomy name", "advanced-woo-search" ),
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_tax_name_by_slug",
                        "input" => 0,
                    ),
                ),
            );

            $options['product'][] = array(
                "name" => __( "Product attributes", "advanced-woo-search" ),
                "id"   => "product_attributes",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_tax_terms',
                    'params'   => array(),
                    'ajax'     => array(
                        "action" => "aws-getSelectOptionValues",
                        "placeholder" => __( "Name", "advanced-woo-search" ),
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_tax_term",
                        "input" => 0,
                    ),
                ),
                "suboption" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_attributes',
                    'params'   => array(),
                    'ajax'     => array(
                        "action" => "aws-getSelectOptionValues",
                        "placeholder" => __( "Attribute name", "advanced-woo-search" ),
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_tax_name_by_slug",
                        "input" => 0,
                    ),
                ),
            );

            $options['product'][] = array(
                "name" => __( "Product custom attributes", "advanced-woo-search" ),
                "id"   => "product_custom_attributes",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_custom_attributes',
                    'params'   => array(),
                    'ajax'     => array(
                        "action" => "aws-getSelectOptionValues",
                        "placeholder" => __( "Value", "advanced-woo-search" ),
                        "input" => 0,
                    ),
                ),
                "suboption" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_custom_attributes',
                    'params'   => array(),
                    'ajax'     => array(
                        "action" => "aws-getSelectOptionValues",
                        "placeholder" => __( "Attribute name", "advanced-woo-search" ),
                        "input" => 0,
                    ),
                ),
            );

            $options['product'][] = array(
                "name" => __( "Product custom fields", "advanced-woo-search" ),
                "id"   => "product_meta",
                "type" => "text",
                "operators" => "equals_contains_compare",
                "placeholder" => __( "Field value. Empty = any field value", "advanced-woo-search" ),
                "suboption" => array(
                    "callback" => "AWS_Admin_Filters_Helpers::get_custom_fields",
                    'params' => array(),
                    'ajax'     => array(
                        "action" => "aws-getSelectOptionValues",
                        "placeholder" => __( "Field name", "advanced-woo-search" ),
                        "input" => 3,
                    ),
                ),
            );

            $options['product'][] = array(
                "name" => __( "Product shipping class", "advanced-woo-search" ),
                "id"   => "product_shipping_class",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_tax_terms',
                    'params'   => array( 'product_shipping_class' ),
                    'ajax'     => array(
                        "action" => "aws-getSelectOptionValues",
                        "placeholder" => __( "Name", "advanced-woo-search" ),
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_tax_term",
                        "input" => 0,
                    ),
                ),
            );

            $options['term'][] = array(
                "name" => __( "Term products count", "advanced-woo-search" ),
                "id"   => "term_count",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['term'][] = array(
                "name" => __( "Term page taxonomy", "advanced-woo-search" ),
                "id"   => "term_taxonomy",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_tax_terms',
                    'params'   => array(),
                    'ajax'     => array(
                        "action" => "aws-getSelectOptionValues",
                        "placeholder" => __( "Name", "advanced-woo-search" ),
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_tax_term",
                        "input" => 0,
                    ),
                ),
                "suboption" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_all_tax',
                    'params'   => array(),
                    'ajax'     => array(
                        "action" => "aws-getSelectOptionValues",
                        "placeholder" => __( "Taxonomy name", "advanced-woo-search" ),
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_tax_name_by_slug",
                        "input" => 0,
                    ),
                ),
            );

            $options['term'][] = array(
                "name" => __( "Term hierarchy type", "advanced-woo-search" ),
                "id"   => "term_hierarchy",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_terms_hierarchy',
                    'params'   => array()
                ),
            );

            $options['term'][] = array(
                "name" => __( "Term has image", "advanced-woo-search" ),
                "id"   => "term_has_image",
                "type" => "bool",
                "operators" => "equals",
            );

            $options['user'][] = array(
                "name" => __( "User", "advanced-woo-search" ),
                "id"   => "user_page_user",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    "callback" => "AWS_Admin_Filters_Helpers::get_users",
                    'params' => array(),
                    'ajax' => array(
                        "action" => "aws-getSelectOptionValues",
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_user_by_id",
                        "placeholder" => __( "User name", "advanced-woo-search" ),
                        "input" => 3,
                    ),
                ),
            );

            $options['user'][] = array(
                "name" => __( "User role", "advanced-woo-search" ),
                "id"   => "user_page_role",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_user_roles',
                    'params'   => array()
                ),
            );
            
            $options['user'][] = array(
                "name" => __( "User products count", "advanced-woo-search" ),
                "id"   => "user_page_count",
                "type" => "number",
                "operators" => "equals_compare",
            );

            $options['current_user'][] = array(
                "name" => __( "Current user", "advanced-woo-search" ),
                "id"   => "current_user",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    "callback" => "AWS_Admin_Filters_Helpers::get_users",
                    'params' => array(),
                    'ajax' => array(
                        "action" => "aws-getSelectOptionValues",
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_user_by_id",
                        "placeholder" => __( "User name", "advanced-woo-search" ),
                        "input" => 3,
                    ),
                ),
            );

            $options['current_user'][] = array(
                "name" => __( "Current user role", "advanced-woo-search" ),
                "id"   => "current_user_role",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_user_roles',
                    'params'   => array()
                ),
            );

            $options['current_user'][] = array(
                "name" => __( "Current user device", "advanced-woo-search" ),
                "id"   => "current_user_device",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_user_devices',
                    'params'   => array()
                ),
            );

            $options['current_page'][] = array(
                "name" => __( "Current page", "advanced-woo-search" ),
                "id"   => "current_page",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    "callback" => "AWS_Admin_Filters_Helpers::get_pages",
                    'params' => array(),
                    'ajax' => array(
                        "action" => "aws-getSelectOptionValues",
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_page_by_id",
                        "placeholder" => __( "Page name", "advanced-woo-search" ),
                        "input" => 3,
                    ),
                ),
            );

            $options['current_page'][] = array(
                "name" => __( "Current page template", "advanced-woo-search" ),
                "id"   => "current_page_template",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_page_templates',
                    'params'   => array()
                ),
            );

            $options['current_page'][] = array(
                "name" => __( "Current page type", "advanced-woo-search" ),
                "id"   => "current_page_type",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_page_type',
                    'params'   => array()
                ),
            );

            $options['current_page'][] = array(
                "name" => __( "Current page archives", "advanced-woo-search" ),
                "id"   => "current_page_archives",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_page_archive_terms',
                    'params'   => array(),
                    'ajax' => array(
                        "action" => "aws-getSelectOptionValues",
                        "value_callback"  => "AWS_Admin_Filters_Helpers::get_tax_term",
                        "placeholder" => __( "Name", "advanced-woo-search" ),
                        "input" => 0,
                    ),
                ),
                "suboption" => array(
                    'callback' => 'AWS_Admin_Filters_Helpers::get_page_archives',
                    'params'   => array()
                ),
            );

            $options['current_page'][] = array(
                "name" => __( "Current page URL", "advanced-woo-search" ),
                "id"   => "current_page_url",
                "type" => "text",
                "placeholder" => __( "Page URL", "advanced-woo-search" ),
                "operators" => "equals_contains",
            );

            $options['current_search'][] = array(
                "name" => __( "Current search text", "advanced-woo-search" ),
                "id"   => "current_search_terms",
                "type" => "text",
                "placeholder" => __( "Search text", "advanced-woo-search" ),
                "operators" => "equals_contains",
            );

            /**
             * Filter filter rules
             * @since 2.45
             * @param array $options Array of filter rules
             */
            $options = apply_filters( 'aws_admin_filter_rules', $options );

            return $options;

        }

        /*
         * Get an array of search form admin tabs names
         * @return array
         */
        static public function get_instance_tabs_names() {

            $tabs = array(
                'general' => __( 'Search Config', 'advanced-woo-search' ),
                'form'    => __( 'Search Bar', 'advanced-woo-search' ),
                'suggestions' => __( 'Search Suggestions', 'advanced-woo-search' ),
                'results' => __( 'Search Results', 'advanced-woo-search' )
            );

            /**
             * Filter tabs names for search form instance
             * @since 3.37
             * @param array $tabs Array of tabs names
             */
            $tabs = apply_filters( 'aws_admin_instance_tabs_names', $tabs );

            return $tabs;

        }

        /*
         * Get an array of search form admin sections names
         * @return array
         */
        static public function get_sections_names() {

            $sections = array(
                'main'     => esc_html__( 'Common', 'advanced-woo-search' ),
                'engine'   => esc_html__( 'Search Engine', 'advanced-woo-search' ),
                'page'     => esc_html__( 'Search Page', 'advanced-woo-search' ),
                'styles'   => esc_html__( 'Styles', 'advanced-woo-search' ),
                'filters'   => esc_html__( 'Filters', 'advanced-woo-search' ),
                'quick_filters' => esc_html__( 'Quick Filters', 'advanced-woo-search' ),
                'content'   => esc_html__( 'Content', 'advanced-woo-search' ),
                'non_products' => esc_html__( 'Non-Products Results', 'advanced-woo-search' ),
            );

            /**
             * Filter sections names for search form instance
             * @since 3.46
             * @param array $sections Array of sections names
             */
            $sections = apply_filters( 'aws_admin_instance_sections_names', $sections );

            return $sections;

        }

        /*
         * Inherit some options from the free plugin version if exists
         * @return array
         */
        static public function inherit_free_options( $options ) {

            $current_version = get_option( 'aws_pro_plugin_ver' );
            $settings = self::get_settings();
            $common_settings = self::get_common_settings();
            $free_settings = get_option( 'aws_settings' );

            // First activation
            if ( ! $current_version ) {

                if ( $free_settings && ( ! $settings || ! $common_settings ) ) {

                    $outofstock = isset( $free_settings['outofstock'] ) ? $free_settings['outofstock'] : false;

                    foreach ( $options as $tab_name => $tab_options ) {
                        foreach( $tab_options as $option_key => $tab_option ) {

                            if ( isset( $tab_option['id'] ) && isset( $tab_option['inherit'] ) ) {

                                $option_id = ( $tab_option['inherit'] && $tab_option['inherit'] !== 'true' ) ? $tab_option['inherit'] : $tab_option['id'];

                                // inherit options values from table
                                if ( isset( $tab_option['choices'] ) && is_array( $tab_option['choices'] ) && isset( $tab_option['value'] ) && is_array( $tab_option['value'] ) ) {
                                    foreach ( $tab_option['choices'] as $choices_name => $choices_value ) {
                                        if ( isset( $tab_option['value'][$choices_name] ) && is_int( $tab_option['value'][$choices_name] ) ) {
                                            $free_val = 0;
                                            if ( isset( $free_settings[$option_id] ) && isset( $free_settings[$option_id][$choices_name] ) ) {
                                                if ( is_array( $free_settings[$option_id][$choices_name] ) ) {
                                                    if (  isset( $free_settings[$option_id][$choices_name]['value'] ) && $free_settings[$option_id][$choices_name]['value'] === '1' ) {
                                                        $free_val = 1;
                                                    }
                                                } else {
                                                    $free_val = intval( $free_settings[$option_id][$choices_name] );
                                                }
                                            }
                                            $options[$tab_name][$option_key]['value'][$choices_name] = $free_val;
                                        }
                                    }
                                } elseif ( isset( $free_settings[$option_id] ) ) {
                                    $options[$tab_name][$option_key]['value'] = $free_settings[$option_id];
                                }

                            }

                            if ( isset( $tab_option['id'] ) && $tab_option['id'] === 'product_stock_status' && $outofstock && $outofstock === 'false' ) {
                                $options[$tab_name][$option_key]['value']['out_of_stock'] = 0;
                            }

                        }
                    }

                    $seamless = get_option( 'aws_pro_seamless' );
                    if ( $seamless === false && isset( $free_settings['seamless'] ) && $free_settings['seamless'] ) {
                        update_option( 'aws_pro_seamless', $free_settings['seamless'] );
                    }

                }

            }

            return $options;

        }

    }

endif;