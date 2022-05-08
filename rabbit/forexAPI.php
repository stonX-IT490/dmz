#!/usr/bin/php 
 
<?php 
 
function getForexData() 
{ 
	
		$currencies = [
			"AUD",
			"BGN",
			"BRL",
			"CAD",
			"CHF",
			"CNY",
			"CZK",
			"DKK",
			"EUR",
			"GBP",
			"HKD",
			"HRK",
			"HUF",
			"IDR",
			"ILS",
			"INR",
			"ISK",
			"JPY",
			"KRW",
			"MXN",
			"MYR",
			"NOK",
			"NZD",
			"PHP",
			"PLN",
			"RON",
			"SEK",
			"SGD",
			"THB",
			"TRY",
			"USD",
			"ZAR"
		];
         
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
			
			array_push($forexArr["rates"], array_values($apiresult["rates"])); 
			
		} 
 
        return $forexArr; 
 
} 

?>

