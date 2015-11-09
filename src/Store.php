<?php

namespace StoreIntegrator;

use StoreIntegrator\Exceptions\ValidationException;
use StoreIntegrator\tests\eBay\SiteIdCodes;

/**
 * Class Store
 * @package StoreIntegrator
 */
class Store
{
    /**
     * String value used to represent PayPal for the eBay API
     */
    const PAYMENT_PAYPAL = 'PayPal';

    /**
     * String value used to represent Visa and MasterCard for the eBay API
     */
    const PAYMENT_VISA_MASTER_CARD = 'VisaMC';

    /**
     * String value used to represent Cash on delivery option for the eBay API*
     */
    const PAYMENT_CASH_ON_DELIVERY = 'COD';

    /**
     * And array of configured payment options.
     * Used when posting a new product to eBay.
     * PayPal is enabled by default
     *
     * @var array
     */
    protected $paymentOptions = [self::PAYMENT_PAYPAL];

    /**
     * The email to be set for PayPal payments
     *
     * @var string
     */
    protected $payPalEmail;

    /**
     * @var integer
     */
    protected $ebaySiteID;

    /**
     * @var array
     */
    protected $storeData = [
        'dispatchTime' => 1,
        'country' => 'US'
    ];

    /**
     * @param $payPalEmail
     * @param array $storeData
     * @param int $ebaySiteID
     * @throws ValidationException
     */
    public function __construct($payPalEmail, $storeData = [], $ebaySiteID = 'US')
    {
        $this->validateEbaySiteID($ebaySiteID);
        $this->validateEmail($payPalEmail);
        $this->validateRequiredDataInArray($storeData, [
            'location' => true,
            'country' => true,
            'postCode' => false,
            'dispatchTime' => false
        ]);

        $this->ebaySiteID = $ebaySiteID;
        $this->payPalEmail = $payPalEmail;
        $this->storeData = array_merge($this->storeData, $storeData);
    }

    /**
     * Adds payment methods to be used when posting products.
     * PayPal is enabled by default.
     *
     * @throws ValidationException
     * @param array $options
     */
    public function addPaymentOptions(array $options)
    {
        foreach($options as $option) {
            $this->validatePaymentOption($option);
        }

        $this->paymentOptions = $options;
    }

    /**
     * Returns the pre-set ebay site id.
     *
     * @return int
     */
    public function getEbaySiteID()
    {
        $siteString = $this->ebaySiteID;
        $reflection = new \ReflectionClass(SiteIdCodes::class);
        return $reflection->getConstant($siteString);
    }

    /**
     * Returns the configured email for PayPal
     * @return string
     */
    public function getPaypalEmail()
    {
        return $this->payPalEmail;
    }

    /**
     * @return array
     */
    public function getPaymentOptions()
    {
        return $this->paymentOptions;
    }

    /**
     * @param $email
     * @return mixed
     * @throws ValidationException
     */
    protected function validateEmail($email)
    {
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('The provided email is invalid.');
        };
    }

    /**
     * @param $siteID
     * @return bool
     * @throws ValidationException
     */
    public function validateEbaySiteID($siteID)
    {
        $reflection = new \ReflectionClass(SiteIdCodes::class);

        $constants = array_keys($reflection->getConstants());

        if(!in_array($siteID, $constants)) {
            throw new ValidationException('The provided eBay site key is invalid.');
        }
    }

    /**
     * Checks if the provided payment option is supported.
     *
     * @param $option
     * @return bool
     * @throws ValidationException
     */
    protected function validatePaymentOption($option)
    {
        if(!in_array($option, [self::PAYMENT_CASH_ON_DELIVERY, self::PAYMENT_VISA_MASTER_CARD, self::PAYMENT_PAYPAL])) {
            throw new ValidationException('The provided payment method is not valid.');
        }
    }

    /**
     * @param $storeData
     * @param $rules
     * @throws ValidationException
     */
    private function validateRequiredDataInArray($storeData, $rules)
    {
        foreach($rules as $key => $required) {
            if($required && !array_key_exists($key, $storeData)) {
                throw new ValidationException('Store ' . $key . ' was not provided');
            }
        }
    }

    /**
     * @param $element
     * @return array
     */
    public function getStoreData($element)
    {
        // throw exception if element is missing
        return $this->storeData[$element];
    }

    /**
     * @param $element
     * @return bool
     */
    public function hasStoreData($element)
    {
        return array_key_exists($element, $this->storeData);
    }
}