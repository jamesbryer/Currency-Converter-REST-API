<?php
include "../functions.php";
include "functions.php";
include "../conf.php";

//todo: add error handling etc 

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