<?php
//header('Content-Type: text/xml');
require "functions.php";
require "errors.php";

$from = $_GET["from"];
$to = $_GET["to"];
$amount = $_GET["amnt"];

$converted_value = ($amount / $from_rate * $to_rate); // TODO round to 2dp

$xml = simplexml_load_file("response.xml");

//if the files doesn't exist - build it
if (!check_files_exist()) {
    $rates = call_api();
    build_xml($rates);
    //reload string after rebuild!
    $xml = simplexml_load_file("response.xml");
} else {
    //check age of rates and update if neccessary
    if (check_rates_age($xml) == true) {
        $rates = call_api();
        update_rates($xml, $rates);
    }
}

foreach ($xml->currency as $currency) {
    if ($currency->code == $from) {
        $from_rate = $currency["rate"];
    } else if ($currency->code == $to) {
        $to_rate = $currency["rate"];
    }
}

$timestamp = intval($xml["timestamp"]);
echo gmdate("F j, Y, g:i a", $timestamp);
//do currency conversion - round to 2dp
$converted_value = number_format(($amount / $from_rate * $to_rate), 2);
echo " Converted rate: " . $converted_value;