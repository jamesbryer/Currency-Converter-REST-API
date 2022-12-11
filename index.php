<?php

require "functions.php";

$from = strtoupper($_GET["from"]);
$to = strtoupper($_GET["to"]);
$amount = $_GET["amnt"];

if (!isset($_GET["format"])) {
    $_GET["format"] = "xml";
}


//if the files doesn't exist - build it
if (!check_files_exist()) {
    $rates = call_api();
    build_xml($rates);
    //reload string after rebuild!
    $xml = simplexml_load_file("response.xml");
} else {
    //check age of rates and update if neccessary
    $xml = simplexml_load_file("response.xml");
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

//check for errors in query string
$error_code = check_query_string($_GET);

//if there is an error within the query string, build and display error
if ($error_code != null) {
    //a bit of housekeeping - if there's an error in the format type, set back to defualt of xml
    if ($error_code == "1400") {
        $_GET["format"] = "xml";
    }
    //output response of create_error function in format described by query string
    output_response($_GET["format"], create_error($error_code));
    exit(); //exit to stop script running
}

//do currency conversion - round to 2dp
$rate = $from_rate * $to_rate;
$converted_value = round(($amount / $from_rate) * $to_rate, 2);

//retrieve timestamp and convert to a readable format
$timestamp = gmdate("F j, Y, g:i:s a", intval($xml["timestamp"]));



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

output_response($_GET["format"], $sxe);