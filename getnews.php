#!/usr/bin/php
<?php
$outfile = date("Y-m-d")."_news.json";
$keys = json_decode(file_get_contents("keys.json"));

function getKey($index)
{
	global $keys;
	return $keys[$index % count($keys)];
}

function addNewsToFile($stock, $key)
{
	global $outfile;
	
	$queryString = http_build_query([
		'api_token' => $key,
		'symbols' => $stock,
		'filter_entities' => 'true',
		'limit' => 2,
		'language' => 'en'
	]);

	$ch = curl_init(sprintf('%s?%s', 'https://api.stockdata.org/v1/news/all', $queryString));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$apiResult = json_decode(curl_exec($ch), true)["data"];

	curl_close($ch);
	
	$json = "\"".$stock."\":[";
	
	foreach($apiResult as $i => $article)
	{
		if ($i != 0) $json .= ", ";
		
		$json .= json_encode(
			array(
				'link' => $article["url"],
				'title' => $article["title"],
				'description' => $article["description"],
				'snippet' => $article["snippet"],
				'image' => $article["image_url"],
				'date' => $article["published_at"]
			)
		);
	}
	
	$json .= "]";

	file_put_contents($outfile, $json, FILE_APPEND);
}

function generateAllDailyNews($stocks)
{
	global $outfile;
	
	$c = count($stocks);
	
	file_put_contents($outfile, "{");
	
	for ($i = 0; $i < $c; $i ++)
	{
		if ($i != 0) file_put_contents($outfile, ",", FILE_APPEND);
		
		addNewsToFile($stocks[$i], getKey($i));
		
	}
	
	file_put_contents($outfile, "}", FILE_APPEND);
	
	return $c;
	
}

?>
