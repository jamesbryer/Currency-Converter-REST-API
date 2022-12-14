<?php
include "../functions.php";
include "../conf.php";


function build_response($currency_code, $method)
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

    $root_attribute = $root->setAttribute("type", $method);
    $root_attribute = $root->appendChild($root_attribute);

    $at_element = $put_response->createElement("at", gmdate("F j, Y, g:i:s a", time()));
    $at_element = $root->appendChild($at_element);

    //only create these elements for put and post methods
    if ($method != "del") {
        if ($method == "put") {
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
        } else if ($method == "post") {
            $old_rate_element = $put_response->createElement("rate", $old_rates_info["rate"]);
            $old_rate_element = $root->appendChild($old_rate_element);
        }

        //create remaining elements common to both put and post methods
        $curr_element = $put_response->createElement("curr");
        $curr_element = $root->appendChild($curr_element);

        $code_element = $put_response->createElement("code", $old_rates_info["code"]);
        $code_element = $curr_element->appendChild($code_element);

        $name_element = $put_response->createElement("name", $old_rates_info["curr"]);
        $name_element = $curr_element->appendChild($name_element);

        $loc_element = $put_response->createElement("loc", $old_rates_info["loc"]);
        $loc_element = $curr_element->appendChild($loc_element);
    } elseif ($method == "del") { // add the code element for delete method only
        $code_element = $put_response->createElement("code", $old_rates_info["code"]);
        $code_element = $root->appendChild($code_element);
    }


    return $put_response;
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


if ($_GET["action"] == "put") {
    output_response($_GET["format"], build_response($_GET["cur"], $_GET["action"]));
}

if ($_GET["action"] == "post" or $_GET["action"] == "del") {
    post_delete_method($_GET["cur"], $_GET["action"]);
    output_response($_GET["format"], build_response($_GET["cur"], $_GET["action"]));
}