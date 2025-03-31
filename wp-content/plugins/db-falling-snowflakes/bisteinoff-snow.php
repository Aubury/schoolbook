<?php
/*
Plugin Name: DB Falling Snowflakes
Plugin URI: https://github.com/bisteinoff/db-snow
Description: Add snow falling animation to your website. Personal settings to make your own style of snowflakes and their movement. Once installed the plugin will run only during the period of time that you set.
Version: 1.6
Author: Denis Bisteinov
Author URI: https://bisteinoff.com
Text Domain: db-falling-snowflakes
License: GPL2
*/

/*  Copyright 2024 Denis BISTEINOV  (email : bisteinoff@gmail.com)
 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.
 
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	class dbSnow

	{

		function thisdir()
		{
			return basename( __DIR__ );
		}

		function __construct()
		{

			add_option( 'db_snow_start_day', '1' );
			add_option( 'db_snow_start_month', '12' );
			add_option( 'db_snow_finish_day', '29' );
			add_option( 'db_snow_finish_month', '2' );
			add_option( 'db_snow_max_number', '50' );
			add_option( 'db_snow_min_size', '10' );
			add_option( 'db_snow_max_size', '40' );
			add_option( 'db_snow_speed', '0.5' );
			add_option( 'db_snow_color_1', '#b9e0f5' );
			add_option( 'db_snow_color_2', '#7ec8ff' );
			add_option( 'db_snow_color_3', '#7eb0ff' );
			add_option( 'db_snow_color_4', '#8ab4ff' );
			add_option( 'db_snow_color_5', '#afd0f5' );
			add_option( 'db_snow_opacity', '0.5' );

			add_filter( 'plugin_action_links_' . $this -> thisdir() . '/bisteinoff-snow.php', array(&$this, 'db_settings_link') );
			add_action( 'admin_menu', array (&$this, 'admin') );

			add_action( 'admin_footer', function() {
							wp_enqueue_style( $this -> thisdir() . '-admin', plugin_dir_url( __FILE__ ) . 'css/admin.min.css' );
							wp_enqueue_script( $this -> thisdir() . '-admin', plugin_dir_url( __FILE__ ) . 'js/admin.min.js', array( 'wp-color-picker' ), false, true );
							wp_enqueue_style( 'wp-color-picker' );
						},
						99
			);

			$db_snow_start_day = (int) get_option('db_snow_start_day');
			$db_snow_start_month = (int) get_option('db_snow_start_month');
			$db_snow_finish_day = (int) get_option('db_snow_finish_day');
			$db_snow_finish_month = (int) get_option('db_snow_finish_month');
			$db_this_year = date("Y");

			$db_date1 = "-"
				. ( $db_snow_start_month < 10 ? $db_snow_start_month = '0' . $db_snow_start_month : $db_snow_start_month )
				. "-"
				. ( $db_snow_start_day < 10 ? $db_snow_start_day = '0' . $db_snow_start_day : $db_snow_start_day );

			$db_date2 = "-"
				. ( $db_snow_finish_month < 10 ? $db_snow_finish_month = '0' . $db_snow_finish_month : $db_snow_finish_month )
				. "-"
				. ( $db_snow_finish_day < 10 ? $db_snow_finish_day = '0' . $db_snow_finish_day : $db_snow_finish_day );

			$db_today = date( "Y-m-d" );

			if (
				// date 1 is earlier than date 2
				$db_this_year . $db_date1 < $db_this_year . $db_date2 && $db_today >= $db_this_year . $db_date1 && $db_today <= $db_this_year . $db_date2 ||
				// date 1 is later than date 2
				$db_this_year . $db_date1 > $db_this_year . $db_date2 &&
					(
						$db_today >= ( $db_this_year - 1) . $db_date1 && $db_today <= $db_this_year . $db_date2 ||
						$db_today >= $db_this_year . $db_date1 && $db_today <= ( $db_this_year + 1) . $db_date2
					)
				)
				if ( !is_admin() )
					{
						wp_enqueue_script( $this -> thisdir(), plugin_dir_url( __FILE__ ) . 'js/snow.min.js', null, null, true );
						add_action( 'wp_footer', array (&$this, 'footer_js') );
					}

		}

		function admin() {

			if ( function_exists('add_menu_page') )
			{

				$icon = '<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="43.3492mm" height="43.3492mm" version="1.1" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd"
				viewBox="0 0 4335 4335"
				 xmlns:xlink="http://www.w3.org/1999/xlink">
				 <defs>
				  <style type="text/css">
				   <![CDATA[
					.str0 {stroke:#373435;stroke-width:17.64}
					.fil0 {fill:none}
					.fil2 {fill:#EEEEEE}
					.fil1 {fill:#FFFFFF}
				   ]]>
				  </style>
				 </defs>
				<symbol id="Symbol1" viewBox="2028 2051 286 228">
				 <line class="fil0 str0" x1="2247" y1="2059" x2="2305" y2= "2276" />
				 <line class="fil0 str0" x1="2247" y1="2059" x2="2030" y2= "2117" />
				</symbol>
				 <g id="Layer_x0020_1">
				  <metadata id="CorelCorpID_0Corel-Layer"/>
				  <path class="fil1" d="M1440 2671l10 -42 -249 133 -88 385c-19,77 -138,51 -120,-28l64 -280 -195 104 -133 578c-22,76 -138,51 -120,-28l109 -473 -370 198c-92,30 -122,-71 -60,-110l379 -202 -460 -175c-75,-25 -36,-149 46,-116l560 213 189 -101 -273 -101c-76,-26 -35,-149 45,-117l375 139 252 -134 -49 -19c-77,-26 -29,-150 46,-116l148 57 190 -101c-46,-118 -41,-251 13,-365l-184 -117 -153 43c-63,32 -124,-90 -35,-120l46 -13 -239 -151 -381 115c-114,0 -92,-101 -36,-120l277 -83 -180 -114 -576 169c-40,40 -139,-86 -35,-120l470 -138 -361 -229c-71,-44 4,-149 67,-102l360 228 -75 -485c-8,-81 107,-99 124,-18l90 591 185 117 -46 -288c-9,-89 116,-94 123,-18l63 396 237 150 -7 -49c-8,-81 107,-99 123,-18l24 156 184 117c73,-87 178,-147 296,-163l0 -223 -120 -105c-58,-50 12,-147 81,-95l39 34 0 -278 -299 -264c-59,-50 11,-147 81,-95l218 192 0 -220 -448 -391c-58,-50 12,-147 82,-95l366 320 0 -423c0,-80 123,-87 123,0l0 429 370 -323c62,-53 145,40 82,95l-452 394 0 217 222 -195c62,-53 145,40 82,95l-304 267 0 272 36 -31c61,-53 142,34 84,91l-120 108 0 224c127,16 239,83 313,180l198 -112 32 -149c17,-76 137,-61 120,24l-9 44 241 -137 82 -390c18,-81 139,-54 123,25l-59 283 184 -105 122 -587c18,-82 139,-54 123,25l-99 478 374 -214c66,-41 136,67 60,110l-378 216 466 161c84,28 26,148 -39,116l-568 -196 -185 105 270 94c83,28 28,150 -38,117l-373 -131 -248 141 43 14c83,28 25,149 -39,117l-146 -51 -195 110c47,116 45,247 -5,361l190 114 149 -48c58,-19 158,78 -9,132l243 145 379 -122c76,-25 115,91 39,117l-279 89 188 113 567 -181c76,-26 115,91 38,116l-465 149 370 221c59,37 28,136 -63,106l-367 -220 88 484c17,84 -110,95 -119,21l-108 -588 -189 -113 53 289c17,84 -110,95 -119,21l-73 -393 -247 -148 9 51c17,84 -110,95 -119,21l-27 -155 -194 -116c-77,96 -191,162 -320,174l-5 217 119 109c62,63 -29,148 -84,92l-39 -35 -6 277 298 276c60,58 -27,147 -84,92l-218 -201 -6 213 446 414c62,62 -30,146 -85,92l-365 -340 -12 435c9,78 -120,82 -120,-4l12 -422 -375 310c-49,49 -142,-31 -77,-96l456 -379 6 -214 -219 181c-62,49 -143,-39 -81,-95l303 -252 7 -285 -35 29c-71,48 -138,-38 -81,-95l120 -99 5 -224c-123,-20 -230,-89 -302,-186l-201 107 -35 149c-9,73 -138,55 -120,-29zm198 -1614c32,0 32,49 0,49 -32,0 -32,-49 0,-49zm-2 168c-144,0 -200,-197 -74,-268 67,-39 155,-18 194,49 54,98 -14,219 -120,219zm-666 850c31,0 31,49 0,49 -32,0 -32,-49 0,-49zm-2 168c-144,0 -200,-197 -74,-268 67,-39 155,-18 194,49 54,98 -14,219 -120,219zm509 971c32,0 32,49 0,49 -32,0 -32,-49 0,-49zm-1 168c-144,0 -200,-196 -75,-268 67,-38 156,-17 194,50 54,97 -14,218 -119,218zm1885 -1239c31,0 31,49 0,49 -32,0 -32,-49 0,-49zm-2 168c-144,0 -200,-196 -74,-268 67,-38 155,-17 194,50 54,97 -14,218 -120,218zm-574 904c31,0 31,49 0,49 -32,0 -32,-49 0,-49zm-611 -1389c-187,0 -346,156 -346,341 0,191 155,346 346,346 190,0 345,-155 345,-346 0,-186 -155,-341 -345,-341zm-1865 489c-115,-115 -40,-324 127,-324 163,0 257,198 137,317 -72,72 -190,81 -264,7zm131 -197c-83,0 -83,127 0,127 90,0 85,-127 0,-127zm867 1741c-214,0 -247,-307 -46,-366 152,-46 301,125 215,271 -39,60 -102,95 -169,95zm-29 -130c70,38 136,-71 64,-113 -78,-45 -137,72 -64,113zm1759 130c-188,0 -260,-253 -95,-349 144,-84 318,49 278,208 -20,81 -100,141 -183,141zm0 -250c-47,0 -82,51 -56,95 41,71 159,2 112,-67 -14,-18 -35,-28 -56,-28zm-254 -226c-144,0 -200,-196 -74,-268 67,-38 155,-17 194,50 54,97 -14,218 -120,218zm1128 -1011c-106,0 -187,-81 -187,-187 0,-102 81,-183 183,-187 107,0 191,79 191,187 -4,102 -85,187 -187,187zm-4 -254c-82,0 -81,127 0,127 82,0 82,-127 0,-127zm-845 -1251c-139,0 -239,-156 -162,-279 24,-42 63,-74 112,-88 157,-45 294,124 212,272 -35,60 -95,95 -162,95zm0 -254c-64,0 -90,88 -32,120 95,53 142,-120 32,-120zm-254 642c-163,0 -204,-239 -39,-286 164,-47 275,202 74,279 -14,7 -25,7 -35,7zm-4 -163c-10,0 -18,11 -17,21 1,19 39,31 39,-6 0,-8 -13,-17 -22,-15zm-1469 -253c-187,0 -257,-255 -92,-353 204,-121 402,196 183,328 -28,18 -60,25 -91,25zm-32 -247c-73,41 -14,150 63,109 69,-36 28,-139 -63,-109z"/>
				  <path class="fil2" d="M3062 613c-64,0 -90,88 -32,120 95,53 142,-120 32,-120z"/>
				  <path class="fil2" d="M2826 1107c0,-8 -13,-17 -22,-15 -10,0 -18,11 -17,21 1,19 39,31 39,-6z"/>
				  <path class="fil2" d="M3907 2118c-82,0 -81,127 0,127 82,0 82,-127 0,-127z"/>
				  <path class="fil2" d="M3361 2192c31,0 31,-49 0,-49 -32,0 -32,49 0,49z"/>
				  <path class="fil2" d="M3093 3637c-14,-18 -35,-28 -56,-28 -47,0 -82,51 -56,95 41,71 159,2 112,-67z"/>
				  <path class="fil2" d="M2785 3264c31,0 31,-49 0,-49 -32,0 -32,49 0,49z"/>
				  <path class="fil2" d="M1278 3729c70,38 136,-71 64,-113 -78,-45 -137,72 -64,113z"/>
				  <path class="fil2" d="M1477 3214c-32,0 -32,49 0,49 32,0 32,-49 0,-49z"/>
				  <path class="fil2" d="M440 2245c90,0 85,-127 0,-127 -83,0 -83,127 0,127z"/>
				  <path class="fil2" d="M970 2075c-32,0 -32,49 0,49 31,0 31,-49 0,-49z"/>
				  <path class="fil2" d="M1303 592c-73,41 -14,150 63,109 69,-36 28,-139 -63,-109z"/>
				  <path class="fil2" d="M1638 1057c-32,0 -32,49 0,49 32,0 32,-49 0,-49z"/>
				 </g>
				</svg>';

				add_menu_page(
					esc_html__('DB Snow Flakes Settings' , 'db-falling-snowflakes' ),
					esc_html__('DB Snow Flakes' , 'db-falling-snowflakes' ),
					'manage_options',
					$this -> thisdir(),
					array (&$this, 'admin_page_callback'),
					'data:image/svg+xml;base64,' . base64_encode( $icon ),
					27
					);

			}

		}

		function admin_page_callback()
		{

			require_once('inc/admin.php');

		}

		function db_settings_link( $links )
		{

			$url = esc_url ( add_query_arg (
				'page',
				$this -> thisdir(),
				get_admin_url() . 'admin.php'
			) );

			$settings_link = "<a href='$url'>" . esc_html__( 'Settings' ) . '</a>';

			array_push(
				$links,
				$settings_link
			);

			return $links;

		}

		function footer_js()
		{

			$db_snow_max_number = (int) get_option( 'db_snow_max_number' );
			$db_snow_min_size = (int) get_option( 'db_snow_min_size' );
			$db_snow_max_size = (int) get_option( 'db_snow_max_size' );
			$db_snow_speed = (float) get_option( 'db_snow_speed' );
			$db_snow_colors = array(
				'Colors',
				get_option( 'db_snow_color_1' ),
				get_option( 'db_snow_color_2' ),
				get_option( 'db_snow_color_3' ),
				get_option( 'db_snow_color_4' ),
				get_option( 'db_snow_color_5' )
			);
			$db_snow_opacity = (float) get_option( 'db_snow_opacity' );

		?><script type="text/javascript">

			let dbSnowFlakesMaxNumber = <?php echo esc_html ( sanitize_text_field ( $db_snow_max_number ) ); ?>;
			let dbSnowFlakesMinSize = <?php echo esc_html ( sanitize_text_field ( $db_snow_min_size ) ); ?>;
			let dbSnowFlakesMaxSize = <?php echo esc_html ( sanitize_text_field ( $db_snow_max_size ) ); ?>;
			let dbSnowFlakesSpeed = <?php echo esc_html ( sanitize_text_field ( $db_snow_speed ) ); ?>;
			let dbSnowFlakesColors = new Array('Colors'<?php
				for ( $i = 1; $i <= 5; $i++ )
					echo ",'" . (string) esc_html ( sanitize_hex_color ( $db_snow_colors[ $i ] ) ) . "'"; 
			?>);
			let dbSnowFlakesOpacity = <?php echo esc_html ( sanitize_text_field ( $db_snow_opacity ) ); ?>;

		</script><?php
			
		}

	}

	$db_snow = new dbSnow();