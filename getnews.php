#!/usr/bin/php
<?php

$newslimit = 2;
$outfile = date("Y-m-d")."_news.json";

function addNewsToFile($stocks)
{
	global $newslimit;
	global $outfile;
	
	$queryString = http_build_query([
		'api_token' => file_get_contents("keys.json"),
		'symbols' => implode(',', $stocks),
		'filter_entities' => 'true',
		'limit' => $newslimit,
	]);

	$ch = curl_init(sprintf('%s?%s', 'https://api.stockdata.org/v1/news/all', $queryString));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$json = curl_exec($ch);
	$apiResult = json_decode($json, true);

	curl_close($ch);

	file_put_contents($outfile, $json, FILE_APPEND);
}

function generateAllDailyNews($stocks)
{
	global $newslimit;
	global $outfile;
	
	$c = count($stocks);
	
	file_put_contents($outfile, "[");
	
	for ($i = 0; $i < $c; $i += $newslimit)
	{
		if ($i != 0) file_put_contents($outfile, ",", FILE_APPEND);
		
		$slice = array_slice($stocks, $i, $i + min($newslimit, $c - $i));
		
		addNewsToFile($slice);
		
		/*
		var_dump($i);
		printf("/");
		var_dump($i + min(3, $c - $i));
		printf("\n");
		*/
		
	}
	
	file_put_contents($outfile, "]", FILE_APPEND);
	
	return $c;
	
}

?>
