<?php

namespace StoreIntegrator\eBay;

use DTS\eBaySDK\Trading\Enums\MeasurementSystemCodeType;
use DTS\eBaySDK\Trading\Types\MeasureType;
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
use DTS\eBaySDK\Trading\Types\ShipPackageDetailsType;
use DTS\eBaySDK\Trading\Types\ShippingDetailsType;
use DTS\eBaySDK\Trading\Types\ShippingServiceOptionsType;
use DTS\eBaySDK\Trading\Types\VariationsType;
use DTS\eBaySDK\Trading\Types\VariationType;
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

        // Renew the item every 30 days until the user cancels it
        $item->ListingDuration = ListingDurationCodeType::C_GTC;

        $item->Title = $product->getTitle();
        $item->Description = $product->getDescription();

        if($product->hasVariations()) {
            $this->addVariationsData($item, $product);
        } else {
            $item->SKU = $product->getSku();
            $item->StartPrice = new AmountType(['value' => $product->getPrice()]);
            $item->Quantity = $product->getQuantity();
        }

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

        // Add brand information as item specific information
        $item->ItemSpecifics = new NameValueListArrayType();

        $brand = new NameValueListType();
        $brand->Name = 'Brand';
        $brand->Value[] = $product->getBrand();

        $item->ItemSpecifics->NameValueList[] = $brand;

        // Add details for the shipping
        // NOTE: doesn't seem to work
        $item->ShippingPackageDetails = new ShipPackageDetailsType();
        $item->ShippingPackageDetails->MeasurementUnit = MeasurementSystemCodeType::C_METRIC;

        $weightKg = intval(floor($product->getWeight() / 1000));
        $weightGr = $product->getWeight() % 1000;

        $item->ShippingPackageDetails->WeightMajor = new MeasureType(['unit' => 'kg', 'value' => $weightKg]);
        $item->ShippingPackageDetails->WeightMinor = new MeasureType(['unit' => 'gr', 'value' => $weightGr]);

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
         * Not accepted
         */
        $default = [
            'ReturnsAccepted' => false,
        ];

        $policy = array_merge($default, $overrides);

        $item->ReturnPolicy = new ReturnPolicyType();

        $item->ReturnPolicy->ReturnsAcceptedOption = $policy['ReturnsAccepted'] ? 'ReturnsAccepted' : 'ReturnsNotAccepted';

        if($policy['ReturnsAccepted']) {
            $item->ReturnPolicy->RefundOption = $policy['Refund'];
            $item->ReturnPolicy->ReturnsWithinOption = $policy['ReturnsWithin'];
            $item->ReturnPolicy->ShippingCostPaidByOption = $policy['ShippingCostPaidBy'];
        }
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

    /**
     * @param $item
     * @param $product
     */
    protected function addVariationsData($item, $product)
    {
        /**
         * Before we specify the variations we need to inform eBay all the possible
         * names and values that the listing could use over its life time.
         */
        $item->Variations = new VariationsType();

        $variationSet = new NameValueListArrayType();

        foreach($product->getVariationTypes() as $type) {
            $nameValue = new NameValueListType();
            $nameValue->Name = $type['name'];
            $nameValue->Value = $type['values'];
            $variationSet->NameValueList[] = $nameValue;

        }

        $item->Variations->VariationSpecificsSet = $variationSet;

        /**
         * Add each specific combination
         */
        foreach($product->getVariationOptions() as $option) {
            $variation = new VariationType();
            $variation->SKU = $option['sku'];
            $variation->Quantity = $option['quantity'];

            if(array_key_exists('price', $option)) {
                $variation->StartPrice = new AmountType(array('value' => doubleval($option['price'])));
            }

            $variationSpecifics = new NameValueListArrayType();

            foreach($option['properties'] as $property) {
                $nameValue = new NameValueListType();
                $nameValue->Name = $property['name'];
                $nameValue->Value = [$property['value']];
                $variationSpecifics->NameValueList[] = $nameValue;
            }

            $variation->VariationSpecifics[] = $variationSpecifics;
            $item->Variations->Variation[] = $variation;
        }
    }
}