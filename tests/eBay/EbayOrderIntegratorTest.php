<?php

namespace StoreIntegrator\tests\eBay;

use StoreIntegrator\eBay\EbayOrderIntegrator;
use StoreIntegrator\eBay\OrdersWrapper;
use StoreIntegrator\tests\TestCase;

class EbayOrderIntegratorTest extends TestCase
{
    /**
     * @var EbayOrderIntegrator
     */
    protected $integrator;

    /**
     * @var
     */
    protected $userToken;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUpEbayServiceMocks();

        $this->userToken = 'user-auth-token';

        $orderssWrapper = new OrdersWrapper($this->userToken, $this->tradingService);

        $this->integrator = new EbayOrderIntegrator($orderssWrapper);
    }

    public function testGettingOrders()
    {
        $mockRespone = $this->generateEbaySuccessResponse('<xml>Got Orders</xml>');
        $this->attachMockedEbayResponse($mockRespone);

        $orders = $this->integrator->getOrders();

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertEquals('GetOrders', $this->mockHttpClient->getApiCallName(), 'Unexpected API call name.');
        $this->assertContains('GetOrders', $requestBody, 'API call element not found in request');
    }
}
