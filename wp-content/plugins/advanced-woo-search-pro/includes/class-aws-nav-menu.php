<?php
/**
 * Allows adding the plugin search form via Appearance -> Menus
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'AWS_Nav_Menu' ) ) :

    /**
     * Class for nav menu integration
     */
    class AWS_Nav_Menu {

        /**
         * Marker CSS class used to identify the plugin menu item
         */
        const MENU_ITEM_CLASS = 'aws-menu-container';

        /**
         * Post meta key that stores the selected search form id
         */
        const META_KEY = '_aws_menu_search_id';

        /**
         * @var AWS_Nav_Menu The single instance of the class
         */
        protected static $_instance = null;

        /**
         * Main AWS_Nav_Menu Instance
         *
         * Ensures only one instance of AWS_Nav_Menu is loaded or can be loaded.
         *
         * @static
         * @return AWS_Nav_Menu - Main instance
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

            add_action( 'admin_init', array( $this, 'add_meta_box' ) );
            add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'custom_fields' ), 10, 2 );
            add_action( 'wp_update_nav_menu_item', array( $this, 'save_custom_fields' ), 10, 2 );
            add_filter( 'walker_nav_menu_start_el', array( $this, 'handle_menu_output' ), 10, 2 );

        }

        /*
         * Register the meta box on the Menus screen
         */
        public function add_meta_box() {
            add_meta_box(
                'aws_nav_menu_link',
                __( 'Advanced Woo Search', 'advanced-woo-search' ),
                array( $this, 'menu_box' ),
                'nav-menus',
                'side',
                'low'
            );
        }

        /*
         * The meta box content on the Navigation Menus screen
         */
        public function menu_box() {
            $this->print_assets();
            ?>
            <div id="posttype-aws" class="posttypediv">
                <div id="tabs-panel-aws" class="tabs-panel tabs-panel-active">
                    <p>
                        <?php
                        printf(
                            /* translators: %s: "Add to Menu" button label */
                            esc_html__( 'Click the %s to add Advanced Woo Search to the menu.', 'advanced-woo-search' ),
                            '<strong>' . esc_html__( 'Add to Menu', 'advanced-woo-search' ) . '</strong>'
                        );
                        ?>
                    </p>
                    <ul id="aws-checklist" class="categorychecklist form-no-clear" style="display:none">
                        <li>
                            <label class="menu-item-title">
                                <input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]" value="1" checked="checked"> <?php esc_html_e( 'Advanced Woo Search', 'advanced-woo-search' ); ?>
                            </label>
                            <input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom">
                            <input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="<?php esc_attr_e( 'Advanced Woo Search', 'advanced-woo-search' ); ?>">
                            <input type="hidden" class="menu-item-url" name="menu-item[-1][menu-item-url]" value="">
                            <input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]" value="<?php echo esc_attr( self::MENU_ITEM_CLASS ); ?>">
                        </li>
                    </ul>
                </div>
                <p class="button-controls">
                    <span class="add-to-menu">
                        <input type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu', 'advanced-woo-search' ); ?>" name="add-post-type-menu-item" id="submit-posttype-aws">
                        <span class="spinner"></span>
                    </span>
                </p>
            </div>
            <?php
        }

        /*
         * Inline styles/scripts to hide the default fields for the plugin menu item
         *
         * Printed inside the meta box so no extra hook is needed. A MutationObserver
         * tags the plugin menu item with the "aws-menu-item" class as items are added,
         * then the standard option fields are hidden via CSS when it is expanded.
         */
        private function print_assets() {
            ?>
            <style>
                .aws-menu-item .menu-item-settings > p.description:not(.aws-menu-item-field),
                .aws-menu-item .menu-item-settings > fieldset {
                    display: none;
                }
            </style>
            <script>
                jQuery(function($){
                    var t;
                    var menu = document.querySelector('.menu.ui-sortable');
                    function addClasses() {
                        $('.aws-menu-item-field').closest('.menu-item').addClass('aws-menu-item');
                    }
                    if ( menu ) {
                        var o = new MutationObserver(function(){
                            clearTimeout(t);
                            t = setTimeout(addClasses, 50);
                        });
                        o.observe(menu, { subtree: true, childList: true });
                    }
                    addClasses();
                });
            </script>
            <?php
        }

        /*
         * Search form selector and info note on the plugin menu item in the menu editor
         */
        public function custom_fields( $item_id, $menu_item ) {
            if ( ! $this->is_plugin_menu_item( $menu_item ) ) {
                return;
            }

            $instances = AWS_Admin_Options::get_settings();
            $selected  = $this->get_search_id( $item_id );
            ?>
            <p class="aws-menu-item-field description description-wide">
                <label for="aws-menu-search-id-<?php echo esc_attr( $item_id ); ?>">
                    <?php esc_html_e( 'Search form', 'advanced-woo-search' ); ?><br>
                    <select id="aws-menu-search-id-<?php echo esc_attr( $item_id ); ?>" class="widefat" name="aws-menu-search-id[<?php echo esc_attr( $item_id ); ?>]">
                        <?php if ( is_array( $instances ) ) : ?>
                            <?php foreach ( $instances as $instance_id => $instance_options ) : ?>
                                <option value="<?php echo esc_attr( $instance_id ); ?>" <?php selected( $instance_id, $selected ); ?>>
                                    <?php echo esc_html( sprintf( '%s ( id: %s )', $instance_options['search_instance'], $instance_id ) ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </label>
            </p>
            <p class="aws-menu-item-field description description-wide">
                <?php esc_html_e( 'This menu item will be replaced with the selected search form on the front end.', 'advanced-woo-search' ); ?>
            </p>
            <?php
        }

        /*
         * Save the selected search form id for the plugin menu item
         */
        public function save_custom_fields( $menu_id, $menu_item_db_id ) {
            if ( isset( $_POST['aws-menu-search-id'][ $menu_item_db_id ] ) ) {
                $search_id = intval( $_POST['aws-menu-search-id'][ $menu_item_db_id ] );
                update_post_meta( $menu_item_db_id, self::META_KEY, $search_id );
            }
        }

        /*
         * Replace the menu item output with the search form on the front end
         */
        public function handle_menu_output( $item_output, $menu_item ) {
            if ( $this->is_plugin_menu_item( $menu_item ) ) {
                $search_id = $this->get_search_id( $menu_item->ID );
                return do_shortcode( '[aws_search_form id="' . esc_attr( $search_id ) . '"]' );
            }
            return $item_output;
        }

        /*
         * Get the search form id stored for a menu item ( falls back to the default instance )
         */
        private function get_search_id( $item_id ) {
            $search_id = get_post_meta( $item_id, self::META_KEY, true );
            if ( $search_id === '' || $search_id === false ) {
                $search_id = AWS_Helpers::get_available_instance_id();
            }
            return intval( $search_id );
        }

        /*
         * Check whether the given menu item is the plugin search form item
         */
        private function is_plugin_menu_item( $menu_item ) {
            $classes = isset( $menu_item->classes ) && is_array( $menu_item->classes ) ? $menu_item->classes : array();
            return in_array( self::MENU_ITEM_CLASS, $classes, true );
        }

    }

endif;
