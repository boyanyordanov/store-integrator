<?php

namespace StoreIntegrator\Contracts;

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
     * @return array
     */
    public function getProducts();
}