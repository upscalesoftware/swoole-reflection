<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Reflection\Http\Response;

class Proxy extends \Swoole\Http\Response
{
    /**
     * @var \Swoole\Http\Response 
     */
    protected $subject;
    
    /**
     * Inject dependencies
     * 
     * @param \Swoole\Http\Response $subject
     */
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
     * @param bool $ucwords
     * @return mixed
     */
    public function header($key, $value, $ucwords = null)
    {
        $result = $this->subject->header($key, $value, $ucwords);
        $this->header = $this->subject->header;
        return $result;
    }

    /**
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @param bool $samesite
     * @return mixed
     */
    public function cookie(
        $name, $value = null, $expires = null, $path = null, $domain = null, $secure = null, $httponly = null, $samesite = null
    ) {
        $result = ($samesite === null)
            ? $this->subject->cookie($name, $value, $expires, $path, $domain, $secure, $httponly)
            : $this->subject->cookie($name, $value, $expires, $path, $domain, $secure, $httponly, $samesite);
        $this->cookie = $this->subject->cookie;
        return $result;
    }

    /**
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @param bool $samesite
     * @return mixed
     */
    public function rawcookie(
        $name, $value = null, $expires = null, $path = null, $domain = null, $secure = null, $httponly = null, $samesite = null
    ) {
        $result = ($samesite === null)
            ? $this->subject->rawcookie($name, $value, $expires, $path, $domain, $secure, $httponly)
            : $this->subject->rawcookie($name, $value, $expires, $path, $domain, $secure, $httponly, $samesite);
        $this->cookie = $this->subject->cookie;
        return $result;
    }

    /**
     * @param int $code
     * @param string|null $reason
     * @return mixed
     */
    public function status($code, $reason = null)
    {
        return ($reason === null)
            ? $this->subject->status($code)
            : $this->subject->status($code, $reason);
    }

    /**
     * @param int $level
     * @return mixed
     */
    public function gzip($level = 1)
    {
        return $this->subject->gzip($level);
    }

    /**
     * @param string $filename
     * @param int $offset
     * @param int $length
     * @return mixed
     */
    public function sendfile($filename, $offset = null, $length = null)
    {
        return $this->subject->sendfile($filename, $offset, $length);
    }
}
