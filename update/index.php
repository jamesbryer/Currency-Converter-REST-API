<?php
include "../functions.php";
include "../conf.php";

//function takes a currency code as a parameter and returns its current rate according to response.xml
function get_currency_rate($currency_code)
{
    if (in_array($currency_code, LIVE_CURRENCIES)) {
        //use foreach loop to retrieve old rate of currency
        $xml = simplexml_load_file("../response.xml") or die("Cannot load file");
        foreach ($xml->currency as $currency) {
            if ($currency->code == $currency_code) {
                $old_rate = $currency["rate"];
                break;
            } else {
                $old_rate = "Error: could not find rate!";
            }
        }
        return $old_rate;
    }
}





$old_rate = get_currency_rate($_GET["curr"]);

$xml = simplexml_load_file("../response.xml") or die("Cannot load file");

if (check_rates_age($xml) == true) {
    $rates = call_api();
    update_rates($xml, $rates);
    $xml = simplexml_load_file(OUTPUT_FILENAME_UPDATE) or die("Cannot load file");
    foreach ($xml->currency as $currency) {
        if ($currency->code == $_GET["curr"]) {
            $new_rate = $currency["rate"];
            break;
        }
    }
} else {
    $new_rate = $old_rate;
}


$xml = simplexml_load_file("../response.xml") or die("Cannot load file");

echo "Old rate = " . $old_rate . " and New rate = " . $new_rate;