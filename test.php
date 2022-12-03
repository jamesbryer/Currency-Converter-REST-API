<?php
include "conf.php";
//TEST STUFF
$test = ERROR_CODES_AND_MESSAGES;
foreach ($test as $code => $message) {
    echo $code . ": " . $message;
}