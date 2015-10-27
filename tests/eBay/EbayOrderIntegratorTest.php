<?php

namespace StoreIntegrator\tests\eBay;

use DateTime;
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

        $store = $this->sampleStore();

        $orderssWrapper = new OrdersWrapper($this->userToken, $store, $this->tradingService);

        $this->integrator = new EbayOrderIntegrator($orderssWrapper);
    }

    public function testGettingOrders()
    {
        $mockRespone = $this->generateEbaySuccessResponse('<xml>Got Orders</xml>');
        $this->attachMockedEbayResponse($mockRespone);

        $startDate = new DateTime('-1 week');
        $this->integrator->getOrders($startDate);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertEquals('GetOrders', $this->mockHttpClient->getApiCallName(), 'Unexpected API call name.');
        $this->assertContains('GetOrders', $requestBody, 'API call element not found in request');
        $this->assertContains('<CreateTimeFrom>' . $startDate->format('Y-m-d\TH:i:s.000\Z') . '</CreateTimeFrom>', $requestBody, 'No StartDate found in the request');
        $this->assertContains('<CreateTimeTo>' . (new DateTime())->format('Y-m-d\TH:i:s.000\Z') . '</CreateTimeTo>', $requestBody, 'Incorrect EndDate found in the request');
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
            'trackingCarrier' => 'USPS',
            'trackingNumber' => 'some-tracking-1234'
        ]);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertContains('<Shipment xmlns="urn:ebay:apis:eBLBaseComponents">', $requestBody,
            'Missing Shipment details in request XML');
        $this->assertContains('<ShipmentTrackingDetails xmlns="urn:ebay:apis:eBLBaseComponents">', $requestBody,
            'Missing Tracking details in request XML');
        $this->assertContains('<ShippingCarrierUsed>USPS</ShippingCarrierUsed>', $requestBody,
            'Missing shipping carrier in request XML.');
        $this->assertContains('<ShipmentTrackingNumber>some-tracking-1234</ShipmentTrackingNumber>', $requestBody,
            'Missing tracking number in request XML');
    }

    public function testLeavingDefaultFeedback()
    {
        $mockRespone = $this->generateEbaySuccessResponse('<xml>Left feedback</xml>');
        $this->attachMockedEbayResponse($mockRespone);

        $this->integrator->fulfilOrder('order-1234', [
            'shipped' => true,
            'tracking' => true,
            'trackingCarrier' => 'USPS',
            'trackingNumber' => 'some-tracking-1234',
            'leaveFeedback' => true,
            'userID' => 'test_user'
        ]);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertContains('<FeedbackInfo xmlns="urn:ebay:apis:eBLBaseComponents">', $requestBody,
            'Missing feedback info cotnainer in request XML');
        $this->assertContains('<CommentText>Great buyer!</CommentText>', $requestBody,
            'Missing feedback text in request XML');
        $this->assertContains('<TargetUser>test_user</TargetUser>', $requestBody,
            'Missing user id in request XML');
    }

    public function testLeavingSpecificFeedback()
    {
        $mockRespone = $this->generateEbaySuccessResponse('<xml>Left feedback</xml>');
        $this->attachMockedEbayResponse($mockRespone);

        $this->integrator->fulfilOrder('order-1234', [
            'shipped' => true,
            'tracking' => true,
            'trackingCarrier' => 'USPS',
            'trackingNumber' => 'some-tracking-1234',
            'leaveFeedback' => true,
            'userID' => 'test_user',
            'feedbackText' => 'Super awesome buyer!'
        ]);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertContains('<FeedbackInfo xmlns="urn:ebay:apis:eBLBaseComponents">', $requestBody,
            'Missing feedback info cotnainer in request XML');
        $this->assertContains('<CommentText>Super awesome buyer!</CommentText>', $requestBody,
            'Missing feedback text in request XML');
    }
}
