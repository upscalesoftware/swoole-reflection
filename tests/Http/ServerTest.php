<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Reflection\Tests\Http;

use Upscale\Swoole\Reflection\Http\Server;

class ServerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Swoole\Http\Server
     */
    protected $server;

    /**
     * @var Server
     */
    protected $subject;

    protected function setUp()
    {
        $this->server = new \Swoole\Http\Server('127.0.0.1', 8080);
        $this->subject = new Server($this->server);
    }

    public function __invoke(\Swoole\Http\Request $request, \Swoole\Http\Response $response)
    {
        $this->fail('Unexpected invocation');
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Server middleware has not been detected
     */
    public function testGetMiddlewareException()
    {
        $this->subject->getMiddleware();
    }

    public function testGetMiddlewareClosure()
    {
        $middleware = function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $this->__invoke($request, $response);
        };
        $this->server->on('request', $middleware);
        $this->assertSame($middleware, $this->subject->getMiddleware());
    }

    public function testGetMiddlewareInvokable()
    {
        $this->server->on('request', $this);
        $this->assertSame($this, $this->subject->getMiddleware());
    }

    public function testGetMiddlewareCallback()
    {
        $middleware = [$this, '__invoke']; 
        $this->server->on('request', $middleware);
        $this->assertSame($middleware, $this->subject->getMiddleware());
    }
    
    public function testSetMiddlewareOverride()
    {
        $this->subject->setMiddleware($this);
        $this->assertSame($this, $this->subject->getMiddleware());
        
        $middleware = [$this, '__invoke']; 
        $this->subject->setMiddleware($middleware);
        $this->assertSame($middleware, $this->subject->getMiddleware());
    }
    
    public function testGetPrimaryPort()
    {
        $port = $this->subject->getPrimaryPort();
        $this->assertInstanceOf(\Swoole\Server\Port::class, $port);
        $this->assertEquals('127.0.0.1', $port->host);
        $this->assertEquals(8080, $port->port);
    }
}