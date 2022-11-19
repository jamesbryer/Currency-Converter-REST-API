<?php

//A script to check whether the output XML files exists and create it if it does not using the ISO file
if (file_exists("response.xml")) {
    //load XML and use SimpleXML to convert to associative array
    $filename = "iso_4217.xml";
    $xml = simplexml_load_file($filename);
    $currencies = array();

    //Pull each currency code into an array
    foreach ($xml->CcyTbl->CcyNtry as $country_currency) {
        $needle = $country_currency->Ccy;
        array_push($currencies, $needle);
    }

    //remove duplicate currency codes from array
    $currencies = array_unique($currencies);


    // Create a new dom document with pretty formatting
    $doc = new DomDocument();
    $doc->formatOutput = true;

    // Add a root node to the document
    $root = $doc->createElement('currencies');
    $root = $doc->appendChild($root);

    // Loop through each row creating a <record> node with the correct data
    foreach ($currencies as $currency) {
        $child = $doc->createElement($currency);
        $child = $container->appendChild($child);
    }
    $root->appendChild($container);

    $strxml = $doc->saveXML();
    $handle = fopen($outputFilename, "w");
    fwrite($handle, $strxml);
    fclose($handle);





    fclose($my_read_file);


/*     $currencies_and_countries = array("Foo" => "bar");

    foreach ($currencies as $ccy) {
        //add code to search xml file and add countries to assiciative array if currency codes match
        foreach ($xml->CcyTbl->CcyNtry as $country) {
            if ($country->Ccy == $ccy) {
                if (!array_key_exists($ccy, $currencies_and_countries)) {
                    $currencies_and_countries[$$ccy] = $country->CtryNm;
                    echo " passed 2nd condition ";
                    echo $currencies_and_countries[$ccy];
                } else {
                    $currencies_and_countries[$$ccy] = $currencies_and_countries[$$ccy] . "," . $country->CtryNm;
                    echo " third condition passes ";
                }
            }
        }
    }

    foreach ($currencies_and_countries as $ccy => $country) {
        echo $ccy;
        echo "<br> ";
        echo $country;
    }
} else {
    echo "This file already exists!";
} 
*/