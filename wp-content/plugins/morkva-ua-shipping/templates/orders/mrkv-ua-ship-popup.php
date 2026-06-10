<div id="mrkv_ua_ship_create_invoice" class="mrkv_ua_ship_modal">
    <div class="mrkv_ua_ship_modal-content">
        <span class="mrkv_ua_ship_close">
            <img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/global'; ?>/close-icon.svg" alt="<?php echo __('Close', 'mrkv-ua-shipping'); ?>" title="<?php echo __('Close', 'mrkv-ua-shipping'); ?>">
        </span>
        <h2>
            <img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/global'; ?>/document-icon.svg" alt="<?php echo __('Create', 'mrkv-ua-shipping'); ?>" title="<?php echo __('Create', 'mrkv-ua-shipping'); ?>">
            <span><?php echo __('Create invoice by Order', 'mrkv-ua-shipping'); ?> #<span class="mrkv_ua_shipping_order_id"></span>
        </h2>
        <p><?php echo __('Check the necessary fields that will be used to create the invoice', 'mrkv-ua-shipping'); ?></p>
        <hr class="mrkv-ua-ship__hr">
        <?php 
            require_once MRKV_UA_SHIPPING_PLUGIN_PATH . 'classes/settings/global/mrkv-ua-shipping-option-fields.php';
            global $mrkv_global_option_generator;
            $mrkv_global_option_generator = new MRKV_UA_SHIPPING_OPTION_FIELDS();
        ?>
        <div class="mrkv_ua_ship_modal-content__inner">
            <?php 
                $m_ua_active_plugins = get_option('m_ua_active_plugins');

                foreach(MRKV_UA_SHIPPING_LIST as $slug => $shipping)
                {
                    if($m_ua_active_plugins && isset($m_ua_active_plugins[$slug]['enabled']) && $m_ua_active_plugins[$slug]['enabled'] == 'on')
                    {
                        ?>
                            <div data-ship="<?php echo $slug; ?>" class="mrkv_ua_ship_create_invoice__changed">
                                <?php
                                    # Include template
                                    include MRKV_UA_SHIPPING_PLUGIN_PATH_TEMP . '/orders/mrkv-ua-ship-popup-'  . $slug . '.php';
                                ?>
                            </div>
                        <?php
                    }
                }
            ?>
        </div>
        <div class="mrkv_ua_ship_create_invoice__footer">
            <?php 
                foreach(MRKV_UA_SHIPPING_LIST as $slug => $shipping)
                {
                    if($m_ua_active_plugins && isset($m_ua_active_plugins[$slug]['enabled']) && $m_ua_active_plugins[$slug]['enabled'] == 'on')
                    {
                        ?>
                            <div data-ship="<?php echo $slug; ?>" class="mrkv_ua_ship_create_invoice__action mrkv_ua_ship_create_invoice__changed"><?php echo __('Create', 'mrkv-ua-shipping'); ?></div>
                            <img class="mrkv_ua_ship_create_invoice__changed" data-ship="<?php echo $slug; ?>" src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/' . $slug; ?>/logo-settings.svg" alt="<?php echo $slug; ?>" title="<?php echo $slug; ?>">
                        <?php
                    }
                }
            ?>
            <div class="mrkv_ua_ship_create_invoice__loader"></div>
        </div>
    </div>
</div>
<div id="mrkv_ua_ship_create_invoice_failed" class="mrkv_ua_ship_modal">
    <div class="mrkv_ua_ship_modal-content">
        <span class="mrkv_ua_ship_close">
            <img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/global'; ?>/close-icon.svg" alt="<?php echo __('Close', 'mrkv-ua-shipping'); ?>" title="<?php echo __('Close', 'mrkv-ua-shipping'); ?>">
        </span>
        <h2>
            <img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/global'; ?>/document-icon.svg" alt="<?php echo __('Create', 'mrkv-ua-shipping'); ?>" title="<?php echo __('Create', 'mrkv-ua-shipping'); ?>">
            <span><?php echo __('Create invoice Failed by Order', 'mrkv-ua-shipping'); ?> #<span class="mrkv_ua_shipping_order_id"></span>
        </h2>
        <hr class="mrkv-ua-ship__hr">
        <div class="mrkv_ua_ship_modal-content__inner">
            <div class="mrkv_ua_ship_modal-content__message_failed">
                
            </div>
        </div>
        <div class="mrkv_ua_ship_create_invoice__footer">
            <a class="close-error-mrkv-ua-ship"><?php echo __('Turn back', 'mrkv-ua-shipping'); ?></a>
        </div>
    </div>
</div>
<?php
    $is_order_page = '';
    global $pagenow;
    if(is_admin() && $pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'shop_order')
    {
        $is_order_page = 'mrkv_orders_list';
    }
?>
<div id="mrkv_ua_ship_create_invoice_completed" class="mrkv_ua_ship_modal <?php echo $is_order_page; ?>">
    <div class="mrkv_ua_ship_modal-content">
        <span class="mrkv_ua_ship_close">
            <img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/global'; ?>/close-icon.svg" alt="<?php echo __('Close', 'mrkv-ua-shipping'); ?>" title="<?php echo __('Close', 'mrkv-ua-shipping'); ?>">
        </span>
        <h2>
            <img src="<?php echo MRKV_UA_SHIPPING_IMG_URL . '/global'; ?>/document-icon.svg" alt="<?php echo __('Create', 'mrkv-ua-shipping'); ?>" title="<?php echo __('Create', 'mrkv-ua-shipping'); ?>">
            <span><?php echo __('Create invoice Completed by Order', 'mrkv-ua-shipping'); ?> #<span class="mrkv_ua_shipping_order_id"></span>
        </h2>
        <hr class="mrkv-ua-ship__hr">
        <div class="mrkv_ua_ship_modal-content__inner">
            <div class="mrkv_ua_ship_modal-content__message_completed">
                
            </div>
        </div>
        <div class="mrkv_ua_ship_create_invoice__footer">
            <a class="close-error-mrkv-ua-ship"><?php echo __('Turn back', 'mrkv-ua-shipping'); ?></a>
            <a style="display:none;" target="blanc" class="print-ttn-mrkv-ua-ship" data-ship="" data-invoice="" data-form=""><?php echo esc_html__('Print invoice', 'mrkv-ua-shipping'); ?></a>
            <a style="display: none;" href="" class="print-sticker-mrkv-ua-ship" target="blanc"><?php echo esc_html__('Print sticker', 'mrkv-ua-shipping'); ?></a>
        </div>
    </div>
</div>