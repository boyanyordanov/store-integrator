<?php

namespace StoreIntegrator\Contracts;

/**
 * Interface OrderIntegrator
 */
interface OrderIntegratorInterface {
    /**
     * @return mixed
     */
    public function getOrders();

    /**
     * @param $id
     * @return mixed
     */
    public function getOrder($id);

    /**
     * @param $orderId
     * @param $fulfillmentData
     * @return mixed
     */
    public function fulfilOrder($orderId, $fulfillmentData);
}