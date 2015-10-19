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
     * @var string
     */
    protected $country = 'US';

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

        if(array_key_exists('ReturnPolicy', $data)) {
            $this->returnPolicy = $data['ReturnPolicy'];
        }
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
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
}