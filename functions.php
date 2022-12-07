<?php

include "conf.php";

//checks timestamp of rates, if over 12 hours old returns true
function check_rates_age($xml)
{
    //gets current Unix timestamp and timestamp from XML document - if document is older than 12 hours, calls update rates function
    $current_time = time();
    if (($current_time - $xml['timestamp']) > 43200) {
        return true;
    } else {
        return false;
    }
}

//updates rates using fresh api data
function update_rates($xml, $rates)
{
    //loops through each currency in xml file and finds corresponding currency in API data
    foreach ($xml->currency as $currency) {
        foreach ($rates as $code => $rate) {
            if ($code == $currency->code) {
                $currency["rate"] = $rate;
                break;
            }
        }
    }
    $xml["timestamp"] = time();
    $xml->asXML("response.xml");
    //echo "Rates updated!";
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

//function to check whether ISO file exists - if it doesn't, download it - checks whether response.xml exists returns boolean value for this
function check_files_exist()
{
    $iso_file = "iso_4217.xml";
    $rates_file = "response.xml";
    if (!file_exists($rates_file)) {
        //Try to load the file, if the file does not exist, download it from the url 
        if (!file_exists($iso_file)) {
            file_put_contents($iso_file, file_get_contents(ISO_FILE_URL));
        }
        return false;
    } else {
        return true;
    }
}

//creates xml file response.xml using iso_4217.xml 
function build_xml($rates)
{
    //A script to check whether the output XML files exists and create it if it does not using the ISO file
    //UPDATE IN FINAL TO IF FILE DOESNT EXIST !!!! ONLY RUNNING LIKE THIS FOR TESTING

    $filename = "iso_4217.xml";
    $xml = simplexml_load_file($filename);

    // Create a new dom document with pretty formatting
    $doc = new DomDocument();
    $doc->formatOutput = true;
    $outputFilename = "response.xml";

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
            $country_name = $country->CtryNm;
            if ($api_code == $code) {
                array_push($countries_array, $country_name);
                $curr = $country->CcyNm;
            }
            //implode the array with a comma as a separator 
            $countries_string = implode(", ", $countries_array);
            //set all words to lowercase
            $countries_string = strtolower($countries_string);
            //set every word to have 1st letter uppercase
            $countries_string = ucwords($countries_string);
            //set every word after an "(" to uppercase
            $countries_string = ucwords($countries_string, "(");
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
        $rate_attribute = $doc->createAttribute("rate");
        $rate_attribute->value = $rate;
        $rate_attribute = $container->appendChild($rate_attribute);

        //create attribute for whether the 
        $live_attribute = $doc->createAttribute("live");
        $live_attribute->value = "1";
        $live_attribute = $container->appendChild($live_attribute);

        $root->appendChild($container);
    }

    $strxml = $doc->saveXML();
    $handle = fopen($outputFilename, "w");
    fwrite($handle, $strxml);
    fclose($handle);
    //echo "Rates updated!";
}

//returns array of currency codes
function get_array_of_currencies()
{
    $filename = "response.xml";
    $xml = simplexml_load_file($filename);
    $currencies = array();

    //Pull each currency code into an array
    foreach ($xml->currency as $currency) {
        $code = $currency->code;
        array_push($currencies, $code);
    }

    return $currencies;
}

function check_query_string($get)
{
    $params = array("to", "from", "amnt", "format");

    //check all required parameters are set
    if (!isset($get["from"]) or !isset($get["to"]) or !isset($get["amnt"])) {
        return "1000";
    }

    $from = strtoupper($get["from"]);
    $to = strtoupper($get["to"]);
    $amount = $get["amnt"];

    //check all paramters are valid
    foreach ($get as $param => $value) {
        if (!in_array($param, $params)) {
            return "1100";
        }
    }
    //get list of currencies
    $currencies = get_array_of_currencies();

    //check currencies are valid
    if (!in_array($from, $currencies) or !in_array($to, $currencies)) {
        return "1200";
    }

    //check that amount parameter is a decimal
    if (!is_numeric($amount)) {
        return "1300";
    }

    $formats = array("xml", "json");
    //set correct output format and check whether it is valid
    if (isset($get["format"])) {
        if (!in_array($get["format"], $formats)) {
            return "1400";
        }
    }
    return null;
}

function output_response($format, $doc)
{
    if ($format == "json") {
        header('Content-Type: text/json');
        $json = new SimpleXMLElement($doc->saveXML());
        $json_response = json_encode(array("conv" => $json), JSON_PRETTY_PRINT);
        echo $json_response;
    } else if ($format = "xml") {
        header('Content-Type: text/xml');
        echo $doc->saveXML();
    }
}