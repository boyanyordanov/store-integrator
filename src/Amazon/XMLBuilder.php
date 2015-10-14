<?php

namespace StoreIntegrator\Amazon;


use Sabre\Xml\Writer;
use Sabre\Xml\XmlSerializable;

/**
 * Class XMLBuilder
 * @package StoreIntegrator\Amazon
 */
abstract class XMLBuilder
{
    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @var array
     */
    protected $rootElAttributes;

    /**
     * @var string
     */
    protected $rootEl;

    /**
     * @param Writer $writer
     */
    public function __construct(Writer $writer)
    {

        $this->writer = $writer;
    }

    /**
     * @param XmlSerializable $data
     * @return mixed
     */
    abstract public function create(XmlSerializable $data);

    /**
     * @param XmlSerializable $data
     * @return string
     */
    protected function buildMessage(XmlSerializable $data)
    {
        $this->writer->openMemory();

        $this->writer->write([
            $this->rootEl => [
                'attributes' => $this->rootElAttributes,
                'value' => $data
            ]
        ]);

        return $this->writer->outputMemory(true);
    }

    /**
     * @param $uri
     * @param $namespace
     */
    public function mapNamespace($uri, $namespace)
    {
        $this->writer->namespaceMap[$uri] = $namespace;
    }

    /**
     * @param $name
     * @param $value
     */
    public function setRootElAttribute($name, $value)
    {
        $this->rootElAttributes[$name] = $value;
    }
}