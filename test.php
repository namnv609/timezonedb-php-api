<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
load_env(__DIR__);

use NNV\TimeZoneDB;

$tzDB = new TimeZoneDB;
\Psy\Shell::debug(get_defined_vars());
