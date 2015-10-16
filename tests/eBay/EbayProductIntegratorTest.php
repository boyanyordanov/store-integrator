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

        $product = new Product([
            'name' => 'Apple MacBook Pro MB990LL/A 13.3 in. Notebook NEW',
            'description' => 'Brand New Apple MacBook Pro MB990LL/A 13.3 in. Notebook!',
            'sku' => 'a12345',
            'category' => '111422',
            'brand' => 'Apple',
            'price' => '1000',
            'currency' => 'USD',
            'weight' => '2000'
        ]);

        $response = $this->productIntegrator->postProduct($product);

        $this->assertContains('AddFixedPriceItemRequest', $this->mockHttpClient->getRequestBody(), 'The request body does not contain the correct operation.');
        $this->assertEquals('AddFixedPriceItem', $this->mockHttpClient->getApiCallName(), 'The api call is not for the correct operation');
    }
}