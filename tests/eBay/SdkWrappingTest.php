<?php

namespace StoreIntegrator\tests\eBay;

use DTS\eBaySDK\Constants\SiteIds;
use StoreIntegrator\tests\TestCase;

use DTS\eBaySDK\Interfaces\HttpClientInterface;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Constants;
use DTS\eBaySDK\Trading\Types\GeteBayOfficialTimeRequestType;
use Guzzle\Http\Client;
use Guzzle\Http\EntityBody;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;


/**
 * Class MockClient
 * @package StoreIntegrator\tests\eBay
 */
class MockClient implements HttpClientInterface {

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
        return $this->client->post($url, $headers, $body)->send()->getBody(true);
    }
}

/**
 * Class SdkWrappingTest
 * @package StoreIntegrator\tests\eBay
 */
class SdkWrappingTest extends TestCase
{
    /**
     * @var
     */
    protected $tradingService;
    /**
     * @var
     */
    protected $mockHttpClient;
    /**
     * @var
     */
    protected $guzzle;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->guzzle = new Client();

        $this->mockHttpClient = new MockClient($this->guzzle);

        $this->tradingService = new TradingService([
            'siteId' => SiteIds::US,
            'sandbox' => true
        ], $this->mockHttpClient);
    }hahfg

    /**
     *
     */
    public function testGetCurrentTimeOp()
    {
        $mockReponse = new Response(200);
        $mockReponse->setBody(EntityBody::factory(file_get_contents(__DIR__ . '/xmlStubs/current-time-response.xml')));

        $plugin = new MockPlugin();
        $plugin->addResponse($mockReponse);

        $this->guzzle->addSubscriber($plugin);

        $request = new GeteBayOfficialTimeRequestType();

        $request->RequesterCredentials = new CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = 'some-user-token';

        $response = $this->tradingService->geteBayOfficialTime($request);

        $this->assertEquals('Success', $response->Ack, 'The request was not successfull.');
        $this->assertEquals('2015-10-16 06:50:51', $response->Timestamp->format('Y-m-d H:i:s'));
    }
}
