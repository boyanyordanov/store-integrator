<?php

namespace StoreIntegrator\tests;


use DOMDocument;
use Exception;
use Guzzle\Http\EntityBody;
use Guzzle\Http\Message\Response;
use PHPUnit_Framework_TestCase;

class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @param $schema
     * @param $actual
     * @param $message
     */
    public function assertValidXML($schema, $actual, $message = 'The provided XML does not validate against the provided schema')
    {
        $xml = new DOMDocument();

        if(file_exists($actual)) {
            $xml->load($actual);
        } else {
            $xml->loadXML($actual);
        }

        try{
            $this->assertTrue($xml->schemaValidate($schema), $message);
        } catch(Exception $e) {
            $this->fail($message . "\n" . $e->getMessage());
        }

    }

    /**
     * @param $xmlStubPath
     * @return Response
     */
    public function generateEbaySuccessResponse($xmlStubPath)
    {
        $mockReponse = new Response(200);
        $mockReponse->setBody(EntityBody::factory(file_get_contents($xmlStubPath)));
        return $mockReponse;
    }
}