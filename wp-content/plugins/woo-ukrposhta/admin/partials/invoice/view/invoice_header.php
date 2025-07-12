<?php
use deliveryplugin\Ukrposhta\classes\invoice\InvoiceController;

$invoiceController = new InvoiceController();
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
?>

<?php $invoiceController->displayNav(); ?>

<div class="container">
    <h1 style="font-size:23px;font-weight:400;line-height:1.3;"><?php echo 'Нове відправлення Укрпошти №' . esc_html($order_id); ?></h1>
    <form class="form-invoice" method="post" name="invoice">
        <?php wp_nonce_field('mrkv_up_form_action', 'mrkv_up_my_form_nonce'); ?>
        <?php  if ( isset( $invoiceModel ) && $invoiceModel->isSuccessSaved ) { ?>
            <div id="messagebox" class="messagebox_show updated" data="160" style="height:0px;padding:0px">
                <?php $invoiceController->displaySuccessNotice( $invoice ); ?>
            </div>
            <?php if ( $invoiceOrder->isNotDimensions && ! $invoiceOrder->getWPOptionLength() || ! $length ) : ?>
                <div class="notice notice-warning is-dismissible" style="margin:0 -5px 0 0;">
                    <?php $invoiceController->displayWarningNotice(); ?>
                </div>
            <?php endif; ?>
        <?php }
        else{
            if (isset($_POST['mrkv_up_my_form_nonce'])) {
                // Remove escaping from input data
                $nonce = sanitize_text_field(wp_unslash($_POST['mrkv_up_my_form_nonce']));

                if(wp_verify_nonce($nonce, 'mrkv_up_form_action'))
                {
                    if( isset($message) && isset($_POST['sender_first_name']) || isset($_POST['up_company_sender_name']) || isset($_POST['up_company_sender_edrpou']) ) { ?>
                    <div id="messagebox" class="messagebox_show error" data="110" style="height: 0px;padding:0px;">
                        <div class="card text-white bg-danger">
                            <h3>Накладну створити не вдалося.</h3>
                            <p><?php echo esc_html($message . $ukrposhtaApi->httpCode401 . $ukrposhtaApi->httpCode403 . $ukrposhtaApi->httpCode404); ?></p>
                            <div class="clr"></div>
                        </div>
                    </div>
                    <?php }        
                }
            } 
        } ?>
        <div class="alink">
            <?php
                if ( ! empty($order_data->get_id() ) ) {
                    echo '<a class="btn" href="/wp-admin/post.php?post=' . esc_html($order_data->get_id()) . '&action=edit">Повернутись до замовлення</a>';
                echo '';
                }
            ?>
            <a href="edit.php?post_type=shop_order">Повернутись до замовлень</a>
        </div>
