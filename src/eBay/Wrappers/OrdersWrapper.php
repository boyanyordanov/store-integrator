<?php

namespace StoreIntegrator\eBay\Wrappers;

use DateTime;
use DTS\eBaySDK\Trading\Enums\CommentTypeCodeType;
use DTS\eBaySDK\Trading\Enums\OrderStatusCodeType;
use DTS\eBaySDK\Trading\Enums\TradingRoleCodeType;
use DTS\eBaySDK\Trading\Types\CompleteSaleRequestType;
use DTS\eBaySDK\Trading\Types\FeedbackInfoType;
use DTS\eBaySDK\Trading\Types\GetOrdersRequestType;
use DTS\eBaySDK\Trading\Types\OrderIDArrayType;
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
     * @param DateTime $startDate
     * @param int $page
     * @param int $perPage
     * @return \DTS\eBaySDK\Trading\Types\GetOrdersResponseType
     */
    public function getAll(DateTime $startDate, $page = 1, $perPage = 100)
    {
        $request = new GetOrdersRequestType();

        $request->DetailLevel = ['ReturnAll'];

        $request->OrderRole = TradingRoleCodeType::C_SELLER;
        $request->OrderStatus = OrderStatusCodeType::C_COMPLETED;

        $request->CreateTimeFrom = $startDate;
        $request->CreateTimeTo = new DateTime();

        $request->Pagination = new PaginationType();
        $request->Pagination->EntriesPerPage = $perPage;
        $request->Pagination->PageNumber = $page;

        $this->addAuthToRequest($request);

        $response = $this->service->getOrders($request);

        if($response->Ack == 'Failure') {
            $this->handleError($response);
        }

        // TODO: Don't return raw response
        return $response;
    }

    /**
     * @param $orderId
     * @return \DTS\eBaySDK\Trading\Types\GetOrdersResponseType
     */
    public function get($orderId)
    {
        $request = new GetOrdersRequestType();
        $request->OrderIDArray = new OrderIDArrayType();
        $request->OrderIDArray->OrderID = [$orderId];
        $request->DetailLevel = ['ReturnAll'];

        $this->addAuthToRequest($request);

        $response = $this->service->getOrders($request);

        if($response->Ack == 'Failure') {
            $this->handleError($response);
        }

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
         *  'trackingCarrier => 'USPS',
         *  'leaveFeedback' => true,
         *  'feedbackText' => 'Great buyer',
         *  'userID' => 'testuser_john'
         * ]
         */

        $request = new CompleteSaleRequestType();

        $this->addAuthToRequest($request);

        $data = $this->formatData($fulfillmentData);

        $request->OrderID = $orderID;
        $request->Paid = $data['paid'];
        $request->Shipped = $data['shipped'];

        if($data['tracking']) {
            $request->Shipment = new ShipmentType();
            $request->Shipment->ShipmentTrackingDetails = [new ShipmentTrackingDetailsType()];
            $request->Shipment->ShipmentTrackingDetails[0]->ShippingCarrierUsed = $fulfillmentData['trackingCarrier'];
            $request->Shipment->ShipmentTrackingDetails[0]->ShipmentTrackingNumber = $fulfillmentData['trackingNumber'];
        }

        if($data['leaveFeedback']) {
            $request->FeedbackInfo = new FeedbackInfoType();
            $request->FeedbackInfo->CommentType = CommentTypeCodeType::C_POSITIVE;
            $request->FeedbackInfo->CommentText = array_key_exists('feedbackText', $fulfillmentData) ? $fulfillmentData['feedbackText'] : 'Great buyer!';
            $request->FeedbackInfo->TargetUser = $fulfillmentData['userID'];
        }


        $response = $this->service->completeSale($request);

        if ($response->Ack === 'Failure') {
            $this->handleError($response);
        }

        return $response;
    }

    /**
     * @param $fulfillmentData
     * @return array
     */
    private function formatData($fulfillmentData)
    {
        $data = [];
        $data['paid'] = $this->determineValue('paid', $fulfillmentData, true);
        $data['shipped'] = $this->determineValue('shipped', $fulfillmentData, false);
        $data['tracking'] = $this->determineValue('tracking', $fulfillmentData, false);
        $data['leaveFeedback'] = $this->determineValue('leaveFeedback', $fulfillmentData, false);

        return $data;
    }
}