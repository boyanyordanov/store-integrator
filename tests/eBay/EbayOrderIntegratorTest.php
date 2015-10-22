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

        $this->integrator->getOrders();

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertEquals('GetOrders', $this->mockHttpClient->getApiCallName(), 'Unexpected API call name.');
        $this->assertContains('GetOrders', $requestBody, 'API call element not found in request');
    }

    public function testMarkingOrdersAsPaid()
    {
        $mockRespone = $this->generateEbaySuccessResponse('<xml>Got Orders</xml>');
        $this->attachMockedEbayResponse($mockRespone);

        $this->integrator->fulfilOrder('order-1234', [
            'paid' => true
        ]);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertEquals('CompleteSale', $this->mockHttpClient->getApiCallName(), 'Incorrect API call');
        $this->assertContains('CompleteSale', $requestBody . 'Missing API Call name in request body XML.');
        $this->assertContains('<OrderID>order-1234</OrderID>', $requestBody,
            'Missing Paid attribute in the request body XML.');
        $this->assertContains('<Paid>true</Paid>', $requestBody, 'Missing Paid attribute in the request body XML.');
    }

    public function testMarkingOrdersAsShipped()
    {
        $mockRespone = $this->generateEbaySuccessResponse('<xml>Got Orders</xml>');
        $this->attachMockedEbayResponse($mockRespone);

        $this->integrator->fulfilOrder('order-1234', [
            'shipped' => true
        ]);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertContains('<Paid>true</Paid>', $requestBody, 'Missing Paid attribute in the request body XML.');
        $this->assertContains('<Shipped>true</Shipped>', $requestBody,
            'Missing Shipped attribute in the request body XML.');
    }

    public function testAddingTrackingDataToOrder()
    {
        $mockRespone = $this->generateEbaySuccessResponse('<xml>Got Orders</xml>');
        $this->attachMockedEbayResponse($mockRespone);

        $this->integrator->fulfilOrder('order-1234', [
            'shipped' => true,
            'tracking' => true,
            'trackingNumber' => 'some-tracking-1234'
        ]);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertContains(
            implode('', [
                '<Shipment xmlns="urn:ebay:apis:eBLBaseComponents">',
                '<ShipmentTrackingDetails xmlns="urn:ebay:apis:eBLBaseComponents">',
                '<ShipmentTrackingNumber>some-tracking-1234</ShipmentTrackingNumber>',
                '</ShipmentTrackingDetails>',
                '</Shipment>'
            ]),
            $requestBody,
            'Missing tracking information in the request body XML.');

    }

    public function testReturningFalseIfOrderFulfillmentFails()
    {
        $mockRespone = $this->generateEbaySuccessResponse(implode('',[
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<CompleteSaleResponse xmlns="urn:ebay:apis:eBLBaseComponents">',
            '<Ack>Failure</Ack>',
            '</CompleteSaleResponse>'
        ]));

        $this->attachMockedEbayResponse($mockRespone);

        $result = $this->integrator->fulfilOrder('order-1234', [
            'paid' => false
        ]);

        $this->assertFalse($result);
    }
}
