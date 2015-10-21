<?php

namespace StoreIntegrator\eBay;


use DTS\eBaySDK\Constants\SiteIds;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;

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
     * @param $userToken
     * @param TradingService|null $service
     */
    public function __construct($userToken, TradingService $service = null)
    {
        if (is_null($service)) {
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

        $this->userToken = $userToken;
    }

    /**
     * @var
     */
    protected $userToken;

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

    public function getConfig()
    {
        return $this->service->config();
    }
}