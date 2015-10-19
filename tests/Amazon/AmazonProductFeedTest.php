<?php

namespace StoreIntegrator\tests\Amazon;

use Sabre\Xml\Writer;
use StoreIntegrator\Amazon\AmazonProduct;
use StoreIntegrator\Amazon\ProductFeed;
use StoreIntegrator\tests\TestCase;

class AmazonProductFeedTest extends TestCase
{
    public function testTestXMLAgainstXSD()
    {
        $schema = __DIR__ . '/../xmlStubs/xsd/amzn-envelope.xsd';
        $xml = __DIR__ . '/../xmlStubs/amazon-product.xml';
        $this->assertValidXML($schema, $xml, 'The generated feed does not validate against Amazon definition.');
    }

    public function testFeedGenerationForComputerCategory()
    {
        $product = new AmazonProduct([
            'name' => 'Dell Vostro',
            'description' => 'Somee cool seo description',
            'category' => 'Computers',
            'productType' => 'NotebookComputer',
            'price' => 2000,
            'currency' => 'USD',
            'brand' => 'Dell',
            'sku' => 'd1234',
            'weight' => 7000,
            'quantity' => 100
        ]);

        $feedGenerator = new ProductFeed(new Writer());

//        $this->assertValidXML(__DIR__ . '/../xmlStubs/xsd/amzn-envelope.xsd', $feedGenerator->create($product));
        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/../xmlStubs/amzn-computer-product.xml', $feedGenerator->create($product));
    }
}
