<?php
header('Content-Type: text/xml');

if (isset($_GET["from"]) and isset($_GET["to"]) and isset($_GET['amnt'])) {
    $from = $_GET["from"];
    $to = $_GET['to'];
    $amount = $_GET["amnt"];
} else {
    echo "ERROR 1000 MESSAGE HERE";
}