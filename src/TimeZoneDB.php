<?php

namespace NNV;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Exception;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;

class TimeZoneDB
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
    public function list(array $options = [])
    {
        $options = $this->validateListParams($options);

        return $this->execute("list-time-zone", $options);
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
                return preg_match("/[a-z]{2}/i", $countryCode);
            });

        return $optionResolver->resolve($listParams);
    }
}
