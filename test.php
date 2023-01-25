<?php
$string = $_GET["string"];
if (strlen($string) === 3 && ctype_upper($string)) {
    echo "The string is 3 characters in length and contains only uppercase letters.";
} else {
    echo "The string does not meet the conditions.";
}