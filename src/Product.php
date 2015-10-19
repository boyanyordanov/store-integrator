<?php

namespace StoreIntegrator;


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
    public function getQuantity()
    {
        return $this->quantity;
    }
    /**
     * @var
     */
    protected $quantity;

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
        $this->quantity = $data['quantity'];
    }
}