<?php

namespace StoreIntegrator\Contracts;

/**
 * Interface ProductIntegrator
 */
interface ProductIntegratorInterface {
    /**
     *
     * @return mixed
     */
    public function postProduct(array $product);

    /**
     * @return mixed
     */
    public function postProducts(array $products);

    /**
     * @return array
     */
    public function getProducts();
}