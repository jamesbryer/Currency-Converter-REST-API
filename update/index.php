<?php
include "../functions.php";
include "../conf.php";


function put_method($currency_code)
{
    $xml = simplexml_load_file(OUTPUT_FILENAME_UPDATE) or die("Cannot load file");

    foreach ($xml->currency as $currency) {
        if ($currency->code == strtoupper($currency_code)) {
            $old_rates_info = array(
                "rate" => $currency["rate"],
                "code" => $currency->code,
                "curr" => $currency->curr,
                "loc" => $currency->loc
            );
        }
    }

    $put_response = new DOMDocument("1.0", "UTF-8");
    $put_response->formatOutput = true;

    $root = $put_response->createElement("action");
    $root = $put_response->appendChild($root);

    $root_attribute = $root->setAttribute("type", "put");
    $root_attribute = $root->appendChild($root_attribute);

    $at_element = $put_response->createElement("at", gmdate("F j, Y, g:i:s a", intval($xml["timestamp"])));
    $at_element = $root->appendChild($at_element);

    $old_rate_element = $put_response->createElement("old_rate", $old_rates_info["rate"]);
    $old_rate_element = $root->appendChild($old_rate_element);

    //get updated rate if required, otherwise use old rate as new rate!
    if (check_rates_age($xml) == true) {
        $rates = call_api();
        update_rates($xml, $rates, OUTPUT_FILENAME_UPDATE);
        $xml = simplexml_load_file(OUTPUT_FILENAME_UPDATE) or die("Cannot load file");
        foreach ($xml->currency as $currency) {
            if ($currency->code == strtoupper($currency_code)) {
                $new_rates_rate = $currency["rate"];
            }
        }
    } else {
        $new_rates_rate = $old_rates_info["rate"];
    }

    $rate_element = $put_response->createElement("rate", $new_rates_rate);
    $rate_element = $root->appendChild($rate_element);

    $curr_element = $put_response->createElement("curr");
    $curr_element = $root->appendChild($curr_element);

    $code_element = $put_response->createElement("code", $old_rates_info["code"]);
    $code_element = $curr_element->appendChild($code_element);

    $name_element = $put_response->createElement("name", $old_rates_info["curr"]);
    $name_element = $curr_element->appendChild($name_element);

    $loc_element = $put_response->createElement("loc", $old_rates_info["loc"]);
    $loc_element = $curr_element->appendChild($loc_element);

    return $put_response;
}

function post_delete_method($currency_code, $action)
{
    $currencies_array = get_array_of_currencies(OUTPUT_FILENAME_UPDATE);

    if (in_array($currency_code, $currencies_array)) {
        $xml = simplexml_load_file(OUTPUT_FILENAME_UPDATE);
        foreach ($xml->currency as $currency) {
            if ($currency_code == $currency->code) {
                if ($action == "post") {
                    $currency["live"] = "1";
                    echo "done posting!";
                } elseif ($action == "delete") {
                    $currency["live"] = "0";
                    echo "done deleting!";
                }
                $xml->asXML(OUTPUT_FILENAME_UPDATE);
                break;
            }
        }
    } else {
        echo "bitch u got an error!";
    }
}

if ($_GET["action"] == "put") {
    output_response($_GET["format"], put_method($_GET["cur"]));
}

if ($_GET["action"] == "post" or $_GET["action"] == "delete") {
    post_delete_method($_GET["cur"], $_GET["action"]);
}