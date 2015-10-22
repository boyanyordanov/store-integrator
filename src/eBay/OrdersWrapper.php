<?php

namespace StoreIntegrator\eBay;

use DTS\eBaySDK\Trading\Enums\OrderStatusCodeType;
use DTS\eBaySDK\Trading\Enums\TradingRoleCodeType;
use DTS\eBaySDK\Trading\Types\CompleteSaleRequestType;
use DTS\eBaySDK\Trading\Types\GetOrdersRequestType;
use DTS\eBaySDK\Trading\Types\PaginationType;
use DTS\eBaySDK\Trading\Types\ShipmentTrackingDetailsType;
use DTS\eBaySDK\Trading\Types\ShipmentType;

/**
 * Class OrdersWrapper
 * @package StoreIntegrator\eBay
 */
class OrdersWrapper extends EbayWrapper
{
    /**
     * @param int $page
     * @param int $perPage
     * @return \DTS\eBaySDK\Trading\Types\GetOrdersResponseType
     */
    public function getAll($page = 1, $perPage = 10)
    {
        $request = new GetOrdersRequestType();

        $request->OrderRole = TradingRoleCodeType::C_SELLER;
        $request->OrderStatus = OrderStatusCodeType::C_COMPLETED;

        // TODO: Don't hard-code those
        $request->CreateTimeFrom = date_create('2015-10-01');
        $request->CreateTimeTo = date_create();

        $request->Pagination = new PaginationType();
        $request->Pagination->EntriesPerPage = $perPage;
        $request->Pagination->PageNumber = $page;

        $this->addAuthToRequest($request);

        $response = $this->service->getOrders($request);

        // TODO: Don't return raw response
        return $response;
    }

    /**
     * @param $orderID
     * @param $fulfillmentData
     * @return bool|\DTS\eBaySDK\Trading\Types\CompleteSaleResponseType
     */
    public function fulfill($orderID, $fulfillmentData)
    {
        /*
         * $fulfillmentData = [
         *  'paid' => true,
         *  'shipped' => true,
         *  'tracking' => true,
         *  'trackingNumber => 'tracking-number-123',
         * ]
         */

        $request = new CompleteSaleRequestType();

        $request->OrderID = $orderID;
        $request->Paid = array_key_exists('paid', $fulfillmentData) ? $fulfillmentData['paid'] : true;
        $request->Shipped = array_key_exists('shipped', $fulfillmentData) ? $fulfillmentData['shipped'] : false;
        
        if(array_key_exists('tracking', $fulfillmentData) && $fulfillmentData['tracking']) {
            $request->Shipment = new ShipmentType();
            $request->Shipment->ShipmentTrackingDetails = [new ShipmentTrackingDetailsType()];
            $request->Shipment->ShipmentTrackingDetails[0]->ShipmentTrackingNumber = $fulfillmentData['trackingNumber'];
        }

       $response = $this->service->completeSale($request);

        if ($response->Ack === 'Failure') {
            return false;
        }

        return $response;
    }
}