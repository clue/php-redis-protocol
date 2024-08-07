<?php

namespace Clue\Tests\Redis\Protocol;

use Clue\Redis\Protocol\Factory;

class FactoryTest extends TestCase
{
    private $factory;

    /**
     * @before
     */
    public function setUpFactory()
    {
        $this->factory = new Factory();
    }

    public function testCreateResponseParser()
    {
        $parser = $this->factory->createResponseParser();

        $this->assertInstanceOf('Clue\Redis\Protocol\Parser\ParserInterface', $parser);
    }

    public function testCreateRequestParser()
    {
        $parser = $this->factory->createRequestParser();

        $this->assertInstanceOf('Clue\Redis\Protocol\Parser\ParserInterface', $parser);
    }

    public function testCreateSerializer()
    {
        $serializer = $this->factory->createSerializer();

        $this->assertInstanceOf('Clue\Redis\Protocol\Serializer\SerializerInterface', $serializer);
    }
}
