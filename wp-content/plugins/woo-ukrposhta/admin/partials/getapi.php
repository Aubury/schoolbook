<?

function get_info($token, $url){

  $ch = curl_init($url);

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  curl_setopt($ch, CURLOPT_HEADER, 0);

  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

  curl_setopt($ch, CURLOPT_HEADER, 0);

  curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );

  //curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);

  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token ) );

  $result = curl_exec($ch);

  curl_close($ch);

  $jd = json_decode($result);

  if( (isset($jd->errors->code) )&& ($jd->errors->code == 1020) ){
      autorization();
      $i++;
      if($i<2){
        $result = get_info($token, $url);
      }
  }

    return $result;
    //echo '</pre>';


}
header("Content-type: application/pdf");
header("Content-Disposition: inline; filename=filename.pdf");
@readfile('path\to\filename.pdf');


if(isset($_GET['international'])){
  echo get_info("1a14715b-4341-3b36-8130-e439b493773e", "https://www.ukrposhta.ua/ecom/0.0.1/international-doc");
}
else{
  echo get_info("1a14715b-4341-3b36-8130-e439b493773e", "https://www.ukrposhta.ua/ecom/0.0.1/doc");
}

?>
