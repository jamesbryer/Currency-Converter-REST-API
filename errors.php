<?php
//header('Content-type: text/xml');

$errors = array(
    "1000" => "Required parameter is missing",
    "1100" => "Parameter not recognised",
    "1200" => "Currency type not recognised",
    "1300" => "Currency type must be a decimal number",
    "1400" => "Format must be xml or json",
    "1500" => "Error in service"
);

function error_messaging($error_code)
{
    global $errors;
    $error_message = $errors[$error_code];
}