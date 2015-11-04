<?php

namespace StoreIntegrator\tests\eBay;

use StoreIntegrator\eBay\Wrappers\ProductUpdateWrapper;
use StoreIntegrator\tests\TestCase;

class ProductUpdateWrapperTest extends TestCase
{
    /**
     * @var ProductUpdateWrapper
     */
    protected $productUpdater;

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

        $this->productUpdater = new ProductUpdateWrapper($this->userToken, $store, $this->tradingService);
    }

    public function testDeleteProduct()
    {
        $mockResponse = $this->generateEbaySuccessResponse('<xml>Success</xml>');
        $this->attachMockedEbayResponse($mockResponse);

        $sku = 'product-12340';
        $this->productUpdater->deleteProduct($sku);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertEquals('EndFixedPriceItem', $this->mockHttpClient->getApiCallName(), 'Incorrect API call name.');
        $this->assertContains('<SKU>' . $sku . '</SKU>', $requestBody, 'Missing item identificator (SKU) in the request body.');
    }

    /**
     * @expectedException \StoreIntegrator\Exceptions\EbayErrorException
     * @expectedExceptionMessage Very Big Error
     * @expectedExceptionCode 1234
     */
    public function testDeleteProductErrorHandling()
    {
        $this->createErrorResponseForOperation('EndFixedPriceItem');
        $this->productUpdater->deleteProduct('non-existent-sku');
    }
}
