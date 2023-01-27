<?php
include "../functions.php";
include "functions.php";
include "../conf.php";

//check files exist - if they don't, make them
check_base_files(OUTPUT_FILENAME_UPDATE);

if (!empty($_GET)) {
  //check for errors in query string
  $error_code = check_update_query_string($_GET);

  //if there is an error within the query string, build and display error
  if ($error_code != null) {
    //output response of create_error function in format described by query string
    output_response("xml", create_error($error_code));
    exit(); //exit to stop script running
  }

  //use action type set in query string to determine what to do
  if ($_GET["action"] == "put") {
    output_response($_GET["format"], build_put_response($_GET["cur"], $_GET["action"]));
  }

  if ($_GET["action"] == "post") {
    post_delete_method($_GET["cur"], $_GET["action"]);
    output_response($_GET["format"], build_post_response($_GET["cur"], $_GET["action"]));
  }

  if ($_GET["action"] == "del") {
    post_delete_method($_GET["cur"], $_GET["action"]);
    output_response($_GET["format"], build_delete_response($_GET["cur"], $_GET["action"]));
  }
}

//if no query string exists - load front-end
if (empty($_GET)) : ?>
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

<body onload="populateDropdown();">
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
    <h3>To test part-b without the frontend, just add a query string! The front end will dissapear and raw XML will be
        displayed :)</h3>
</body>

</html>

<?php endif; ?>