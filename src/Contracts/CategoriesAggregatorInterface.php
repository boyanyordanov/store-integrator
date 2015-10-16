<?php

namespace StoreIntegrator\Contracts;

/**
 * Interface CategoriesAggregatorInterface
 * @package StoreIntegrator\Contracts
 */
interface CategoriesAggregatorInterface
{
    /**
     * Returns an array of categories to map to the product
     * Each category is an array with id and name
     *
     * @return array
     */
    public function getCategories();
}