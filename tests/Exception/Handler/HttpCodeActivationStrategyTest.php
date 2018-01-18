<?php

namespace Tests\App\Exception\Handler;

use App\Exception\Handler\HttpCodeActivationStrategy;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HttpCodeActivationStrategyTest extends TestCase
{
    /**
     * @dataProvider isActivatedProvider
     */
    public function testIsActivated($url, $record, $expected)
    {
        $requestStack = new RequestStack();
        $requestStack->push(Request::create($url));

        $strategy = new HttpCodeActivationStrategy($requestStack, array(403, 404, 405), Logger::WARNING);

        $this->assertEquals($expected, $strategy->isHandlerActivated($record));
    }

    public function isActivatedProvider()
    {
        return array(
            array('/test', array('level' => Logger::ERROR), true),
            array('/foo',  array('level' => Logger::ERROR, 'context' => $this->getContextException(401)), true),
            array('/foo',  array('level' => Logger::ERROR, 'context' => $this->getContextException(402)), true),
            array('/foo',  array('level' => Logger::ERROR, 'context' => $this->getContextException(403)), false),
            array('/foo',  array('level' => Logger::ERROR, 'context' => $this->getContextException(404)), false),
            array('/foo',  array('level' => Logger::ERROR, 'context' => $this->getContextException(405)), false),
            array('/foo',  array('level' => Logger::ERROR, 'context' => $this->getContextException(406)), true),
            array('/foo',  array('level' => Logger::ERROR, 'context' => $this->getContextException(500)), true),
        );
    }

    protected function getContextException($code)
    {
        return array('exception' => new HttpException($code));
    }
}
