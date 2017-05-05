<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
load_env(__DIR__);

use NNV\TimeZoneDB;

$tzDB = new TimeZoneDB(env("TIMEZONEDB_API_KEY"));
$getTimeZoneParams = [
    "by" => "zone",
    "zone" => "America/Chicago",
];
$convertTimeZoneParams = [
    "from" => "America/Los_Angeles",
    "to" => "Australia/Sydney",
    "time" => "06/01/2016 03:00PM",
];

\Psy\Shell::debug(get_defined_vars());
