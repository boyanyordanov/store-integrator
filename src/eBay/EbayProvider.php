<?php

namespace StoreIntegrator\eBay;

use DTS\eBaySDK\Trading\Services\TradingService;
use StoreIntegrator\Contracts\IntegratorFactoryInterface;
use StoreIntegrator\eBay\Wrappers\AuthWrapper;
use StoreIntegrator\eBay\Wrappers\CategoriesWrapper;
use StoreIntegrator\eBay\Wrappers\DetailsWrapper;
use StoreIntegrator\eBay\Wrappers\EbayWrapper;
use StoreIntegrator\eBay\Wrappers\OrdersWrapper;
use StoreIntegrator\eBay\Wrappers\ProductUpdateWrapper;
use StoreIntegrator\eBay\Wrappers\ProductWrapper;
use StoreIntegrator\Provider;
use StoreIntegrator\Store;

/**
 * Class EbayProvider
 * @package StoreIntegrator\eBay
 */
class EbayProvider extends Provider
{

    /**
     * @var TradingService
     */
    protected $service;

    /**
     * @var
     */
    protected $store;

    /**
     * @var IntegratorFactoryInterface
     */
    public $factory;

    /**
     * @param array $ebayConfig
     */
    public function __construct($ebayConfig)
    {
        if (isset($ebayConfig['serviceConfigs'])) {
            $this->service = new TradingService($ebayConfig['serviceConfigs']);
        }

        $this->store = new Store($ebayConfig['store']['email'], $ebayConfig['store']['data']);

        $productWrapper = $this->buildWrapper(ProductWrapper::class, $ebayConfig);
        $orderWrapper = $this->buildWrapper(OrdersWrapper::class, $ebayConfig);
        $categoriesWrapper = $this->buildWrapper(CategoriesWrapper::class, $ebayConfig);
        $detailsWrapper = $this->buildWrapper(DetailsWrapper::class, $ebayConfig);
        $productUpdateWrapper = $this->buildWrapper(ProductUpdateWrapper::class, $ebayConfig);
        $authWrapper = $this->buildWrapper(AuthWrapper::class, $ebayConfig);

        $this->products = new EbayProductIntegrator($productWrapper, $productUpdateWrapper, $categoriesWrapper, $detailsWrapper);
        $this->categories = $this->products;
        $this->orders = new EbayOrderIntegrator($orderWrapper);

        $this->factory = new EbayFactory();

        $this->auth = $authWrapper;
    }

    /**
     * @param string $wrapper
     * @param array $ebayConfig
     * @return EbayWrapper
     */
    private function buildWrapper($wrapper, $ebayConfig)
    {
        $token = isset($ebayConfig['userToken']) ? $ebayConfig['userToken'] : null;

        if(isset($this->service)) {
            return new $wrapper($token, $this->store, $this->service);
        }

        return new $wrapper($token, $this->store);
    }
}