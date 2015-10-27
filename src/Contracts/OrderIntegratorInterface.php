<?php

namespace StoreIntegrator\Contracts;

use DateTime;

/**
 * Interface OrderIntegrator
 */
interface OrderIntegratorInterface {
    /**
     * @param DateTime $startDate
     * @return array
     */
    public function getOrders(DateTime $startDate);

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