<?php

$filename = "iso_4217.xml";
$xml = simplexml_load_file($filename);
$currencies = array();

foreach ($xml->CcyTbl->CcyNtry as $country_currency) {
    $needle = $country_currency->Ccy;
    array_push($currencies, $needle);
}
$currencies = array_unique($currencies);

foreach ($currencies as $ccy_in_array) {
    echo $ccy_in_array;
    echo "<br> ";
}