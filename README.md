Swoole Reflection API [![Build Status](https://github.com/upscalesoftware/swoole-reflection/workflows/Tests/badge.svg?branch=master)](https://github.com/upscalesoftware/swoole-reflection/actions?query=workflow%3ATests+branch%3Amaster)
=====================

This library provides a [Reflection API](http://us3.php.net/manual/en/intro.reflection.php) for classes of [Swoole](https://www.swoole.co.uk/) web-server.

**Features:**
- Server middleware manipulation
- Response lifecycle tracking

## Installation

The library is to be installed via [Composer](https://getcomposer.org/) as a dependency:
```bash
composer require upscale/swoole-reflection
```
## Usage

### Middleware Manipulation

Override server middleware optionally reusing the original callback:
```php
$server = new \Swoole\Http\Server('127.0.0.1', 8080);
$server->on('request', function ($request, $response) {
   $response->end("Served by Swoole server\n");
});

$reflection = new \Upscale\Swoole\Reflection\Http\Server($server);
$middleware = $reflection->getMiddleware();
$reflection->setMiddleware(function ($request, $response) use ($middleware) {
   $response->header('Content-Type', 'text/plain');
   $middleware($request, $response);
});

$server->start();
```

### Response Lifecycle

#### Headers Tracking

Modify response headers before they have been sent:
```php
$server->on('request', function ($request, $response) {
    $callback = function () use ($request, $response) {
        $response->header('Content-Type', 'text/plain');
    };
    $response = new \Upscale\Swoole\Reflection\Http\Response\Observable($response);
    $response->onHeadersSentBefore($callback);    
    $response->end("Served by Swoole server\n");
});
```

Callback is invoked once per request upon the first call to `\Swoole\Http\Response::write/end/sendfile()` methods.

**Warning!** Callbacks that need to modify the response must use the original response rather than its observable proxy.
Dependency on observable proxy creates circular reference between the observable and callbacks registered within it.
Instances involved in orphan circular cross-references will not be destroyed until the next garbage collection takes place.
Swoole sends out response by calling `\Swoole\Http\Response::end()` in the destructor upon the request completion.
The response destructor will not be called causing the the worker to hang up without sending the response. 

#### Body Interception

Modify response body before sending it out:
```php
$server->on('request', function ($request, $response) use ($server) {
    $response = new \Upscale\Swoole\Reflection\Http\Response\Observable($response);
    $response->onBodyAppend(function (&$content) use ($server) {
        $content .= "<!-- Served by worker {$server->worker_id} -->\n";
    });
    $response->header('Content-Type', 'text/html');
    $response->end("Served by <b>Swoole server</b>\n");
});
```

Callback is invoked on every call to `\Swoole\Http\Response::write/end()` methods with non-empty content.

## Contributing

Pull Requests with fixes and improvements are welcome!

## License

Copyright © Upscale Software. All rights reserved.

Licensed under the [Apache License, Version 2.0](https://github.com/upscalesoftware/swoole-reflection/blob/master/LICENSE.txt).