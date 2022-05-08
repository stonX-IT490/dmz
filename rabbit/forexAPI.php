#!/usr/bin/php 
 
<?php 
//require_once __DIR__ . "/rabbitmq-dmzHost/rabbitMQLib.php";

 
function getForexData($currencies) 
{ 
         
	$apiURL = "https://www.currency-api.com/rates?base="; 

	$forexArr = array();
	$forexArr["currencies"] = $currencies;
	$forexArr["rates"] = array();

	for ($i = 0; $i < count($currencies); $i++)
	{

		$ch = curl_init();  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_URL, $apiURL.$currencies[$i]); 

		$apiresult = json_decode(curl_exec($ch), true);

		//array_push($forexArr["rates"], array_values($apiresult["rates"])); 

		foreach ($apiresult["rates"] as $exchangeCurr => $rate)

			array_push($forexArr["rates"], 
				array(
					"source" => $currencies[$i],
					"destination" => $exchangeCurr,
					"rate" => $rate
			)); 

	} 

	return $forexArr; 
 
} 

$client = new rabbitMQProducer('amq.direct', 'dmz');
$response = $client->send_request([ 'type' => 'getAllCurrencies' ]);

if(!$response) 
{
	die("Error comm. with RMQ!\n");
} 

else if (isset($response['error']) && $response['error']) 
{
	die($response['msg']."\n");
}

$client->publish(["type" => "insertForex", "data" => getForexData($response)]);

?>

