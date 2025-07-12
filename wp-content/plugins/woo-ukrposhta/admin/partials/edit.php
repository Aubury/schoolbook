<?php

if(!isset($_GET['post']) || !isset($_GET['order']))
{
  return;
}
if (isset($_GET['_wpnonce'])) {
  // Remove escaping from input data
  $nonce = sanitize_text_field(wp_unslash($_GET['_wpnonce']));
  $order_data_main = sanitize_text_field(wp_unslash($_GET['order']));

  if(wp_verify_nonce( $nonce, 'morkvaup_invoice_action_' . $order_data_main ))
  {
      $post_data_main = sanitize_text_field(wp_unslash($_GET['post']));
      $getinfo = $ukrposhtaApi->GetInfo($post_data_main);
      $type = $getinfo['type'];

      $uuid = $getinfo['uuid'];
      $checkOnDelivery = $getinfo['checkOnDelivery'];

      $getinfo = $ukrposhtaApi->GetInfo($post_data_main);

      if (isset($_POST['update'])) {
          $ukrposhtaApi = new UkrposhtaApi($bearer, $cptoken, $tbearer);
          logg('Created instance of $ukrposhtaApi');

          // Safely retrieve POST data with checks
          $description = isset($_POST['description']) ? sanitize_textarea_field(wp_unslash($_POST['description'])) : '';
          $postpay = isset($_POST['postpay']) ? intval(wp_unslash($_POST['postpay'])) : 0;
          $onFailReceiveType = isset($_POST['onFailReceiveType']) ? sanitize_text_field(wp_unslash($_POST['onFailReceiveType'])) : '';
          $weight = isset($_POST['weight']) ? floatval(wp_unslash($_POST['weight'])) : 0.0;
          $length = isset($_POST['length']) ? floatval(wp_unslash($_POST['length'])) : 0.0;
          $width = isset($_POST['width']) ? floatval(wp_unslash($_POST['width'])) : 0.0;
          $height = isset($_POST['height']) ? floatval(wp_unslash($_POST['height'])) : 0.0;
          $declaredPrice = isset($_POST['declaredPrice']) ? floatval(wp_unslash($_POST['declaredPrice'])) : 0.0;

          $data = array(
              "uuid" => $uuid,
              "checkOnDelivery" => 1,
              "description" => $description,
              "postpay" => $postpay,
              "onFailReceiveType" => $onFailReceiveType,
              "parcels" => array(
                  array(
                      "uuid" => $getinfo['parcels'][0]['uuid'],
                      "name" => 'Посилка',
                      "weight" => $weight,
                      "length" => $length,
                      "width" => $width,
                      "height" => $height,
                      "declaredPrice" => $declaredPrice,
                  ),
              ),
          );

          logg('tut0');
          $ukrposhtaApi->modelShipmentsPut($data, $uuid);
          $getinfo = $ukrposhtaApi->GetInfo($post_data_main);
      }
      ?>
        <h3>Редагування відправлення <?php echo esc_html($post_data_main); ?> для замовлення <a href=post.php?post=<?php echo esc_html($order_data_main); ?>&action=edit><?php echo esc_html($order_data_main); ?></a></h3>
<form style="grid-template-columns: 2fr 1fr;" class="form-invoice" action="admin.php?page=morkvaup_invoices&post=<?php echo esc_html($post_data_main); ?>&order=<?php echo esc_html($order_data_main); ?>" method="post">
  <input type="hidden" name="uuid" value="<?php echo esc_html($getinfo['uuid']); ?>">
  <div class="tablecontainer" style=display:none>
    <table class="form-table full-width-input">
      <?php formblock_title('Відправник'); ?>
      <tr><td>
        <input type="text" readonly  name="" value="<?php echo esc_html($getinfo['sender']['name']); ?>">
      </td></tr>
    </table>
    <table class="form-table full-width-input">
      <?php formblock_title('Отримувач'); ?>
      <tr><td>
        <input type="text" readonly name="" value="<?php echo esc_html($getinfo['recipient']['name']); ?>">
      </td></tr>
    </table>
  </div>
  <div class="tablecontainer">
    <table class="form-table full-width-input">
      <?php formblock_title('Параметри відправлення'); ?>
    <tr>
      <?php the_upformlabel('Опис'); ?>
     <td>
       <textarea name="description"><?php echo  esc_textarea($getinfo['description']); ?></textarea>
     </td>
   </tr>
   <tr>
    <?php the_upformlabel('У разі не вручення:'); ?>
    <td style="min-width: 180px;">
      <div class="">
                <?php $faildedtype =  $getinfo['onFailReceiveType']; ?>
                <div class="onfail ">
                  <input <?php if($faildedtype == 'RETURN'){ echo 'checked';} ?> type="radio" id="dqq" name="onFailReceiveType" value="RETURN">
                 <label for="dqq">повернути відправнику через 14 календарних днів.</label>
               </div>
               <div class="onfail ">
                 <input  <?php if($faildedtype == 'RETURN_AFTER_7_DAYS'){ echo 'checked';} ?>  type="radio" id="dqq2" name="onFailReceiveType" value="RETURN_AFTER_7_DAYS">
                 <label for="dqq2">повернути відправлення після закінчення строку безкоштовного зберігання (5 робочих днів).</label>
               </div>
               <div class="onfail">
                 <input  <?php if($faildedtype == 'PROCESS_AS_REFUSAL'){ echo 'checked';} ?>   type="radio" id="dqq3" name="onFailReceiveType" value="PROCESS_AS_REFUSAL">
                 <label for="dqq3">знищити відправлення</label>
               </div>
            </div>
    </td>
   </tr>
   <tr>
     <?php the_upformlabel('Оголошена вартість'); ?>
     <td>
       <input id="invoice_priceid" type="text" name="declaredPrice" value="<?php echo esc_html($getinfo['declaredPrice']); ?>" >
     </td>
   </tr>
   <tr>
     <?php the_upformlabel( 'Вага, ' .  woo_name_weihgt_unit_translate() ); ?>
    <td>
      <?php $weight_in_unit = round ( $getinfo['parcels'][0]['weight'] / woo_setting_weight_unit(), 3 ) ?>
      <input type="text" name="weight"  id="invoice_cargo_mass" value="<?php echo esc_html($weight_in_unit); ?>  ">
    </td>
  </tr>
  <tr>
    <?php the_upformlabel('Довжина, см'); ?>
   <td>
     <input type="text" name="length"  id="length" value="<?php echo esc_html($getinfo['parcels'][0]['length']); ?> ">
   </td>
 </tr>
 <tr>
   <?php the_upformlabel('Ширина, см'); ?>
  <td>
    <input type="text" name="width"  id="width" value="<?php echo esc_html($getinfo['parcels'][0]['width']); ?> ">
  </td>
</tr>
<tr>
  <?php the_upformlabel('Висота, см'); ?>
 <td>
   <input type="text" name="height"  id="height" value="<?php echo esc_html($getinfo['parcels'][0]['height']); ?>">
 </td>
</tr>
 <tr>
   <?php the_upformlabel('Післяплата, грн'); ?>
  <td style="padding-bottom: 0;">
    <input type="text" id="invoice_placesi" name="postpay" value="<?php echo esc_html($getinfo['postPayUah']); ?>" >
  </td>
  </tr>

   <tr>
    <td>
      <input class="wpbtn button button-primary" type="submit" name="update" value="Оновити">
    </td>
   </tr>
    </table>
    <?php wp_nonce_field('mrkv_up_form_action', 'mrkv_up_my_form_nonce'); ?>



  </div>
  <div class="">
    <?php require 'card.php'; ?>
  </div>
</form>


      <?php
  }
}

?>


