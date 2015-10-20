<?php

namespace StoreIntegrator\tests;

use StoreIntegrator\ShippingService;

class ConcreteShippingService extends ShippingService
{
    public function __construct($data)
    {
        parent::__construct($data['id'], $data['name'], $data['description'], [
            'minTime' => $data['ShippingMinTime'],
            'maxTime' => $data['ShippingMaxTime']
        ]);
    }
}

class ShippingServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testAddingProviderSpecificProperties()
    {
        $shippingService = new ConcreteShippingService([
            'id' => 1,
            'name' => 'Post',
            'description' => 'Not very fast, but reliable',
            'ShippingMinTime' => 3,
            'ShippingMaxTime' => 10
        ]);

        $this->assertEquals(1, $shippingService->getId(), 'The getter for id did not work correctly.');
        $this->assertEquals('Post', $shippingService->getName(), 'The getter for name did not work correctly.');
        $this->assertEquals('Not very fast, but reliable', $shippingService->getDescription(), 'The getter for description did not work correctly.');
        $this->assertEquals(3, $shippingService->getMinTime(), 'The dynamic method for minTime was not mapped correctly.');
        $this->assertEquals(10, $shippingService->getMaxTime(), 'The dynamic method for maxTime was not mapped correctly.');
    }
}
