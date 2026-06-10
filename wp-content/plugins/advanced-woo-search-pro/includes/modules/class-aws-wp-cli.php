<?php
/**
 * WP-Cli commands
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

WP_CLI::add_command( 'awssearch', 'AWS_WP_CLI' );

class AWS_WP_CLI {

    public $data = array();

    /**
     * Display plugin information
     *
     * ## EXAMPLES
     * wp awssearch info
     *
     * @subcommand info
     */
    function info( $args, $assoc_args ) {
        WP_CLI::success( 'Thanks for using Advanced Woo Search plugin!' );
        WP_CLI::line( '' );
        WP_CLI::line( '- Plugin Version: ' . AWS_PRO_VERSION );
        WP_CLI::line( '- Plugin Directory: ' . AWS_PRO_DIR );
        WP_CLI::line( '- Plugin Website: https://advanced-woo-search.com/' );
        WP_CLI::line( '' );
    }

    /*
     * Index related commands
     *
     *  ## OPTIONS
     *
     * [--type=<type>]
     * : Index action type
     *
     *  [--id=<id>]
     * : Product ID to be indexed
     *
     *  ## EXAMPLES
     * wp awssearch index --type=all
     * wp awssearch index --type=status
     * wp awssearch index --type=update
     * wp awssearch index --type=update --id=1
     * wp awssearch index --type=delete
     * wp awssearch index --type=delete --id=1
     *
     * @subcommand index
     */
    function index( $args, $assoc_args ) {

        if ( ! isset( $assoc_args['type'] ) ) {
            WP_CLI::error( '--type=<type> parameter is required.' );
        }

        switch ( $assoc_args['type'] ) {
            case 'status':
                $index_count = AWS_Helpers::get_indexed_products_count();
                if ( $index_count ) {
                    WP_CLI::line( 'Products in index: ' . $index_count );
                } else {
                    WP_CLI::line( 'Index table is empty. Please run "wp awssearch index --type=all"' );
                }
                break;
            case 'all':
                do_action('aws_reindex_table');
                break;
            case 'update':
                if ( isset( $assoc_args['id'] ) ) {
                    $id = intval( $assoc_args['id'] );
                    if ( $id ) {
                        do_action( 'aws_force_reindex_product', $id );
                    }
                } else {
                    do_action('aws_reindex_table');
                }
                break;
            case 'delete':
                global $wpdb;
                $index_table = $wpdb->prefix . AWS_INDEX_TABLE_NAME;
                if ( isset( $assoc_args['id'] ) ) {
                    $id = intval( $assoc_args['id'] );
                    if ( $id ) {
                        $wpdb->delete( $index_table, array( 'id' => $id ) );
                    }
                } else {
                    $wpdb->query("DROP TABLE IF EXISTS {$index_table}");
                }
                break;
            default:
                WP_CLI::error( 'Invalid argument for type parameter.' );
        }

        WP_CLI::success( 'Index command completed.' );

    }

    /*
     * Clear cache table
     *
     * ## EXAMPLES
     * wp awssearch cache_clear
     *
     * @subcommand cache_clear
     */
    function cache_clear( $args, $assoc_args ) {

        AWS()->cache->clear_cache();

        WP_CLI::success( 'Cache cleared!' );

    }

    /*
     * Perform the search and return results
     *
     * ## OPTIONS
     *
     *  <term>
     * : Search term
     *
     * [--return=<return>]
     * : What data to return ( all, ids )
     *
     *  [--num=<num>]
     * : Number of results
     *
     * [--type=<type>]
     * : What search results types to display ( tax, products, users )
     *
     *  [--form_id=<form_id>]
     * : What form instance to use for search
     *
     *  [--filter_id=<filter_id>]
     * : What from filter to use for search
     *
     * ## EXAMPLES
     * wp awssearch search my term
     * wp awssearch search my term --return=all
     * wp awssearch search my term --return=ids --num=20
     * wp awssearch search my term --type=products
     *
     * @subcommand search
     */
    function search( $args, $assoc_args ) {

        if ( empty( $args ) ) {
            WP_CLI::error( 'Search query is missing!' );
        }

        if ( isset( $assoc_args['num'] ) ) {
            $this->data = $assoc_args;
            add_filter( 'aws_page_results', function ( $num ) {
                return $this->data['num'];
            });
        }

        if ( isset( $assoc_args['form_id'] ) ) {
            $_REQUEST['aws_id'] = intval( $assoc_args['form_id'] );
        }

        if ( isset( $assoc_args['filter_id'] ) ) {
            $_REQUEST['aws_filter'] = (string) $assoc_args['filter_id'];
        }

        $results = array();
        $search_terms = implode( ' ', $args );
        $search_res = aws_search( $search_terms );

        if ( ! $search_res ) {
            WP_CLI::error( 'Search failed.' );
        }

        if ( isset( $search_res['style'] ) ) {
            unset( $search_res['style'] );
        }

        if ( isset( $assoc_args['type'] ) ) {
            $type = $assoc_args['type'];
            if ( isset( $search_res[$type] ) ) {
                $results[$type] = $search_res[$type];
            }
        } else {
            $results = $search_res;
        }

        if ( isset( $assoc_args['return'] ) && $assoc_args['return'] == 'ids' ) {
            $results_ids = array();
            foreach ( $results as $result_type => $result_arr ) {
                $results_ids[$result_type] = array();
                foreach ( $result_arr as $result_item ) {
                    $results_ids[$result_type][] = $result_item['id'];
                }
            }
            $results = $results_ids;
        }

        WP_CLI::line( var_dump( $results ) );

        WP_CLI::success( 'Search completed!' );

    }

}
