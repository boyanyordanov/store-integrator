<?php
/**
 * Created by PhpStorm.
 * User: boyan
 * Date: 21.10.15
 * Time: 15:55
 */

namespace StoreIntegrator\eBay;


use StoreIntegrator\Contracts\OrderIntegratorInterface;

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
     * @param int $page
     * @param int $perPage
     * @return mixed
     */
    public function getOrders($page = 1, $perPage = 10)
    {
        return $this->ordersWrapper->getAll($page, $perPage);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getOrder($id)
    {
        // TODO: Implement getOrder() method.
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