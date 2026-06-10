<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! class_exists( 'AWS_Admin_Filters_Helpers' ) ) :

    /**
     * Class for plugin help methods
     */
    class AWS_Admin_Filters_Helpers {

        /*
         * Get available price formats
         * @return array
         */
        static public function get_price() {

            $options = array();

            $values = array(
                'current' => __( 'Current', 'advanced-woo-search' ),
                'sale'    => __( 'Sale', 'advanced-woo-search' ),
                'regular' => __( 'Regular', 'advanced-woo-search' ),
            );

            foreach ( $values as $value_val => $value_name ) {
                $options[$value_val] = $value_name;
            }

            return $options;

        }

        /*
         * Get available stock statuses
         * @return array
         */
        static public function get_stock_statuses() {

            $options = array();

            if ( function_exists( 'wc_get_product_stock_status_options' ) ) {
                $values = wc_get_product_stock_status_options();
            } else {
                $values = apply_filters(
                    'woocommerce_product_stock_status_options',
                    array(
                        'instock'     => __( 'In stock', 'woocommerce' ),
                        'outofstock'  => __( 'Out of stock', 'woocommerce' ),
                        'onbackorder' => __( 'On backorder', 'woocommerce' ),
                    )
                );
            }

            foreach ( $values as $value_val => $value_name ) {
                $options[$value_val] = $value_name;
            }

            return $options;

        }

        /*
         * Get available product visibilities
         * @return array
         */
        static public function get_visibilities() {

            $options = array();

            if ( function_exists( 'wc_get_product_visibility_options' ) ) {
                $values = wc_get_product_visibility_options();
            } else {
                $values = apply_filters(
                    'woocommerce_product_visibility_options',
                    array(
                        'visible' => __( 'Shop and search results', 'woocommerce' ),
                        'catalog' => __( 'Shop only', 'woocommerce' ),
                        'search'  => __( 'Search results only', 'woocommerce' ),
                        'hidden'  => __( 'Hidden', 'woocommerce' ),
                    )
                );
            }

            foreach ( $values as $value_val => $value_name ) {
                $options[$value_val] = $value_name;
            }

            return $options;

        }

        /*
         * Get available product types
         * @return array
         */
        static public function get_product_types() {

            $options = array();

            if ( function_exists( 'wc_get_product_types' ) ) {
                $values = wc_get_product_types();
            } else {
                $values = apply_filters(
                    'product_type_selector',
                    array(
                        'simple'   => __( 'Simple product', 'woocommerce' ),
                        'grouped'  => __( 'Grouped product', 'woocommerce' ),
                        'external' => __( 'External/Affiliate product', 'woocommerce' ),
                        'variable' => __( 'Variable product', 'woocommerce' ),
                    )
                );
            }

            $values['variation']  = __( 'Product variation', 'advanced-woo-search' );

            foreach ( $values as $value_val => $value_name ) {
                $options[$value_val] = $value_name;
            }

            return $options;

        }

        /*
         * Get available products
         * @return array
         */
        static public function get_products() {

            $options = array();

            $options['aws_any'] = __( "Any product", "advanced-woo-search" );

            $args = array(
                'posts_per_page' => -1,
                'post_type'      => 'product'
            );

            $products = get_posts( $args );

            if ( ! empty( $products ) ) {
                foreach ( $products as $product ) {
                    $options[$product->ID] = $product->post_title;
                }
            }

            return $options;

        }

        /*
         * Search for products via product name
         * @param string $name Search string
         * @return array
         */
        static public function search_for_products( $name = '' ) {

            $products = array();
            $products[] = array(
                'id' => 'aws_any',
                'text' => __( "Any product", "advanced-woo-search" )
            );

            $include_variations = false;
            $limit = 30;

            if ( class_exists('WC_Data_Store') ) {

                $data_store = WC_Data_Store::load( 'product' );
                $ids        = $data_store->search_products( $name, '', (bool) $include_variations, false, $limit, array(), array() );

                foreach ( $ids as $id ) {

                    $product_object = wc_get_product( $id );

                    if ( ! wc_products_array_filter_readable( $product_object ) ) {
                        continue;
                    }

                    $formatted_name = $product_object->get_formatted_name();
                    $products[] = array(
                        'id' => $product_object->get_id(),
                        'text' => rawurldecode( wp_strip_all_tags( $formatted_name ) )
                    );

                }

            }

            return $products;

        }

        /*
         * Get specific product
         * @return array
         */
        static public function get_product( $id = 0 ) {

            $value = $id;

            if ( $id === 'aws_any' ) {
                $value = __( "Any product", "advanced-woo-search" );
                return $value;
            }

            if ( $id ) {
                $product_object = wc_get_product( $id );
                if ( $product_object ) {
                    $formatted_name = $product_object->get_formatted_name();
                    $value = rawurldecode( wp_strip_all_tags( $formatted_name ) );
                }
            }

            return $value;

        }

        /*
         * Get available taxonomies
         * @return array
         */
        static public function get_tax() {

            $taxonomy_objects = get_object_taxonomies( 'product', 'objects' );
            $options = array();

            foreach( $taxonomy_objects as $taxonomy_object ) {
                if ( in_array( $taxonomy_object->name, array( 'product_cat', 'product_tag', 'product_type', 'product_visibility', 'product_shipping_class' ) ) ) {
                    continue;
                }

                if ( strpos( $taxonomy_object->name, 'pa_' ) === 0 ) {
                    continue;
                }

                $options[] = array(
                    'text'  => $taxonomy_object->label,
                    'id' => $taxonomy_object->name
                );

            }

            return $options;

        }

        /*
        * Get all available taxonomies
        * @return array
        */
        static public function get_all_tax() {

            $taxonomy_objects = get_object_taxonomies( 'product', 'objects' );
            $options = array();

            foreach( $taxonomy_objects as $taxonomy_object ) {
                if ( in_array( $taxonomy_object->name, array( 'product_type', 'product_visibility', 'product_shipping_class' ) ) ) {
                    continue;
                }

                $text = isset( $taxonomy_object->labels ) && is_object( $taxonomy_object->labels ) ? $taxonomy_object->labels->singular_name : $taxonomy_object->label;

                $options[] = array(
                    'text'  => $text,
                    'id' => $taxonomy_object->name
                );

            }

            return $options;

        }

        /*
        * Get available taxonomies_terms
        * @param $name string Tax name
        * @return array
        */
        static public function get_tax_terms( $name = false ) {

            if ( ! $name ) {
                return false;
            }

            $tax = get_terms( array(
                'taxonomy'   => $name,
                'hide_empty' => false,
            ) );

            $options = array();

            if ( ! empty( $tax ) ) {
                foreach ( $tax as $tax_item ) {

                    $options[] = array(
                        'id'  => $tax_item->term_id,
                        'text' => $tax_item->name
                    );

                }
            }

            /**
             * Filter options array of taxonomy terms
             * @since 2.79
             * @param array $options Terms array
             * @param string $name Taxonomy name
             */
            $options = apply_filters( 'aws_options_get_tax_terms', $options, $name );

            return $options;

        }

        static public function get_tax_term( $taxonomy = '', $term_id = 0 ) {

            $value = $term_id;

            if ( $taxonomy && $term_id ) {
                $term = get_term( $term_id, $taxonomy );
                if ( ! is_wp_error( $term ) && $term ) {
                    $value =  $term->name;
                }
            }

            return $value;

        }

        /*
         * Get available product attributes
         * @return array
         */
        static public function get_attributes() {

            $options = array();

            if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
                $attributes = wc_get_attribute_taxonomies();
                if ( $attributes && ! empty( $attributes ) ) {
                    foreach( $attributes as $attribute ) {
                        $attribute_name = wc_attribute_taxonomy_name( $attribute->attribute_name );
                        $options[] = array(
                            'text'  => $attribute->attribute_label,
                            'id' => $attribute_name
                        );
                    }
                }

            }

            return $options;

        }

        /*
         * Get specific taxonomy name by slug
         * @param string $slug Attribute slug
         * @return string
         */
        static public function get_tax_name_by_slug( $slug ) {

            $value = $slug;

            $taxonomy = get_taxonomy( $slug );

            if ( $taxonomy && $taxonomy->labels ) {
                $value = $taxonomy->labels->singular_name;
            }

            return $value;

        }

        /*
         * Get available product custom attributes
         * @return array
         */
        static public function get_custom_attributes( $name = '' ) {

            global $wpdb;

            $options = array();
            $attributes = array();
            $custom_attributes = $wpdb->get_results( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_product_attributes'" );

            if ( ! empty( $custom_attributes ) && !is_wp_error( $custom_attributes ) ) {
                foreach ( $custom_attributes as $custom_attribute ) {
                    if ( $custom_attribute->meta_value ) {
                        $custom_attribute_array = maybe_unserialize( $custom_attribute->meta_value );

                        if ( $custom_attribute_array && is_array( $custom_attribute_array ) && ! empty( $custom_attribute_array ) ) {

                            foreach ($custom_attribute_array as $custom_attribute_key => $custom_attribute_val) {

                                if ( isset( $custom_attribute_val['is_taxonomy'] ) && $custom_attribute_val['is_taxonomy'] ) {
                                    continue;
                                }

                                $attributes[$custom_attribute_key]['name'] = $custom_attribute_val['name'];

                                $val_array = array_map( 'trim', explode( '|', $custom_attribute_val['value'] ) );

                                if ( $val_array && ! empty( $val_array ) ) {
                                    foreach( $val_array as $val_array_attr ) {
                                        $val_array_attr_key = sanitize_key( strval( $val_array_attr ) );
                                        $attributes[$custom_attribute_key]['val'][$val_array_attr_key] = $val_array_attr;
                                    }
                                }

                            }

                        }

                    }
                }
            }

            if ( ! empty( $attributes ) ) {

                foreach( $attributes as $attribute_slug => $attribute ) {

                    if ( $name ) {
                        if ( $name === $attribute_slug && isset( $attribute['val'] ) ) {
                            foreach( $attribute['val'] as $val_key => $val ) {
                                $options[] = array(
                                    'text'  => $val,
                                    'id' => $val_key
                                );
                            }
                        }
                    } else {
                        $options[] = array(
                            'text'  => $attribute['name'],
                            'id' => $attribute_slug
                        );
                    }

                }

            }

            return $options;

        }

        /*
         * Get available product custom fields
         * @return array
         */
        static public function get_custom_fields( $name = '' ) {

            global $wpdb;

            $query = "
                SELECT DISTINCT meta_key as val
                FROM $wpdb->postmeta
                WHERE meta_key NOT LIKE 'attribute_%'
                ORDER BY val ASC
            ";

            if ( $name ) {

                $like = '%' . $wpdb->esc_like( $name ) . '%';

                $query = "
                    SELECT DISTINCT meta_key as val
                    FROM $wpdb->postmeta
                    WHERE meta_key NOT LIKE 'attribute_%'
                    AND meta_key LIKE '{$like}'
                    ORDER BY val ASC
                ";

            }

            $wp_es_fields = $wpdb->get_results( $query );
            $options = array();

            if ( is_array( $wp_es_fields ) && ! empty( $wp_es_fields ) ) {
                foreach ( $wp_es_fields as $field ) {
                    if ( isset( $field->val ) ) {
                        $options[] = array(
                            'id'  => $field->val,
                            'text' => $field->val
                        );
                    }
                }
            }

            return $options;

        }

        /*
         * Get all available users
         * @return array
         */
        static public function get_users( $name = '' ) {

            if ( $name ) {

                $users = get_users(array(
                    'search' => '*' . $name . '*',
                    'search_columns' => array(
                        'user_login',
                        'user_nicename',
                        'display_name',
                    ),
                ));

            } else {

                $users = get_users();

            }

            $options = array();

            if ( $users && ! empty( $users ) ) {
                foreach( $users as $user ) {

                    $options[] = array(
                        'id'  => $user->ID,
                        'text' => $user->display_name . ' (' . $user->user_nicename . ')'
                    );

                }
            }

            return $options;

        }

        /*
         * Get specific username by users id
         * $param int $user_id Specific user id
         * @return string
         */
        static public function get_user_by_id( $user_id ) {

            $value = $user_id;

            $user = get_userdata( $user_id );

            if ( $user ) {
                $value = $user->display_name . ' (' . $user->user_nicename . ')';
            }

            return $value;

        }

        /*
         * Get all available user roles
         * @return array
         */
        static public function get_user_roles() {

            global $wp_roles;

            $roles = $wp_roles->roles;
            $options = array();

            if ( $roles && ! empty( $roles ) ) {

                if ( is_multisite() ) {
                    $options['super_admin'] = __( 'Super Admin', 'advanced-woo-search' );
                }

                foreach( $roles as $role_slug => $role ) {
                    $options[$role_slug] = $role['name'];
                }

                $options['non-logged'] = __( 'Visitor ( not logged-in )', 'advanced-woo-search' );

            }

            return $options;

        }

        /*
         * Get all available user countries
         * @return array
         */
        static public function get_user_countries() {

            $options = array();

            $values = WC()->countries->get_allowed_countries() + WC()->countries->get_shipping_countries();

            foreach ( $values as $value_val => $value_name ) {
                $options[$value_val] = $value_name;
            }

            return $options;

        }

        /*
         * Get all available user languages
         * @return array
         */
        static public function get_user_languages() {

            $options = array();

            $values = include AWS_PRO_DIR . '/includes/admin/languages.php';

            foreach ( $values as $value_val => $value_name ) {
                $options[$value_val] = $value_name;
            }

            return $options;

        }

        /*
         * Get user devices
         * @return array
         */
        static public function get_user_devices() {

            $options = array();

            $values = array(
                'desktop' => __( 'Desktop', 'advanced-woo-search' ),
                'mobile'  => __( 'Mobile', 'advanced-woo-search' ),
            );

            foreach ( $values as $value_val => $value_name ) {
                $options[$value_val] = $value_name;
            }

            return $options;

        }

        /*
         * Get all available pages
         * @return array
         */
        static public function get_pages( $name = '' ) {

            if ( $name ) {

                $pages_query = new WP_Query( array(
                    'post_type' => 'page',
                    's' => $name,
                ) );

                $pages = $pages_query->posts;

            } else {

                $pages = get_pages( array( 'parent' => 0, 'hierarchical' => 0 ) );

            }

            $options = array();

            if ( $pages && ! empty( $pages ) ) {

                foreach( $pages as $page ) {

                    $title = $page->post_title ? $page->post_title :  __( "(no title)", "advanced-woo-search" );

                    $options[] = array(
                        'id'  => $page->ID,
                        'text' => $title
                    );

                    $child_pages = get_pages( array( 'child_of' => $page->ID ) );

                    if ( $child_pages && ! empty( $child_pages ) ) {

                        foreach( $child_pages as $child_page ) {

                            $page_prefix = '';
                            $parents_number = sizeof( $child_page->ancestors );

                            if ( $parents_number && is_int( $parents_number ) ) {
                                $page_prefix = str_repeat( "-", $parents_number );
                            }

                            $title = $child_page->post_title ? $child_page->post_title :  __( "(no title)", "advanced-woo-search" );
                            $title = $page_prefix . $title;

                            $options[] = array(
                                'id'  => $child_page->ID,
                                'text' => $title
                            );

                        }

                    }

                }

            }

            return $options;

        }

        /*
         * Get specific page name by page id
         * $param int $page_id Specific page id
         * @return string
         */
        static public function get_page_by_id( $page_id ) {

            $value = get_the_title( $page_id );

            if ( ! $value ) {
                $value  = __( "(no title)", "advanced-woo-search" );
            }

            return $value;

        }

        /*
         * Get all available page templates
         * @return array
         */
        static public function get_page_templates() {

            $page_templates = get_page_templates();
            $options = array();

            $options['default'] = __( 'Default template', 'advanced-woo-search' );

            if ( $page_templates && ! empty( $page_templates ) ) {
                foreach( $page_templates as $page_template_name => $page_template_file ) {
                    $options[] = array(
                        'text'  => $page_template_name,
                        'id' => $page_template_file
                    );
                }
            }

            return $options;

        }

        /*
         * Get available pages types
         * @return array
         */
        static public function get_page_type() {

            $options = array();

            $types = array(
                'product' => __( 'Product single page', 'advanced-woo-search' ),
                'front' => __( 'Front page', 'advanced-woo-search' ),
                'shop' => __( 'Shop page', 'advanced-woo-search' ),
                'cart' => __( 'Cart page', 'advanced-woo-search' ),
                'checkout' => __( 'Checkout page', 'advanced-woo-search' ),
                'account' => __( 'Account page', 'advanced-woo-search' ),
                'search' => __( 'Search results page', 'advanced-woo-search' ),
                'brand_page' => __( 'Brand archive page', 'advanced-woo-search' ),
                'category_page' => __( 'Category archive page', 'advanced-woo-search' ),
                'tag_page' => __( 'Tag archive page', 'advanced-woo-search' ),
                'attribute_page' => __( 'Attributes archive page', 'advanced-woo-search' ),
                'tax_page' => __( 'Any taxonomy archive page', 'advanced-woo-search' ),
            );

            foreach( $types as $type_slug => $type_name ) {
                $options[$type_slug] = $type_name;
            }

            return $options;

        }

        /*
         * Get available archive pages
         * @return array
         */
        static public function get_page_archives() {

            $options = array();
            $taxonomy_objects = get_object_taxonomies( 'product', 'objects' );

            $types = array(
                'product_cat' => __( 'Category', 'advanced-woo-search' ),
                'product_tag' => __( 'Tag', 'advanced-woo-search' ),
                'attributes' => __( 'Attributes', 'advanced-woo-search' ),
            );

            foreach( $types as $type_slug => $type_name ) {

                $options[] = array(
                    'text'  => $type_name,
                    'id' => $type_slug
                );

            }

            foreach( $taxonomy_objects as $taxonomy_object ) {
                if ( in_array( $taxonomy_object->name, array( 'product_cat', 'product_tag', 'product_type', 'product_visibility', 'product_shipping_class' ) ) ) {
                    continue;
                }

                if ( strpos( $taxonomy_object->name, 'pa_' ) === 0 ) {
                    continue;
                }

                $options[] = array(
                    'text'  => $taxonomy_object->label,
                    'id' => $taxonomy_object->name
                );

            }

            return $options;

        }

        /*
         * Get available archive pages terms
         * @return array
         */
        static public function get_page_archive_terms( $name = false ) {

            if ( ! $name ) {
                return false;
            }

            $options = array();

            switch( $name ) {

                case 'attributes':

                    if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
                        $attributes = wc_get_attribute_taxonomies();

                        if ( $attributes && ! empty( $attributes ) ) {
                            foreach( $attributes as $attribute ) {
                                if ( $attribute->attribute_public ) {

                                    $options[] = array(
                                        'text'  => $attribute->attribute_label,
                                        'id' => wc_attribute_taxonomy_name( $attribute->attribute_name )
                                    );

                                }
                            }
                        }

                    }

                    break;

                default:

                    $options = AWS_Admin_Filters_Helpers::get_tax_terms( $name );

            }

            return $options;

        }

        /*
         * Get user cart
         * @return array
         */
        static public function get_user_cart() {

            $options = array();

            $values = array(
                'number'  => __( 'Number of items', 'advanced-woo-search' ),
                'average' => __( 'Average items cost', 'advanced-woo-search' ),
                'sum'     => __( 'Total sum of items', 'advanced-woo-search' ),
            );

            foreach ( $values as $value_val => $value_name ) {
                $options[$value_val] = $value_name;
            }

            return $options;

        }

        /*
         * Get available price formats
         * @return array
         */
        static public function get_shop_stats() {

            $options = array();

            $values = array(
                'orders_number' => __( 'Orders number', 'advanced-woo-search' ),
                'aov'           => __( 'Average order value', 'advanced-woo-search' ),
                'total_spend'   => __( 'Total spend', 'advanced-woo-search' ),
            );

            foreach ( $values as $value_val => $value_name ) {
                $options[$value_val] = $value_name;
            }

            return $options;

        }

        /*
         * Get terms pages hierarchy types
         * @return array
         */
        static public function get_terms_hierarchy() {

            $options = array();

            $values = array(
                'top_parent' => __( 'Top parent', 'advanced-woo-search' ),
                'child'  => __( 'Child', 'advanced-woo-search' ),
            );

            foreach ( $values as $value_val => $value_name ) {
                $options[$value_val] = $value_name;
            }

            return $options;

        }

        /*
         * Get available sales number periods
         * @return array
         */
        static public function get_time_periods() {

            $options = array();

            $values = array(
                'all'   => __( 'all time', 'advanced-woo-search' ),
                'hour'  => __( 'last 24 hours', 'advanced-woo-search' ),
                'week'  => __( 'last 7 days', 'advanced-woo-search' ),
                'month' => __( 'last month', 'advanced-woo-search' ),
                'year'  => __( 'last year', 'advanced-woo-search' ),
            );

            foreach ( $values as $value_val => $value_name ) {
                $options[$value_val] = $value_name;
            }

            return $options;

        }

        /*
         * Get filter section name
         * @param $name string Section id
         * @return string
         */
        static public function get_filter_section( $name ) {

            $label = $name;

            $sections = array(
                'product'        => __( "Product", "advanced-woo-search" ),
                'current_user'   => __( "Current user", "advanced-woo-search" ),
                'current_page'   => __( "Current page", "advanced-woo-search" ),
                'current_search' => __( "Current search", "advanced-woo-search" ),
                'term'           => __( "Terms pages", "advanced-woo-search" ),
                'user'           => __( "Users pages", "advanced-woo-search" ),
            );

            if ( isset( $sections[$name] ) ) {
                $label = $sections[$name];
            }

            return $label;

        }

        /*
         * Filter operators
         * @param $name string Operator name
         * @return array
         */
        static public function get_filter_operators( $name ) {

            $operators = array();

            $equals = array(
                array(
                    "name" => __( "equal to", "advanced-woo-search" ),
                    "id"   => "equal",
                ),
                array(
                    "name" => __( "not equal to", "advanced-woo-search" ),
                    "id"   => "not_equal",
                ),
            );

            $compare = array(
                array(
                    "name" => __( "greater or equal to", "advanced-woo-search" ),
                    "id"   => "greater",
                ),
                array(
                    "name" => __( "less or equal to", "advanced-woo-search" ),
                    "id"   => "less",
                ),
            );

            $contains = array(
                array(
                    "name" => __( "contains", "advanced-woo-search" ),
                    "id"   => "contains",
                ),
                array(
                    "name" => __( "not contains", "advanced-woo-search" ),
                    "id"   => "not_contains",
                ),
            );

            $operators['equals'] = $equals;
            $operators['equals_compare'] = array_merge( $equals, $compare );
            $operators['equals_contains'] = array_merge( $equals, $contains );
            $operators['equals_contains_compare'] = array_merge( $equals, $compare, $contains );

            return $operators[$name];

        }

        /*
         * Include rule array by filter rule id
         * @return array
         */
        static public function include_filter_rule_by_id( $id ) {

            $rules = AWS_Admin_Options::include_filters();
            $rule = array();

            if ( $rules ) {
                foreach ( $rules as $rule_section => $section_rules ) {
                    foreach ( $section_rules as $section_rule ) {
                        if ( $section_rule['id'] === $id ) {
                            $rule = $section_rule;
                            break;
                        }
                    }
                }
            }

            if ( empty( $rule ) ) {
                $rule = $rules['product'][0];
            }

            return $rule;

        }

        /*
         * Get filter parameters that must be excluded for the current section
         * @return array
         */
        static public function get_filter_section_excluded_params( $section_name ) {

            $disabled_sections = array( 'term', 'user' );
            if ( $section_name === 'term' ) {
                $disabled_sections = array( 'product', 'user' );
            } elseif ( $section_name === 'user' ) {
                $disabled_sections = array( 'product', 'term' );
            }

            return $disabled_sections;

        }

    }

endif;