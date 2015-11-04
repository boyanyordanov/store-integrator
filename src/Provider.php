<?php

namespace StoreIntegrator;

use StoreIntegrator\Contracts\CategoriesAggregatorInterface;
use StoreIntegrator\Contracts\OrderIntegratorInterface;
use StoreIntegrator\Contracts\ProductIntegratorInterface;

/**
 * Class Provider
 * @package StoreIntegrator
 */
abstract class Provider
{
    /**
     * @var ProductIntegratorInterface
     */
    public $products;

    /**
     * @var OrderIntegratorInterface
     */
    public $orders;

    /**
     * @var CategoriesAggregatorInterface
     */
    public $categories;


    /**
     * @param ProductIntegratorInterface $products
     * @param OrderIntegratorInterface $orders
     * @param CategoriesAggregatorInterface $categories
     */
    public function __construct(ProductIntegratorInterface $products, OrderIntegratorInterface $orders, CategoriesAggregatorInterface $categories)
    {
        $this->products = $products;
        $this->orders = $orders;
        $this->categories = $categories;
    }
}