<?php

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

//setting rates from query string codes
foreach ($xml->currency as $currency) {
    if ($currency->code == $from) {
        $from_rate = $currency["rate"];
        $from_code = $currency->code;
        $from_currency_name = $currency->curr;
        $from_currency_location = $currency->loc;
    } else if ($currency->code == $to) {
        $to_rate = $currency["rate"];
        $to_code = $currency->code;
        $to_currency_name = $currency->curr;
        $to_currency_location = $currency->loc;
    }
}

$timestamp = intval($xml["timestamp"]);
$timestamp = gmdate("F j, Y, g:i:s a", $timestamp);
//do currency conversion - round to 2dp
$rate = number_format(($amount / $from_rate), 2);
$converted_value = number_format(($rate * $to_rate), 2);

//echo " Converted rate: " . $converted_value;

$error_code = check_query_string($_GET);

if ($error_code != null) {
    header('Content-Type: text/xml');
    $doc = new DOMDocument();
    $doc->formatOutput = true;
    $doc->loadXML($doc->load("errors.xml"));
    $xpath = new DOMXpath($doc);
    $xpath_query = ".//errors//" . $error_code . "/node()";
    $elements = $xpath->query($xpath_query);
    if ($elements) {
        foreach ($elements as $item) {
            echo $doc->saveXML($item), "\n";
        }
    }
    exit();
}


header('Content-Type: text/xml');
$xmlstr = <<<XML
<?xml version='1.0' standalone='yes'?>
<conv></conv>
XML;

$sxe = new SimpleXMLElement($xmlstr);

$at = $sxe->addChild('at', $timestamp);
$rate = $sxe->addChild("rate", $rate);

$from_element = $sxe->addChild("from");
$from_code_element = $from_element->addChild("code", $from_code);
$from_currency_element = $from_element->addChild("curr", $from_currency_name);
$from_loc_element = $from_element->addChild("loc", $from_currency_location);
$from_amnt_element = $from_element->addChild("amnt", $amount);

$to_element = $sxe->addChild("to");
$to_code_element = $to_element->addChild("code", $to_code);
$to_currency_element = $to_element->addChild("curr", $to_currency_name);
$to_loc_element = $to_element->addChild("loc", $to_currency_location);
$to_amnt_element = $to_element->addChild("amnt", $converted_value);




echo ($sxe->asXML());

die();