<?php

include "conf.php";
require "get_array_of_currencies.php";
function build_xml($base_currency)
{
    //A script to check whether the output XML files exists and create it if it does not using the ISO file
    //UPDATE IN FINAL TO IF FILE DOESNT EXIST !!!! ONLY RUNNING LIKE THIS FOR TESTING
    if (!file_exists("response.xml")) {
        //call function to get list of currencies
        $currencies = get_array_of_currencies();

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
        $base_attribute->value = $base_currency;
        $base_attribute = $root->appendChild($base_attribute);

        // Loop through each row creating a <record> node with the correct data

        foreach ($currencies as $currency) {
            $container = $doc->createElement('currency');
            $child = $doc->createElement("code");
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