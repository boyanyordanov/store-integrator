<?php

namespace StoreIntegrator;


/**
 * Class Product
 * @package StoreIntegrator
 */
/**
 * Class Product
 * @package StoreIntegrator
 */
/**
 * Class Product
 * @package StoreIntegrator
 */
/**
 * Class Product
 * @package StoreIntegrator
 */
use StoreIntegrator\Exceptions\ValidationException;

/**
 * Class Product
 * @package StoreIntegrator
 */
class Product
{
    /**
     * @var string
     */
    protected $sku;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $brand;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var number
     */
    protected $price;
    /**
     * @var string
     */
    protected $currency;
    /**
     * @var string
     */
    protected $category;

    /**
     * @var number
     */
    protected $weight;

    /**
     * @var array
     */
    protected $returnPolicy = [];

    /**
     * @var
     */
    protected $quantity;

    /**
     * @var array
     */
    protected $shippingOptions = [];

    /**
     * @var array
     */
    protected $pictures = [];

    /**
     * @var array
     */
    protected $variationTypes = [];

    /**
     * @var array
     */
    protected $variationOptions = [];

    /**
     * @param $data
     */
    public function __construct($data)
    {
        $this->sku = $data['sku'];
        $this->title = $data['name'];
        $this->description = $data['description'];
        $this->brand = $data['brand'];
        $this->price = doubleval($data['price']);
        $this->currency = $data['currency'];
        $this->category = $data['category'];
        $this->weight = $data['weight'];
        $this->quantity = intval($data['quantity']);

        // TODO: Add better validation

        if(array_key_exists('ReturnPolicy', $data)) {
            $this->returnPolicy = $data['ReturnPolicy'];
        }

        if(array_key_exists('shippingOptions', $data)) {
            $this->shippingOptions = $data['shippingOptions'];
        }

        if(array_key_exists('pictures', $data)) {
            $this->pictures = $data['pictures'];
        }

        if(array_key_exists('variations', $data)) {
            $this->mapVariations($data['variations']);
        }
    }


    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return number
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return number
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return mixed
     */
    public function getReturnPolicy()
    {
        return $this->returnPolicy;
    }

    /**
     * @return mixed
     */
    public function getShippingOptions()
    {
        return $this->shippingOptions;
    }

    /**
     * @return array
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * @param $variations
     * @throws ValidationException
     */
    private function mapVariations($variations)
    {
        foreach($variations['types'] as $type) {
            if(!array_key_exists('name', $type) || !array_key_exists('values', $type)) {
                throw new ValidationException('A name and values for the variation type must be provided.');
            }

            $this->variationTypes[] = $type;
        }

        if(array_key_exists('options', $variations)) {
            throw new ValidationException('At least on variation option should be provided');
        }

        // TODO: consider to validate the data for each validation

        $this->variationOptions = $variations['options'];
    }

    /**
     * @return array
     */
    public function getVariationTypes()
    {
        return $this->variationTypes;
    }

    /**
     * @return array
     */
    public function getVariationOptions()
    {
        return $this->variationOptions;
    }

    /**
     * @return bool
     */
    public function hasVariations()
    {
        return count($this->variationTypes) > 0;
    }
}