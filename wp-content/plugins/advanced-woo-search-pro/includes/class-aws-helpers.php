<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! class_exists( 'AWS_Helpers' ) ) :

/**
 * Class for plugin help methods
 */
class AWS_Helpers {

    /*
     * Retrieves thumbnail url for term
     */
    static public function get_term_thumbnail( $term_id ) {

        $thumb_src = '';
        $thumbnail_id = function_exists( 'get_term_meta' ) ? get_term_meta( $term_id, 'thumbnail_id', true ) : get_metadata( 'woocommerce_term', $term_id, 'thumbnail_id', true );

        /**
         * Filter terms images size
         * @since 2.64
         * @param string $image_size Image size
         */
        $image_size = apply_filters( 'aws_term_image_size', 'thumbnail' );

        if ( $thumbnail_id ) {
            $thumb = wp_get_attachment_image_src( $thumbnail_id, $image_size );
            if ( ! empty( $thumb ) ) {
                $thumb_src = current( $thumb );
            }
        }

        return $thumb_src;

    }

    /**
     * Get product add to cart button values
     *
     * @param object $product Product
     * @param string $show_cart_quantity Show or not additional quantity selector
     * @return array $add_to_cart
     */
    static public function get_product_cart_args( $product, $show_cart_quantity ) {

        $product_quantity_max = method_exists( $product, 'get_max_purchase_quantity' ) && $product->get_max_purchase_quantity() !== -1 ? $product->get_max_purchase_quantity() : '';
        $product_quantity_min = method_exists( $product, 'get_min_purchase_quantity' ) ? $product->get_min_purchase_quantity() : '';

        $args = array(
            'input_id'     => uniqid( 'quantity_' ),
            'input_name'   => 'quantity',
            'input_value'  => '1',
            'classes'      => apply_filters( 'woocommerce_quantity_input_classes', array( 'input-text', 'qty', 'text' ), $product ),
            'max_value'    => apply_filters( 'woocommerce_quantity_input_max', $product_quantity_max, $product ),
            'min_value'    => apply_filters( 'woocommerce_quantity_input_min', $product_quantity_min, $product ),
            'step'         => apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
            'pattern'      => apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' ),
            'inputmode'    => apply_filters( 'woocommerce_quantity_input_inputmode', has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : '' ),
            'product_name' => $product ? $product->get_title() : '',
            'placeholder'  => apply_filters( 'woocommerce_quantity_input_placeholder', '', $product ),
        );

        $args = apply_filters( 'woocommerce_quantity_input_args', $args, $product );

        $args['min_value'] = max( $args['min_value'], 0 );
        $args['max_value'] = 0 < $args['max_value'] ? $args['max_value'] : '';

        // Max cannot be lower than min if defined.
        if ( '' !== $args['max_value'] && $args['max_value'] < $args['min_value'] ) {
            $args['max_value'] = $args['min_value'];
        }

        $add_to_cart = array(
            'url'            => WC_AJAX::get_endpoint( 'add_to_cart' ),
            'text'           => $product->add_to_cart_text(),
            'id'             => $product->get_id(),
            'permalink'      => $product->get_type() === 'variation' && ! $product->is_in_stock() ? $product->get_permalink() : $product->add_to_cart_url(),
            'i18n_view_cart' => esc_attr__( 'View cart', 'woocommerce' ),
            'cart_url'       => function_exists( 'wc_get_cart_url' ) ? apply_filters( 'woocommerce_add_to_cart_redirect', wc_get_cart_url(), null ) : '',
            'inputmode'      => $args['inputmode'],
            'quantity'       => ( $show_cart_quantity === 'true' && $product->is_purchasable() && $product->is_in_stock() ) ? 'show' : '',
            'quantity_value' => $args['input_value'],
            'quantity_max'   => $args['max_value'],
            'quantity_min'   => $args['min_value'],
            'quantity_step'  => $args['step'],
        );

        return $add_to_cart;

    }

    /*
     * Get array of products attributes archives
     */
    static public function get_attribute_archives( $values = false ) {

        $attributes_array = array();

        if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
            $attributes = wc_get_attribute_taxonomies();

            if ( $attributes && ! empty( $attributes ) ) {
                foreach( $attributes as $attribute ) {
                    if ( $attribute->attribute_public ) {

                        $attribute_name = wc_sanitize_taxonomy_name( $attribute->attribute_name );
                        $attribute_name = AWS_Helpers::normalize_term_name( $attribute_name, $attribute->attribute_name );
                        $attribute_name = 'pa_' . $attribute_name;

                        $attributes_array[$attribute_name] = $values ? 0 : $attribute->attribute_label . ' (' . wc_attribute_taxonomy_name( $attribute->attribute_name ) . ')';

                        if ( $values === 'names' ) {
                            $attributes_array[$attribute_name] = wc_attribute_taxonomy_name( $attribute->attribute_name );
                        }

                    }
                }
            }

        }

        return $attributes_array;

    }

    /*
     * Get all available users roles
     */
    static public function get_user_roles( $values = false ) {

        global $wp_roles;

        $roles = $wp_roles->roles;
        $users_array = array();

        if ( $roles && ! empty( $roles ) ) {

            if ( is_multisite() ) {
                $users_array['super_admin'] = $values ? 0 : __( 'Super Admin', 'advanced-woo-search' );
            }

            foreach( $roles as $role_slug => $role ) {
                $role_slug_n = wc_sanitize_taxonomy_name( $role_slug );
                $role_slug = AWS_Helpers::normalize_term_name( $role_slug_n, $role_slug );
                $users_array[$role_slug] = $values ? 0 : $role['name'];
            }

        }

        return $users_array;

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
     * Get array of products attributes
     */
    static public function get_attributes( $values = false ) {

        $attributes_array = array();

        if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
            $attributes = wc_get_attribute_taxonomies();

            if ( $attributes && ! empty( $attributes ) ) {
                foreach( $attributes as $attribute ) {

                    $attribute_slug = wc_sanitize_taxonomy_name( $attribute->attribute_name );
                    $attribute_slug = AWS_Helpers::normalize_term_name( $attribute_slug, $attribute->attribute_name );

                    $attribute_name = 'attr_pa_' . $attribute_slug;
                    $attributes_array[$attribute_name] = $values ? 0 : $attribute->attribute_label . ' (' . wc_attribute_taxonomy_name( $attribute->attribute_name ) . ')';

                }
            }

        }

        $attributes_array['attr_custom'] = $values ? 0 : esc_html__( 'Custom product attributes', 'advanced-woo-search' );

        return $attributes_array;

    }

    /*
     * Get array of products taxonomies
     */
    static public function get_taxonomies( $values = false, $prefix = true, $def_value = 1 ) {

        $taxonomy_objects = get_object_taxonomies( 'product', 'objects' );
        $taxonomies_array = array();

        foreach( $taxonomy_objects as $taxonomy_object ) {
            if ( in_array( $taxonomy_object->name, array( 'product_cat', 'product_tag', 'product_brand', 'product_type', 'product_visibility', 'product_shipping_class' ) ) ) {
                continue;
            }

            if ( strpos( $taxonomy_object->name, 'pa_' ) === 0 ) {
                continue;
            }

            $tax_name = AWS_Helpers::normalize_term_name( $taxonomy_object->name, $taxonomy_object->name );
            $tax_name = $prefix ? 'tax_' . $tax_name : $tax_name;

            $taxonomies_array[$tax_name] = $values ? $def_value : $taxonomy_object->label . ' (' . $taxonomy_object->name . ')';

            if ( $values === 'names' ) {
                $taxonomies_array[$tax_name] = $taxonomy_object->name;
            }

        }

        return $taxonomies_array;

    }

    /*
     * Get array of products custom fields
     */
    static public function get_custom_fields( $values = false ) {
        global $wpdb;

        $query = "
            SELECT DISTINCT meta_key
            FROM $wpdb->postmeta
            WHERE meta_key NOT LIKE 'attribute_pa%'
            ORDER BY meta_key ASC
        ";

        $meta_keys = array();

        $wp_es_fields = $wpdb->get_results( $query );

        /**
         * Results of SQL query for meta keys
         * @since 2.07
         * @param array $wp_es_fields array of meta keys
         */
        $wp_es_fields = apply_filters( 'aws_meta_keys_unfiltered', $wp_es_fields );

        if ( is_array( $wp_es_fields ) && !empty( $wp_es_fields ) ) {
            foreach ( $wp_es_fields as $field ) {
                if ( isset( $field->meta_key ) ) {

                    if ( AWS_Helpers::filter_custom_fields( $field->meta_key ) ) {
                        continue;
                    }

                    $meta_name = AWS_Helpers::normalize_term_name( $field->meta_key, $field->meta_key );
                    $meta_name = 'meta_' . strtolower( $meta_name );
                    $meta_keys[$meta_name] = $values ? 0 : $field->meta_key;

                }
            }
        }

        /**
         * Filter results of SQL query for meta keys
         * @since 1.32
         * @param array $meta_keys array of meta keys
         */
        $meta_keys = apply_filters( 'aws_meta_keys', $meta_keys );

        return $meta_keys;

    }

    /*
     * Exclude some meta fields from search
     */
    static public function filter_custom_fields( $meta_name ) {

        $exclude = false;

        /**
         * Include special meta fields to search
         * @since 1.57
         * @param array Array of meta keys
         */
        $include_meta = apply_filters( 'aws_meta_keys_include', array() );

        if ( $include_meta && is_array( $include_meta ) && ! empty( $include_meta ) ) {
            foreach( $include_meta as $include_meta_name ) {
                if ( strpos( $meta_name, $include_meta_name ) !== false ) {
                    return false;
                }
            }
        }

        if ( $meta_name === 'wc_productdata_options' || $meta_name === 'total_sales' ) {
            $exclude = true;
        }

        return $exclude;

    }

    /*
     * Recursively implode multi-dimensional arrays
     */
    static public function recursive_implode( $separator, $array ) {

        $output = " ";

        foreach ( $array as $av ) {
            if ( is_array( $av ) ) {
                $output .= AWS_Helpers::recursive_implode( $separator, $av );
            } elseif ( is_string( $av ) ) {
                $output .= $separator.$av;
            }

        }

        return trim( $output );

    }

    /*
     * Get instance page url
     */
    static public function get_settings_instance_page_url( $part = false ) {

        $current_page      = isset( $_GET['page']  ) ? sanitize_text_field( $_GET['page'] ) : 'aws-options';
        $instance_id       = isset( $_GET['aws_id'] ) ? sanitize_text_field( $_GET['aws_id'] ) : 0;
        $current_tab       = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';

        if ( $instance_id ) {
            $instance_page = admin_url( 'admin.php?page=aws-options&tab='. $current_tab .'&aws_id=' . $instance_id );
        } elseif ( $current_page === 'aws-performance' ) {
            $instance_page = admin_url( 'admin.php?page=aws-performance' );
        } else {
            $instance_page = admin_url( 'admin.php?page=aws-options&tab=' . $current_tab );
        }

        if ( $part ) {
            $instance_page = $instance_page . $part;
        }

        return $instance_page;
    }

    /*
     * Removes scripts, styles, html tags
     */
    static public function html2txt( $str ) {
        $search = array(
            '@<script[^>]*?>.*?</script>@si',
            '@<[\/\!]*?[^<>]*?>@si',
            '@<style[^>]*?>.*?</style>@siU',
            '@<![\s\S]*?--[ \t\n\r]*>@'
        );
        $str = preg_replace( $search, ' ', $str );

        $str = preg_replace('/\s+/', ' ', $str);

        $str = esc_attr( $str );
        $str = stripslashes( $str );
        $str = str_replace( array( "\r", "\n" ), ' ', $str );

        $str = str_replace( array(
            "Â·",
            "â€¦",
            "â‚¬",
            "&shy;"
        ), "", $str );

        return $str;
    }

    /*
     * Strip shortcodes
     */
    static public function strip_shortcodes( $str ) {

        /**
         * Filter content string before striping shortcodes
         * @since 1.92
         * @param string $str
         */
        $str = apply_filters( 'aws_before_strip_shortcodes', $str );

        $str = preg_replace( '#\[[^\]]+\]#', '', $str );
        return $str;

    }

    /*
     * Check if index table exist
     */
    static public function is_table_not_exist() {

        return AWS_PRO()->option_vars->is_index_table_not_exists();

    }

    /*
     * Get amount of indexed products
     */
    static public function get_indexed_products_count() {

        global $wpdb;

        $table_name = $wpdb->prefix . AWS_INDEX_TABLE_NAME;

        $indexed_products = 0;

        if ( ! AWS_PRO()->option_vars->is_index_table_not_exists() ) {

            $sql_cond = AWS_PRO()->table_updates->get_products_count();

            $sql = "SELECT COUNT(*) FROM {$table_name} WHERE {$sql_cond} GROUP BY ID;";

            $indexed_products = $wpdb->query( $sql );

        }

        return $indexed_products;

    }

    /*
     * Check if index table has new terms columns
     */
    static public function is_index_table_has_terms() {

        global $wpdb;

        $table_name =  $wpdb->prefix . AWS_INDEX_TABLE_NAME;

        $return = false;

        if ( ! AWS_PRO()->option_vars->is_index_table_not_exists() ) {

            $columns = $wpdb->get_row("
                SELECT * FROM {$table_name} LIMIT 0, 1
            ", ARRAY_A );

            if ( $columns && ! isset( $columns['term_id'] ) ) {
                $return = 'no_terms';
            } else {
                $return = 'has_terms';
            }

        }

        return $return;

    }

    /*
     * Check if index table has new on_sale columns
     */
    static public function is_index_table_has_on_sale() {

        global $wpdb;

        $table_name =  $wpdb->prefix . AWS_INDEX_TABLE_NAME;

        $return = false;

        if ( ! AWS_PRO()->option_vars->is_index_table_not_exists() ) {

            $columns = $wpdb->get_row("
                SELECT * FROM {$table_name} LIMIT 0, 1
            ", ARRAY_A );

            if ( $columns && ! isset( $columns['on_sale'] ) ) {
                $return = 'no';
            } else {
                $return = 'has';
            }

        }

        return $return;

    }

    /*
     * Add term_id column to index table
     */
    static public function add_term_id_column() {

        if ( AWS_Helpers::is_index_table_has_terms() == 'no_terms' ) {

            global $wpdb;
            $table_name = $wpdb->prefix . AWS_INDEX_TABLE_NAME;

            $wpdb->query("
                ALTER TABLE {$table_name}
                ADD COLUMN `term_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0
            ");

        }

    }

    /*
     * Add on_sale column to index table
     */
    static public function add_on_sale_column() {

        if ( AWS_Helpers::is_index_table_has_on_sale() == 'no' ) {

            global $wpdb;
            $table_name = $wpdb->prefix . AWS_INDEX_TABLE_NAME;

            $wpdb->query("
                ALTER TABLE {$table_name}
                ADD COLUMN `on_sale` INT(11) NOT NULL DEFAULT 0
            ");

        }

    }

    /*
     * Get index table specific source name from taxonomy name
     *
     * @return string Source name
     */
    static public function get_source_name( $taxonomy ) {

        $source_name = '';

        if ( $taxonomy === 'product_cat' ) {
            $source_name = 'category';
        }
        elseif ( $taxonomy === 'product_tag' ) {
            $source_name = 'tag';
        }
        elseif ( strpos( $taxonomy, 'pa_' ) === 0 ) {
            $source_name = 'attr_' . $taxonomy;
        }
        else {
            $taxonomies = AWS_Helpers::get_taxonomies( false, false );

            if ( $taxonomies && ! empty( $taxonomies ) && isset( $taxonomies[$taxonomy] ) ) {
                $source_name = 'tax_' . $taxonomy;
            }

        }

        return $source_name;

    }

    /*
     * Get special characters that must be striped
     */
    static public function get_special_chars() {

        $chars = array(
            '&#33;', //exclamation point
            '&#34;', //double quotes
            '&quot;', //double quotes
            '&#35;', //number sign
            '&#36;', //dollar sign
            '&#37;', //percent sign
            '&#38;', //ampersand
            '&amp;', //ampersand
            '&lsquo;', //opening single quote
            '&rsquo;', //closing single quote & apostrophe
            '&ldquo;', //opening double quote
            '&rdquo;', //closing double quote
            '&#39;', //single quote
            '&#039;', //single quote
            '&#40;', //opening parenthesis
            '&#41;', //closing parenthesis
            '&#42;', //asterisk
            '&#43;', //plus sign
            '&#44;', //comma
            '&#45;', //minus sign - hyphen
            '&#46;', //period
            '&#47;', //slash
            '&#58;', //colon
            '&#59;', //semicolon
            '&#60;', //less than sign
            '&lt;', //less than sign
            '&#61;', //equal sign
            '&#62;', //greater than sign
            '&gt;', //greater than sign
            '&#63;', //question mark
            '&#64;', //at symbol
            '&#91;', //opening bracket
            '&#92;', //backslash
            '&#93;', //closing bracket
            '&#94;', //caret - circumflex
            '&#95;', //underscore
            '&#96;', //grave accent
            '&#123;', //opening brace
            '&#124;', //vertical bar
            '&#125;', //closing brace
            '&#126;', //equivalency sign - tilde
            '&#161;', //inverted exclamation mark
            '&iexcl;', //inverted exclamation mark
            '&#162;', //cent sign
            '&cent;', //cent sign
            '&#163;', //pound sign
            '&pound;', //pound sign
            '&#164;', //currency sign
            '&curren;', //currency sign
            '&#165;', //yen sign
            '&yen;', //yen sign
            '&#166;', //broken vertical bar
            '&brvbar;', //broken vertical bar
            '&#167;', //section sign
            '&sect;', //section sign
            '&#168;', //spacing diaeresis - umlaut
            '&uml;', //spacing diaeresis - umlaut
            '&#169;', //copyright sign
            '&copy;', //copyright sign
            '&#170;', //feminine ordinal indicator
            '&ordf;', //feminine ordinal indicator
            '&#171;', //left double angle quotes
            '&laquo;', //left double angle quotes
            '&#172;', //not sign
            '&not;', //not sign
            '&#174;', //registered trade mark sign
            '&reg;', //registered trade mark sign
            '&#175;', //spacing macron - overline
            '&macr;', //spacing macron - overline
            '&#176;', //degree sign
            '&deg;', //degree sign
            '&#177;', //plus-or-minus sign
            '&plusmn;', //plus-or-minus sign
            '&#178;', //superscript two - squared
            '&sup2;', //superscript two - squared
            '&#179;', //superscript three - cubed
            '&sup3;', //superscript three - cubed
            '&#180;', //acute accent - spacing acute
            '&acute;', //acute accent - spacing acute
            '&#181;', //micro sign
            '&micro;', //micro sign
            '&#182;', //pilcrow sign - paragraph sign
            '&para;', //pilcrow sign - paragraph sign
            '&#183;', //middle dot - Georgian comma
            '&middot;', //middle dot - Georgian comma
            '&#184;', //spacing cedilla
            '&cedil;', //spacing cedilla
            '&#185;', //superscript one
            '&sup1;', //superscript one
            '&#186;', //masculine ordinal indicator
            '&ordm;', //masculine ordinal indicator
            '&#187;', //right double angle quotes
            '&raquo;', //right double angle quotes
            '&#188;', //fraction one quarter
            '&frac14;', //fraction one quarter
            '&#189;', //fraction one half
            '&frac12;', //fraction one half
            '&#190;', //fraction three quarters
            '&frac34;', //fraction three quarters
            '&#191;', //inverted question mark
            '&iquest;', //inverted question mark
            '&#247;', //division sign
            '&divide;', //division sign
            '&#8211;', //en dash
            '&#8212;', //em dash
            '&#8216;', //left single quotation mark
            '&#8217;', //right single quotation mark
            '&#8218;', //single low-9 quotation mark
            '&#8220;', //left double quotation mark
            '&#8221;', //right double quotation mark
            '&#8222;', //double low-9 quotation mark
            '&#8224;', //dagger
            '&#8225;', //double dagger
            '&#8226;', //bullet
            '&#8230;', //horizontal ellipsis
            '&#8240;', //per thousand sign
            '&#8364;', //euro sign
            '&euro;', //euro sign
            '&#8482;', //trade mark sign
            '&#215;', // multiplication sign
            '!', //exclamation point
            '"', //double quotes
            '#', //number sign
            '$', //dollar sign
            '%', //percent sign
            '&', //ampersand
            "'", //single quote
            '(', //opening parenthesis
            ')', //closing parenthesis
            '*', //asterisk
            '+', //plus sign
            ",", //comma
            '-', //minus sign - hyphen
            ".", //period
            "/", //slash
            ':', //colon
            ';', //semicolon
            "<", //less than sign
            "=", //equal sign
            ">", //greater than sign
            '?', //question mark
            '@', //at symbol
            "[", //opening bracket
            '\\', //backslash
            "]", //closing bracket
            '^', //caret - circumflex
            '_', //underscore
            '`', //grave accent
            "{", //opening brace
            '|', //vertical bar
            "}", //closing brace
            '~', //equivalency sign - tilde
            '¡', //inverted exclamation mark
            '¢', //cent sign
            '£', //pound sign
            '¤', //currency sign
            '¥', //yen sign
            '¦', //broken vertical bar
            '§', //section sign
            '¨', //spacing diaeresis - umlaut
            '©', //copyright sign
            'ª', //feminine ordinal indicator
            '«', //left double angle quotes
            '¬', //not sign
            '®', //registered trade mark sign
            '¯', //spacing macron - overline
            '°', //degree sign
            '±', //plus-or-minus sign
            '²', //superscript two - squared
            '³', //superscript three - cubed
            '´', //acute accent - spacing acute
            'µ', //micro sign
            '¶', //pilcrow sign - paragraph sign
            '·', //middle dot - Georgian comma
            '¸', //spacing cedilla
            '¹', //superscript one
            'º', //masculine ordinal indicator
            '»', //right double angle quotes
            '¼', //fraction one quarter
            '½', //fraction one half
            '¾', //fraction three quarters
            '¿', //inverted question mark
            '÷', //division sign
            '–', //en dash
            '—', //em dash
            '‘', //left single quotation mark
            "’", //right single quotation mark
            '‚', //single low-9 quotation mark
            "“", //left double quotation mark
            "”", //right double quotation mark
            '„', //double low-9 quotation mark
            '†', //dagger
            '‡', //double dagger
            '•', //bullet
            '…', //horizontal ellipsis
            '‰', //per thousand sign
            '€', //euro sign
            '™', //trade mark sign
        );

        return apply_filters( 'aws_special_chars', $chars );

    }

    /*
     * Get diacritical marks
     */
    static public function get_diacritic_chars() {

        $chars = array(
            'Š'=>'S',
            'š'=>'s',
            'Ž'=>'Z',
            'ž'=>'z',
            'À'=>'A',
            'Á'=>'A',
            'Â'=>'A',
            'Ã'=>'A',
            'Ä'=>'A',
            'Å'=>'A',
            'Æ'=>'A',
            'Ç'=>'C',
            'È'=>'E',
            'É'=>'E',
            'Ê'=>'E',
            'Ë'=>'E',
            'Ì'=>'I',
            'Í'=>'I',
            'Î'=>'I',
            'Ï'=>'I',
            'İ'=>'I',
            'Ñ'=>'N',
            'Ò'=>'O',
            'Ó'=>'O',
            'Ô'=>'O',
            'Õ'=>'O',
            'Ö'=>'O',
            'Ø'=>'O',
            'Ù'=>'U',
            'Ú'=>'U',
            'Û'=>'U',
            'Ü'=>'U',
            'Ý'=>'Y',
            'à'=>'a',
            'á'=>'a',
            'â'=>'a',
            'ã'=>'a',
            'ä'=>'a',
            'å'=>'a',
            'ç'=>'c',
            'è'=>'e',
            'é'=>'e',
            'ê'=>'e',
            'ë'=>'e',
            'ì'=>'i',
            'í'=>'i',
            'î'=>'i',
            'ï'=>'i',
            'ð'=>'o',
            'ñ'=>'n',
            'ò'=>'o',
            'ó'=>'o',
            'ô'=>'o',
            'õ'=>'o',
            'ö'=>'o',
            'ø'=>'o',
            'ù'=>'u',
            'ú'=>'u',
            'û'=>'u',
            'ý'=>'y',
            'þ'=>'b',
            'ÿ'=>'y',
        );

        /**
         * Filters array of diacritic chars
         *
         * @since 1.41
         */
        return apply_filters( 'aws_diacritic_chars', $chars );

    }

    /*
     * Normalize string
     */
    static public function normalize_string( $string ) {

        /**
         * Filters string before normalization
         * @since 2.84
         * @param string $string
         */
        $string = apply_filters( 'aws_pre_normalize_string', $string );

        $special_chars = AWS_Helpers::get_special_chars();

        $string = AWS_Helpers::html2txt( $string );
        if ( array_search( '&#44;', $special_chars ) !== false || array_search( ',', $special_chars ) !== false ) {
            $string = str_replace( array( '&#44;', ',' ), ' ', $string );
        }
        $string = str_replace( array( '&#215;', '&times;', '×' ), 'x', $string );
        $string = str_replace( $special_chars, '', $string );
        $string = trim( $string );

        //$str = preg_replace( '/[[:punct:]]+/u', ' ', $str );
        $string = preg_replace( '/[[:space:]]+/', ' ', $string );

        // Most objects except unicode characters
        $string = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $string );

        // Line feeds, carriage returns, tabs
        $string = preg_replace( '/[\x00-\x1F\x80-\x9F]/u', '', $string );

        if ( function_exists( 'mb_strtolower' ) ) {
            $string = mb_strtolower( $string );
        } else {
            $string = strtolower( $string );
        }

        // Diacritical marks
        $string = strtr( $string, AWS_Helpers::get_diacritic_chars() );

        /**
         * Filters normalized string
         *
         * @since 1.41
         */
        return apply_filters( 'aws_normalize_string', $string );

    }

    /*
     * Replace stopwords
     */
    static public function filter_stopwords( $str_array ) {

        $stopwords = AWS_PRO()->get_common_settings( 'stopwords' );
        $stopwords_array = array();
        $new_str_array = array();

        if ( $stopwords ) {
            $stopwords_array = explode( ',', $stopwords );
        }

        if ( $str_array && is_array( $str_array ) && ! empty( $str_array ) && $stopwords_array && ! empty( $stopwords_array ) ) {

            $stopwords_array = array_map( 'trim', $stopwords_array );

            foreach ( $str_array as $str_word ) {
                if ( in_array( $str_word, $stopwords_array ) ) {
                    continue;
                }
                $new_str_array[] = $str_word;
            }

        } else {
            $new_str_array = $str_array;
        }

        return $new_str_array;

    }

    /*
     * Singularize terms
     * @param string $search_term Search term
     * @return string Singularized search term
     */
    static public function singularize( $search_term ) {

        $lang = apply_filters( 'aws_current_scrapping_lang', 'en' );

        $search_term_len = strlen( $search_term );
        $search_term_norm = AWS_Plurals::singularize( $search_term, $lang );

        if ( $search_term_norm && $search_term_len > 3 && strlen( $search_term_norm ) > 2 ) {
            $search_term = $search_term_norm;
        }

        return $search_term;

    }

    /*
     * Add synonyms
     */
    static public function get_synonyms( $str_array, $singular = false ) {

        $synonyms = AWS_PRO()->get_common_settings( 'synonyms' );
        $synonyms_array = array();
        $new_str_array = array();

        if ( $synonyms ) {
            $synonyms_array = preg_split( '/\r\n|\r|\n|&#13;&#10;/', $synonyms );
        }

        if ( $str_array && is_array( $str_array ) && ! empty( $str_array ) && $synonyms_array && ! empty( $synonyms_array ) ) {

            $synonyms_array = array_map( 'trim', $synonyms_array );

            /**
             * Filters synonyms array before adding them to the index table where need
             * @since 1.70
             * @param array $synonyms_array Array of synonyms groups
             */
            $synonyms_array = apply_filters( 'aws_synonyms_option_array', $synonyms_array );

            foreach ( $synonyms_array as $synonyms_string ) {

                if ( $synonyms_string ) {

                    $synonym_array = explode( ',', $synonyms_string );

                    if ( $synonym_array && ! empty( $synonym_array ) ) {

                        $synonym_array = array_map( array( 'AWS_Helpers', 'normalize_string' ), $synonym_array );
                        if ( $singular ) {
                            $synonym_array = array_map( array( 'AWS_Helpers', 'singularize' ), $synonym_array );
                        }

                        foreach ( $synonym_array as $synonym_item ) {

                            if ( $synonym_item && isset( $str_array[$synonym_item] ) ) {
                                $new_str_array = array_merge( $new_str_array, $synonym_array );
                                break;
                            }

                            if ( $synonym_item && preg_match( '/\s/',$synonym_item )  ) {

                                $synonym_words = explode( ' ', $synonym_item );

                                if ( $synonym_words && ! empty( $synonym_words ) ) {

                                    $synonym_words = array_filter($synonym_words);
                                    $str_array_keys = array_keys( $str_array );
                                    $synonym_prev_word_pos = 0;
                                    $use_this = true;

                                    foreach ( $synonym_words as $synonym_word ) {
                                        if ( $synonym_word && isset( $str_array[$synonym_word] ) ) {
                                            $synonym_current_word_pos = array_search( $synonym_word, $str_array_keys );
                                            $synonym_prev_word_pos = $synonym_prev_word_pos ? $synonym_prev_word_pos : $synonym_current_word_pos;

                                            if ( ( $synonym_prev_word_pos !== $synonym_current_word_pos ) && ++$synonym_prev_word_pos !== $synonym_current_word_pos ) {
                                                $use_this = false;
                                                break;
                                            }

                                        } else {
                                            $use_this = false;
                                            break;
                                        }
                                    }

                                    if ( $use_this ) {
                                        $new_str_array = array_merge( $new_str_array, $synonym_array );
                                        break;
                                    }

                                }
                            }

                        }
                    }

                }

            }

        }

        if ( $new_str_array ) {
            $new_str_array = array_unique( $new_str_array );
            foreach ( $new_str_array as $new_str_array_item ) {
                if ( ! isset( $str_array[$new_str_array_item] ) ) {
                    $str_array[$new_str_array_item] = 1;
                }
            }
        }

        return $str_array;

    }

    /*
     * Wrapper for WPML print
     *
     * @return string Source name
     */
    static public function translate( $name, $value ) {

        $translated_value = $value;

        if ( function_exists( 'icl_t' ) ) {
            $translated_value = icl_t( 'aws', $name, $value );
        }

        if ( $translated_value === $value ) {
            $translated_value = __( $translated_value, 'advanced-woo-search' );
        }

        return $translated_value;

    }

    /*
     * Get current page
     *
     * @return int Page ID
     */
    static public function get_current_page_id() {

        global $wp_query;

        if ( is_shop() ) {
            $value = wc_get_page_id( 'shop' );
        } elseif ( is_cart() ) {
            $value = wc_get_page_id( 'cart' );
        } elseif ( is_checkout() ) {
            $value = wc_get_page_id( 'checkout' );
        } elseif ( is_account_page() ) {
            $value = wc_get_page_id( 'myaccount' );
        } else {
            $value = $wp_query->get_queried_object_id();
        }

        return $value;

    }

    /*
     * Get current active site language
     *
     * @return string Language code
     */
    static public function get_lang() {

        $current_lang = false;

        if ( ( defined( 'ICL_SITEPRESS_VERSION' ) || function_exists( 'pll_current_language' ) ) ) {

            if ( has_filter('wpml_current_language') ) {
                $current_lang = apply_filters( 'wpml_current_language', NULL );
            } elseif ( function_exists( 'pll_current_language' ) ) {
                $current_lang = pll_current_language();
            }

        } elseif( function_exists( 'qtranxf_getLanguage' ) ) {

            $current_lang = qtranxf_getLanguage();

        }  elseif ( defined( 'FALANG_VERSION' ) ) {

            $current_lang = Falang()->get_current_language()->slug;

        }

        return $current_lang;

    }

    /*
     * Get active stock statuses for search query
     *
     * @return array Stock statuses
     */
    static public function get_query_stock_status( $product_stock_status, $reindex_version ) {

        $stock_in = array();

        if ( $product_stock_status && is_array( $product_stock_status ) ) {

            if ( $reindex_version && version_compare( $reindex_version, '2.26', '>=' ) ) {
                foreach( $product_stock_status as $stock_status => $is_active ) {
                    if ( $is_active ) {
                        $val = '';
                        switch ( $stock_status ) {
                            case 'out_of_stock':
                                $val = 0;
                                break;
                            case 'in_stock':
                                $val = 1;
                                break;
                            case 'on_backorder':
                                $val = 2;
                                break;
                        }
                        if ( $val !== '' ) {
                            $stock_in[] = $val;
                        }
                    }
                }
            } else {
                if ( $product_stock_status['out_of_stock'] === '1' && $product_stock_status['in_stock'] === '0' ) {
                    $stock_in[] = 0;
                } elseif ( $product_stock_status['out_of_stock'] === '0' && $product_stock_status['in_stock'] === '1' ) {
                    $stock_in[] = 1;
                }
            }

        }

        if ( is_array( $product_stock_status ) && count( $stock_in ) === count( $product_stock_status ) ) {
            $stock_in = array();
        }

        return $stock_in;

    }

    /*
     * Get search form action link
     *
     * @return string Search URL
     */
    static public function get_search_url() {

        $search_url = home_url( '/' );

        if ( function_exists( 'pll_home_url' ) ) {

            $search_url = pll_home_url();

            if ( get_option( 'show_on_front' ) === 'page' ) {

                $current_language = pll_current_language();
                $default_language = pll_default_language();

                if ( $current_language != $default_language ) {
                    if ( strpos( $search_url, '/' . $current_language ) !== false ) {
                        $language_subdir = $current_language.'/';
                        $search_url = home_url( '/' . $language_subdir );
                    }
                }

            }

        }

        return $search_url;

    }

    /*
     * Generate link for search results page term search
     *
     * @return string Search URL
     */
    static public function get_search_term_url( $s, $atts = array() ) {

        $search_url = AWS_Helpers::get_search_url();
        $form_id = isset( $_GET['aws_id'] ) ? $_GET['aws_id'] : '1';
        $filter_id = isset( $_GET['aws_filter'] ) ? $_GET['aws_filter'] : '1';
        $awscat = isset( $_GET['awscat'] ) ? $_GET['awscat'] : false;
        $current_lang = AWS_Helpers::get_lang();

        $params = shortcode_atts( array(
            's' => urlencode( sanitize_text_field( $s ) ),
            'post_type' => 'product',
            'type_aws' => 'true',
            'aws_id' => $form_id,
            'aws_filter' => $filter_id,
            'lang' => $current_lang,
            'awscat' => $awscat,
        ), $atts );

        $search_url = add_query_arg( $params, $search_url );

        return $search_url;

    }

    /*
     * Get main search form instance ID or just first available
     *
     * @return int Search form instance ID
     */
    static public function get_available_instance_id() {

        $settings = AWS_PRO()->get_settings();
        $main_instances = get_option( 'aws_main_instance' );

        $all_available_instances = array();

        if ( $settings ) {
            foreach( $settings as $search_instance_num => $search_instance_settings ) {
                $all_available_instances[] = $search_instance_num;
            }
        }

        $id = 1;

        if ( $main_instances && array_search( $main_instances, $all_available_instances ) !== false ) {
            $id = $main_instances;
        } elseif ( ! empty( $all_available_instances ) ) {
            $id = $all_available_instances[0];
        }

        return $id;

    }

    /*
     * Check whether the plugin is active by checking the active_plugins list.
     */
    static public function is_plugin_active( $plugin ) {

        return in_array( $plugin, (array) get_option( 'active_plugins', array() ) ) || AWS_Helpers::is_plugin_active_for_network( $plugin );

    }

    /*
     * Check whether the plugin is active for the entire network
     */
    static public function is_plugin_active_for_network( $plugin ) {
        if ( !is_multisite() )
            return false;

        $plugins = get_site_option( 'active_sitewide_plugins');
        if ( isset($plugins[$plugin]) )
            return true;

        return false;
    }

    /*
     * Extract product attributes data
     */
    static public function extract_attributes( $attributes, $id ) {

        $custom_attributes = '';
        $data = array();

        foreach( $attributes as $p_att => $attribute_object ) {

            if ( $attribute_object ) {

                if ( ( is_object( $attribute_object ) && method_exists( $attribute_object, 'is_taxonomy' ) && $attribute_object->is_taxonomy() ) ||
                    ( is_array( $attribute_object ) && isset( $attribute_object['is_taxonomy'] ) && $attribute_object['is_taxonomy'] )
                ) {

                    $attr_slug = ( is_object( $attribute_object ) && method_exists( $attribute_object, 'get_name' ) ) ? $attribute_object->get_name() : $p_att;
                    $attr_slug = str_replace( 'pa_', '', $attr_slug );
                    $attr_slug = AWS_Helpers::normalize_term_name( $attr_slug, $attr_slug );

                    $attr_tax = ( is_object( $attribute_object ) && method_exists( $attribute_object, 'get_taxonomy' ) ) ? $attribute_object->get_taxonomy() : $p_att;

                    $attr_name = 'attr_pa_' . $attr_slug;

                    $attr_array = AWS_Helpers::get_terms_array( $id, $attr_tax, $attr_name );

                    if ( $attr_array && ! empty( $attr_array ) ) {

                        $tax_attr_obj = method_exists( $attribute_object, 'get_taxonomy_object' ) ? $attribute_object->get_taxonomy_object() : '';

                        if ( $tax_attr_obj && property_exists( $tax_attr_obj, 'attribute_name' ) && property_exists( $tax_attr_obj, 'attribute_label' ) ) {
                            $data[$attr_name] = $tax_attr_obj->attribute_name . ' ' . $tax_attr_obj->attribute_label;
                        }

                        foreach( $attr_array as $attr_source => $attr_terms ) {
                            $data[$attr_source] = $attr_terms;
                        }

                    }

                } else {

                    $attr_string = '';

                    if ( function_exists( 'wc_implode_text_attributes' ) && method_exists( $attribute_object, 'get_options' ) ) {
                        $attr_string = wc_implode_text_attributes( $attribute_object->get_options() );
                    } elseif( is_array( $attribute_object ) && isset( $attribute_object['value'] ) ) {
                        $attr_string = $attribute_object['value'];
                    }

                    if ( $attr_string && is_string( $attr_string ) && $attr_string ) {
                        $attr_name = is_object( $attribute_object ) && method_exists( $attribute_object, 'get_name' ) ? $attribute_object->get_name() : '';
                        $custom_attributes = $custom_attributes . ' ' . $attr_name . ' ' . $attr_string;
                    }

                }

            }

        }

        if ( $custom_attributes ) {
            $attr_name = 'attr_custom';
            $data[$attr_name] = $custom_attributes;
        }

        return $data;

    }

    /*
     * Extract product custom fields
     */
    static public function extract_custom_fields( $custom_fields ) {

        $data = array();

        foreach( $custom_fields as $custom_field_key => $custom_field_value ) {

            if ( AWS_Helpers::filter_custom_fields( $custom_field_key ) ) {
                continue;
            }

            if ( is_array( $custom_field_value ) && empty( $custom_field_value ) ) {
                continue;
            }

            $meta_values = array_map( 'maybe_unserialize', $custom_field_value );
            $meta_string_value = '';

            if ( ! empty( $meta_values ) ) {
                $meta_string_value = AWS_Helpers::recursive_implode( ' ', $meta_values );
            }

            if ( $meta_string_value ) {
                $custom_field_key = AWS_Helpers::normalize_term_name( $custom_field_key, $custom_field_key );
                $meta_source = 'meta_' . strtolower( $custom_field_key );
                $data[$meta_source] = $meta_string_value;
            }

        }

        return $data;

    }

    /*
     * Extract product taxonomies
     */
    static public function extract_taxonomies( $id ) {

        $data = array();

        $taxonomies_list = AWS_Helpers::get_taxonomies( false, false );

        if ( $taxonomies_list && ! empty( $taxonomies_list ) ) {

            foreach( $taxonomies_list as $taxonomy_name => $taxonomy_val ) {

                $tax_name = AWS_Helpers::normalize_term_name( $taxonomy_name, $taxonomy_name );
                $tax_name = 'tax_' . $tax_name;
                $tax_array = AWS_Helpers::get_terms_array( $id, $taxonomy_name, $tax_name );

                if ( $tax_array && ! empty( $tax_array ) ) {
                    foreach( $tax_array as $tax_source => $tax_terms ) {
                        $data[$tax_source] = $tax_terms;
                    }
                }

            }
        }

        return $data;

    }

    /*
     * Remove all non-latin chars from term name and normalize it
     *
     * @return string Normalized term name
     */
    static public function normalize_term_name( $name, $initial_name ) {

        $name = preg_replace( '/[^\00-\255]+/u', '', $name );

        if ( ! preg_match( "/[a-zA-Z]+/i", $name ) ) {
            $name = hash( 'crc32', $initial_name );
        }

        return $name;

    }

    /*
     * Get string with current product terms names
     *
     * @return array List of terms names and their sources
     */
    static public function get_terms_array( $id, $taxonomy, $source_name ) {

        $terms = wp_get_object_terms( $id, $taxonomy );

        if ( is_wp_error( $terms ) ) {
            return '';
        }

        if ( empty( $terms ) ) {
            return '';
        }

        $tax_array_temp = array();

        foreach ( $terms as $term ) {
            $source = $source_name . '%' . $term->term_id . '%';
            $tax_array_temp[$source] = $term->name;
        }

        return $tax_array_temp;

    }

    /*
     * Extract terms from content
     *
     * @return Array of extracted and normalized terms
     */
    static public function extract_terms( $str, $source = '' ) {

        $str = AWS_Helpers::normalize_string( $str );

        $str = str_replace( array(
            "Ă‹â€ˇ",
            "Ă‚Â°",
            "Ă‹â€ş",
            "Ă‹ĹĄ",
            "Ă‚Â¸",
            "Ă‚Â§",
            "%",
            "=",
            "Ă‚Â¨",
            "â€™",
            "â€",
            "â€ť",
            "â€ś",
            "â€ž",
            "Â´",
            "â€”",
            "â€“",
            "Ă—",
            '&#8217;',
            "&nbsp;",
            chr( 194 ) . chr( 160 )
        ), " ", $str );

        $str = str_replace( 'Ăź', 'ss', $str );

        if ( $source !== 'attr' ) {
            $str = preg_replace( '/^[a-z]$/i', "", $str );
        }

        $str = trim( preg_replace( '/\s+/', ' ', $str ) );

        /**
         * Filters extracted string
         *
         * @since 1.33
         *
         * @param string $str String of product content
         * @param @since 1.88 string $source Terms source
         */
        $str = apply_filters( 'aws_extracted_string', $str, $source );

        $str_array = explode( ' ', $str );
        $str_array = AWS_Helpers::filter_stopwords( $str_array );
        $str_array = array_count_values( $str_array );

        /**
         * Filters extracted terms before adding to index table
         *
         * @since 1.33
         *
         * @param string $str_array Array of terms
         * @param @since 1.88 string $source Terms source
         */
        $str_array = apply_filters( 'aws_extracted_terms', $str_array, $source );

        $str_new_array = array();

        // Remove e, es, ies from the end of the string
        if ( ! empty( $str_array ) && $str_array ) {
            foreach( $str_array as $str_item_term => $str_item_num ) {
                if ( $str_item_term  ) {

                    if ( ! isset( $str_new_array[$str_item_term] ) && preg_match("/es$/", $str_item_term ) ) {
                        $str_new_array[$str_item_term] = $str_item_num;
                    }

                    $new_array_key = AWS_Helpers::singularize( $str_item_term );

                    if ( $new_array_key && strlen( $str_item_term ) > 3 && strlen( $new_array_key ) > 2 ) {
                        if ( ! isset( $str_new_array[$new_array_key] ) ) {
                            $str_new_array[$new_array_key] = $str_item_num;
                        }
                        if ( $source === 'sku' || $source === 'gtin' ) {
                            $str_new_array[$str_item_term] = $str_item_num;
                        }
                    } else {
                        if ( ! isset( $str_new_array[$str_item_term] ) ) {
                            $str_new_array[$str_item_term] = $str_item_num;
                        }
                    }

                }
            }
        }

        // Add synonyms
        $str_old_array = $str_new_array;

        $str_new_array = AWS_Helpers::get_synonyms( $str_new_array );

        if ( count( $str_old_array ) !== count( $str_new_array ) ) {

            $synonyms_phrases = array();

            foreach ( $str_new_array as $str_new_arr_i => $str_new_arr_num ) {
                $str_new_arr_i = trim( $str_new_arr_i );
                if ( strpos( $str_new_arr_i, ' ' ) !== false ) {
                    $synonyms_phrases_i_arr = explode( ' ', $str_new_arr_i );
                    foreach ( $synonyms_phrases_i_arr as $synonyms_phrases_i_arr_name ) {
                        $synonyms_phrases[$synonyms_phrases_i_arr_name] = 1;
                    }
                }
            }

            if ( ! empty( $synonyms_phrases ) ) {
                $str_new_array = array_merge( $str_new_array, $synonyms_phrases );
            }

        }

        return $str_new_array;

    }

    /*
     * Get taxonomies archive pages that must be available for search
     *
     * @return array List of taxonomies
     */
    static public function get_tax_to_display( $search_archives ) {

        $search_archives_temp = array();

        if ( is_array( $search_archives ) ) {
            foreach ( $search_archives as $search_source => $search_archive_params ) {
                if ( is_array( $search_archive_params ) && isset( $search_archive_params['value'] ) && $search_archive_params['value'] === '1' ) {

                    switch ( $search_source ) {

                        case 'archive_users':
                            break;

                        case 'archive_category':
                            $search_archives_temp[] = 'product_cat';
                            break;

                        case 'archive_tag':
                            $search_archives_temp[] = 'product_tag';
                            break;

                        case 'archive_brand':
                            $search_archives_temp[] = 'product_brand';
                            break;

                        case 'archive_tax':

                            $available_tax = AWS_Helpers::get_taxonomies( 'names', false );

                            if ( $available_tax && ! empty( $available_tax ) ) {
                                if ( isset( $search_archive_params['fields'] ) && is_array( $search_archive_params['fields'] ) && ! empty( $search_archive_params['fields'] ) ) {
                                    foreach( $available_tax as $available_tax_val => $available_tax_label ) {
                                        $available_tax_val = 'tax_' . $available_tax_val;
                                        if ( isset( $search_archive_params['fields'][$available_tax_val] ) && $search_archive_params['fields'][$available_tax_val] ) {
                                            $search_archives_temp[] = $available_tax_label;
                                        }
                                    }
                                }
                            }

                            break;

                        case 'archive_attr':

                            $available_attributes = AWS_Helpers::get_attribute_archives( 'names' );

                            if ( $available_attributes && ! empty( $available_attributes ) ) {
                                if ( isset( $search_archive_params['fields'] ) && is_array( $search_archive_params['fields'] ) && ! empty( $search_archive_params['fields'] ) ) {
                                    foreach( $available_attributes as $available_attribute_val => $available_attribute_label ) {
                                        $available_attribute_val = 'attr_' . $available_attribute_val;
                                        if ( isset( $search_archive_params['fields'][$available_attribute_val] ) && $search_archive_params['fields'][$available_attribute_val] ) {
                                            $search_archives_temp[] = $available_attribute_label;
                                        }
                                    }
                                }
                            }

                            break;

                        default:
                            $search_archives_temp[] = $search_source;

                    }

                }
            }
        }

        return $search_archives_temp;

    }

    /*
     * Get WooCommerce product types
     *
     * @return array Product types
     */
    static public function get_product_types() {
        $types = array_merge( array_keys( wc_get_product_types() ) );
        $types[] = 'product_variation';
        $types[] = 'variation';
        return $types;
    }

    /**
     * Get product quantity
     * @param  object $product Product
     * @return integer
     */
    static public function get_quantity( $product ) {

        $stock_levels = array();

        if ( $product->is_type( 'variable' ) ) {
            foreach ( $product->get_children() as $variation ) {
                $var = wc_get_product( $variation );
                $stock_levels[] = $var->get_stock_quantity();
            }
        } else {
            $stock_levels[] = $product->get_stock_quantity();
        }

        return max( $stock_levels );

    }

    /**
     * Check if terms really exists and get their term_id value
     * @param array $terms Taxonomy terms array
     * @param string $taxonomy Taxonomy name
     * @return array $new_terms_arr
     */
    static public function check_terms( $terms, $taxonomy ) {

        $new_terms_arr = array();
        foreach ( $terms as $term_name ) {

            $term_check = term_exists( $term_name, $taxonomy );
            if ( $term_check && isset( $term_check['term_id'] ) ) {
                $new_terms_arr[] = $term_check['term_id'];
            }

            if ( ! $term_check && strpos( $taxonomy, 'pa_' ) !== 0 ) {
                $term_check = term_exists( $term_name, 'pa_' . $taxonomy );
                if ( $term_check && isset( $term_check['term_id'] ) ) {
                    $new_terms_arr[] = $term_check['term_id'];
                }
            }

        }

        return $new_terms_arr;

    }

    /**
     * Filter search page results by taxonomies
     * @param array $product_terms Available product terms
     * @param array $filter_terms Filter terms
     * @param string $operator Operator
     * @return bool $skip
     */
    static public function page_filter_tax( $product_terms, $filter_terms, $operator = 'OR' ) {

        $skip = true;
        $operator = strtoupper( $operator );

        if ( $filter_terms && is_array( $filter_terms ) && ! empty( $filter_terms ) ) {

            if ( $operator === 'AND' ) {

                $has_all = true;

                foreach( $filter_terms as $term ) {
                    if ( array_search( $term, $product_terms ) === false ) {
                        $has_all = false;
                        break;
                    }
                }

                if ( $has_all ) {
                    $skip = false;
                }

            }

            if ( $operator === 'IN' || $operator === 'OR' ) {

                $has_all = false;

                foreach( $filter_terms as $term ) {
                    if ( array_search( $term, $product_terms ) !== false ) {
                        $has_all = true;
                        break;
                    }
                }

                if ( $has_all ) {
                    $skip = false;
                }

            }

        }

        return $skip;

    }

    /**
     * Get array of index table options
     * @return array $options
     */
    static public function get_index_options() {

        $index_variations_option = AWS_PRO()->get_common_settings( 'index_variations' );
        $index_sources_option = AWS_PRO()->get_common_settings( 'index_sources' );
        $index_shortcodes_option = AWS_PRO()->get_common_settings( 'index_shortcodes' );

        $index_shortcodes = $index_shortcodes_option && $index_shortcodes_option === 'false' ? false : true;

        /**
         * Apply or not WP filters to indexed content
         * @since 1.82
         * @param bool false
         */
        $apply_filters = apply_filters( 'aws_index_apply_filters', false );

        /**
         * Run or not shortcodes inside product content
         * @since 2.46
         * @param bool true
         */
        $do_shortcodes = apply_filters( 'aws_index_do_shortcodes', $index_shortcodes );

        $index_variations = $index_variations_option && $index_variations_option === 'false' ? false : true;

        // check all index fields - are they enabled
        $index_fields_vals = array();
        $all_avaialble_index_fields = array( 'title', 'content', 'sku', 'gtin', 'excerpt', 'brand', 'category', 'tag', 'id', 'attr', 'tax', 'meta' );
        foreach ( $all_avaialble_index_fields as $index_field_name ) {
            $enabled = false;
            if ( is_array( $index_sources_option ) && isset( $index_sources_option[$index_field_name] ) ) {
                if ( is_array( $index_sources_option[$index_field_name] ) ) {
                    if ( isset( $index_sources_option[$index_field_name]['value'] ) && $index_sources_option[$index_field_name]['value'] === '1' ) {
                        $enabled = true;
                    }
                } else {
                    // depricated
                    $enabled = $index_sources_option[$index_field_name];
                }
            }
            $index_fields_vals[$index_field_name] = $enabled;
        }

        // check all index subfields - are they enabled
        $index_subfields_vals = array();
        $all_avaialble_index_subfields = array( 'attr', 'tax', 'meta' );
        foreach ( $all_avaialble_index_subfields as $index_subfield_name ) {
            $index_subfields_vals[$index_subfield_name] = array();
            if ( is_array( $index_sources_option ) && isset( $index_sources_option[$index_subfield_name] ) && isset( $index_sources_option[$index_subfield_name]['fields'] ) && is_array( $index_sources_option[$index_subfield_name]['fields'] ) ) {
                foreach ( $index_sources_option[$index_subfield_name]['fields'] as $field_name => $field_params ) {
                    if ( is_array( $field_params ) && isset( $field_params['value'] ) && $field_params['value'] === '1' ) {
                        $index_subfields_vals[$index_subfield_name][$field_name] = 1;
                    }
                }
            }
        }

        $option_vars = AWS_PRO()->option_vars;
        if ( is_object( $option_vars ) && method_exists( $option_vars, 'get_reindex_version' ) ) {
            $reindex_version = $option_vars->get_reindex_version();
        } else {
            $reindex_version = AWS_PRO_VERSION;
        }

        $index_vars = array(
            'variations' => $index_variations,
            'title' => $index_fields_vals['title'],
            'content' => $index_fields_vals['content'],
            'sku' => $index_fields_vals['sku'],
            'gtin' => $index_fields_vals['gtin'],
            'excerpt' => $index_fields_vals['excerpt'],
            'brand' => $index_fields_vals['brand'],
            'category' => $index_fields_vals['category'],
            'tag' => $index_fields_vals['tag'],
            'id' => $index_fields_vals['id'],
            'attr' => $index_fields_vals['attr'],
            'tax' => $index_fields_vals['tax'],
            'meta' => $index_fields_vals['meta'],
            'attr_sources' => $index_subfields_vals['attr'],
            'tax_sources' => $index_subfields_vals['tax'],
            'meta_sources' => $index_subfields_vals['meta'],
        );

        $options = array(
            'apply_filters'   => $apply_filters,
            'do_shortcodes'   => $do_shortcodes,
            'reindex_version' => $reindex_version,
            'index'           => $index_vars,
        );

        return $options;

    }

    /**
     * Get array of relevance scores
     * @return array $relevance_array
     */
    static public function get_relevance_scores( $data ) {

        $relevance_array = AWS_Helpers::get_default_relevance_scores();

        if ( $data && isset( $data['search_in_weights'] ) && is_array( $data['search_in_weights'] ) ) {
            foreach ( $data['search_in_weights'] as $field => $weight ) {
                $relevance_array[$field] = (int) $weight;
            }
        }

        /**
         * Change relevance scores for product search fields
         * @since 2.53
         * @param array $relevance_array Array of relevance scores
         * @param array $data Array of search query related data
         */
        $relevance_array_filtered = apply_filters( 'aws_relevance_scores', $relevance_array, $data );

        $relevance_array = shortcode_atts( $relevance_array, $relevance_array_filtered, 'aws_relevance_scores' );

        return $relevance_array;

    }

    /**
     * Get array of default relevance scores
     * @return array $default_relevance_array
     */
    static public function get_default_relevance_scores() {

        $default_relevance_array = array(
            'title'     => 350,
            'content'   => 100,
            'sku'       => 300,
            'gtin'      => 500,
            'excerpt'   => 100,
            'brand'     => 100,
            'category'  => 35,
            'tag'       => 35,
            'id'        => 300,
            'attr'      => 35,
            'tax'       => 35,
            'meta'      => 35,
            'other'     => 35,
            'tax_name'  => 350,
            'tax_desc'  => 100,
            'user_name' => 350,
        );

        return $default_relevance_array;

    }

    /*
     * Find duplicates in $relevance_params and combine them
     * @return array $relevance_sources_groups
     */
    static public function grouped_similar_relevance_scores( $relevance_params, $search_in_arr ) {

        $grouped = array();

        foreach ($relevance_params as $key => $values) {
            $groupKey = $values['full'] . '_' . $values['like'];

            if ( array_search( $key, $search_in_arr ) !== false ) {

                if (!isset($grouped[$groupKey])) {
                    $grouped[$groupKey] = [
                        'full' => $values['full'],
                        'like' => $values['like'],
                        'sources' => [],
                    ];
                }

                $grouped[$groupKey]['sources'][] = $key;

            }

        }

        $relevance_sources_groups = array_values(array_filter($grouped, function ($group) {
            return count($group['sources']) > 1;
        }));

        return $relevance_sources_groups;

    }

    /**
     * Check if we should override default search query
     * @param string $query Current query object
     * @return bool
     */
    static public function aws_searchpage_enabled( $query ) {
        $enabled = true;

        $post_type_product = ( $query->get( 'post_type' ) && ( ( is_string( $query->get( 'post_type' ) ) && ( $query->get( 'post_type' ) === 'product' ) ) || ( is_array( $query->get( 'post_type' ) ) && in_array( 'product', $query->get( 'post_type' ) ) ) ) ) ? true :
            ( ( isset( $_GET['post_type'] ) && $_GET['post_type'] === 'product' ) ? true : false );

        if ( ( isset( $query->query_vars['s'] ) && ! isset( $_GET['type_aws'] ) ) ||
            ! isset( $query->query_vars['s'] ) ||
            ! $query->is_search() ||
            ! $post_type_product
        ) {
            $enabled = false;
        }

        return apply_filters( 'aws_searchpage_enabled', $enabled, $query );
    }

    /**
     * Get array of custom data for search results output
     * @param array $results Search results
     * @param array $s_data Search related data
     * @return array
     */
    static public function get_custom_results_data( $results, $s_data ) {

        $results_data = array();
        $notices = array();
        $custom_top_results = array();

        $results_data['top_text'] = apply_filters( 'aws_search_top_text', '', $results, $s_data );

        $results_data['notices'] = apply_filters( 'aws_search_notices', $notices, $results, $s_data );

        $results_data['top_results'] = apply_filters( 'aws_search_custom_top_results', $custom_top_results, $results, $s_data );

        $results_data = apply_filters( 'aws_search_custom_results_data', $results_data, $results, $s_data );

        return (array) $results_data;

    }

    /**
     * Generate all possible combinations or array items
     * @param array $array_groups
     * @return array
     */
    static public function generate_combinations( $array_groups ) {

        $groups = array( array() );
        foreach ( $array_groups as $array ) {
            $tmp = array();
            foreach ($groups as $resultItem) {
                foreach ($array as $item) {
                    $tmp[] = array_merge( $resultItem, array( $item ) );
                }
            }
            $groups = $tmp;
        }

        return $groups;

    }

    /**
     * Get variations of suggested fixed terms that was misspelled
     * @param array $data Search related data
     * @param int $max_terms_to_suggest Max number of suggested terms variations
     * @return array
     */
    static public function get_fixed_terms_suggestions( $data, $max_terms_to_suggest = 3 ) {

        /**
         * Filter number of suggested fixed terms
         * @since 3.10
         * @param int $max_terms_to_suggest Max number of fixed terms suggestions
         * @param array $data Array of search parameters
         */
        $max_terms_to_suggest = apply_filters( 'aws_search_fixed_terms_suggestions_num', $max_terms_to_suggest, $data );

        $terms_suggestions = array();

        if ( isset( $data['similar_terms'] ) && isset( $data['similar_terms']['pairs'] ) ) {

            $terms_pairs = $data['similar_terms']['pairs'];
            $s = $data['s'];

            $similar_groupds = array();
            foreach ( $terms_pairs as $pair ) {
                $tmp = array();
                if ( ! empty( $pair['new'] ) ) {
                    foreach ( $pair['new'] as $new_term ) {
                        $tmp[] = array(
                            'old' => $pair['old'],
                            'new' => $new_term,
                        );
                    }
                }
                $similar_groupds[] = $tmp;
            }

            $terms_groups = AWS_Helpers::generate_combinations( $similar_groupds );

            if ( ! empty( $terms_groups ) ) {
                $count = 0;
                foreach ( $terms_groups as $terms_group ) {
                    if ( ++$count > $max_terms_to_suggest ) {
                        break;
                    }
                    $new_s = $s;
                    foreach ( $terms_group as $terms ) {
                        $new_s = str_replace( $terms['old'], $terms['new'], $new_s );
                    }
                    $terms_suggestions[] = $new_s;
                }
            }

        }

        return $terms_suggestions;

    }

    /**
     * Highlight text words
     * @param string $text Text string
     * @param array $data Search related data
     * @param string $highlight_tag Html tag for highlight
     * @return string
     */
    static public function highlight_words( $text, $data = array(), $highlight_tag = 'strong' ) {

        $pattern = array();
        $search_terms = array();

        if ( ! empty( $data ) ) {
           if ( isset( $data['s_nonormalize'] ) && is_string( $data['s_nonormalize'] ) && $data['s_nonormalize'] ) {
               $search_terms_dirty = array_unique( explode( ' ', $data['s_nonormalize'] ) );
               $search_terms = array_merge( $search_terms, $search_terms_dirty );
           }
           if ( isset( $data['search_terms'] ) && ! empty( $data['search_terms'] ) ) {
               $search_terms = array_merge( $search_terms, $data['search_terms'] );
           }
        }

        if ( $search_terms ) {
            $search_terms = array_fill_keys( $search_terms, 1 );
            $search_terms = AWS_Helpers::get_synonyms( $search_terms );
            $search_terms = array_keys( $search_terms );
        }

        foreach( $search_terms as $search_in ) {

            $search_in = preg_quote( $search_in, '/' );

            if ( $data['search_exact'] === 'true' ) {
                $pattern[] = $search_in;
            } elseif ( strlen( $search_in ) > 1 ) {
                $pattern[] = '(' . $search_in . ')+';
            } else {
                $pattern[] = '\b[' . $search_in . ']{1}\b';
            }

        }

        if ( ! empty( $pattern ) ) {

            usort( $pattern, array( 'AWS_Helpers', 'sort_by_length' ) );
            $pattern = implode( '|', $pattern );

            if ( $data['search_exact'] === 'true' ) {
                $pattern = '\b('. $pattern .')\b';
            }

            $pattern = sprintf( '/%s/i', $pattern );

            $highlight_tag_pattern = '<' . $highlight_tag . '>$0</' . $highlight_tag . '>';

            $text = preg_replace($pattern, $highlight_tag_pattern, $text );

        }

        return $text;

    }

    /**
     * Generate quick filters values array
     * @param array $filters Filters settings
     * @param int $form_id Current form instance id
     * @return array
     */
    static public function get_quick_filters( $filters, $form_id ) {

        $filters_arr = array();

        if ( ! is_array( $filters) || empty( $filters ) ) {
            return $filters_arr;
        }

        foreach ( $filters as $filter_id => $filter_opts ) {
            $filter_names = AWS_Helpers::translate( 'filter_name_' . $form_id . '_' . $filter_id, stripslashes( str_replace( array( '"',"'" ), '', strip_tags( html_entity_decode( $filter_opts['item_name'] ) ) ) ) );

            if ( isset( $filter_opts['filter_type'] ) && $filter_opts['filter_type'] === 'complex' ) {

                $filtering_tax = $filter_opts['filter_by'];
                $hide_empty = isset( $filter_opts['hide_empty'] ) && $filter_opts['hide_empty'] === 'true';
                $preselect = isset( $filter_opts['preselect'] ) && $filter_opts['preselect'] === 'true';
                $only_parents = isset( $filter_opts['only_parents'] ) && $filter_opts['only_parents'] === 'true' ? 0 : '';
                $max_number = isset( $filter_opts['max_number'] ) && $filter_opts['max_number'] ? intval( $filter_opts['max_number'] ) : 0;

                $tax = get_terms( array(
                    'taxonomy'   => $filtering_tax,
                    'hide_empty' => $hide_empty,
                    'number'     => $max_number,
                    'parent'     => $only_parents,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ) );

                if ( ! empty( $tax ) ) {

                    if ( $max_number > 0 && count( $tax ) > $max_number ) {
                        $tax = array_slice( $tax, 0, $max_number );
                    }

                    foreach ( $tax as $tax_item ) {

                        if ( $tax_item->parent == 0 ) {
                            $parent_id = $tax_item->term_id;
                            $tax_id = $filter_id . '-' . $tax_item->term_id;
                            $filters_arr['filters'][][$tax_id] = stripslashes( str_replace( array( '"',"'" ), '', strip_tags( html_entity_decode( $tax_item->name ) ) ) );

                            // get child terms if needed. limit to 2 levels deep
                            if ( ! $only_parents ) {
                                foreach( $tax as $tax_item_l2 ) {
                                    if( $tax_item_l2->parent == $parent_id ) {
                                        $parent_id_l2 = $tax_item_l2->term_id;
                                        $tax_id_l2 = $filter_id . '-' . $tax_item_l2->term_id;
                                        $filters_arr['filters'][][$tax_id_l2] = stripslashes( str_replace( array( '"',"'" ), '', strip_tags( html_entity_decode( '— ' . $tax_item_l2->name ) ) ) );
                                        foreach( $tax as $tax_item_l3 ) {
                                            if( $tax_item_l3->parent == $parent_id_l2 ) {
                                                $tax_id_l3 = $filter_id . '-' . $tax_item_l3->term_id;
                                                $filters_arr['filters'][][$tax_id_l3] = stripslashes( str_replace( array( '"',"'" ), '', strip_tags( html_entity_decode( '— — ' . $tax_item_l3->name ) ) ) );
                                            }
                                        }
                                    }
                                }
                            }

                        }

                    }

                    $filters_arr['preselect'] = 0;

                    // set filter id that must be preselected ( if prselect option is enabled )
                    if ( $preselect && is_tax( $filtering_tax ) && isset( $filters_arr['filters'] ) && ! empty( $filters_arr['filters'] ) ) {
                        $queried_object = get_queried_object();
                        if ( isset( $queried_object->term_id ) ) {
                            $filters_arr['preselect'] = $filter_id . '-' . $queried_object->term_id;
                            if ( $filter_opts['only_parents'] === 'true' || ! self::array_key_exists( $filters_arr['filters'], $filters_arr['preselect'] ) ) {
                                $taxonomy_obj = get_taxonomy( $filtering_tax );
                                if ( $taxonomy_obj && $taxonomy_obj->hierarchical ) {
                                    $term = $queried_object;
                                    while ( $term->parent != 0 && ! self::array_key_exists( $filters_arr['filters'], $filter_id . '-' . $term->term_id ) ) {
                                        $term = get_term( $term->parent, $filtering_tax );
                                    }
                                    $filters_arr['preselect'] = $filter_id . '-' . $term->term_id;
                                }
                            }
                        }
                    }

                }

            } else {

                $filters_arr['filters'][][$filter_id] = $filter_names;

            }

        }

        /**
         * Filter search form filters before output
         * @since 1.66
         * @param array $filters_arr Array of filters
         * @param int $form_id Search form id
         */
        $filters_arr = apply_filters( 'aws_front_filters', $filters_arr, $form_id );

        return $filters_arr;

    }

    /**
     * Check if key exists in quick filters array
     * @param array $filters Filters array
     * @param string $filter_id Filter id
     * @return bool
     */
    static public function array_key_exists( $filters, $filter_id ) {
        foreach ( $filters as $item ) {
            foreach ( $item as $key => $value ) {
                if ( $key === $filter_id ) {
                    return true;
                }
                break;
            }
        }
        return false;
    }

    /**
     * Update search results filtring rules according to current search form filter
     * @param array $adv_filters Current filtering rules
     * @param array $quick_filters Available search filters
     * @param string $filter_id Current filter ID
     * @return array
     */
    static public function update_adv_filters( $adv_filters, $quick_filters, $filter_id ) {

        $filter_id_val = (string) $filter_id;

        $term_id = 0;
        if ( strpos( $filter_id_val, '-' ) !== false ) {
            list( $filter_id, $term_id ) = explode( '-', $filter_id, 2 );
        } else {
            $filter_id = $filter_id_val;
        }

        if ( isset( $quick_filters[$filter_id] ) ) {

            $filter_settings = $quick_filters[$filter_id];

            // single filter. overwrite adv_filters values
            if ( isset( $filter_settings['filter_type'] ) && $filter_settings['filter_type'] === 'simple' && isset( $filter_settings['adv_filters'] ) ) {
                $adv_filters = $filter_settings['adv_filters'];
            }
            // multiply filters. extends adv_filters values
            elseif ( isset( $filter_settings['filter_type'] ) && $filter_settings['filter_type'] === 'complex' && $term_id ) {

                $filtering_tax = $filter_settings['filter_by'];

                $child_term_ids = get_term_children( (int) $term_id, $filtering_tax );
                $all_term_ids   = array_merge( array( $term_id ), is_array( $child_term_ids ) ? $child_term_ids : array() );

                $new_tax_rule = array(
                    'param' => 'product_taxonomy',
                    'suboption' => $filtering_tax,
                    'operator' => 'equal',
                    'value' => $all_term_ids,
                );

                if ( $adv_filters && is_array( $adv_filters ) && ! empty( $adv_filters ) && isset( $adv_filters['product'] ) && ! empty( $adv_filters['product'] ) ) {

                    foreach ( $adv_filters['product'] as $group_id => $group_rules ) {
                        $adv_filters['product'][$group_id]['rule_abc'] = $new_tax_rule;
                    }

                } else {

                    $adv_filters = array();
                    $adv_filters['product']['group_1']['rule_1'] = $new_tax_rule;

                }

            }

        }

        return $adv_filters;

    }

    /*
     * Sort array by its values length
     */
    static public function sort_by_length( $a, $b ) {
        return strlen( $b ) - strlen( $a );
    }

}

endif;