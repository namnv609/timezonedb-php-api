<?php

namespace NNV;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Exception;

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
}
