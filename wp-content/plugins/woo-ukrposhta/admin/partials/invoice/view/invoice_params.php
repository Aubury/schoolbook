        <div class="tablecontainer">
            <table id="i6x" class="form-table full-width-input i6x">
                <tbody>
                    <tr>
                        <th colspan=2>
                            <h3 class="formblock_title">Параметри відправлення</h3>
                        </th>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="mrkvup_default_payer">Платник</label>
                        </th>
                        <td>
                            <select id="mrkvup_default_payer" name="mrkvup_default_payer">
                                <?php
                                    $default_payer =  get_option( 'morkva_ukrposhta_default_payer' );
                                    if ( 'mrkvup_recipient' == $default_payer ) : ?>
                                        <option value="mrkvup_recipient" selected="selected">Одержувач</option>
                                        <option value="mrkvup_sender">Відправник</option>
                                    <?php
                                    elseif ( 'mrkvup_sender' == $default_payer ) : ?>
                                        <option value="mrkvup_recipient">Одержувач</option>
                                        <option value="mrkvup_sender" selected="selected">Відправник</option>
                                    <?php endif; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label class="light" for="invoice_cargo_mass" >Вага, г *</label>
                        </th>
                        <?php
                            $invoice_addweight = intval( get_option( 'invoice_addweight' ) );
                            $Weight_object = new \stdClass();
                            if ( null !== $order_data->get_meta_data()[1] ) {
                                $Weight_object = ( $order_data->get_meta_data()[1] );
                            }
                            $weight_value = 0;
                            if ( isset( $Weight_object ) ) {
                                $weight_value  =  $Weight_object->get_data();
                            }
                            $order_weight = 0;
                            if ( isset( $weight_value['value']['data']['Weight'] ) ) {
                                $order_weight = $weight_value['value']['data']['Weight'];
                            } else {
                                $order_weight = $invoiceOrder->getOrderWeight();
                            }
                            $all_weight0 = $order_weight + $invoice_addweight;
                            $all_weight = ( $all_weight0 > 0 ) ?  ( $invoiceOrder->getUnitWeight() * $all_weight0 ) : $order_weight;
                        ?>
                        <td>
                            <input type="text" name="invoice_cargo_mass" required id="invoice_cargo_mass"
                                value="<?php echo (int) $weight; ?>"  />
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <p>
                                <?php if ($weight > 0) {
                                        echo '<span> Вага замовлення: ' . $weight. ' г' . '.<br></span>';
                                    } else {
                                        echo '<span> Вагу замовлення не пораховано тому що вага товарів відсутня.<br></span>';
                                    }
                                    if ( $invoice_addweight > 0 ) {
                                        echo '<span> Вага упаковки: ' . $invoice_addweight . ' ' . $invoiceOrder->translateUnitWeightName() . '.<br></span>';
                                    } else {
                                        echo '<span> Вагу упаковки не пораховано тому що дані про вагу упаковки відсутні. </span>';
                                    }
                                ?>
                            </p>
                            <p class="light"><?php echo $invoiceOrder->weightMsg; ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label class="light" for="max_order_lenght">Найбільша сторона, см *</label>
                        </th>
                        <td>
                            <input type="text" id="max_order_lenght" name="invoice_volume" value="<?php echo $length; ?>" required />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="invoice_priceid">Заявлена цінність</label>
                        </th>
                        <td>
                            <input id="invoice_priceid" type="text" name="declaredPrice"
                                value="<?php echo esc_html( $order_data->get_total() - $order_data->get_shipping_total() ); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="invoice_placesi">Післяплата, грн</label>
                        </th>
                        <td style="padding-bottom: 0;">
                            <input type="text" id="invoice_placesi" name="invoice_places" value="<?php $mess1 = '';
                            if ( ( $order_data->get_payment_method() == 'cod' ) ) {
                                echo esc_html( $order_data->get_total() - $order_data->get_shipping_total() );
                                $mess1 = 'При замовленні було обрано оплату при отриманні,  в післяплату автоматично вписана сума замовлення.';
                            } else {
                                echo '0';
                                $mess1 = 'Замовлення без післяплати, тому тут вказаний нуль.';
                            }
                            ?>
                            " required/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2><p style="font-size:90%"><?php echo $mess1; ?></p></td>
                    </tr>
                    <tr class="nv-if-international">
                        <th scope="row">
                            <label for="up_invoice_description">Опис відправлення</label>
                        </th>
                        <td class="pb7">
                            <?php
                                $path = MUP_PLUGIN_PATH . '/admin/partials/morkvaup-plugin-invoices-page.php';
                                if ( file_exists( $path ) ) {
                                    $id = $order_data->get_id();
                                    $descriptionarea = $invoiceOrder->getDescription();
                                    // $descriptionarea = str_replace("[list_qa]", $list2, $descriptionarea);
                                    // $descriptionarea = str_replace("[list]", $list, $descriptionarea);
                                    // $descriptionarea = str_replace("[q]", $prod_quantity, $descriptionarea);
                                    // $descriptionarea = str_replace("[qa]", $prod_quantity2, $descriptionarea);
                                    $descriptionarea = str_replace("[p]", $order_data->get_total(), $descriptionarea);
                                } else {
                                    $descriptionarea = '';
                                }
                            ?>
                            <textarea  type="text" id="up_invoice_description" name="up_invoice_description" class="input"
                                minlength="1" placeholder="Не більше 40 символів" required><?php echo esc_textarea($descriptionarea); ?>
                            </textarea>
                            <p id="error_dec"></p>
                        </td>
                    </tr>
                    <tr>
                        <th colspan=2  scope="row">У разі не вручення:</th>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <div  class="onfail ">
                                <input type="radio" id="dqq" name="onFailReceiveType" value="RETURN">
                                <label for="dqq">повернути відправнику через 14 календарних днів.</label>
                            </div>
                            <div class="onfail ">
                                <input checked type="radio" id="dqq2" name="onFailReceiveType" value="RETURN_AFTER_7_DAYS">
                                <label for="dqq2">повернути відправлення після закінчення строку безкоштовного зберігання (5 робочих днів).</label>
                            </div>
                            <div class="onfail">
                                <input type="radio" id="dqq3" name="onFailReceiveType" value="PROCESS_AS_REFUSAL">
                                <label for="dqq3">знищити відправлення</label>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="form-table full-width-input i7x">
                <tbody>
                    <tr>
                        <td>
                            <input type="hidden" name="sendtype" value="<?php  echo esc_attr(get_option('sendtype')); ?>" />
                            <input type="hidden" name="sendwtype" value="<?php  echo esc_attr(get_option('sendwtype')); ?>" />
                            <input type="hidden" name="country_rec" id="country_rec" list="country" value="<?php 
                                if($order_data->get_billing_country())
                                {
                                    echo $order_data->get_billing_country();
                                }
                                else
                                {
                                    echo 'UA';
                                }
                             ?>"  />
                             <input type="hidden" name="region_data" id="region_data" value="<?php echo $order_data->get_billing_state(); ?>">
                             <input type="hidden" name="city_data" id="city_data" value="<?php echo $order_data->get_billing_city(); ?>">
                             <input type="hidden" name="street_data" id="street_data" value="<?php echo $order_data->get_billing_address_1(); ?>">
                             <input type="hidden" name="apartmentNumber_data" id="apartmentNumber_data" value="<?php echo $order_data->get_billing_address_2(); ?>">
                            <input type="submit" value="Створити" name="morkvaup_checkforminputs" class="morkvaup_checkforminputs button button-primary" id="submit"/>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
            <?php include MORKVA_UKRPOSHTA_PLUGIN_DIR . 'admin/partials/card.php'; ?>
        </div>
* - обов'язкове для заповнення поле
    </form><!-- class="form-invoice" action="admin.php?page=morkvaup_invoice" method="post" name="invoice" -->
</div><!--class="container" -->
