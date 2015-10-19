<?php

namespace StoreIntegrator\tests\eBay;


use StoreIntegrator\eBay\EbayProductIntegrator;
use StoreIntegrator\Product;
use StoreIntegrator\tests\TestCase;

// should get categories version
// load current version from configs
// should then get the current categories
// Should expose methods to do the checks and to update the version in the configs

/**
 * Class EbayProductIntegratorTest
 * @package StoreIntegrator\tests\eBay
 */
class EbayProductIntegratorTest extends TestCase
{
    /**
     * @var EbayProductIntegrator
     */
    protected $productIntegrator;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUpEbayServiceMocks();

        $this->productIntegrator = new EbayProductIntegrator($this->tradingService);
        $this->productIntegrator->addUserToken('user-auth-token');
    }

    public function testInitializingWithEnvironmentConfigs()
    {
        putenv('EBAY-TRADING-API-VERSION=1234');
        putenv('EBAY-DEV-ID=dev-id');
        putenv('EBAY-APP-ID=app-id');
        putenv('EBAY-CERT-ID=cert-id');

        $integrator = new EbayProductIntegrator();

        $configuration = $integrator->getConfig();

        $this->assertEquals('1234', $configuration['apiVersion'], 'Api version not set correctly.');
        $this->assertEquals('app-id', $configuration['appId'], 'App ID not set correctly.');
        $this->assertEquals('dev-id', $configuration['devId'], 'Dev ID not set correctly.');
        $this->assertEquals('cert-id', $configuration['certId'], 'Cert not set correctly.');
    }
    
    /**
     *
     */
    public function testGettingCategoriesVersion()
    {
        $mockResponse = $this->generateEbaySuccessResponse(__DIR__ . '/xmlStubs/categories-general.xml');
        $this->attachMockedEbayResponse($mockResponse);

        $this->productIntegrator->updateCategoriesVersion();

        $this->assertContains('GetCategoriesRequest', $this->mockHttpClient->getRequestBody(), 'The request body does not contain the correct operation.');
        $this->assertEquals('GetCategories', $this->mockHttpClient->getApiCallName(), 'The api call is not for the correct operation');

        $this->assertEquals('113', $this->productIntegrator->getCategoriesVersion(), 'Received category version does not match.');
    }

    /**
     *
     */
    public function testGettingCategories()
    {
        $expectedData = [
            [
                'id'    => '20081',
                'name'  => 'Antiques'
            ],
            [
                'id'    => '37903',
                'name'  => 'Antiquities'
            ],
            [
                'id'    => '37908',
                'name'  => 'The Americas'
            ]
        ];

        $mockResponse = $this->generateEbaySuccessResponse(__DIR__ . '/xmlStubs/categories-all.xml');
        $this->attachMockedEbayResponse($mockResponse);

        $categories = $this->productIntegrator->getCategories();

        $this->assertContains('GetCategoriesRequest', $this->mockHttpClient->getRequestBody(), 'The request body does not contain the correct operation.');
        $this->assertEquals('GetCategories', $this->mockHttpClient->getApiCallName(), 'The api call is not for the correct operation');

        $this->assertCount(3, $categories, 'The number of categories retrieved is not correct.');
        $this->assertArrayHasKey('id', $categories[0], 'The category does not have id attribute as expected.');
        $this->assertArrayHasKey('name', $categories[0], 'The category does not have name attribute as expected.');

        $this->assertEquals($expectedData, $categories, 'The result does not match the expected result.');
    }

    /**
     *
     */
    public function testAddingProduct()
    {
        $mockResponse = $this->generateEbaySuccessResponse(__DIR__ . '/xmlStubs/add-product-response.xml');
        $this->attachMockedEbayResponse($mockResponse);

        $product = $this->sampleProduct();

        $response = $this->productIntegrator->postProduct($product);

        $this->assertContains('AddFixedPriceItemRequest', $this->mockHttpClient->getRequestBody(), 'The request body does not contain the correct operation.');
        $this->assertEquals('AddFixedPriceItem', $this->mockHttpClient->getApiCallName(), 'The api call is not for the correct operation');
    }

    public function testAddingDefaultReturnPolicy()
    {
        $mockResponse = $this->generateEbaySuccessResponse(__DIR__ . '/xmlStubs/add-product-response.xml');
        $this->attachMockedEbayResponse($mockResponse);

        $product = $this->sampleProduct();

        $response = $this->productIntegrator->postProduct($product);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertContains('ReturnPolicy', $requestBody, 'The request body does not contain the return policy information.');
        $this->assertContains('<ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>', $requestBody, 'The request body does not contain the correct return policy option.');;
        $this->assertContains('<RefundOption>MoneyBack</RefundOption>', $requestBody, 'The request body does not contain the correct refund option.');;
        $this->assertContains('<ReturnsWithinOption>Days_14</ReturnsWithinOption>', $requestBody, 'The request body does not contain the correct return limit option.');;
        $this->assertContains('<ShippingCostPaidByOption>Buyer</ShippingCostPaidByOption>', $requestBody, 'The request body does not contain the correct shipping cost option.');;
    }

    public function testAddingReturnPolicy()
    {
        $mockResponse = $this->generateEbaySuccessResponse(__DIR__ . '/xmlStubs/add-product-response.xml');
        $this->attachMockedEbayResponse($mockResponse);

        $product = $this->sampleProduct([
            'ReturnPolicy' => [
                'ReturnsAccepted' => true,
                'Refund' => 'Exchange',
                'ReturnsWithin' => 'Days_30',
                'ShippingCostPaidBy' => 'Store'
            ]
        ]);

        $response = $this->productIntegrator->postProduct($product);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertContains('ReturnPolicy', $requestBody, 'The request body does not contain the return policy information.');
        $this->assertContains('<ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>', $requestBody, 'The request body does not contain the correct return policy option.');;
        $this->assertContains('<RefundOption>Exchange</RefundOption>', $requestBody, 'The request body does not contain the correct refund option.');;
        $this->assertContains('<ReturnsWithinOption>Days_30</ReturnsWithinOption>', $requestBody, 'The request body does not contain the correct return limit option.');;
        $this->assertContains('<ShippingCostPaidByOption>Store</ShippingCostPaidByOption>', $requestBody, 'The request body does not contain the correct shipping cost option.');;
    }

    /**
     * @param array $additionalData
     * @return Product
     */
    public function sampleProduct($additionalData = [])
    {
        $product = new Product(array_merge([
            'name' => 'Apple MacBook Pro MB990LL/A 13.3 in. Notebook NEW',
            'description' => 'Brand New Apple MacBook Pro MB990LL/A 13.3 in. Notebook!',
            'sku' => 'a12345',
            'category' => '111422',
            'brand' => 'Apple',
            'price' => '1000',
            'currency' => 'USD',
            'weight' => '2000',
            'quantity' => 150
        ], $additionalData));

        return $product;
    }


}