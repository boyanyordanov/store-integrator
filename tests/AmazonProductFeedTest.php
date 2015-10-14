<?php

class AmazonProductFeedTest extends PHPUnit_Framework_TestCase
{
    public function testAgainstXSD()
    {
        $xml = new DOMDocument();
        $xml->load(__DIR__ . '/xmlStubs/amazon-product.xml');

        $this->assertTrue($xml->schemaValidate(__DIR__ . '/xmlStubs/another-xsds/amzn-envelope.xsd'), 'The generated feed does not validate against Amazon definition.');
    }
}
