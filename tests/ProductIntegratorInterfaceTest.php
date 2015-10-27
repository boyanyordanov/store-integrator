<?php

namespace StoreIntegrator\tests;

use DateTime;
use StoreIntegrator\Contracts\OrderIntegratorInterface;
use StoreIntegrator\Contracts\ProductIntegratorInterface;
use StoreIntegrator\Product;


class IntegratorMock implements ProductIntegratorInterface, OrderIntegratorInterface {

    /**
     *
     * @param Product $product
     * @return mixed
     */
    public function postProduct(Product $product)
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
     * @param DateTime $startDate
     * @return array
     */
    public function getProducts(DateTime $startDate)
    {
        // TODO: Implement getProducts() method.
    }

    /**
     * @param DateTime $startDate
     * @return mixed
     */
    public function getOrders(DateTIme $startDate)
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
     * @param $orderId
     * @param $fulfillmentData
     * @return mixed
     */
    public function fulfilOrder($orderId, $fulfillmentData)
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
