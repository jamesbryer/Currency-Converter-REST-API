<?php

function check_rates_age($xml)
{
    //gets current Unix timestamp and timestamp from XML document - if document is older than 6 hours, calls update rates function
    $current_time = time();
    if (($current_time - $xml['Tmestmp']) > 21600) {
        update_rates($xml);
    } else {
        echo "Rates do not need updating!";
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

$filename = "iso_4217.xml";
$xml = simplexml_load_file($filename);
check_rates_age($xml);

//TODO: create new XML file