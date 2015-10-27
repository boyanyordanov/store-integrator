<?php

namespace StoreIntegrator\tests\eBay;


use DateTime;
use StoreIntegrator\Contracts\ShippingServiceInterface;
use StoreIntegrator\eBay\CategoriesWrapper;
use StoreIntegrator\eBay\DetailsWrapper;
use StoreIntegrator\eBay\EbayProductIntegrator;
use StoreIntegrator\eBay\EbayShippingService;
use StoreIntegrator\eBay\ProductWrapper;
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

        $productWrapper = new ProductWrapper($this->userToken, $store, $this->tradingService);
        $categoriesWrapper = new CategoriesWrapper($this->userToken, $store, $this->tradingService);
        $detailsWrapper = new DetailsWrapper($this->userToken, $store, $this->tradingService);

        $this->productIntegrator = new EbayProductIntegrator(
            $productWrapper,
            $categoriesWrapper,
            $detailsWrapper);
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

        $store = $this->sampleStore();

        $integrator = new EbayProductIntegrator(
            new ProductWrapper($this->userToken, $store),
            new CategoriesWrapper($this->userToken, $store),
            new DetailsWrapper($this->userToken, $store)
        );

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

        $this->assertContains('GetCategoriesRequest', $this->mockHttpClient->getRequestBody(),
            'The request body does not contain the correct operation.');
        $this->assertEquals('GetCategories', $this->mockHttpClient->getApiCallName(),
            'The api call is not for the correct operation');

        $this->assertEquals('113', $this->productIntegrator->getCategoriesVersion(),
            'Received category version does not match.');
    }

    /**
     *
     */
    public function testGettingCategories()
    {
        $expectedData = [new \stdClass(), new \stdClass(), new \stdClass()];
        $expectedData[0]->id = '20081';
        $expectedData[0]->name = 'Antiques';
        $expectedData[0]->level = 1;
        $expectedData[0]->parentID = '20081';
        $expectedData[1]->id = '37903';
        $expectedData[1]->name = 'Antiquities';
        $expectedData[1]->level = 2;
        $expectedData[1]->parentID = '20081';
        $expectedData[2]->id = '37908';
        $expectedData[2]->name = 'The Americas';
        $expectedData[2]->level = 3;
        $expectedData[2]->parentID = '37903';

        $mockResponse = $this->generateEbaySuccessResponse(__DIR__ . '/xmlStubs/categories-all.xml');
        $this->attachMockedEbayResponse($mockResponse);

        $categories = $this->productIntegrator->getCategories();

        $this->assertContains('GetCategoriesRequest', $this->mockHttpClient->getRequestBody(),
            'The request body does not contain the correct operation.');
        $this->assertEquals('GetCategories', $this->mockHttpClient->getApiCallName(),
            'The api call is not for the correct operation');

        $this->assertCount(3, $categories, 'The number of categories retrieved is not correct.');
        $this->assertObjectHasAttribute('id', $categories[0], 'The category does not have id attribute as expected.');
        $this->assertObjectHasAttribute('name', $categories[0], 'The category does not have name attribute as expected.');
        $this->assertObjectHasAttribute('level', $categories[0], 'The category does not have name attribute as expected.');
        $this->assertObjectHasAttribute('parentID', $categories[0], 'The category does not have name attribute as expected.');
        $this->assertEquals(1, $categories[0]->level, 'The category is not on the expected level.');
        $this->assertEquals(2, $categories[1]->level, 'The category is not on the expected level.');

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

        $this->assertContains('AddFixedPriceItemRequest', $this->mockHttpClient->getRequestBody(),
            'The request body does not contain the correct operation.');
        $this->assertEquals('AddFixedPriceItem', $this->mockHttpClient->getApiCallName(),
            'The api call is not for the correct operation');
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

        $this->assertContains('ReturnPolicy', $requestBody,
            'The request body does not contain the return policy information.');
        $this->assertContains('<ReturnsAcceptedOption>ReturnsNotAccepted</ReturnsAcceptedOption>', $requestBody,
            'The request body does not contain the correct return policy option.');
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

        $this->assertContains('ReturnPolicy', $requestBody,
            'The request body does not contain the return policy information.');
        $this->assertContains('<ReturnsAcceptedOption>ReturnsAccepted</ReturnsAcceptedOption>', $requestBody,
            'The request body does not contain the correct return policy option.');;
        $this->assertContains('<RefundOption>Exchange</RefundOption>', $requestBody,
            'The request body does not contain the correct refund option.');;
        $this->assertContains('<ReturnsWithinOption>Days_30</ReturnsWithinOption>', $requestBody,
            'The request body does not contain the correct return limit option.');;
        $this->assertContains('<ShippingCostPaidByOption>Store</ShippingCostPaidByOption>', $requestBody,
            'The request body does not contain the correct shipping cost option.');;
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

        $this->assertContains('GeteBayDetails', $requestBody,
            'The requested operation is not getting details about ebay services.');
        $this->assertEquals('GeteBayDetails', $this->mockHttpClient->getApiCallName(),
            'The requested operation is not getting details about ebay services.');
        $this->assertContains('<DetailName>ShippingServiceDetails</DetailName>', $requestBody,
            'The request does not coantain the correct detail name to get available shipping methods.');

        $this->assertCount(140, $result, 'The expected number of shipping method was not returned correctly');
        $this->assertInstanceOf(ShippingServiceInterface::class, $result[0],
            'The resulting objects are not of the expected type');
        $this->assertEquals('50100', $result[0]->getId(),
            'The expected number of shipping method was not returned correctly');
        $this->assertEquals('International Priority Shipping', $result[0]->getDescription(),
            'The expected number of shipping method was not returned correctly');
    }

    /**
     *
     */
    public function testAddingShippingMethods()
    {
        $mockResponse = $this->generateEbaySuccessResponse('<xml>Success</xml>');
        $this->attachMockedEbayResponse($mockResponse);

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
        $this->assertContains('ShippingServiceOption', $requestBody, 'No domestic shipping option present.');
        $this->assertContains('<ShippingService>PostService</ShippingService>', $requestBody,
            'PostService is missing from the shipping details.');
        $this->assertContains('<ShippingServiceCost xmlns="urn:ebay:apis:eBLBaseComponents">3</ShippingServiceCost>',
            $requestBody, 'No shipping cost for the domestic option is present.');
        $this->assertContains('InternationalShippingServiceOption', $requestBody,
            'No international shipping option present.');
        $this->assertContains('<ShippingService>CourierService</ShippingService>', $requestBody,
            'CourierService is missing from the shipping details.');
        $this->assertContains('<ShippingServiceCost xmlns="urn:ebay:apis:eBLBaseComponents">8.99</ShippingServiceCost>',
            $requestBody, 'No shipping cost for the international$ option is present.');
    }

    public function testGettingProducts()
    {
        $mockResponse = $this->generateEbaySuccessResponse('<xml>Success</xml>');
        $this->attachMockedEbayResponse($mockResponse);

        $startDate = new DateTime('-1 week');
        $this->productIntegrator->getProducts($startDate);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertEquals('GetSellerList', $this->mockHttpClient->getApiCallName(), 'Incorrect api call hedaer.');
        $this->assertContains('GetSellerList', $requestBody, 'Incorect api call in the request xml.');
        $this->assertContains('<DetailLevel>ReturnAll</DetailLevel>', $requestBody, 'The detail level for the request is not set properly.');
        $this->assertContains('Pagination', $requestBody. 'No pagination data found in the request.');
        $this->assertContains('<EntriesPerPage>100</EntriesPerPage>', $requestBody. 'Unexpected value for products per page found.');
        $this->assertContains('<PageNumber>1</PageNumber>', $requestBody. 'Unexpected value for page found.');
        $this->assertContains('<StartTimeFrom>' . $startDate->format('Y-m-d\TH:i:s.000\Z') . '</StartTimeFrom>', $requestBody. 'Missing start time for range.');
        $this->assertContains('<StartTimeTo>' . (new DateTime())->format('Y-m-d\TH:i:s.000\Z') . '</StartTimeTo>', $requestBody. 'Missing end time for range.');
    }

    public function testAddingPictures()
    {
        $mockResponse = $this->generateEbaySuccessResponse('<xml>Success</xml>');
        $this->attachMockedEbayResponse($mockResponse);

        $product = $this->sampleProduct([
           'pictures'=> [
               'http://some-url.dev/picture1.jpg',
               'http://some-url.dev/picture2.jpg',
               'http://some-url.dev/picture3.jpg'
           ]
        ]);

        $this->productIntegrator->postProduct($product);

        $requestBody = $this->mockHttpClient->getRequestBody();

        $this->assertContains('PictureDetails', $requestBody, 'Missing picture details object.');
        $this->assertContains('<PictureURL>http://some-url.dev/picture1.jpg</PictureURL>', $requestBody, 'No element for picture 1.');
        $this->assertContains('<PictureURL>http://some-url.dev/picture2.jpg</PictureURL>', $requestBody, 'No element for picture 2.');
        $this->assertContains('<PictureURL>http://some-url.dev/picture3.jpg</PictureURL>', $requestBody, 'No element for picture 3.');
    }
}