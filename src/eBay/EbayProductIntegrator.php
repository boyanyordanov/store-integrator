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
     * Version of the categories used.
     * Has a default value but is update when the category hierarchy is called.
     *
     * @var string
     */
    protected $categoriesVersion = '113';

    /**
     * Instance of the API wrapper for the methods working with products
     *
     * @var ProductWrapper
     */
    private $productWrapper;

    /**
     * Instance of the API wrapper for the methods working with categories
     *
     * @var CategoriesWrapper
     */
    private $categoriesWrapper;

    /**
     * Instance of the API wrapper for the methods working with ebay settings and options
     *
     * @var DetailsWrapper
     */
    private $detailsWrapper;

    /**
     * @param ProductWrapper $productWrapper
     * @param CategoriesWrapper $categoriesWrapper
     * @param DetailsWrapper $detailsWrapper
     */
    public function __construct(ProductWrapper $productWrapper, CategoriesWrapper $categoriesWrapper, DetailsWrapper $detailsWrapper)
    {
        $this->productWrapper = $productWrapper;
        $this->categoriesWrapper = $categoriesWrapper;
        $this->detailsWrapper = $detailsWrapper;
    }

    /**
     * Posts a product to eBay.
     *
     * @param Product $product
     * @return mixed
     */
    public function postProduct(Product $product)
    {
        $response = $this->productWrapper->post($product);

        return $response;
    }

    /**
     * Post multiple products to eBay
     *
     * @param array $products
     * @return mixed
     */
    public function postProducts(array $products)
    {
        $responses = [];

        foreach($products as $product) {
            $responses[] = $this->productWrapper->post($product);
        }

        return $responses;
    }

    /**
     * Get products for the current user.
     * Has pagination
     *
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getProducts($page = 1, $perPage = 100)
    {
        return $this->productWrapper->getAll($page, $perPage);
    }

    /**
     * Returns an array of categories to map to the product
     * Each category is an array with id and name

     * @return array
     */
    public function getCategories()
    {
        $categories = $this->categoriesWrapper->get();

        $this->categoriesVersion = $this->categoriesWrapper->getVersion();

        $result = [];

        foreach ($categories as $item) {
            $cat = new \stdClass;
            $cat->id = $item['CategoryID'];
            $cat->name = $item['CategoryName'];
            $cat->level = $item['CategoryLevel'];
            $cat->parentID = $item['CategoryParentID'][0];

            array_push($result, $cat);
        }

        return $result;
    }

    /**
     * Calls the eBay API to get the current version of the category hierarchy
     *
     * @return array
     */
    public function updateCategoriesVersion()
    {
        $response = $this->categoriesWrapper->update();

        $this->categoriesVersion = $this->categoriesWrapper->getVersion();

        return $response;
    }

    /**
     * Getter for the version of the category hierarchy used for the requests.
     *
     * @return int
     */
    public function getCategoriesVersion()
    {
        return $this->categoriesVersion;
    }

    /**
     * Returns the settigns used to configure the product (and the rest) wrapper
     *
     * @return mixed
     */
    public function getConfig()
    {
        return $this->productWrapper->getConfig();
    }

    /**
     * Calls the eBay API and returns a list of all available shipping methods for the selected eBay site.
     *
     * @return array
     */
    public function getAvailableShippingMethods()
    {
        $shippingMethods = $this->detailsWrapper->getShippingMethods();

        return $shippingMethods;
    }
}