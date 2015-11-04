<?php

namespace StoreIntegrator\tests\eBay;


use DateTime;
use StoreIntegrator\eBay\Wrappers\OrdersWrapper;
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
