<?php
/*
Plugin Name: Weather Effect
Plugin URI: https://awplife.com/
Description: This is Weather Effect Widget.
Version: 1.6.0
Author: A WP Life
Author URI: https://awplife.com/
License: GPLv2 or later
Text Domain: weather-effect
Domain Path: /languages

Weather Effect is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Weather Effect is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with User Registration. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

define( 'AWPLIFE_WE_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );
define("AWPLIFE_WEP_PLUGIN_DIR_PATH", plugin_dir_path(__FILE__));

// Plugin Text Domain
define( 'AWPLIFE_WE_TXTD', 'weather-effect' );

// Load text domain
add_action( 'plugins_loaded', 'awplife_we_load_textdomain' );

function awplife_we_load_textdomain() {
    load_plugin_textdomain( 'weather-effect', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

// Default settings
function awplife_we_default_settings() {
    $default_settings = array(
        'enable_weather_effect' => 1,
    );

    if ( get_option( 'weather_effect_settings' ) === false ) {
        add_option( 'weather_effect_settings', $default_settings );
    }
}
register_activation_hook( __FILE__, 'awplife_we_default_settings' );

// Enqueue necessary scripts
function awplife_we_enqueue_scripts() {
    
    // Enqueue frontend scripts only when effects are enabled
    $weather_effect_settings = get_option( 'weather_effect_settings' );
    $enable_weather_effect = isset( $weather_effect_settings['enable_weather_effect'] ) ? $weather_effect_settings['enable_weather_effect'] : 1;

    if ( $enable_weather_effect == 1 ) {
        wp_enqueue_script( 'awplife-we-snow-christmas-snow-js', AWPLIFE_WE_PLUGIN_PATH . 'assets/js/christmas-snow/christmas-snow.js', array( 'jquery' ), '1.5.9', true );
        wp_enqueue_script( 'awplife-we-snow-snow-falling-js', AWPLIFE_WE_PLUGIN_PATH . 'assets/js/snow-falling/snow-falling.js', array( 'jquery' ), '1.5.9', true );
        wp_enqueue_script( 'awplife-we-snow-snowfall-master-js', AWPLIFE_WE_PLUGIN_PATH . 'assets/js/snowfall-master/snowfall-master.min.js', array( 'jquery' ), '1.5.9', true );
    }
}
add_action( 'wp_enqueue_scripts', 'awplife_we_enqueue_scripts' );

// Weather effect premium setting page
if (file_exists(AWPLIFE_WEP_PLUGIN_DIR_PATH . 'settings.php')) {
    require_once AWPLIFE_WEP_PLUGIN_DIR_PATH . 'settings.php';
}

/**
 * Load frontend effects in footer
 */
function awplife_we_load_frontend_effects() {
    $weather_effect_settings = get_option( 'weather_effect_settings' );
    $enable_weather_effect = isset( $weather_effect_settings['enable_weather_effect'] ) ? $weather_effect_settings['enable_weather_effect'] : 1;

    // Check if weather effect is enabled
    if ( $enable_weather_effect == 1 ) {
        require_once 'output.php';
    }
}
add_action( 'wp_footer', 'awplife_we_load_frontend_effects' );

?>
