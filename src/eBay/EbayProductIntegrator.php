<?php

namespace StoreIntegrator\eBay;


use DTS\eBaySDK\Trading\Enums\ListingDurationCodeType;
use DTS\eBaySDK\Trading\Enums\ListingTypeCodeType;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\AddFixedPriceItemRequestType;
use DTS\eBaySDK\Trading\Types\AmountType;
use DTS\eBaySDK\Trading\Types\GetCategoriesRequestType;
use DTS\eBaySDK\Trading\Types\ItemType;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
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
        $request = new AddFixedPriceItemRequestType();
        $request = $this->addAuthToRequest($request);

        $item = new ItemType;

        $item->ListingType = ListingTypeCodeType::C_FIXED_PRICE_ITEM;

        // Add quantity
        $item->Quantity = $product->getQuantity();

        // Renew the item every 30 days until the user cancels it
        $item->ListingDuration = ListingDurationCodeType::C_GTC;

        // Add price
        $item->StartPrice = new AmountType(['value' => $product->getPrice()]);

        $item->Title = $product->getTitle();
        $item->Description = $product->getDescription();
        $item->SKU = $product->getSku();
//        $item->Country = 'US';
//        $item->Location = 'Beverly Hills';
//        $item->PostalCode = '90210';

        $item->Currency = $product->getCurrency();

        // Condition (should be brand new)
        $item->ConditionID = 1000;

        // TODO: Check data for payments, shipping and return policy

        $request->Item = $item;

        $response = $this->service->addFixedPriceItem($request);

        // TODO: handle errors

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

    /**
     * @param $request
     * @return mixed $request
     */
    protected function addAuthToRequest($request)
    {
        $request->RequesterCredentials = new CustomSecurityHeaderType();
        // TODO: Add way to add real user token
        $request->RequesterCredentials->eBayAuthToken = 'some-user-token';

        return $request;
    }
}