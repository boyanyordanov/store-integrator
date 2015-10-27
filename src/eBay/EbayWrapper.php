<?php

namespace StoreIntegrator\eBay;


use DTS\eBaySDK\Constants\SiteIds;
use DTS\eBaySDK\Trading\Services\TradingService;
use DTS\eBaySDK\Trading\Types\CustomSecurityHeaderType;
use StoreIntegrator\Exceptions\EbayErrorException;
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

        $this->userToken = $userToken;
        $this->store = $store;
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