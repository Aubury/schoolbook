<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWS_Search_Filters' ) ) :

    /**
     * AWS search filters generator class
     */
    class AWS_Search_Filters {

        /**
         * @var AWS_Search_Filters Array of filters $conditions
         */
        protected $conditions = null;

        /**
         * @var AWS_Search_Filters Array with current condition rule $rule
         */
        protected $rule = null;

        /**
         * @var AWS_Search_Filters ID of current form instance $form_id
         */
        private $form_id = 0;

        /**
         * @var AWS_Search_Filters ID of current filter $filter_id
         */
        private $filter_id = 0;

        /*
         * Constructor
         */
        public function __construct( $conditions, $form_id = 1, $filter_id = 1 ) {

            /**
             * Filters condition rules
             *
             * @since 2.45
             *
             * @param array $conditions Condition rules
             * @param int $form_id Current form ID
             * @param int $filter_id Current filter ID
             */
            $this->conditions = apply_filters( 'aws_filters_conditions', $conditions, $form_id, $filter_id );

            $this->form_id = $form_id;

            $this->filter_id = $filter_id;

        }

        /*
         * Check whether current rule uses positive operator
         *
         * @param string|null $operator Rule operator.
         * @return bool
         */
        private function is_positive_operator( $operator = null ) {

            if ( null === $operator ) {
                $operator = isset( $this->rule['operator'] ) ? $this->rule['operator'] : '';
            }

            return in_array( $operator, array( 'equal', 'in_list' ), true );

        }

        /*
         * Get current rule values as array
         *
         * @param mixed|null $value Rule value.
         * @return array
         */
        private function get_rule_values( $value = null ) {

            if ( null === $value ) {
                $value = isset( $this->rule['value'] ) ? $this->rule['value'] : array();
            }

            if ( ! is_array( $value ) ) {
                $value = '' !== $value && null !== $value ? array( $value ) : array();
            }

            $value = array_map( 'strval', $value );
            $value = array_filter( $value, 'strlen' );

            return array_values( $value );

        }

        /*
         * Check if current rule uses wildcard value for multiselect fields
         *
         * Covers both the 'aws_any' sentinel and empty arrays left by old migrations.
         *
         * @return bool
         */
        private function has_rule_wildcard_value() {
            return $this->has_rule_any_value() || $this->has_rule_empty_value();
        }

        /*
         * Check if the rule value is the 'aws_any' sentinel (match everything).
         *
         * @return bool
         */
        private function has_rule_any_value() {

            $raw_value = isset( $this->rule['value'] ) ? $this->rule['value'] : null;

            if ( ! is_array( $raw_value ) && 'aws_any' === $raw_value ) {
                return true;
            }

            return in_array( 'aws_any', $this->get_rule_values( $raw_value ), true );

        }

        /*
         * Check if the rule value is an empty array (no items selected).
         *
         * @return bool
         */
        private function has_rule_empty_value() {

            $raw_value = isset( $this->rule['value'] ) ? $this->rule['value'] : null;

            return is_array( $raw_value ) && empty( $this->get_rule_values( $raw_value ) ) && ! $this->has_rule_any_value();

        }

        /*
         * Check if wildcard value should behave as a positive (include) condition.
         *
         * - aws_any  : standard semantics — in_list = include (show), not_in_list = exclude (hide).
         * - empty [] : reversed semantics — in_list = nothing to include (hide), not_in_list = nothing to exclude (show).
         *
         * @return bool
         */
        private function is_wildcard_positive_operator() {

            if ( $this->has_rule_any_value() ) {
                return $this->is_positive_operator();
            }

            $operator = isset( $this->rule['operator'] ) ? $this->rule['operator'] : '';

            if ( 'not_in_list' === $operator ) {
                return true;
            }

            if ( 'in_list' === $operator ) {
                return false;
            }

            return $this->is_positive_operator( $operator );

        }

        /*
         * Return SQL relation for list rules
         *
         * @return string
         */
        private function get_list_relation() {

            return $this->is_positive_operator() ? 'IN' : 'NOT IN';

        }

        /*
         * Convert boolean match result to SQL stub
         *
         * @param bool $match Rule match state.
         * @return string
         */
        private function get_match_sql( $match ) {

            return $this->is_positive_operator() === (bool) $match ? '( 1=1 )' : '( 1=2 )';

        }

        /*
         * Filter products results and output SQL string
         */
        public function filter() {

            $filters = array();
            $filters_sql = '';

            if ( empty( $this->conditions ) || ! is_array( $this->conditions ) ) {
                return $filters;
            }

            /**
             * Filter condition functions
             * @since 2.45
             * @param array Array of custom condition functions
             */
            $custom_match_functions = apply_filters( 'aws_filters_condition_rules', array() );

            foreach ( $this->conditions as $condition_group ) {

                if ( $condition_group && ! empty( $condition_group ) ) {

                    /**
                     * Filter condition rule parameters for filters
                     * @since 2.79
                     * @param array $condition_group Condition parameters
                     */
                    $condition_group = apply_filters( 'aws_filters_condition_group', $condition_group );

                    $group_rules = $this->filter_product_terms_rules( $condition_group );
                    $group_filters = array();

                    foreach( $group_rules as $condition_rule ) {

                        $this->rule = $condition_rule;
                        $condition_name = $condition_rule['param'];
                        $condition_output = '';

                        if ( isset( $custom_match_functions[$condition_name] ) ) {
                            $condition_output = call_user_func( $custom_match_functions[$condition_name], $condition_rule );
                        } elseif ( method_exists( $this, 'match_' . $condition_name ) ) {
                            $condition_output = call_user_func( array( $this, 'match_' . $condition_name ) );
                        }

                        if ( $condition_output ) {
                            $group_filters[] = $condition_output;
                        }

                    }

                    if ( ! empty( $group_filters ) ) {
                        $filters[] = '( ' . implode( ' AND ', $group_filters ) . ' )';
                    }

                }

            }

            if ( ! empty( $filters ) ) {
                $filters_sql =  implode( ' OR ', $filters );
            }

            $filters_sql = trim( preg_replace( '/\s+/', ' ', $filters_sql ) );

            return $filters_sql;

        }

        /*
         * Aggregate product terms rules
         */
        private function filter_product_terms_rules( $group_rules ) {

            global $wpdb;

            $terms_rules = array( 'product_category', 'product_cat', 'product_tag', 'product_brand', 'product_taxonomy', 'product_attributes', 'product_shipping_class' );
            $new_group_rules = array();

            $terms_equal_array = array();
            $terms_not_equal_array = array();

            $taxonomies_equal_array = array();
            $taxonomies_not_equal_array = array();

            foreach( $group_rules as $condition_rule ) {

                if ( array_search( $condition_rule['param'], $terms_rules ) !== false ) {
                    $operator = isset( $condition_rule['operator'] ) ? $condition_rule['operator'] : '';
                    $values = isset( $condition_rule['value'] ) ? $condition_rule['value'] : array();
                    $values = is_array( $values ) ? array_values( array_filter( $values, 'strlen' ) ) : array( $values );

                    $is_any = in_array( 'aws_any', $values, true );
                    $is_positive = in_array( $operator, array( 'equal', 'in_list' ), true );

                    if ( $is_positive ) {
                        if ( $is_any ) {
                            $taxonomies_equal_array[] = $wpdb->prepare( '%s', $condition_rule['suboption'] );
                        } elseif ( ! empty( $values ) ) {
                            $terms_equal_array = array_merge( $terms_equal_array, $values );
                        } else {
                            // in_list + [] → nothing to include → always hide
                            $new_group_rules[] = array( 'param' => 'product_terms', 'operator' => 'equal', 'value' => array() );
                        }
                    } else {
                        if ( $is_any ) {
                            $taxonomies_not_equal_array[] = $wpdb->prepare( '%s', $condition_rule['suboption'] );
                        } elseif ( ! empty( $values ) ) {
                            $terms_not_equal_array = array_merge( $terms_not_equal_array, $values );
                        }
                        // else: not_in_list + [] → nothing to exclude → always show → no constraint needed
                    }
                    continue;
                }

                $new_group_rules[] = $condition_rule;

            }

            if ( $terms_equal_array ) {
                $new_group_rules[] = array( 'param' => 'product_terms', 'operator' => 'equal', 'value' => $terms_equal_array );
            }

            if ( $terms_not_equal_array ) {
                $new_group_rules[] = array( 'param' => 'product_terms', 'operator' => 'not_equal', 'value' => $terms_not_equal_array );
            }

            if ( $taxonomies_equal_array ) {
                $new_group_rules[] = array( 'param' => 'product_taxonomies', 'operator' => 'equal', 'value' => $taxonomies_equal_array );
            }

            if ( $taxonomies_not_equal_array ) {
                $new_group_rules[] = array( 'param' => 'product_taxonomies', 'operator' => 'not_equal', 'value' => $taxonomies_not_equal_array );
            }

            return $new_group_rules;

        }

        /*
         * Product rule
         */
        public function match_product() {

            if ( ! isset( $this->rule['value'] ) ) {
                return '';
            }

            if ( $this->has_rule_wildcard_value() ) {
                return $this->is_wildcard_positive_operator() ? '( 1=1 )' : '( 1=2 )';
            }

            $filter_products = array();
            $relation = $this->get_list_relation();
            $product_ids = $this->get_rule_values();

            foreach ( $product_ids as $product_id ) {
                $product = wc_get_product( $product_id );

                if ( ! is_a( $product, 'WC_Product' ) ) {
                    continue;
                }

                $filter_products[] = $product_id;

                if ( $product->is_type( 'variable' ) && method_exists( $product, 'get_children' ) ) {
                    $filter_products = array_merge( $filter_products, $product->get_children() );
                }
            }

            if ( empty( $filter_products ) ) {
                return 'IN' === $relation ? '( 1=2 )' : '( 1=1 )';
            }

            /*
             * Products filter
             */
            $filter_products = apply_filters( 'aws_products_filter', $filter_products, $relation, $this->form_id, $this->filter_id );
            $filter_products = array_unique( array_map( 'intval', $filter_products ) );

            $product_ids = implode( ',', $filter_products );

            $string = "( id {$relation} ({$product_ids}) )";

            return $string;

        }

        /*
         * Product taxonomies terms rule
         */
        private function match_product_terms() {

            global $wpdb;

            $relation = $this->get_list_relation();

            $terms = apply_filters( 'aws_tax_filter', $this->rule['value'], $relation, $this->form_id, $this->filter_id );

            if ( is_array( $terms ) && empty( $terms ) ) {
                return 'IN' === $relation ? '( 1=2 )' : '( 1=1 )';
            }

            $terms = implode( ',', $terms );

            /**
             * Include or not child terms for tax filter
             * @since 1.75
             */
            $filter_tax_include_childs = apply_filters( 'aws_tax_filter_include_childs', true, $this->form_id, $this->filter_id );

            $include_childs = '';
            if ( $filter_tax_include_childs ) {
                $include_childs = " OR parent IN ({$terms}) OR parent IN ( SELECT term_id from {$wpdb->term_taxonomy} WHERE parent IN ({$terms}) )";
            }

            $string = "( id {$relation} (
                   SELECT $wpdb->posts.ID
                   FROM $wpdb->term_relationships
                   JOIN $wpdb->posts
                   ON ( $wpdb->term_relationships.object_id = $wpdb->posts.post_parent OR $wpdb->term_relationships.object_id = $wpdb->posts.ID )
                   WHERE $wpdb->term_relationships.term_taxonomy_id IN ( 
                       select term_taxonomy_id from $wpdb->term_taxonomy WHERE term_id IN ({$terms}) {$include_childs}
                   )
                ))";

            return $string;

        }

        /*
         * Product taxonomies rule
         */
        private function match_product_taxonomies() {

            global $wpdb;

            $relation = $this->get_list_relation();

            if ( is_array( $this->rule['value'] ) && empty( $this->rule['value'] ) ) {
                return 'IN' === $relation ? '( 1=2 )' : '( 1=1 )';
            }

            $taxonomies = implode( ',', $this->rule['value'] );


            $string = "( id {$relation} (
                   SELECT $wpdb->posts.ID
                   FROM $wpdb->term_relationships
                   JOIN $wpdb->posts
                   ON ( $wpdb->term_relationships.object_id = $wpdb->posts.post_parent OR $wpdb->term_relationships.object_id = $wpdb->posts.ID )
                   WHERE $wpdb->term_relationships.term_taxonomy_id IN ( 
                       select term_taxonomy_id from $wpdb->term_taxonomy WHERE taxonomy IN ({$taxonomies})
                   )
                ))";

            return $string;

        }

        /*
         * Product type rule
         */
        private function match_product_type() {

            if ( $this->has_rule_wildcard_value() ) {
                return $this->is_wildcard_positive_operator() ? '( 1=1 )' : '( 1=2 )';
            }

            $values = $this->get_rule_values();

            if ( empty( $values ) ) {
                return $this->is_positive_operator() ? '( 1=2 )' : '( 1=1 )';
            }

            $strings = array();

            foreach ( $values as $type ) {
                $this->rule['value'] = $type;
                $strings[] = $this->match_product_type_single();
            }

            return '( ' . implode( $this->is_positive_operator() ? ' OR ' : ' AND ', $strings ) . ' )';

        }

        /*
         * Product type single value rule
         */
        private function match_product_type_single() {

            global $wpdb;

            $type = $this->rule['value'];
            $relation = $this->is_positive_operator() ? '=' : '!=';

            if ( $type === 'simple') {
                $pr_type = AWS_PRO()->table_updates->get_product_type_code( 'product' );
                $pr_type = is_string( $pr_type ) ? "'" . $pr_type . "'" : $pr_type;
                return "( type {$relation} {$pr_type} )";
            } elseif ( $type === 'variable') {
                $pr_type = AWS_PRO()->table_updates->get_product_type_code( 'var' );
                $pr_type = is_string( $pr_type ) ? "'" . $pr_type . "'" : $pr_type;
                return "( type {$relation} {$pr_type} )";
            } elseif ( $type === 'variation') {
                $pr_type = AWS_PRO()->table_updates->get_product_type_code( 'child' );
                $pr_type = is_string( $pr_type ) ? "'" . $pr_type . "'" : $pr_type;
                return "( type {$relation} {$pr_type} )";
            }

            $relation = $this->get_list_relation();

            return "( id {$relation} (
               SELECT $wpdb->posts.ID
               FROM $wpdb->term_relationships
               JOIN $wpdb->posts
               ON ( $wpdb->term_relationships.object_id = $wpdb->posts.post_parent OR $wpdb->term_relationships.object_id = $wpdb->posts.ID )
               WHERE $wpdb->term_relationships.term_taxonomy_id IN ( 
                   select term_taxonomy_id from $wpdb->term_taxonomy WHERE term_id IN (
                        SELECT term_id
                        FROM $wpdb->terms
                        WHERE slug = '{$type}'
                   )
               )
            ))";

        }

        /*
         * Product featured rule
         */
        private function match_product_featured() {

            global $wpdb;

            $is_featured = $this->rule['value'] === 'true';
            $is_featured = $this->rule['operator'] === 'equal' ? $is_featured : ! $is_featured;

            $relation = $is_featured ? 'IN' : 'NOT IN';

            $string = "( id {$relation} (
                   SELECT $wpdb->posts.ID
                   FROM $wpdb->term_relationships
                   JOIN $wpdb->posts
                   ON ( $wpdb->term_relationships.object_id = $wpdb->posts.post_parent OR $wpdb->term_relationships.object_id = $wpdb->posts.ID )
                   WHERE $wpdb->term_relationships.term_taxonomy_id IN ( 
                       select term_taxonomy_id from $wpdb->term_taxonomy WHERE term_id IN (
                            SELECT term_id
                            FROM $wpdb->terms
                            WHERE slug = 'featured'
                       )
                   )
                ))";

            return $string;

        }

        /*
         * Product visibility rule
         */
        private function match_product_visibility() {

            if ( $this->has_rule_wildcard_value() ) {
                return $this->is_wildcard_positive_operator() ? '( 1=1 )' : '( 1=2 )';
            }

            $visibilities = array();
            $relation = $this->get_list_relation();

            foreach ( $this->get_rule_values() as $visibility ) {
                $visibility = AWS_PRO()->table_updates->get_visibility_code( $visibility );
                $visibilities[] = is_string( $visibility ) ? "'" . esc_sql( $visibility ) . "'" : intval( $visibility );
            }

            $visibilities = array_unique( $visibilities );

            if ( empty( $visibilities ) ) {
                return 'IN' === $relation ? '( 1=2 )' : '( 1=1 )';
            }

            $string = '( visibility ' . $relation . ' ( ' . implode( ',', $visibilities ) . ' ) )';

            return $string;

        }

        /*
         * Product sale status rule
         */
        private function match_product_sale_status() {

            $sale_val = $this->rule['value'] === 'true' ? '1' : '0';
            $relation = $this->rule['operator'] === 'equal' ? '=' : '!=';

            $string = "( on_sale {$relation} {$sale_val} )";

            return $string;

        }

        /*
         * Product stock status rule
         */
        private function match_product_stock_status() {

            if ( $this->has_rule_wildcard_value() ) {
                return $this->is_wildcard_positive_operator() ? '( 1=1 )' : '( 1=2 )';
            }

            $relation = $this->get_list_relation();
            $values = array();

            foreach ( $this->get_rule_values() as $stock_status ) {
                switch ( $stock_status ) {
                    case 'instock':
                        $values[] = 1;
                        break;
                    case 'outofstock':
                        $values[] = 0;
                        break;
                    default:
                        $values[] = 2;
                }
            }

            $values = array_unique( array_map( 'intval', $values ) );

            if ( empty( $values ) ) {
                return 'IN' === $relation ? '( 1=2 )' : '( 1=1 )';
            }

            $string = '( in_stock ' . $relation . ' ( ' . implode( ',', $values ) . ' ) )';

            return $string;

        }

        /*
         * Product has image
         */
        private function match_product_has_image() {

            global $wpdb;

            $has_image = $this->rule['value'] === 'true';
            $has_image = $this->rule['operator'] === 'equal' ? $has_image : ! $has_image;

            $relation = $has_image ? 'IN' : 'NOT IN';

            $string = "( id {$relation} (
                  SELECT post_id
                  FROM $wpdb->postmeta
                  WHERE meta_key = '_thumbnail_id'
            ))";

            return $string;

        }

        /*
         * Product has gallery
         */
        private function match_product_has_gallery() {

            global $wpdb;

            $has_gallery = $this->rule['value'] === 'true';
            $has_gallery = $this->rule['operator'] === 'equal' ? $has_gallery : ! $has_gallery;

            $relation = $has_gallery ? 'IN' : 'NOT IN';

            $string = "( id {$relation} (
                  SELECT post_id
                  FROM $wpdb->postmeta
                  WHERE meta_key = '_product_image_gallery'
            ))";

            return $string;

        }

        /*
         * Product is in cart rule
         */
        private function match_product_is_in_cart() {

            global $wpdb;

            $is_is_in_cart = $this->rule['value'] === 'true';
            $is_is_in_cart = $this->rule['operator'] === 'equal' ? $is_is_in_cart : ! $is_is_in_cart;

            $relation = $is_is_in_cart ? 'IN' : 'NOT IN';

            $product_ids = array();

            if ( function_exists('WC') && property_exists( WC(), 'cart' ) && WC()->cart && method_exists( WC()->cart, 'get_cart' ) ) {
                foreach ( WC()->cart->get_cart() as $item ) {
                    $product_ids[] = $item['product_id'];
                    if ( isset( $item['variation_id'] ) ) {
                        $product_ids[] = $item['variation_id'];
                    }
                }
            }

            if ( $product_ids ) {
                $product_ids_str = implode( ',', $product_ids );
                $string = "( ID {$relation} ( {$product_ids_str} ) )";
            } else {
                $string = $relation === 'IN' ? '( 1=2 )' : '( 1=1 )';
            }

            return $string;

        }

        /*
         * Product meta rule
         */
        private function match_product_meta() {

            global $wpdb;

            $relation = ( $this->rule['operator'] === 'not_equal' || $this->rule['operator'] === 'not_contains' ) ? 'NOT IN' : 'IN';
            $meta_name = $this->rule['suboption'];
            $meta_value = $this->rule['value'] === 'aws_any' ? '' : $this->rule['value'];
            $string = '';

            if ( $meta_value ) {

                $operator = sprintf( "meta_value = '%s'", $meta_value );

                switch ( $this->rule['operator'] ) {
                    case 'contains':
                    case 'not_contains':
                        $operator = sprintf( "meta_value LIKE '%s'", '%' . $wpdb->esc_like( $meta_value ) . '%' );
                        break;
                    case 'greater':
                        $operator = sprintf( "meta_value >= '%s'", $meta_value );
                        break;
                    case 'less':
                        $operator = sprintf( "meta_value <= '%s'", $meta_value );
                        break;
                }

                $string = "( id {$relation} (
                      SELECT post_id
                      FROM $wpdb->postmeta
                      WHERE meta_key = '{$meta_name}' AND {$operator}
                ))";

            } else {

                $string = "( id {$relation} (
                      SELECT post_id
                      FROM $wpdb->postmeta
                      WHERE meta_key = '{$meta_name}'
                ))";

            }

            return $string;

        }

        /*
         * Product SKU
         */
        private function match_product_sku() {

            global $wpdb;

            $relation = ( $this->rule['operator'] === 'equal' || $this->rule['operator'] === 'contains' ) ? 'IN' : 'NOT IN';
            $sku_value = $this->rule['value'];
            $sql = '';

            switch ( $this->rule['operator'] ) {
                case 'equal':
                case 'not_equal':
                    $sql = $wpdb->prepare( "meta_key = '_sku' AND meta_value = '%s'", $sku_value );
                    break;
                case 'contains':
                case 'not_contains':
                    $sql = $wpdb->prepare( "meta_key = '_sku' AND meta_value LIKE %s", '%' . $wpdb->esc_like( $sku_value ) . '%' );
                    break;
            }

            $string = "( id {$relation} (
                  SELECT post_id
                  FROM $wpdb->postmeta
                  WHERE {$sql}
            ))";

            return $string;

        }

        /*
         * Product GTIN
         */
        private function match_product_gtin() {

            global $wpdb;

            $relation = ( $this->rule['operator'] === 'equal' || $this->rule['operator'] === 'contains' ) ? 'IN' : 'NOT IN';
            $gtin_value = $this->rule['value'];
            $sql = '';

            switch ( $this->rule['operator'] ) {
                case 'equal':
                case 'not_equal':
                    $sql = $wpdb->prepare( "meta_key = '_global_unique_id' AND meta_value = '%s'", $gtin_value );
                    break;
                case 'contains':
                case 'not_contains':
                    $sql = $wpdb->prepare( "meta_key = '_global_unique_id' AND meta_value LIKE %s", '%' . $wpdb->esc_like( $gtin_value ) . '%' );
                    break;
            }

            $string = "( id {$relation} (
                  SELECT post_id
                  FROM $wpdb->postmeta
                  WHERE {$sql}
            ))";

            return $string;

        }

        /*
         * Product custom attributes rule
         */
        private function match_product_custom_attributes() {

            global $wpdb;

            if ( $this->has_rule_wildcard_value() ) {
                return $this->is_wildcard_positive_operator() ? '( 1=1 )' : '( 1=2 )';
            }

            $relation = $this->get_list_relation();
            $meta_name = $this->rule['suboption'];
            $meta_values = $this->get_rule_values();

            if ( $meta_values ) {

                $value_conditions = array();
                foreach ( $meta_values as $meta_value ) {
                    $value_conditions[] = $wpdb->prepare( 'meta_value LIKE %s', '%' . $wpdb->esc_like( $meta_value ) . '%' );
                }

                $string = $wpdb->prepare( "( id {$relation} (
                      SELECT post_id
                      FROM $wpdb->postmeta
                      WHERE meta_key = '_product_attributes' AND meta_value LIKE %s AND ( " . implode( ' OR ', $value_conditions ) . " )
                ))", '%' . $wpdb->esc_like( $meta_name ) . '%' );

            } else {

                $string = $wpdb->prepare( "( id {$relation} (
                      SELECT post_id
                      FROM $wpdb->postmeta
                      WHERE meta_key = '_product_attributes' AND meta_value LIKE %s
                ))", '%' . $wpdb->esc_like( $meta_name ) . '%' );

            }

            return $string;

        }

        /*
         * Terms pages term taxonomy rule
         */
        private function match_term_taxonomy() {

            global $wpdb;

            $relation = $this->get_list_relation();
            $taxonomy = $this->rule['suboption'];
            $term_values = $this->has_rule_wildcard_value() ? array() : array_map( 'intval', $this->get_rule_values() );
            $term = implode( ',', $term_values );

            /**
             * Include or not child terms for tax filter
             * @since 1.75
             */
            $filter_tax_include_childs = apply_filters( 'aws_tax_filter_include_childs', true, $this->form_id, $this->filter_id );

            $include_childs = '';
            if ( $filter_tax_include_childs ) {
                $include_childs_operator = 'IN' === $relation ? 'OR' : 'AND';
                $include_childs = " {$include_childs_operator} {$wpdb->term_taxonomy}.parent {$relation} ( {$term} ) {$include_childs_operator} {$wpdb->term_taxonomy}.parent {$relation} ( SELECT term_id from {$wpdb->term_taxonomy} WHERE parent IN ({$term}) )";
            }
            
            if ( $term ) {

                $string = "( {$wpdb->terms}.term_id {$relation} ( {$term} ) {$include_childs} )";

            } else {

                if ( $this->has_rule_wildcard_value() ) {
                    $relation = $this->is_wildcard_positive_operator() ? 'IN' : 'NOT IN';
                }

                $string = "( {$wpdb->term_taxonomy}.taxonomy {$relation} ( '{$taxonomy}' ) )";

            }

            return $string;

        }

        /*
         * Terms pages products count rule
         */
        private function match_term_count() {

            switch ( $this->rule['operator'] ) {
                case 'equal':
                    $operator = '=';
                    break;
                case 'not_equal':
                    $operator = '!=';
                    break;
                case 'greater':
                    $operator = '>=';
                    break;
                default:
                    $operator = '<=';
            }

            $count = intval( $this->rule['value'] );

            $string = "( count {$operator} {$count} )";

            return $string;

        }

        /*
         * Terms pages hierarchy type rule
         */
        private function match_term_hierarchy() {

            $is_top = $this->rule['value'] === 'top_parent';
            $is_top = $this->rule['operator'] === 'equal' ? $is_top : ! $is_top;

            $string = $is_top ? "( parent = 0 )" : "( parent != 0 )";

            return $string;

        }

        /*
         * Terms pages term has image rule
         */
        private function match_term_has_image() {

            global $wpdb;

            $relation = $this->rule['operator'] === 'equal' ? 'IN' : 'NOT IN';

            $string = "( {$wpdb->terms}.term_id {$relation} (
                      SELECT term_id
                      FROM $wpdb->termmeta
                      WHERE meta_key = 'thumbnail_id'
            ))";

            return $string;

        }

        /*
         * User pages: user rule
         */
        private function match_user_page_user() {

            if ( $this->has_rule_wildcard_value() ) {
                return $this->is_wildcard_positive_operator() ? '( 1=1 )' : '( 1=2 )';
            }

            $user_id = implode( ',', array_map( 'intval', $this->get_rule_values() ) );
            $relation = $this->get_list_relation();

            if ( ! $user_id ) {
                return 'IN' === $relation ? '( 1=2 )' : '( 1=1 )';
            }

            $string = "(ID {$relation} ( {$user_id} ))";

            return $string;

        }

        /*
         * User pages: user role rule
         */
        private function match_user_page_role() {

            if ( $this->has_rule_wildcard_value() ) {
                return $this->is_wildcard_positive_operator() ? '( 1=1 )' : '( 1=2 )';
            }

            $relation = $this->get_list_relation();
            $users_array = array();
            $users_args = array(
                'role__in' => $this->get_rule_values(),
            );

            $users = get_users( $users_args );

            if ( !is_wp_error( $users ) && $users && !empty( $users ) ) {
                foreach( $users as $user ) {
                    $users_array[] = $user->ID;
                }
            }

            if ( $users_array ) {
                $users_ids = implode( ',', $users_array );
                $string = "( ID {$relation} ( {$users_ids} ) )";
            } else {
                $string = $relation === 'IN' ? '( 1=2 )' : '( 1=1 )';
            }

           return $string;

        }

        /*
         * User pages: user products number rule
         */
        private function match_user_page_count() {

            global $wpdb;

            switch ( $this->rule['operator'] ) {
                case 'equal':
                    $operator = '=';
                    break;
                case 'not_equal':
                    $operator = '!=';
                    break;
                case 'greater':
                    $operator = '>=';
                    break;
                default:
                    $operator = '<=';
            }

            $count = intval( $this->rule['value'] );

            $string = "( (
                SELECT COUNT(*)
                FROM {$wpdb->posts}
                WHERE {$wpdb->posts}.post_author = {$wpdb->users}.ID AND {$wpdb->posts}.post_type = 'product' AND {$wpdb->posts}.post_status = 'publish'
            ) {$operator} {$count} )";

            return $string;

        }

        /*
         * Current user: user id
         */
        private function match_current_user() {

            $value = array_map( 'intval', $this->get_rule_values() );
            $current_user_id = get_current_user_id();
            $match = in_array( $current_user_id, $value, true );

            return $this->get_match_sql( $match );

        }

        /*
         * Current user: user role
         */
        private function match_current_user_role() {

            $values = $this->get_rule_values();

            if ( is_user_logged_in() ) {
                global $current_user;
                $current_user_roles = (array) $current_user->roles;
            } else {
                $current_user_roles = array( 'non-logged' );
            }

            $match = (bool) array_intersect( $values, $current_user_roles );

            return $this->get_match_sql( $match );

        }

        /*
         * Current user: user device
         */
        private function match_current_user_device() {

            $value = wp_is_mobile() ? 'mobile' : 'desktop';

            $match = $this->rule['value'] === $value;

            if ( $this->rule['operator'] === 'equal' && $match ) {
                return "( 1=1 )";
            } elseif ( $this->rule['operator'] === 'not_equal' && ! $match ) {
                return "( 1=1 )";
            } else {
                return "( 1=2 )";
            }

        }

        /*
         * Current page: page
         */
        public function match_current_page() {

            $value = isset( $_REQUEST['aws_page'] ) ? intval( $_REQUEST['aws_page'] ) : AWS_Helpers::get_current_page_id();
            $match = in_array( $value, array_map( 'intval', $this->get_rule_values() ), true );

            return $this->get_match_sql( $match );

        }

        /*
         * Current page: page template
         */
        public function match_current_page_template() {

            $page_id = isset( $_REQUEST['aws_page'] ) ? intval( $_REQUEST['aws_page'] ) : AWS_Helpers::get_current_page_id();

            if ( ! is_page( $page_id ) ) {
                $value = 'none';
            }

            $value = get_page_template_slug( $page_id );

            if ( $value === '' ) {
                $value = 'default';
            }

            $match = in_array( $value, $this->get_rule_values(), true );

            return $this->get_match_sql( $match );

        }

        /*
         * Current page: page type
         */
        public function match_current_page_type() {

            $page_id = isset( $_REQUEST['aws_page'] ) ? intval( $_REQUEST['aws_page'] ) : AWS_Helpers::get_current_page_id();

            $page_type = array();

            $term_obj = get_term( $page_id );

            if ( wc_get_product( $page_id ) ) {
                $page_type[] = 'product';
            }
            if ( get_option('page_on_front') === $page_id ) {
                $page_type[] = 'front';
            }
            if ( wc_get_page_id( 'shop' ) === $page_id ) {
                $page_type[] = 'shop';
            }
            if ( wc_get_page_id( 'cart' ) === $page_id ) {
                $page_type[] = 'cart';
            }
            if ( wc_get_page_id( 'checkout' ) === $page_id ) {
                $page_type[] = 'checkout';
            }
            if ( wc_get_page_id( 'myaccount' ) === $page_id ) {
                $page_type[] = 'account';
            }
            if ( ( isset( $_SERVER['QUERY_STRING'] ) && ( strpos( $_SERVER['QUERY_STRING'], 's=' ) !== false || strpos( $_SERVER['QUERY_STRING'], 'type_aws' ) !== false ) )
                || ( isset( $_SERVER['HTTP_REFERER'] ) && ( strpos( $_SERVER['HTTP_REFERER'], 's=' ) !== false || strpos( $_SERVER['HTTP_REFERER'], 'type_aws' ) !== false ) )
            ) {
                $page_type[] = 'search';
            }
            if ( $term_obj && $term_obj->taxonomy === 'product_brand' ) {
                $page_type[] = 'brand_page';
            }
            if ( $term_obj && $term_obj->taxonomy === 'product_cat' ) {
                $page_type[] = 'category_page';
            }
            if ( $term_obj && $term_obj->taxonomy === 'product_tag' ) {
                $page_type[] = 'tag_page';
            }
            if ( $term_obj && taxonomy_is_product_attribute( $term_obj->taxonomy ) ) {
                $page_type[] = 'attribute_page';
            }
            if ( $term_obj ) {
                $page_type[] = 'tax_page';
            }

            $match = (bool) array_intersect( $this->get_rule_values(), $page_type );

            return $this->get_match_sql( $match );

        }

        /*
         * Current page: page archives
         */
        public function match_current_page_archives() {

            $page_id = isset( $_REQUEST['aws_page'] ) ? intval( $_REQUEST['aws_page'] ) : AWS_Helpers::get_current_page_id();

            $tax = $this->rule['suboption'];
            $terms = $this->has_rule_wildcard_value() ? array() : array_map( 'intval', $this->get_rule_values() );

            $term_obj = get_term( $page_id );

            if ( ! $term_obj ) {
                return false;
            }

            if ( 'attributes' === $tax ) {
                $match = $terms ? in_array( intval( $term_obj->term_id ), $terms, true ) : taxonomy_is_product_attribute( $term_obj->taxonomy );
            } else {
                $match = $terms ? in_array( intval( $term_obj->term_id ), $terms, true ) : $tax === $term_obj->taxonomy;
            }

            if ( $this->has_rule_wildcard_value() ) {
                return $this->is_wildcard_positive_operator() === (bool) $match ? '( 1=1 )' : '( 1=2 )';
            }

            return $this->get_match_sql( $match );

        }

        /*
         * Current page: page URL
         */
        public function match_current_page_url() {

            $term = trim( $this->rule['value'] );
            $match = true;

            $page_url = '';

            if ( isset( $_REQUEST['pageurl'] ) ) {
                $page_url = $_REQUEST['pageurl'];
            } else {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $page_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            }

            if ( $this->rule['operator'] === 'equal' ) {
                $match = $page_url == $term;
            } elseif ( $this->rule['operator'] === 'not_equal' ) {
                $match = $page_url!= $term;
            } elseif ( $this->rule['operator'] === 'contains' ) {
                $match = strpos( $page_url, $term) !== false;
            } elseif ( $this->rule['operator'] === 'not_contains' ) {
                $match = strpos( $page_url, $term) === false;
            }

            if ( $match ) {
                return "( 1=1 )";
            } else {
                return "( 1=2 )";
            }

        }

        /*
         * Current search: search terms
         */
        public function match_current_search_terms() {

            $term = trim( $this->rule['value'] );
            $match = true;

            if ( isset( $_REQUEST['keyword'] ) ) {
                $s = esc_attr( $_REQUEST['keyword'] );
            } else {
                global $wp_query;
                $s = isset( $_GET['s'] ) ? $_GET['s'] : ( ( is_object( $wp_query ) && $wp_query->query_vars['s'] ) ? $wp_query->query_vars['s'] : '' );
            }

            $s = trim( $s );

            if ( $this->rule['operator'] === 'equal' ) {
                $match = $s == $term;
            } elseif ( $this->rule['operator'] === 'not_equal' ) {
                $match = $s!= $term;
            } elseif ( $this->rule['operator'] === 'contains' ) {
                $match = strpos( $s, $term) !== false;
            } elseif ( $this->rule['operator'] === 'not_contains' ) {
                $match = strpos( $s, $term) === false;
            }

            if ( $match ) {
                return "( 1=1 )";
            } else {
                return "( 1=2 )";
            }

        }

    }

endif;
