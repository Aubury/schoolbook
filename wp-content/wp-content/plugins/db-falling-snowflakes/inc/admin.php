<?php // THE SETTINGS PAGE

	if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	$baseObj = new DB_SNOW_Settings();
	$d = $baseObj -> thisdir(); // domain for translate.wordpress.org

	$db_snow_start_day    = (int) get_option( 'db_snow_start_day' );
	$db_snow_start_month  = (int) get_option( 'db_snow_start_month' );
	$db_snow_finish_day   = (int) get_option( 'db_snow_finish_day' );
	$db_snow_finish_month = (int) get_option( 'db_snow_finish_month' );

	$db_snow_max_number_mobile = (int) get_option( 'db_snow_max_number_mobile' );
	$db_snow_max_number_tablet = (int) get_option( 'db_snow_max_number_tablet' );
	$db_snow_max_number = (int) get_option( 'db_snow_max_number' );

	$db_snow_min_size_mobile = (int) get_option( 'db_snow_min_size_mobile' );
	$db_snow_min_size_tablet = (int) get_option( 'db_snow_min_size_tablet' );
	$db_snow_min_size = (int) get_option( 'db_snow_min_size' );

	$db_snow_max_size_mobile = (int) get_option( 'db_snow_max_size_mobile' );
	$db_snow_max_size_tablet = (int) get_option( 'db_snow_max_size_tablet' );
	$db_snow_max_size = (int) get_option( 'db_snow_max_size' );

	$db_snow_speed_mobile = (float) get_option( 'db_snow_speed_mobile' );
	$db_snow_speed_tablet = (float) get_option( 'db_snow_speed_tablet' );
	$db_snow_speed = (float) get_option( 'db_snow_speed' );

	$db_snow_colors = array(
		'Colors',
		sanitize_hex_color( get_option('db_snow_color_1') ),
		sanitize_hex_color( get_option('db_snow_color_2') ),
		sanitize_hex_color( get_option('db_snow_color_3') ),
		sanitize_hex_color( get_option('db_snow_color_4') ),
		sanitize_hex_color( get_option('db_snow_color_5') )
	);

	$db_snow_opacity_mobile = (float) get_option( 'db_snow_opacity_mobile' );
	$db_snow_opacity_tablet = (float) get_option( 'db_snow_opacity_tablet' );
	$db_snow_opacity = (float) get_option( 'db_snow_opacity' );

	if ( isset( $_POST[ 'submit' ] ) && 
		 isset( $_POST[ $d . '_nonce' ] ) &&
		 wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $d . '_nonce' ] ) ), sanitize_text_field( $d ) ) )
	{

		if ( function_exists( 'current_user_can' ) &&
			 !current_user_can( 'manage_options' ) )
				die( esc_html_e( 'Error: You do not have the permission to update the value', 'db-falling-snowflakes' ) );

		if ( isset( $_POST[ 'start_day' ] ) )
			$db_snow_start_day = (int) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'start_day' ] ) ) );
		if ( isset( $_POST[ 'start_month' ] ) )
			$db_snow_start_month = (int) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'start_month' ] ) ) );
		if ( isset( $_POST[ 'finish_day' ] ) )
			$db_snow_finish_day = (int) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'finish_day' ] ) ) );
		if ( isset( $_POST[ 'finish_month' ] ) )
			$db_snow_finish_month = (int) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'finish_month' ] ) ) );

		if ( isset( $_POST[ 'max_number_mobile' ] ) )
			$db_snow_max_number_mobile = (int) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'max_number_mobile' ] ) ) );
		if ( isset( $_POST[ 'max_number_tablet' ] ) )
			$db_snow_max_number_tablet = (int) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'max_number_tablet' ] ) ) );
		if ( isset( $_POST[ 'max_number' ] ) )
			$db_snow_max_number = (int) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'max_number' ] ) ) );

		if ( isset( $_POST[ 'min_size_mobile' ] ) )
			$db_snow_min_size_mobile = (int) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'min_size_mobile' ] ) ) );
		if ( isset( $_POST[ 'min_size_tablet' ] ) )
			$db_snow_min_size_tablet = (int) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'min_size_tablet' ] ) ) );
		if ( isset( $_POST[ 'min_size' ] ) )
			$db_snow_min_size = (int) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'min_size' ] ) ) );

		if ( isset( $_POST[ 'max_size_mobile' ] ) )
			$db_snow_max_size_mobile = (int) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'max_size_mobile' ] ) ) );
		if ( isset( $_POST[ 'max_size_tablet' ] ) )
			$db_snow_max_size_tablet = (int) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'max_size_tablet' ] ) ) );
		if ( isset( $_POST[ 'max_size' ] ) )
			$db_snow_max_size = (int) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'max_size' ] ) ) );

		if ( isset( $_POST[ 'speed_mobile' ] ) )
			$db_snow_speed_mobile = (float) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'speed_mobile' ] ) ) );
		if ( isset( $_POST[ 'speed_tablet' ] ) )
			$db_snow_speed_tablet = (float) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'speed_tablet' ] ) ) );
		if ( isset( $_POST[ 'speed' ] ) )
			$db_snow_speed = (float) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'speed' ] ) ) );

		if ( isset( $_POST[ 'opacity_mobile' ] ) )
			$db_snow_opacity_mobile = (float) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'opacity_mobile' ] ) ) );
		if ( isset( $_POST[ 'opacity_tablet' ] ) )
			$db_snow_opacity_tablet = (float) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'opacity_tablet' ] ) ) );
		if ( isset( $_POST[ 'opacity' ] ) )
			$db_snow_opacity = (float) esc_html( sanitize_text_field( wp_unslash( $_POST[ 'opacity' ] ) ) );

		// Start on
		if ( $db_snow_start_day > 0 && $db_snow_start_month > 0 )
		{
			update_option( 'db_snow_start_day', $db_snow_start_day );
			update_option( 'db_snow_start_month', $db_snow_start_month );
		}

		// Stop on
		if ( $db_snow_finish_day > 0 && $db_snow_finish_month > 0 )
		{
			update_option( 'db_snow_finish_day', $db_snow_finish_day );
			update_option( 'db_snow_finish_month', $db_snow_finish_month );
		}

		// Max Number
		if ( $db_snow_max_number_mobile >= 0 )
			update_option( 'db_snow_max_number_mobile', $db_snow_max_number_mobile );
		if ( $db_snow_max_number_tablet >= 0 )
			update_option( 'db_snow_max_number_tablet', $db_snow_max_number_tablet );
		if ( $db_snow_max_number >= 0 )
			update_option( 'db_snow_max_number', $db_snow_max_number );

		// Min and Max Size
		if ( $db_snow_min_size_mobile >= 0 )
			update_option( 'db_snow_min_size_mobile', $db_snow_min_size_mobile );
		if ( $db_snow_min_size_tablet >= 0 )
			update_option( 'db_snow_min_size_tablet', $db_snow_min_size_tablet );
		if ( $db_snow_min_size >= 0 )
			update_option( 'db_snow_min_size', $db_snow_min_size );

		if ( $db_snow_max_size_mobile >= 0 )
			update_option( 'db_snow_max_size_mobile', $db_snow_max_size_mobile );
		if ( $db_snow_max_size_tablet >= 0 )
			update_option( 'db_snow_max_size_tablet', $db_snow_max_size_tablet );
		if ( $db_snow_max_size >= 0 )
			update_option( 'db_snow_max_size', $db_snow_max_size );

		if ( $db_snow_min_size_mobile >= 0 && $db_snow_max_size_mobile >= 0 && $db_snow_min_size_mobile > $db_snow_max_size_mobile )
		{
			update_option( 'db_snow_min_size_mobile', $db_snow_max_size_mobile );
			update_option( 'db_snow_max_size_mobile', $db_snow_min_size_mobile );
		}
		if ( $db_snow_min_size_tablet >= 0 && $db_snow_max_size_tablet >= 0 && $db_snow_min_size_tablet > $db_snow_max_size_tablet )
		{
			update_option( 'db_snow_min_size_tablet', $db_snow_max_size_tablet );
			update_option( 'db_snow_max_size_tablet', $db_snow_min_size_tablet );
		}
		if ( $db_snow_min_size >= 0 && $db_snow_max_size >= 0 && $db_snow_min_size > $db_snow_max_size )
		{
			update_option( 'db_snow_min_size', $db_snow_max_size );
			update_option( 'db_snow_max_size', $db_snow_min_size );
		}

		// Speed
		if ( $db_snow_speed_mobile >= 0 )
			update_option( 'db_snow_speed_mobile', $db_snow_speed_mobile );
		if ( $db_snow_speed_tablet >= 0 )
			update_option( 'db_snow_speed_tablet', $db_snow_speed_tablet );
		if ( $db_snow_speed >= 0 )
			update_option( 'db_snow_speed', $db_snow_speed );

		// Colors
		for ( $i = 1; $i <= 5; $i++ )
		{
			if ( isset( $_POST[ "color_{$i}" ] ) ) {
				$db_snow_colors[ $i ] = (string) esc_html( sanitize_hex_color( wp_unslash( $_POST[ "color_{$i}" ] ) ) );
				update_option( 'db_snow_color_' . $i , $db_snow_colors[ $i ] );
			}
		}

		// Opacity
		if ( $db_snow_opacity_mobile >= 0 && $db_snow_opacity_mobile <= 1 )
			update_option( 'db_snow_opacity_mobile', $db_snow_opacity_mobile );
		if ( $db_snow_opacity_tablet >= 0 && $db_snow_opacity_tablet <= 1 )
			update_option( 'db_snow_opacity_tablet', $db_snow_opacity_tablet );
		if ( $db_snow_opacity >= 0 && $db_snow_opacity <= 1 )
			update_option( 'db_snow_opacity', $db_snow_opacity );

	}

	$db_months = [
		esc_html__( 'Month', 'db-falling-snowflakes' ),
		esc_html__( 'January', 'db-falling-snowflakes' ),
		esc_html__( 'February', 'db-falling-snowflakes' ),
		esc_html__( 'March', 'db-falling-snowflakes' ),
		esc_html__( 'April', 'db-falling-snowflakes' ),
		esc_html__( 'May', 'db-falling-snowflakes' ),
		esc_html__( 'June', 'db-falling-snowflakes' ),
		esc_html__( 'July', 'db-falling-snowflakes' ),
		esc_html__( 'August', 'db-falling-snowflakes' ),
		esc_html__( 'September', 'db-falling-snowflakes' ),
		esc_html__( 'October', 'db-falling-snowflakes' ),
		esc_html__( 'November', 'db-falling-snowflakes' ),
		esc_html__( 'December', 'db-falling-snowflakes' ),
	];

?>
<div class='wrap db-snow-admin'>

	<h1><?php esc_html_e( 'DB Snow Flakes', 'db-falling-snowflakes' ) ?></h1>

	<div class="db-snow-container">

		<div class="db-snow-row">

			<div class="db-snow-col-8">

				<h2><?php esc_html_e( 'Settings', 'db-falling-snowflakes' ) ?></h2>

				<form name="db-snow" method="post" action="<?php if ( isset( $_SERVER[ 'PHP_SELF' ] ) ) echo esc_html( sanitize_text_field( wp_unslash( $_SERVER[ 'PHP_SELF' ] ) ) ) ?>?page=<?php echo esc_html( sanitize_text_field( $d ) ) ?>&amp;updated=true">

					<table class="form-table db-snow-table" width="100%">
						<tr valign="top">
							<th scope="rowgroup" rowspan="2" width="30%" class="db-snow-bg-highlight db-snow-table-border-bottom-left-radius">
								<?php esc_html_e( 'Period of activity' , 'db-falling-snowflakes' ) ?>
								<div class="db-snow-field-description"><?php esc_html_e( 'Set when the plugin is active' , 'db-falling-snowflakes' ) ?></div>
							</th>
							<th scope="row" width="30%">
								<?php esc_html_e( 'Start on' , 'db-falling-snowflakes' ) ?>
							</th>
							<td width="20%">
								<select type="text" name="start_day" id="db_snow_start_day">
									<option value="0" disabled><?php esc_html_e( 'Day' , 'db-falling-snowflakes' ) ?></option>
								<?php
									for ( $i = 1; $i <= 31 ; $i++ )
									{
								?>
									<option value="<?php echo esc_html( sanitize_text_field( $i ) ) ?>" <?php selected( $db_snow_start_day, $i ); ?>><?php echo esc_html( sanitize_text_field( $i ) ) ?></option>
								<?php
									}
								?>
								</select>
							</td>
							<td width="20%">
								<select type="text" name="start_month" id="db_snow_start_month">
									<?php
										foreach( $db_months as $db_month_id => $db_month ) :
										?>
											<option value="<?php echo esc_html( sanitize_text_field( $db_month_id ) ) ?>" <?php
												if ( $db_month_id === 0 ) echo esc_html( 'disabled' ); 
												else selected( $db_snow_start_month, $db_month_id );
											?>><?php echo esc_html( sanitize_text_field( $db_month ) ) ?></option>
										<?php
										endforeach;
									?>
								</select>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="db-snow-after-rowspan">
								<?php esc_html_e( 'Stop on' , 'db-falling-snowflakes' ) ?>
							</th>
							<td>
								<select type="text" name="finish_day" id="db_snow_finish_day">
									<option value="0" disabled><?php esc_html_e( 'Day' , 'db-falling-snowflakes' ) ?></option>
								<?php
									for ( $i = 1; $i <= 31 ; $i++ )
									{
								?>
									<option value="<?php echo esc_html( sanitize_text_field( $i ) ) ?>" <?php selected( $db_snow_finish_day, $i ); ?>><?php echo esc_html( sanitize_text_field( $i ) ) ?></option>
								<?php
									}
								?>
								</select>
							</td>
							<td>
								<select type="text" name="finish_month" id="db_snow_finish_month">
									<?php
										foreach( $db_months as $db_month_id => $db_month ) :
										?>
											<option value="<?php echo esc_html( sanitize_text_field( $db_month_id ) ) ?>" <?php
												if ( $db_month_id === 0 ) echo esc_html( 'disabled' ); 
												else selected( $db_snow_finish_month, $db_month_id );
											?>><?php echo esc_html( sanitize_text_field( $db_month ) ) ?></option>
										<?php
										endforeach;
									?>
								</select>
							</td>
						</tr>
					</table>
					<table class="form-table db-snow-table" width="100%">
						<tr valign="top">
							<th scope="rowgroup" rowspan="5" width="30%" class="db-snow-bg-highlight db-snow-table-border-bottom-left-radius">
								<?php esc_html_e( 'Colors' , 'db-falling-snowflakes' ) ?>
								<div class="db-snow-field-description"><?php esc_html_e( 'Choose 5 colors of the snowflakes. The color of every other snowflake will be chosen randomly from these five ones' , 'db-falling-snowflakes' ) ?></div>
							</th>
							<th scope="row" width="30%">
								<?php esc_html_e( 'Color' , 'db-falling-snowflakes' ) ?> 1
							</th>
							<td id="db_snow_color_inner" width="40%">
								<input type="text" name="color_1" id="db_snow_color_1" class="db-snow-color"
										size="7" value="<?php echo esc_html( sanitize_hex_color( $db_snow_colors[1] ) ) ?>" data-default-color="#b9e0f5" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="db-snow-after-rowspan">
								<?php esc_html_e( 'Color' , 'db-falling-snowflakes' ) ?> 2
							</th>
							<td id="db_snow_color_inner">
								<input type="text" name="color_2" id="db_snow_color_2" class="db-snow-color"
										size="7" value="<?php echo esc_html( sanitize_hex_color( $db_snow_colors[2] ) ) ?>" data-default-color="#7ec8ff" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="db-snow-after-rowspan">
								<?php esc_html_e( 'Color' , 'db-falling-snowflakes' ) ?> 3
							</th>
							<td id="db_snow_color_inner">
								<input type="text" name="color_3" id="db_snow_color_3" class="db-snow-color"
										size="7" value="<?php echo esc_html( sanitize_hex_color( $db_snow_colors[3] ) ) ?>" data-default-color="#7eb0ff" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="db-snow-after-rowspan">
								<?php esc_html_e( 'Color' , 'db-falling-snowflakes' ) ?> 4
							</th>
							<td id="db_snow_color_inner">
								<input type="text" name="color_4" id="db_snow_color_4" class="db-snow-color"
										size="7" value="<?php echo esc_html( sanitize_hex_color( $db_snow_colors[4] ) ) ?>" data-default-color="#8ab4ff" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row" class="db-snow-after-rowspan">
								<?php esc_html_e( 'Color' , 'db-falling-snowflakes' ) ?> 5
							</th>
							<td id="db_snow_color_inner">
								<input type="text" name="color_5" id="db_snow_color_5" class="db-snow-color"
										size="7" value="<?php echo esc_html( sanitize_hex_color( $db_snow_colors[5] ) ) ?>" data-default-color="#afd0f5" />
							</td>
						</tr>
					</table>
					<table class="form-table db-snow-table" width="100%">
						<tr valign="top">
							<th scope="rowgroup" width="25%" class="db-snow-bg-highlight">
								<?php esc_html_e( 'Parameters' , 'db-falling-snowflakes' ) ?>
								<div class="db-snow-field-description"><?php esc_html_e( 'Customization of the performance of the snowflakes' , 'db-falling-snowflakes' ) ?></div>
							</th>
							<th width="25%" class="db-snow-bg-highlight">
								<?php
									$html = $baseObj->icon( $d . '/img/mobile.svg', 18, 18, esc_html__( 'Mobile' , 'db-falling-snowflakes' ), '&le;576px', 'db-snow-block-icon-top db-snow-block-icon-center' );
									echo wp_kses_post( $html );
								?>
							</th>
							<th width="25%" class="db-snow-bg-highlight">
								<?php
									$html = $baseObj->icon( $d . '/img/tablet.svg', 16, 16, esc_html__( 'Tablet' , 'db-falling-snowflakes' ), '&le;992px', 'db-snow-block-icon-top db-snow-block-icon-center' );
									echo wp_kses_post( $html );
								?>
							</th>
							<th width="25%" class="db-snow-bg-highlight">
								<?php
									$html = $baseObj->icon( $d . '/img/desktop.svg', 16, 16, esc_html__( 'Desktop' , 'db-falling-snowflakes' ), '&gt;992px', 'db-snow-block-icon-top db-snow-block-icon-center' );
									echo wp_kses_post( $html );
								?>
							</th>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Maximum number of snowflakes' , 'db-falling-snowflakes' ) ?>
							</th>
							<td>
								<input type="text" name="max_number_mobile" id="db_snow_max_number_mobile"
										size="5" value="<?php echo esc_html( sanitize_text_field( $db_snow_max_number_mobile ) ) ?>" />
							</td>
							<td>
								<input type="text" name="max_number_tablet" id="db_snow_max_number_tablet"
										size="5" value="<?php echo esc_html( sanitize_text_field( $db_snow_max_number_tablet ) ) ?>" />
							</td>
							<td>
								<input type="text" name="max_number" id="db_snow_max_number"
										size="5" value="<?php echo esc_html( sanitize_text_field( $db_snow_max_number ) ) ?>" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Minimum Size' , 'db-falling-snowflakes' ) ?>
							</th>
							<td>
								<input type="text" name="min_size_mobile" id="db_snow_min_size_mobile"
										size="5" value="<?php echo esc_html( sanitize_text_field( $db_snow_min_size_mobile ) ) ?>" />
							</td>
							<td>
								<input type="text" name="min_size_tablet" id="db_snow_min_size_tablet"
										size="5" value="<?php echo esc_html( sanitize_text_field( $db_snow_min_size_tablet ) ) ?>" />
							</td>
							<td>
								<input type="text" name="min_size" id="db_snow_min_size"
										size="5" value="<?php echo esc_html( sanitize_text_field( $db_snow_min_size ) ) ?>" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Maximum Size' , 'db-falling-snowflakes' ) ?>
							</th>
							<td>
								<input type="text" name="max_size_mobile" id="db_snow_max_size_mobile"
										size="5" value="<?php echo esc_html( sanitize_text_field( $db_snow_max_size_mobile ) ) ?>" />
							</td>
							<td>
								<input type="text" name="max_size_tablet" id="db_snow_max_size_tablet"
										size="5" value="<?php echo esc_html( sanitize_text_field( $db_snow_max_size_tablet ) ) ?>" />
							</td>
							<td>
								<input type="text" name="max_size" id="db_snow_max_size"
										size="5" value="<?php echo esc_html( sanitize_text_field( $db_snow_max_size ) ) ?>" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Speed' , 'db-falling-snowflakes' ) ?>
							</th>
							<td scope="colgroup">
								<div id="db_snow_speed_value_mobile"></div>
								<div class="db-snow-input-range">
									<input type="range" name="speed_mobile" id="db_snow_speed_mobile"
											size="5" min="0.1" max="2" step="0.01" value="<?php echo esc_html( sanitize_text_field( $db_snow_speed_mobile ) ) ?>" />
								</div>
							</td>
							<td scope="colgroup">
								<div id="db_snow_speed_value_tablet"></div>
								<div class="db-snow-input-range">
									<input type="range" name="speed_tablet" id="db_snow_speed_tablet"
											size="5" min="0.1" max="2" step="0.01" value="<?php echo esc_html( sanitize_text_field( $db_snow_speed_tablet ) ) ?>" />
								</div>
							</td>
							<td scope="colgroup">
								<div id="db_snow_speed_value"></div>
								<div class="db-snow-input-range">
									<input type="range" name="speed" id="db_snow_speed"
											size="5" min="0.1" max="2" step="0.01" value="<?php echo esc_html( sanitize_text_field( $db_snow_speed ) ) ?>" />
								</div>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<?php esc_html_e( 'Opacity' , 'db-falling-snowflakes' ) ?>
							</th>
							<td scope="colgroup">
								<div id="db_snow_opacity_value_mobile"></div>
								<div class="db-snow-input-range">
									<input type="range" name="opacity_mobile" id="db_snow_opacity_mobile"
											size="5" min="0" max="1" step="0.01" value="<?php echo esc_html( sanitize_text_field( $db_snow_opacity_mobile ) ) ?>" />
								</div>
							</td>
							<td scope="colgroup">
								<div id="db_snow_opacity_value_tablet"></div>
								<div class="db-snow-input-range">
									<input type="range" name="opacity_tablet" id="db_snow_opacity_tablet"
											size="5" min="0" max="1" step="0.01" value="<?php echo esc_html( sanitize_text_field( $db_snow_opacity_tablet ) ) ?>" />
								</div>
							</td>
							<td scope="colgroup">
								<div id="db_snow_opacity_value"></div>
								<div class="db-snow-input-range">
									<input type="range" name="opacity" id="db_snow_opacity"
											size="5" min="0" max="1" step="0.01" value="<?php echo esc_html( sanitize_text_field( $db_snow_opacity ) ) ?>" />
								</div>
							</td>
						</tr>
					</table>

					<input type="hidden" name="action" value="update" />

					<?php $nonce = wp_create_nonce( $d ); ?>

					<input type="hidden" name="<?php echo esc_html( sanitize_text_field( $d ) ) ?>_nonce" value="<?php echo esc_html( sanitize_text_field( $nonce ) ) ?>" />

					<?php submit_button(); ?>

				</form>

			</div>

			<div class="db-snow-col-4">

				<div class="db-snow-logo">

					<a href="https://bisteinoff.com/" target="_blank"><img src="<?php echo esc_url( sanitize_text_field( plugins_url( $d . '/img/logo.png' ) ) ) ?>" width="200" height="42" alt="Bisteinoff Web Agency" title="Bisteinoff Web Agency" /></a>

				</div>

				<h2><?php esc_html_e( 'About', 'db-falling-snowflakes' ) ?></h2>

				<div class="db-snow-description">

					<p><?php esc_html_e( "The plugin generates snowflakes falling down on the foreground of the pages of the website.", 'db-falling-snowflakes' ) ?></p>

					<p><?php esc_html_e( "Once installed it will run the script only in the period of time when you want. A lot of options to customize snowflakes and their actions.", 'db-falling-snowflakes' ) ?></p>

					<p>&nbsp;</p>

					<p>&copy; <?php echo esc_html( gmdate( "Y" ) ) ?> <a href="https://bisteinoff.com/?utm_source=copyright" target="_blank">Bisteinoff Web Agency</a></p>

				</div>

			</div>

		</div>

	</div>

</div>