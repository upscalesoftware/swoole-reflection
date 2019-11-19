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
     * @param int $timeout
     */
    public static function spawn(\Swoole\Server $server, $timeout = 10)
    {
        $lock = new \Swoole\Atomic;
        
        $server->on('workerStart', function ($server) use ($lock, $timeout) {
            $server->tick(100, function ($timerId) use ($server, $timeout) {
                $status = $server->stats();
                $served = $status['request_count'] ?? 0;
                $uptime = time() - $status['start_time'];
                if ($served > 0 || $uptime > $timeout) {
                    $server->clearTimer($timerId);
                    $server->shutdown();
                }
            });
            $lock->wakeup();
        });
        
        $process = new \Swoole\Process([$server, 'start']);
        $process->start();
        
        $lock->wait($timeout);
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
        curl_setopt($curl, CURLOPT_FORBID_REUSE, true);
        curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}