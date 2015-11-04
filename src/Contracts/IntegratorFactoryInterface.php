<?php

namespace StoreIntegrator\Contracts;

use StoreIntegrator\Product;
use StoreIntegrator\ShippingService;

/**
 * Interface IntegratorFactoryInterface
 * @package StoreIntegrator\Contracts
 */
interface IntegratorFactoryInterface
{
    /**
     * @param array $data
     * @return Product
     */
    public function makeProduct($data);

    /**
     * @param array $data
     * @return ShippingService
     */
    public function makeShippingService($data);

    /**
     * @param array $data
     * @return Store
     */
    public function makeStore($data);
}