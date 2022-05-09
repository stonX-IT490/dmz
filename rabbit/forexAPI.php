#!/usr/bin/php 
<?php 
require_once __DIR__ . "/rabbitmq-dmzHost/rabbitMQLib.php";
 
function getForexData($currencies) {     
  $apiURL = "https://www.currency-api.com/rates?base="; 

  $forexArr = [];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  for ($i = 0; $i < count($currencies); $i++) {
    curl_setopt($ch, CURLOPT_URL, $apiURL.$currencies[$i]); 
    $apiresult = json_decode(curl_exec($ch), true);

    foreach ($apiresult["rates"] as $exchangeCurr => $rate) {
      array_push($forexArr, [
        "source" => $currencies[$i],
        "destination" => $exchangeCurr,
        "rate" => $rate
      ]);
    }
  }

  curl_close($ch);
  return $forexArr; 
} 

$client = new rabbitMQProducer('amq.direct', 'dmz');
$response = $client->send_request([ 'type' => 'getAllCurrencies' ]);

if(!$response) {
  die("Error comm. with RMQ!\n");
} else if (isset($response['error']) && $response['error'])  {
  die($response['msg']."\n");
}

$response = $client->send_request(["type" => "insertForex", "data" => getForexData($response)]);

if(!$response) {
  die("Error comm. with RMQ!\n");
} else if (isset($response['error']) && $response['error'])  {
  die($response['msg']."\n");
}
die();

?>

