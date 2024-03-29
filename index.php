<?php
include "conf.php";
require "functions.php";

//checks if format is set in query string, if not set to xml
if (!isset($_GET["format"])) {
    $_GET["format"] = "xml";
}

//check files exist - if they don't, make them
check_base_files();

//check for errors in query string
$error_code = check_query_string($_GET);

//if there is an error within the query string, build and display error
if ($error_code != null) {
    //a bit of housekeeping - if there's an error in the format type, set back to defualt of xml -
    //this allows errors to be returned as JSON data as well as XML
    if ($error_code == "1400") {
        $_GET["format"] = "xml";
    }
    //output response of create_error function in format described by query string
    output_response($_GET["format"], create_error($error_code));
    exit(); //exit to stop script running
}

//load response.xml file
$xml = simplexml_load_file(OUTPUT_FILENAME_ROOT) or die("Cannot load file");

//setting data for query string codes from response.xml
foreach ($xml->currency as $currency) {
    //create break condition
    if ($from_rate != null and $to_rate != null) {
        break;
    }
    //if codes match, set data
    if ($currency->code == strtoupper($_GET["from"])) {
        $from_rate = $currency["rate"];
        $from_code = $currency->code;
        $from_currency_name = $currency->curr;
        $from_currency_location = $currency->loc;
    } else if ($currency->code == strtoupper($_GET["to"])) {
        $to_rate = $currency["rate"];
        $to_code = $currency->code;
        $to_currency_name = $currency->curr;
        $to_currency_location = $currency->loc;
    }
}

//do currency conversion - round to 2dp
$rate = $from_rate * $to_rate;
$converted_value = round(($_GET["amnt"] / $from_rate) * $to_rate, 2);

//retrieve timestamp and convert to a readable format
$timestamp = gmdate("F j, Y, g:i:s a", intval($xml["timestamp"]));

//build response using data retrieved from response.xml and a simplexmlelement
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
$from_amnt_element = $from_element->addChild("amnt", $_GET["amnt"]);

$to_element = $sxe->addChild("to");
$to_code_element = $to_element->addChild("code", $to_code);
$to_currency_element = $to_element->addChild("curr", $to_currency_name);
$to_loc_element = $to_element->addChild("loc", $to_currency_location);
$to_amnt_element = $to_element->addChild("amnt", $converted_value);

//output response in format specified by query string
output_response($_GET["format"], $sxe);