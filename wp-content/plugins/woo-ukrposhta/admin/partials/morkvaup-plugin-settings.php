<?php

/**
 * Provide a public-facing view for the settings plugin page.
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin/partials
 */

use deliveryplugin\Ukrposhta\classes\invoice\InvoiceController;

?>

<?php
	function enqueue_custom_admin_scripts_and_styles_footer() 
	{

	$style_version = filemtime(MORKVA_UKRPOSHTA_PLUGIN_DIR . 'admin/css/style.css'); 
    $script_version = filemtime(MORKVA_UKRPOSHTA_PLUGIN_DIR . 'admin/js/script.js');

    wp_enqueue_style(
        'custom-admin-style',
        MORKVA_UKRPOSHTA_PLUGIN_URL . 'admin/css/style.css', 
        array(), 
        $style_version, 
        'all' 
    );

   
    wp_enqueue_script(
        'custom-admin-script', 
        MORKVA_UKRPOSHTA_PLUGIN_URL . 'admin/js/script.js', 
        array(), 
        $script_version,
        true
    );
}
add_action('admin_footer', 'enqueue_custom_admin_scripts_and_styles_footer');

    $invoiceController = new InvoiceController();
    $invoiceController->displayNav(); ?>

<div class="container">
	<div class="row">
		<h1 style="font-size:23px;font-weight:400;line-height:1.3;"><?php echo esc_html('Плагін Woo ' . MUP_PLUGIN_NAME); ?></h1>
		<?php settings_errors(); ?>
		<hr>

	    <?php 
	    	if (isset($_GET['_wpnonce'])) {
	            // Remove escaping from input data
	            $nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));

	            if(wp_verify_nonce($nonce, 'morkvaup_plugin_credentials_action'))
	            {
	                if ( isset( $_GET['credentials'] ) ){
	                	?>
	                		<div class="error">
								<p><strong>Помилка</strong>: Ключі API укрпошти відсуnні</p>
							</div>
	                	<?php
	                }
	            }
	        } ?>

		<div class="settingsgrid">
			<div class="w70">
				<div class="tab-content">
					<div id="tab-1" class="tab-pane active">
						<form method="post" action="options.php">
							<?php
								settings_fields( 'morkvaup_options_group' );
								do_settings_sections( 'morkvaup_plugin' );
								submit_button();
							?>
						</form>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<div>
				<?php include 'card.php' ; ?>
			</div>
			<div class="clear"></div>
		</div>
	</div>
</div>
