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