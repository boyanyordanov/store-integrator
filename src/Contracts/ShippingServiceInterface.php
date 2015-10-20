<?php

namespace StoreIntegrator\Contracts;

/**
 * Interface ShippingServiceInterface
 * @package StoreIntegrator\Contracts
 */
interface ShippingServiceInterface
{
    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDescription();
}