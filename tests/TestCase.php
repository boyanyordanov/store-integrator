<?php

namespace StoreIntegrator\tests;


use DOMDocument;
use DTS\eBaySDK\Constants\SiteIds;
use DTS\eBaySDK\Trading\Services\TradingService;
use Exception;
use Guzzle\Http\Client;
use Guzzle\Http\EntityBody;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use PHPUnit_Framework_TestCase;
use StoreIntegrator\tests\eBay\MockHttpClient;

/**
 * Class TestCase
 * @package StoreIntegrator\tests
 */
class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var TradingService
     */
    protected $tradingService;
    /**
     * @var MockClient
     */
    protected $mockHttpClient;
    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * @param $schema
     * @param $actual
     * @param $message
     */
    public function assertValidXML($schema, $actual, $message = 'The provided XML does not validate against the provided schema')
    {
        $xml = new DOMDocument();

        if(file_exists($actual)) {
            $xml->load($actual);
        } else {
            $xml->loadXML($actual);
        }

        try{
            $this->assertTrue($xml->schemaValidate($schema), $message);
        } catch(Exception $e) {
            $this->fail($message . "\n" . $e->getMessage());
        }

    }

    /**
     *
     */
    public function setUpEbayServiceMocks()
    {
        $this->guzzle = new Client();

        $this->mockHttpClient = new MockHttpClient($this->guzzle);

        $this->tradingService = new TradingService([
            'siteId' => SiteIds::US,
            'sandbox' => true
        ], $this->mockHttpClient);
    }

    /**
     * @param $mockReponse
     * @return \DTS\eBaySDK\Trading\Types\GeteBayOfficialTimeRequestType
     */
    public function attachMockedEbayResponse($mockReponse)
    {
        $plugin = new MockPlugin();
        $plugin->addResponse($mockReponse);

        $this->guzzle->addSubscriber($plugin);
    }

    /**
     * @param $xmlStubPath
     * @return Response
     */
    public function generateEbaySuccessResponse($xmlStubPath)
    {
        $mockReponse = new Response(200);
        $mockReponse->setBody(EntityBody::factory(file_get_contents($xmlStubPath)));
        return $mockReponse;
    }
}