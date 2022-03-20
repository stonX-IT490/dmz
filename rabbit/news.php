#!/usr/bin/php
<?php
require_once __DIR__ . "/rabbitmq-dmzHost/rabbitMQLib.php";

$client = new rabbitMQProducer('amq.direct', 'dmz');
$response = $client->send_request([ 'type' => 'getAllStocks' ]);

if(!$response) {
  die("Error comm. with RMQ!\n");
} else if (isset($response['error']) && $response['error']) {
  die($response['msg']."\n");
}

$outfile = __DIR__."/../news/".date("Y-m-d")."_news.json";
$keys = json_decode(file_get_contents(__DIR__."/../keys.json"))->stockdata;

function getKey() {
	global $keys;
	return $keys[ rand(0, count($keys) - 1) ];
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

function getNewsForSymbol($stock, $key) {
  global $ch;

	$queryString = http_build_query([
		'api_token' => $key,
		'symbols' => $stock,
		'filter_entities' => 'true',
    'must_have_entities' => 'true',
		'limit' => 2,
		'language' => 'en'
	]);

  curl_setopt($ch, CURLOPT_URL, sprintf('%s?%s', 'https://api.stockdata.org/v1/news/all', $queryString));

	$apiResult = json_decode(curl_exec($ch), true);
	
  $articles = [];

  if( isset($apiResult["data"]) && count($apiResult["data"]) != 0 ) {
    foreach($apiResult["data"] as $article) {
      $articles[] = [
        'link' => $article["url"],
        'title' => $article["title"],
        'description' => $article["description"],
        'snippet' => $article["snippet"],
        'image' => $article["image_url"],
        'date' => $article["published_at"]
      ];
    }
  }

	return $articles;
}

function generateAllDailyNews($stocks) {
	global $outfile;
  global $ch;
	
  $json = [];

	foreach($stocks as $stock) {
    echo $stock.'...';
    $json[$stock] = getNewsForSymbol($stock, getKey());
    echo " Done.\n";
  }

	curl_close($ch);
	file_put_contents($outfile, json_encode($json));
}

generateAllDailyNews($response);

?>