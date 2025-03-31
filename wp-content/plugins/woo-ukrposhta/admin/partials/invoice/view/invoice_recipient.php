    <table id=i5x class="form-table full-width-input i5x">
        <tbody>
            <tr class="nv-if-international">
                <th colspan=2>
                    <h3 class="formblock_title">Одержувач</h3>
                </th>
            </tr>
            <tr class="nv-if-international">
                <th scope="row">
                    <label for="rec_first_name">Прізвище *</label>
                </th>
                <td>
                    <input type="text" name="rec_first_name" id="rec_first_name" class="input recipient_name"
                        value="<?php echo esc_html( $invoiceOrder->getShippingLastName() ); ?>" required />
                </td>
            </tr>
            <tr class="nv-if-international">
                <th scope="row">
                    <label for="rec_last_name">Ім'я *</label>
                </th>
                <td>
                    <input type="text" name="rec_last_name" id="rec_last_name" class="input recipient_name"
                        value="<?php echo esc_html( $invoiceOrder->getShippingFirstName() ); ?>" required />
                </td>
            </tr>
            <tr class="nv-if-international">
                <th scope="row">
                    <label for="rec_middle_name">По батькові *</label>
                </th>
                <td>
                    <input type="text" name="rec_middle_name" id="rec_middle_name" class="input recipient_name"
                        value="<?php
                            if($invoiceOrder->getDeliveryType() == 'W2W')
                            {
                                echo esc_html( $invoiceOrder->getShippingMiddleName() );
                            }
                            else{
                                $order_data = $invoiceOrder->getOrderData();

                                
                                $billing_up_address_surname = $order_data->get_meta('_billing_up_address_surname');
                                if(!$billing_up_address_surname)
                                {
                                    $billing_up_address_surname = $order_data->get_meta('_shipping_up_address_surname');
                                }

                                echo $billing_up_address_surname; 
                            }
                          ?>" required />
                </td>
            </tr>
            <tr class="nv-if-international">
                <th scope="row">
                    <label for="index2">Поштовий індекс *</label>
                </th>
                <td>
                    <input id="index2" type="text" name="index2" class="input recipient_region"
                        value="<?php echo esc_html( $invoiceOrder->getShippingPostcode() ); ?>" required />
                </td>
            </tr>
            <tr t=user1>
                <th scope="row">
                    <label for="phone2">Телефон *</label>
                </th>
                <td>
                <input id="phone2" type="text" value="<?php echo '' . $invoiceOrder->getShippingPhone(); ?>"  name="phone2" required />
                </td>
            </tr>
        </tbody>
    </table>
</div><!-- class="tablecontainer" -->
