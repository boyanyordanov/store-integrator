<?php
/**
 * Created by PhpStorm.
 * User: boyan
 * Date: 09.11.15
 * Time: 09:10
 */

namespace StoreIntegrator\tests\eBay;


use StoreIntegrator\eBay\EbayProvider;
use StoreIntegrator\tests\TestCase;

/**
 * Class EbayProviderMock
 * @package StoreIntegrator\tests\eBay
 */
class EbayProviderMock extends EbayProvider {
    /**
     * @param array $ebayConfig
     * @param $service
     */
    public function __construct($ebayConfig, $service)
    {
        $this->service = $service;

        parent::__construct($ebayConfig);
    }
}

/**
 * Class EbayProviderTest
 * @package StoreIntegrator\tests\eBay
 */
class EbayProviderTest extends TestCase
{
    /**
     * @var EbayProviderMock
     */
    protected $ebayProvider;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->setUpEbayServiceMocks();

        $config = [
            'store' => [
                'email' => 'ebay_test@some-mail.dev',
                'data' => [
                    'location' => 'Varna',
                    'country' => 'BG'
                ],
                'ebaySite' => 'GB'
            ],
        ];

        $this->ebayProvider = new EbayProviderMock($config, $this->tradingService);
    }

    /**
     *
     */
    public function testGettingSiteIds()
    {
        $countries = $this->ebayProvider->getSiteIds();

        $this->assertCount(22, $countries);
        $this->assertEquals('US', $countries[0]);
    }
}
