<?php

namespace StoreIntegrator\tests;

use Sabre\Xml\Writer;
use StoreIntegrator\Amazon\Product;
use StoreIntegrator\Amazon\ProductFeed;

class AmazonProductFeedTest extends TestCase
{
    public function testTestXMLAgainstXSD()
    {
        $schema = __DIR__ . '/xmlStubs/xsd/amzn-envelope.xsd';
        $xml = __DIR__ . '/xmlStubs/amazon-product.xml';
        $this->assertValidXML($schema, $xml, 'The generated feed does not validate against Amazon definition.');
    }

    public function testFeedGenerationForComputerCategory()
    {
        $product = new Product([
            'name' => 'Dell Vostro',
            'description' => 'Somee cool seo description',
            'category' => 'Computers',
            'productType' => 'NotebookComputer',
            'price' => 2000,
            'currency' => 'USD',
            'vendor' => 'Dell',
            'sku' => 'd1234',
            'weight' => 7000
        ]);

        $feedGenerator = new ProductFeed(new Writer());

        $this->assertValidXML(__DIR__ . '/xmlStubs/xsd/amzn-envelope.xsd', $feedGenerator->create($product));
    }
}
