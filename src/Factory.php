<?php

namespace StoreIntegrator;

use StoreIntegrator\eBay\EbayProvider;
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
     * @param $name
     * @return EbayProvider
     * @throws ProviderNotFoundException
     */
    public function provider($name, $config = null)
    {
        if(isset($this->providers[$name])) {
            return $this->providers[$name];
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
}