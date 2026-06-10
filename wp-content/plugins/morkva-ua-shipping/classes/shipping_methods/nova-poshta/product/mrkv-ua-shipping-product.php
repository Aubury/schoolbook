<?php 
    $classes_enabled = (isset($settings_shipping['shipment']['class']['enabled']) && $settings_shipping['shipment']['class']['enabled']) ? true : false;
    $global_cargo_type = (isset($settings_shipping['shipment']['type']) && $settings_shipping['shipment']['type']) ? $settings_shipping['shipment']['type'] : '';
    $tire_classes = (isset($settings_shipping['shipment']['class']['list']['TiresWheels']) && is_array($settings_shipping['shipment']['class']['list']['TiresWheels']) && !empty($settings_shipping['shipment']['class']['list']['TiresWheels'])) ? $settings_shipping['shipment']['class']['list']['TiresWheels'] : [];
    $document_classes = (isset($settings_shipping['shipment']['class']['list']['Documents']) && is_array($settings_shipping['shipment']['class']['list']['Documents']) && !empty($settings_shipping['shipment']['class']['list']['Documents'])) ? $settings_shipping['shipment']['class']['list']['Documents'] : [];
    $shipping_class_id = $product->get_shipping_class_id();

    $exists = false;

    if(isset($settings_shipping['shipment']['class']['list']) &&
    is_array($settings_shipping['shipment']['class']['list']) && !empty($settings_shipping['shipment']['class']['list']))
    {
        $ids = array_column($settings_shipping['shipment']['class']['list'], 0);

        if (in_array((int)$shipping_class_id, $ids, true)) {
            $exists = true;
        }
    }

    if(($classes_enabled && !empty($tire_classes) && in_array($shipping_class_id, $tire_classes)) || ($global_cargo_type == 'TiresWheels' && !$exists))
    {
        require_once MRKV_UA_SHIPPING_PLUGIN_PATH . 'classes/shipping_methods/nova-poshta/api/mrkv-ua-shipping-api-nova-poshta.php';
        $mrkv_object_nova_poshta = new MRKV_UA_SHIPPING_API_NOVA_POSHTA($settings_shipping);
        require_once MRKV_UA_SHIPPING_PLUGIN_PATH . 'classes/shipping_methods/nova-poshta/api/mrkv-ua-shipping-calculate-nova-poshta.php';
        $mrkv_calculate_nova_poshta = new MRKV_UA_SHIPPING_CALCULATE_NOVA_POSHTA($mrkv_object_nova_poshta);
        $nova_poshta_tires = $mrkv_calculate_nova_poshta->get_tiregroup_list();
        $mrkv_tire_type = get_post_meta($post->ID, '_mrkv_tire_type', true);
        ?>
            <hr>
            <h3 style="padding:0 15px;"><?php _e('Nova Poshta', 'mrkv-ua-shipping'); ?></h3>
            <p class="form-field">
            <label for="_mrkv_tire_type"><?php _e('Tire Type', 'mrkv-ua-shipping'); ?></label>
            <select id="_mrkv_tire_type" name="_mrkv_tire_type" class="select short">
                <option value=""><?php _e('Select Tire Type', 'mrkv-ua-shipping'); ?></option>
                <?php foreach ($nova_poshta_tires as $key => $label) : ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($mrkv_tire_type, $key); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="description"><?php _e('Select the type of tire for this product.', 'mrkv-ua-shipping'); ?></span>
        </p>
        <?php
    }

    if(($classes_enabled && !empty($document_classes) && in_array($shipping_class_id, $document_classes)) || ($global_cargo_type == 'Documents' && !$exists))
    {
        $nova_poshta_document_weights = [
            '0.1' => __('0.1 kg', 'mrkv-ua-shipping'),
            '0.5' => __('0.5 kg', 'mrkv-ua-shipping'),
            '1' => __('1 kg', 'mrkv-ua-shipping')

        ];
        $mrkv_document_weight = get_post_meta($post->ID, '_mrkv_document_weight', true);
        ?>
            <hr>
            <h3 style="padding:0 15px;"><?php _e('Nova Poshta', 'mrkv-ua-shipping'); ?></h3>
            <p class="form-field">
            <label for="_mrkv_document_weight"><?php _e('Document weight', 'mrkv-ua-shipping'); ?></label>
            <select id="_mrkv_document_weight" name="_mrkv_document_weight" class="select short">
                <option value=""><?php _e('Select Document Weight', 'mrkv-ua-shipping'); ?></option>
                <?php foreach ($nova_poshta_document_weights as $key => $label) : ?>
                    <option value="<?php echo esc_attr($key); ?>" <?php selected($mrkv_document_weight, $key); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="description"><?php _e('Select the weight of document for this product.', 'mrkv-ua-shipping'); ?></span>
        </p>
        <?php
    }
?>