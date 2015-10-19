<?php

namespace StoreIntegrator\eBay;


use DTS\eBaySDK\Constants\SiteIds;
use DTS\eBaySDK\Trading\Enums\ListingDurationCodeType;
use DTS\eBaySDK\Trading\Enums\ListingTypeCodeType;
use DTS\eBaySDK\Trading\Enums\ShippingTypeCodeType;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\AddFixedPriceItemRequestType;
use DTS\eBaySDK\Trading\Types\AmountType;
use DTS\eBaySDK\Trading\Types\CategoryType;
use DTS\eBaySDK\Trading\Types\GetCategoriesRequestType;
use DTS\eBaySDK\Trading\Types\InternationalShippingServiceOptionsType;
use DTS\eBaySDK\Trading\Types\ItemType;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
use DTS\eBaySDK\Trading\Types\ReturnPolicyType;
use DTS\eBaySDK\Trading\Types\ShippingDetailsType;
use DTS\eBaySDK\Trading\Types\ShippingServiceOptionsType;
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
     * @var
     */
    protected $userToken;

    /**
     * @param TradingService|null $service
     */
    public function __construct(TradingService $service = null)
    {
        if(is_null($service)) {
            // TODO: implement configuration from environment variables
            $this->service = new TradingService([
                'apiVersion' => getenv('EBAY-TRADING-API-VERSION'),
                'sandbox' => true,
                'siteId' => SiteIds::US,
                'devId' => getenv('EBAY-DEV-ID'),
                'appId' => getenv('EBAY-APP-ID'),
                'certId' => getenv('EBAY-CERT-ID'),
            ]);
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
        $item->Country = $product->getCountry();
        $item->Location = 'Beverly Hills';
        $item->PostalCode = '90210';

        $item->Currency = $product->getCurrency();

        $item->PrimaryCategory = new CategoryType();
        $item->PrimaryCategory->CategoryID = $product->getCategory();

        // Condition (should be brand new)
        $item->ConditionID = 1000;

        // TODO: Check data for payments, shipping and return policy

        // Start hard-coded shipping, payment and retrn policy
        /**
         * Buyers can use one of two payment methods when purchasing the item.
         * Visa / Master Card
         * PayPal
         * The item will be dispatched within 1 business days once payment has cleared.
         * Note that you have to provide the PayPal account that the seller will use.
         * This is because a seller may have more than one PayPal account.
         */
        $item->PaymentMethods = array(
            'VisaMC',
            'PayPal'
        );
        $item->PayPalEmailAddress = 'example@example.com';
        $item->DispatchTimeMax = 1;
        /**
         * Setting up the shipping details.
         * We will use a Flat shipping rate for both domestic and international.
         */
        $item->ShippingDetails = new ShippingDetailsType();
        $item->ShippingDetails->ShippingType = ShippingTypeCodeType::C_FLAT;
        /**
         * Create our first domestic shipping option.
         * Offer the Economy Shipping (1-10 business days) service at $2.00 for the first item.
         * Additional items will be shipped at $1.00.
         */
        $shippingService = new ShippingServiceOptionsType();
        $shippingService->ShippingServicePriority = 1;
        $shippingService->ShippingService = 'Other';
        $shippingService->ShippingServiceCost = new AmountType(array('value' => 2.00));
        $shippingService->ShippingServiceAdditionalCost = new AmountType(array('value' => 1.00));
        $item->ShippingDetails->ShippingServiceOptions[] = $shippingService;
        /**
         * Create our second domestic shipping option.
         * Offer the USPS Parcel Select (2-9 business days) at $3.00 for the first item.
         * Additional items will be shipped at $2.00.
         */
        $shippingService = new ShippingServiceOptionsType();
        $shippingService->ShippingServicePriority = 2;
        $shippingService->ShippingService = 'USPSParcel';
        $shippingService->ShippingServiceCost = new AmountType(array('value' => 3.00));
        $shippingService->ShippingServiceAdditionalCost = new AmountType(array('value' => 2.00));
        $item->ShippingDetails->ShippingServiceOptions[] = $shippingService;
        /**
         * Create our first international shipping option.
         * Offer the USPS First Class Mail International service at $4.00 for the first item.
         * Additional items will be shipped at $3.00.
         * The item can be shipped Worldwide with this service.
         */
        $shippingService = new InternationalShippingServiceOptionsType();
        $shippingService->ShippingServicePriority = 1;
        $shippingService->ShippingService = 'USPSFirstClassMailInternational';
        $shippingService->ShippingServiceCost = new AmountType(array('value' => 4.00));
        $shippingService->ShippingServiceAdditionalCost = new AmountType(array('value' => 3.00));
        $shippingService->ShipToLocation = array('WorldWide');
        $item->ShippingDetails->InternationalShippingServiceOption[] = $shippingService;
        /**
         * Create our second international shipping option.
         * Offer the USPS Priority Mail International (6-10 business days) service at $5.00 for the first item.
         * Additional items will be shipped at $4.00.
         * The item will only be shipped to the following locations with this service.
         * N. and S. America
         * Canada
         * Australia
         * Europe
         * Japan
         */
        $shippingService = new InternationalShippingServiceOptionsType();
        $shippingService->ShippingServicePriority = 2;
        $shippingService->ShippingService = 'USPSPriorityMailInternational';
        $shippingService->ShippingServiceCost = new AmountType(array('value' => 5.00));
        $shippingService->ShippingServiceAdditionalCost = new AmountType(array('value' => 4.00));
        $shippingService->ShipToLocation = array(
            'Americas',
            'CA',
            'AU',
            'Europe',
            'JP'
        );
        $item->ShippingDetails->InternationalShippingServiceOption[] = $shippingService;
        /**
         * The return policy.
         * Returns are accepted.
         * A refund will be given as money back.
         * The buyer will have 14 days in which to contact the seller after receiving the item.
         * The buyer will pay the return shipping cost.
         */
        $item->ReturnPolicy = new ReturnPolicyType();
        $item->ReturnPolicy->ReturnsAcceptedOption = 'ReturnsAccepted';
        $item->ReturnPolicy->RefundOption = 'MoneyBack';
        $item->ReturnPolicy->ReturnsWithinOption = 'Days_14';
        $item->ReturnPolicy->ShippingCostPaidByOption = 'Buyer';

        // End hard coded shipping payment and return policy

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

        $this->addAuthToRequest($categoriesRequest);

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
        $request->RequesterCredentials->eBayAuthToken = $this->userToken;

        return $request;
    }

    /**
     * @param $token
     */
    public function addUserToken($token)
    {
        $this->userToken = $token;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->service->config();
    }
}