<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Reflection\Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Launch server in a child process
     * 
     * @param \Swoole\Server $server
     * @return int Process PID
     */
    public static function spawn(\Swoole\Server $server)
    {
        $process = new \Swoole\Process([$server, 'start']);
        return $process->start();
    }

    /**
     * Send an HTTP request and return response headers and body
     * 
     * @param string $host
     * @param int $port
     * @param int $timeout Timeout in seconds
     * @return string
     */
    public static function curl($host, $port = 80, $timeout = 10)
    {
        return `curl http://$host:$port/ -H 'Connection: close' -s -i -m $timeout`;
    }
}