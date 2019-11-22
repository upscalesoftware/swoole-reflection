<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Reflection\Tests\Http\Response;

use Upscale\Swoole\Reflection\Http\Response\Observable;

class ObservableTest extends ProxyTest
{
    /**
     * @param \Swoole\Http\Response $response
     * @return Observable
     */
    protected function proxy(\Swoole\Http\Response $response)
    {
        return new Observable($response);
    }
    
    public function testOnHeadersSentBeforeEnd()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $callback = function () use ($response) {
                $response->header('Content-Type', 'text/plain');
            };
            $response = $this->proxy($response);
            $response->onHeadersSentBefore($callback);
            $response->end('Test');
        });
        $this->spawn($this->server);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Content-Length: 4\r\n", $result);
        $this->assertContains("Content-Type: text/plain\r\n", $result);
        $this->assertStringEndsWith("\r\n\r\nTest", $result);
    }
    
    public function testOnHeadersSentBeforeWrite()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $callback = function () use ($response) {
                $response->header('Content-Type', 'text/plain');
            };
            $response = $this->proxy($response);
            $response->onHeadersSentBefore($callback);
            $response->write('Test1');
            $response->write('Test2');
        });
        $this->spawn($this->server);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Content-Type: text/plain\r\n", $result);
        $this->assertStringEndsWith("\r\n\r\nTest1Test2", $result);
    }

    public function testOnHeadersSentBeforeSendfile()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $callback = function () use ($response) {
                $response->header('Content-Type', 'text/csv');
            };
            $response = $this->proxy($response);
            $response->onHeadersSentBefore($callback);
            $response->sendfile(__DIR__ . '/../../_files/fixture.txt');
        });
        $this->spawn($this->server);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Content-Type: text/csv\r\n", $result);
        $this->assertContains("Content-Length: 10\r\n\r\n", $result);
        $this->assertStringEndsWith("\r\n\r\n0123456789", $result);
    }

    public function testOnBodyAppendEnd()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = $this->proxy($response);
            $response->onBodyAppend(function (&$content) {
                $content .= "<!-- Served by worker {$this->server->worker_id} -->";
            });
            $response->end('Test');
        });
        $this->spawn($this->server);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Content-Length: 31\r\n", $result);
        $this->assertContains("Content-Type: text/html\r\n", $result);
        $this->assertStringEndsWith("\r\n\r\nTest<!-- Served by worker 0 -->", $result);
    }
    
    public function testOnBodyAppendWrite()
    {
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response = $this->proxy($response);
            $response->onBodyAppend(function (&$content) {
                $content = "<!-- $content -->";
            });
            $response->write('Test1');
            $response->write('Test2');
        });
        $this->spawn($this->server);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringStartsWith("HTTP/1.1 200 OK\r\n", $result);
        $this->assertContains("Content-Type: text/html\r\n", $result);
        $this->assertStringEndsWith("\r\n\r\n<!-- Test1 --><!-- Test2 -->", $result);
    }
}