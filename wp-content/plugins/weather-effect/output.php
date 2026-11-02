<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// $weather_effect_settings is provided by the calling function scope.

if ( isset( $weather_effect_settings['weather_occasion'] ) ) {
	$weather_occasion = $weather_effect_settings['weather_occasion'];
} else {
	$weather_occasion = '';
}

// ---- Season-agnostic variable reads ----

// Spring
$spring_min_size_leaf = isset( $weather_effect_settings['spring_min_size_leaf'] ) ? $weather_effect_settings['spring_min_size_leaf'] : '';
$spring_max_size_leaf = isset( $weather_effect_settings['spring_max_size_leaf'] ) ? $weather_effect_settings['spring_max_size_leaf'] : '';
$spring_flakes_leaf   = isset( $weather_effect_settings['spring_flakes_leaf'] ) ? $weather_effect_settings['spring_flakes_leaf'] : '';
$spring_speed         = isset( $weather_effect_settings['spring_speed'] ) ? $weather_effect_settings['spring_speed'] : '';

// Summer
$summer_min_size_leaf = isset( $weather_effect_settings['summer_min_size_leaf'] ) ? $weather_effect_settings['summer_min_size_leaf'] : '';
$summer_max_size_leaf = isset( $weather_effect_settings['summer_max_size_leaf'] ) ? $weather_effect_settings['summer_max_size_leaf'] : '';
$summer_flakes_leaf   = isset( $weather_effect_settings['summer_flakes_leaf'] ) ? $weather_effect_settings['summer_flakes_leaf'] : '';
$summer_speed         = isset( $weather_effect_settings['summer_speed'] ) ? $weather_effect_settings['summer_speed'] : '';

// Halloween
$halloween_min_size_leaf = isset( $weather_effect_settings['halloween_min_size_leaf'] ) ? $weather_effect_settings['halloween_min_size_leaf'] : '';
$halloween_max_size_leaf = isset( $weather_effect_settings['halloween_max_size_leaf'] ) ? $weather_effect_settings['halloween_max_size_leaf'] : '';
$halloween_flakes_leaf   = isset( $weather_effect_settings['halloween_flakes_leaf'] ) ? $weather_effect_settings['halloween_flakes_leaf'] : '';
$halloween_speed         = isset( $weather_effect_settings['halloween_speed'] ) ? $weather_effect_settings['halloween_speed'] : '';

// Rain
$rain_min_size_leaf = isset( $weather_effect_settings['rain_min_size_leaf'] ) ? $weather_effect_settings['rain_min_size_leaf'] : '';
$rain_max_size_leaf = isset( $weather_effect_settings['rain_max_size_leaf'] ) ? $weather_effect_settings['rain_max_size_leaf'] : '';
$rain_flakes_leaf   = isset( $weather_effect_settings['rain_flakes_leaf'] ) ? $weather_effect_settings['rain_flakes_leaf'] : '';
$rain_speed         = isset( $weather_effect_settings['rain_speed'] ) ? $weather_effect_settings['rain_speed'] : '';

// Thanksgiving
$thanksgiving_min_size_leaf = isset( $weather_effect_settings['thanksgiving_min_size_leaf'] ) ? $weather_effect_settings['thanksgiving_min_size_leaf'] : '';
$thanksgiving_max_size_leaf = isset( $weather_effect_settings['thanksgiving_max_size_leaf'] ) ? $weather_effect_settings['thanksgiving_max_size_leaf'] : '';
$thanksgiving_flakes_leaf   = isset( $weather_effect_settings['thanksgiving_flakes_leaf'] ) ? $weather_effect_settings['thanksgiving_flakes_leaf'] : '';
$thanksgiving_speed         = isset( $weather_effect_settings['thanksgiving_speed'] ) ? $weather_effect_settings['thanksgiving_speed'] : '';

// Valentine
$valentine_min_size_leaf = isset( $weather_effect_settings['valentine_min_size_leaf'] ) ? $weather_effect_settings['valentine_min_size_leaf'] : '';
$valentine_max_size_leaf = isset( $weather_effect_settings['valentine_max_size_leaf'] ) ? $weather_effect_settings['valentine_max_size_leaf'] : '';
$valentine_flakes_leaf   = isset( $weather_effect_settings['valentine_flakes_leaf'] ) ? $weather_effect_settings['valentine_flakes_leaf'] : '';
$valentine_speed         = isset( $weather_effect_settings['valentine_speed'] ) ? $weather_effect_settings['valentine_speed'] : '';

// New Year
$newyear_min_size_leaf = isset( $weather_effect_settings['newyear_min_size_leaf'] ) ? $weather_effect_settings['newyear_min_size_leaf'] : '';
$newyear_max_size_leaf = isset( $weather_effect_settings['newyear_max_size_leaf'] ) ? $weather_effect_settings['newyear_max_size_leaf'] : '';
$newyear_flakes_leaf   = isset( $weather_effect_settings['newyear_flakes_leaf'] ) ? $weather_effect_settings['newyear_flakes_leaf'] : '';
$newyear_speed         = isset( $weather_effect_settings['newyear_speed'] ) ? $weather_effect_settings['newyear_speed'] : '';
?>
<?php if ( $weather_occasion == 'christmas_check' ) : ?>
<?php
$christmas_types         = isset( $weather_effect_settings['christmas_types'] ) ? $weather_effect_settings['christmas_types'] : '';
$ball                    = isset( $weather_effect_settings['ball'] ) ? $weather_effect_settings['ball'] : '';
$bell                    = isset( $weather_effect_settings['bell'] ) ? $weather_effect_settings['bell'] : '';
$candy                   = isset( $weather_effect_settings['candy'] ) ? $weather_effect_settings['candy'] : '';
$gift                    = isset( $weather_effect_settings['gift'] ) ? $weather_effect_settings['gift'] : '';
$snowman                 = isset( $weather_effect_settings['snowman'] ) ? $weather_effect_settings['snowman'] : '';
$snow_flake              = isset( $weather_effect_settings['snow_flake'] ) ? $weather_effect_settings['snow_flake'] : '';
$christmas_min_size_leaf = isset( $weather_effect_settings['christmas_min_size_leaf'] ) ? $weather_effect_settings['christmas_min_size_leaf'] : '';
$christmas_max_size_leaf = isset( $weather_effect_settings['christmas_max_size_leaf'] ) ? $weather_effect_settings['christmas_max_size_leaf'] : '';
$christmas_flakes_leaf   = isset( $weather_effect_settings['christmas_flakes_leaf'] ) ? $weather_effect_settings['christmas_flakes_leaf'] : '';
$christmas_speed         = isset( $weather_effect_settings['christmas_speed'] ) ? $weather_effect_settings['christmas_speed'] : '';
$christmas_ball          = isset( $weather_effect_settings['christmas_ball'] ) ? $weather_effect_settings['christmas_ball'] : '';
$christmas_bell          = isset( $weather_effect_settings['christmas_bell'] ) ? $weather_effect_settings['christmas_bell'] : '';
$christmas_candy         = isset( $weather_effect_settings['christmas_candy'] ) ? $weather_effect_settings['christmas_candy'] : '';
$christmas_gift          = isset( $weather_effect_settings['christmas_gift'] ) ? $weather_effect_settings['christmas_gift'] : '';
$christmas_snowman       = isset( $weather_effect_settings['christmas_snowman'] ) ? $weather_effect_settings['christmas_snowman'] : '';
$christmas_snow_flake    = isset( $weather_effect_settings['christmas_snow_flake'] ) ? $weather_effect_settings['christmas_snow_flake'] : '';
$master_round            = isset( $weather_effect_settings['master_round'] ) ? $weather_effect_settings['master_round'] : '';
$round_type              = isset( $weather_effect_settings['round_type'] ) ? $weather_effect_settings['round_type'] : '';
$min_size_round          = isset( $weather_effect_settings['min_size_round'] ) ? $weather_effect_settings['min_size_round'] : '';
$max_size_round          = isset( $weather_effect_settings['max_size_round'] ) ? $weather_effect_settings['max_size_round'] : '';
$master_shadows          = isset( $weather_effect_settings['master_shadows'] ) ? $weather_effect_settings['master_shadows'] : '';
$shadow_type             = isset( $weather_effect_settings['shadow_type'] ) ? $weather_effect_settings['shadow_type'] : '';
$flakes_shadow           = isset( $weather_effect_settings['flakes_shadow'] ) ? $weather_effect_settings['flakes_shadow'] : '';
$img_base                = plugin_dir_url( __FILE__ ) . 'assets/images/christmas/';
?>
<script>
jQuery(document).ready(function(){
<?php if ( $christmas_types == 'snow_effect' ) : ?>
	<?php if ( $ball == 'ball' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $img_base . $christmas_ball . '.png' ); ?>", minSize: <?php echo esc_js( (int) $christmas_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $christmas_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $christmas_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $christmas_speed ); ?> });
	<?php } ?>
	<?php if ( $bell == 'bell' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $img_base . $christmas_bell . '.png' ); ?>", minSize: <?php echo esc_js( (int) $christmas_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $christmas_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $christmas_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $christmas_speed ); ?> });
	<?php } ?>
	<?php if ( $candy == 'candy' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $img_base . $christmas_candy . '.png' ); ?>", minSize: <?php echo esc_js( (int) $christmas_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $christmas_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $christmas_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $christmas_speed ); ?> });
	<?php } ?>
	<?php if ( $gift == 'gift' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $img_base . $christmas_gift . '.png' ); ?>", minSize: <?php echo esc_js( (int) $christmas_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $christmas_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $christmas_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $christmas_speed ); ?> });
	<?php } ?>
	<?php if ( $snowman == 'snowman' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $img_base . $christmas_snowman . '.png' ); ?>", minSize: <?php echo esc_js( (int) $christmas_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $christmas_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $christmas_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $christmas_speed ); ?> });
	<?php } ?>
	<?php if ( $snow_flake == 'snow_flake' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $img_base . $christmas_snow_flake . '.png' ); ?>", minSize: <?php echo esc_js( (int) $christmas_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $christmas_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $christmas_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $christmas_speed ); ?> });
	<?php } ?>
<?php elseif ( $christmas_types == 'snowfall_master' ) : ?>
	<?php if ( $master_round == 'true' ) { ?>
	jQuery(document).snowfall({ round: <?php echo esc_js( $round_type ); ?>, minSize: <?php echo esc_js( (int) $min_size_round ); ?>, maxSize: <?php echo esc_js( (int) $max_size_round ); ?> });
	<?php } ?>
	<?php if ( $master_shadows == 'true' ) { ?>
	snowFall.snow(document.body, { shadow: <?php echo esc_js( $shadow_type ); ?>, flakeCount: <?php echo esc_js( (int) $flakes_shadow ); ?> });
	<?php } ?>
<?php endif; ?>
});
</script>
<?php endif; ?>

<?php if ( $weather_occasion == 'winter_check' ) : ?>
<?php
$snow_types         = isset( $weather_effect_settings['snow_types'] ) ? $weather_effect_settings['snow_types'] : '';
$ice_cube           = isset( $weather_effect_settings['ice_cube'] ) ? $weather_effect_settings['ice_cube'] : '';
$min_size_christmas = isset( $weather_effect_settings['min_size_christmas'] ) ? $weather_effect_settings['min_size_christmas'] : '';
$max_size_christmas = isset( $weather_effect_settings['max_size_christmas'] ) ? $weather_effect_settings['max_size_christmas'] : '';
$flake_christmas    = isset( $weather_effect_settings['flake_christmas'] ) ? $weather_effect_settings['flake_christmas'] : '';
$min_size_falling   = isset( $weather_effect_settings['min_size_falling'] ) ? $weather_effect_settings['min_size_falling'] : '';
$max_size_falling   = isset( $weather_effect_settings['max_size_falling'] ) ? $weather_effect_settings['max_size_falling'] : '';
$snow_falling_time  = isset( $weather_effect_settings['snow_falling_time'] ) ? $weather_effect_settings['snow_falling_time'] : '';
$snow_falling_color = isset( $weather_effect_settings['snow_falling_color'] ) ? $weather_effect_settings['snow_falling_color'] : '';
?>
<script>
<?php if ( $snow_types == 'winter_snow' ) : ?>
jQuery(document).ready(function(){
	jQuery(document).snowfall({ deviceorientation: false, round: <?php echo esc_js( $ice_cube ); ?>, minSize: <?php echo esc_js( (int) $min_size_christmas ); ?>, maxSize: <?php echo esc_js( (int) $max_size_christmas ); ?>, flakeCount: <?php echo esc_js( (int) $flake_christmas ); ?> });
	setTimeout(function(){ jQuery(document).snowfall("clear"); }, 30000);
});
<?php elseif ( $snow_types == 'falling_snow' ) : ?>
jQuery(document).ready(function(){
	jQuery.fn.snow({ minSize: <?php echo (int) $min_size_falling; ?>, maxSize: <?php echo (int) $max_size_falling; ?>, newOn: <?php echo (int) $snow_falling_time; ?>, flakeColor: <?php echo wp_json_encode( (strpos($snow_falling_color, '#') === 0) ? $snow_falling_color : '#' . $snow_falling_color ); ?>, durationMillis: 30000 });
});
<?php endif; ?>
</script>
<style>.we-flake{ color: <?php echo esc_html( (strpos($snow_falling_color, '#') === 0) ? $snow_falling_color : '#' . $snow_falling_color ); ?> !important; }</style>
<?php endif; ?>

<?php if ( $weather_occasion == 'autumn_check' ) : ?>
<?php
$leaf              = isset( $weather_effect_settings['leaf'] ) ? $weather_effect_settings['leaf'] : '';
$autumn_leaf       = isset( $weather_effect_settings['autumn_leaf'] ) ? $weather_effect_settings['autumn_leaf'] : '';
$autumn_min_size   = isset( $weather_effect_settings['autumn_min_size_leaf'] ) ? $weather_effect_settings['autumn_min_size_leaf'] : '';
$autumn_max_size   = isset( $weather_effect_settings['autumn_max_size_leaf'] ) ? $weather_effect_settings['autumn_max_size_leaf'] : '';
$autumn_flakes     = isset( $weather_effect_settings['autumn_flakes_leaf'] ) ? $weather_effect_settings['autumn_flakes_leaf'] : '';
$autumn_speed      = isset( $weather_effect_settings['autumn_speed'] ) ? $weather_effect_settings['autumn_speed'] : '';
?>
<script>
jQuery(document).ready(function(){
	<?php if ( $leaf == 'leaf' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'assets/images/autumn/' . $autumn_leaf . '.png' ); ?>", minSize: <?php echo esc_js( (int) $autumn_min_size ); ?>, maxSize: <?php echo esc_js( (int) $autumn_max_size ); ?>, flakeCount: <?php echo esc_js( (int) $autumn_flakes ); ?>, maxSpeed: <?php echo esc_js( (int) $autumn_speed ); ?> });
	<?php } ?>
});
</script>
<?php endif; ?>

<?php if ( $weather_occasion == 'spring_check' ) : ?>
<?php
$leaf_spring  = isset( $weather_effect_settings['leaf_spring'] ) ? $weather_effect_settings['leaf_spring'] : '';
$spring_leaf  = isset( $weather_effect_settings['spring_leaf'] ) ? $weather_effect_settings['spring_leaf'] : '';
?>
<script>
jQuery(document).ready(function(){
	<?php if ( $leaf_spring == 'leaf_spring' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'assets/images/spring/' . $spring_leaf . '.png' ); ?>", minSize: <?php echo esc_js( (int) $spring_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $spring_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $spring_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $spring_speed ); ?> });
	<?php } ?>
});
</script>
<?php endif; ?>

<?php if ( $weather_occasion == 'summer_check' ) : ?>
<?php
$drink       = isset( $weather_effect_settings['drink'] ) ? $weather_effect_settings['drink'] : '';
$summer_drink = isset( $weather_effect_settings['summer_drink'] ) ? $weather_effect_settings['summer_drink'] : '';
$sun         = isset( $weather_effect_settings['sun'] ) ? $weather_effect_settings['sun'] : '';
$summer_sun  = isset( $weather_effect_settings['summer_sun'] ) ? $weather_effect_settings['summer_sun'] : '';
?>
<script>
jQuery(document).ready(function(){
	<?php if ( $drink == 'drink' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'assets/images/summer/' . $summer_drink . '.png' ); ?>", minSize: <?php echo esc_js( (int) $summer_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $summer_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $summer_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $summer_speed ); ?> });
	<?php } ?>
	<?php if ( $sun == 'sun' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'assets/images/summer/' . $summer_sun . '.png' ); ?>", minSize: <?php echo esc_js( (int) $summer_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $summer_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $summer_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $summer_speed ); ?> });
	<?php } ?>
});
</script>
<?php endif; ?>

<?php if ( $weather_occasion == 'halloween_check' ) : ?>
<?php
$ghost          = isset( $weather_effect_settings['ghost'] ) ? $weather_effect_settings['ghost'] : '';
$halloween_ghost = isset( $weather_effect_settings['halloween_ghost'] ) ? $weather_effect_settings['halloween_ghost'] : '';
$bats           = isset( $weather_effect_settings['bats'] ) ? $weather_effect_settings['bats'] : '';
$halloween_bats = isset( $weather_effect_settings['halloween_bats'] ) ? $weather_effect_settings['halloween_bats'] : '';
$moon           = isset( $weather_effect_settings['moon'] ) ? $weather_effect_settings['moon'] : '';
$halloween_moon = isset( $weather_effect_settings['halloween_moon'] ) ? $weather_effect_settings['halloween_moon'] : '';
$pumpkin        = isset( $weather_effect_settings['pumpkin'] ) ? $weather_effect_settings['pumpkin'] : '';
$halloween_pumpkin = isset( $weather_effect_settings['halloween_pumpkin'] ) ? $weather_effect_settings['halloween_pumpkin'] : '';
$web            = isset( $weather_effect_settings['web'] ) ? $weather_effect_settings['web'] : '';
$halloween_web  = isset( $weather_effect_settings['halloween_web'] ) ? $weather_effect_settings['halloween_web'] : '';
$witch          = isset( $weather_effect_settings['witch'] ) ? $weather_effect_settings['witch'] : '';
$halloween_witch = isset( $weather_effect_settings['halloween_witch'] ) ? $weather_effect_settings['halloween_witch'] : '';
$h_img_base     = plugin_dir_url( __FILE__ ) . 'assets/images/halloween/';
?>
<script>
jQuery(document).ready(function(){
	<?php if ( $ghost == 'ghost' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $h_img_base . $halloween_ghost . '.png' ); ?>", minSize: <?php echo esc_js( (int) $halloween_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $halloween_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $halloween_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $halloween_speed ); ?> });
	<?php } ?>
	<?php if ( $bats == 'bats' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $h_img_base . $halloween_bats . '.png' ); ?>", minSize: <?php echo esc_js( (int) $halloween_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $halloween_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $halloween_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $halloween_speed ); ?> });
	<?php } ?>
	<?php if ( $moon == 'moon' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $h_img_base . $halloween_moon . '.png' ); ?>", minSize: <?php echo esc_js( (int) $halloween_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $halloween_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $halloween_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $halloween_speed ); ?> });
	<?php } ?>
	<?php if ( $pumpkin == 'pumpkin' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $h_img_base . $halloween_pumpkin . '.png' ); ?>", minSize: <?php echo esc_js( (int) $halloween_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $halloween_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $halloween_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $halloween_speed ); ?> });
	<?php } ?>
	<?php if ( $web == 'web' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $h_img_base . $halloween_web . '.png' ); ?>", minSize: <?php echo esc_js( (int) $halloween_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $halloween_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $halloween_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $halloween_speed ); ?> });
	<?php } ?>
	<?php if ( $witch == 'witch' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $h_img_base . $halloween_witch . '.png' ); ?>", minSize: <?php echo esc_js( (int) $halloween_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $halloween_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $halloween_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $halloween_speed ); ?> });
	<?php } ?>
});
</script>
<?php endif; ?>

<?php if ( $weather_occasion == 'rainy_check' ) : ?>
<?php
$umbrella    = isset( $weather_effect_settings['umbrella'] ) ? $weather_effect_settings['umbrella'] : '';
$rain_umbrella = isset( $weather_effect_settings['rain_umbrella'] ) ? $weather_effect_settings['rain_umbrella'] : '';
$drop        = isset( $weather_effect_settings['drop'] ) ? $weather_effect_settings['drop'] : '';
$rain_drops  = isset( $weather_effect_settings['rain_drops'] ) ? $weather_effect_settings['rain_drops'] : '';
$cloud       = isset( $weather_effect_settings['cloud'] ) ? $weather_effect_settings['cloud'] : '';
$rain_cloud  = isset( $weather_effect_settings['rain_cloud'] ) ? $weather_effect_settings['rain_cloud'] : '';
$r_img_base  = plugin_dir_url( __FILE__ ) . 'assets/images/rain/';
?>
<script>
jQuery(document).ready(function(){
	<?php if ( $umbrella == 'umbrella' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $r_img_base . $rain_umbrella . '.png' ); ?>", minSize: <?php echo esc_js( (int) $rain_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $rain_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $rain_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $rain_speed ); ?> });
	<?php } ?>
	<?php if ( $drop == 'drop' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $r_img_base . $rain_drops . '.png' ); ?>", minSize: <?php echo esc_js( (int) $rain_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $rain_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $rain_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $rain_speed ); ?> });
	<?php } ?>
	<?php if ( $cloud == 'cloud' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $r_img_base . $rain_cloud . '.png' ); ?>", minSize: <?php echo esc_js( (int) $rain_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $rain_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $rain_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $rain_speed ); ?> });
	<?php } ?>
});
</script>
<?php endif; ?>

<?php if ( $weather_occasion == 'thanks_giving_check' ) : ?>
<?php
$turkey            = isset( $weather_effect_settings['turkey'] ) ? $weather_effect_settings['turkey'] : '';
$thanksgiving_turkey = isset( $weather_effect_settings['thanksgiving_turkey'] ) ? $weather_effect_settings['thanksgiving_turkey'] : '';
?>
<script>
jQuery(document).ready(function(){
	<?php if ( $turkey == 'turkey' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'assets/images/thanksgiving/' . $thanksgiving_turkey . '.png' ); ?>", minSize: <?php echo esc_js( (int) $thanksgiving_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $thanksgiving_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $thanksgiving_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $thanksgiving_speed ); ?> });
	<?php } ?>
});
</script>
<?php endif; ?>

<?php if ( $weather_occasion == 'valentine_check' ) : ?>
<?php
$rose            = isset( $weather_effect_settings['rose'] ) ? $weather_effect_settings['rose'] : '';
$valentine_rose  = isset( $weather_effect_settings['valentine_rose'] ) ? $weather_effect_settings['valentine_rose'] : '';
$balloon         = isset( $weather_effect_settings['balloon'] ) ? $weather_effect_settings['balloon'] : '';
$valentine_balloon = isset( $weather_effect_settings['valentine_balloon'] ) ? $weather_effect_settings['valentine_balloon'] : '';
$heart           = isset( $weather_effect_settings['heart'] ) ? $weather_effect_settings['heart'] : '';
$valentine_heart = isset( $weather_effect_settings['valentine_heart'] ) ? $weather_effect_settings['valentine_heart'] : '';
$v_img_base      = plugin_dir_url( __FILE__ ) . 'assets/images/valentine/';
?>
<script>
jQuery(document).ready(function(){
	<?php if ( $rose == 'rose' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $v_img_base . $valentine_rose . '.png' ); ?>", minSize: <?php echo esc_js( (int) $valentine_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $valentine_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $valentine_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $valentine_speed ); ?> });
	<?php } ?>
	<?php if ( $balloon == 'balloon' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $v_img_base . $valentine_balloon . '.png' ); ?>", minSize: <?php echo esc_js( (int) $valentine_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $valentine_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $valentine_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $valentine_speed ); ?> });
	<?php } ?>
	<?php if ( $heart == 'heart' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( $v_img_base . $valentine_heart . '.png' ); ?>", minSize: <?php echo esc_js( (int) $valentine_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $valentine_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $valentine_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $valentine_speed ); ?> });
	<?php } ?>
});
</script>
<?php endif; ?>

<?php if ( $weather_occasion == 'new_year_check' ) : ?>
<?php
$balloon_new    = isset( $weather_effect_settings['balloon_new'] ) ? $weather_effect_settings['balloon_new'] : '';
$newyear_balloon = isset( $weather_effect_settings['newyear_balloon'] ) ? $weather_effect_settings['newyear_balloon'] : '';
$sticker        = isset( $weather_effect_settings['sticker'] ) ? $weather_effect_settings['sticker'] : '';
$new_year_sticker = isset( $weather_effect_settings['new_year_sticker'] ) ? $weather_effect_settings['new_year_sticker'] : '';
?>
<script>
jQuery(document).ready(function(){
	<?php if ( $balloon_new == 'balloon_new' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'assets/images/newyear/' . $newyear_balloon . '.png' ); ?>", minSize: <?php echo esc_js( (int) $newyear_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $newyear_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $newyear_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $newyear_speed ); ?> });
	<?php } ?>
	<?php if ( $sticker == 'sticker' ) { ?>
	snowFall.snow(document.body, { image: "<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'assets/images/newyear/' . $new_year_sticker . '.png' ); ?>", minSize: <?php echo esc_js( (int) $newyear_min_size_leaf ); ?>, maxSize: <?php echo esc_js( (int) $newyear_max_size_leaf ); ?>, flakeCount: <?php echo esc_js( (int) $newyear_flakes_leaf ); ?>, maxSpeed: <?php echo esc_js( (int) $newyear_speed ); ?> });
	<?php } ?>
});
</script>
<?php endif; ?>
