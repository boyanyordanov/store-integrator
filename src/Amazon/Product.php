<?php

namespace StoreIntegrator\Amazon;


use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Class Product
 * @package StoreIntegrator\Amazon
 */
class Product implements XmlSerializable
{
    /**
     * @var string
     */
    protected $sku;
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $brand;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var number
     */
    protected $msrp;
    /**
     * @var string
     */
    protected $currency;
    /**
     * @var string
     */
    protected $category;
    /**
     * @var string
     */
    protected $productType;
    /**
     * @var number
     */
    protected $weight;

    /**
     * @param $data
     */
    public function __construct($data)
    {
        $this->sku = $data['sku'];
        $this->title = $data['name'];
        $this->description = $data['description'];
        $this->brand = $data['vendor'];
        $this->msrp = $data['price'];
        $this->currency = $data['currency'];
        $this->category = $data['category'];
        $this->productType = $data['productType'];
        $this->weight = $data['weight'];
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