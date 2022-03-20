#!/usr/bin/php
<?php

function addNewsToFile($stocks)
{
	$queryString = http_build_query([
		'api_token' => file_get_contents("keys.json"),
		'symbols' => implode(',', $stocks),
		'filter_entities' => 'true',
		'limit' => 2,
	]);

	$ch = curl_init(sprintf('%s?%s', 'https://api.stockdata.org/v1/news/all', $queryString));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$json = curl_exec($ch);
	$apiResult = json_decode($json, true);

	curl_close($ch);

	file_put_contents(date("Y-m-d")."_news.json", $json, FILE_APPEND);
}

function generateAllDailyNews($stocks)
{
	
	$c = count($stocks);
	
	for ($i = 0; $i < $c; $i += 3)
	{
		$slice = array_slice($stocks, $i, $i + min(3, $c - $i));
		
		addNewsToFile($slice);
		
		/*
		var_dump($i);
		printf("/");
		var_dump($i + min(3, $c - $i));
		printf("\n");
		*/
		
	}
	
	return $c;
	
}

?>
