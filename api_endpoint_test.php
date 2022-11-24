<?php

include "conf.php";
//require "xml_builder.php";
//require "download_iso_file.php";

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

function update_rates($xml, $rates)
{
    //loops through each currency in xml file and finds corresponding currency in API data
    foreach ($xml->currency as $currency) {
        foreach ($rates as $code => $rate) {
            if ($code == $currency->code) {
                $currency["rate"] = $rate;
                echo $rate;
                break;
            }
        }
    }
    $xml["timestamp"] = time();
    $xml->asXML("response.xml");
    echo "Rates updated!";
}

function call_api()
{
    //uses curl to make API call
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.apilayer.com/fixer/latest?base=GBP",
        CURLOPT_HTTPHEADER => array(
            "Content-Type: text/plain",
            "apikey: Ra3niNeqTj1IWKnYIDkUTdaETgU2nV1x"
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

//function to check whether ISO file exists - if it doesn't, download it and build xml from it. RETURNS $XML 
function check_files_exist()
{
    $iso_file = "iso_4217.xml";
    $rates_file = "response.xml";
    if (!file_exists($rates_file)) {
        //Try to load the file, if the file does not exist, download it from the url 
        if (!file_exists($iso_file)) {
            echo "File doesn't exist, downloading...";
            $url = 'https://www.six-group.com/dam/download/financial-information/data-center/iso-currrency/lists/list-one.xml';
            file_put_contents($iso_file, file_get_contents($url));
        }
        return false;
    } else {
        return true;
    }
}

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

    // Add a root node to the document
    $root = $doc->createElement('rates');
    $root = $doc->appendChild($root);
    $root_attribute = $doc->createAttribute('timestamp');
    $root_attribute->value = time();
    $root_attribute = $root->appendChild($root_attribute);
    $base_attribute = $doc->createAttribute('base');
    $base_attribute->value = BASE_CURRENCY;
    $base_attribute = $root->appendChild($base_attribute);

    // Loop through each row creating a <record> node with the correct data

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
            $countries_string = strtolower($countries_string);
            $countries_string = ucwords($countries_string);
        }

        $code_element = $doc->createElement("code");
        $code_element = $container->appendChild($code_element);
        $code_value = $doc->createTextNode($api_code);
        $code_value = $code_element->appendChild($code_value);

        $country_element = $doc->createElement("loc");
        $country_element = $container->appendChild($country_element);
        $country_value = $doc->createTextNode($countries_string);
        $country_value = $country_element->appendChild($country_value);

        $currency_element = $doc->createElement("curr");
        $currency_element = $container->appendChild($currency_element);
        $currency_value = $doc->createTextNode($curr);
        $currency_value = $currency_element->appendChild($currency_value);

        $rate_attribute = $doc->createAttribute("rate");
        $rate_attribute->value = $rate;
        $rate_attribute = $container->appendChild($rate_attribute);

        $live_attribute = $doc->createAttribute("live");
        $live_attribute->value = "1";
        $live_attribute = $container->appendChild($live_attribute);

        $root->appendChild($container);
    }


    $strxml = $doc->saveXML();
    $handle = fopen($outputFilename, "w");
    fwrite($handle, $strxml);
    fclose($handle);
    echo "Rates updated!";
}

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