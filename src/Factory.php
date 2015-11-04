<?php

namespace StoreIntegrator;

use StoreIntegrator\Exceptions\ProviderNotFoundException;

class Factory
{

    /**
     * @param $name
     * @return EbayProvider
     * @throws ProviderNotFoundException
     */
    public function provider($name)
    {
        switch ($name) {
            case 'ebay':
                $provider = new EbayProvider();
                break;
            case 'amazon':
            default:
                throw new ProviderNotFoundException("There is no '{$name}' store integrator provider");
        }

        return $provider;
    }
}