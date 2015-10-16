<?php

namespace StoreIntegrator\tests\eBay;


use StoreIntegrator\tests\TestCase;

class EbayProductIntegratorTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->setUpEbayServiceMocks();
    }

    public function testGettingCategorVersion()
    {
        // should get categories version
        // load current version from configs
        // should then get the current categories
        // Should expose methods to do the checks and to update the version in the configs
    }
}