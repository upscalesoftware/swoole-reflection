Swoole Reflection API
=====================

This library provides a [Reflection API](http://us3.php.net/manual/en/intro.reflection.php) for classes of [Swoole](https://www.swoole.co.uk/) web-server.

**Features:**
- Retrieve server middleware callback
- Retrieve server primary port instance

## Installation

The library is to be installed via [Composer](https://getcomposer.org/) as a dependency:
```bash
composer require upscale/swoole-reflection
```
## Usage

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

## Contributing

Pull Requests with fixes and improvements are welcome!

## License

Copyright © Upscale Software. All rights reserved.

Licensed under the [Apache License, Version 2.0](http://www.apache.org/licenses/LICENSE-2.0).