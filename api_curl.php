$queryString = http_build_query([
    'api_token' => 'token',
    'symbols' => 'AAPL,MSFT, AMZN',
    'filter_entities' => 'true',
    'limit' => 50,
]);

$ch = curl_init(sprintf('%s?%s', 'https://api.stockdata.org/v1/news/all', $queryString));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$json = curl_exec($ch);

curl_close($ch);

$apiResult = json_decode($json, true);

print_r($apiResult);
