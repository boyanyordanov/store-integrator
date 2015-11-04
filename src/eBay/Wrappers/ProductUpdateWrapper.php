<?php

namespace StoreIntegrator\eBay\Wrappers;

use DTS\eBaySDK\Trading\Enums\EndReasonCodeType;
use DTS\eBaySDK\Trading\Types\EndFixedPriceItemRequestType;

class ProductUpdateWrapper extends EbayWrapper
{
    public function deleteProduct($sku)
    {
        $request = new EndFixedPriceItemRequestType();
        $request->SKU = $sku;
        $request->EndingReason = EndReasonCodeType::C_NOT_AVAILABLE;

        $this->addAuthToRequest($request);

        $response = $this->service->endFixedPriceItem($request);

        if($response->Ack == 'Failure') {
            $this->handleError($response);
        }

        return $response;
    }
}