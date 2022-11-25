<?php
//header('Content-Type: text/xml');
require "functions.php";
require "errors.php";

$params = array("to", "from", "amnt", "format");

//check all required parameters are set
if (!isset($_GET["from"]) and !isset($_GET["to"]) and !isset($_GET["amnt"])) {
    $error_1000 = true;
}

//check all paramters are valid
foreach ($_GET as $param => $value) {
    if (!in_array($param, $params)) {
        $error_1100 = true;
    }
}
//get list of currencies
$currencies = get_array_of_currencies();

//check currencies are valid
if (!in_array($from, $currencies) and !in_array($to, $currencies)) {
    $error_1200 = true;
}

//check that amount parameter is a decimal
if (!is_float($_GET["amnt"])) {
    $error_1300 = true;
}

//set correct output format and check whether it is valid
if (isset($_GET["format"])) {
    if ($_GET == "json") {
        $format = "json";
    } elseif ($_GET["format"] == "xml") {
        $format = "xml";
    } else {
        $error_1400 = true;
    }
} else {
    $format = "xml";
}

$from = $_GET["from"];
$to = $_GET["to"];
$amount = $_GET["amnt"];
echo "FROM:  " . $from . " TO: " . $to . " AMOUNT: " . $amount;

$converted_value = ($amount / $from * $to); // then round to 2dp

if (!check_files_exist()) {
    $rates = call_api();
    build_xml($rates);
} else {
    $xml = simplexml_load_file("response.xml");
    if (check_rates_age($xml) == true) {
        $rates = call_api();
        update_rates($xml, $rates);
    } else {
        echo "Rates do not need updating!";
    }
}