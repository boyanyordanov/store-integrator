<?php

namespace StoreIntegrator\eBay;

use DTS\eBaySDK\Trading\Types\EndFixedPriceItemRequestType;

class ProductUpdateWrapper extends EbayWrapper
{
    public function deleteProduct($sku)
    {
        $request = new EndFixedPriceItemRequestType();
        $request->SKU = $sku;

        $this->addAuthToRequest($request);

        $response = $this->service->endFixedPriceItem($request);

        if($response->Ack == 'Failure') {
            $this->handleError($response);
        }

        return $response;
    }
}