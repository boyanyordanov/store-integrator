<?php

namespace StoreIntegrator\eBay;

use StoreIntegrator\Contracts\IntegratorFactoryInterface;
use StoreIntegrator\Product;
use StoreIntegrator\ShippingService;
use StoreIntegrator\Store;

/**
 * Class EbayFactory
 * @package StoreIntegrator\eBay
 */
class EbayFactory implements IntegratorFactoryInterface
{

    /**
     * @param array $data
     * @return Product
     */
    public function makeProduct($data)
    {
        return new Product($data);
    }

    /**
     * @param array $data
     * @return ShippingService
     */
    public function makeShippingService($data)
    {
        return new EbayShippingService($data);
    }

    /**
     * @param array $data
     * @return Store
     */
    public function makeStore($data)
    {
        return new Store($data);
    }
}