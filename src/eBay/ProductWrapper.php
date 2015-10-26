<?php

namespace StoreIntegrator\eBay;

use DTS\eBaySDK\Trading\Types\NameValueListArrayType;
use DTS\eBaySDK\Trading\Types\NameValueListType;
use DTS\eBaySDK\Trading\Enums\GalleryTypeCodeType;
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
use DTS\eBaySDK\Trading\Types\PictureDetailsType;
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

        $item->Country = $this->store->getStoreData('country');
        $item->Location = $this->store->getStoreData('location');

        if($this->store->hasStoreData('postCode')) {
            $item->PostalCode = $this->store->getStoreData('postCode');
        }

        $item->Currency = $product->getCurrency();

        $item->PrimaryCategory = new CategoryType();
        $item->PrimaryCategory->CategoryID = $product->getCategory();

        // Condition (should be brand new)
        $item->ConditionID = 1000;

        $item->ItemSpecifics = new NameValueListArrayType();

        $brand = new NameValueListType();
        $brand->Name = 'Brand';
        $brand->Value[] = $product->getBrand();

        $item->ItemSpecifics->NameValueList[] = $brand;

        // Add pictures
        $this->addPictures($item, $product);

        // Add payement
        $item->PaymentMethods = $this->store->getPaymentOptions();
        $item->PayPalEmailAddress = $this->store->getPaypalEmail();
        $item->DispatchTimeMax = $this->store->getStoreData('dispatchTime');

        // Add shipping options and return policy
        $this->addShippingOptions($item, $product->getShippingOptions());

        $this->addReturnPolicy($item, $product->getReturnPolicy());

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

    /**
     * @param $item
     * @param $product
     */
    public function addPictures($item, $product)
    {
        $result = [];

        foreach($product->getPictures() as $pictureUrl) {
            $result[] = $pictureUrl;
        }

        if(count($result) > 0) {
            $item->PictureDetails = new PictureDetailsType();
            $item->PictureDetails->GalleryType = GalleryTypeCodeType::C_GALLERY;
            $item->PictureDetails->PictureURL = $result;
        }
    }
}