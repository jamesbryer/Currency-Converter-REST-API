<?php

$filename = "iso_4217.xml";
//hello this is a comment 
$xml = simplexml_load_file($filename);
foreach ($xml->CcyTbl->CcyNtry as $currency) {
    echo "hello world";
}