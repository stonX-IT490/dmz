<?php

include(__DIR__."/kese.inc");

//insert array here as $givenArray. For now is hard coded, but will be made to set the requested symbols as the given aray
$givenArray = array(
  "AAPL",
  "ABBV",
  "ABT",
  "ACN",
  "ADBE",
  "AIG",
  "AMGN",
  "AMT",
  "AMZN",
  "AVGO",
  "AXP",
  "BA",
  "BAC",
  "BK",
  "BKNG",
  "BLK",
  "BMY",
  "BRK.B",
  "C",
  "CAT",
  "CHTR",
  "CL",
  "CMCSA",
  "COF",
  "COP",
  "COST",
  "CRM",
  "CSCO",
  "CVS",
  "CVX",
  "DD",
  "DHR",
  "DIS",
  "DOW",
  "DUK",
  "EMR",
  "EXC",
  "F",
  "FB",
  "FDX",
  "GD",
  "GE",
  "GILD",
  "GM",
  "GOOG",
  "GOOGL",
  "GS",
  "HD",
  "HON",
  "IBM",
  "INTC",
  "JNJ",
  "JPM",
  "KHC",
  "KO",
  "LIN",
  "LLY",
  "LMT",
  "LOW",
  "MA",
  "MCD",
  "MDLZ",
  "MDT",
  "MET",
  "MMM",
  "MO",
  "MRK",
  "MS",
  "MSFT",
  "NEE",
  "NFLX",
  "NKE",
  "NVDA",
  "ORCL",
  "PEP",
  "PFE",
  "PG",
  "PM",
  "PYPL",
  "QCOM",
  "RTX",
  "SBUX",
  "SCHW",
  "SO",
  "SPG",
  "SPY",
  "T",
  "TGT",
  "TMO",
  "TMUS",
  "TSLA",
  "TXN",
  "UNH",
  "UNP",
  "UPS",
  "USB",
  "V",
  "VZ",
  "WBA",
  "WFC",
  "WMT",
  "XOM"
);

$tokenValue = getToken(); //token for api

$seconds = 1; //seconds between each loop is done. avoids 30/sec and 60/min request limit from api

$apiResult = array(); //empty array for the final result. When finished, will be an array of arraysa with the data needed and be sent.

//__________________for loop over given symbol array starts here____________________________________
foreach ($givenArray as &$symb){

$xSymbol = $symb; //replace AAPL with givenArray[x] from loop


//sets up url to curl. Takes token and symbol to build http tags for end of link
$queryString = http_build_query([
    'token' => $tokenValue,
    'symbol' => $xSymbol
]);

//takes the complete http adress from the link+queryString, and starts curl session with the results. saves session as ch
$ch = curl_init(sprintf('%s?%s', 'https://finnhub.io/api/v1/quote', $queryString));

//set options for curl transfer
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//save results of a curl session as json variable
$json = curl_exec($ch);

//close the curl session
curl_close($ch);

//decode results of curl session (the resulting string), making it a php readable array
$apiResultfull = json_decode($json, true);

//take results of the array, and convert into the proper format for our database use
$apiResultsmall = array('symbol' => $xSymbol, 'value' => $apiResultfull['c'], 'dateTime' => $apiResultfull['t']);

//$apiResult array variable that was established earlier. through each loop, the formatted array is added.
$apiResult[] = $apiResultsmall;

sleep($seconds); //wait seconds to avoid 30req per second and 60 req per minute limit

}
//__________________for loop over given symbol array ends here____________________________________

print_r($apiResult);//print/send full php array of data


?>
