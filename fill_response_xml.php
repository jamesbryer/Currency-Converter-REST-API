<?php

$filename = "iso_4217.xml";
$xml = simplexml_load_file($filename);
foreach ($xml->CcyTbl->CcyNtry as $currency) {
    echo "hello worlds";
}