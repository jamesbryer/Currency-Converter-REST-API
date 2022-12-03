<?php
//header('Content-type: text/xml');


function error_messaging($error_code)
{
    global $errors;
    $error_message = $errors[$error_code];
}