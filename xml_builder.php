<?php

include "conf.php";
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
        foreach ($xml->CcyTbl->CcyNtry as $country_2) {
            $code = $country_2->Ccy;
            $country_name = $country_2->CtryNm;
            if ($api_code == $code) {
                array_push($countries_array, $country_name);
                $curr = $country_2->CcyNm;
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