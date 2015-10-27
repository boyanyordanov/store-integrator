<?php

namespace StoreIntegrator\Contracts;

use DateTime;
use StoreIntegrator\Product;

/**
 * Interface ProductIntegrator
 */
interface ProductIntegratorInterface {
    /**
     *
     * @param Product $product
     * @return mixed
     */
    public function postProduct(Product $product);

    /**
     * @param array $products
     * @return mixed
     */
    public function postProducts(array $products);

    /**
     * @param DateTime $startDate
     * @return array
     */
    public function getProducts(DateTime $startDate);
}