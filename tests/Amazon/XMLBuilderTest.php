<?php

namespace StoreIntegrator\tests\Amazon;

use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use StoreIntegrator\Amazon\XMLBuilder;
use StoreIntegrator\tests\TestCase;

class MyItem implements XmlSerializable {

    protected $name;
    protected $description;

    public $namespace = "{http://link-to-amazon.dev/definition.xsd}";

    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->description = $data['description'];
    }

    function xmlSerialize(Writer $writer)
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
     * @param XmlSerializable $data
     * @return mixed
     */
    public function create(XmlSerializable $data)
    {
        $this->mapNamespace('http://www.w3.org/2001/XMLSchema', '');
        $this->setRootElAttribute('some-root-attr', 'root-attr-value');

        return $this->buildMessage($data);
    }
}

class XMLBuilderTest extends TestCase
{
    public function testXMLBuildingAndNamespacing()
    {
        $builder = new XMLBuilderConcrete(new Writer());

        $data = new MyItem([
            'name' => 'Product name',
            'description' => 'Product description'
        ]);

        $expectedXML = '<?xml version="1.0" encoding="iso-8859-1"?><item xmlns="http://www.w3.org/2001/XMLSchema" some-root-attr="root-attr-value"><Message><name>Product name</name><description>Product description</description></Message></item>';

        $this->assertXmlStringEqualsXmlString($expectedXML, $builder->create($data));
    }
}
