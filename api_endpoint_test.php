<?php

include "conf.php";
require "xml_builder.php";

function check_rates_age($xml)
{
    //gets current Unix timestamp and timestamp from XML document - if document is older than 12 hours, calls update rates function
    $current_time = time();
    if (($current_time - $xml['Tmestmp']) > 43200) {
        return true;
    } else {
        return false;
    }
}

function update_rates($xml)
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

    //loops through each currency in xml file and finds corresponding currency in API data
    foreach ($xml->CcyTbl->CcyNtry as $currency) {
        foreach ($rates as $code => $rate) {
            if ($code == $currency->Ccy) {
                if (!$currency->rate) {
                    $currency->addChild("rate", $rate);
                } else {
                    $currency->rate = $rate;
                }
                break;
            }
        }
    }
    $xml["Tmestmp"] = time();
    $xml->asXML("iso_4217.xml");
    echo "Rates updated!";
}

//function to check whether ISO file exists - if it doesn't, download it and build xml from it. RETURNS $XML 
function check_files_exist($base_currency)
{
    $filename = "iso_4217.xml";
    //Try to load the file, if the file does not exist, download it from the url 
    while (!file_exists($filename)) {
        echo "File doesn't exist, downloading...";
        $url = 'https://www.six-group.com/dam/download/financial-information/data-center/iso-currrency/lists/list-one.xml';
        file_put_contents($filename, file_get_contents($url));
        $xml = simplexml_load_file($filename) or die("ADD HTTP ERROR THING HERE");
        //adds timestamp attribute to xml document and sets time to 0 so as to cause rates to be updated.
        $xml->addAttribute("Tmestmp", "0");
    }
    $xml = simplexml_load_file($filename) or die("ADD HTTP ERROR THING HERE");
    //call build_xml from 
    build_xml($base_currency);
    return $xml;
}
$base_currency = BASE_CURRENCY;
$xml = check_files_exist($base_currency);
if (check_rates_age($xml) == true) {
    update_rates($xml);
} else {
    echo "Rates do not need updating!";
}