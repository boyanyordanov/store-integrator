<?php

namespace StoreIntegrator\eBay;

use DTS\eBaySDK\Trading\Enums\ListingDurationCodeType;
use DTS\eBaySDK\Trading\Enums\ListingTypeCodeType;
use DTS\eBaySDK\Trading\Enums\ShippingTypeCodeType;
use DTS\eBaySDK\Trading\Types\AddFixedPriceItemRequestType;
use DTS\eBaySDK\Trading\Types\AmountType;
use DTS\eBaySDK\Trading\Types\CategoryType;
use DTS\eBaySDK\Trading\Types\GetSellerListRequestType;
use DTS\eBaySDK\Trading\Types\InternationalShippingServiceOptionsType;
use DTS\eBaySDK\Trading\Types\ItemType;
use DTS\eBaySDK\Trading\Types\PaginationType;
use DTS\eBaySDK\Trading\Types\ReturnPolicyType;
use DTS\eBaySDK\Trading\Types\ShippingDetailsType;
use DTS\eBaySDK\Trading\Types\ShippingServiceOptionsType;
use StoreIntegrator\Product;

/**
 * Class ProductWrapper
 * @package StoreIntegrator\eBay
 */
class ProductWrapper extends EbayWrapper
{
    /**
     * @param Product $product
     * @return \DTS\eBaySDK\Trading\Types\AddFixedPriceItemResponseType
     */
    public function post(Product $product)
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
        $item->Location = $this->store->getStoreData('location');
        $item->PostalCode = $this->store->getStoreData('postCode');

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
        $item->PaymentMethods = $this->store->getPaymentOptions();
        $item->PayPalEmailAddress = $this->store->getPaypalEmail();
        $item->DispatchTimeMax = $this->store->getStoreData('dispatchTime');

        $this->addShippingOptions($item, $product->getShippingOptions());

        $this->addReturnPolicy($item, $product->getReturnPolicy());

        // End hard coded shipping payment and return policy

        $request->Item = $item;

        $response = $this->service->addFixedPriceItem($request);

        // TODO: handle errors

        return $response;
    }

    /**
     * @param $item
     * @param array $overrides
     */
    protected function addReturnPolicy($item, $overrides = [])
    {
        /**
         * Default return policy.
         * Returns are accepted.
         * A refund will be given as money back.
         * The buyer will have 14 days in which to contact the seller after receiving the item.
         * The buyer will pay the return shipping cost.
         */
        $default = [
            'ReturnsAccepted' => true,
            'Refund' => 'MoneyBack',
            'ReturnsWithin' => 'Days_14',
            'ShippingCostPaidBy' => 'Buyer'
        ];

        $policy = array_merge($default, $overrides);

        $item->ReturnPolicy = new ReturnPolicyType();
        $item->ReturnPolicy->ReturnsAcceptedOption = $policy['ReturnsAccepted'] ? 'ReturnsAccepted' : 'ReturnsAccepted';
        $item->ReturnPolicy->RefundOption = $policy['Refund'];
        $item->ReturnPolicy->ReturnsWithinOption = $policy['ReturnsWithin'];
        $item->ReturnPolicy->ShippingCostPaidByOption = $policy['ShippingCostPaidBy'];
    }

    /**
     * @param $item
     * @param array $shippingOptions
     */
    public function addShippingOptions($item, array $shippingOptions = [])
    {
        /**
         * Setting up the shipping details.
         * We will use a Flat shipping rate for both domestic and international.
         */
        $item->ShippingDetails = new ShippingDetailsType();
        $item->ShippingDetails->ShippingType = ShippingTypeCodeType::C_FLAT;

        /**
         * @var EbayShippingService $shippingOption
         */
        foreach($shippingOptions as $index => $shippingOption) {
            if($shippingOption->getInternational()) {
                $shippingService = new InternationalShippingServiceOptionsType();
                $shippingService->ShippingServicePriority = $index;
                $shippingService->ShippingService = $shippingOption->getName();
                $shippingService->ShippingServiceCost = new AmountType(array('value' => $shippingOption->getCost()));
                $shippingService->ShipToLocation = $shippingOption->getShipsTo();
                $item->ShippingDetails->InternationalShippingServiceOption[] = $shippingService;
            } else {
                $shippingService = new ShippingServiceOptionsType();
                $shippingService->ShippingServicePriority = $index;
                $shippingService->ShippingService = $shippingOption->getName();
                $shippingService->ShippingServiceCost = new AmountType(array('value' => $shippingOption->getCost()));
                $item->ShippingDetails->ShippingServiceOptions[] = $shippingService;
            }
        }
    }

    /**
     * @param int $page
     * @param int $perPage
     * @return \DTS\eBaySDK\Trading\Types\GetSellerListResponseType
     */
    public function getAll($page = 1, $perPage = 100)
    {
        $request = new GetSellerListRequestType();

        $request->DetailLevel = ['ReturnAll'];

        $request->Pagination = new PaginationType();
        $request->Pagination->EntriesPerPage = $perPage;
        $request->Pagination->PageNumber = $page;

        // TODO: Don't hard-code those
        $request->StartTimeFrom = date_create('2015-10-01');
        $request->StartTimeTo = date_create();

        $this->addAuthToRequest($request);

        $response = $this->service->getSellerList($request);

        return $response;
    }
}