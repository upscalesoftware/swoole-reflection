Swoole Reflection API
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

Retrieve server middleware callback retroactively:
```php
namespace Example\Swoole;

require 'vendor/autoload.php';

class Middleware
{
    public function __invoke($request, $response)
    {
        $response->header('Content-Type', 'text/plain');
        $response->end("Served by Swoole server\n");
    }
}

$server = new \Swoole\Http\Server('127.0.0.1', 8080);
$server->on('request', new Middleware());

$reflection = new \Upscale\Swoole\Reflection\Http\Server($server);
$middleware = $reflection->getMiddleware();
echo get_class($middleware);  // Outputs Example\Swoole\Middleware

$server->start();
```

### Response Lifecycle

#### Headers Tracking

Modify response headers before they have been sent:
```php
$server->on('request', function ($request, $response) {
    $response = new \Upscale\Swoole\Reflection\Http\Response\Observable($response);
    $response->onHeadersSentBefore(function () use ($request, $response) {
        $response->header('Content-Type', 'text/plain');
    });    
    $response->end("Served by Swoole server\n");
});
```

Callback is invoked once per request upon the first call to `\Swoole\Http\Response::write/end/sendfile()` methods.

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

Licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0).