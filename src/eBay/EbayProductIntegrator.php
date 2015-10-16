<?php

namespace StoreIntegrator\eBay;


use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\GetCategoriesRequestType;
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
     * @var string
     */
    protected $categoriesVersion = '113';

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
            // TODO: implement configuration from environment variables
            $this->service = new TradingService();
        } else {
            $this->service = $service;
        }
    }

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
     * @return array
     */
    public function getCategories()
    {
        $categoriesRequest = new GetCategoriesRequestType([
            // TODO: get the version from environment
            'Version'    => '943'
        ]);

        $categoriesRequest->DetailLevel = ['ReturnAll'];

        $response = $this->service->getCategories($categoriesRequest);

        $result = [];

        $this->categoriesVersion = $response->CategoryVersion;

        $categories = $response->toArray()['CategoryArray']['Category'];

        foreach($categories as $item) {
            array_push($result, [
                'id' => $item['CategoryID'],
                'name' => $item['CategoryName']
            ]);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function updateCategoriesVersion()
    {
        $categoriesRequest = new GetCategoriesRequestType([
            // TODO: get the version from environment
            'Version'    => '943'
        ]);

        $response = $this->service->getCategories($categoriesRequest);

        $this->categoriesVersion = $response->CategoryVersion;

        return $response->toArray();
    }

    /**
     * @return int
     */
    public function getCategoriesVersion()
    {
        return $this->categoriesVersion;
    }
}