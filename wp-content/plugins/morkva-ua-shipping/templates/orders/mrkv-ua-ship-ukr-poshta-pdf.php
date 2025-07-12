<?php
header("Content-type:application/pdf");
header("Content-disposition: attachment; filename=ttn.pdf");
header("Content-disposition: inline; filename=ttn.pdf");

if(isset($_POST['bearer']) && isset($_POST['cp_token'])) 
{
    $token = $_POST['bearer'];
    $cptoken = $_POST['cp_token'];
    $ttn = $_POST['invoice_number'];
  
    $type = $_POST['type'];
    $size='';
  
    if($type=='100*100A4')
    {
      $size = '&size=SIZE_A4';
    }
    elseif($type =='100*100A5')
    {
      $size = '&size=SIZE_A5';
    }
  
    $url = 'https://www.ukrposhta.ua/ecom/0.0.1/shipments/' . $ttn . '/sticker?token=' . $cptoken . $size;
  
    $formurl = 'https://www.ukrposhta.ua/forms/ecom/0.0.1/';
  
      if(isset($_POST['fs1']))
      {
          if($_POST['fs1'] == 'forms' || $_POST['fs1'] == '100x100')
          {
            $url = $formurl . '/international/shipments/' . $ttn . '/' . 'forms' . '?token=' . $cptoken . '&size=SIZE_10X10';
          }
          else
          {
            $url = $formurl . '/international/shipments/' . $ttn .' /' . $_POST['fs1'] . '?token=' . $cptoken;
          }
    }

    $authorization = "Authorization: Bearer ".$token;

    $cur = curl_init($url);
    curl_setopt( $cur, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($cur, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization )); 
    $html = curl_exec( $cur );
    curl_close ( $cur );
    print_r($html);
}