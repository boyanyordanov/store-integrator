<?php

namespace StoreIntegrator\Contracts;

/**
 * Interface ProductIntegrator
 */
interface ProductIntegratorInterface {
    /**
     *
     * @param array $product
     * @return mixed
     */
    public function postProduct(array $product);

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