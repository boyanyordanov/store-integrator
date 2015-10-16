<?php

namespace StoreIntegrator\Amazon;


use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;
use StoreIntegrator\AbstractProduct;
use StoreIntegrator\Product;

/**
 * Class Product
 * @package StoreIntegrator\Amazon
 */
class AmazonProduct extends Product implements XmlSerializable
{
    /**
     * @var string
     */
    protected $productType;

    public function __construct($data)
    {
        parent::__construct($data);
        $this->productType = $data['productType'];
        $this->msrp = $data['price'];
    }

    /**
     * The xmlSerialize metod is called during xml writing.
     *
     * Use the $writer argument to write its own xml serialization.
     *
     * An important note: do _not_ create a parent element. Any element
     * implementing XmlSerializble should only ever write what's considered
     * its 'inner xml'.
     *
     * The parent of the current element is responsible for writing a
     * containing element.
     *
     * This allows serializers to be re-used for different element names.
     *
     * If you are opening new elements, you must also close them again.
     *
     * @param Writer $writer
     * @return void
     */
    function xmlSerialize(Writer $writer)
    {
        $productData = [
            $this->category => []
        ];

        if($this->productType) {
            $productData[$this->category] = [
                'ProductType' => [$this->productType => [
                    'AdditionalDrives' => 'dvd',
                    'ComputerMemoryType' => 'sodimm',
                    'DisplayResolutionMaximum' => 'fullhd   '
                ]]
            ];
        }

        $writer->write([
            'MessageID' => 1,
            'OperationType' => 'Update',
            'Product' => [
                'SKU' => $this->sku,
                'DescriptionData' => [
                    'Title' => $this->title,
                    'Brand' => $this->brand,
                    'Description' => $this->description,
                    'MSRP' => [
                        'attributes' => [
                            'currency' => $this->currency
                        ],
                        'value' => $this->msrp
                    ],
                ],
                'ProductData' => $productData
            ]
        ]);
    }
}