<?php
$url = 'https://www.six-group.com/dam/download/financial-information/data-center/iso-currrency/lists/list-one.xml';
$file_name = "iso_4217.xml";
file_put_contents($file_name, file_get_contents($url));