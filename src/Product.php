<?php

namespace StoreIntegrator;


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
     * @param $data
     */
    public function __construct($data)
    {
        $this->sku = $data['sku'];
        $this->title = $data['name'];
        $this->description = $data['description'];
        $this->brand = $data['brand'];
        $this->parice = $data['price'];
        $this->currency = $data['currency'];
        $this->category = $data['category'];
        $this->weight = $data['weight'];
    }
}