<div class="tablecontainer">

    <table class="form-table full-width-input i4x"  id="i4x">
        <tbody>
            <tr class="nv-if-international" t=user1>
                <th colspan=2>
                    <h3 class="formblock_title">Відправник</h3>
                    <input type="hidden" name="up_sender_type" id="up_sender_type" value="
                        <?php $up_sender_type = esc_attr( get_option( 'up_sender_type' ) ); echo $up_sender_type; ?>">
                    <div id="errors"></div>
                </th>
            </tr>
            <?php if ( 'COMPANY' == $senderType ) : ?>
                <tr t=user1>
                    <th scope="row">
                        <label for="up_company_sender_name">Назва компанії *</label>
                    </th>
                    <td>
                        <input type="text" id="up_company_sender_name" name="up_company_sender_name" class="input sender_name"
                            value="<?php echo esc_attr( get_option('up_company_name') ); ?>" placeholder="Не більше 60 символів" />
                    </td>
                </tr>
                <tr t=user1>
                    <th scope="row">
                        <label for="up_company_sender_edrpou">ЄДРПОУ *</label>
                    </th>
                    <td>
                        <input type="text" id="up_company_sender_edrpou" name="up_company_sender_edrpou" class="input sender_name"
                            value="<?php echo  esc_attr(get_option('edrpou')); ?>" />
                    </td>
                </tr>
            <?php endif; ?>
            <?php if ( 'INDIVIDUAL' == $senderType ) : ?>
                <tr t=user1>
                    <th scope="row">
                        <label for="sender_first_name">Прізвище *</label>
                    </th>
                    <td>
                        <input type="text" id="sender_first_name" name="sender_first_name" class="input sender_name"
                            value="<?php echo  esc_attr(get_option('names1')); ?>" required />
                    </td>
                </tr>
                <tr t=user1>
                    <th scope="row">
                        <label for="sender_first_name">Імя *</label>
                    </th>
                    <td>
                        <input type="text" id="sender_last_name" name="sender_last_name" class="input sender_name"
                            value="<?php  echo esc_attr(get_option('names2')); ?>" required />
                    </td>
                </tr>
            <?php endif; ?>
            <?php if ( 'PRIVATE_ENTREPRENEUR' == $senderType ) : ?>
                <tr t=user1>
                    <th scope="row">
                        <label for="up_company_sender_name">Фізична особа-підприємець (ФОП) *</label>
                    </th>
                    <td>
                        <input type="text" id="up_company_sender_name" name="up_company_sender_name" class="input sender_name"
                            value="<?php echo esc_attr( get_option('up_company_name') ); ?>" placeholder="Не більше 60 символів" />
                    </td>
                </tr>
                <tr t=user1>
                    <th scope="row">
                        <label for="up_company_sender_edrpou">Індивідуальний податковий номер (ІПН) *</label>
                    </th>
                    <td>
                        <input type="text" id="up_sep_tin" name="up_sep_tin" class="input sender_name"
                            value="<?php echo  esc_attr( get_option('up_tin' ) ); ?>" />
                    </td>
                </tr>
                <tr t=user1>
                    <th scope="row">
                        <label for="sender_first_name">Прізвище *</label>
                    </th>
                    <td>
                        <input type="text" id="sender_first_name" name="sender_first_name" class="input sender_name"
                            value="<?php echo  esc_attr(get_option('names1')); ?>" required />
                    </td>
                </tr>
                <tr t=user1>
                    <th scope="row">
                        <label for="sender_first_name">Імя *</label>
                    </th>
                    <td>
                        <input type="text" id="sender_last_name" name="sender_last_name" class="input sender_name"
                            value="<?php  echo esc_attr(get_option('names2')); ?>" required />
                    </td>
                </tr>
            <?php endif; ?>
            <tr t=address1>
                <th scope="row">
                    <label for="index1">Індекс відділення подачі відправлення *</label>
                </th>
                <td>
                    <input id="index1" type="text"  value="<?php  echo esc_attr(get_option('woocommerce_store_postcode')); ?>"
                        name="index1" required />
                </td>
            </tr>
            <tr t=user1>
                <th scope="row">
                    <label for="phone1">Телефон *</label>
                </th>
                <td>
                    <input id="phone1" type="text"  value="<?php echo esc_attr( get_option( 'phone' ) ); ?>"  name="phone1" required />
                </td>
            </tr>
        </tbody>
    </table>
