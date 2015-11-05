<?php

namespace StoreIntegrator;

use StoreIntegrator\eBay\EbayProvider;
use StoreIntegrator\Exceptions\NoConfigsProvidedException;
use StoreIntegrator\Exceptions\ProviderNotFoundException;

/**
 * Class Factory
 * @package StoreIntegrator
 */
class Factory
{
    /**
     * @var
     */
    protected $providers;

    /**
     * @var array
     */
    protected $configs = [];

    /**
     * @param $configs
     */
    public function __construct($configs)
    {
        $this->extractConfig('ebay', $configs);
    }

    /**
     * @param $name
     * @return EbayProvider
     * @throws ProviderNotFoundException
     */
    public function provider($name, $config = null)
    {
        if (isset($this->providers[$name])) {
            return $this->providers[$name];
        }

        if(is_null($config)) {
            $config = $this->getConfig($name);
        }

        switch ($name) {
            case 'ebay':
                $provider = new EbayProvider($config);
                break;
            case 'amazon':
            default:
                throw new ProviderNotFoundException("There is no '{$name}' store integrator provider");
        }

        $this->providers[$name] = $provider;
        return $provider;
    }

    /**
     * @param $key
     * @param $configs
     */
    private function extractConfig($key, $configs)
    {
        if (isset($configs[$key])) {
            $this->configs[$key] = $configs[$key];
        }
    }

    /**
     * @param $name
     * @return mixed
     */
    private function getConfig($name)
    {
        if (!isset($this->configs[$name])) {
            throw new NoConfigsProvidedException("No configuration found for provider {$name}");
        }

        return $this->configs[$name];
    }
}