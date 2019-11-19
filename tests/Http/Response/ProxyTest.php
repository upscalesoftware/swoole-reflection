<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Reflection\Tests\Http\Response;

use Upscale\Swoole\Reflection\Http\Response\Proxy;
use Upscale\Swoole\Reflection\Tests\TestCase;

class ProxyTest extends TestCase
{
    /**
     * @var \Swoole\Http\Server
     */
    protected $server;

    protected function setUp()
    {
        $this->server = new \Swoole\Http\Server('127.0.0.1', 8080);
        $this->server->set([
            'log_file' => '/dev/null',
            'log_level' => 4,
            'worker_num' => 1,
        ]);
    }

    public function testEnd()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = new Proxy($response);
            $response->end();
        });
        $this->spawn($this->server);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Content-Length: 0\r\n\r\n", $result);
    }

    public function testEndContent()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = new Proxy($response);
            $response->end('Test');
        });
        $this->spawn($this->server);
        
        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Content-Length: 4\r\n", $result);
        $this->assertContains("\r\n\r\nTest", $result);
    }

    public function testWriteContent()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = new Proxy($response);
            $response->write('Test1');
            $response->write('Test2');
        });
        $this->spawn($this->server);
        
        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("\r\n\r\nTest1Test2", $result);
    }

    public function testHeader()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = new Proxy($response);
            $response->header('Content-Type', 'text/plain');
            $response->end();
        });
        $this->spawn($this->server);
        
        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Content-Type: text/plain\r\n", $result);
    }

    public function testHeaderUcwords()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = new Proxy($response);
            $response->header('content-type', 'text/plain', true);
            $response->end();
        });
        $this->spawn($this->server);
        
        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Content-Type: text/plain\r\n", $result);
    }

    public function testCookie()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = new Proxy($response);
            $response->cookie('SID', 'test 123');
            $response->end();
        });
        $this->spawn($this->server);
        
        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Set-Cookie: SID=test+123\r\n", $result);
    }

    public function testRawCookie()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = new Proxy($response);
            $response->rawcookie('SID', 'test 123');
            $response->end();
        });
        $this->spawn($this->server);
        
        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Set-Cookie: SID=test 123\r\n", $result);
    }

    public function testStatus()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = new Proxy($response);
            $response->status(404);
            $response->end();
        });
        $this->spawn($this->server);
        
        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 404 Not Found\r\n", $result);
    }

    public function testStatusReason()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = new Proxy($response);
            $response->status(404, 'Missing');
            $response->end();
        });
        $this->spawn($this->server);
        
        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 404 Missing\r\n", $result);
    }

    public function testGzip()
    {
        if (version_compare(swoole_version(), '4.1.1', '>=')) {
            $this->markTestSkipped();
        }
        
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = new Proxy($response);
            $response->gzip();
            $response->end('Test');
        });
        $this->spawn($this->server);
        
        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Content-Encoding: gzip\r\n", $result);
    }

    public function testSendfile()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = new Proxy($response);
            $response->sendfile(__DIR__ . '/../../_files/fixture.txt');
        });
        $this->spawn($this->server);
        
        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Content-Length: 10\r\n\r\n", $result);
        $this->assertContains("\r\n\r\n0123456789", $result);
    }
    
    public function testSendfileOffset()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = new Proxy($response);
            $response->sendfile(__DIR__ . '/../../_files/fixture.txt', 4);
        });
        $this->spawn($this->server);
        
        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Content-Length: 6\r\n\r\n", $result);
        $this->assertContains("\r\n\r\n456789", $result);
    }
    
    public function testSendfileOffsetLength()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = new Proxy($response);
            $response->sendfile(__DIR__ . '/../../_files/fixture.txt', 4, 3);
        });
        $this->spawn($this->server);
        
        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Content-Length: 3\r\n\r\n", $result);
        $this->assertContains("\r\n\r\n456", $result);
    }
}