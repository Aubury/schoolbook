<?php

$getinfo = $ukrposhtaApi->GetInfo( $_GET['post'] );
$type = $getinfo['type'];

if ($type == "INTERNATIONAL"){
  require __DIR__.'/edit-international.php';
}
else{
$uuid = $getinfo['uuid'];
$checkOnDelivery = $getinfo['checkOnDelivery'];

$getinfo = $ukrposhtaApi->GetInfo( $_GET['post'] );
if(isset($_POST['update'])){
  //
  $ukrposhtaApi = new UkrposhtaApi($bearer ,$cptoken, $tbearer);
  logg('created  istance of $ukrposhtaApi');
  $data = array(
  "uuid" => $uuid,
  "checkOnDelivery" => 1,
  "description" => $_POST['description'],
  "postpay" => intval($_POST['postpay']),
  "onFailReceiveType" => $_POST['onFailReceiveType'],

  "parcels"=> array( array(
    "uuid"=>	$getinfo['parcels'][0]['uuid'],
    "name"=> 'Посилка',
    "weight"=> $_POST['weight'],
    "length"=> $_POST['length'],
    "width"=> $_POST['width'],
    "height"=> $_POST['height'],
    "declaredPrice" => $_POST['declaredPrice']),
  )
 );
 logg('tut0');
  $ukrposhtaApi->modelShipmentsPut($data, $uuid);
  $getinfo = $ukrposhtaApi->GetInfo( $_GET['post'] );
}

?>


<h3>Редагування відправлення <?php echo $_GET['post']; ?> для замовлення <a href=post.php?post=<?php echo $_GET['order']; ?>&action=edit><?php echo $_GET['order']; ?></a></h3>
<form style="grid-template-columns: 2fr 1fr;" class="form-invoice" action="admin.php?page=morkvaup_invoices&post=<?php echo $_GET['post']; ?>&order=<?php echo $_GET['order']; ?>" method="post">
  <input type="hidden" name="uuid" value="<?php echo $getinfo['uuid']; ?>">
  <div class="tablecontainer" style=display:none>
    <table class="form-table full-width-input">
      <?php formblock_title('Відправник'); ?>
      <tr><td>
        <input type="text" readonly  name="" value="<?php echo $getinfo['sender']['name']; ?>">
      </td></tr>
    </table>
    <table class="form-table full-width-input">
      <?php formblock_title('Отримувач'); ?>
      <tr><td>
        <input type="text" readonly name="" value="<?php echo $getinfo['recipient']['name']; ?>">
      </td></tr>
    </table>
  </div>
  <div class="tablecontainer">
    <table class="form-table full-width-input">
      <?php formblock_title('Параметри відправлення'); ?>
    <tr>
      <?php the_upformlabel('Опис'); ?>
     <td>
       <textarea name="description"><?php echo 	$getinfo['description']; ?></textarea>
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
       <input id="invoice_priceid" type="text" name="declaredPrice" value="<?php echo $getinfo['declaredPrice']; ?>" >
     </td>
   </tr>
   <tr>
     <?php the_upformlabel( 'Вага, ' .  woo_name_weihgt_unit_translate() ); ?>
    <td>
      <?php $weight_in_unit = round ( $getinfo['parcels'][0]['weight'] / woo_setting_weight_unit(), 3 ) ?>
      <input type="text" name="weight"  id="invoice_cargo_mass" value="<?php echo $weight_in_unit ?>  ">
    </td>
  </tr>
  <tr>
    <?php the_upformlabel('Довжина, см'); ?>
   <td>
     <input type="text" name="length"  id="length" value="<?php echo $getinfo['parcels'][0]['length']; ?> ">
   </td>
 </tr>
 <tr>
   <?php the_upformlabel('Ширина, см'); ?>
  <td>
    <input type="text" name="width"  id="width" value="<?php echo $getinfo['parcels'][0]['width']; ?> ">
  </td>
</tr>
<tr>
  <?php the_upformlabel('Висота, см'); ?>
 <td>
   <input type="text" name="height"  id="height" value="<?php echo $getinfo['parcels'][0]['height']; ?>">
 </td>
</tr>
 <tr>
   <?php the_upformlabel('Післяплата, грн'); ?>
  <td style="padding-bottom: 0;">
    <input type="text" id="invoice_placesi" name="postpay" value="<?php echo $getinfo['postPayUah']; ?>" >
  </td>
  </tr>

   <tr>
    <td>
      <input class="wpbtn button button-primary" type="submit" name="update" value="Оновити">
    </td>
   </tr>
    </table>




  </div>
  <div class="">
    <?php require 'card.php'; ?>
  </div>
</form>


<?php

echo '<pre>';
// print_r($getinfo);
echo '</pre>';

}
 ?>
