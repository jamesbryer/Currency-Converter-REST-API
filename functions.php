<?php

include "conf.php";


//checks timestamp of rates, if over 12 hours old returns true
function check_rates_age($xml)
{
    //gets current Unix timestamp and timestamp from XML document - if document is older than 12 hours, calls update rates function
    $current_time = time();
    if (($current_time - $xml['timestamp']) > UPDATE_INTERVAL) {
        return true;
    } else {
        return false;
    }
}

//updates rates using fresh api data
function update_rates($xml, $rates, $output = OUTPUT_FILENAME_ROOT)
{
    //loops through each currency in xml file and finds corresponding currency in API data
    foreach ($xml->currency as $currency) {
        foreach ($rates as $code => $rate) {
            if ($code == $currency->code) {
                //sets rate in response.xml to rate from API data
                $currency["rate"] = $rate;
                //breaks out of inner loop
                break;
            }
        }
    }
    //updates timestamp in response.xml
    $xml["timestamp"] = time();
    //saves updated xml file
    $xml->asXML($output);
}

//calls fixer api to obtain rates - returns decoded JSON string
//endpoint, base currency and api key all set in conf.php
function call_api()
{
    //uses curl to make API call
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => API_ENDPOINT . BASE_CURRENCY,
        CURLOPT_HTTPHEADER => array(
            "Content-Type: text/plain",
            "apikey: " . CURRENCY_API_KEY
        ),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET"
    ));

    $response = curl_exec($curl);
    //decode json from API and insert into array
    $response = json_decode($response);
    $rates = $response->rates;
    curl_close($curl);
    return $rates;
}

//creates xml file response.xml using iso_4217.xml obtained from webpage so as to always get most up to date list of currencies
//iso_4217.xml is not saved to server, only used to create response.xml
function build_xml($rates, $output_filename)
{
    //A script to check whether the output XML files exists and create it if it does not using the ISO file
    $xml = simplexml_load_file(ISO_FILE_URL);

    // Create a new dom document with pretty formatting
    $doc = new DomDocument();
    $doc->formatOutput = true;

    // Add a root node to the document called rates
    $root = $doc->createElement('rates');
    $root = $doc->appendChild($root);
    //create attribute of rates called timestamp and set its value to current Unix time
    $root_attribute = $doc->createAttribute('timestamp');
    $root_attribute->value = time();
    $root_attribute = $root->appendChild($root_attribute);
    //create attribute base and assign its value to base currency constant from conf.php
    $base_attribute = $doc->createAttribute('base');
    $base_attribute->value = BASE_CURRENCY;
    $base_attribute = $root->appendChild($base_attribute);


    //loop through api data as each country code and its current rate
    foreach ($rates as $api_code => $rate) {

        //declare new array to hold country names for each currency
        $countries_array = array();
        //loop through each country in xml document and add country to array if it uses the same currency
        $container = $doc->createElement("currency");
        foreach ($xml->CcyTbl->CcyNtry as $country) {
            $code = $country->Ccy;
            if ($api_code == $code) {
                $country_name = $country->CtryNm;
                array_push($countries_array, $country_name);
                $curr = $country->CcyNm;
            }
        }
        if ($countries_array != null) {
            //implode the array with a comma as a separator 
            $countries_string = implode(", ", $countries_array);
            //set all words to lowercase
            $countries_string = strtolower($countries_string);
            //set every word to have 1st letter uppercase
            $countries_string = ucwords($countries_string);
            //set every word after an "(" to uppercase
            $countries_string = ucwords($countries_string, "(");

            if (in_array($api_code, LIVE_CURRENCIES)) {
                $status = "1";
            } else {
                $status = "0";
            }

            //create element for currency code and set its value to the current code from api data
            $code_element = $doc->createElement("code", $api_code);
            $code_element = $container->appendChild($code_element);


            //create element to hold countries that use the currency and set its value from the for loop string
            $country_element = $doc->createElement("loc", $countries_string);
            $country_element = $container->appendChild($country_element);


            //create element for currency name and set using value from second foreach loop
            $currency_element = $doc->createElement("curr", $curr);
            $currency_element = $container->appendChild($currency_element);

            //create attribute for rate and set value from api data
            $rate_attribute = $container->setAttribute("rate", $rate);
            $rate_attribute = $container->appendChild($rate_attribute);

            //create attribute for whether the currency is live or not
            $live_attribute = $doc->createAttribute("live");
            $live_attribute->value = $status;
            $live_attribute = $container->appendChild($live_attribute);

            $root->appendChild($container);
        }
    }

    $strxml = $doc->saveXML();
    $handle = fopen($output_filename, "w");
    fwrite($handle, $strxml);
    fclose($handle);
}

//returns array of ALL currency codes
function get_array_of_currencies($filename = OUTPUT_FILENAME_ROOT)
{

    $xml = simplexml_load_file($filename);
    $currencies = array();

    //Push each currency code into an array
    foreach ($xml->currency as $currency) {
        $code = $currency->code;
        array_push($currencies, $code);
    }

    return $currencies;
}

//returns array of LIVE currency codes
function get_array_of_live_currencies($filename = OUTPUT_FILENAME_ROOT)
{

    $xml = simplexml_load_file($filename) or die("cannot load file");
    $currencies = array();

    //Pull each currency code into an array
    foreach ($xml->currency as $currency) {
        if ($currency["live"] == 1) {
            $code = $currency->code;
            array_push($currencies, $code);
        }
    }

    return $currencies;
}

//checks query string for API for validation
function check_query_string($get)
{
    //check all required parameters are set
    if (!isset($get["from"]) or !isset($get["to"]) or !isset($get["amnt"])) {
        return "1000";
    }

    $from = strtoupper($get["from"]);
    $to = strtoupper($get["to"]);
    $amount = $get["amnt"];

    //check all paramters are valid - looks at array const of allowed params in conf.php
    foreach ($get as $param => $value) {
        if (!in_array($param, PARAMS)) {
            return "1100";
        }
    }

    //check currencies are live
    $live_currencies = get_array_of_live_currencies();
    if (!in_array($from, $live_currencies) or !in_array($to, $live_currencies)) {
        return "1200";
    }

    //check that amount parameter is a decimal
    if (!is_numeric($amount)) {
        return "1300";
    }

    //set correct output format and check whether it is valid
    if (isset($get["format"])) {
        if (!in_array($get["format"], FORMATS)) {
            return "1400";
        }
    }
    return null;
}

//returns domdoc of error in correct format with code and message
function create_error($error_code)
{
    $doc = new DOMDocument("1.0", "UTF-8");
    $doc->formatOutput = true;
    $root = $doc->createElement("conv");
    $root = $doc->appendChild($root);
    $error_element = $doc->createElement("error");
    $error_element = $root->appendChild($error_element);
    //create error code element
    $code_element = $doc->createElement("code", $error_code);
    $code_element = $error_element->appendChild($code_element);
    //create error message element
    $msg_element = $doc->createElement("msg", ERROR_CODES_AND_MESSAGES[$error_code]);
    $msg_element = $error_element->appendChild($msg_element);
    return $doc;
}

//outputs a doc as either json or xml according to param set in query string
function output_response($format, $doc)
{
    if ($format == "json") {
        header('Content-Type: text/json');
        $json = new SimpleXMLElement($doc->saveXML());
        $json_response = json_encode(array("conv" => $json), JSON_PRETTY_PRINT);
        echo $json_response;
    } else if ($format = "xml") { //all other formats are xml - whether specifically set in query string or not
        header('Content-Type: text/xml');
        echo $doc->saveXML();
    }
}

//function to check response.xml exists, if it does not then create it - also updates rates if over 12 hours old
function check_base_files($filename = OUTPUT_FILENAME_ROOT)
{
    //if the files doesn't exist - build it
    if (!file_exists($filename)) {
        $rates = call_api();
        build_xml($rates, $filename);
    } else {
        //check age of rates and update if neccessary
        $xml = simplexml_load_file($filename);
        if (check_rates_age($xml) == true) {
            $rates = call_api();
            update_rates($xml, $rates);
        }
    }
}