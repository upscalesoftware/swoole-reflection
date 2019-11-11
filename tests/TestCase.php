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
     * @param string $url
     * @param int $timeout Timeout in seconds
     * @return string
     */
    public static function curl($url, $timeout = 10)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Connection: close']);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}