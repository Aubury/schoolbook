<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWS_Search_Suggestions' ) ) :

    /**
     * Class for misspellings fix
     */
    class AWS_Search_Suggestions {

        /**
         * @var AWS_Search_Suggestions Search data
         */
        private $data = array();

        /**
         * @var AWS_Search_Suggestions Suggestions parameters
         */
        private $config = array();

        /**
         * @var AWS_Search_Suggestions Search terms array
         */
        private $search_terms = array();

        /**
         * @var AWS_Search_Suggestions Fixed search terms array
         */
        private $similar_terms = array();

        /**
         * @var AWS_Search_Suggestions All available terms to search for
         */
        private $available_terms = array();

        /**
         * @var AWS_Search_Suggestions Common terms of specific products
         */
        private $common_terms = array();

        /**
         * @var AWS_Search_Suggestions Common terms of specific products
         */
        private $popular_terms = array();

        /**
         * Constructor
         */
        public function __construct( $data, $atts = array() ) {

            $this->data = $data;

            $this->search_terms = isset( $this->data['search_terms'] ) ? $this->data['search_terms'] : array();

            $form_id = isset( $this->data['form_id'] ) ? $this->data['form_id'] : 1;

            $max_number = AWS_PRO()->get_settings( 'suggestions_max_number', $form_id );
            $suggestion_words_max = AWS_PRO()->get_settings( 'suggestions_words_max', $form_id );
            $sources_arr = AWS_PRO()->get_settings( 'suggestions_sources', $form_id );

            $sources = array();
            if ( ! empty( $sources_arr ) ) {
                foreach ( $sources_arr as $source_name => $source_val ) {
                    if ( $source_val ) {
                        $sources[] = $source_name;
                    }
                }
            } else {
                $sources = array( 'title' );
            }

            $this->config = shortcode_atts( array(
                'max_number' => intval( $max_number ), // max number of possible suggestion
                'suggestion_words_max' => intval( $suggestion_words_max ), // max number of words per suggestion
                'sources' => $sources, // product fields to use
                'min_word_length' => 2, // min number of chars for search term to use for suggestios generation
                'min_repeats' => 1,  // min number of word total repeats to be added to suggestios phrase
                'misspell_fix' => true, // misspelling fix
                'include_variations' => false, // use child product titles for suggestions
            ), $atts );

            /**
             * Filter suggestions related parameters
             * @since 3.23
             * @param array $this->config Array of suggestions parameters
             * @param array $this->data Array of search parameters
             */
            $this->config = apply_filters( 'aws_suggestions_params', $this->config, $this->data );

            $this->similar_terms = $this->get_similar_terms();

            $this->available_terms = array_merge( $this->search_terms, $this->similar_terms );

        }

        /*
         * Try to fix misspellings and get similar terms
         */
        private function get_similar_terms() {

            $similar_terms = array();

            if ( ! $this->config['misspell_fix'] ) {
                return array();
            }

            $data_params = $this->data;
            $data_params['search_logic'] = 'or';
            //$data_params['search_exact'] = 'true';
            $data_params['search_in'] = $this->config['sources'];

            $similar_terms_obj = new AWS_Similar_Terms( $data_params );
            $similar_terms_res = $similar_terms_obj->get_similar_terms();

            if ( ! empty( $similar_terms_res ) && ! empty( $similar_terms_res['all'] ) ) {

                $similar_terms = array_diff( $similar_terms_res['all'], $this->search_terms );

            }

            return $similar_terms;

        }

        /*
         * Get search suggestions
         */
        public function get_suggestions() {

            if ( empty( $this->config['sources'] ) ) {
                return array();
            }

            $updated_terms_array = array();

            $suggestions = array();
            $suggestions_final = array();

            $total_words = count( $this->search_terms );

            foreach ( $this->available_terms as $search_term ) {
                if ( strlen( $search_term ) > $this->config['min_word_length'] ) {
                    $updated_terms_array[] = $search_term;
                }
            }

            $suggestions = $this->get_possible_suggestions( $updated_terms_array );
            $products_ids = array();

            if ( $suggestions ) {

                foreach ( $suggestions as $suggestion ) {

                    $products_ids[] = $suggestion['ID'];

                }

            }

            $all_product_terms = $this->get_producs_terms( $products_ids );

            // get populat terms
            $this->popular_terms = $this->get_common_terms( $all_product_terms );

            // generate suggestions
            if ( $all_product_terms ) {

                $all_suggests = array();

                foreach ( $all_product_terms as $pr_t ) {

                    $word_found_pos = false;
                    $type = false;

                    $product_terms_arr = explode(',', $pr_t['terms'] );

                    // filters terms by min_repeats
                    if ( $this->config['min_repeats'] > 1 ) {
                        $product_terms_arr = array_intersect( $product_terms_arr, array_keys( $this->common_terms ) );
                        $product_terms_arr = array_values( $product_terms_arr );
                    }

                    if ( ! empty( $product_terms_arr ) ) {

                        // check position of each word

                        $terms_vals = array();
                        $terms_found = array();

                        foreach ( $this->available_terms as $search_term ) {

                            $is_fixed_term = in_array( $search_term, $this->similar_terms );

                            $term_val = array(
                                'term' => $search_term,
                                'type' => false,
                                'pos' => false
                            );

                            foreach ( $product_terms_arr as $word_pos => $product_word ) {
                                // check exact
                                if ( $product_word === $search_term ) {
                                    $term_val['pos'] = $word_pos;
                                    $term_val['type'] = 'exact';
                                    $terms_found[] = $product_word;
                                }
                            }

                            if ( $term_val['pos'] === false && ! $is_fixed_term ) {
                                // check start with
                                foreach ( $product_terms_arr as $word_pos => $product_word ) {
                                    if ( strpos( $product_word, $search_term ) === 0 ) {
                                        $term_val['pos'] = $word_pos;
                                        $term_val['type'] = 'starts';
                                        $terms_found[] = $product_word;
                                    }
                                }
                            }

                            if ( $term_val['pos'] === false && ! $is_fixed_term ) {
                                // check contains
                                foreach ( $product_terms_arr as $word_pos => $product_word ) {
                                    if ( strpos( $product_word, $search_term ) !== false ) {
                                        $term_val['pos'] = $word_pos;
                                        $term_val['type'] = 'contains';
                                        $terms_found[] = $product_word;
                                    }
                                }
                            }

                            $terms_vals['terms'][] = $term_val;

                        }

                        if ( ! empty( $terms_found ) ) {

                            $length = count($product_terms_arr);
                            $last_pos = $length - 1;

                            $is_terms_stands_nearby = false;
                            $min_position = false;
                            $max_position = false;

                            $positions = array_filter(array_column($terms_vals['terms'], 'pos'), function($value) {
                                return $value !== false && $value !== null;
                            });

                            sort( $positions );

                            $min_position = min($positions);
                            $max_position = max($positions);

                            $is_terms_stands_nearby = $this->is_consecutive( $positions );

                            // add similar terms as suggestions
                            if ( $total_words < 3 ) {
                                $similar_terms = array();
                                foreach ( $terms_vals['terms'] as $term_found ) {
                                    $is_fixed_term = in_array( $term_found['term'], $this->similar_terms );
                                    if ( $term_found['type'] !== 'exact' && $term_found['pos'] !== false ) {
                                        $word_found_pos = $term_found['pos'];
                                        $similar_terms[] = $product_terms_arr[$word_found_pos];
                                    }
                                    if ( $is_fixed_term && $term_found['type'] === 'exact' && $term_found['pos'] !== false ) {
                                        $similar_terms[] = $term_found['term'];
                                    }

                                }
                                $all_suggests[] = $similar_terms;
                            }

                            $terms = array();

                            // if terms is near each other
                            if ( $is_terms_stands_nearby ) {

                                if ( $min_position === 0 && isset( $product_terms_arr[$max_position+1] ) ) {
                                    // first and second terms
                                    foreach ( $positions as $position ) {
                                        $terms[] = $product_terms_arr[$position];
                                    }
                                    $terms[] = $product_terms_arr[$max_position+1];
                                }
                                elseif ( $min_position === 1 ) {
                                    // first and second terms
                                    $terms[] = $product_terms_arr[0];
                                    foreach ( $positions as $position ) {
                                        $terms[] = $product_terms_arr[$position];
                                    }
                                }
                                elseif ( $last_pos === $max_position && isset( $product_terms_arr[$min_position-1] ) ) {
                                    // get last and prev terms
                                    $terms[] = $product_terms_arr[$min_position-1];
                                    foreach ( $positions as $position ) {
                                        $terms[] = $product_terms_arr[$position];
                                    }
                                }
                                elseif ( isset( $product_terms_arr[$min_position-1] ) && isset( $product_terms_arr[$max_position+1] ) ) {
                                    // get terms nearby
                                    $terms[] = $product_terms_arr[$min_position-1];
                                    foreach ( $positions as $position ) {
                                        $terms[] = $product_terms_arr[$position];
                                    }
                                    $terms[] = $product_terms_arr[$max_position+1];
                                }

                            } else {

                                $generate_range = range( $min_position, $max_position );

                                // add all terms in range
                                foreach ( $product_terms_arr as $word_pos => $product_word ) {
                                    if ( in_array( $word_pos, $generate_range ) ) {
                                        $terms[] = $product_word;
                                    }
                                }

                                if ( $min_position === 2 && count($terms) + 2 <= $this->config['suggestion_words_max'] ) {
                                    // add first and second terms
                                    array_unshift( $terms, $product_terms_arr[0] );
                                } elseif ( $min_position === 1 && count($terms) + 1 <= $this->config['suggestion_words_max'] ) {
                                    // add first term
                                    array_unshift( $terms, $product_terms_arr[0] );
                                } elseif ( $last_pos === $max_position && isset( $product_terms_arr[$min_position-1] ) && count($terms) + 1 <= $this->config['suggestion_words_max'] ) {
                                    // add last and prev terms
                                    array_unshift( $terms, $product_terms_arr[$min_position-1] );
                                } elseif ( isset( $product_terms_arr[$min_position-1] ) && isset( $product_terms_arr[$max_position+1] ) && count($terms) + 2 <= $this->config['suggestion_words_max'] ) {
                                    // add terms nearby
                                    array_unshift( $terms, $product_terms_arr[$min_position-1] );
                                    array_push( $terms, $product_terms_arr[$max_position+1] );
                                }

                            }

                            if ( ! empty( $terms ) ) {

                                // make suggestion shorter if needed
                                $terms = $this->cut_suggestions_if_needed( $terms, $terms_found );

                                $all_suggests[] = $terms;

                            }


                        }


                    }

                }

                // Filter suggestions
                $filtered_suggestions = $this->filter_suggestions( $all_suggests );

                if ( ! empty( $filtered_suggestions ) ) {
                    foreach ( $filtered_suggestions as $f_sug ) {
                        $suggestions_final[] = implode( ' ', $f_sug );
                    }
                }

                $suggestions_final = array_unique( $suggestions_final );

                // limit suggestions number
                if ( ! empty( $suggestions_final ) && count( $suggestions_final ) > $this->config['max_number'] ) {
                    $suggestions_final = $this->limit_suggestions_by_score( $suggestions_final );
                }

                // order by suggestions length
                usort($suggestions_final, function($a, $b) {
                    return strlen($a) - strlen($b);
                });

            }

            /**
             * Filter suggestions results
             * @since 3.23
             * @param array $suggestions_final Array of generated suggestions
             * @param array $this->data Array of search parameters
             */
            $suggestions_final = apply_filters( 'aws_suggestions_results', $suggestions_final, $this->data );

            return $suggestions_final;

        }

        /*
         * Make suggestions shorter if necessary
         */
        private function cut_suggestions_if_needed( $terms, $terms_found ) {

            // make suggestion shorter if needed
            if ( count( $terms ) > $this->config['suggestion_words_max'] ) {

                $non_search_terms = array_diff( $terms, $terms_found );

                if ( count( $terms ) - count( $non_search_terms ) > $this->config['suggestion_words_max'] ) {

                    // we still need to remove some search terms
                    $terms = array_diff( $terms, $non_search_terms );

                    $terms = array_slice( $terms, 0, $this->config['suggestion_words_max'] );

                } else {

                    // remove only some non search terms
                    $to_remove = count( $terms ) - $this->config['suggestion_words_max'];

                    $terms_to_remove = array_slice($non_search_terms, -$to_remove);

                    $terms = array_diff( $terms, $terms_to_remove );

                }

            }

            return $terms;

        }

        /*
         * Get N suggestions ordered by score
         */
        private function limit_suggestions_by_score( $suggestions ) {

            $result = array();
            $new_suggestions = array();

            foreach ( $suggestions as $suggestion ) {

                $terms = explode( ' ', $suggestion );
                $score = 0;

                if ( $terms ) {

                    foreach ( $terms as $term ) {
                        $match = false;
                        foreach ( $this->available_terms as $search_term ) {

                            // add score for popular words
                            if ( array_key_exists( $term, $this->popular_terms ) ) {
                                $score = $score + 5;
                            }

                            // less score for similar terms
                            if ( in_array( $search_term, $this->similar_terms ) ) {
                                if ( $term === $search_term ) {
                                    $score = $score + 50;
                                    $match = true;
                                }
                                continue;
                            }

                            if ( $term === $search_term ) {
                                $score = $score + 150;
                                $match = true;
                            }
                            elseif ( strpos( $term, $search_term ) === 0 ) {
                                $score = $score + 50;
                                $match = true;
                            }
                            elseif ( strpos( $term, $search_term ) !== false ) {
                                $score = $score + 10;
                                $match = true;
                            }

                        }
                        if ( ! $match ) {
                            $score = $score - 10;
                        }
                    }

                    $result[] = array(
                        'score' => $score,
                        'val'   => $suggestion
                    );

                }

            }

            if ( ! empty( $result ) ) {

                usort($result, function($a, $b) {
                    if ( $a['score'] == $b['score'] ) {
                        return 0;
                    }
                    return ( $a['score'] > $b['score'] ) ? -1 : 1;
                });

                $result = array_slice( $result, 0, $this->config['max_number'] );

                foreach ( $result as $res_i ) {
                    $new_suggestions[] = $res_i['val'];
                }

            }

            return $new_suggestions;

        }

        /*
         * Filter suggestions ( remove that has same words, that has only same as search terms )
         */
        private function filter_suggestions( $all_suggests ) {

            $filtered_suggestions = array();

            if ( ! empty( $all_suggests ) ) {

                foreach ( $all_suggests as $suggestion_arr ) {

                    $suggestion_arr = array_unique( $suggestion_arr );

                    $is_same = count( $suggestion_arr ) == count( $this->search_terms ) && array_diff( $suggestion_arr, $this->search_terms ) === array_diff( $this->search_terms, $suggestion_arr );

                    if ( ! $is_same && $suggestion_arr && ! empty( $suggestion_arr ) ) {

                        $has_duplicate = false;

                        if ( ! empty($filtered_suggestions) ) {
                            foreach ( $filtered_suggestions as $f_sug ) {
                                $is_same = count( $suggestion_arr ) == count( $f_sug ) && array_diff( $suggestion_arr, $f_sug ) === array_diff( $f_sug, $suggestion_arr );
                                if ( $is_same ) {
                                    $has_duplicate = true;
                                    break;
                                }
                            }
                        }

                        if ( ! $has_duplicate ) {
                            $filtered_suggestions[] = $suggestion_arr;
                        }

                    }

                }

            }

            return $filtered_suggestions;

        }

        /*
         * Check if items are consecutive
         */
        private function is_consecutive( $positions ) {
            $count = count($positions);
            for ($i = 1; $i < $count; $i++) {
                if ($positions[$i] - $positions[$i - 1] != 1) {
                    return false;
                }
            }
            return true;
        }

        /*
         * Get most common terms of specific products
         */
        private function get_common_terms( $terms ) {

            $terms_arr = array();

            if ( $terms ) {

                foreach ( $terms as $product_terms ) {
                    $pr_terms = explode(',', $product_terms['terms'] );
                    $terms_arr = array_merge( $pr_terms, $terms_arr );
                }

                if ( ! empty( $terms_arr ) ) {

                    //$terms_arr = array_diff( $terms_arr, $this->search_terms );

                    $terms_arr = array_count_values( $terms_arr );

                    $this->common_terms = $terms_arr;

                    $this->common_terms = array_filter( $this->common_terms, function( $count, $term ) {
                        return $count >= $this->config['min_repeats'] && strlen( $term ) >= 3;
                    }, ARRAY_FILTER_USE_BOTH );

                    $terms_arr = array_filter( $terms_arr, function( $count, $term ) {
                        return $count > 1 && strlen( $term ) >= 3;
                    }, ARRAY_FILTER_USE_BOTH );

                    arsort($terms_arr);

                    $terms_arr = array_slice( $terms_arr, 0, 10, true );

                }

            }

            return $terms_arr;

        }

        /*
         * Get array of products that can contains probable suggestions
         */
        private function get_possible_suggestions( $search_terms ) {

            global $wpdb;

            if ( empty( $search_terms ) ) {
                return false;
            }

            $index_table_version = AWS_PRO()->option_vars->get_index_table_version();

            $term_sources = array();
            foreach ( $this->config['sources'] as $source ) {
                $term_sources[] = sprintf( "term_source = '%s'", $source );
            }

            $query_sources = sprintf( ' AND ( %s )', implode( ' OR ', $term_sources ) );

            $query_adv_filters = isset( $this->data['query_params'] ) && isset( $this->data['query_params']['adv_filters'] ) ? $this->data['query_params']['adv_filters'] : '';
            $query_exclude_terms = isset( $this->data['query_params'] ) && isset( $this->data['query_params']['exclude_terms'] ) ? $this->data['query_params']['exclude_terms'] : '';
            $query_sale = isset( $this->data['query_params'] ) && isset( $this->data['query_params']['sale'] ) ? $this->data['query_params']['sale'] : '';
            $query_stock = isset( $this->data['query_params'] ) && isset( $this->data['query_params']['stock'] ) ? $this->data['query_params']['stock'] : '';
            $query_visibility = isset( $this->data['query_params'] ) && isset( $this->data['query_params']['visibility'] ) ? $this->data['query_params']['visibility'] : '';
            $query_exclude_products = isset( $this->data['query_params'] ) && isset( $this->data['query_params']['exclude_products'] ) ? $this->data['query_params']['exclude_products'] : '';
            $query_lang = isset( $this->data['query_params'] ) && isset( $this->data['query_params']['lang'] ) ? $this->data['query_params']['lang'] : '';

            $query_type = " AND ( type NOT LIKE 'child' )";
            if ( $this->config['include_variations'] ) {
                $query_type = "";
            }

            if ( $index_table_version && version_compare( $index_table_version, '3.21', '>=' ) ) {
                $query_type = " AND ( type IN ( 0, 1 ) )";
                if ( $this->config['include_variations'] ) {
                    $query_type = "";
                }
            }

            $table_name = $wpdb->prefix . AWS_INDEX_TABLE_NAME;

            $relevance_array = array();
            $search_array = array();

            $query_relevance = '';
            $query_search = '';

            foreach ( $search_terms as $search_term ) {

                $start_with = $wpdb->esc_like( $search_term ) . '%';
                $contains = '%' . $wpdb->esc_like( $search_term ) . '%';

                // allow only exact match for similar_terms
                if ( in_array( $search_term, $this->similar_terms ) ) {
                    $relevance_array[] = $wpdb->prepare( "( case when ( term = '%s' ) then 50 else 0 end )", $search_term );
                    $search_array[] = $wpdb->prepare( "( term = '%s' )", $search_term );
                } else {
                    $relevance_array[] = $wpdb->prepare( "( case when ( term = '%s' ) then 150 else 0 end )", $search_term );
                    $relevance_array[] = $wpdb->prepare( "( case when ( term LIKE %s ) then 50 else 0 end )", $start_with );
                    $relevance_array[] = $wpdb->prepare( "( case when ( term LIKE %s ) then 10 else 0 end )", $contains );
                    $search_array[] = $wpdb->prepare( '( term LIKE %s )', $contains );
                }

            }

            $query_search = sprintf( ' AND ( %s )', implode( ' OR ', $search_array ) );
            $query_relevance = sprintf( ' (SUM( %s )) ', implode( ' + ', $relevance_array ) );

            $sql = "SELECT 
                        distinct ID,
                        GROUP_CONCAT(DISTINCT term) AS terms, 
                        {$query_relevance} as relevance
                    FROM
                        {$table_name}
                    WHERE
                        1=1
                        {$query_sources}
                        {$query_search}
                        {$query_adv_filters}
                        {$query_exclude_terms}
                        {$query_exclude_products}
                        {$query_stock}
                        {$query_sale}
                        {$query_visibility}
                        {$query_lang}
                        {$query_type}
                    GROUP BY 
                        ID
                    HAVING 
                        relevance > 0
                    ORDER BY 
                        relevance DESC, id DESC
                    LIMIT 0, 100
                ";

            return $wpdb->get_results( $sql, ARRAY_A );

        }

        /*
         * Get available terms of specific products
         */
        private function get_producs_terms( $products ) {

            if ( empty($products) ) {
                return array();
            }

            global $wpdb;

            $table_name = $wpdb->prefix . AWS_INDEX_TABLE_NAME;

            $term_sources = array();
            foreach ( $this->config['sources'] as $source ) {
                $term_sources[] = sprintf( "term_source = '%s'", $source );
            }

            $query_sources = sprintf( ' AND ( %s )', implode( ' OR ', $term_sources ) );

            $query_search = sprintf( ' AND id IN ( %s )', implode( ',', $products ) );

            $sql = "SELECT 
                        distinct ID,
                        GROUP_CONCAT(term ORDER BY k) as terms
                    FROM
                        {$table_name}
                    WHERE
                        1=1
                        {$query_sources}
                        {$query_search}
                    GROUP BY 
                        ID
                    ORDER BY 
                        id DESC
                    LIMIT 0, 999
                ";

            return $wpdb->get_results( $sql, ARRAY_A );

        }

    }

endif;
