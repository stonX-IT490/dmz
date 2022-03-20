<?php

require_once __DIR__ . "/rabbitmq-dmzHost/rabbitMQLib.php";

$client = new rabbitMQProducer('amq.direct', 'dmz');

$response = $client->send_request([ 'type' => 'getAllStocks' ]);
if(!$response) {
  die("Error comm. with RMQ!\n");
} else if (isset($response['error']) && $response['error']) {
  die($response['msg']."\n");
}

$key = json_decode(file_get_contents(__DIR__."/../keys.json"))->finnhub;

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$stockData = [];

foreach($response as $symbol) {
  echo "$symbol...";
  $queryString = http_build_query([
    'token' => $key,
    'symbol' => $symbol
  ]);
  curl_setopt($ch, CURLOPT_URL, sprintf('%s?%s', 'https://finnhub.io/api/v1/quote', $queryString));
  $apiResult = json_decode(curl_exec($ch), true);
  $stockData[] = [
    'symbol' => $symbol,
    'value' => $apiResult['c'],
    'created' => date("Y-m-d H:m:s", $apiResult['t'])
  ];
  echo "Done.\n";
  sleep(1);
}

curl_close($ch);

$client->publish([ 'type' => 'insertStocks', 'data' => $stockData ]);
die();

?>
