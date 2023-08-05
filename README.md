Loguzz
[![codecov](https://codecov.io/gh/ssi-anik/loguzz/branch/master/graph/badge.svg?token=L35HVDZ91V)](https://codecov.io/gh/ssi-anik/loguzz)
[![Total Downloads](https://poser.pugx.org/anik/loguzz/downloads)](//packagist.org/packages/anik/loguzz)
[![Latest Stable Version](https://poser.pugx.org/anik/loguzz/v)](//packagist.org/packages/anik/loguzz)
==============

Loguzz is a middleware for [Guzzle](https://github.com/guzzle/guzzle) which logs requests and responses.

## Installation

You'll need composer to install the package.
`composer require anik/loguzz`

## Documentation V1

Find the thorough [documentation here](https://bit.ly/3dwgYB1).

## Documentation V2

To log a request, you'll need to push `Loguzz\Middleware\LogMiddleware` to Guzzle's handler.

```php
$logger = new \ColinODell\PsrTestLogger\TestLogger();
$handlerStack = \GuzzleHttp\HandlerStack::create();
$options = [];
$handlerStack->push(new \Loguzz\Middleware\LogMiddleware($logger, $options), 'logger');
```

- `$logger` is the implementation of `Psr\Log\LoggerInterface`.
- `$options` is an array to change the default behaviour of LogMiddleware.
- `'logger'` is the internal name of the middleware for Guzzle. It can be any name.

### Options

```php
// Default values
$options = [
    'length' => 100,
    'log_request' => true,
    'log_response' => true,
    'success_only' => false,
    'exceptions_only' => false,
    'log_level' => 'debug',
    'request_formatter' => new \Loguzz\Formatter\RequestCurlFormatter(),
    'response_formatter' => new \Loguzz\Formatter\ResponseJsonFormatter(),
    'exception_formatter' => new \Loguzz\Formatter\ExceptionJsonFormatter(),
    'tag' => '',
    'force_json' => true,
    'separate' => false,
];
```

- `length` - **int**. Minimum 10. To set the length of when formatting request
  with `\Loguzz\Formatter\RequestCurlFormatter`.
- `log_request` - **bool**. To enable or disable request logging.
- `log_response` - **bool**. To enable or disable response logging.
- `success_only` - **bool**. Only log successful responses. **If the server could be reached, it's a success.**
- `exception_only` - **bool** Only log exceptions. **Logs when an exception is thrown by Guzzle for connection/timeout
  related exceptions**.
- `log_level` - **string**. Any valid log level.
- `request_formatter` - instance of **\Loguzz\Formatter\AbstractRequestFormatter**. Available
    * `\Loguzz\Formatter\RequestArrayFormatter`
    * `\Loguzz\Formatter\RequestCurlFormatter`
    * `\Loguzz\Formatter\RequestJsonFormatter`
- `response_formatter` - instance of **\Loguzz\Formatter\AbstractResponseFormatter**
    * `\Loguzz\Formatter\ResponseArrayFormatter`
    * `\Loguzz\Formatter\ResponseJsonFormatter`
- `exception_formatter` - instance of **\Loguzz\Formatter\AbstractResponseFormatter**
    * `\Loguzz\Formatter\ExceptionArrayFormatter`
    * `\Loguzz\Formatter\ExceptionJsonFormatter`
- `tag` - **string**. **Empty** by default. When non-empty string, it'll log the formatted data under this tag. Tag can
  be used to search for specific type of request/response in your log file or your storage.
- `force_json` - **bool**. **true** by default. It is only applicable when **tag** is non-empty string. If enabled, it
  will then log data as
  json string, otherwise it'll log as an array. **If set to** `false`, **the code may break due to the type-hint in
  psr/log interface. If
  your [logger interface supports array](https://github.com/laravel/framework/blob/dd5c5178274e64d0384dc30bf2c8139b00dba098/src/Illuminate/Log/Logger.php#L260),
  it will work.**
- `separate` - **bool**. It is only applicable when **tag** is non-empty string. If enabled, it will then log data
  in `{tag}.request`, `{tag}.success`, `{tag}.failure` for request logging, successful response and error response.

### Request Formatter

To create a new request formatter you need to **extend** the `\Loguzz\Formatter\AbstractRequestFormatter` class.

### Response Formatter

To create a new response formatter you need to **extend** the `\Loguzz\Formatter\AbstractResponseFormatter` class.

### Exception Formatter

To create a new exception formatter you need to **extend** the `\Loguzz\Formatter\AbstractExceptionFormatter` class.

## Manual Request formatting

Implementations of `\Loguzz\Formatter\AbstractRequestFormatter::format` accept parameters as

- `\Psr\Http\Message\RequestInterface $request`
- `array $options = []`

Available request formatters parse data from `$request` and **cookies** from the `$options`. The values
in `$options['cookies']` must be an implementation of `\GuzzleHttp\Cookie\CookieJarInterface`. To parse **cookies**, the
request URL must contain the **domain**.

## Manual Response formatting

Implementations of `\Loguzz\Formatter\AbstractResponseFormatter::format` accept parameters as

- `\Psr\Http\Message\RequestInterface $request`
- `\Psr\Http\Message\ResponseInterface $response`
- `array $options = []`

Available response formatters parse data from `$response` and **cookies** from the `set-cookie` header. To parse
**cookies**, the request URL must contain the **domain**.

The `set-cookie` headers will not be available in the headers for available response formatters.

## Issues & PRs

If you think something is missing in the package or cause bug, please report an Issue. If you're available and want to
contribute to the repository, please submit a PR.
