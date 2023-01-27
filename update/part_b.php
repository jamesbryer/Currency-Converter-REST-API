<?php
include "../functions.php";
include "functions.php";
include "../conf.php";

//check files exist - if they don't, make them
check_base_files(OUTPUT_FILENAME_UPDATE);
//check for errors in query string
$error_code = check_update_query_string($_GET);

//if there is an error within the query string, build and display error
if ($error_code != null) {
    //output response of create_error function in format described by query string
    output_response("xml", create_error($error_code));
    exit(); //exit to stop script running
}

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