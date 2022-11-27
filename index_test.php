<?php
//header('Content-Type: text/xml');
require "functions.php";
require "errors.php";

$from = $_GET["from"];
$to = $_GET["to"];
$amount = $_GET["amnt"];

$converted_value = ($amount / $from_rate * $to_rate); // TODO round to 2dp

$xml = simplexml_load_file("response.xml");

if (!check_files_exist()) {
    $rates = call_api();
    build_xml($rates);
    //reload string incase file doesn't exist!
    $xml = simplexml_load_file("response.xml");
} else {
    if (check_rates_age($xml) == true) {
        $rates = call_api();
        update_rates($xml, $rates);
    } else {
        echo "Rates do not need updating!";
    }
}




foreach ($xml->currency as $currency) {
    if ($currency->code == $from) {
        $from_rate = $currency["rate"];
    }
    if ($currency->code == $to) {
        $to_rate = $currency["rate"];
    }
}

echo "From: " . $from . " Rate: " . $from_rate . " To: " . $to . " To rate: " . $to_rate;

$converted_value = ($amount / $from_rate * $to_rate); // TODO round to 2dp
echo " Converted rate: " . $converted_value;