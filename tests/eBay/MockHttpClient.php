<?php
/**
 * Created by PhpStorm.
 * User: boyan
 * Date: 16.10.15
 * Time: 14:07
 */

namespace StoreIntegrator\tests\eBay;

use DTS\eBaySDK\Interfaces\HttpClientInterface;
use Guzzle\Http\Client;


/**
 * Class MockClient
 * @package StoreIntegrator\tests\eBay
 */
class MockHttpClient implements HttpClientInterface {
    protected $url;
    protected $body;
    protected $headers;

    /**
     * @var
     */
    private $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create an API POST request and send it to eBay.
     *
     * @param string $url API endpoint.
     * @param array $headers Associative array of HTTP headers.
     * @param string $body The body of the POST request. Will be a XML string for the API operation call.
     *
     * @return string The XML response from the API.
     */
    public function post($url, $headers, $body)
    {
        $this->url = $url;
        $this->body = $body;
        $this->headers = $headers;

        return $this->client->post($url, $headers, $body)->send()->getBody(true);
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getRequestBody()
    {
        return $this->body;
    }

    public function getApiCallName()
    {
        return $this->headers['X-EBAY-API-CALL-NAME'];
    }
}
