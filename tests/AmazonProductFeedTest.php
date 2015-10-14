<?php

namespace StoreIntegrator\tests;

class AmazonProductFeedTest extends TestCase
{
    public function testTestXMLAgainstXSD()
    {
        $schema = __DIR__ . '/xmlStubs/xsd/amzn-envelope.xsd';
        $xml = __DIR__ . '/xmlStubs/amazon-product.xml';
        $this->assertValidXML($schema, $xml, 'The generated feed does not validate against Amazon definition.');
    }
}
