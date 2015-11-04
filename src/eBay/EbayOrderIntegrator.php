<?php

namespace StoreIntegrator\eBay;

use DateTime;
use StoreIntegrator\Contracts\OrderIntegratorInterface;
use StoreIntegrator\eBay\Wrappers\OrdersWrapper;

/**
 * Class EbayOrderIntegrator
 * @package StoreIntegrator\eBay
 */
class EbayOrderIntegrator implements OrderIntegratorInterface
{

    /**
     * @var OrdersWrapper
     */
    private $ordersWrapper;

    /**
     * @param OrdersWrapper $ordersWrapper
     */
    public function __construct(OrdersWrapper $ordersWrapper)
    {
        $this->ordersWrapper = $ordersWrapper;
    }

    /**
     * @param DateTime $startDate
     * @param int $page
     * @param int $perPage
     * @return mixed
     */
    public function getOrders(DateTime $startDate, $page = 1, $perPage = 10)
    {
        return $this->ordersWrapper->getAll($startDate, $page, $perPage);
    }

    /**
     * @param $orderId
     * @return mixed
     */
    public function getOrder($orderId)
    {
        return $this->ordersWrapper->get($orderId);
    }

    /**
     * @param $orderId
     * @param $fulfillmentData
     * @return mixed
     */
    public function fulfilOrder($orderId, $fulfillmentData)
    {
        return $this->ordersWrapper->fulfill($orderId, $fulfillmentData);
    }
}