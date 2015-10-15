<?php

namespace StoreIntegrator\Contracts;

/**
 * Interface OrderIntegrator
 */
interface OrderIntegratorInterface {
    /**
     * @return mixed
     */
    public function getOrders();agag

    /**
     * @param $id
     * @return mixed
     */
    public function getOrder($id);

    /**
     * @return mixed
     */
    public function fulfilOrder();
}