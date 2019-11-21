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
     * {@inheritdoc}
     */
    protected function proxy(\Swoole\Http\Response $response)
    {
        return new Observable($response);
    }
}