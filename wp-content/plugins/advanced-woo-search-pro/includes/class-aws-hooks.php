<?php
/**
 * AWS plugin hooks
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'AWS_Hooks' ) ) :

    /**
     * Class for main plugin functions
     */
    class AWS_Hooks {

        /**
         * @var AWS_Hooks The single instance of the class
         */
        protected static $_instance = null;

        protected $data = array();

        /**
         * Main AWS_Hooks Instance
         *
         * Ensures only one instance of AWS_Hooks is loaded or can be loaded.
         *
         * @static
         * @return AWS_Hooks - Main instance
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Constructor
         */
        public function __construct() {

            // Get current search data
            add_filter( 'aws_search_results_products_ids', array( $this, 'aws_search_results_products_ids' ), 1, 3 );

            // Retrive current search data
            add_filter( 'aws_current_search_data', array( $this, 'aws_current_search_data' ) );

            // Retrive current search results
            add_filter( 'aws_current_search_product_ids', array( $this, 'aws_current_search_product_ids' ) );

            // Add search suggestions
            add_filter( 'aws_search_custom_results_data', array( $this, 'aws_add_suggestions' ), 1, 3 );

            // Misspelling fix suggestions
            add_filter( 'aws_search_notices', array( $this, 'aws_search_notices' ), 1, 3 );

        }

        /*
         * Get current search data
         */
        public function aws_search_results_products_ids( $posts_ids, $s, $data ) {
            $this->data['s_data'] = $data;
            $this->data['posts_ids'] = $posts_ids;
            return $posts_ids;
        }

        /*
         * Retrive current search data
         */
        public function aws_current_search_data( $data ) {
            $data = isset( $this->data['s_data'] ) ? $this->data['s_data'] : $data;
            return $data;
        }

        /*
         * Retrive current search results
         */
        public function aws_current_search_product_ids( $posts_ids ) {
            $posts_ids = isset( $this->data['posts_ids'] ) ? $this->data['posts_ids'] : $posts_ids;
            return $posts_ids;
        }

        /*
         * Add search suggestions
         */
        public function aws_add_suggestions( $results_data, $results, $s_data ) {

            $empty = true;
            foreach ( $results as $result ) {
                if ( ! empty( $result ) ) {
                    $empty = false;
                    break;
                }
            }

            $form_id = isset( $s_data['form_id'] ) ? $s_data['form_id'] : 1;

            $enable_suggestions = isset( $s_data['suggestions'] ) && $s_data['suggestions'] === 'true';
            if ( ! $enable_suggestions ) {
                return $results_data;
            }

            $show_when_no_results = AWS_PRO()->get_settings( 'suggestions_no_results', $form_id );
            if ( $show_when_no_results === 'true' && ! $empty ) {
                return $results_data;
            }

            $similar_terms_obj = new AWS_Search_Suggestions( $s_data );
            $suggestions_terms = $similar_terms_obj->get_suggestions();

            if ( $suggestions_terms && ! empty( $suggestions_terms ) ) {

                $layout = AWS_PRO()->get_settings( 'suggestions_layout', $form_id );

                $on_click = AWS_PRO()->get_settings( 'suggestions_on_click', $form_id );
                $ajax_search = AWS_PRO()->get_settings( 'enable_ajax', $form_id );
                $force_redirect = '';

                if ( $ajax_search && $on_click === 'page' ) {
                    $force_redirect = 'data-aws-term-submit-form="true"';
                }

                if ( $layout === '2' ) {

                    $new_suggestion_terms = array();
                    foreach ( $suggestions_terms as $suggestion_term ) {
                        $new_suggestion_terms[] = '<a '.$force_redirect.' data-aws-term-submit="' . $suggestion_term . '" href="#" class="aws_term_suggestion">'. $suggestion_term . '</a>';
                    }

                    $text = esc_html__( 'Suggestions:', 'advanced-woo-search' );

                    /**
                     * Filter suggestions title text
                     * @since 3.23
                     * @param string $text Title text for suggestions
                     * @param array $s_data Array of search parameters
                     * @param array $results Array of search results
                     */
                    $text = apply_filters( 'aws_suggestions_title', $text, $s_data, $results );

                    if ( ! empty( $new_suggestion_terms ) ) {
                        $results_data['notices']['s_suggest'] = '<div class="aws_terms_suggestions aws_suggestions">' . '<span class="aws_terms_suggestions_title">' . $text . '</span>' . ' ' . implode(' ', $new_suggestion_terms ) . '</div>';
                    }

                } else {

                    $results = array();

                    if ( $suggestions_terms && ! empty( $suggestions_terms ) ) {
                        foreach ( $suggestions_terms as $suggestion_term ) {
                            $results[] = array(
                                'name' => $suggestion_term,
                                'link' => '#',
                                'link_data' => 'data-aws-term-submit="' . $suggestion_term . '" ' . $force_redirect,
                                'image' => AWS_PRO_URL . '/assets/img/search.svg'
                            );
                        }
                    }

                    $results_data['top_results']['s_suggest'] = $results;

                }

            }

            return $results_data;

        }

        /*
         * Add custom text at the top of search results list
         */
        public function aws_search_notices( $notices, $results, $s_data ) {

            $empty = true;
            foreach ( $results as $result ) {
                if ( ! empty( $result ) ) {
                    $empty = false;
                    break;
                }
            }

            if ( ! $empty && isset( $s_data['fuzzy'] ) && $s_data['fuzzy'] === 'true_text' && isset( $s_data['similar_terms'] )  ) {

                $terms_suggestions = AWS_Helpers::get_fixed_terms_suggestions( $s_data );

                if ( ! empty( $terms_suggestions ) ) {
                    $new_terms = array();
                    foreach ( $terms_suggestions as $terms_suggestion ) {
                        $new_terms[] = '<span class="aws_term_suggestion"><strong>'. $terms_suggestion . '</strong></span>';
                    }
                    $notices['showing_res_for'] = '<div class="aws_terms_suggestions">' . esc_html__( 'Showing results for', 'advanced-woo-search' ) . ' ' . implode(', ', $new_terms ) . '</div>';
                }

            }

            if ( isset( $s_data['fuzzy'] ) && $s_data['fuzzy'] === 'false_text' ) {

                $terms_suggestions = array();

                if ( $empty && ! isset( $s_data['similar_terms'] ) ) {
                    $similar_terms_obj = new AWS_Similar_Terms( $s_data );
                    $similar_terms_res = $similar_terms_obj->get_similar_terms();

                    if ( ! empty( $similar_terms_res ) && ! empty( $similar_terms_res['all'] ) ) {

                        $s_data['similar_terms'] = $similar_terms_res;

                        $terms_suggestions = AWS_Helpers::get_fixed_terms_suggestions( $s_data );

                    }

                }

                if ( ! empty( $terms_suggestions ) ) {

                    $new_terms = array();
                    foreach ( $terms_suggestions as $terms_suggestion ) {
                        if ( $terms_suggestion === $s_data['s'] ) {
                            continue;
                        }
                        $new_terms[] = '<a data-aws-term-submit="' . $terms_suggestion . '" href="#" class="aws_term_suggestion">'. $terms_suggestion . '</a>';
                    }

                    if ( ! empty( $new_terms ) ) {
                        $notices['suggestions'] = '<div class="aws_terms_suggestions">' . esc_html__( 'Did you mean:', 'advanced-woo-search' ) . ' ' . implode(', ', $new_terms ) . '</div>';
                    }

                }

            }

            return $notices;

        }

    }

endif;