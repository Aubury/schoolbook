<?php
header("Content-type: application/pdf");
header("Content-Disposition: inline; filename=filename.pdf");
@readfile('path\to\filename.pdf');

# Make Shipment Group
$apiGroup = "https://www.ukrposhta.ua/ecom/0.0.1/shipment-groups";
$resGroup = '';

if ( isset( $_POST['bearer'] ) && isset( $_POST['cp_token'] ) ) {

  $bearer = $_POST['bearer'];
  $cptoken = $_POST['cp_token'];
  $tokenQuery = "?token=" . $cptoken;

  $urlGr = $apiGroup . $tokenQuery;
  $newgroup = "Mrkv Group " . gmdate("Y-m-d H:i:s");
  $type = $_POST['sendtype'];
  $resGroup = makeShipmentGroup( $bearer, $urlGr, $newgroup, $type ); 

  # Add Shipment to Shipment Group
  $jsonGroup = json_decode($resGroup);  
  $uuidGroup = $jsonGroup->uuid;

  if ( ! empty( $_POST['mrkv_ua_hidden_group_actions'] )) {
    $selectedInvoices = explode( ",", $_POST['mrkv_ua_hidden_group_actions']);
    foreach( $selectedInvoices as $selectedInvoice ) {
    $urlShToGr = $apiGroup . "/" . $uuidGroup . "/shipments/" . $selectedInvoice . $tokenQuery;

      $resShToGr = addInvoiceToShipmentGroup( $bearer, $urlShToGr );

    }
  }

  $apiForm = "https://www.ukrposhta.ua/forms/ecom/0.0.1/shipment-groups/";
  $urlShow103a = $apiForm . $uuidGroup . '/form103a' . $tokenQuery;
  $form103a = showBulkPrintForm103a( $bearer, $urlShow103a );

  echo $form103a;

}

function makeShipmentGroup( $bearer, $url, $group, $type ) {

  $bodyRow = json_encode( array( "name" => $group, "type" => $type ) );

  $curl = curl_init($url);

  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_VERBOSE => true,
    CURLOPT_STDERR => $fp,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $bodyRow,
    CURLOPT_HTTPHEADER => array(
      "Content-Type: application/json",
      "Authorization: Bearer Bearer " . $bearer
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  return $response;

}

function addInvoiceToShipmentGroup( $bearer, $url ) {

  $curl = curl_init($url);

  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_VERBOSE => true,
    CURLOPT_STDERR => $fp,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_HTTPHEADER => array(
      "Content-Type: application/json",
      "Authorization: Bearer Bearer " . $bearer
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  return $response;

}

function showBulkPrintForm103a( $bearer, $url ) {
  $curl = curl_init($url);

  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_VERBOSE => true,
    CURLOPT_STDERR => $fp,    
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
    CURLOPT_HTTPHEADER => array(
      "Content-Type: application/json",
      "Authorization: Bearer Bearer " . $bearer
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
  return $response;
}

?>
