<?php

namespace NNV;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Exception;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use NNV\TimeZoneDB\TimeZones;

class TimeZoneDB extends TimeZones
{
    /**
     * TimeZoneDB API key
     * @var string
     */
    private $apiKey;

    /**
     * Guzzle Client instance
     * @var GuzzleHttp\Client
     */
    private $guzzleClient;

    public function __construct($apiKey)
    {
        parent::__construct();

        $this->apiKey = $apiKey;
        $this->guzzleClient = new Client([
            "base_uri" => "http://api.timezonedb.com/v2/"
        ]);
    }

    /**
     * List out all available time zones supported by TimeZoneDB.
     *
     * @param  array  $options Request params
     * @return \NNV\TimeZoneDB::execute
     */
    public function listTimeZone(array $options = [])
    {
        $options = $this->validateListParams($options);

        return $this->execute("list-time-zone", $options);
    }

    /**
     * Get local time of a city by its name, time zone, latitude & longtiude, or IP address.
     *
     * @param  array  $options Request params
     * @return \NNV\TimeZoneDB::execute
     */
    public function getTimeZone(array $options)
    {
        $options = $this->validateGetTimeZoneParams($options);

        return $this->execute("get-time-zone", $options);
    }

    /**
     * Convert time two different time zone.
     *
     * @param  array  $options Request params
     * @return \NNV\TimeZoneDB::execute
     */
    public function convertTimeZone(array $options)
    {
        $options = $this->validateConvertTimeZoneParams($options);

        return $this->execute("convert-time-zone", $options);
    }

    /**
     * Execute TimeZoneDB requests
     *
     * @param  string $url           TimeZoneDB request URL
     * @param  array  $requestParams Request paramas
     * @param  string $method        Request method
     * @return mixed                 TimeZoneDB response (SimpleXMLElement or JSON object)
     */
    private function execute($url, array $requestParams = [], $method = "GET")
    {
        $queryParams = array_merge([
            "key" => $this->apiKey,
            "format" => "json"
        ], $requestParams);

        try {
            $response = $this->guzzleClient->request(strtoupper($method), $url, [
                "query" => $queryParams,
            ])->getBody()->getContents();

            if (strtolower($queryParams["format"]) === "xml") {
                return simplexml_load_string($response);
            }

            return json_decode($response);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }

    /**
     * Validate List time zone request params
     *
     * @param  array  $listParams List time zone request params
     * @return mixed  Symfony\Component\OptionsResolver\Exception\InvalidOptionsException or resolved data
     */
    private function validateListParams(array $listParams)
    {
        $optionResolver = new OptionsResolver;

        $optionResolver->setDefined(["format", "fields", "country", "zone"])
            ->setAllowedValues("format", ["json", "xml"])
            ->setAllowedValues("fields", function($fields) {
                $availableFields = ["countryCode", "countryName", "zoneName", "gmtOffset", "timestamp"];
                $fieldArr = array_map(function($field) use ($availableFields) {
                    return in_array(trim($field), $availableFields);
                }, explode(",", $fields));

                return $fields === "all" || !in_array(false, $fieldArr);
            })
            ->setNormalizer("fields", function(Options $options, $fields) {
                return preg_replace("/\s+/", "", $fields);
            })
            ->setAllowedValues("country", function($countryCode) {
                return preg_match("/^[a-z]{2}$/i", $countryCode);
            });

        return $optionResolver->resolve($listParams);
    }

    /**
     * Validate Get time zone request paramas
     *
     * @param  array  $getTimeZoneParams Get time zone request paramas
     * @return mixed  Symfony\Component\OptionsResolver\Exception\InvalidOptionsException or resolved data
     */
    private function validateGetTimeZoneParams(array $getTimeZoneParams)
    {
        $optionResolver = new OptionsResolver;

        $optionResolver->setDefined([
            "format", "fields", "by", "zone", "lat", "lng",
            "country", "region", "city", "page", "time"
        ])
        ->setDefaults([
            "zone" => "",
            "lat" => "",
            "lng" => "",
            "country" => "",
            "city" => "",
        ])
        ->setRequired("by")
        ->setAllowedValues("format", ["xml", "json"])
        ->setAllowedValues("fields", function($fields) {
            $availableFields = [
                "countryCode", "countryName", "regionName", "cityName", "zoneName",
                "abbreviation", "gmtOffset", "dst", "dstStart", "dstEnd",
                "nextAbbreviation", "timestamp", "formatted"
            ];
            $fieldArr = array_map(function($field) use ($availableFields) {
                return in_array(trim($field), $availableFields);
            }, explode(",", $fields));

            return $fields === "all" || !in_array(false, $fieldArr);
        })
        ->setAllowedValues("by", ["zone", "position", "city", "ip"])
        ->setAllowedValues("region", function($regionCode) {
            return preg_match("/^[a-z]{2}$/i", $regionCode);
        })
        ->setNormalizer("zone", function(Options $options, $zone) {
            return $this->setRequiredIf($options, $zone, "zone", "zone");
        })
        ->setNormalizer("lat", function(Options $options, $lat) {
            return $this->setRequiredIf($options, $lat, "lat", "position");
        })
        ->setNormalizer("lng", function(Options $options, $lng) {
            return $this->setRequiredIf($options, $lng, "lng", "position");
        })
        ->setNormalizer("country", function(Options $options, $country) {
            return $this->setRequiredIf($options, $country, "country", "city");
        })
        ->setNormalizer("city", function(Options $options, $city) {
            return $this->setRequiredIf($options, $city, "city", "city");
        });

        $getTimeZoneParams = $optionResolver->resolve($getTimeZoneParams);

        return array_filter($getTimeZoneParams);
    }

    /**
     * Required a field by another field
     *
     * @param Symfony\Component\OptionsResolver\Options $options
     * @param mixed  $filedValue   Field value
     * @param string $currentField Current field name
     * @param string $otherField   Another field name
     * @return mixed Symfony\Component\OptionsResolver\Exception\InvalidOptionsException or field value
     */
    private function setRequiredIf($options, $filedValue, $currentField, $otherField)
    {
        $lookupBy = $options["by"];

        if ($lookupBy == $otherField && !$filedValue) {
            throw new InvalidOptionsException(
                sprintf("%s must be set when use lookup by %s", $currentField, $otherField)
            );
        }

        return $filedValue;
    }

    /**
     * Validate Convert time zone
     *
     * @param  array  $convertTimeZoneParams Convert time zone params
     * @return mixed  Symfony\Component\OptionsResolver\Exception\InvalidOptionsException or resolved data
     */
    private function validateConvertTimeZoneParams(array $convertTimeZoneParams)
    {
        $optionResolver = new OptionsResolver;

        $optionResolver->setDefined(["format", "fields", "from", "to", "time"])
            ->setRequired(["from", "to"])
            ->setAllowedValues("format", ["json", "xml"])
            ->setAllowedValues("fields", function($fields) {
                $availableFields = [
                    "fromZoneName", "fromAbbreviation", "fromTimestamp",
                    "toZoneName", "toAbbreviation", "toTimestamp", "offset"
                ];
                $fieldArr = array_map(function($field) use ($availableFields) {
                    return in_array(trim($field), $availableFields);
                }, explode(",", $fields));

                return $fields === "all" || !in_array(false, $fieldArr);
            })
            ->setNormalizer("time", function(Options $options, $time) {
                // $time is valid timestamp?
                if ((string)(int) $time === $time && $time <= PHP_INT_MAX && $time >= ~PHP_INT_MAX) {
                    return $time;
                }

                // If $time is invalid timestamp. Try to convert it.
                $time = strtotime($time . " UTC");

                if (!$time) {
                    throw new InvalidArgumentException("Time is invalid datetime format");
                }

                return $time;
            });

        return $optionResolver->resolve($convertTimeZoneParams);
    }
}
