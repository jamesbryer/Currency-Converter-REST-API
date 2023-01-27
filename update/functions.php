<?php
include "../conf.php";

function build_put_response($currency_code)
{
    //load rates file
    $xml = simplexml_load_file(OUTPUT_FILENAME_UPDATE) or die("Cannot load file");

    //get required info to build output from rates file
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

    //create new DOMDocument
    $put_response = new DOMDocument("1.0", "UTF-8");
    //pretty formatting
    $put_response->formatOutput = true;

    //create required elements for all http methods
    $root = $put_response->createElement("action");
    $root = $put_response->appendChild($root);

    $root_attribute = $root->setAttribute("type", "put");
    $root_attribute = $root->appendChild($root_attribute);

    $at_element = $put_response->createElement("at", gmdate("F j, Y, g:i:s a", time()));
    $at_element = $root->appendChild($at_element);

    //only create these elements for put and post methods

    //create element for old rate 
    $old_rate_element = $put_response->createElement("old_rate", $old_rates_info["rate"]);
    $old_rate_element = $root->appendChild($old_rate_element);
    //get updated rate if required, otherwise use old rate as new rate!
    if (check_rates_age($xml) == true) {
        $rates = call_api();
        update_rates($xml, $rates, OUTPUT_FILENAME_UPDATE);
        $xml = simplexml_load_file(OUTPUT_FILENAME_UPDATE) or die("Cannot load file");
        foreach ($xml->currency as $currency) {
            if ($currency->code == strtoupper($currency_code)) {
                $new_rate = $currency["rate"];
            }
        }
    } else {
        $new_rate = $old_rates_info["rate"];
    }
    $rate_element = $put_response->createElement("rate", $new_rate);
    $rate_element = $root->appendChild($rate_element);


    //create remaining elements common to both put and post methods
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

function build_post_response($currency_code)
{
    //load rates file
    $xml = simplexml_load_file(OUTPUT_FILENAME_UPDATE) or die("Cannot load file");

    //get required info to build output from rates file
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

    //create new DOMDocument
    $put_response = new DOMDocument("1.0", "UTF-8");
    //pretty formatting
    $put_response->formatOutput = true;

    //create required elements for all http methods
    $root = $put_response->createElement("action");
    $root = $put_response->appendChild($root);

    $root_attribute = $root->setAttribute("type", "post");
    $root_attribute = $root->appendChild($root_attribute);

    $at_element = $put_response->createElement("at", gmdate("F j, Y, g:i:s a", time()));
    $at_element = $root->appendChild($at_element);
    $old_rate_element = $put_response->createElement("rate", $old_rates_info["rate"]);
    $old_rate_element = $root->appendChild($old_rate_element);
    //create remaining elements common to both put and post methods
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

function build_delete_response($currency_code)
{
    //create new DOMDocument
    $response = new DOMDocument("1.0", "UTF-8");
    //pretty formatting
    $response->formatOutput = true;

    //create required elements for all http methods
    $root = $response->createElement("action");
    $root = $response->appendChild($root);

    $root_attribute = $root->setAttribute("type", 'delete');
    $root_attribute = $root->appendChild($root_attribute);

    $at_element = $response->createElement("at", gmdate("F j, Y, g:i:s a", time()));
    $at_element = $root->appendChild($at_element);

    $code_element = $response->createElement("code", $currency_code);
    $code_element = $root->appendChild($code_element);

    return $response;
}

function post_delete_method($currency_code, $action) // function to change "live" element value based on method passed as parameter
{
    //retrieve list of currencies
    $currencies_array = get_array_of_currencies(OUTPUT_FILENAME_UPDATE);

    //if the passed currency is real, either activate or deactivate it
    if (in_array($currency_code, $currencies_array)) {
        $xml = simplexml_load_file(OUTPUT_FILENAME_UPDATE);
        foreach ($xml->currency as $currency) {
            if ($currency_code == $currency->code) {
                if ($action == "post") {
                    $currency["live"] = "1";
                } elseif ($action == "del") {
                    $currency["live"] = "0";
                }
                $xml->asXML(OUTPUT_FILENAME_UPDATE);
                break;
            }
        }
    } else {
        $error = true;
    }
}

function check_update_query_string()
{
    if (!in_array($_GET["action"], UPDATE_ACTIONS)) {
        return "2000";
    }

    if (!strlen($_GET["cur"]) == 3 and !ctype_upper($_GET["cur"]) and !is_numeric($_GET["cur"])) {
        return "2100";
    }

    if (!in_array($_GET["cur"], get_array_of_live_currencies(OUTPUT_FILENAME_UPDATE))) {
        if ($_GET["action"] == "post" and in_array($_GET["cur"], get_array_of_currencies(OUTPUT_FILENAME_UPDATE))) {
            $true = true;
        } else {
            return "2200";
        }
    }
    $xml = simplexml_load_file(OUTPUT_FILENAME_UPDATE) or die("cannot load file");
    foreach ($xml->currency as $currency) {
        if ($currency["rate"] == null) {
            return "2300";
        }
    }

    if ($_GET["cur"] == BASE_CURRENCY) {
        return "2400";
    }
}