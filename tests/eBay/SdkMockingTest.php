<?php

namespace StoreIntegrator\tests\eBay;

use StoreIntegrator\tests\TestCase;

use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
use DTS\eBaySDK\Constants;
use DTS\eBaySDK\Trading\Types\GeteBayOfficialTimeRequestType;


/**
 * Class SdkWrappingTest
 * @package StoreIntegrator\tests\eBay
 */
class SdkMockingTest extends TestCase
{
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUpEbayServiceMocks();
    }

    /**
     *
     */
    public function testGetCurrentTimeOp()
    {
        $mockReponse = $this->generateEbaySuccessResponse(__DIR__ . '/xmlStubs/current-time-response.xml');

        $this->attachMockedEbayResponse($mockReponse);

        $request = new GeteBayOfficialTimeRequestType();

        $request->RequesterCredentials = new CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = 'some-user-token';

        $response = $this->tradingService->geteBayOfficialTime($request);

        $this->assertContains('GeteBayOfficialTimeRequest', $this->mockHttpClient->getRequestBody(), 'The request body does not contain the correct operation.');
        $this->assertEquals('GeteBayOfficialTime', $this->mockHttpClient->getApiCallName(), 'The api call is not for the correct operation');

        $this->assertEquals('Success', $response->Ack, 'The request was not successfull.');
        $this->assertEquals('2015-10-16 06:50:51', $response->Timestamp->format('Y-m-d H:i:s'));
    }
}
