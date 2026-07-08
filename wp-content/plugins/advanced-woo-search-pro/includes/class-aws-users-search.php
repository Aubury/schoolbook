<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWS_Users_Search' ) ) :

    /**
     * Class for admin condition rules
     */
    class AWS_Users_Search {

        /**
         * @var array AWS_Users_Search User roles
         */
        private $roles;

        /**
         * @var array AWS_Users_Search Search data
         */
        private $data;

        /**
         * @var string AWS_Users_Search Search logic
         */
        private $search_logic;

        /**
         * @var string AWS_Users_Search Exact search or not
         */
        private $search_exact;

        /**
         * @var string AWS_Users_Search Search rule ( %s%, s% )
         */
        private $search_rule;

        /**
         * @var string AWS_Users_Search Search string
         */
        private $search_string;

        /**
         * @var string AWS_Users_Search Unfiltered search string
         */
        private $search_string_unfiltered;

        /**
         * @var array AWS_Users_Search Search terms array
         */
        private $search_terms;

        /**
         * @var array AWS_Users_Search Advanced filters
         */
        private $filters;

        /**
         * @var AWS_Users_Search Number of search results $results_num
         */
        private $results_num = 10;

        /**
         * @var AWS_Users_Search Show or not archive pages images
         */
        private $search_archives_images;

        /**
         * @var AWS_Users_Search ID of current form instance $form_id
         */
        private $form_id = 0;

        /**
         * @var AWS_Users_Search ID of current filter $filter_id
         */
        private $filter_id = 0;

        /*
         * Constructor
         */
        public function __construct( $roles, $data ) {

            /**
             * Filters the array of user roles
             * @since 2.04
             * @param array $data Array of search data
             * @param string $taxonomy Taxonomy name
             */
            $data = apply_filters( 'aws_users_search_data', $data, $roles );

            $this->roles = $this->set_user_roles( $roles );
            $this->data = $data;

            $this->search_logic = isset( $data['search_logic'] ) ? $data['search_logic'] : 'or';
            $this->search_exact = isset( $data['search_exact'] ) ? $data['search_exact'] : 'false';
            $this->search_rule = isset( $data['search_rule'] ) ? $data['search_rule'] : 'contains';
            $this->search_string = isset( $data['s'] ) ? $data['s'] : '';
            $this->search_string_unfiltered = isset( $data['s_nonormalize'] ) ? $data['s_nonormalize'] : $this->search_string ;
            $this->search_terms = isset( $data['search_terms'] ) ? $data['search_terms'] : array();
            $this->filters = isset( $data['adv_filters']['user'] ) ? $data['adv_filters']['user'] : array();
            $this->results_num = isset( $data['pages_results_num'] ) ? $data['pages_results_num'] : 10;
            $this->search_archives_images = isset( $data['search_archives_images'] ) ? $data['search_archives_images'] : 'true';
            $this->form_id = isset( $data['form_id'] ) ? $data['form_id'] : 1;
            $this->filter_id = isset( $data['filter_id'] ) ? $data['filter_id'] : 1;

        }

        /**
         * Get search results
         * @return array Results array
         */
        public function get_results() {

            if ( ! $this->search_terms || empty( $this->search_terms ) || empty( $this->roles ) ) {
                return array();
            }

            global $wpdb;

            $search_query = '';
            $search_logic_operator = 'OR';

            if ( $this->search_logic === 'and' ) {
                $search_logic_operator = 'AND';
            }

            $like = '%' . $wpdb->esc_like( $this->search_string_unfiltered ) . '%';

            if ( $this->search_exact === 'true' ) {
                $filtered_terms_full = $wpdb->prepare( "( display_name = '%s' )", $this->search_string_unfiltered );
            } elseif ( $this->search_rule === 'begins' ) {
                $filtered_terms_full = $wpdb->prepare( '( display_name LIKE %s OR display_name LIKE %s )', $wpdb->esc_like( $this->search_string_unfiltered ) . '%', '% ' . $wpdb->esc_like( $this->search_string_unfiltered ) . '%' );
            } else {
                $filtered_terms_full = $wpdb->prepare( '( display_name LIKE %s )', $like );
            }

            $search_array = array_map( array( 'AWS_Helpers', 'singularize' ), $this->search_terms  );

            // Group terms in groups with their synonyms
            $new_search_array = array();
            $search_array_with_synonyms = $this->synonyms( $search_array );
            foreach ( $search_array_with_synonyms as $term_with_synonyms ) {
                $group_terms = $this->get_search_array( $term_with_synonyms );
                $new_search_array[] = '(' . implode( ' OR ', $group_terms ) . ')';
            }

            $search_array = $new_search_array;

            $search_query .= sprintf( ' AND ( ( %s ) OR %s )', implode( sprintf( ' %s ', $search_logic_operator ), $search_array ), $filtered_terms_full );

            $search_results = $this->query( $search_query );
            $result_array = $this->output( $search_results );
            
            return $result_array;

        }

        /**
         * Search query
         * @param string $search_query SQL query
         * @return array SQL query results
         */
        private function query( $search_query ) {

            global $wpdb;

            $users_array = array();
            $users_args = array(
                'role__in' => $this->roles,
            );

            /**
             * Filter users search query
             * @since 2.04
             * @param array $users_args Users query arguments
             */
            $users_args = apply_filters( 'aws_users_search_args', $users_args );

            $users = get_users( $users_args );

            if ( !is_wp_error( $users ) && $users && !empty( $users ) ) {
                foreach( $users as $user ) {
                    $users_array[] = $user->ID;
                }
            } else {
                return $users_array;
            }

            $users_ids = implode( ',', $users_array );

            /**
             * Max number of terms to show
             * @since 1.64
             * @param int
             */
            $terms_number = apply_filters( 'aws_search_terms_number', $this->results_num );
            if ( ! $terms_number ) {
                return array();
            }

            $relevance_array = $this->get_relevance_array();

            if ( $relevance_array && ! empty( $relevance_array ) ) {
                $relevance_query = sprintf( ' (SUM( %s )) ', implode( ' + ', $relevance_array ) );
            } else {
                $relevance_query = '0';
            }

            // Advanced filters
            $adv_filters_arr_obj = new AWS_Search_Filters( $this->filters, $this->form_id, $this->filter_id );
            $adv_filters_string = $adv_filters_arr_obj->filter();

            if ( $adv_filters_string ) {
                $search_query .= sprintf( ' AND ( %s )', $adv_filters_string );
            }

            /**
             * User custom fields that are available for search
             * @since 3.09
             * @param array $users_meta_keys Array of meta keys
             * @param array $this->data Array of search data
             */
            $users_meta_keys = apply_filters( 'aws_users_search_meta_keys', array( 'first_name', 'last_name' ), $this->data );

            $users_meta_keys = array_map( array( $this, 'prepare' ), $users_meta_keys );
            $users_meta_string = implode( ',', $users_meta_keys );

            $sql = "
			SELECT
				distinct(ID),
				display_name,
				user_nicename,
				$wpdb->usermeta.meta_value as meta_value,
				{$relevance_query} as relevance
			FROM
				$wpdb->users,
				$wpdb->usermeta
			WHERE 1 = 1
				{$search_query}
				AND ID IN ( {$users_ids} )
				AND $wpdb->users.ID = $wpdb->usermeta.user_id
				AND $wpdb->usermeta.meta_key IN ( {$users_meta_string} )
			    GROUP BY ID
			    ORDER BY relevance DESC, ID DESC
			LIMIT 0, {$terms_number}";

            $sql = trim( preg_replace( '/\s+/', ' ', $sql ) );

            /**
             * Filter users search query
             * @since 2.04
             * @param string $sql Sql query
             * @param string $roles User roles
             * @param string $search_query Search query
             */
            $sql = apply_filters( 'aws_users_search_query', $sql, $this->roles, $search_query );

            $search_results = $wpdb->get_results( $sql );

            return $search_results;

        }

        /**
         * Order and output search results
         * @param array SQL query results
         * @return array Array of taxonomies results
         */
        private function output( $search_results ) {

            $result_array = array();

            if ( ! empty( $search_results ) && !is_wp_error( $search_results ) ) {

                foreach ( $search_results as $result ) {

                    $image = '';
                    if ( $this->search_archives_images === 'true' ) {
                        $image = get_avatar_url( $result->ID );
                    }

                    $new_result = array(
                        'id'       => $result->ID,
                        'name'     => $result->display_name,
                        'link'     => get_author_posts_url( $result->ID ),
                        'excerpt'  => '',
                        'image'    => $image
                    );

                    $result_array[$result->ID][] = $new_result;

                }

                /**
                 * Filters array of users that must be displayed in search results
                 *
                 * @since 2.04
                 *
                 * @param array $result_array Array of custom taxonomies
                 * @param string $roles User roles
                 * @param string $s Search query
                 */
                $result_array = apply_filters( 'aws_search_users_results', $result_array, $this->roles, $this->search_string );

            }

            return $result_array;

        }

        /**
         * Get taxonomies relevance array
         *
         * @return array Relevance array
         */
        private function get_relevance_array() {

            global $wpdb;

            $relevance_scores = AWS_Helpers::get_relevance_scores( $this->data );

            $relevance_array = array();

            foreach ( $this->search_terms as $search_term ) {

                $search_term_len = strlen( $search_term );
                $relevance = $relevance_scores['user_name'] + 2 * $search_term_len;

                $like = '%' . $wpdb->esc_like( $search_term ) . '%';

                if ( $this->search_rule === 'begins' ) {
                    $relevance_array[] = $wpdb->prepare( "( case when ( display_name LIKE %s OR display_name LIKE %s ) then {$relevance} else 0 end )", $wpdb->esc_like( $search_term ) . '%', '% ' . $wpdb->esc_like( $search_term ) . '%' );
                    $relevance_array[] = $wpdb->prepare( "( case when ( user_nicename LIKE %s OR user_nicename LIKE %s ) then {$relevance} else 0 end )", $wpdb->esc_like( $search_term ) . '%', '% ' . $wpdb->esc_like( $search_term ) . '%' );
                    $relevance_array[] = $wpdb->prepare( "( case when ( meta_value LIKE %s OR meta_value LIKE %s ) then {$relevance} else 0 end )", $wpdb->esc_like( $search_term ) . '%', '% ' . $wpdb->esc_like( $search_term ) . '%' );
                } else {
                    $relevance_array[] = $wpdb->prepare( "( case when ( display_name LIKE %s ) then {$relevance} else 0 end )", $like );
                    $relevance_array[] = $wpdb->prepare( "( case when ( user_nicename LIKE %s ) then {$relevance} else 0 end )", $like );
                    $relevance_array[] = $wpdb->prepare( "( case when ( meta_value LIKE %s ) then {$relevance} else 0 end )", $like );
                }

            }

            return $relevance_array;

        }

        /**
         * Get taxonomies search array
         * @param array Search terms array
         * @return array Terms
         */
        private function get_search_array( $search_terms ) {

            global $wpdb;

            $search_array = array();

            foreach ( $search_terms as $search_term ) {

                $like = '%' . $wpdb->esc_like( $search_term ) . '%';

                if ( $this->search_exact === 'true' ) {
                    $search_array[] = $wpdb->prepare( "( display_name = '%s' )", $search_term );
                    $search_array[] = $wpdb->prepare( "( user_nicename = '%s' )", $search_term );
                    $search_array[] = $wpdb->prepare( "( meta_value = '%s' )", $search_term );
                } elseif ( $this->search_rule === 'begins' ) {
                    $search_array[] = $wpdb->prepare( '( display_name LIKE %s OR display_name LIKE %s )', $wpdb->esc_like( $search_term ) . '%', '% ' . $wpdb->esc_like( $search_term ) . '%' );
                    $search_array[] = $wpdb->prepare( '( user_nicename LIKE %s OR user_nicename LIKE %s  )', $wpdb->esc_like( $search_term ) . '%', '% ' . $wpdb->esc_like( $search_term ) . '%' );
                    $search_array[] = $wpdb->prepare( '( meta_value LIKE %s OR meta_value LIKE %s  )', $wpdb->esc_like( $search_term ) . '%', '% ' . $wpdb->esc_like( $search_term ) . '%' );
                } else {
                    $search_array[] = $wpdb->prepare( '( display_name LIKE %s )', $like );
                    $search_array[] = $wpdb->prepare( '( user_nicename LIKE %s )', $like );
                    $search_array[] = $wpdb->prepare( '( meta_value LIKE %s )', $like );
                }

            }

            return $search_array;

        }

        /*
         * Prepare strings for query
         * @param string $name String to prepare
         * @return string Prepared string
         */
        private function prepare( $name ) {
            global $wpdb;
            return $wpdb->prepare('%s', $name);
        }

        /*
         * Add synonyms
         * @param array $search_terms Search term
         * @return array Search term with synonyms
         */
        private function synonyms( $search_terms ) {

            if ( $search_terms && ! empty( $search_terms ) ) {

                $new_search_terms = array();
                foreach( $search_terms as $search_term ) {
                    $current_search_term_arr = array( $search_term => 1 );
                    $new_search_terms[$search_term] = array_keys( AWS_Helpers::get_synonyms( $current_search_term_arr, true ) );
                }

                return $new_search_terms;

            }

            return $search_terms;

        }

        /*
         * Set active user roles
         */
        private function set_user_roles( $roles ) {
            $role_array = array();
            if ( $roles && ! empty( $roles ) ) {
                foreach( $roles as $role_name => $role_params ) {
                    $role_array[] = $role_name;
                }
            }
            return $role_array;
        }

    }

endif;