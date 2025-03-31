<?php
header("Content-type:application/pdf");
//header("filename=ttn.pdf");//deprecated on some hostings
if(isset($_POST['download'])){
    header("Content-disposition: attachment; filename=ttn.pdf");
}

header("Content-disposition: inline; filename=ttn.pdf");

require("api.php");

if(isset($_POST['bearer']) && isset($_POST['cp_token'])) {
  $token = $_POST['bearer'];
  $cptoken = $_POST['cp_token'];
  $ttn = $_POST['ttn'];

  $type = $_POST['type'];
  $size='';
  if($type=='1'){
    $size = '&size=SIZE_A4';
  }
  else if($type=='2'){
    $size = '&size=SIZE_A5';
  }
  else{
    $size='';
  }

  $url = 'https://www.ukrposhta.ua/ecom/0.0.1/shipments/'.$ttn.'/sticker?token='.$cptoken.$size;

  $formurl = 'https://www.ukrposhta.ua/forms/ecom/0.0.1/';
  

  $authorization = "Authorization: Bearer ".$token;

  $cur = curl_init($url);
  curl_setopt( $cur, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($cur, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); // Inject the token into the header
  $html = curl_exec( $cur );
  curl_close ( $cur );
  print_r($html);

}

else{
//  echo '<script>window.close();</script>';
  print_r($_POST);
}
