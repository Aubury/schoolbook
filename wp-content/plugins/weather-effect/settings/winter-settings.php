<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div id="winter_weather_effect" class="tab-content">
	<p class="wep-section-heading"><?php esc_html_e( 'Winter Falls Types', 'weather-effect' ); ?></p>
	<?php
	if ( isset( $weather_effect_settings['snow_types'] ) ) {
		$snow_types = $weather_effect_settings['snow_types'];
	} else {
		$snow_types = 'winter_snow';
	}
	?>
	<p class="switch-field padding_settings">
		<input class="widefat" id="snow_types1" name="snow_types" type="radio" value="winter_snow" 
		<?php
		if ( $snow_types == 'winter_snow' ) {
			echo 'checked=checked';}
		?>
		>
		<label for="snow_types1"><?php esc_html_e( 'Snows', 'weather-effect' ); ?></label>
		<input class="widefat" id="snow_types2" name="snow_types" type="radio" value="falling_snow" 
		<?php
		if ( $snow_types == 'falling_snow' ) {
			echo 'checked=checked';}
		?>
		>
		<label for="snow_types2"><?php esc_html_e( 'Snow Flakes', 'weather-effect' ); ?></label>
	</p>

	<!-- Christmas Snow Falls Settings -->
	<div id="snow_winter">
		<p class="bg-title"><?php esc_html_e( '1. Snow Falls Settings', 'weather-effect' ); ?></p>
		<div class="row">
			<label class="bg_lower_label"><?php esc_html_e( 'A. Ice Cube Settings', 'weather-effect' ); ?></label>
			<?php
			if ( isset( $weather_effect_settings['ice_cube'] ) ) {
				$ice_cube = $weather_effect_settings['ice_cube'];
			} else {
				$ice_cube = 'true';
			}
			?>
			<p class="switch-field padding_settings">
				<input class="widefat" id="ice_cube1" name="ice_cube" type="radio" value="true" 
				<?php
				if ( $ice_cube == 'true' ) {
					echo 'checked=checked';}
				?>
				>
				<label for="ice_cube1"><?php esc_html_e( 'Round', 'weather-effect' ); ?></label>
				<input class="widefat" id="ice_cube2" name="ice_cube" type="radio" value="false" 
				<?php
				if ( $ice_cube == 'false' ) {
					echo 'checked=checked';}
				?>
				>
				<label for="ice_cube2"><?php esc_html_e( 'Square', 'weather-effect' ); ?></label>
			</p>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="row">
					<label class="bg_lower_label"><?php esc_html_e( 'B. Minimum Snow Size On page', 'weather-effect' ); ?></label>
					<?php
					if ( isset( $weather_effect_settings['min_size_christmas'] ) ) {
						$min_size_christmas = $weather_effect_settings['min_size_christmas'];
					} else {
						$min_size_christmas = 1;
					}
					?>
					<p class="range-slider padding_settings">
						<input id="min_size_christmas" name="min_size_christmas" class="range-slider__range" type="range" value="<?php echo esc_attr( $min_size_christmas ); ?>" min="1" step="1" max="5">
						<span class="range-slider__value">0</span>
					</p>
				</div>
				<div class="row">
					<label class="bg_lower_label"><?php esc_html_e( 'C. Maximum Snow Size On page', 'weather-effect' ); ?></label>
					<?php
					if ( isset( $weather_effect_settings['max_size_christmas'] ) ) {
						$max_size_christmas = $weather_effect_settings['max_size_christmas'];
					} else {
						$max_size_christmas = 5;
					}
					?>
					<p class="range-slider padding_settings">
						<input id="max_size_christmas" name="max_size_christmas" class="range-slider__range" type="range" value="<?php echo esc_attr( $max_size_christmas ); ?>" min="10" step="1" max="100">
						<span class="range-slider__value">0</span>
					</p>
				</div>
			</div>
			<div class="col-md-6">
				<div class="row">
					<label class="bg_lower_label"><?php esc_html_e( 'D. Snow Flakes Count', 'weather-effect' ); ?></label>
					<?php
					if ( isset( $weather_effect_settings['flake_christmas'] ) ) {
						$flake_christmas = $weather_effect_settings['flake_christmas'];
					} else {
						$flake_christmas = 5;
					}
					?>
					<p class="range-slider padding_settings">
						<input id="flake_christmas" name="flake_christmas" class="range-slider__range" type="range" value="<?php echo esc_attr( $flake_christmas ); ?>" min="1" step="1" max="100">
						<span class="range-slider__value">0</span>
					</p>
				</div>
			</div>
		</div>
	</div>

	<!--Winter Falling Settings -->
	<div id="snow_falling">
		<p class="bg-title"><?php esc_html_e( '2. Falling Snows Flakes Settings', 'weather-effect' ); ?></p>
		<div class="row">
			<div class="col-md-6">
				<div class="row">
					<label class="bg_lower_label"><?php esc_html_e( 'A. Minimum Snows Flakes Size On page', 'weather-effect' ); ?></label>
					<?php
					if ( isset( $weather_effect_settings['min_size_falling'] ) ) {
						$min_size_falling = $weather_effect_settings['min_size_falling'];
					} else {
						$min_size_falling = 3;
					}
					?>
					<p class="range-slider padding_settings">
						<input id="min_size_falling" name="min_size_falling" class="range-slider__range" type="range" value="<?php echo esc_attr( $min_size_falling ); ?>" min="1" step="1" max="5">
						<span class="range-slider__value">0</span>
					</p>
				</div>
				<div class="row">
					<label class="bg_lower_label"><?php esc_html_e( 'B. Maximum Snows Flakes Size On page', 'weather-effect' ); ?></label>
					<?php
					if ( isset( $weather_effect_settings['max_size_falling'] ) ) {
						$max_size_falling = $weather_effect_settings['max_size_falling'];
					} else {
						$max_size_falling = 30;
					}
					?>
					<p class="range-slider padding_settings">
						<input id="max_size_falling" name="max_size_falling" class="range-slider__range" type="range" value="<?php echo esc_attr( $max_size_falling ); ?>" min="10" step="1" max="50">
						<span class="range-slider__value">0</span>
					</p>
				</div>
			</div>
			<div class="col-md-6">
				<div class="row">
					<label class="bg_lower_label"><?php esc_html_e( 'C. Snows Flakes Falls Time Duration', 'weather-effect' ); ?></label>
					<?php
					if ( isset( $weather_effect_settings['snow_falling_time'] ) ) {
						$snow_falling_time = $weather_effect_settings['snow_falling_time'];
					} else {
						$snow_falling_time = 500;
					}
					?>
					<p class="range-slider padding_settings">
						<input id="snow_falling_time" name="snow_falling_time" class="range-slider__range" type="range" value="<?php echo esc_attr( $snow_falling_time ); ?>" min="300" step="10" max="1000">
						<span class="range-slider__value">0</span>
					</p>
				</div>
				<div class="row">
					<label class="bg_lower_label"><?php esc_html_e( 'D. Select Flakes Color', 'weather-effect' ); ?></label>
					<?php
					if ( isset( $weather_effect_settings['snow_falling_color'] ) ) {
						$snow_falling_color = $weather_effect_settings['snow_falling_color'];
					} else {
						$snow_falling_color = 'FFFFFF';
					}
					?>
					<input type="text" class="form-control" id="snow_falling_color" name="snow_falling_color" placeholder="choose form color" value="<?php echo esc_html( $snow_falling_color ); ?>">
				</div>
			</div>
		</div>
	</div>
</div>
<style>
	.we-flake {
		color: <?php echo esc_html( $snow_falling_color ); ?> !important;
	}
</style>
<script>
	<?php if ( $weather_occasion == 'winter_check' ) { ?>
		<?php if ( $snow_types == 'winter_snow' ) { ?>
			jQuery(document).ready(function(){
				jQuery(document).snowfall({deviceorientation : false,
					round : <?php echo esc_js( $ice_cube ); ?>,
					minSize: <?php echo esc_js( $min_size_christmas ); ?>,
					maxSize: <?php echo esc_js( $max_size_christmas ); ?>,
					flakeCount : <?php echo esc_js( $flake_christmas ); ?>
				});
			});
		<?php } ?>

		<?php if ( $snow_types == 'falling_snow' ) { ?>
			jQuery(document).ready( function(){
				jQuery.fn.snow({
					minSize:<?php echo esc_js( $min_size_falling ); ?>,
					maxSize:<?php echo esc_js( $max_size_falling ); ?>,
					newOn:<?php echo esc_js( $snow_falling_time ); ?>,
					flakeColor:<?php echo wp_json_encode( $snow_falling_color ); ?>
				});
			});
		<?php } ?>
	<?php } ?>

	(function( jQuery ) {
		jQuery(function() {
			jQuery('#snow_falling_color').wpColorPicker();
		});
	})( jQuery );

	var snow_types = jQuery('input[name="snow_types"]:checked').val();
	if(snow_types == "winter_snow") {
		jQuery('#snow_winter').show();
		jQuery('#snow_falling').hide();
	}
	if(snow_types == "falling_snow") {
		jQuery('#snow_winter').hide();
		jQuery('#snow_falling').show();
	}

	jQuery(document).ready(function() {
		jQuery('input[name="snow_types"]').change(function(){
			var snow_types = jQuery('input[name="snow_types"]:checked').val();
			if(snow_types == "winter_snow") {
				jQuery('#snow_winter').show();
				jQuery('#snow_falling').hide();
			}
			if(snow_types == "falling_snow") {
				jQuery('#snow_winter').hide();
				jQuery('#snow_falling').show();
			}
		})
	});
</script>
