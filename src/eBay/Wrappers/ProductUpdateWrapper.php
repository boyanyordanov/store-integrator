<?php

namespace StoreIntegrator\eBay\Wrappers;

use DTS\eBaySDK\Trading\Enums\EndReasonCodeType;
use DTS\eBaySDK\Trading\Enums\MeasurementSystemCodeType;
use DTS\eBaySDK\Trading\Types\AmountType;
use DTS\eBaySDK\Trading\Types\CategoryType;
use DTS\eBaySDK\Trading\Types\EndFixedPriceItemRequestType;
use DTS\eBaySDK\Trading\Types\GetItemRequestType;
use DTS\eBaySDK\Trading\Types\InventoryStatusType;
use DTS\eBaySDK\Trading\Types\ItemType;
use DTS\eBaySDK\Trading\Types\MeasureType;
use DTS\eBaySDK\Trading\Types\NameValueListArrayType;
use DTS\eBaySDK\Trading\Types\NameValueListType;
use DTS\eBaySDK\Trading\Types\ReviseFixedPriceItemRequestType;
use DTS\eBaySDK\Trading\Types\ReviseInventoryStatusRequestType;
use DTS\eBaySDK\Trading\Types\ShipPackageDetailsType;
use DTS\eBaySDK\Trading\Types\VariationsType;
use DTS\eBaySDK\Trading\Types\VariationType;
use StoreIntegrator\Exceptions\EbayErrorException;
use StoreIntegrator\Product;

/**
 * Class ProductUpdateWrapper
 * @package StoreIntegrator\eBay\Wrappers
 */
class ProductUpdateWrapper extends EbayWrapper
{
    /**
     * @param $sku
     * @return \DTS\eBaySDK\Trading\Types\EndFixedPriceItemResponseType
     * @throws \StoreIntegrator\Exceptions\EbayErrorException
     * @throws \StoreIntegrator\Exceptions\MissingTokenException
     */
    public function deleteProduct($sku)
    {
        $request = new EndFixedPriceItemRequestType();
        $request->SKU = $sku;
        $request->EndingReason = EndReasonCodeType::C_NOT_AVAILABLE;

        $this->addAuthToRequest($request);

        $response = $this->service->endFixedPriceItem($request);

        if($response->Ack == 'Failure') {
            $this->handleError($response);
        }

        return $response;
    }

    /**
     * @param $sku
     * @param $data
     * @return \DTS\eBaySDK\Trading\Types\ReviseFixedPriceItemResponseType
     * @throws \StoreIntegrator\Exceptions\EbayErrorException
     * @throws \StoreIntegrator\Exceptions\MissingTokenException
     */
    public function updateProduct($sku, Product $product)
    {
        $eBayProductResp = $this->getProduct($sku);
        if(!$eBayProductResp->Item) {
            throw new EbayErrorException('No product with sku "' . $sku . '" was found');
        }

        $eBayProduct = $eBayProductResp->Item;

        if(!$this->hasChanged($eBayProduct, $product)) {
            return $this->checkAndRevisePriceAndQuantity($eBayProduct, $product);
        }

        $request = new ReviseFixedPriceItemRequestType();

        $item = new ItemType();

        $item->SKU = $sku;

        if($eBayProduct->Title != $product->getTitle()) {
            $item->Title = $product->getTitle();
        }

        if($eBayProduct->Description != $product->getDescription()) {
            $item->Description = $product->getDescription();
        }

        $item->Country = $this->store->getStoreData('country');
        $item->Location = $this->store->getStoreData('location');

        if($this->store->hasStoreData('postCode')) {
            $item->PostalCode = $this->store->getStoreData('postCode');
        }

        if($eBayProduct->Currency != $product->getCurrency()) {
            $item->Currency = $product->getCurrency();
        }

        if($product->hasVariations()) {
            if(!$eBayProduct->Variations || !$eBayProduct->Variations->Variation || $this->hasVariationSales($eBayProduct)) {
                $this->changeProductType($product);
            } else {
                $this->updateVariationsData($eBayProduct, $item, $product);
            }
        } else {
            if($eBayProduct->Variations && $eBayProduct->Variations->Variation) {
                $this->changeProductType($product);
            }

            if($eBayProduct->StartPrice->value != $product->getPrice()) {
                $item->StartPrice = new AmountType(['value' => $product->getPrice()]);
            }
            if($eBayProduct->Quantity != $product->getQuantity()) {
                $item->Quantity = $product->getQuantity();
            }
        }

        if($eBayProduct->PrimaryCategory->CategoryID != $product->getCategory()) {
            $item->PrimaryCategory = new CategoryType();
            $item->PrimaryCategory->CategoryID = $product->getCategory();
            $item->CategoryMappingAllowed = true;
        }

        if($product->getBrand()) {
            foreach($eBayProduct->ItemSpecifics->NameValueList as $specific) {
                if($specific->Name == 'Brand' && $specific->Value[0] != $product->getBrand()) {
                    // Add brand information as item specific information
                    $item->ItemSpecifics = new NameValueListArrayType();

                    $brand = new NameValueListType();
                    $brand->Name = 'Brand';
                    $brand->Value[] = $product->getBrand();

                    $item->ItemSpecifics->NameValueList[] = $brand;
                }
            }
        }

        if($product->getWeight()) {
            $totalOz = $product->getWeight() * 0.035274;
            $weightMajor = intval(floor($totalOz / 16));
            $weightMinor = intval(floor($totalOz - ($weightMajor * 16)));

            if($eBayProduct->ShippingPackageDetails->WeightMajor->value != $weightMajor && $eBayProduct->ShippingPackageDetails->WeightMinor->value != $weightMinor) {
                $item->ShippingPackageDetails = new ShipPackageDetailsType();
                $item->ShippingPackageDetails->MeasurementUnit = MeasurementSystemCodeType::C_ENGLISH;
                $item->ShippingPackageDetails->WeightMajor = new MeasureType(['unit' => 'lbs', 'value' => $weightMajor]);
                $item->ShippingPackageDetails->WeightMinor = new MeasureType(['unit' => 'oz', 'value' => $weightMinor]);
            }
        }

        $this->addPictures($item, $product);

        $item->PaymentMethods = $this->store->getPaymentOptions();
        $item->PayPalEmailAddress = $this->store->getPaypalEmail();
        $item->DispatchTimeMax = $this->store->getStoreData('dispatchTime');

        // Add shipping options and return policy
        $this->addShippingOptions($item, $product->getShippingOptions());

        $request->Item = $item;

        $this->addAuthToRequest($request);

        $response = $this->service->reviseFixedPriceItem($request);

        if($response->Ack == 'Failure') {
            $this->handleError($response);
        }

        return $response;
    }

    /**
     * @param $sku
     * @return \DTS\eBaySDK\Trading\Types\GetItemResponseType
     * @throws \StoreIntegrator\Exceptions\EbayErrorException
     * @throws \StoreIntegrator\Exceptions\MissingTokenException
     */
    public function getProduct($sku)
    {
        $request = new GetItemRequestType();

        $request->DetailLevel = ['ReturnAll'];
        $request->IncludeItemSpecifics = true;
        $request->SKU = $sku;

        $this->addAuthToRequest($request);

        $response = $this->service->getItem($request);

        if($response->Ack == 'Failure') {
            $this->handleError($response);
        }

        return $response;
    }

    /**
     * @param $eBayProduct
     * @param $item
     * @param $product
     */
    private function updateVariationsData($eBayProduct, $item, Product $product)
    {
        if(!$eBayProduct->Variations){
            return;
        }

        if(!$eBayProduct->Variations->Variation) {
            return;
        }

        $item->Variations = new VariationsType();

        $variationSet = new NameValueListArrayType();

        foreach($product->getVariationTypes() as $type) {
            $nameValue = new NameValueListType();
            $nameValue->Name = $type['name'];
            $nameValue->Value = $type['values'];
            $variationSet->NameValueList[] = $nameValue;

        }

        $item->Variations->VariationSpecificsSet = $variationSet;

        $forUpdate =[];

        $separated = $this->separateVariations($eBayProduct, $product);

        $foundVariations = $separated['found'];
        $newVariations = $separated['new'];

        // Attach the updated variations
        foreach($foundVariations as $variation) {
            $item->Variations->Variation[] = $variation;
        }

        // Attach the new variations
        foreach($newVariations as $option) {
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

        // remove missing variations
        foreach($eBayProduct->Variations->Variation as $ebayVariation) {
            if(!in_array($ebayVariation, $foundVariations)) {
                if($ebayVariation->SellingStatus->QuantitySold > 0) {
                    $ebayVariation->Quantity = 0;
                } else {
                    $ebayVariation->Delete = true;
                }

                $item->Variations->Variation[] = $ebayVariation;
            }
        }
    }

    /**
     * @param $product
     */
    private function changeProductType($product)
    {
        $productWrapper = new ProductWrapper($this->userToken, $this->store, $this->service);

        $this->deleteProduct($product->getSku());
        $productWrapper->post($product);
    }

    /**
     * @param $eBayProduct
     * @return bool
     */
    private function hasVariationSales($eBayProduct)
    {
        if(!$eBayProduct->Variations || !$eBayProduct->Variations->Variation){
            return false;
        }

        foreach($eBayProduct->Variations->Variation as $variation) {
            if($variation->SellingStatus->QuantitySold > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $eBayProduct
     * @param Product $product
     * @return bool
     */
    private function hasChanged($eBayProduct, Product $product)
    {
        $ebayTitle = $eBayProduct->Title;
        $currentTitle = $product->getTitle();

        if($ebayTitle != $currentTitle) {
            return true;
        }

        $ebayDescription = htmlentities($eBayProduct->Description);
        $currentDescription = $product->getDescription();

        if($ebayDescription != $currentDescription) {
            return true;
        }

        $currentBrand = $product->getBrand();
        $ebayBrand = '';
        foreach($eBayProduct->ItemSpecifics->NameValueList as $specific) {
            if($specific->Name == 'Brand') {
                $ebayBrand = $specific->Value[0];
            }
        }

        if($ebayBrand != $currentBrand) {
            return true;
        }

        $currentPictures = $product->getPictures();
        $ebayPictures = $eBayProduct->PictureDetails->ExternalPictureURL;

        foreach($ebayPictures as $ebayPicture) {
            if(!in_array($ebayPicture, $currentPictures)) {
                return true;
            }
        }

        if($product->hasVariations()) {
            $separated = $this->separateVariations($eBayProduct, $product);
            $foundVariations = $separated['found'];
            $newVariations = $separated['new'];

            if(count($newVariations) > 0) {
                return true;
            }

            foreach($eBayProduct->Variations->Variation as $ebayVariation) {
                if (!in_array($ebayVariation, $foundVariations)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $eBayProduct
     * @param Product $product
     * @return \DTS\eBaySDK\Trading\Types\ReviseInventoryStatusResponseType|null
     * @throws EbayErrorException
     * @throws \StoreIntegrator\Exceptions\MissingTokenException
     */
    private function checkAndRevisePriceAndQuantity($eBayProduct, Product $product)
    {
        $inventoryChanges = [];

        if($product->hasVariations()) {
            foreach($product->getVariationOptions() as $option) {
                $inventoryChanges[] = [
                    'sku' => $option['sku'],
                    'quantity' => $option['quantity'],
                    'price' => $option['price']
                ];
            }
        } else {
            $inventoryChanges[] = [
                'sku' =>  $product->getSku(),
                'quantity' => $product->getQuantity(),
                'price' => $product->getPrice()
            ];
        }

        $chunks = array_chunk($inventoryChanges, 4);
        $response = null;;

        foreach($chunks as $chunk) {
            foreach($chunk as $inventoryData) {
                $request = new ReviseInventoryStatusRequestType();

                $inventory = new InventoryStatusType();
                $inventory->SKU = $inventoryData['sku'];
                $inventory->Quantity = $inventoryData['quantity'];
                $inventory->StartPrice = new AmountType(array('value' => doubleval($inventoryData['price'])));

                $request->InventoryStatus[] = $inventory;

                $this->addAuthToRequest($request);

                $response = $this->service->reviseInventoryStatus($request);

                if($response->Ack == 'Failure') {
                    $this->handleError($response);
                }
            }
        }

        return $response;
    }

    /**
     * @param $eBayProduct
     * @param Product $product
     * @return array
     */
    private function separateVariations($eBayProduct, Product $product)
    {
        $foundVariations = [];
        $newVariations = [];

        // Find variations that are to be updated and separate the new ones
        foreach($product->getVariationOptions() as $option) {
            $found = false;
            foreach($eBayProduct->Variations->Variation as $ebayVariation) {
                if($ebayVariation->SKU === $option['sku']) {
                    $ebayVariation->Quantity = $option['quantity'];

                    if(array_key_exists('price', $option) && $ebayVariation->StartPrice->value != $option['price']) {
                        $ebayVariation->StartPrice = new AmountType(array('value' => doubleval($option['price'])));
                    }

                    $foundVariations[] = $ebayVariation;
                    $found = true;
                    break;
                }
            }

            if(!$found) {
                $newVariations[] = $option;
            }
        }

        return [
            'new' => $newVariations,
            'found' => $foundVariations
        ];
    }

}