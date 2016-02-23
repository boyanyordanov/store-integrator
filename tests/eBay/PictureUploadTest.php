<?php

namespace StoreIntegrator\tests\eBay;


use StoreIntegrator\eBay\Wrappers\ProductWrapper;
use StoreIntegrator\tests\TestCase;

class PictureUploadTest extends TestCase
{

    protected $userToken;
    protected $productWrapper;

    public function setUp()
    {
        parent::setUp();

        $this->setUpEbayServiceMocks();

        $this->userToken = 'user-auth-token';

        $store = $this->sampleStore();

        $this->productWrapper = new ProductWrapper($this->userToken, $store, $this->tradingService);

    }

    public function testUploadRequest()
    {
        $mockResponse = $this->generateEbaySuccessResponse('<?xml version="1.0" encoding="utf-8"?>'
            . '<UploadSiteHostedPicturesResponse xmlns="urn:ebay:apis:eBLBaseComponents">'
                . '<Timestamp>2015-04-19T23:18:20.560Z</Timestamp>'
                . '<Ack>Success</Ack>'
                . '<Version>919</Version>'
                . '<Build>E919_CORE_MSA_17469444_R1</Build>'
                . '<SiteHostedPictureDetails>'
                    . '<PictureName>Hosted picture name</PictureName>'
                    . '<PictureFormat>JPG</PictureFormat>'
                    . '<FullURL>http://i.ebayimg.com/00/s/NDAwWDE2MDA=/z/egUAAOSwBahVNDe8/$_1.JPG?set_id=8800005007</FullURL>'
                    . '<UseByDate>2016-03-01</UseByDate>'
                . '</SiteHostedPictureDetails>'
            .'</UploadSiteHostedPicturesResponse>'
        );

        $this->attachMockedEbayResponse($mockResponse);

        $response = $this->productWrapper->uploadPicture('http://picture.url', 'Hosted picture name');

        $this->assertEquals('Hosted picture name', $response->name);
        $this->assertEquals('http://i.ebayimg.com/00/s/NDAwWDE2MDA=/z/egUAAOSwBahVNDe8/$_1.JPG?set_id=8800005007', $response->url);
        $this->assertEquals('JPG', $response->format);
        $this->assertEquals('2016-03-01', $response->expireDate->format('Y-m-d'));
    }

}
