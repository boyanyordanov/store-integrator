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
     * @param null $parentId
     * @return mixed
     * @throws \StoreIntegrator\Exceptions\MissingTokenException
     */
    public function get($parentId = null)
    {
        $categoriesRequest = new GetCategoriesRequestType([
            // TODO: get the version from environment
//            'Version' => '943'
        ]);

        $categoriesRequest->DetailLevel = ['ReturnAll'];

        if(is_null($parentId)) {
            $categoriesRequest->LevelLimit = 1;
        } else {
            $categoriesRequest->CategoryParent = [$parentId];
//            $categoriesRequest->ViewAllNodes = false;
        }


        $this->addAuthToRequest($categoriesRequest);

        $response = $this->service->getCategories($categoriesRequest);

        $this->version = $response->CategoryVersion;

        $categories = $response->toArray()['CategoryArray']['Category'];

        return $categories;
    }

    /**
     * @return array
     * @throws \StoreIntegrator\Exceptions\MissingTokenException
     */
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