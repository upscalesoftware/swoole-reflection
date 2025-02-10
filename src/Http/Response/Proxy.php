<?php
declare(strict_types=1);
/**
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Upscale\Swoole\Reflection\Http\Response;

class Proxy extends \Swoole\Http\Response
{
    protected \Swoole\Http\Response $subject;
    
    public function __construct(\Swoole\Http\Response $subject)
    {
        $this->subject = $subject;
        $this->fd = $subject->fd;
    }

    public function end(?string $content = null): bool
    {
        return $this->subject->end($content);
    }

    public function write(string $content): bool
    {
        return $this->subject->write($content);
    }

    public function header(string $key, array|string $value, bool $format = true): bool
    {
        $result = $this->subject->header($key, $value, $format);
        $this->header = $this->subject->header;
        return $result;
    }

    public function cookie(...$args): bool {
        $result = $this->subject->cookie(...$args);
        $this->cookie = $this->subject->cookie;
        return $result;
    }

    public function rawcookie(...$args): bool {
        $result = $this->subject->rawcookie(...$args);
        $this->cookie = $this->subject->cookie;
        return $result;
    }

    public function status(int $code, string $reason = ''): bool
    {
        return $this->subject->status($code, $reason);
    }

    public function sendfile(string $filename, int $offset = 0, int $length = 0): bool
    {
        return $this->subject->sendfile($filename, $offset, $length);
    }
}
