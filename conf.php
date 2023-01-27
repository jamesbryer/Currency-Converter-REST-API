<?php

@date_default_timezone_set("GMT");

define("CURRENCY_API_KEY", "Ra3niNeqTj1IWKnYIDkUTdaETgU2nV1x");
define("BASE_CURRENCY", "GBP");
define("API_ENDPOINT", "https://api.apilayer.com/fixer/latest?base=");
define("ISO_FILE_URL", "https://www.six-group.com/dam/download/financial-information/data-center/iso-currrency/lists/list-one.xml");
// timestamp length (42300)
define("UPDATE_INTERVAL", 43200);
//params for from to amnt format
define("PARAMS", array("from", "to", "amnt", "format"));
//define CRUD params for part b 
define("CRUD_PARAMS", array("cur", "action"));
//formats xml or json
define("FORMATS", array("xml", "json"));
//define live with array of codes of live currencies
define("LIVE_CURRENCIES", array(
    "AUD", "BRL", "CAD", "CHF",
    "CNY", "DKK", "EUR", "GBP",
    "HKD", "HUF", "INR", "JPY",
    "MXN", "MYR", "NOK", "NZD",
    "PHP", "RUB", "SEK", "SGD",
    "THB", "TRY", "USD", "ZAR"
));

//names of files as well response and iso as consts 
define("OUTPUT_FILENAME_UPDATE", "../response.xml");

define("OUTPUT_FILENAME_ROOT", "response.xml");

define("ISO_FILENAME", "iso_4217.xml");

define("ISO_FILENAME_UPDATE", "../iso_4217.xml");

define("ERROR_CODES_AND_MESSAGES", array(
    "1000" => "Required parameter is missing",
    "1100" => "Parameter not recognised",
    "1200" => "Currency type not recognised",
    "1300" => "Currency type must be a decimal number",
    "1400" => "Format must be xml or json",
    "1500" => "Error in service",
    "2000" => "Action not recognised or it is missing",
    "2100" => "Currency code in wrong format or is missing",
    "2200" => "Currency code not found for update",
    "2300" => "No rate listed for this currency",
    "2400" => "Cannot update base currency"
));

define("UPDATE_ACTIONS", array("post", "put", "del"));