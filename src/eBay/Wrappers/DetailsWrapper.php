<?php

namespace StoreIntegrator\eBay\Wrappers;

use DTS\eBaySDK\Trading\Enums\DetailNameCodeType;
use DTS\eBaySDK\Trading\Types\GeteBayDetailsRequestType;
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
        $request = new GeteBayDetailsRequestType();
        $request->DetailName = [DetailNameCodeType::C_SHIPPING_SERVICE_DETAILS];

        $this->addAuthToRequest($request);

        $response = $this->service->geteBayDetails($request);

        $result = [];

        foreach ($response->ShippingServiceDetails as $item) {
            $result[] = new EbayShippingService($item);
        }

        return $result;
    }
}