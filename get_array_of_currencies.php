<?php

function get_array_of_currencies()
{
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
    $currencies = array_map("strtoupper", $currencies);
    return $currencies;
}