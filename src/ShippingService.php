<?php

namespace StoreIntegrator;


use StoreIntegrator\Contracts\ShippingServiceInterface;

/**
 * Class ShippingService
 * Has __call method to allow dynamic getters for custom data
 * added for different providers.
 * @package StoreIntegrator
 */
abstract class ShippingService implements ShippingServiceInterface
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @param mixed $id
     * @param string $name
     * @param string $desc
     * @param array $providerSpecific
     */
    public function __construct($id, $name, $desc, $providerSpecific = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $desc;

        $this->mapProviderSpecificProperties($providerSpecific);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param array $providerSpecific
     */
    private function mapProviderSpecificProperties(array $providerSpecific)
    {
        foreach($providerSpecific as $prop => $val) {
            $this->{$prop} = $val;
        }
    }

    /**
     * Used to for dynamic getters for properties added per providers.
     * @param $method
     * @param $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        // Expects a getter in format getSomeProp and tries to map it to property with format someProp
        $propName = lcfirst(substr($method, 3));

        // if the property exists returns the value
        if(property_exists($this, $propName)) {
            return $this->$propName;
        }
    }
}