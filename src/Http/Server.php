<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Upscale\Swoole\Reflection\Http;

class Server
{
    /**
     * @var \Swoole\Http\Server
     */
    protected $server;

    /**
     * Inject dependencies
     * 
     * @param \Swoole\Http\Server $server
     */
    public function __construct(\Swoole\Http\Server $server)
    {
        $this->server = $server;
    }

    /**
     * Retrieve a middleware handling requests of the server
     * 
     * @return callable
     * @throws \UnexpectedValueException
     */
    public function getMiddleware()
    {
        $middleware = $this->getCallback($this->server, 'request')
            ?: $this->getCallback($this->getPrimaryPort(), 'request');
        if (!is_callable($middleware)) {
            throw new \UnexpectedValueException('Server middleware has not been detected.');
        }
        return $middleware;
    }

    /**
     * Assign a middleware handling requests of the server, overriding the existing one
     *
     * @param callable $middleware
     * @throws \UnexpectedValueException
     */
    public function setMiddleware(callable $middleware)
    {
        $this->server->on('request', $middleware);
    }

    /**
     * Retrieve the primary port listened by the server
     * 
     * @return \Swoole\Server\Port
     * @throws \UnexpectedValueException 
     */
    public function getPrimaryPort()
    {
        foreach ((array)$this->server->ports as $port) {
            if ($port->host == $this->server->host && $port->port == $this->server->port) {
                return $port;
            }
        }
        throw new \UnexpectedValueException('Server port has not been identified.');
    }

    /**
     * Retrieve a callback subscribed to a given event
     * 
     * @param object $observable
     * @param string $eventName
     * @return callable|null
     */
    protected function getCallback($observable, $eventName)
    {
        try {
            $propertyName = 'on' . ucfirst($eventName);
            $property = new \ReflectionProperty($observable, $propertyName);
            $property->setAccessible(true);
            return $property->getValue($observable);
        } catch (\ReflectionException $e) {
            return null;
        }
    }
}
