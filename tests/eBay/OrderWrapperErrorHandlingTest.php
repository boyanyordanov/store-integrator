<?php

namespace StoreIntegrator\tests\eBay;


use DateTime;
use StoreIntegrator\eBay\OrdersWrapper;
use StoreIntegrator\tests\TestCase;

class OrderWrapperErrorHandlingTest extends TestCase
{
    /**
     * @var OrdersWrapper
     */
    protected $ordersWrapper;

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

        $this->ordersWrapper = new OrdersWrapper($this->userToken, $store, $this->tradingService);
    }

    public function createErrorResponseForOperation($operation)
    {
        $responseXML = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
            <{$operation}Response xmlns="urn:ebay:apis:eBLBaseComponents">
                <Timestamp>2015-10-27T15:16:03.205Z</Timestamp>
                <Ack>Failure</Ack>
                <Errors>
                    <ShortMessage>Small warning</ShortMessage>
                    <LongMessage>Very small warning</LongMessage>
                    <ErrorCode>1</ErrorCode>
                    <SeverityCode>Warning</SeverityCode>
                    <ErrorClassification>RequestWarning</ErrorClassification>
                </Errors>
                <Errors>
                    <ShortMessage>Big Time Error</ShortMessage>
                    <LongMessage>Very Big Error</LongMessage>
                    <ErrorCode>1234</ErrorCode>
                    <SeverityCode>Error</SeverityCode>
                    <ErrorClassification>RequestError</ErrorClassification>
                </Errors>
                <Version>921</Version>
                <Build>E921_CORE_API_17506731_R1</Build>
            </{$operation}Response>​
XML;

        $mockRespone = $this->generateEbaySuccessResponse($responseXML);
        $this->attachMockedEbayResponse($mockRespone);
    }

    /**
     * @expectedException \StoreIntegrator\Exceptions\EbayErrorException
     * @expectedExceptionMessage Very Big Error
     * @expectedExceptionCode 1234
     */
    public function testGetOrdersError()
    {
        $this->createErrorResponseForOperation('GetOrrdersResponse');
        $this->ordersWrapper->getAll(new DateTime('-1 week'));
    }

    /**
     * @expectedException \StoreIntegrator\Exceptions\EbayErrorException
     * @expectedExceptionMessage Very Big Error
     * @expectedExceptionCode 1234
     */
    public function testGetOrderError()
    {
        $this->createErrorResponseForOperation('GetOrrdersResponse');
        $this->ordersWrapper->get('foobar');
    }

    /**
     * @expectedException \StoreIntegrator\Exceptions\EbayErrorException
     * @expectedExceptionMessage Very Big Error
     * @expectedExceptionCode 1234
     */
    public function testFulfillOrderError()
    {
        $this->createErrorResponseForOperation('CompleteSale');
        $this->ordersWrapper->fulfill('foobar', []);
    }
}
