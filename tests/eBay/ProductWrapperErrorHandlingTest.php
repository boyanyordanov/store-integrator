<?php

namespace StoreIntegrator\tests\eBay;

use DateTime;
use StoreIntegrator\eBay\Wrappers\ProductWrapper;
use StoreIntegrator\tests\TestCase;

/**
 * Class TestProductWrapperErrorHandling
 * @package StoreIntegrator\tests\eBay
 */
class TestProductWrapperErrorHandling extends TestCase
{
    /**
     * @var ProductWrapper
     */
    protected $productWrapper;

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

        $this->productWrapper = new ProductWrapper($this->userToken, $store, $this->tradingService);
    }

    /**
     * @expectedException \StoreIntegrator\Exceptions\EbayErrorException
     * @expectedExceptionMessage Very Big Error
     * @expectedExceptionCode 1234
     */
    public function testPostProductError()
    {
        $this->createErrorResponseForOperation('AddFixedPriceItem');
        $product = $this->sampleProduct();

        $this->productWrapper->post($product);
    }

    /**
     * @expectedException \StoreIntegrator\Exceptions\EbayErrorException
     * @expectedExceptionMessage Very Big Error
     * @expectedExceptionCode 1234
     */
    public function testGetttingProductsError()
    {
        $this->createErrorResponseForOperation('GetSellerList');
        $this->productWrapper->getAll(new DateTime('-1 week'));
    }
}
