<?php

namespace StoreIntegrator\tests\eBay;


use StoreIntegrator\eBay\EbayProductIntegrator;
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
        $this->productIntegrator = new EbayProductIntegrator();

        parent::setUp();

        $this->setUpEbayServiceMocks();
    }

    /**
     *
     */
    public function testGettingCategorVersion()
    {
        $mockResponse = $this->generateEbaySuccessResponse(__DIR__ . '/xmlStubs/categories-general.xml');
        $this->attachMockedEbayResponse($mockResponse);

        $this->productIntegrator->getCategories();

        $this->assertEquals(113, $this->productIntegrator->getCategoriesVersion(), 'Received category version does not match.');
    }
}