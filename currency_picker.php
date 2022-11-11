<?php

$filename = "iso_4217.xml";
$xml = simplexml_load_file($filename);
$currency_codes = array();
foreach ($xml->CcyTbl->CcyNtry as $currency) {
    array_push($currency_codes, $currency->Ccy);
    $child = $currency->addChild("rate");
    $xml->asXML("iso_4217.xml");
}

echo time();

/* $datetime1 = date_create('2016-06-01');
$datetime2 = date_create('2018-09-21');

// Calculates the difference between DateTime objects
$interval = date_diff($datetime1, $datetime2);

// Printing result in years & months format
echo $interval; */


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Currency Converter</title>
</head>

<body>

    <form action="">
        <select name="currency" id="currency">
            <?php
            foreach ($currency_codes as $crncy) {
                echo "<option value='" . $crncy . "'>" . $crncy . "</option>";
            }
            ?>
        </select>
    </form>

</body>

</html>