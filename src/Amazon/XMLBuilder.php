<?php

namespace StoreIntegrator\Amazon;


use Sabre\Xml\XmlSerializable;

abstract class XMLBuilder
{
    abstract public function create(XmlSerializable $data);

    protected function buildMessage(array $data)
    {

    }
}