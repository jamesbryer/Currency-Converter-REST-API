<?php

function get_array_of_currencies()
{
    $filename = "iso_4217.xml";
    $xml = simplexml_load_file($filename);
    $currencies = array();
    $empty_string = "";

    //Pull each currency code into an array
    foreach ($xml->CcyTbl->CcyNtry as $country_currency) {
        $code = strtolower($country_currency->Ccy);
        array_push($currencies, $code);
    }

    //remove duplicate currency codes from array
    $currencies = array_unique($currencies);
    sort($currencies);
    $currencies = array_map("strtoupper", $currencies);

    $currencies_and_countries = array();

    foreach ($currencies as $currency) {
        $currencies_and_countries[$currency] = "";
        $countries_array = array();
        foreach ($xml->CcyTbl->CcyNtry as $country_2) {
            $code = $country_2->Ccy;
            $country_name = $country_2->CtryNm;
            if ($currency == $code) {
                array_push($countries_array, $country_name);
            }
            $countries_string = implode(", ", $countries_array);
            $currencies_and_countries[$currency] = $countries_string;
        }
    }

    foreach ($currencies_and_countries as $code => $countries) {
        echo $code . ": " . $countries . "<br> ";
    }
    return $currencies;
}

get_array_of_currencies();