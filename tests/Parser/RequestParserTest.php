<?php

namespace Clue\Tests\Redis\Protocol\Parser;

use Clue\Redis\Protocol\Model\Request;
use Clue\Redis\Protocol\Parser\RequestParser;

class RequestParserTest extends AbstractParserTest
{
    protected function createParser()
    {
        return new RequestParser();
    }

    public function testSimplePingRequest()
    {
        $message = "*1\r\n$4\r\nping\r\n";

        $this->assertCount(1, $models = $this->parser->pushIncoming($message));

        $request = reset($models);

        $this->assertInstanceOf('Clue\Redis\Protocol\Model\Request', $request);
        $this->assertEquals('ping', $request->getCommand());
        $this->assertEquals(array(), $request->getArgs());

        return $request;
    }

    /**
     *
     * @param Request $expected
     * @depends testSimplePingRequest
     */
    public function testInlinePingRequest(Request $expected)
    {
        $message = "ping\r\n";

        $this->assertCount(1, $models = $this->parser->pushIncoming($message));

        $request = reset($models);

        $this->assertEquals($expected, $request);
    }

    public function testInlineWhitespaceIsIgnored()
    {
        $message = "    set     name    value  \r\n";

        $this->assertCount(1, $models = $this->parser->pushIncoming($message));

        $request = reset($models);

        $this->assertInstanceOf('Clue\Redis\Protocol\Model\Request', $request);
        $this->assertEquals('set', $request->getCommand());
        $this->assertEquals(array('name', 'value'), $request->getArgs());
    }

    public function testIncompleteSuccessive()
    {
        $this->assertEquals(array(), $this->parser->pushIncoming("*1\r\n"));
        $this->assertEquals(array(), $this->parser->pushIncoming("$4\r\n"));
        $this->assertEquals(array(), $this->parser->pushIncoming("test"));
        $this->assertCount(1, $this->parser->pushIncoming("\r\n"));
    }

    public function testNullMultiBulkRequestIsIgnored()
    {
        $message = "*-1\r\n";

        $this->assertEquals(array(), $this->parser->pushIncoming($message));
    }

    public function testEmptyMultiBulkRequestIsIgnored()
    {
        $message = "*0\r\n";

        $this->assertEquals(array(), $this->parser->pushIncoming($message));
    }

    public function testEmptyInlineIsIgnored()
    {
        $message = "\r\n";

        $this->assertEquals(array(), $this->parser->pushIncoming($message));
    }

    public function testInlineParsesMultipleRequestsAtOnce()
    {
        $message = "hello\r\n\world\r\ntest\r\n";

        $this->assertCount(3, $this->parser->pushIncoming($message));
    }


    public function testEmptyInlineAroundInlineIsIgnored()
    {
        $message = "\r\n\r\n" . "ping\r\n\r\n";

        $this->assertCount(1, $models = $this->parser->pushIncoming($message));

        $request = reset($models);

        $this->assertInstanceOf('Clue\Redis\Protocol\Model\Request', $request);
        $this->assertEquals('ping', $request->getCommand());
        $this->assertEquals(array(), $request->getArgs());
    }

    public function testWhitespaceInlineIsIgnored()
    {
        $message = "      \r\n";

        $this->assertEquals(array(), $this->parser->pushIncoming($message));
    }

    public function testInvalidMultiBulkMustContainBulk()
    {
        $message = "*1\r\n:123\r\n";

        $this->setExpectedException('Clue\Redis\Protocol\Parser\ParserException');
        $this->parser->pushIncoming($message);
    }

    public function testInvalidBulkLength()
    {
        $message = "*1\r\n$-1\r\n";

        $this->setExpectedException('Clue\Redis\Protocol\Parser\ParserException');
        $this->parser->pushIncoming($message);
    }
}
