#!/usr/bin/php
<?php

function getTodaysNews($stock)
{
	
	$outfile = date("Y-m-d")."_news.json";
	$allnewstoday = json_decode(file_get_contents($outfile));
	
	return $allnewstoday[$stock];
}

?>