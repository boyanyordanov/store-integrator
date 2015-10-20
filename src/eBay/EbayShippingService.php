<?php

namespace StoreIntegrator\eBay;

use StoreIntegrator\ShippingService;

/**
 * Class EbayShippingService
 * @package StoreIntegrator\eBay
 */
class EbayShippingService extends ShippingService
{
    /**
     * A data object returned from the ebay sdk.
     * Corresponds directly to the xml response.
     *
     * @param $data
     */
    public function __construct($data)
    {
        // TODO: Map eBay specific data
        parent::__construct($data->ShippingServiceID, $data->ShippingService, $data->Description);
    }
}