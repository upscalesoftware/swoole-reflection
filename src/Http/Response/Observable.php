<?php
declare(strict_types=1);
/**
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Upscale\Swoole\Reflection\Http\Response;

class Observable extends Proxy
{
    protected bool $isHeadersSent = false;
    
    /**
     * @var callable[]
     */
    protected array $headersSentObservers = [];

    /**
     * @var callable[]
     */
    protected array $bodyAppendObservers = [];

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
    public function sendfile($filename, $offset = 0, $length = 0)
    {
        $this->doHeadersSentBefore();
        return parent::sendfile($filename, $offset, $length);
    }

    /**
     * Subscribe a callback to be notified before sending headers
     */
    public function onHeadersSentBefore(callable $callback)
    {
        $this->headersSentObservers[] = $callback;
    }

    /**
     * Subscribe a callback to be notified upon appending body content
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
     */
    protected function doBodyAppend(string &$content)
    {
        if (strlen($content) > 0) {
            foreach ($this->bodyAppendObservers as $callback) {
                $callback($content);
            }
        }
    }
}
