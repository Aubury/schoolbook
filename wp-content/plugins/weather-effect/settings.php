<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Setting Page
add_action( 'admin_menu', 'weather_effect' );
function weather_effect() {
	add_menu_page( 'Weather Effect Setting', __( 'Weather Effect', 'weather-effect' ), 'manage_options', 'weather-effects-setting', 'weather_effect_setting_page', 'dashicons-cloud' );
	add_submenu_page( 'weather-effects-setting', __( 'Our Themes', 'weather-effect' ), __( 'Our Themes', 'weather-effect' ), 'manage_options', 'awplife-our-themes', 'awplife_we_our_themes_page_body' );
	add_submenu_page( 'weather-effects-setting', __( 'Our Plugins', 'weather-effect' ), __( 'Our Plugins', 'weather-effect' ), 'manage_options', 'awplife-our-plugins', 'awplife_we_our_plugins_page_body' );
}

function weather_effect_setting_page() {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'awplife-we-snow-bootstrap-js', AWPLIFE_WE_PLUGIN_PATH . 'assets/js/bootstrap.js', array( 'jquery' ), '1.6.1', true );
	wp_enqueue_script( 'awplife-we-snow-christmas-snow-js', AWPLIFE_WE_PLUGIN_PATH . 'assets/js/christmas-snow/christmas-snow.js', array( 'jquery' ), '1.6.1', true );
	wp_enqueue_script( 'awplife-we-snow-snow-falling-js', AWPLIFE_WE_PLUGIN_PATH . 'assets/js/snow-falling/snow-falling.js', array( 'jquery' ), '1.6.1', true );
	wp_enqueue_script( 'awplife-we-snow-snowfall-master-js', AWPLIFE_WE_PLUGIN_PATH . 'assets/js/snowfall-master/snowfall-master.min.js', array( 'jquery' ), '1.6.1', true );
	wp_enqueue_script( 'awplife-we-image-preview-js', AWPLIFE_WE_PLUGIN_PATH . 'assets/js/image-preview.js', array( 'jquery' ), '1.6.1', true );
	wp_add_inline_script( 'awplife-we-image-preview-js', 'var AWPLIFE_WE_PLUGIN_PATH = ' . wp_json_encode( AWPLIFE_WE_PLUGIN_PATH ) . ';', 'before' );
	wp_enqueue_script( 'awplife-we-admin-ui', AWPLIFE_WE_PLUGIN_PATH . 'assets/js/admin-ui-enhancements.js', array( 'jquery' ), '1.6.1', true );
	wp_enqueue_style( 'awplife-we-bootstrap-css', AWPLIFE_WE_PLUGIN_PATH . 'assets/css/bootstrap.css', array(), '1.6.1' );
	wp_enqueue_style( 'awplife-we-font-awesome-css', AWPLIFE_WE_PLUGIN_PATH . 'assets/css/font-awesome.css', array(), '1.6.1' );
	wp_enqueue_style( 'awplife-we-admin-modern', AWPLIFE_WE_PLUGIN_PATH . 'assets/css/admin-modern.css', array( 'awplife-we-bootstrap-css' ), '1.6.1' );
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );

	$weather_effect_settings = get_option( 'weather_effect_settings' );
	?>
	<style>
		.wep-loading-overlay {
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
			z-index: 999999;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			gap: 20px;
		}
		.wep-loading-spinner {
			width: 50px;
			height: 50px;
			border: 4px solid #e2e8f0;
			border-top-color: #6366f1;
			border-radius: 50%;
			animation: wep-spin 0.8s linear infinite;
		}
		.wep-loading-text {
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
			font-size: 16px;
			font-weight: 500;
			color: #64748b;
		}
		.wep-loading-icon {
			margin-bottom: 10px;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.wep-loading-icon .dashicons {
			font-size: 48px !important;
			width: 48px !important;
			height: 48px !important;
			color: #6366f1 !important;
			animation: wep-pulse 1.5s infinite ease-in-out;
		}
		@keyframes wep-pulse {
			0%, 100% { transform: scale(1); opacity: 0.7; }
			50% { transform: scale(1.1); opacity: 1; }
		}
		@keyframes wep-spin {
			to { transform: rotate(360deg); }
		}
		.wep-admin-wrap {
			opacity: 0;
			transition: opacity 0.3s ease;
		}
		.wep-admin-wrap.wep-loaded {
			opacity: 1;
		}
	</style>

	<div class="wep-loading-overlay" id="wep-loading-overlay">
		<div class="wep-loading-spinner"></div>
		<div class="wep-loading-text"><?php esc_html_e( 'Loading Weather Effect...', 'weather-effect' ); ?></div>
	</div>

	<div class="wep-admin-wrap">
		<div class="snowfalls-page">
			<h1>
				<span>Weather Effect - <?php esc_html_e( 'Configure Settings', 'weather-effect' ); ?></span>
				<span class="wep-version-badge">v<?php echo esc_html( AWPLIFE_WE_VERSION ); ?></span>
			</h1>
			<form id="snowfall-setting-form">
				<?php wp_nonce_field( 'we_save_nonce', 'we_settings_nonce' ); ?>
				<div class="wep-top-controls">
					<div class="wep-control-group">
						<p class="bg-title"><?php esc_html_e( 'Turn ON / OFF Effect', 'weather-effect' ); ?></p>
						<?php
						if ( isset( $weather_effect_settings['enable_weather_effect'] ) ) {
							$enable_weather_effect = $weather_effect_settings['enable_weather_effect'];
						} else {
							$enable_weather_effect = 0;
						}
						?>
						<p class="col-xs-6 switch-field em_size_field padding_settings">
							<input class="widefat" id="enable_weather_effect1" name="enable_weather_effect" type="radio" value="1"
							<?php
							if ( $enable_weather_effect == '1' ) {
								echo 'checked=checked';}
							?>
							>
							<label for="enable_weather_effect1"><?php esc_html_e( 'Enable', 'weather-effect' ); ?></label>
							<input class="widefat" id="enable_weather_effect2" name="enable_weather_effect" type="radio" value="0"
							<?php
							if ( $enable_weather_effect == '0' ) {
								echo 'checked=checked';}
							?>
							>
							<label for="enable_weather_effect2"><?php esc_html_e( 'Disable', 'weather-effect' ); ?></label>
						</p>
					</div>
				</div>

				<div class="row wep-occasion-row">
					<p class="wep-section-heading"><?php esc_html_e( 'Select A Weather / Season / Occasion', 'weather-effect' ); ?></p>
					<?php
					if ( isset( $weather_effect_settings['weather_occasion'] ) ) {
						$weather_occasion = $weather_effect_settings['weather_occasion'];
					} else {
						$weather_occasion = 'christmas_check';
					}
					?>
					<div class="wep-occasion-select-wrap">
						<select id="weather_occasion" name="weather_occasion" class="form-control">
							<option value="christmas_check"
							<?php
							if ( $weather_occasion == 'christmas_check' ) {
								echo 'selected="selected"';}
							?>
							><?php esc_html_e( 'Christmas', 'weather-effect' ); ?></option>
							<option value="winter_check"
							<?php
							if ( $weather_occasion == 'winter_check' ) {
								echo 'selected="selected"';}
							?>
							><?php esc_html_e( 'Winter', 'weather-effect' ); ?></option>
							<option value="autumn_check"
							<?php
							if ( $weather_occasion == 'autumn_check' ) {
								echo 'selected="selected"';}
							?>
							><?php esc_html_e( 'Autumn', 'weather-effect' ); ?></option>
							<option value="spring_check"
							<?php
							if ( $weather_occasion == 'spring_check' ) {
								echo 'selected="selected"';}
							?>
							><?php esc_html_e( 'Spring', 'weather-effect' ); ?></option>
							<option value="summer_check"
							<?php
							if ( $weather_occasion == 'summer_check' ) {
								echo 'selected="selected"';}
							?>
							><?php esc_html_e( 'Summer', 'weather-effect' ); ?></option>
							<option value="rainy_check"
							<?php
							if ( $weather_occasion == 'rainy_check' ) {
								echo 'selected="selected"';}
							?>
							><?php esc_html_e( 'Rain', 'weather-effect' ); ?></option>
							<option value="halloween_check"
							<?php
							if ( $weather_occasion == 'halloween_check' ) {
								echo 'selected="selected"';}
							?>
							><?php esc_html_e( 'Halloween', 'weather-effect' ); ?></option>
							<option value="thanks_giving_check"
							<?php
							if ( $weather_occasion == 'thanks_giving_check' ) {
								echo 'selected="selected"';}
							?>
							><?php esc_html_e( 'Thanks Giving', 'weather-effect' ); ?></option>
							<option value="valentine_check"
							<?php
							if ( $weather_occasion == 'valentine_check' ) {
								echo 'selected="selected"';}
							?>
							><?php esc_html_e( 'Valentine', 'weather-effect' ); ?></option>
							<option value="new_year_check"
							<?php
							if ( $weather_occasion == 'new_year_check' ) {
								echo 'selected="selected"';}
							?>
							><?php esc_html_e( 'New Year', 'weather-effect' ); ?></option>
						</select>
					</div>
				</div>

				<div>
				<?php
					require_once 'settings/christmas-snow-settings.php';
					require_once 'settings/winter-settings.php';
					require_once 'settings/autumn-settings.php';
					require_once 'settings/spring-settings.php';
					require_once 'settings/summer-settings.php';
					require_once 'settings/rainy-settings.php';
					require_once 'settings/halloween-settings.php';
					require_once 'settings/thanks-giving-settings.php';
					require_once 'settings/valentine-settings.php';
					require_once 'settings/new-year-settings.php';
				?>
				</div>

				<script>
					jQuery(document).ready(function ($) {
						function showSeasonPanel(selectedOccasion) {
							$('#christmas_weather_effect').hide();
							$('#winter_weather_effect').hide();
							$('#autumn_weather_effect').hide();
							$('#spring_weather_effect').hide();
							$('#summer_weather_effect').hide();
							$('#halloween_weather_effect').hide();
							$('#rain_weather_effect').hide();
							$('#thanksgiving_weather_effect').hide();
							$('#valentine_weather_effect').hide();
							$('#new_year_weather_effect').hide();

							switch (selectedOccasion) {
								case 'christmas_check': $('#christmas_weather_effect').show(); break;
								case 'winter_check': $('#winter_weather_effect').show(); break;
								case 'autumn_check': $('#autumn_weather_effect').show(); break;
								case 'spring_check': $('#spring_weather_effect').show(); break;
								case 'summer_check': $('#summer_weather_effect').show(); break;
								case 'halloween_check': $('#halloween_weather_effect').show(); break;
								case 'rainy_check': $('#rain_weather_effect').show(); break;
								case 'thanks_giving_check': $('#thanksgiving_weather_effect').show(); break;
								case 'valentine_check': $('#valentine_weather_effect').show(); break;
								case 'new_year_check': $('#new_year_weather_effect').show(); break;
							}
						}

						var initialOccasion = $('#weather_occasion').val();
						showSeasonPanel(initialOccasion);

						$('#weather_occasion').on('change', function () {
							showSeasonPanel($(this).val());
						});
					});
				</script>

				<input type="hidden" id="snow_action" name="snow_action" value="save_setting">

				<div class="wep-action-buttons">
					<button type="button" id="wep-save_setting" class="wep-btn wep-btn-primary" onclick="SnowSaveSettings();">
						<span class="wep-btn-icon"><span class="dashicons dashicons-saved"></span></span>
						<span class="wep-btn-spinner" style="display: none;"><span class="dashicons dashicons-update wep-spin"></span></span>
						<span class="wep-btn-text"><?php esc_html_e( 'Save Settings', 'weather-effect' ); ?></span>
					</button>
					<button type="button" id="wep-preview" class="wep-btn wep-btn-secondary" onclick="PreviewSettings();">
						<span class="wep-btn-icon"><span class="dashicons dashicons-visibility"></span></span>
						<span class="wep-btn-spinner" style="display: none;"><span class="dashicons dashicons-update wep-spin"></span></span>
						<span class="wep-btn-text"><?php esc_html_e( 'Preview', 'weather-effect' ); ?></span>
					</button>
				</div>
				<p class="wep-preview-note">
					<em><?php esc_html_e( 'Preview saves and opens your site in a new tab', 'weather-effect' ); ?></em>
				</p>

				<hr />
				<h3><?php esc_html_e( 'Review Appeal', 'weather-effect' ); ?></h3>
				<p><?php esc_html_e( 'We hope you love the plugin. If you do, would you consider posting an online review? This helps us to continue providing great plugin and support to potential users to make confident decisions.', 'weather-effect' ); ?></p>
				<a href="https://wordpress.org/support/plugin/weather-effect/reviews/#new-post" target="_blank" class="wep-btn wep-btn-success wep-review-btn">
					<span class="dashicons dashicons-thumbs-up wep-review-icon"></span> <?php esc_html_e( 'Post Review', 'weather-effect' ); ?>
				</a>
			</form>

			<hr />
			<div class="wep-upgrade-section">
				<div class="wep-upgrade-header">
					<h2><?php esc_html_e( 'Upgrade To Weather Effect Premium Just In Half Price', 'weather-effect' ); ?></h2>
					<div class="wep-upgrade-pricing">
						<span class="wep-strike-price">$42</span>
						<span class="wep-sale-price">$21</span>
					</div>
				</div>
				
				<div class="wep-pro-features-grid">
					<div class="wep-feature-item">
						<span class="wep-feature-icon"><span class="dashicons dashicons-calendar-alt"></span></span>
						<div class="wep-feature-content">
							<strong><?php esc_html_e( '11 Seasonal Occasions', 'weather-effect' ); ?></strong>
							<p><?php esc_html_e( 'Unlock Spring, Summer, Halloween, Thanksgiving, Valentine\'s Day, New Year, and Birthday.', 'weather-effect' ); ?></p>
						</div>
					</div>
					<div class="wep-feature-item">
						<span class="wep-feature-icon"><span class="dashicons dashicons-cloud-upload"></span></span>
						<div class="wep-feature-content">
							<strong><?php esc_html_e( 'Custom Image Upload', 'weather-effect' ); ?></strong>
							<p><?php esc_html_e( 'Upload up to 10 of your own PNG/JPEG/GIF graphics as falling elements.', 'weather-effect' ); ?></p>
						</div>
					</div>
					<div class="wep-feature-item">
						<span class="wep-feature-icon"><span class="dashicons dashicons-clock"></span></span>
						<div class="wep-feature-content">
							<strong><?php esc_html_e( 'Auto-Timeout Settings', 'weather-effect' ); ?></strong>
							<p><?php esc_html_e( 'Set custom seconds to automatically stop the weather animations.', 'weather-effect' ); ?></p>
						</div>
					</div>
					<div class="wep-feature-item">
						<span class="wep-feature-icon"><span class="dashicons dashicons-no-alt"></span></span>
						<div class="wep-feature-content">
							<strong><?php esc_html_e( 'Targeted Exclusions', 'weather-effect' ); ?></strong>
							<p><?php esc_html_e( 'Exclude weather effects on specific pages, posts, or Custom Post Types.', 'weather-effect' ); ?></p>
						</div>
					</div>
					<div class="wep-feature-item">
						<span class="wep-feature-icon"><span class="dashicons dashicons-admin-generic"></span></span>
						<div class="wep-feature-content">
							<strong><?php esc_html_e( 'Advanced Velocity & Sizing', 'weather-effect' ); ?></strong>
							<p><?php esc_html_e( 'Configure random particle sizing and customized falling speeds.', 'weather-effect' ); ?></p>
						</div>
					</div>
					<div class="wep-feature-item">
						<span class="wep-feature-icon"><span class="dashicons dashicons-performance"></span></span>
						<div class="wep-feature-content">
							<strong><?php esc_html_e( 'High-Performance Canvas Engine', 'weather-effect' ); ?></strong>
							<p><?php esc_html_e( 'Renders hundreds of smooth particles at 60fps with lightweight footprint.', 'weather-effect' ); ?></p>
						</div>
					</div>
					<div class="wep-feature-item">
						<span class="wep-feature-icon"><span class="dashicons dashicons-cloud"></span></span>
						<div class="wep-feature-content">
							<strong><?php esc_html_e( 'Wind Control & Trajectory', 'weather-effect' ); ?></strong>
							<p><?php esc_html_e( 'Configure wind directions and horizontal drift speeds for realistic motion.', 'weather-effect' ); ?></p>
						</div>
					</div>
					<div class="wep-feature-item">
						<span class="wep-feature-icon"><span class="dashicons dashicons-move"></span></span>
						<div class="wep-feature-content">
							<strong><?php esc_html_e( 'Interactive Mouse Proximity', 'weather-effect' ); ?></strong>
							<p><?php esc_html_e( 'Particles scatter away dynamically as the visitor moves their cursor.', 'weather-effect' ); ?></p>
						</div>
					</div>
					<div class="wep-feature-item">
						<span class="wep-feature-icon"><span class="dashicons dashicons-arrow-down-alt2"></span></span>
						<div class="wep-feature-content">
							<strong><?php esc_html_e( 'Dynamic Scroll Parallax', 'weather-effect' ); ?></strong>
							<p><?php esc_html_e( 'Creates a sense of depth by moving particles relative to scroll speed.', 'weather-effect' ); ?></p>
						</div>
					</div>
					<div class="wep-feature-item">
						<span class="wep-feature-icon"><span class="dashicons dashicons-image-rotate"></span></span>
						<div class="wep-feature-content">
							<strong><?php esc_html_e( 'Sway & Rotation Effects', 'weather-effect' ); ?></strong>
							<p><?php esc_html_e( 'Particles rotate and sway side-to-side naturally as they float down.', 'weather-effect' ); ?></p>
						</div>
					</div>
					<div class="wep-feature-item">
						<span class="wep-feature-icon"><span class="dashicons dashicons-format-image"></span></span>
						<div class="wep-feature-content">
							<strong><?php esc_html_e( 'Bottom Snow Accumulation', 'weather-effect' ); ?></strong>
							<p><?php esc_html_e( 'Enable falling elements to settle and stack up at the bottom of the screen.', 'weather-effect' ); ?></p>
						</div>
					</div>
					<div class="wep-feature-item">
						<span class="wep-feature-icon"><span class="dashicons dashicons-cart"></span></span>
						<div class="wep-feature-content">
							<strong><?php esc_html_e( 'WooCommerce & Blocks Ready', 'weather-effect' ); ?></strong>
							<p><?php esc_html_e( 'Perfectly compatible with block editors, WooCommerce catalogs, and custom templates.', 'weather-effect' ); ?></p>
						</div>
					</div>
				</div>

				<div class="wep-upgrade-actions">
					<a href="https://awplife.com/wordpress-plugins/weather-effect-wordpress-plugin/" target="_blank" class="wep-btn wep-btn-primary"><?php esc_html_e( 'Premium Version Details', 'weather-effect' ); ?></a>
					<a href="https://awplife.com/demo/weather-effect/" target="_blank" class="wep-btn wep-btn-secondary"><?php esc_html_e( 'Check Live Demo', 'weather-effect' ); ?></a>
				</div>
			</div>
			<hr />

			<div class="awp_bale_offer">
				<h1><?php esc_html_e( "Plugin's Bale Offer", 'weather-effect' ); ?></h1>
				<h3><?php esc_html_e( 'Get All Premium Plugin ( Personal License) in just $149', 'weather-effect' ); ?></h3>
				<h3><strike>$399</strike> <?php esc_html_e( 'For $149 Only', 'weather-effect' ); ?></h3>
				<div class="awp_bale_btn_wrap">
					<a href="https://awplife.com/account/signup/all-premium-plugins" target="_blank" class="wep-btn wep-btn-primary wep-btn-bale"><?php esc_html_e( 'BUY NOW', 'weather-effect' ); ?></a>
				</div>
			</div>
		</div>
	</div>

	<script>
		function SnowSaveSettings() {
			jQuery("#wep-save_setting").prop('disabled', true);
			jQuery("#wep-preview").prop('disabled', true);
			jQuery("#wep-save_setting .wep-btn-icon").hide();
			jQuery("#wep-save_setting .wep-btn-spinner").show();
			jQuery("#wep-save_setting .wep-btn-text").text("<?php echo esc_js( __( 'Saving...', 'weather-effect' ) ); ?>");

			jQuery.ajax({
				dataType: 'html',
				type: 'POST',
				url: location.href,
				cache: false,
				data: jQuery('#snowfall-setting-form').serialize() + '&snow_action=save_setting' + '&security=' + <?php echo wp_json_encode( wp_create_nonce( 'we_save_nonce' ) ); ?>,
				complete: function() {},
				success: function(data) {
					jQuery("#wep-save_setting").prop('disabled', false);
					jQuery("#wep-preview").prop('disabled', false);
					jQuery("#wep-save_setting .wep-btn-icon").show();
					jQuery("#wep-save_setting .wep-btn-spinner").hide();
					jQuery("#wep-save_setting .wep-btn-text").text("<?php echo esc_js( __( 'Save Settings', 'weather-effect' ) ); ?>");

					jQuery('#result-msg').html(jQuery(data).find('div#setting-result'));
					jQuery("div#setting-result").fadeOut(5000, "linear");
				},
				error: function(xhr, status, error) {
					console.error("AJAX error saving settings:", status, error);
					jQuery("#wep-save_setting").prop('disabled', false);
					jQuery("#wep-preview").prop('disabled', false);
					jQuery("#wep-save_setting .wep-btn-icon").show();
					jQuery("#wep-save_setting .wep-btn-spinner").hide();
					jQuery("#wep-save_setting .wep-btn-text").text("<?php echo esc_js( __( 'Save Settings', 'weather-effect' ) ); ?>");
				}
			});
		}

		function PreviewSettings() {
			jQuery("#wep-save_setting").prop('disabled', true);
			jQuery("#wep-preview").prop('disabled', true);
			jQuery("#wep-preview .wep-btn-icon").hide();
			jQuery("#wep-preview .wep-btn-spinner").show();
			jQuery("#wep-preview .wep-btn-text").text("<?php echo esc_js( __( 'Saving...', 'weather-effect' ) ); ?>");

			jQuery.ajax({
				dataType: 'html',
				type: 'POST',
				url: location.href,
				cache: false,
				data: jQuery('#snowfall-setting-form').serialize() + '&snow_action=save_setting' + '&security=' + <?php echo wp_json_encode( wp_create_nonce( 'we_save_nonce' ) ); ?>,
				success: function(data) {
					jQuery("#wep-save_setting").prop('disabled', false);
					jQuery("#wep-preview").prop('disabled', false);
					jQuery("#wep-preview .wep-btn-icon").show();
					jQuery("#wep-preview .wep-btn-spinner").hide();
					jQuery("#wep-preview .wep-btn-text").text("<?php echo esc_js( __( 'Preview', 'weather-effect' ) ); ?>");

					window.open('<?php echo esc_url( home_url( '/' ) ); ?>', 'we_preview_window');
				},
				error: function(xhr, status, error) {
					console.error("AJAX error loading preview:", status, error);
					jQuery("#wep-save_setting").prop('disabled', false);
					jQuery("#wep-preview").prop('disabled', false);
					jQuery("#wep-preview .wep-btn-icon").show();
					jQuery("#wep-preview .wep-btn-spinner").hide();
					jQuery("#wep-preview .wep-btn-text").text("<?php echo esc_js( __( 'Preview', 'weather-effect' ) ); ?>");
				}
			});
		}

		var rangeSlider = function(){
			var slider = jQuery('.range-slider'),
				range = jQuery('.range-slider__range'),
				value = jQuery('.range-slider__value');
			slider.each(function(){
				value.each(function(){
					var value = jQuery(this).prev().attr('value');
					jQuery(this).html(value);
				});
				range.on('input', function(){
					jQuery(this).siblings('.range-slider__value').html(this.value);
				});
			});
		};
		rangeSlider();

		jQuery(window).on('load', function() {
			setTimeout(function() {
				jQuery('#wep-loading-overlay').fadeOut(300);
				jQuery('.wep-admin-wrap').addClass('wep-loaded');
			}, 100);
		});
		setTimeout(function() {
			jQuery('#wep-loading-overlay').fadeOut(300);
			jQuery('.wep-admin-wrap').addClass('wep-loaded');
		}, 3000);
	</script>
	<?php

	if ( isset( $_POST['snow_action'] ) ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$we_save_nonce_value = isset( $_POST['security'] ) ? wp_unslash( $_POST['security'] ) : '';
		if ( wp_verify_nonce( $we_save_nonce_value, 'we_save_nonce' ) ) {

			$enable_weather_effect = isset( $_POST['enable_weather_effect'] ) ? sanitize_text_field( wp_unslash( $_POST['enable_weather_effect'] ) ) : 1;
			$weather_occasion      = isset( $_POST['weather_occasion'] ) ? sanitize_text_field( wp_unslash( $_POST['weather_occasion'] ) ) : 'christmas_check';

			$christmas_types         = isset( $_POST['christmas_types'] ) ? sanitize_text_field( wp_unslash( $_POST['christmas_types'] ) ) : 'snow_effect';
			$ball                    = isset( $_POST['ball'] ) ? sanitize_text_field( wp_unslash( $_POST['ball'] ) ) : '';
			$christmas_ball          = isset( $_POST['christmas_ball'] ) ? sanitize_text_field( wp_unslash( $_POST['christmas_ball'] ) ) : 'ball3';
			$bell                    = isset( $_POST['bell'] ) ? sanitize_text_field( wp_unslash( $_POST['bell'] ) ) : '';
			$christmas_bell          = isset( $_POST['christmas_bell'] ) ? sanitize_text_field( wp_unslash( $_POST['christmas_bell'] ) ) : 'bell3';
			$candy                   = isset( $_POST['candy'] ) ? sanitize_text_field( wp_unslash( $_POST['candy'] ) ) : '';
			$christmas_candy         = isset( $_POST['christmas_candy'] ) ? sanitize_text_field( wp_unslash( $_POST['christmas_candy'] ) ) : 'candy3';
			$gift                    = isset( $_POST['gift'] ) ? sanitize_text_field( wp_unslash( $_POST['gift'] ) ) : '';
			$christmas_gift          = isset( $_POST['christmas_gift'] ) ? sanitize_text_field( wp_unslash( $_POST['christmas_gift'] ) ) : 'gift3';
			$snowman                 = isset( $_POST['snowman'] ) ? sanitize_text_field( wp_unslash( $_POST['snowman'] ) ) : '';
			$christmas_snowman       = isset( $_POST['christmas_snowman'] ) ? sanitize_text_field( wp_unslash( $_POST['christmas_snowman'] ) ) : 'Snowman3';
			$snow_flake              = isset( $_POST['snow_flake'] ) ? sanitize_text_field( wp_unslash( $_POST['snow_flake'] ) ) : '';
			$christmas_snow_flake    = isset( $_POST['christmas_snow_flake'] ) ? sanitize_text_field( wp_unslash( $_POST['christmas_snow_flake'] ) ) : 'flack2';
			$christmas_min_size_leaf = isset( $_POST['christmas_min_size_leaf'] ) ? absint( $_POST['christmas_min_size_leaf'] ) : 20;
			$christmas_max_size_leaf = isset( $_POST['christmas_max_size_leaf'] ) ? absint( $_POST['christmas_max_size_leaf'] ) : 50;
			$christmas_flakes_leaf   = isset( $_POST['christmas_flakes_leaf'] ) ? absint( $_POST['christmas_flakes_leaf'] ) : 5;
			$christmas_speed         = isset( $_POST['christmas_speed'] ) ? absint( $_POST['christmas_speed'] ) : 5;
			$master_round            = isset( $_POST['master_round'] ) ? sanitize_text_field( wp_unslash( $_POST['master_round'] ) ) : 'false';
			$round_type              = isset( $_POST['round_type'] ) ? sanitize_text_field( wp_unslash( $_POST['round_type'] ) ) : 'true';
			$min_size_round          = isset( $_POST['min_size_round'] ) ? absint( $_POST['min_size_round'] ) : 4;
			$max_size_round          = isset( $_POST['max_size_round'] ) ? absint( $_POST['max_size_round'] ) : 20;
			$master_shadows          = isset( $_POST['master_shadows'] ) ? sanitize_text_field( wp_unslash( $_POST['master_shadows'] ) ) : 'false';
			$shadow_type             = isset( $_POST['shadow_type'] ) ? sanitize_text_field( wp_unslash( $_POST['shadow_type'] ) ) : 'true';
			$flakes_shadow           = isset( $_POST['flakes_shadow'] ) ? absint( $_POST['flakes_shadow'] ) : 50;
			$snow_types              = isset( $_POST['snow_types'] ) ? sanitize_text_field( wp_unslash( $_POST['snow_types'] ) ) : 'winter_snow';
			$ice_cube                = isset( $_POST['ice_cube'] ) ? sanitize_text_field( wp_unslash( $_POST['ice_cube'] ) ) : 'true';
			$min_size_christmas      = isset( $_POST['min_size_christmas'] ) ? absint( $_POST['min_size_christmas'] ) : 1;
			$max_size_christmas      = isset( $_POST['max_size_christmas'] ) ? absint( $_POST['max_size_christmas'] ) : 5;
			$flake_christmas         = isset( $_POST['flake_christmas'] ) ? absint( $_POST['flake_christmas'] ) : 5;
			$min_size_falling        = isset( $_POST['min_size_falling'] ) ? absint( $_POST['min_size_falling'] ) : 3;
			$max_size_falling        = isset( $_POST['max_size_falling'] ) ? absint( $_POST['max_size_falling'] ) : 30;
			$snow_falling_time       = isset( $_POST['snow_falling_time'] ) ? absint( $_POST['snow_falling_time'] ) : 500;
			$snow_falling_color      = isset( $_POST['snow_falling_color'] ) ? sanitize_text_field( wp_unslash( $_POST['snow_falling_color'] ) ) : 'FFFFFF';
			$leaf                    = isset( $_POST['leaf'] ) ? sanitize_text_field( wp_unslash( $_POST['leaf'] ) ) : '';
			$autumn_leaf             = isset( $_POST['autumn_leaf'] ) ? sanitize_text_field( wp_unslash( $_POST['autumn_leaf'] ) ) : 'autumn-leaf2';
			$autumn_min_size_leaf    = isset( $_POST['autumn_min_size_leaf'] ) ? absint( $_POST['autumn_min_size_leaf'] ) : 20;
			$autumn_max_size_leaf    = isset( $_POST['autumn_max_size_leaf'] ) ? absint( $_POST['autumn_max_size_leaf'] ) : 50;
			$autumn_flakes_leaf      = isset( $_POST['autumn_flakes_leaf'] ) ? absint( $_POST['autumn_flakes_leaf'] ) : 5;
			$autumn_speed            = isset( $_POST['autumn_speed'] ) ? absint( $_POST['autumn_speed'] ) : 5;
			$leaf_spring             = isset( $_POST['leaf_spring'] ) ? sanitize_text_field( wp_unslash( $_POST['leaf_spring'] ) ) : '';
			$spring_leaf             = isset( $_POST['spring_leaf'] ) ? sanitize_text_field( wp_unslash( $_POST['spring_leaf'] ) ) : 'spring-leaf2';
			$spring_min_size_leaf    = isset( $_POST['spring_min_size_leaf'] ) ? absint( $_POST['spring_min_size_leaf'] ) : 20;
			$spring_max_size_leaf    = isset( $_POST['spring_max_size_leaf'] ) ? absint( $_POST['spring_max_size_leaf'] ) : 50;
			$spring_flakes_leaf      = isset( $_POST['spring_flakes_leaf'] ) ? absint( $_POST['spring_flakes_leaf'] ) : 5;
			$spring_speed            = isset( $_POST['spring_speed'] ) ? absint( $_POST['spring_speed'] ) : 5;
			$drink                   = isset( $_POST['drink'] ) ? sanitize_text_field( wp_unslash( $_POST['drink'] ) ) : '';
			$summer_drink            = isset( $_POST['summer_drink'] ) ? sanitize_text_field( wp_unslash( $_POST['summer_drink'] ) ) : 'drink2';
			$sun                     = isset( $_POST['sun'] ) ? sanitize_text_field( wp_unslash( $_POST['sun'] ) ) : '';
			$summer_sun              = isset( $_POST['summer_sun'] ) ? sanitize_text_field( wp_unslash( $_POST['summer_sun'] ) ) : 'sun2';
			$summer_min_size_leaf    = isset( $_POST['summer_min_size_leaf'] ) ? absint( $_POST['summer_min_size_leaf'] ) : 20;
			$summer_max_size_leaf    = isset( $_POST['summer_max_size_leaf'] ) ? absint( $_POST['summer_max_size_leaf'] ) : 50;
			$summer_flakes_leaf      = isset( $_POST['summer_flakes_leaf'] ) ? absint( $_POST['summer_flakes_leaf'] ) : 5;
			$summer_speed            = isset( $_POST['summer_speed'] ) ? absint( $_POST['summer_speed'] ) : 5;
			$umbrella                = isset( $_POST['umbrella'] ) ? sanitize_text_field( wp_unslash( $_POST['umbrella'] ) ) : '';
			$rain_umbrella           = isset( $_POST['rain_umbrella'] ) ? sanitize_text_field( wp_unslash( $_POST['rain_umbrella'] ) ) : 'umbrella2';
			$drop                    = isset( $_POST['drop'] ) ? sanitize_text_field( wp_unslash( $_POST['drop'] ) ) : '';
			$rain_drops              = isset( $_POST['rain_drops'] ) ? sanitize_text_field( wp_unslash( $_POST['rain_drops'] ) ) : 'drops2';
			$cloud                   = isset( $_POST['cloud'] ) ? sanitize_text_field( wp_unslash( $_POST['cloud'] ) ) : '';
			$rain_cloud              = isset( $_POST['rain_cloud'] ) ? sanitize_text_field( wp_unslash( $_POST['rain_cloud'] ) ) : 'cloud2';
			$rain_min_size_leaf      = isset( $_POST['rain_min_size_leaf'] ) ? absint( $_POST['rain_min_size_leaf'] ) : 20;
			$rain_max_size_leaf      = isset( $_POST['rain_max_size_leaf'] ) ? absint( $_POST['rain_max_size_leaf'] ) : 50;
			$rain_flakes_leaf        = isset( $_POST['rain_flakes_leaf'] ) ? absint( $_POST['rain_flakes_leaf'] ) : 5;
			$rain_speed              = isset( $_POST['rain_speed'] ) ? absint( $_POST['rain_speed'] ) : 5;
			$ghost                   = isset( $_POST['ghost'] ) ? sanitize_text_field( wp_unslash( $_POST['ghost'] ) ) : '';
			$halloween_ghost         = isset( $_POST['halloween_ghost'] ) ? sanitize_text_field( wp_unslash( $_POST['halloween_ghost'] ) ) : 'ghost2';
			$bats                    = isset( $_POST['bats'] ) ? sanitize_text_field( wp_unslash( $_POST['bats'] ) ) : '';
			$halloween_bats          = isset( $_POST['halloween_bats'] ) ? sanitize_text_field( wp_unslash( $_POST['halloween_bats'] ) ) : 'bats2';
			$moon                    = isset( $_POST['moon'] ) ? sanitize_text_field( wp_unslash( $_POST['moon'] ) ) : '';
			$halloween_moon          = isset( $_POST['halloween_moon'] ) ? sanitize_text_field( wp_unslash( $_POST['halloween_moon'] ) ) : 'moon2';
			$pumpkin                 = isset( $_POST['pumpkin'] ) ? sanitize_text_field( wp_unslash( $_POST['pumpkin'] ) ) : '';
			$halloween_pumpkin       = isset( $_POST['halloween_pumpkin'] ) ? sanitize_text_field( wp_unslash( $_POST['halloween_pumpkin'] ) ) : 'pumpkin2';
			$web                     = isset( $_POST['web'] ) ? sanitize_text_field( wp_unslash( $_POST['web'] ) ) : '';
			$halloween_web           = isset( $_POST['halloween_web'] ) ? sanitize_text_field( wp_unslash( $_POST['halloween_web'] ) ) : 'web2';
			$witch                   = isset( $_POST['witch'] ) ? sanitize_text_field( wp_unslash( $_POST['witch'] ) ) : '';
			$halloween_witch         = isset( $_POST['halloween_witch'] ) ? sanitize_text_field( wp_unslash( $_POST['halloween_witch'] ) ) : 'witch2';
			$halloween_min_size_leaf = isset( $_POST['halloween_min_size_leaf'] ) ? absint( $_POST['halloween_min_size_leaf'] ) : 20;
			$halloween_max_size_leaf = isset( $_POST['halloween_max_size_leaf'] ) ? absint( $_POST['halloween_max_size_leaf'] ) : 50;
			$halloween_flakes_leaf   = isset( $_POST['halloween_flakes_leaf'] ) ? absint( $_POST['halloween_flakes_leaf'] ) : 5;
			$halloween_speed         = isset( $_POST['halloween_speed'] ) ? absint( $_POST['halloween_speed'] ) : 5;
			$turkey                  = isset( $_POST['turkey'] ) ? sanitize_text_field( wp_unslash( $_POST['turkey'] ) ) : '';
			$thanksgiving_turkey     = isset( $_POST['thanksgiving_turkey'] ) ? sanitize_text_field( wp_unslash( $_POST['thanksgiving_turkey'] ) ) : 'turkey2';
			$thanksgiving_min_size_leaf = isset( $_POST['thanksgiving_min_size_leaf'] ) ? absint( $_POST['thanksgiving_min_size_leaf'] ) : 20;
			$thanksgiving_max_size_leaf = isset( $_POST['thanksgiving_max_size_leaf'] ) ? absint( $_POST['thanksgiving_max_size_leaf'] ) : 50;
			$thanksgiving_flakes_leaf   = isset( $_POST['thanksgiving_flakes_leaf'] ) ? absint( $_POST['thanksgiving_flakes_leaf'] ) : 5;
			$thanksgiving_speed         = isset( $_POST['thanksgiving_speed'] ) ? absint( $_POST['thanksgiving_speed'] ) : 5;
			$rose                    = isset( $_POST['rose'] ) ? sanitize_text_field( wp_unslash( $_POST['rose'] ) ) : '';
			$valentine_rose          = isset( $_POST['valentine_rose'] ) ? sanitize_text_field( wp_unslash( $_POST['valentine_rose'] ) ) : 'rose2';
			$balloon                 = isset( $_POST['balloon'] ) ? sanitize_text_field( wp_unslash( $_POST['balloon'] ) ) : '';
			$valentine_balloon       = isset( $_POST['valentine_balloon'] ) ? sanitize_text_field( wp_unslash( $_POST['valentine_balloon'] ) ) : 'balloon2';
			$heart                   = isset( $_POST['heart'] ) ? sanitize_text_field( wp_unslash( $_POST['heart'] ) ) : '';
			$valentine_heart         = isset( $_POST['valentine_heart'] ) ? sanitize_text_field( wp_unslash( $_POST['valentine_heart'] ) ) : 'heart2';
			$valentine_min_size_leaf = isset( $_POST['valentine_min_size_leaf'] ) ? absint( $_POST['valentine_min_size_leaf'] ) : 20;
			$valentine_max_size_leaf = isset( $_POST['valentine_max_size_leaf'] ) ? absint( $_POST['valentine_max_size_leaf'] ) : 50;
			$valentine_flakes_leaf   = isset( $_POST['valentine_flakes_leaf'] ) ? absint( $_POST['valentine_flakes_leaf'] ) : 5;
			$valentine_speed         = isset( $_POST['valentine_speed'] ) ? absint( $_POST['valentine_speed'] ) : 5;
			$balloon_new             = isset( $_POST['balloon_new'] ) ? sanitize_text_field( wp_unslash( $_POST['balloon_new'] ) ) : '';
			$newyear_balloon         = isset( $_POST['newyear_balloon'] ) ? sanitize_text_field( wp_unslash( $_POST['newyear_balloon'] ) ) : 'balloon3';
			$sticker                 = isset( $_POST['sticker'] ) ? sanitize_text_field( wp_unslash( $_POST['sticker'] ) ) : '';
			$new_year_sticker        = isset( $_POST['new_year_sticker'] ) ? sanitize_text_field( wp_unslash( $_POST['new_year_sticker'] ) ) : 'sticker2';
			$newyear_min_size_leaf   = isset( $_POST['newyear_min_size_leaf'] ) ? absint( $_POST['newyear_min_size_leaf'] ) : 20;
			$newyear_max_size_leaf   = isset( $_POST['newyear_max_size_leaf'] ) ? absint( $_POST['newyear_max_size_leaf'] ) : 50;
			$newyear_flakes_leaf     = isset( $_POST['newyear_flakes_leaf'] ) ? absint( $_POST['newyear_flakes_leaf'] ) : 5;
			$newyear_speed           = isset( $_POST['newyear_speed'] ) ? absint( $_POST['newyear_speed'] ) : 5;

			$weather_effect_settings = array(
				'enable_weather_effect'      => $enable_weather_effect,
				'weather_occasion'           => $weather_occasion,
				'christmas_types'            => $christmas_types,
				'ball'                       => $ball,
				'christmas_ball'             => $christmas_ball,
				'bell'                       => $bell,
				'christmas_bell'             => $christmas_bell,
				'candy'                      => $candy,
				'christmas_candy'            => $christmas_candy,
				'gift'                       => $gift,
				'christmas_gift'             => $christmas_gift,
				'snowman'                    => $snowman,
				'christmas_snowman'          => $christmas_snowman,
				'snow_flake'                 => $snow_flake,
				'christmas_snow_flake'       => $christmas_snow_flake,
				'christmas_min_size_leaf'    => $christmas_min_size_leaf,
				'christmas_max_size_leaf'    => $christmas_max_size_leaf,
				'christmas_flakes_leaf'      => $christmas_flakes_leaf,
				'christmas_speed'            => $christmas_speed,
				'master_round'               => $master_round,
				'round_type'                 => $round_type,
				'min_size_round'             => $min_size_round,
				'max_size_round'             => $max_size_round,
				'master_shadows'             => $master_shadows,
				'shadow_type'                => $shadow_type,
				'flakes_shadow'              => $flakes_shadow,
				'snow_types'                 => $snow_types,
				'ice_cube'                   => $ice_cube,
				'min_size_christmas'         => $min_size_christmas,
				'max_size_christmas'         => $max_size_christmas,
				'flake_christmas'            => $flake_christmas,
				'min_size_falling'           => $min_size_falling,
				'max_size_falling'           => $max_size_falling,
				'snow_falling_time'          => $snow_falling_time,
				'snow_falling_color'         => $snow_falling_color,
				'leaf'                       => $leaf,
				'autumn_leaf'                => $autumn_leaf,
				'autumn_min_size_leaf'       => $autumn_min_size_leaf,
				'autumn_max_size_leaf'       => $autumn_max_size_leaf,
				'autumn_flakes_leaf'         => $autumn_flakes_leaf,
				'autumn_speed'               => $autumn_speed,
				'leaf_spring'                => $leaf_spring,
				'spring_leaf'                => $spring_leaf,
				'spring_min_size_leaf'       => $spring_min_size_leaf,
				'spring_max_size_leaf'       => $spring_max_size_leaf,
				'spring_flakes_leaf'         => $spring_flakes_leaf,
				'spring_speed'               => $spring_speed,
				'drink'                      => $drink,
				'summer_drink'               => $summer_drink,
				'sun'                        => $sun,
				'summer_sun'                 => $summer_sun,
				'summer_min_size_leaf'       => $summer_min_size_leaf,
				'summer_max_size_leaf'       => $summer_max_size_leaf,
				'summer_flakes_leaf'         => $summer_flakes_leaf,
				'summer_speed'               => $summer_speed,
				'umbrella'                   => $umbrella,
				'rain_umbrella'              => $rain_umbrella,
				'drop'                       => $drop,
				'rain_drops'                 => $rain_drops,
				'cloud'                      => $cloud,
				'rain_cloud'                 => $rain_cloud,
				'rain_min_size_leaf'         => $rain_min_size_leaf,
				'rain_max_size_leaf'         => $rain_max_size_leaf,
				'rain_flakes_leaf'           => $rain_flakes_leaf,
				'rain_speed'                 => $rain_speed,
				'ghost'                      => $ghost,
				'halloween_ghost'            => $halloween_ghost,
				'bats'                       => $bats,
				'halloween_bats'             => $halloween_bats,
				'moon'                       => $moon,
				'halloween_moon'             => $halloween_moon,
				'pumpkin'                    => $pumpkin,
				'halloween_pumpkin'          => $halloween_pumpkin,
				'web'                        => $web,
				'halloween_web'              => $halloween_web,
				'witch'                      => $witch,
				'halloween_witch'            => $halloween_witch,
				'halloween_min_size_leaf'    => $halloween_min_size_leaf,
				'halloween_max_size_leaf'    => $halloween_max_size_leaf,
				'halloween_flakes_leaf'      => $halloween_flakes_leaf,
				'halloween_speed'            => $halloween_speed,
				'turkey'                     => $turkey,
				'thanksgiving_turkey'        => $thanksgiving_turkey,
				'thanksgiving_min_size_leaf' => $thanksgiving_min_size_leaf,
				'thanksgiving_max_size_leaf' => $thanksgiving_max_size_leaf,
				'thanksgiving_flakes_leaf'   => $thanksgiving_flakes_leaf,
				'thanksgiving_speed'         => $thanksgiving_speed,
				'rose'                       => $rose,
				'valentine_rose'             => $valentine_rose,
				'balloon'                    => $balloon,
				'valentine_balloon'          => $valentine_balloon,
				'heart'                      => $heart,
				'valentine_heart'            => $valentine_heart,
				'valentine_min_size_leaf'    => $valentine_min_size_leaf,
				'valentine_max_size_leaf'    => $valentine_max_size_leaf,
				'valentine_flakes_leaf'      => $valentine_flakes_leaf,
				'valentine_speed'            => $valentine_speed,
				'balloon_new'                => $balloon_new,
				'newyear_balloon'            => $newyear_balloon,
				'sticker'                    => $sticker,
				'new_year_sticker'           => $new_year_sticker,
				'newyear_min_size_leaf'      => $newyear_min_size_leaf,
				'newyear_max_size_leaf'      => $newyear_max_size_leaf,
				'newyear_flakes_leaf'        => $newyear_flakes_leaf,
				'newyear_speed'              => $newyear_speed,
			);

			update_option( 'weather_effect_settings', $weather_effect_settings );
			?>
			<div class="updated notice is-dismissible" id="setting-result">
				<p><strong><?php esc_html_e( 'Settings Saved.', 'weather-effect' ); ?></strong></p>
			</div>
			<?php
		}
	}
}

function awplife_we_our_plugins_page_body() {
	require_once 'our-plugins.php';
}

function awplife_we_our_themes_page_body() {
	require_once 'our-themes.php';
}

add_action( 'admin_enqueue_scripts', 'weather_effect_admin_styles' );
function weather_effect_admin_styles() {
	$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
	if ( 'awplife-our-themes' === $page || 'awplife-our-plugins' === $page ) {
		wp_enqueue_style( 'awplife-we-our-plugins-style', AWPLIFE_WE_PLUGIN_PATH . 'assets/css/our-plugins-style.css', array(), '1.6.1' );
	}
}
