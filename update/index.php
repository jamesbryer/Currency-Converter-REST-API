<?php
include "../functions.php";
include "functions.php";
include "../conf.php";

//check files exist - if they don't, make them
check_base_files(OUTPUT_FILENAME_UPDATE);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Currency Converter Form</title>
    <style>
    textarea {
        outline: none;
        resize: none;
        width: 400px;
        height: 400px;
    }
    </style>
    <script src="scripts.js"></script>
</head>

<body onload="checkFiles();populateDropdown();">
    <select id="dropdown" name="Currency"></select>
    <input type="radio" name="radio" value="post" /> Post
    <input type="radio" name="radio" value="put" /> Put
    <input type="radio" name="radio" value="del" /> Delete
    <button type="button"
        onclick="sendRequest(getSelectedCur(),getSelectedRadio());setTimeout(function() {populateDropdown();}, 1000);">
        Submit
    </button>
    <p />
    <textarea id="xml_text"></textarea>
    <h1>
        NOTE: TO TEST UPDATE WITHOUT PART C - USE THE URL<a
            href="http://localhost/atwd1/assignment/update/part_b.php">http://localhost/atwd1/assignment/update/part_b.php</a>
    </h1>
</body>

</html>