<?php

namespace StoreIntegrator\eBay\Wrappers;

use DTS\eBaySDK\Trading\Enums\DetailNameCodeType;
use DTS\eBaySDK\Trading\Types\GeteBayDetailsRequestType;
use DTS\eBaySDK\Trading\Types\GetUserRequestType;
use StoreIntegrator\eBay\EbayShippingService;

/**
 * Class DetailsWrapper
 * @package StoreIntegrator\eBay
 */
class DetailsWrapper extends EbayWrapper
{
    /**
     * @return array
     */
    public function getShippingMethods()
    {
        $response = $this->getEbayDetail(DetailNameCodeType::C_SHIPPING_SERVICE_DETAILS);

        $result = [];

        foreach ($response->ShippingServiceDetails as $item) {
            if($item->ValidForSellingFlow && strtolower($item->ServiceType[0]) == 'flat') {
                $result[] = new EbayShippingService($item);
            }
        }

        return $result;
    }

    /**
     * @return \DTS\eBaySDK\Trading\Types\GeteBayDetailsResponseType
     */
    public function getShippingLocations()
    {
        return $this->getEbayDetail(DetailNameCodeType::C_SHIPPING_LOCATION_DETAILS);
    }

    /**
     * @return \DTS\eBaySDK\Trading\Types\GeteBayDetailsResponseType
     */
    public function getShippingExcludeLocations()
    {
        return $this->getEbayDetail(DetailNameCodeType::C_EXCLUDE_SHIPPING_LOCATION_DETAILS);
    }

    /**
     * @param $detail
     * @return \DTS\eBaySDK\Trading\Types\GeteBayDetailsResponseType
     * @throws \StoreIntegrator\Exceptions\MissingTokenException
     */
    private function getEbayDetail($detail)
    {
        $request = new GeteBayDetailsRequestType();
        $request->DetailName = [$detail];

        $this->addAuthToRequest($request);

        $response = $this->service->geteBayDetails($request);
        return $response;
    }
}