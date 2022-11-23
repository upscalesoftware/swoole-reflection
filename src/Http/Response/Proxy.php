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

    /**
     * @param string $content
     * @return mixed
     */
    public function end($content = '')
    {
        return $this->subject->end($content);
    }

    /**
     * @param string $content
     * @return mixed
     */
    public function write($content)
    {
        return $this->subject->write($content);
    }

    /**
     * @param string $key
     * @param string $value
     * @param bool $format
     * @return mixed
     */
    public function header($key, $value, $format = true)
    {
        $result = $this->subject->header($key, $value, $format);
        $this->header = $this->subject->header;
        return $result;
    }

    /**
     * @param string $name
     * @param string|null $value
     * @param int|null $expires
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param bool|null $httponly
     * @param string|null $samesite
     * @param string|null $priority
     * @return mixed
     */
    public function cookie(
        $name,
        $value = null,
        $expires = null,
        $path = null,
        $domain = null,
        $secure = null,
        $httponly = null,
        $samesite = null,
        $priority = null
    ) {
        $result = $this->subject->cookie(...func_get_args());
        $this->cookie = $this->subject->cookie;
        return $result;
    }

    /**
     * @param string $name
     * @param string|null $value
     * @param int|null $expires
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @param bool|null $httponly
     * @param string|null $samesite
     * @param string|null $priority
     * @return mixed
     */
    public function rawcookie(
        $name,
        $value = null,
        $expires = null,
        $path = null,
        $domain = null,
        $secure = null,
        $httponly = null,
        $samesite = null,
        $priority = null
    ) {
        $result = $this->subject->rawcookie(...func_get_args());
        $this->cookie = $this->subject->cookie;
        return $result;
    }

    /**
     * @param int $status
     * @param string $reason
     * @return mixed
     */
    public function status($status, $reason = '')
    {
        return $this->subject->status($status, $reason);
    }

    /**
     * @param string $filename
     * @param int $offset
     * @param int $length
     * @return mixed
     */
    public function sendfile($filename, $offset = 0, $length = 0)
    {
        return $this->subject->sendfile($filename, $offset, $length);
    }
}
