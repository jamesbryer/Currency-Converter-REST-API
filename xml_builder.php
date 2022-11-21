<?php
function build_xml()
{
    //A script to check whether the output XML files exists and create it if it does not using the ISO file
    //UPDATE IN FINAL TO IF FILE DOESNT EXIST !!!! ONLY RUNNING LIKE THIS FOR TESTING
    if (!file_exists("response.xml")) {
        //load XML and use SimpleXML to convert to associative array
        $filename = "iso_4217.xml";
        $xml = simplexml_load_file($filename);
        $currencies = array();

        //Pull each currency code into an array
        foreach ($xml->CcyTbl->CcyNtry as $country_currency) {
            $code = strtolower($country_currency->Ccy);
            array_push($currencies, $code);
        }

        //remove duplicate currency codes from array
        $currencies = array_unique($currencies);
        sort($currencies);

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
        $base_attribute->value = "";

        // Loop through each row creating a <record> node with the correct data

        foreach ($currencies as $currency) {
            $currency = strtoupper($currency);
            $container = $doc->createElement('currency');
            $child = $doc->createElement("currency_code");
            $child = $container->appendChild($child);
            $value = $doc->createTextNode($currency);
            $value = $child->appendChild($value);
            echo $currency . " ";
            $root->appendChild($container);
        }


        $strxml = $doc->saveXML();
        $handle = fopen($outputFilename, "w");
        fwrite($handle, $strxml);
        fclose($handle);
    }
}