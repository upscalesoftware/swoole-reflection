<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Upscale\Swoole\Reflection\Http\Response;

class Observable extends Proxy
{
    /**
     * @var bool
     */
    protected $isHeadersSent = false;
    
    /**
     * @var callable[] 
     */
    protected $headersSentObservers = [];

    /**
     * @var callable[] 
     */
    protected $bodyAppendObservers = [];

    /**
     * {@inheritdoc}
     */
    public function end($content = '')
    {
        $this->doHeadersSentBefore();
        $this->doBodyAppend($content);
        return parent::end($content);
    }

    /**
     * {@inheritdoc}
     */
    public function write($content)
    {
        $this->doHeadersSentBefore();
        $this->doBodyAppend($content);
        return parent::write($content);
    }

    /**
     * {@inheritdoc}
     */
    public function sendfile($filename, $offset = null, $length = null)
    {
        $this->doHeadersSentBefore();
        return parent::sendfile($filename, $offset, $length);
    }

    /**
     * Subscribe a callback to be notified before sending headers
     * 
     * @param callable $callback
     */
    public function onHeadersSentBefore(callable $callback)
    {
        $this->headersSentObservers[] = $callback;
    }

    /**
     * Subscribe a callback to be notified upon appending body content
     * 
     * @param callable $callback
     */
    public function onBodyAppend(callable $callback)
    {
        $this->bodyAppendObservers[] = $callback;
    }

    /**
     * Notify registered header lifecycle observers
     */
    protected function doHeadersSentBefore()
    {
        if (!$this->isHeadersSent) {
            $this->isHeadersSent = true;
            foreach ($this->headersSentObservers as $callback) {
                $callback();
            }
        }
    }
    /**
     * Notify registered body lifecycle observers allowing them to modify content 
     * 
     * @param string $content
     */
    protected function doBodyAppend(&$content)
    {
        if (strlen($content) > 0) {
            foreach ($this->bodyAppendObservers as $callback) {
                $callback($content);
            }
        }
    }
}
