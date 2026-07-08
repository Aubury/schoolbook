<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWS_Search' ) ) :

/**
 * Class for plugin search action
 */
class AWS_Search {

    /**
     * @var AWS_Search Array of all plugin data $data
     */
    private $data = array();

    /**
     * @var AWS_Search ID of current form instance $form_id
     */
    private $form_id = 0;

    /**
     * @var AWS_Search ID of current filter $filter_id
     */
    private $filter_id = 0;

    /**
     * @var AWS_Search Current language $lang
     */
    private $lang = 0;

    /**
     * Return a singleton instance of the current class
     *
     * @return object
     */
    public static function factory() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
            $instance->setup();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {}

    /**
     * Setup actions and filters for all things settings
     */
    public function setup() {

        $this->data['settings'] = get_option( 'aws_pro_settings' );

        if ( isset( $_REQUEST['wc-ajax'] ) ) {
            add_action( 'wc_ajax_aws_action', array( $this, 'action_callback' ) );
        } else {
            add_action( 'wp_ajax_aws_action', array( $this, 'action_callback' ) );
            add_action( 'wp_ajax_nopriv_aws_action', array( $this, 'action_callback' ) );
        }

    }

    /*
     * AJAX call action callback
     */
    public function action_callback() {

        if ( ! defined( 'DOING_AJAX' ) ) {
            define( 'DOING_AJAX', true );
        }

        if ( ! headers_sent() && isset( $_REQUEST['typedata'] ) ) {
            header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
        }

        ob_start();

        $search_results = $this->search();

        ob_end_clean();

        echo json_encode( $search_results );

        die;

    }

    /*
     * AJAX call action callback
     */
    public function search( $keyword = '', $output = 'all' ) {

        global $wpdb;

        $this->form_id   = isset( $_REQUEST['aws_id'] ) ? sanitize_text_field( $_REQUEST['aws_id'] ) : ( isset( $_REQUEST['id'] ) ? sanitize_text_field( $_REQUEST['id'] ) : 1 );
        $this->filter_id = isset( $_REQUEST['aws_filter'] ) ? sanitize_text_field( $_REQUEST['aws_filter'] ) : ( isset( $_REQUEST['filter'] ) ? sanitize_text_field( $_REQUEST['filter'] ) : 1 );
        $this->lang      = isset( $_REQUEST['lang'] ) ? sanitize_text_field( $_REQUEST['lang'] ) : '';

        if ( $this->lang ) {
            do_action( 'wpml_switch_language', $this->lang );
        }

        if ( $this->lang ) {
            $current_lang = $this->lang;
        } else {
            $current_lang = AWS_Helpers::get_lang();
        }

        /**
         * Filter current language code
         * @since 1.50
         * @param string $current_lang Lang code
         */
        $current_lang = apply_filters( 'aws_search_current_lang', $current_lang, $this->form_id, $this->filter_id );

        $cache = AWS_PRO()->get_common_settings( 'cache' );

        $s = $keyword ? esc_attr( $keyword ) : ( isset( $_REQUEST['keyword'] ) ? esc_attr( $_REQUEST['keyword'] ) : '' );
        $s = htmlspecialchars_decode( $s );
        $s = preg_replace('/\s+/', ' ', trim( $s ) );

        /**
         * Filters search string before normalization
         * @since 3.37
         * @param string $string
         */
        $s = apply_filters( 'aws_pre_normalized_search_string', $s );

        $this->data['s_nonormalize'] = $s;

        $s = AWS_Helpers::normalize_string( $s );

        /**
         * Fires each time when performing the search
         * @since 1.50
         * @param string $s Search query
         * @param integer $this->form_id Search form id
         * @param integer $this->filter_id Search form filter id ( @since 2.11 )
         */
        do_action( 'aws_search_start', $s, $this->form_id, $this->filter_id );

        $cache_option_name = '';
            
        if ( $cache === 'true' && ! $keyword && $output === 'all' ) {
            $cache_option_name = AWS_PRO()->cache->get_cache_name( $s, $this->form_id, $this->filter_id );
            $res = AWS_PRO()->cache->get_from_cache_table( $cache_option_name );
            if ( $res ) {
                $cached_value = json_decode( $res );
                if ( $cached_value && ! empty( $cached_value ) ) {
                    return $cached_value;
                }
            }
        }

        $products_array = array();
        $custom_tax_array = array();
        $users_array = array();

        $search_logic          = AWS_PRO()->get_settings( 'search_logic', $this->form_id );
        $search_exact          = AWS_PRO()->get_settings( 'search_exact', $this->form_id );
        $fuzzy                 = AWS_PRO()->get_settings( 'fuzzy', $this->form_id );
        $enable_suggestions    = AWS_PRO()->get_settings( 'enable_suggestions', $this->form_id );
        $search_page_highlight = AWS_PRO()->get_settings( 'search_page_highlight', $this->form_id );
        $style                 = AWS_PRO()->get_settings( 'style', $this->form_id );
        $product_stock_status  = AWS_PRO()->get_settings( 'product_stock_status', $this->form_id );
        $var_rules             = AWS_PRO()->get_settings( 'var_rules', $this->form_id );
        $onsale                = AWS_PRO()->get_settings( 'on_sale', $this->form_id );
        $product_visibility    = AWS_PRO()->get_settings( 'product_visibility', $this->form_id );
        $results_num           = $keyword ? apply_filters( 'aws_page_results', 100 ) : AWS_PRO()->get_settings( 'results_num', $this->form_id );
        $pages_results_num     = AWS_PRO()->get_settings( 'pages_results_num', $this->form_id );
        $exclude_rel           = AWS_PRO()->get_settings( 'exclude_rel', $this->form_id );
        $exclude_cats          = AWS_PRO()->get_settings( 'exclude_cats', $this->form_id );
        $exclude_tags          = AWS_PRO()->get_settings( 'exclude_tags', $this->form_id );
        $exclude_products      = AWS_PRO()->get_settings( 'exclude_products', $this->form_id );
        $search_archives       = AWS_PRO()->get_settings( 'search_archives', $this->form_id );
        $search_archives_heading = AWS_PRO()->get_settings( 'search_archives_heading', $this->form_id );
        $search_archives_hierarchy = AWS_PRO()->get_settings( 'search_archives_hierarchy', $this->form_id );
        $search_archives_count = AWS_PRO()->get_settings( 'search_archives_count', $this->form_id );
        $search_archives_empty = AWS_PRO()->get_settings( 'search_archives_empty', $this->form_id );
        $search_archives_images = AWS_PRO()->get_settings( 'search_archives_images', $this->form_id );
        $adv_filters           = AWS_PRO()->get_settings( 'adv_filters', $this->form_id );
        $quick_filters         = AWS_PRO()->get_settings( 'quick_filters', $this->form_id );

        // update $adv_filters array according to current filter rules
        if ( $quick_filters && $this->filter_id && $this->filter_id != '1' ) {
            $adv_filters = AWS_Helpers::update_adv_filters( $adv_filters, $quick_filters, $this->filter_id );
        }

        $search_rule           = AWS_PRO()->get_common_settings( 'search_rule' );
        $search_words_num      = AWS_PRO()->get_common_settings( 'search_words_num' );

        $this->data['s']                  = $s;
        $this->data['search_terms']       = array();
        $this->data['search_in']          = $this->set_search_in();
        $this->data['search_in_weights']  = $this->get_search_in_weights();
        $this->data['results_num']        = $results_num;
        $this->data['pages_results_num']  = $pages_results_num;
        $this->data['search_archives']    = $search_archives;
        $this->data['search_archives_heading'] = $search_archives_heading;
        $this->data['search_archives_hierarchy'] = $search_archives_hierarchy;
        $this->data['search_archives_count'] = $search_archives_count;
        $this->data['search_archives_empty'] = $search_archives_empty;
        $this->data['search_archives_images'] = $search_archives_images;
        $this->data['search_logic']       = $search_logic ? $search_logic : 'or';
        $this->data['search_exact']       = $search_exact ? $search_exact : 'false';
        $this->data['fuzzy']              = $fuzzy;
        $this->data['suggestions']        = $enable_suggestions;
        $this->data['search_page_highlight'] = $search_page_highlight;
        $this->data['exclude_rel']        = $exclude_rel;
        $this->data['exclude_cats']       = $exclude_cats;
        $this->data['exclude_tags']       = $exclude_tags;
        $this->data['exclude_products']   = $exclude_products;
        $this->data['adv_filters']        = $adv_filters;
        $this->data['product_stock_status'] = $product_stock_status;
        $this->data['var_rules']          = $var_rules;
        $this->data['onsale']             = $onsale;
        $this->data['product_visibility'] = $product_visibility;
        $this->data['search_rule']        = $search_rule;
        $this->data['search_words_num']   = $search_words_num;
        $this->data['form_id']            = $this->form_id;
        $this->data['filter_id']          = $this->filter_id;
        $this->data['is_search_page']     = !! $keyword;
        $this->data['current_lang']       = $current_lang;

        $search_array = array_unique( explode( ' ', $s ) );
        $search_array = AWS_Helpers::filter_stopwords( $search_array );

        $tax_to_display = AWS_Helpers::get_tax_to_display( $search_archives );

        /**
         * Filters array of custom taxonomies that must be displayed in search results
         *
         * @since 1.54
         *
         * @param array $taxonomies_archives Array of custom taxonomies
         * @param string $s Search query
         */
        $taxonomies_archives = apply_filters( 'aws_search_results_tax_archives', $tax_to_display, $s );

        $this->data['taxonomies_archives'] = $taxonomies_archives;

        if ( is_array( $search_array ) && ! empty( $search_array ) ) {
            foreach ( $search_array as $search_term ) {
                $search_term = trim( $search_term );
                if ( $search_term ) {
                    $this->data['search_terms'][] = $search_term;
                }
            }
        }

        if ( $this->data['search_words_num'] && count( $this->data['search_terms'] ) > intval( $this->data['search_words_num'] ) ) {
            $this->data['search_terms'] = array_slice( $this->data['search_terms'], 0, intval( $this->data['search_words_num'] ) );
        }

//        if ( empty( $this->data['search_terms'] ) ) {
//            $this->data['search_terms'][] = '';
//        }

        /**
         * Filter search data parameters
         * @since 2.50
         * @param array $this->data Array of data parameters
         */
        $this->data = apply_filters( 'aws_search_data_parameters', $this->data );

        $posts_ids = array();

        if ( ! empty( $this->data['search_terms'] ) ) {

            if ( ! empty( $this->data['search_in'] ) && $this->data['results_num'] && ! isset( $this->data['posts_ids_rewrite'] ) ) {

                $posts_ids = $this->query_index_table();

                // try to fix misspellings
                if ( empty( $posts_ids ) && ( $fuzzy === 'true' || $fuzzy === 'true_text' ) ) {

                    $similar_terms_obj = new AWS_Similar_Terms( $this->data );
                    $similar_terms_res = $similar_terms_obj->get_similar_terms();

                    if ( ! empty( $similar_terms_res ) && ! empty( $similar_terms_res['all'] ) ) {

                        $this->data['similar_terms'] = $similar_terms_res;

                        $similar_terms = $similar_terms_res['all'];

                        if ( $search_logic === 'and' && isset( $similar_terms[0] ) && is_array( $similar_terms[0] ) ) {
                            foreach ( $similar_terms as $similar_terms_i ) {
                                $this->data['search_terms'] = $similar_terms_i;
                                $temp_posts_ids = $this->query_index_table();
                                if ( ! empty( $temp_posts_ids ) ) {
                                    $posts_ids = array_merge( $posts_ids, $temp_posts_ids );
                                    if ( count( $posts_ids ) >= $results_num ) {
                                        $posts_ids = array_slice( $posts_ids, 0, $results_num );
                                        break;
                                    }
                                }
                            }
                        } else {
                            $this->data['search_terms'] = $similar_terms;
                            $posts_ids = $this->query_index_table();
                        }

                    }

                }

            }

            if ( $output === 'all' ) {

                if ( $taxonomies_archives && is_array( $taxonomies_archives ) && ! empty( $taxonomies_archives ) ) {

                    $tax_search = new AWS_Tax_Search( $taxonomies_archives, $this->data );
                    $custom_tax_array = $tax_search->get_results();

                }

                if ( $search_archives && isset( $search_archives['archive_users'] ) && is_array( $search_archives['archive_users'] ) && isset( $search_archives['archive_users']['value'] ) && $search_archives['archive_users']['value'] === '1' ) {
                    if ( isset( $search_archives['archive_users']['fields'] ) && is_array( $search_archives['archive_users']['fields'] ) ) {
                        $users_search = new AWS_Users_Search( $search_archives['archive_users']['fields'], $this->data );
                        $users_array = $users_search->get_results();
                    }
                }

            }

        }

        // Received $posts_ids from third party source
        if ( isset( $this->data['posts_ids_rewrite'] ) && is_array( $this->data['posts_ids_rewrite'] )  ) {
            $posts_ids = $this->data['posts_ids_rewrite'];
        }

        /**
         * Filters array of products ids
         * @since 1.42
         * @param array $posts_ids Array of products ids
         * @param string $s Search query
         * @param array $this->data Array of search data ( since 3.09 )
         */
        $posts_ids = apply_filters( 'aws_search_results_products_ids', $posts_ids, $s, $this->data );

        if ( empty( $posts_ids ) && empty( $custom_tax_array ) && empty( $users_array ) ) {

            /**
             * If no search results - apply filter to add custom ones
             * @since 3.09
             * @param array $posts_ids Array of products ids
             * @param string $s Search query
             * @param array $this->data Array of search data
             */
            $posts_ids = apply_filters( 'aws_search_no_results', $posts_ids, $s, $this->data );

        }

        // Return array of its to short-circuit search return
        if ( $output === 'ids' ) {
            return $posts_ids;
        }

        $products_array = $this->get_products( $posts_ids );

        $result_array = array(
            'tax'      => $custom_tax_array,
            'users'    => $users_array,
            'products' => $products_array,
            'style'    => $style,
        );

        /**
         * Filters array of all results data before they displayed in search results
         *
         * @since 1.32
         *
         * @param array $result_array Array of products data
         * @param string $s Search query
         */
        $result_array = apply_filters( 'aws_search_results_all', $result_array, $s );

        $result_array['data'] = AWS_Helpers::get_custom_results_data( array( 'products' => $products_array, 'tax' => $custom_tax_array, 'users' => $users_array ), $this->data );

        if ( $cache === 'true' && ! $keyword && $output === 'all' ) {
            AWS_PRO()->cache->insert_into_cache_table( $cache_option_name, $result_array );
        }

        return $result_array;

    }

    /*
     * Query in index table
     */
    private function query_index_table() {

        global $wpdb;

        $table_name = $wpdb->prefix . AWS_INDEX_TABLE_NAME;

        $search_logic       = $this->data['search_logic'];
        $search_exact       = $this->data['search_exact'];
        $search_in_arr      = $this->data['search_in'];
        $results_num        = $this->data['results_num'];
        $exclude_rel        = $this->data['exclude_rel'];
        $exclude_cats       = $this->data['exclude_cats'];
        $exclude_tags       = $this->data['exclude_tags'];
        $exclude_products   = $this->data['exclude_products'];
        $adv_filters        = $this->data['adv_filters'];
        $product_stock_status = $this->data['product_stock_status'];
        $var_rules          = $this->data['var_rules'];
        $onsale             = $this->data['onsale'];
        $product_visibility = $this->data['product_visibility'];
        $search_rule        = $this->data['search_rule'];
        $current_lang       = $this->data['current_lang'];

        $reindex_version = AWS_PRO()->option_vars->get_reindex_version();
        $index_table_version = AWS_PRO()->option_vars->get_index_table_version();

        $query = array();

        $query['select']           = '';
        $query['search']           = '';
        $query['relevance']        = '';
        $query['exclude_terms']    = '';
        $query['exclude_products'] = '';
        $query['adv_filters']      = '';
        $query['stock']            = '';
        $query['sale']             = '';
        $query['visibility']       = '';
        $query['lang']             = '';
        $query['type']             = '';
        $query['having']           = '';

        $search_array = array();
        $search_terms = array();
        $relevance_array = array();
        $new_relevance_array = array();


        /**
         * Filters array of search terms before generating SQL query
         *
         * @since 1.38
         *
         * @param array $this->data['search_terms'] Array of search terms
         */
        $this->data['search_terms'] = apply_filters( 'aws_search_terms', $this->data['search_terms'] );


        /**
         * Multiplier for relevance score depending on number of terms repeats
         * @since 3.06
         * @param array $this->data Search parameters
         */
        $count_multiplier = apply_filters( 'aws_relevance_count_multiplier', '1 + (count-1)/5', $this->data );


        $relevance_scores = AWS_Helpers::get_relevance_scores( $this->data );

        foreach ( $this->data['search_terms'] as $search_term ) {

            $search_term_len = strlen( $search_term );
            $is_normal_term = $search_term_len > 1;

            $relevance_params = array();

            if ( $relevance_scores ) {
                foreach ( $relevance_scores as $relevance_score_name => $relevance_score_value ) {

                    $full_score = $relevance_score_value;
                    $like_score = $relevance_score_value / 5;

                    if ( $relevance_score_name === 'title' ) {
                        $full_score = $relevance_score_value + 20 * $search_term_len;
                        $like_score = $relevance_score_value / 5 + 2 * $search_term_len;
                    }
                    elseif ( $relevance_score_name === 'content' || $relevance_score_name === 'excerpt' ) {
                        $full_score = $relevance_score_value + 4 * $search_term_len;
                        $like_score = $relevance_score_value + 1 * $search_term_len;
                    }
                    elseif ( $relevance_score_name === 'id' ) {
                        $like_score = $relevance_score_value / 10;
                    }

                    $relevance_params[$relevance_score_name] = array(
                        'full' => $full_score,
                        'like' => $like_score,
                    );

                }
            }

            /**
             * Array of relevance parameters
             * @since 2.88
             * @param array $relevance_params Array of relevance parameters
             * @param array $relevance_scores Array of relevance scores
             * @param string $search_term Search term
             * @param array $data Array of search query related data
             */
            $relevance_params = apply_filters( 'aws_relevance_parameters', $relevance_params, $relevance_scores, $search_term, $this->data );

            $search_term_norm = AWS_Helpers::singularize( $search_term );

            if ( $search_term_norm && $search_term_len > 3 && strlen( $search_term_norm ) > 2 ) {
                $search_term = $search_term_norm;
            }

            if ( $search_rule === 'begins' ) {
                $like = $wpdb->esc_like( $search_term ) . '%';
            } else {
                $like = '%' . $wpdb->esc_like( $search_term ) . '%';
            }

            $search_terms[] = $search_term;

            if ( $is_normal_term || ! $search_term_len ) {

                if ( $search_exact === 'true' ) {
                    $search_array[] = $wpdb->prepare( "( term = '%s' )", $search_term );
                } else {
                    $search_array[] = $wpdb->prepare( '( term LIKE %s )', $like );
                }

            } else {
                $search_array[] = $wpdb->prepare( "( term = '%s' )", $search_term );
            }

            $relevance_sources_groups = array();

            // find duplicates in $relevance_params and move them to $relevance_sources_groups
            $relevance_sources_groups = AWS_Helpers::grouped_similar_relevance_scores( $relevance_params, $search_in_arr );

            foreach ( $search_in_arr as $search_in_term ) {

                if ( strlen( $search_in_term) > 50 ) {
                    $search_in_term = substr( $search_in_term, 0, 50 );
                }

                if ( isset( $relevance_params[$search_in_term] ) ) {

                    if ( $relevance_sources_groups ) {
                        foreach ( $relevance_sources_groups as $relevance_sources_group ) {
                            if ( isset( $relevance_sources_group['sources'] ) && ! empty( $relevance_sources_group['sources'] ) && in_array( $search_in_term, $relevance_sources_group['sources'], true ) ) {
                                continue 2;
                            }
                        }
                    }

                    $relevance = $relevance_params[$search_in_term]['full'];
                    $relevance_like = $relevance_params[$search_in_term]['like'];

                    $relevance_array[$search_in_term][] = $wpdb->prepare( "( case when ( term_source = '%s' AND term = '%s' ) then {$relevance} * ( {$count_multiplier} ) else 0 end )", $search_in_term, $search_term );
                    if ( $is_normal_term ) {
                        $relevance_array[$search_in_term][] = $wpdb->prepare( "( case when ( term_source = '%s' AND term LIKE %s ) then {$relevance_like} * ( {$count_multiplier} ) else 0 end )", $search_in_term, $like );
                    }

                } elseif ( $search_in_term ) {

                    $relevance_sources_groups['other']['sources'][] = $search_in_term;
                    $relevance_sources_groups['other']['full'] = $relevance_scores['other'];
                    $relevance_sources_groups['other']['like'] = $relevance_scores['other'] / 5;

                }

            }

            if ( $relevance_sources_groups ) {
                foreach ( $relevance_sources_groups as $relevance_sources_group ) {

                    if ( empty( $relevance_sources_group['sources'] ) || ! is_array( $relevance_sources_group['sources'] ) ) {
                        continue;
                    }

                    $group_sources = array_values( array_unique( $relevance_sources_group['sources'] ) );
                    $group_relevance_score = isset( $relevance_sources_group['full'] ) ? (int) $relevance_sources_group['full'] : 0;
                    $group_relevance_like_score = isset( $relevance_sources_group['like'] ) ? (int) $relevance_sources_group['like'] : 0;

                    if ( ! $group_sources ) {
                        continue;
                    }

                    $source_placeholders = implode( ',', array_fill( 0, count( $group_sources ), '%s' ) );

                    $sql_full = $wpdb->prepare(
                        "( case when ( term_source IN ($source_placeholders) AND term = %s ) then %d * ( {$count_multiplier} ) else 0 end )",
                        array_merge( $group_sources, [ $search_term, $group_relevance_score ] )
                    );

                    $new_relevance_string = $sql_full;

                    if ( $is_normal_term ) {
                        $sql_like = $wpdb->prepare(
                            "( case when ( term_source IN ($source_placeholders) AND term LIKE %s ) then %d * ( {$count_multiplier} ) else 0 end )",
                            array_merge( $group_sources, [ $like, $group_relevance_like_score ] )
                        );

                        $new_relevance_string .= ' + ' . $sql_like;
                    }

                    $new_relevance_array[] = $new_relevance_string;

                }
            }

        }


        // Sort 'relevance' queries in the array by search priority
        foreach ( $search_in_arr as $search_in_item ) {
            if ( isset( $relevance_array[$search_in_item] ) ) {
                $new_relevance_array[] = implode( ' + ', $relevance_array[$search_in_item] );
            }
        }


        $query['select'] = ' distinct ID';
        // Guard against an empty relevance list, which would produce an
        // invalid 'SUM(  )' expression and a fatal SQL syntax error.
        if ( empty( $new_relevance_array ) ) {
            $new_relevance_array[] = '0';
        }
        $query['relevance'] = sprintf( ' (SUM( %s )) ', implode( ' + ', $new_relevance_array ) );
        $query['search'] = sprintf( ' AND ( %s )', implode( ' OR ', $search_array ) );


        $stock_status_in = AWS_Helpers::get_query_stock_status( $product_stock_status, $reindex_version );
        if ( $stock_status_in && ! empty( $stock_status_in ) ) {
            $query['stock'] = sprintf( ' AND in_stock IN ( %s )', implode( ',', $stock_status_in ) );
        }


        if ( $onsale === 'false' ) {
            $query['sale'] = " AND on_sale = 1";
        } elseif ( $onsale === 'not' ) {
            $query['sale'] = " AND on_sale = 0";
        }


        if ( $product_visibility ) {

            $visibility_array = array();
            $visibility_codes = array();

            foreach( $product_visibility as $visibility => $is_active ) {
                if ( $is_active ) {
                    $visibility_codes[] = AWS_PRO()->table_updates->get_visibility_code( $visibility );
                } else {
                    $like = '%' . $wpdb->esc_like( $visibility ) . '%';
                    $visibility_array[] = $wpdb->prepare( '( visibility NOT LIKE %s )', $like );
                }
            }

            if ( $index_table_version && version_compare( $index_table_version, '3.21', '>=' ) ) {
                $query['visibility'] = "";
                if ( ! empty( $visibility_codes ) && count( $visibility_codes ) !== count( $product_visibility ) ) {
                    $query['visibility'] = sprintf( ' AND ( visibility IN ( %s ) )', implode( ',', $visibility_codes ) );
                }
            } elseif( ! empty( $visibility_array ) ) {
                $query['visibility'] = sprintf( ' AND ( %s )', implode( ' AND ', $visibility_array ) );
            }

        }

        // Advanced filters
        if ( isset( $adv_filters['product'] ) && ! empty( $adv_filters['product'] ) ) {

            $adv_filters_arr_obj = new AWS_Search_Filters( $adv_filters['product'], $this->form_id, $this->filter_id );
            $adv_filters_string = $adv_filters_arr_obj->filter();

            if ( $adv_filters_string ) {
                $query['adv_filters'] .= sprintf( ' AND ( %s )', $adv_filters_string );
            }

        }

        /*
         * Exclude certain products from search
         * @deprecated
         * @param array
         */
        $exclude_products_filter = apply_filters( 'aws_exclude_products', array(), $this->form_id, $this->filter_id );

        if ( $exclude_products_filter && is_array( $exclude_products_filter ) && ! empty( $exclude_products_filter ) ) {
            $query['exclude_products'] = sprintf( ' AND ( id NOT IN ( %s ) )', implode( ',', $exclude_products_filter ) );
        }

        // Language option

        if ( $current_lang && $reindex_version && version_compare( $reindex_version, '1.02', '>=' ) ) {
            $query['lang'] = $wpdb->prepare( " AND ( lang LIKE %s OR lang = '' )", '%' . $wpdb->esc_like( $current_lang ) . '%' );
        }


        // Type query
        $query['type'] = " AND ( type NOT LIKE 'child' )";

        if ( $reindex_version && version_compare( $reindex_version, '1.58', '>=' ) ) {
            if ( $var_rules === 'both' ) {
                $query['type'] = "";
            } elseif ( $var_rules === 'child' ) {
                $query['type'] = " AND ( type NOT LIKE 'var' )";
            }
        }

        if ( $index_table_version && version_compare( $index_table_version, '3.21', '>=' ) ) {
            if ( $var_rules === 'both' ) {
                $query['type'] = "";
            } elseif ( $var_rules === 'child' ) {
                $query['type'] = " AND ( type IN ( 0, 2 ) )";
            } else {
                $query['type'] = " AND ( type IN ( 0, 1 ) )";
            }
        }


        // Search logic
        if ( $search_logic === 'and' ) {

            if ( $search_exact === 'true' ) {
                $terms_number = count( $this->data['search_terms'] );
                if ( $terms_number ) {
                    $having_count = $terms_number - 1;
                    if ( $having_count < 0 ) {
                        $having_count = 0;
                    }

                    $search_in_arr_string = '';
                    foreach ( $search_in_arr as $search_in_arr_source ) {
                        $search_in_arr_string .= "'" . $search_in_arr_source . "',";
                    }
                    $search_in_arr_string = rtrim( $search_in_arr_string, "," );

                    $query['search'] .= sprintf( ' AND term_source IN ( %s )', $search_in_arr_string );
                    $query['having'] = sprintf( ' AND count(distinct term) > %s', $having_count );

                }

            } else {
                $and_search_array = array();
                $and_search_sources = array();
                foreach( $search_terms as $and_search_term ) {
                    if ( $and_search_term && strlen( $and_search_term ) > 1 ) {
                        $and_like = '%' . $wpdb->esc_like( $and_search_term ) . '%';
                        $and_search_array[] = $wpdb->prepare( '( term_string LIKE %s )', $and_like );
                    }
                }
                foreach( $search_in_arr as $and_search_source) {
                    $and_search_sources[] = 'term_source="' . $and_search_source . '"';
                }
                if ( $and_search_array ) {
                    $having_string = implode( ' AND ', $and_search_array );
                    $query['search'] .= sprintf( ' AND ( %s )', implode( ' OR ', $and_search_sources ) );
                    $query['select'] = ' distinct ID, GROUP_CONCAT(distinct term) as term_string';
                    $query['having'] = sprintf( ' AND ( %s )', $having_string );
                }
            }

        }

        /**
         * Filter search query parameters
         * @since 1.59
         * @param array $query Query parameters
         * @param int $this->form_id Form id
         * @param int $this->filter_id Filter id
         */
        $query = apply_filters( 'aws_search_query_array', $query, $this->form_id, $this->filter_id );
        
        $sql = "SELECT
                    {$query['select']},
                    {$query['relevance']} as relevance
                FROM
                    {$table_name}
                WHERE
                    1=1
                {$query['search']}
                {$query['adv_filters']}
                {$query['exclude_terms']}
                {$query['exclude_products']}
                {$query['stock']}
                {$query['sale']}
                {$query['visibility']}
                {$query['lang']}
                {$query['type']}
                GROUP BY ID
                  having relevance > 0 {$query['having']}
                ORDER BY 
                    relevance DESC, id DESC
		LIMIT 0, {$results_num}
		";

        /**
         * Filter search query string
         * @since 2.06
         * @param array $query Query string
         * @param int $this->form_id Form id
         * @param int $this->filter_id Filter id
         */
        $sql = apply_filters( 'aws_search_query_string', $sql, $this->form_id, $this->filter_id );
        
        $this->data['query_params'] = $query;

        $this->data['sql'] = $sql;

        $posts_ids = $this->get_posts_ids( $sql );

        return $posts_ids;

    }

    /*
     * Set sources to search_in option
     */
    private function set_search_in() {

        $search_in = AWS_PRO()->get_settings( 'search_in', $this->form_id );
        $search_in_arr = array();

        if ( $search_in && is_array( $search_in ) ) {

            foreach ( $search_in as $search_in_name => $search_in_params ) {
                if ( is_array( $search_in_params ) && isset( $search_in_params['value'] ) && $search_in_params['value'] === '1' ) {
                    if ( isset( $search_in_params['fields'] ) && is_array( $search_in_params['fields'] ) ) {
                        foreach ( $search_in_params['fields'] as $field_name => $field_param ) {
                            $search_in_arr[] = $field_name;
                        }
                    } else {
                        $search_in_arr[] = $search_in_name;
                    }
                }
            }

        } else {
            // depricated
            $search_in_arr = $search_in ? explode( ',',  $search_in ) : array();
        }

        // Drop empty entries so a misconfigured/deprecated 'search_in' setting
        // cannot leave a source list of only empty strings (e.g. explode( ',', '' )
        // returns array( '' ) ), which would yield an empty relevance SUM().
        $search_in_arr = array_values( array_filter( $search_in_arr, 'strlen' ) );

        return $search_in_arr;

    }

    /*
     * Get weights for search in sources
     */
    private function get_search_in_weights() {

        $search_in = AWS_PRO()->get_settings( 'search_in', $this->form_id );

        $search_in_weights_arr = array();

        if ( $search_in && is_array( $search_in ) ) {

            foreach ( $search_in as $search_in_name => $search_in_params ) {
                if ( is_array( $search_in_params ) && isset( $search_in_params['weight'] ) ) {

                    $search_in_weights_arr[$search_in_name] = $search_in_params['weight'];

                    if ( isset( $search_in_params['fields'] ) && is_array( $search_in_params['fields'] ) ) {
                        foreach ( $search_in_params['fields'] as $field_name => $field_param ) {
                            if ( $field_param && is_array( $field_param ) ) {
                                $child_weight_val = isset( $field_param['weight'] ) && $field_param['weight'] !== '' ? $field_param['weight'] : $search_in_params['weight'];
                                $search_in_weights_arr[$field_name] = $child_weight_val;
                            }
                        }
                    }

                }
            }

        }

        return $search_in_weights_arr;

    }

    /*
     * Get array of included to search result posts ids
     */
    private function get_posts_ids( $sql ) {

        global $wpdb;

        $posts_ids = array();

        $search_results = $wpdb->get_results( $sql );


        if ( !empty( $search_results ) && !is_wp_error( $search_results ) && is_array( $search_results ) ) {
            foreach ( $search_results as $search_result ) {
                $post_id = intval( $search_result->ID );
                if ( ! in_array( $post_id, $posts_ids ) ) {
                    $posts_ids[] = $post_id;
                }
            }
        }

        unset( $search_results );

        return $posts_ids;

    }

    /*
     * Get products info
     */
    public function get_products( $posts_ids ) {

        $products_array = array();

        if ( count( $posts_ids ) > 0 ) {

            $excerpt_source       = AWS_PRO()->get_settings( 'desc_source', $this->form_id );

            $desc_scrap_words     = AWS_PRO()->get_settings( 'mark_words', $this->form_id );
            $highlight_words      = AWS_PRO()->get_settings( 'highlight', $this->form_id );
            $style                = AWS_PRO()->get_settings( 'style', $this->form_id );
            $show_excerpt         = AWS_PRO()->get_settings( 'show_excerpt', $this->form_id );
            $excerpt_length       = AWS_PRO()->get_settings( 'excerpt_length', $this->form_id );
            $show_price           = AWS_PRO()->get_settings( 'show_price', $this->form_id );
            $show_outofstockprice = AWS_PRO()->get_settings( 'show_outofstock_price', $this->form_id );
            $show_sale            = AWS_PRO()->get_settings( 'show_sale', $this->form_id );
            $show_sku             = AWS_PRO()->get_settings( 'show_sku', $this->form_id );
            $show_gtin            = AWS_PRO()->get_settings( 'show_gtin', $this->form_id );
            $show_stock_status    = AWS_PRO()->get_settings( 'show_stock', $this->form_id );
            $show_stock_quantity  = AWS_PRO()->get_settings( 'show_stock_quantity', $this->form_id );
            $show_image           = AWS_PRO()->get_settings( 'show_image', $this->form_id );
            $show_brand           = AWS_PRO()->get_settings( 'show_brand', $this->form_id );
            $show_cats            = AWS_PRO()->get_settings( 'show_result_cats', $this->form_id );
            $show_rating          = AWS_PRO()->get_settings( 'show_rating', $this->form_id );
            $show_featured        = AWS_PRO()->get_settings( 'show_featured', $this->form_id );
            $show_variations      = AWS_PRO()->get_settings( 'show_variations', $this->form_id );
            $show_add_to_cart     = AWS_PRO()->get_settings( 'show_cart', $this->form_id );
            $show_cart_quantity   = AWS_PRO()->get_settings( 'show_cart_quantity', $this->form_id );


            $posts_items = $posts_ids;


            foreach ( $posts_items as $post_item ) {

                if ( ! is_object( $post_item ) ) {
                    $product = wc_get_product( $post_item );
                } else {
                    $product = $post_item;
                }

                if ( ! is_a( $product, 'WC_Product' ) ) {
                    continue;
                }

                setup_postdata( $post_item );

                $post_id = method_exists( $product, 'get_id' ) ? $product->get_id() : $post_item;
                $parent_id = $product->is_type( 'variation' ) && method_exists( $product, 'get_parent_id' ) ? $product->get_parent_id() : $post_id;

                /**
                 * Filter additional product data
                 * @since 1.51
                 * @param array $this->data Additional data
                 * @param int $post_id Product id
                 * @param object $product Product
                 */
                $this->data = apply_filters( 'aws_search_data_params', $this->data, $post_id, $product );

                $post_data = get_post( $post_id );

                $title = isset( $post_data->post_title ) ? $post_data->post_title : '';
                $title = apply_filters( 'the_title', $title, $post_id );

                // Add custom variation attributes to product title
                if ( $product->is_type( 'variation' ) && class_exists( 'WC_Product_Variation' ) ) {
                    $variation_product = new WC_Product_Variation( $post_id );
                    if ( method_exists( $variation_product, 'get_attributes' ) ) {
                        $variation_attr = $variation_product->get_attributes();
                        $attr_to_title = array();

                        if ( $variation_attr && is_array( $variation_attr ) ) {
                            foreach( $variation_attr as $variation_p_att => $variation_p_text ) {

                                if ( strpos( $variation_p_att, 'pa_' ) === 0 ) {
                                    $attr_term = get_term_by( 'slug', $variation_p_text, $variation_p_att );
                                    if ( ! is_wp_error( $attr_term ) && $attr_term && $attr_term->name ) {
                                        $variation_p_text = $attr_term->name;
                                    }
                                }

                                if ( $variation_p_text ) {
                                    $attr_to_title[] = $variation_p_text;
                                }

                            }
                        }

                        if ( $attr_to_title && strpos( $title, '&#8211;' ) === false ) {
                            $title = get_the_title( $parent_id );
                            $title = $title . ' &#8211; ' . implode( ', ', $attr_to_title );
                        }

                    }
                }


                $title = AWS_Helpers::html2txt( $title );


                $excerpt = '';
                $price   = '';
                $on_sale = '';
                $sku = '';
                $gtin = '';
                $brands = '';
                $stock_status = '';
                $image = '';
                $categories = '';
                $rating = '';
                $is_featured = '';
                $reviews = '';
                $variations = '';
                $add_to_cart = '';

                if ( $show_excerpt === 'true' ) {

                    $excerpt = ( $excerpt_source === 'excerpt' && $post_data->post_excerpt ) ? $post_data->post_excerpt : $post_data->post_content;
                    $excerpt = AWS_Helpers::html2txt( $excerpt );
                    $excerpt = str_replace('"', "'", $excerpt);
                    $excerpt = strip_shortcodes( $excerpt );
                    $excerpt = AWS_Helpers::strip_shortcodes( $excerpt );

                    if ( $desc_scrap_words === 'true'  ) {

                        $marked_content = $this->scrap_content( $excerpt );

                        if ( $marked_content ) {
                            $excerpt = $marked_content;
                        }
                    }

                    $excerpt = $excerpt_length ? wp_trim_words( $excerpt, $excerpt_length, '...' ) : '';

                }

                if ( $show_price === 'true' && ( $product->is_in_stock() || ( ! $product->is_in_stock() && $show_outofstockprice === 'true' ) ) ) {
                    $price = $product->get_price_html();
                    $price = preg_replace("/<a\s(.+?)>(.+?)<\/a>/is", "<span>$2</span>", $price);
                }

                if ( $show_sale === 'true' && ( $product->is_in_stock() || ( ! $product->is_in_stock() && $show_outofstockprice === 'true' ) ) ) {
                    $on_sale = $product->is_on_sale();
                }

                if ( $show_sku === 'true' ) {
                    $sku = $product->get_sku();
                }

                if ( $show_gtin === 'true' ) {
                    $gtin = method_exists( $product, 'get_global_unique_id' ) ? $product->get_global_unique_id() : '';
                }

                if ( method_exists( $product, 'get_stock_status' ) ) {
                    $product_stock_status = $product->get_stock_status();
                } else {
                    $product_stock_status = false;
                }

                if ( $show_stock_status === 'true' ) {
                    if ( $product->is_in_stock() && $product_stock_status !== 'onbackorder' ) {
                        
                        if ( $show_stock_quantity === 'true' && $product->get_stock_quantity() ) {
                            $stock_status = array(
                                'status' => true,
                                'text'   => $product->get_stock_quantity() . ' ' . esc_html__( 'In stock', 'woocommerce' )
                            );
                        } else {
                            $stock_status = array(
                                'status' => true,
                                'text'   => esc_html__( 'In stock', 'woocommerce' )
                            );
                        }
                       
                    } else {
                        $stock_status = array(
                            'status' => false,
                            'text'   => $product_stock_status === 'onbackorder' ? esc_html__( 'On backorder', 'woocommerce' ) : esc_html__( 'Out of stock', 'woocommerce' )
                        );
                    }
                }
              
                if ( $show_image === 'true' ) {
                    $image_size = ( $style === 'style-big-grid' ) ? 'normal' : 'thumbnail';
                    $image = $this->get_image( $product, $image_size, $post_data );
                }

                if ( $show_brand === 'true' ) {
                    $brands = $this->get_product_brands( $parent_id );
                }

                if ( $show_cats === 'true' ) {
                    $categories = $this->get_terms_list( $parent_id, 'product_cat' );
                }

                if ( $show_rating === 'true' && method_exists( $product, 'get_average_rating' ) ) {
                    $rating = $product->get_average_rating();
                    $rating = $rating ? $rating * 20 : '';
                    if ( method_exists( $product, 'get_review_count' ) ) {
                        $reviews = sprintf( _nx( '1 Review', '%1$s Reviews', $product->get_review_count(), 'product reviews', 'advanced-woo-search' ), number_format_i18n( $product->get_review_count() ) );
                    }
                }

                if ( $show_featured === 'true' ) {
                    $is_featured = $product->is_featured();
                }

                if ( $show_variations === 'true' ) {
                    if ( $product->is_type( 'variable' ) && method_exists( $product, 'get_variation_attributes' ) ) {

                        $variations_array = $product->get_variation_attributes();
                        $variations = array();

                        foreach ( $variations_array as $variation_key => $variation_value ) {
                            if ( strpos( $variation_key, 'pa_' ) === 0 ) {
                                $attr_tax = get_taxonomy( $variation_key );
                                if ( ! is_wp_error( $attr_tax ) && $attr_tax ) {
                                    $attr_terms = get_the_terms( $post_id, $variation_key );
                                    if ( ! is_wp_error( $attr_terms ) && ! empty( $attr_terms ) ) {
                                        $attr_terms_array = array();
                                        foreach ( $attr_terms as $attr_term ) {
                                            if ( in_array( $attr_term->slug, $variation_value ) ) {
                                                $attr_terms_array[] = $attr_term->name;
                                            }
                                        }

                                        if ( ! empty( $attr_terms_array ) ) {
                                            $variations[$attr_tax->labels->singular_name] = $attr_terms_array;
                                        }

                                    }
                                }
                            } else {
                                $variations[$variation_key] = $variation_value;
                            }
                        }

                    }
                }

                if ( $show_add_to_cart !== 'false' && class_exists( 'WC_AJAX' ) ) {
                    $add_to_cart = AWS_Helpers::get_product_cart_args( $product, $show_cart_quantity );
                }

                $tags = $this->get_terms_list( $post_id, 'product_tag' );

                if ( method_exists( $product, 'get_price' ) ) {
                    $f_price = $product->get_price();
                }

                if ( method_exists( $product, 'get_average_rating' ) ) {
                    $f_rating  = $product->get_average_rating();
                }

                if ( method_exists( $product,'get_review_count' ) ) {
                    $f_reviews = $product->get_review_count();
                }

                $f_stock = $product->is_in_stock();
                $f_sale  = $product->is_on_sale();

                if ( $highlight_words === 'true'  ) {
                    $title      = $this->highlight_words( $title );
                    $excerpt    = $this->highlight_words( $excerpt );
                    $sku        = $this->highlight_words( $sku );
                    $gtin       = $this->highlight_words( $gtin );
                    $categories = $this->highlight_words( $categories );
                }

                $title   = apply_filters( 'aws_title_search_result', $title, $post_id, $product );
                $excerpt = apply_filters( 'aws_excerpt_search_result', $excerpt, $post_id, $product );

                if ( ! isset( $this->data['is_search_page'] ) || ! $this->data['is_search_page'] ) {
                    $post_data->post_content = '';
                    $post_data->post_excerpt = '';
                }

                $new_result = array(
                    'id'           => $post_id,
                    'parent_id'    => $parent_id,
                    'title'        => $title,
                    'excerpt'      => $excerpt,
                    'link'         => get_permalink( $post_id ),
                    'image'        => $image,
                    'price'        => $price,
                    'categories'   => $categories,
                    'tags'         => $tags,
                    'brands'       => $brands,
                    'on_sale'      => $on_sale,
                    'sku'          => $sku,
                    'gtin'         => $gtin,
                    'stock_status' => $stock_status,
                    'featured'     => $is_featured,
                    'rating'       => $rating,
                    'reviews'      => $reviews,
                    'variations'   => $variations,
                    'add_to_cart'  => $add_to_cart,
                    'f_price'      => $f_price,
                    'f_rating'     => $f_rating,
                    'f_reviews'    => $f_reviews,
                    'f_stock'      => $f_stock,
                    'f_sale'       => $f_sale,
                    'post_data'    => $post_data
                );

                /**
                 * Filter single product search result
                 * @since 2.49
                 * @param array $new_result Product data array
                 * @param int $post_id Product id
                 * @param object $product Product
                 */
                $new_result = apply_filters( 'aws_search_pre_filter_single_product', $new_result, $post_id, $product );

                $products_array[] = $new_result;

                wp_reset_postdata();

            }

        }

        /**
         * Filter products array before output
         * @since 1.51
         * @param array $products_array Products array
         * @param array $this->data Additional data
         */
        $products_array = apply_filters( 'aws_search_pre_filter_products', $products_array, $this->data );

        $s = isset( $this->data['s'] ) ? $this->data['s'] : '';

        /**
         * Filters array of products before they displayed in search results
         *
         * @since 1.31
         *
         * @param array $products_array Array of products results
         * @param string $s Search query
         */
        $products_array = apply_filters( 'aws_search_results_products', $products_array, $s );

        return $products_array;

    }

    /*
	 * Get array of product brands
	 *
	 * @return array Array of brands
	 */
    private function get_product_brands( $id ) {

        $terms = get_the_terms( $id, 'product_brand' );

        if ( is_wp_error( $terms ) ) {
            return '';
        }

        if ( empty( $terms ) ) {
            return '';
        }

        $brands_array = array();

        foreach ( $terms as $term ) {

            $thumb_src = AWS_Helpers::get_term_thumbnail( $term->term_id );

            $brands_array[] = array(
                'name'  => $term->name,
                'image' => $thumb_src
            );

        }

        return $brands_array;

    }

    /*
	 * Get string with current product terms
	 *
	 * @return string List of terms
	 */
    private function get_terms_list( $id, $taxonomy ) {

        $terms = get_the_terms( $id, $taxonomy );

        if ( is_wp_error( $terms ) ) {
            return '';
        }

        if ( empty( $terms ) ) {
            return '';
        }

        $cats_array_temp = array();

        foreach ( $terms as $term ) {
            if ( is_object( $term ) && property_exists( $term, 'name' ) ) {
                $cats_array_temp[] = $term->name;
            }
        }

        return implode( ', ', $cats_array_temp );

    }

    /*
     * Get product image
     *
     * @return string Image url
     */
    private function get_image( $product, $image_size, $post_data ) {

        $image_sources = AWS_PRO()->get_settings( 'image_source', $this->form_id );
        $default_img   = AWS_PRO()->get_settings( 'default_img', $this->form_id );

        /**
         * Filter products images size
         * @since 2.06
         * @param string $image_size Image size
         */
        $image_size = apply_filters( 'aws_image_size', $image_size );

        $post_id = $post_data->ID;

        $image_src = '';

        if ( ! is_array( $image_sources ) || empty( $image_sources ) ) {
            $image_sources = array( 'featured' => 1 );
        }

        foreach ( $image_sources as $image_source => $source_val ) {

            if ( ! intval( $source_val ) ) {
                continue;
            }

            switch( $image_source ) {

                case 'featured';

                    $post_thumbnail_id = get_post_thumbnail_id( $post_id );

                    if ( ! $post_thumbnail_id && method_exists( $product, 'get_image_id' ) ) {
                        $post_thumbnail_id = $product->get_image_id();
                    }

                    if ( $post_thumbnail_id ) {
                        $thumb_url_array = wp_get_attachment_image_src( $post_thumbnail_id, $image_size );
                        $image_src = $thumb_url_array ? $thumb_url_array[0] : '';
                    }

                    break;

                case 'gallery';

                    $gallery_images_array = method_exists( $product, 'get_gallery_image_ids' ) ? $product->get_gallery_image_ids() : ( method_exists( $product, 'get_gallery_attachment_ids' ) ? $product->get_gallery_attachment_ids() : array() );

                    if ( ! empty( $gallery_images_array ) ) {
                        $gallery_url_array = wp_get_attachment_image_src( $gallery_images_array[0], $image_size );
                        $image_src = $gallery_url_array ? $gallery_url_array[0] : '';
                    }

                    break;

                case 'content';

                    $image_src = $this->scrap_img( $post_data->post_content );

                    break;

                case 'description';

                    $image_src = $this->scrap_img( $post_data->post_excerpt );

                    break;

                case 'default';

                    $image_src = $default_img;

                    break;

            }

            if ( $image_src ) {
                break;
            }

        }

        return $image_src;

    }

    /*
     * Scrap img src from string
     *
     * @return string Image src url
     */
    private function scrap_img( $content ) {
        preg_match( '|<img.*?src=[\'"](.*?)[\'"].*?>|i', $content, $matches );
        $result = ( isset( $matches[1] ) && $matches[1] ) ? $matches[1] : '';
        return $result;
    }

    /*
     * Scrap content excerpt
     */
    private function scrap_content( $content ) {

        $exact_words = array();
        $words = array();
        $excerpt_length = AWS_PRO()->get_settings( 'excerpt_length', $this->form_id );

        $search_terms = array();

        if ( ! empty( $this->data['search_terms'] ) ) {
            $search_terms = array_fill_keys( $this->data['search_terms'], 1);
            $search_terms = AWS_Helpers::get_synonyms( $search_terms );
            $search_terms = array_keys( $search_terms );
        }

        foreach( $search_terms as $search_in ) {

            $search_in = preg_quote( $search_in, '/' );
            $exact_words[] = '\b' . $search_in . '\b';

            if ( strlen( $search_in ) > 1 ) {
                $words[] = $search_in;
            } else {
                $words[] = '\b' . $search_in . '\b';
            }

        }

        usort( $exact_words, array( $this, 'sort_by_length' ) );
        $exact_words = implode( '|', $exact_words );

        usort( $words, array( $this, 'sort_by_length' ) );
        $words = implode( '|', $words );

        preg_match( '/([^.?!]*?)(' . $exact_words . '){1}(.*?[.!?])/i', $content, $matches );

        if ( ! isset( $matches[0] ) ) {
            preg_match( '/([^.?!]*?)(' . $words . '){1}(.*?[.!?])/i', $content, $matches );
        }

        if ( isset( $matches[0] ) ) {

            $content = $matches[0];

            // Trim to long content
            if (str_word_count(strip_tags($content)) > 34) {

                if (str_word_count(strip_tags($matches[3])) > 34) {
                    $matches[3] = wp_trim_words($matches[3], 30, '...');
                }

                $content = '...' . $matches[2] . $matches[3];

            }

        } else {

            // Get first N sentences
            if ( str_word_count( strip_tags( $content ) ) > $excerpt_length ) {

                $sentences_array = preg_split( "/(?<=[.!?])/", $content, 10, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
                $sentences_string = '';
                $str_word_count = 0;

                if ( ! empty( $sentences_array ) ) {
                    foreach ( $sentences_array as $sentence ) {
                        $str_word_count = $str_word_count + str_word_count( strip_tags( $sentence ) );
                        if ( $str_word_count <= $excerpt_length ) {
                            $sentences_string .= $sentence;
                        } else {
                            break;
                        }
                    }
                }

                if ( $sentences_string ) {
                    $content = $sentences_string;
                }

            }

        }

        return $content;

    }

    /*
     * Highlight search words
     */
    private function highlight_words( $text ) {

        if ( ! $text ) {
            return $text;
        }

        /**
         * Tag to use for highlighting search words inside content
         * @since 1.79
         * @param string Tag for highlighting
         */
        $highlight_tag = apply_filters( 'aws_highlight_tag', 'strong' );

        $text = AWS_Helpers::highlight_words( $text, $this->data, $highlight_tag );

        return $text;

    }

    /*
     * Sort array by its values length
     */
    private function sort_by_length( $a, $b ) {
        return strlen( $b ) - strlen( $a );
    }

}

endif;


AWS_Search::factory();

function aws_search( $keyword = '', $output = 'all' ) {

    ob_start();

    $search_results = AWS_Search::factory()->search( $keyword, $output );

    ob_end_clean();

    return $search_results;

}