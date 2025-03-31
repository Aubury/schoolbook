<?php


//getting uuid  from outer $getinfo variable
$uuid = $getinfo['uuid'];
//get checkout on delivery value
$checkOnDelivery = $getinfo['checkOnDelivery'];
//refresh info need?
$getinfo = $ukrposhtaApi->GetInfo( $_GET['post'] );

if(isset($_POST['update'])){
  //trying update info
  $ukrposhtaApi = new UkrposhtaApi($bearer ,$cptoken, $tbearer);
  //new ukrposhta api instance
  $declaredPrice = !isset($_POST['declaredPrice']) ?  $_POST['declaredPrice'] : null;
  //declared price rules are different
  $parcelitems  = array(array(
    "uuid"=>$getinfo['parcels'][0]['parcelItems'][0]['uuid'],
    "latinName" => $getinfo['parcels'][0]['parcelItems'][0]['latinName'],
  	"length"=> $_POST['length'],
  	"value"=> $_POST['declaredPrice'],
  	"description"=>$_POST['description'],
  	"quantity"=> $_POST['quantity'],
  	"weight"=> $_POST['weight'],
  	"countryOfOrigin"=>$_POST['countryOfOrigin'],
  	"currencyCode"=> "UAH"
   ));
   //data for modelShipmentsPut
  $data = array(
  "uuid" => $uuid,
  "checkOnDelivery" => 1,
  	"weight"=> $_POST['declaredweight'],

  "packageType" => $_POST['packageType'],
  "description" => $_POST['description'],
  "onFailReceiveType" => $_POST['onFailReceiveType'],



  "parcels"=> array( array(
    "uuid"=>	$getinfo['parcels'][0]['uuid'],
    "name"=> $_POST['nameparcel'],
    "length"=> $_POST['length'],
    "width"=> $_POST['width'],
    "height"=> $_POST['height'],
  	"parcelItems" => $parcelitems
  	),
  )
 );
 if(isset($_POST['postpay'])){
   if(intval($_POST['postpay']) > 0){
     $data["postpay"] = intval($_POST['postpay']);
   }
}

 //if declared price not empty
  if($declaredPrice){
  	 $data["declaredPrice"] = $_POST['declaredPrice'];
  	 $data["parcels"][0]["declaredPrice"] = $_POST['declaredPrice'];
  }
 //send request for update  shipment
  $ukrposhtaApi->modelShipmentsPut($data, $uuid);
  $getinfo = $ukrposhtaApi->GetInfo( $_GET['post'] );
}

//get parcel to display foreach loop
$parcels = $getinfo['parcels'];

?>
<!-- start frontend -->
<h3>Редагування відправлення <?php echo $_GET['post']; ?> для замовлення <a href=post.php?post=<?php echo $_GET['order']; ?>&action=edit><?php echo $_GET['order']; ?></a></h3>
<!-- form for editing  ttn -->
<form style="grid-template-columns: 2fr 1fr;" class="form-invoice" action="admin.php?page=morkvaup_invoices&post=<?php echo $_GET['post']; ?>&order=<?php echo $_GET['order']; ?>" method="post">
  <!-- uuid -->
  <input type="hidden" name="uuid" value="<?php echo $getinfo['uuid']; ?>">
  <!-- outer container for  form fields -->
  <div class="tablecontainer">
    <?php
    //parcels in parcel array
    foreach ($parcels as $parcel){
    	$parcelitems = $parcel['parcelItems'];
     ?>
     <!-- uuid -->
      <table class="form-table full-width-input">
        <?php formblock_title('Параметри відправлення '.$parcel['barcode'] ); ?>
        <tr>
        <?php the_upformlabel('Тип відправлення:'); ?>
        <td style="min-width: 180px;">
          <input type="text" readonly name="packageType" value="<?php echo $getinfo['packageType']; ?>">
        </td>
       </tr>

       <!-- parcel name block -->
        <tr>
	        <?php the_upformlabel('Назва посилки'); ?>
	       <td>
	         <input type=text name="nameparcel" value="<?php echo 	$parcel['name']; ?>" />
	       </td>
	     </tr>
       <!-- end parcel name block -->

       <tr>
	       <?php the_upformlabel('Оголошена вага, грам'); ?>
	       <td>
	         <input id="invoice_priceid" type="text" name="declaredweight" value="<?php echo $parcel['weight']; ?>" >
	       </td>
	     </tr>

       <!-- postpay if block -->
        <?php if(isset($parcel['postpay'])){
        $postpay = intval($parcel['postpay']); ?>
       <tr>
  	     <?php the_upformlabel('Післяплата'); ?>
  	     <td>
  	     	<input type=text name="postpay" value="<?php echo 	intval($parcel['postpay']); ?>" />
  	     	<p>Поле доступне не для всіх видів посилок</p>
  	     </td>
  	 	</tr>
      <?php } ?>
       <!-- end postpay if block -->

       <!--  block onFailReceiveType -->
     <tr>
      <?php the_upformlabel('У разі не вручення:'); ?>
      <td style="min-width: 180px;">
        <div class="">
                  <?php $faildedtype =  $getinfo['onFailReceiveType']; ?>
                  <div class="onfail ">
                    <input <?php if($faildedtype == 'RETURN'){ echo 'checked';} ?> type="radio" id="dqq" name="onFailReceiveType" value="RETURN">
                   <label for="dqq">повернути відправнику через 14 календарних днів.</label>
                 </div>
                 <div class="onfail">
                   <input  <?php if($faildedtype == 'PROCESS_AS_REFUSAL'){ echo 'checked';} ?>   type="radio" id="dqq3" name="onFailReceiveType" value="PROCESS_AS_REFUSAL">
                   <label for="dqq3">знищити відправлення</label>
                 </div>
              </div>
      </td>
     </tr>
      <!--  end block onFailReceiveType -->
       <!--  dimensions block -->
     <tr>
	     <?php the_upformlabel('Довжина'); ?>
	     <td>
	     	<input type=number name="length" value="<?php echo 	$parcel['length']; ?>" />
	     </td>
	   </tr>
     <tr>
	     <?php the_upformlabel('Ширина'); ?>
	     <td>
	     	<input type=number name="width" value="<?php echo 	$parcel['width']; ?>" />
	     </td>
	   </tr>
     <tr>
	     <?php the_upformlabel('Висота'); ?>
	     <td>
	     	<input type=number name="height" value="<?php echo 	$parcel['height']; ?>" />
	     </td>
	   </tr>
      <!--  end dimensions block -->

      <!-- declared price if block -->
       <?php if(isset($parcel['declaredPrice'])){
       $postpay = intval($parcel['declaredPrice']); ?>
      <tr>
        <?php the_upformlabel('Оголошена вартість'); ?>
        <td>
         <input type=text name="declaredPrice" value="<?php echo 	intval($parcel['declaredPrice']); ?>" />
         <p>Поле доступне не для всіх видів посилок</p>
        </td>
     </tr>
     <?php } ?>
      <!-- end declared price if block -->


    <tr>

    <?php

    $index = 1;
    foreach ($parcelitems as $parcelitem){ ?>

    	<tr>
    		<td><hr>Елемент № <?php echo  $index; ?></td>
    	</tr>
    	<tr>
	        <?php the_upformlabel('Кількість'); ?>
	       <td>
	         <input type=number name="quantity" value="<?php echo 	$parcelitem['quantity']; ?>" />
	       </td>
	     </tr>

    	<tr>
	        <?php the_upformlabel('Опис'); ?>
	       <td>
	         <textarea name="description"> <?php echo 	$parcelitem['description']; ?></textarea>
	       </td>
	     </tr>

	     <tr>
	        <?php the_upformlabel('Маса1'); ?>
	       <td>
	       	 <input type=text name="weight" value="<?php echo 	$parcelitem['weight']; ?>" />
	       </td>
	     </tr>

	     <tr>
	       <?php the_upformlabel('Оголошена вартість'); ?>
	       <td>
	         <input id="invoice_priceid" type="text" name="declaredPrice" value="<?php echo $parcelitem['value']; ?>" >
	       </td>
	     </tr>
       <tr>
	       <?php the_upformlabel('Оголошена вага, грам'); ?>
	       <td>
	         <input id="invoice_priceid" type="text" name="parcelweight" value="<?php echo $parcelitem['weight']; ?>" >
	       </td>
	     </tr>
	     <tr>
	       <?php the_upformlabel('Країна походження'); ?>
	       <td>
	         <input id="invoice_priceid" type="text" name="countryOfOrigin" value="<?php echo $parcelitem['countryOfOrigin']; ?>" >
	       </td>
	     </tr>

    <?php
	 $index++;
	 } ?>







     <tr>
      <td>
        <input class="wpbtn button button-primary" type="submit" name="update" value="Оновити">
      </td>
     </tr>
      </table>

      <?php
    }
     ?>







  </div>
  <div class="">
    <?php require 'card.php'; ?>
  </div>
</form>


<?php

echo '<pre>';
//print_r($getinfo);
echo '</pre>';
