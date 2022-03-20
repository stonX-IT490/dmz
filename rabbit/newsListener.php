#!/usr/bin/php
<?php

set_error_handler(function($errno, $errstr, $errfile, $errline ){
  throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
  die();
});

require_once __DIR__ . "/rabbitmq-webDmzHost/rabbitMQLib.php";

function getTodaysNews($request) {
  if (!isset($request['symbol'])) {
    return [ 'error' => true, 'msg' => 'ERROR RMQ: Parameter not set!' ];
  }
  $stock = $request['symbol'];
  $outfile = __DIR__."/../news/".date("Y-m-d")."_news.json";
  try {
    $allnewstoday = json_decode(file_get_contents($outfile));
    return $allnewstoday->$stock;
  } catch(Exception $e) {
    return [];
  }
}

$server = new rabbitMQConsumer('amq.direct', 'news');
$server->process_requests('getTodaysNews');

?>
