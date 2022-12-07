<?php

require "functions.php";
require "errors.php";

$from = strtoupper($_GET["from"]);
$to = strtoupper($_GET["to"]);
$amount = number_format($_GET["amnt"], 2);

if (isset($_GET["format"])) {
    $format = $_GET["format"];
} else {
    $format = "xml";
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

//retrieve timestamp and convert to a readable format
$timestamp = intval($xml["timestamp"]);
$timestamp = gmdate("F j, Y, g:i:s a", $timestamp);

//do currency conversion - round to 2dp
$rate = $from_rate * $to_rate;
$converted_value = ($amount / $from_rate) * $to_rate;

//check for errors in query string
$error_code = check_query_string($_GET);

//if there is an error within the query string, build and display error
if ($error_code != null) {

    $doc = new DOMDocument();
    $doc->formatOutput = true;
    $root = $doc->createElement("conv");
    $root = $doc->appendChild($root);
    $error_element = $doc->createElement("error");
    $error_element = $root->appendChild($error_element);

    foreach (ERROR_CODES_AND_MESSAGES as $code => $message) {
        if ($code == $error_code) {
            $code_element = $doc->createElement("code", $code);
            $code_element = $error_element->appendChild($code_element);

            $msg_element = $doc->createElement("msg", $message);
            $msg_element = $error_element->appendChild($msg_element);
            break;
        }
    }

    if ($error_code == "1400") {
        $format = "xml";
    }

    output_response($format, $doc);
    exit();
}



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

output_response($format, $sxe);
