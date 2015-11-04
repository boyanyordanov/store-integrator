<?php

namespace StoreIntegrator\eBay\Wrappers;

use DTS\eBaySDK\Trading\Types\FetchTokenRequestType;
use DTS\eBaySDK\Trading\Types\GetSessionIDRequestType;

/**
 * Class AuthWrapper
 * @package StoreIntegrator\eBay\Wrappers
 */
class AuthWrapper extends EbayWrapper
{
    /**
     * @return string
     * @throws \StoreIntegrator\Exceptions\EbayErrorException
     */
    public function getSessionID()
    {
        $request = new GetSessionIDRequestType();

        $request->RuName = $this->ruName;

        $response = $this->service->getSessionID($request);

        if($response->Ack == 'Failure') {
            $this->handleError($response);
        }

        return $response->SessionID;
    }

    /**
     * @param string $sessionId
     * @return string
     */
    public function getUserToken($sessionId)
    {
        $request = new FetchTokenRequestType();

        $request->SessionID = $sessionId;

        $response = $this->service->fetchToken($request);

        if($response->Ack == 'Failure') {
            $this->handleError($response);
        }

        return $response->eBayAuthToken;
    }

    public function buildRedirectUrl($sessionId)
    {
        $inSandbox = $this->getConfig()['sandbox'];

        if($inSandbox) {
            return "https://signin.sandbox.ebay.com/ws/eBayISAPI.dll?SignIn&RuName={$this->ruName}&SessID={$sessionId}";
        }

        return "https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&RuName={$this->ruName}&SessID={$sessionId}";
    }
}