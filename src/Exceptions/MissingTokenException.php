<?php
/**
 * Created by PhpStorm.
 * User: boyan
 * Date: 04.11.15
 * Time: 15:25
 */

namespace StoreIntegrator\Exceptions;


/**
 * Class MissingTokenException
 * @package StoreIntegrator\Exceptions
 */
class MissingTokenException extends \Exception
{

    /**
     * MissingTokenException constructor.
     * @param string $string
     */
    public function __construct($string)
    {
    }
}