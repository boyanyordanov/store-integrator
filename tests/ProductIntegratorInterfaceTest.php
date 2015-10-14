<?php

namespace StoreIntegrator\tests;

use StoreIntegrator\Contracts\OrderIntegratorInterface;
use StoreIntegrator\Contracts\ProductIntegratorInterface;


class IntegratorMock implements ProductIntegratorInterface, OrderIntegratorInterface {

    /**
     *
     * @return mixed
     */
    public function postProduct(array $product)
    {
        // TODO: Implement postProduct() method.
    }

    /**
     * @return mixed
     */
    public function postProducts(array $products)
    {
        // TODO: Implement postProducts() method.
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        // TODO: Implement getProducts() method.
    }

    /**
     * @return mixed
     */
    public function getOrders()
    {
        // TODO: Implement getOrders() method.
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getOrder($id)
    {
        // TODO: Implement getOrder() method.
    }

    /**
     * @return mixed
     */
    public function fulfilOrder()
    {
        // TODO: Implement fulfilOrder() method.
    }
}

class InterfacesTestTest extends TestCase
{
    public function testProductIntegratorInterfaceInstance()
    {
        $mock = new IntegratorMock();

        $this->assertInstanceOf(ProductIntegratorInterface::class, $mock, 'The expected interface is not implemented');
    }

    public function testOrderIntegratorInterfaceInstance()
    {
        $mock = new IntegratorMock();

        $this->assertInstanceOf(OrderIntegratorInterface::class, $mock, 'The expected interface is not implemented');
    }
}
