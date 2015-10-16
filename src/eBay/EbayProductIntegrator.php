<?php

namespace StoreIntegrator\eBay;


use DTS\eBaySDK\Trading\Services\TradingService;
use StoreIntegrator\Contracts\CategoriesAggregatorInterface;
use StoreIntegrator\Contracts\ProductIntegratorInterface;
use StoreIntegrator\Product;

/**
 * Class EbayProductIntegrator
 * @package StoreIntegrator\eBay
 */
class EbayProductIntegrator implements ProductIntegratorInterface, CategoriesAggregatorInterface
{
    /**
     * @var int
     */
    protected $categoriesVersion = 113;

    /**
     * @var TradingService
     */
    protected $service;

    /**
     * @param TradingService|null $service
     */
    public function __construct(TradingService $service = null)
    {
        if(is_null($service)) {
            $this->service = new TradingService();
        } else {
            $this->service = $service;
        }
    }

    /**
     *
     * @param array $product
     * @return mixed
     */
    public function postProduct(array $product)
    {
        // TODO: Implement postProduct() method.
    }

    /**
     * @param array $products
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
     * Returns an array of categories to map to the product
     * Each category is an array with id and name
     *
     * @return array
     */
    public function getCategories()
    {

    }

    /**
     * @return int
     */
    public function getCategoriesVersion()
    {
        return $this->categoriesVersion;
    }
}