<?php

namespace StoreIntegrator\eBay\Wrappers;


use DTS\eBaySDK\Trading\Types\GetCategoriesRequestType;

/**
 * Class CategoriesWrapper
 * @package StoreIntegrator\eBay
 */
class CategoriesWrapper extends EbayWrapper
{
    /**
     * @var
     */
    protected $version;

    /**
     * @return mixed
     */
    public function get()
    {
        $categoriesRequest = new GetCategoriesRequestType([
            // TODO: get the version from environment
            'Version' => '943'
        ]);

        $categoriesRequest->DetailLevel = ['ReturnAll'];

        $this->addAuthToRequest($categoriesRequest);

        $response = $this->service->getCategories($categoriesRequest);

        $this->version = $response->CategoryVersion;

        $categories = $response->toArray()['CategoryArray']['Category'];

        return $categories;
    }

    public function update()
    {
        $categoriesRequest = new GetCategoriesRequestType([
            // TODO: get the version from environment
            'Version' => '943'
        ]);

        $this->addAuthToRequest($categoriesRequest);

        $response = $this->service->getCategories($categoriesRequest);

        $this->version = $response->CategoryVersion;

        return $response->toArray();
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }
}