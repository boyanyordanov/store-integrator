<?php

namespace StoreIntegrator\eBay\Wrappers;

use DTS\eBaySDK\Trading\Enums\GalleryTypeCodeType;
use DTS\eBaySDK\Trading\Enums\ShippingTypeCodeType;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\AmountType;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
use DTS\eBaySDK\Trading\Types\InternationalShippingServiceOptionsType;
use DTS\eBaySDK\Trading\Types\PictureDetailsType;
use DTS\eBaySDK\Trading\Types\ReturnPolicyType;
use DTS\eBaySDK\Trading\Types\ShippingDetailsType;
use DTS\eBaySDK\Trading\Types\ShippingServiceOptionsType;
use StoreIntegrator\eBay\EbayShippingService;
use StoreIntegrator\Exceptions\EbayErrorException;
use StoreIntegrator\Exceptions\MissingTokenException;
use StoreIntegrator\Product;
use StoreIntegrator\Store;

/**
 * Class EbayWrapper
 * @package StoreIntegrator\eBay
 */
abstract class EbayWrapper
{
    /**
     * @var TradingService
     */
    protected $service;

    /**
     * @var Store
     */
    protected $store;

    /**
     * Special ebay identification string for the application
     *
     * @var string
     */
    protected $ruName;

    /**
     * @param $userToken
     * @param Store $store
     * @param TradingService|null $service
     */
    public function __construct($userToken, Store $store, TradingService $service = null)
    {
        if (is_null($service)) {
            $this->service = new TradingService([
                'apiVersion' => getenv('EBAY-TRADING-API-VERSION'),
                'sandbox' => true,
                'siteId' => $store->getEbaySiteID(),
                'devId' => getenv('EBAY-DEV-ID'),
                'appId' => getenv('EBAY-APP-ID'),
                'certId' => getenv('EBAY-CERT-ID'),
            ]);
        } else {
            $this->service = $service;
        }

        if(!is_null($userToken)) {
            $this->userToken = $userToken;
        }

        if(getenv('EBAY-RUNAME')) {
            $this->ruName = getenv('EBAY-RUNAME');
        }

        $this->store = $store;
    }

    /**
     * @var
     */
    protected $userToken;

    /**
     * @return string
     */
    public function getRuName()
    {
        return $this->ruName;
    }

    /**
     * @param string $ruName
     */
    public function setRuName($ruName)
    {
        $this->ruName = $ruName;
    }

    /**
     * @param $item
     * @param $product
     */
    public function addPictures($item, Product $product)
    {
        $result = [];

        foreach ($product->getPictures() as $pictureUrl) {
            $result[] = $pictureUrl;
        }

        if (count($result) > 0) {
            $item->PictureDetails = new PictureDetailsType();
            $item->PictureDetails->GalleryType = GalleryTypeCodeType::C_GALLERY;
            $item->PictureDetails->PictureURL = $result;
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
        foreach ($shippingOptions as $index => $shippingOption) {
            if ($shippingOption->getInternational()) {
                $shippingService = new InternationalShippingServiceOptionsType();
                $shippingService->ShippingServicePriority = $index;
                $shippingService->ShippingService = $shippingOption->getName();
                $shippingService->ShippingServiceCost = new AmountType(array('value' => $shippingOption->getCost()));
                if ($shippingOption->getAdditionalCost()) {
                    $shippingService->ShippingServiceAdditionalCost = new AmountType(array('value' => $shippingOption->getAdditionalCost()));
                }
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
     * @param $request
     * @return mixed $request
     */
    protected function addAuthToRequest($request)
    {
        if(!isset($this->userToken)) {
            throw new MissingTokenException('This request requires user authorization. A user token must be added first.');
        }

        $request->RequesterCredentials = new CustomSecurityHeaderType();
        $request->RequesterCredentials->eBayAuthToken = $this->userToken;

        return $request;
    }

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->service->config();
    }

    /**
     * @param $element
     * @param $array
     * @param $default
     * @return bool
     */
    protected function determineValue($element, $array, $default)
    {
        return array_key_exists($element, $array) ? $array[$element] : $default;
    }

    /**
     * @param $response
     * @throws EbayErrorException
     */
    protected function handleError($response)
    {
        foreach ($response->Errors as $error) {
            if ($error->SeverityCode == 'Error') {
                throw new EbayErrorException($error->LongMessage, $error->ErrorCode);
            }
        }
    }
}