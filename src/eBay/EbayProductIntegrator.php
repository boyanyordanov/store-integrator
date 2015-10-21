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
     * @var string
     */
    protected $categoriesVersion = '113';

    /**
     * @var TradingService
     */
    protected $service;

    /**
     * @var ProductWrapper
     */
    private $productWrapper;
    /**
     * @var CategoriesWrapper
     */
    private $categoriesWrapper;
    /**
     * @var DetailsWrapper
     */
    private $detailsWrapper;

    /**
     * @param ProductWrapper $productWrapper
     * @param CategoriesWrapper $categoriesWrapper
     * @param DetailsWrapper $detailsWrapper
     * @internal param TradingService|null $service
     */
    public function __construct(ProductWrapper $productWrapper, CategoriesWrapper $categoriesWrapper, DetailsWrapper $detailsWrapper)
    {
//        if (is_null($service)) {
//            // TODO: implement configuration from environment variables
//            $this->service = new TradingService([
//                'apiVersion' => getenv('EBAY-TRADING-API-VERSION'),
//                'sandbox' => true,
//                'siteId' => SiteIds::US,
//                'devId' => getenv('EBAY-DEV-ID'),
//                'appId' => getenv('EBAY-APP-ID'),
//                'certId' => getenv('EBAY-CERT-ID'),
//            ]);
//        } else {
//            $this->service = $service;
//        }
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
        $categories = $this->categoriesWrapper->get();

        $this->categoriesVersion = $this->categoriesWrapper->getVersion();

        $result = [];

        foreach ($categories as $item) {
            $cat = new \stdClass;
            $cat->id = $item['CategoryID'];
            $cat->name = $item['CategoryName'];

            array_push($result, $cat);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function updateCategoriesVersion()
    {
        $response = $this->categoriesWrapper->update();

        $this->categoriesVersion = $this->categoriesWrapper->getVersion();

        return $response;
    }

    /**
     * @return int
     */
    public function getCategoriesVersion()
    {
        return $this->categoriesVersion;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->productWrapper->getConfig();
    }

    /**
     *
     */
    public function getAvailableShippingMethods()
    {
        $shippingMethods = $this->detailsWrapper->getShippingMethods();

        return $shippingMethods;
    }
}