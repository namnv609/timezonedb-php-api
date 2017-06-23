# TimeZoneDB API

> TimeZoneDB provides free time zone database for cities of the world. The database is licensed under Creative Commons Attribution 3.0 License. It contains countries name, time zones, abbreviation, GMT offset, and Daylight Saving Time (DST). The data is available in CSV and SQL format. You can download and implement into your projects for free.

## System requirements
* **PHP >= 5.5**

## Installation
Using Composer
* `composer install namnv609/php-onesignal-sdk`

or you can include the following in your `composer.json`
* `"namnv609/php-onesignal-sdk": "1.0"`

## Usage Instructions

First, create new `TimeZoneDB` instance to make configuring the library for usage.

```PHP
use NNV\TimeZoneDB;

$tzDB = new TimeZoneDB(<TimeZoneDB API Key>);
```

Once the `TimeZoneDB` instance has been registered. You may use it like so:

### [List Time Zone](https://timezonedb.com/references/list-time-zone)

> List out all available time zones supported by TimeZoneDB.

```PHP
$listTimeZoneParams = [
    "fields" => "countryCode,countryName",
    "country" => "NZ",
    // Other params
];
$tzDB->listTimeZone($listTimeZoneParams);
```

### [Get Time Zone](https://timezonedb.com/references/get-time-zone)

> Get local time of a city by its name, time zone, latitude & longtiude, or IP address.

```PHP
$getTimeZoneParams = [
    "by" => "zone",
    "zone" => "America/Chicago",
    // Other params
];
$tzDB->getTimeZone($getTimeZoneParams);
```

### [Convert Time Zone](https://timezonedb.com/references/convert-time-zone)

> Convert timestamp between two different time zone.

```PHP
$convertTimeZoneParams = [
    "from" => "America/Los_Angeles",
    "to" => "Australia/Sydney",
    "time" => "06/01/2016 03:00PM", // You can use standard DateTime format or Unix time
    // Other params
];
$tzDB->convertTimeZone($convertTimeZoneParams);
```

### Utilities

We support some utilities function for your usage like:

#### listTimeZones

> List of time zones supported by TimeZoneDB

```PHP
$tzDB->listTimeZones();
```

#### codeToZones

> Convert country code to time zones

```PHP
$tzDB->codeToZones("AQ");

// Result
[
    8 => "Antarctica/Casey",
    9 => "Antarctica/Davis",
    10 => "Antarctica/DumontDUrville",
    11 => "Antarctica/Mawson",
    12 => "Antarctica/McMurdo",
    13 => "Antarctica/Palmer",
    14 => "Antarctica/Rothera",
    15 => "Antarctica/Syowa",
    16 => "Antarctica/Troll",
    17 => "Antarctica/Vostok",
]

```

#### countryToZones

> Country name to time zones

```PHP
$tzDB->countryToZones("Vietnam");

// Result
[
    418 => "Asia/Ho_Chi_Minh",
]
```

#### zoneToCode

> Time zone to country code

```PHP
$tzDB->zoneToCode("Asia/Ho_Chi_Minh");

// Result
"VN"
```

#### zoneToCountry

> Time zone to country name

```PHP
$tzDB->zoneToCountry("Asia/Ho_Chi_Minh");

// Result
"Vietnam"
```
