<?php

namespace StoreIntegrator\tests\eBay;

use StoreIntegrator\eBay\EbayShippingService;

class EbayShippingServiceTest extends \PHPUnit_Framework_TestCase
{

    public function testCreatingFromArray()
    {
        $service = new EbayShippingService([
            'id' => 1,
            'name' => 'Post',
            'description' => 'Not very fast, but reliable',
            'ShippingMinTime' => 3,
            'ShippingMaxTime' => 10
        ]);

        $this->assertEquals(1, $service->getId(), 'ID not set correctly.');
        $this->assertEquals('Post', $service->getName(), 'Name not set correctly.');
        $this->assertEquals('Not very fast, but reliable', $service->getDescription(), 'Description not set correctly.');
    }
}
