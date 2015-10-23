<?php

namespace StoreIntegrator\tests;

use StoreIntegrator\Store;

class StoreTest extends TestCase
{
    public function testDefaultData()
    {
        $store = new Store('testEmail@mail.dev', [
            'location' => 'Varna',
            'postCode' => '9000'
        ]);

        $this->assertEquals(0, $store->getEbaySiteID(), 'Incorrectly set the default site to something other than US.');
        $this->assertEquals('testEmail@mail.dev', $store->getPaypalEmail(), 'The PayPal email does not match the provided in the constructor.');
        $this->assertContains('PayPal', $store->getPaymentOptions(), 'The default payment method was not set to PayPal as expected.');
    }


    /**
     * @expectedException \StoreIntegrator\Exceptions\ValidationException
     * @expectedExceptionMessage The provided email is invalid.
     */
    public function testPayPalEmailValidation()
    {
        new Store('invalid-email', [
            'location' => 'Varna',
            'postCode' => '9000'
        ]);
    }

    /**
     * @expectedException \StoreIntegrator\Exceptions\ValidationException
     * @expectedExceptionMessage The provided eBay site code is invalid.
     */
    public function testEbaySiteIDValidation()
    {
        new Store('testEmail@mail.dev', ['location' => 'Varna', 'postCode' => '9000'], 1411523);
    }

    /**
     * @expectedException \StoreIntegrator\Exceptions\ValidationException
     * @expectedExceptionMessage The provided payment method is not valid.
     */
    public function testPaymentMethodValidation()
    {
        $store = new Store('testEmail@mail.dev', [
            'location' => 'Varna',
            'postCode' => '9000'
        ]);

        $store->addPaymentOptions(['fafafa']);
    }

    public function testSettingPaymentMethods()
    {
        $store = new Store('testEmail@mail.dev', [
            'location' => 'Varna',
            'postCode' => '9000'
        ]);

        $store->addPaymentOptions([Store::PAYMENT_CASH_ON_DELIVERY, Store::PAYMENT_VISA_MASTER_CARD]);

        $this->assertEquals(['COD', 'VisaMC'], $store->getPaymentOptions(), 'Payment options were not set correctly.');
    }

    /**
     * @expectedException \StoreIntegrator\Exceptions\ValidationException
     * @expectedExceptionMessage Store location was not provided
     */
    public function testLocationStoreDataValidation()
    {
        new Store('testEmail@mail.dev');
    }

    /**
     * @expectedException \StoreIntegrator\Exceptions\ValidationException
     * @expectedExceptionMessage Store postCode was not provided
     */
    public function testPostCodeStoreDataValidation()
    {
        new Store('testEmail@mail.dev', ['location' => 'Varna, Bulgaria']);
    }
}
