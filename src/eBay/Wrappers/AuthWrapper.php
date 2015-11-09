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
     * @var
     */
    protected $token;
    /**
     * @var
     */
    protected $expirationTime;

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
     * @param $sessionId
     * @return $this
     * @throws \StoreIntegrator\Exceptions\EbayErrorException
     */
    public function fetchToken($sessionId)
    {
        $request = new FetchTokenRequestType();

        $request->SessionID = $sessionId;

        $response = $this->service->fetchToken($request);

        if($response->Ack == 'Failure') {
            $this->handleError($response);
        }

        $this->token = $response->eBayAuthToken;
        $this->expirationTime = $response->HardExpirationTime;

        return $this;
    }

    /**
     * @param string $sessionId
     * @return string
     */
    public function getUserToken($sessionId = null)
    {

        if(!is_null($sessionId) || !$this->token) {
            $this->fetchToken($sessionId);
        }

        return $this->token;
    }

    /**
     * @param string $sessionId
     * @return string
     */
    public function getTokenExpiration($sessionId = null)
    {

        if(!is_null($sessionId) || !$this->token) {
            $this->fetchToken($sessionId);
        }

        return $this->expirationTime;
    }

    /**
     * @param $sessionId
     * @return string
     */
    public function buildRedirectUrl($sessionId)
    {
        $inSandbox = $this->getConfig()['sandbox'];

        if($inSandbox) {
            return "https://signin.sandbox.ebay.com/ws/eBayISAPI.dll?SignIn&RuName={$this->ruName}&SessID={$sessionId}";
        }

        return "https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&RuName={$this->ruName}&SessID={$sessionId}";
    }
}