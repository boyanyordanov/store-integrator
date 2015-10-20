<?php

namespace StoreIntegrator\tests\eBay;


use StoreIntegrator\Contracts\ShippingServiceInterface;
use StoreIntegrator\eBay\EbayProductIntegrator;
use StoreIntegrator\eBay\EbayShippingService;
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

    /**
     *
     */
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
        $expectedData = [new \stdClass(), new \stdClass(), new \stdClass()];
        $expectedData[0]->id = '20081';
        $expectedData[0]->name = 'Antiques';
        $expectedData[1]->id = '37903';
        $expectedData[1]->name = 'Antiquities';
        $expectedData[2]->id = '37908';
        $expectedData[2]->name = 'The Americas';

        $mockResponse = $this->generateEbaySuccessResponse(__DIR__ . '/xmlStubs/categories-all.xml');
        $this->attachMockedEbayResponse($mockResponse);

        $categories = $this->productIntegrator->getCategories();

        $this->assertContains('GetCategoriesRequest', $this->mockHttpClient->getRequestBody(), 'The request body does not contain the correct operation.');
        $this->assertEquals('GetCategories', $this->mockHttpClient->getApiCallName(), 'The api call is not for the correct operation');

        $this->assertCount(3, $categories, 'The number of categories retrieved is not correct.');
        $this->assertObjectHasAttribute('id', $categories[0], 'The category does not have id attribute as expected.');
        $this->assertObjectHasAttribute('name', $categories[0], 'The category does not have name attribute as expected.');

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

        $this->productIntegrator->postProduct($product);

        $this->assertContains('AddFixedPriceItemRequest', $this->mockHttpClient->getRequestBody(), 'The request body does not contain the correct operation.');
        $this->assertEquals('AddFixedPriceItem', $this->mockHttpClient->getApiCallName(), 'The api call is not for the correct operation');
    }

    /**
     *
     */
    public function testAddingDefaultReturnPolicy()
    {
        $mockResponse = $this->generateEbaySuccessResponse(__DIR__ . '/xmlStubs/add-product-response.xml');
        $this->attachMockedEbayResponse($mockResponse);

        $product = $this->sampleProduct();

        $this->productIntegrator->postProduct($product);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertContains('ReturnPolicy', $requestBody, 'The request body does not contain the return policy information.');
        $this->assertContains('<ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>', $requestBody, 'The request body does not contain the correct return policy option.');;
        $this->assertContains('<RefundOption>MoneyBack</RefundOption>', $requestBody, 'The request body does not contain the correct refund option.');;
        $this->assertContains('<ReturnsWithinOption>Days_14</ReturnsWithinOption>', $requestBody, 'The request body does not contain the correct return limit option.');;
        $this->assertContains('<ShippingCostPaidByOption>Buyer</ShippingCostPaidByOption>', $requestBody, 'The request body does not contain the correct shipping cost option.');;
    }

    /**
     *
     */
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

        $this->productIntegrator->postProduct($product);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertContains('ReturnPolicy', $requestBody, 'The request body does not contain the return policy information.');
        $this->assertContains('<ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>', $requestBody, 'The request body does not contain the correct return policy option.');;
        $this->assertContains('<RefundOption>Exchange</RefundOption>', $requestBody, 'The request body does not contain the correct refund option.');;
        $this->assertContains('<ReturnsWithinOption>Days_30</ReturnsWithinOption>', $requestBody, 'The request body does not contain the correct return limit option.');;
        $this->assertContains('<ShippingCostPaidByOption>Store</ShippingCostPaidByOption>', $requestBody, 'The request body does not contain the correct shipping cost option.');;
    }

    /**
     *
     */
    public function testRetrievingAvailableShippingMethods()
    {
        $mockResponse = $this->generateEbaySuccessResponse(__DIR__ . '/xmlStubs/shipping-methods-response.xml');
        $this->attachMockedEbayResponse($mockResponse);

        $result = $this->productIntegrator->getAvailableShippingMethods();

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertContains('GeteBayDetails', $requestBody, 'The requested operation is not getting details about ebay services.');
        $this->assertEquals('GeteBayDetails', $this->mockHttpClient->getApiCallName(), 'The requested operation is not getting details about ebay services.');
        $this->assertContains('<DetailName>ShippingServiceDetails</DetailName>', $requestBody, 'The request does not coantain the correct detail name to get available shipping methods.');

        $this->assertCount(140, $result, 'The expected number of shipping method was not returned correctly');
        $this->assertInstanceOf(ShippingServiceInterface::class, $result[0], 'The resulting objects are not of the expected type');
        $this->assertEquals('50100', $result[0]->getId(), 'The expected number of shipping method was not returned correctly');
        $this->assertEquals('International Priority Shipping', $result[0]->getDescription(), 'The expected number of shipping method was not returned correctly');
    }

    /**
     *
     */
    public function testAddingShippingMethods()
    {
        $product = $this->sampleProduct([
            'shippingOptions' => [
                new EbayShippingService([
                    'id' => '123',
                    'name' => 'PostService',
                    'description' => '',
                    'international' => false,
                    'cost' => 3.00
                ]),
                new EbayShippingService([
                    'id' => '1234',
                    'name' => 'CourierService',
                    'description' => '',
                    'international' => true,
                    'cost' => 8.99,
                    'shipsTo' => ['USA', 'UK', "Europe"]
                ])
            ]
        ]);

        $this->productIntegrator->postProduct($product);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertContains('ShippingDetails', $requestBody, 'No shipping details present.');
        $this->assertContains('<ShippingServiceOption>', $requestBody, 'No domestic shipping option present.');
        $this->assertContains('<ShippingService>PostService</ShippingService>', $requestBody, 'PostSercie is missing from the shipping details.');
        $this->assertContains('<ShippingServiceCost xmlns="urn:ebay:apis:eBLBaseComponents">3</ShippingServiceCost>', $requestBody, 'No shipping cost for the domestic option is present.');
        $this->assertContains('<InternationalShippingServiceOption>', $requestBody, 'No international shipping option present.');
        $this->assertContains('<ShippingService>CourierService</ShippingService>', $requestBody, 'CourierService is missing from the shipping details.');
        $this->assertContains('<ShippingServiceCost xmlns="urn:ebay:apis:eBLBaseComponents">8.99</ShippingServiceCost>', $requestBody, 'No shipping cost for the international$ option is present.');
    }

}