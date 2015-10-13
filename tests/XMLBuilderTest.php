<?php

use Sabre\Xml\XmlSerializable;
use StoreIntegrator\Amazon\XMLBuilder;

class MyItem implements XmlSerializable {

    protected $name;
    protected $description;

    public $namespace = "{http://link-to-amazon.dev/definition.xsd}";

    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->description = $data['description'];
    }

    function xmlSerialize(\Sabre\Xml\Writer $writer)
    {
        $writer->write([
           'name' => $this->name,
           'description' => $this->description
        ]);
    }
}

class XMLBuilderConcrete extends XMLBuilder {

    protected $rootEl = 'item';

    protected $rootElAttributes = [];

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        $item = new MyItem($data);

        return $this->buildMessage($item);
    }
}

class XMLBuilderTest extends PHPUnit_Framework_TestCase
{
    public function testXMLBuilding()
    {
        $builder = new XMLBuilderConcrete(new \Sabre\Xml\Writer());

        $data = [
            'name' => 'Product name',
            'description' => 'Product description'
        ];

        $expectedXML = '<?xml version="1.0" encoding="iso-8859-1"?><item><name>Product name</name><description>Product description</description></item>';

        $this->assertXmlStringEqualsXmlString($expectedXML, $builder->create($data));
    }

    public function testXMLNamespace()
    {
        $builder = new XMLBuilderConcrete(new \Sabre\Xml\Writer());

        $builder->mapNamespace('http://www.w3.org/2001/XMLSchema', '');
        $builder->setRootElAttribute('some-root-attr', 'root-attr-value');

        $data = [
            'name' => 'Product name',
            'description' => 'Product description'
        ];

        $expectedXML = '<?xml version="1.0" encoding="iso-8859-1"?><item xmlns="http://www.w3.org/2001/XMLSchema" some-root-attr="root-attr-value"><name>Product name</name><description>Product description</description></item>';

        $this->assertXmlStringEqualsXmlString($expectedXML, $builder->create($data));
    }
}
