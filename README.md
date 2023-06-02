# Extended Logger

![ci](https://github.com/exbico/extended-logger/actions/workflows/ci.yml/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/exbico/extended-logger/v/stable)](https://packagist.org/packages/exbico/extended-logger)
[![Total Downloads](https://poser.pugx.org/exbico/extended-logger/downloads)](https://packagist.org/packages/exbico/extended-logger)
[![License](https://poser.pugx.org/exbico/extended-logger/license)](https://packagist.org/packages/exbico/extended-logger)

This package provides a convenient way to log exceptions.

## Installation
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require exbico/extended-logger
```

or add

```
"exbico/extended-logger" : "*"
```

to the `require` section of your application's composer.json file.

## Usage

```php
<?php

use Exbico\ExtendedLogger\Logger;
use Psr\Log\NullLogger;

$exception = new RuntimeException(
    message: 'Exception message',
    previous: new JsonException('Invalid JSON'),
);

// You can use this extended logger in the following way
$logger = new Logger(new NullLogger());
$logger->error(source: $exception, context: ['demo' => true]);

// which is equal to
$logger = new NullLogger();
$logger->error(
    message: 'Exception message',
    context: [
                 'demo'      => true,
                 'exception' => [
                     'class' => 'RuntimeException',
                     'previous' => [
                         'class' => 'JsonException',
                         'message' => 'Invalid JSON',
                         'trace' => 'getTraceAsString() output',
                     ],
                     'trace' => 'getTraceAsString() output',
                 ],
             ],
);
```

### Detailed exceptions

There is also an abstract class [DetailedException](src/DetailedException.php) which allows you to add some additional context to your exceptions.

```php
<?php

use Exbico\ExtendedLogger\DetailedException;
use Exbico\ExtendedLogger\Logger;
use Psr\Log\NullLogger;

final class DemoException extends DetailedException
{
}

$exception = new DemoException(message: 'Demo message', details: ['some' => 'context']);
$logger = new Logger(new NullLogger());
$logger->error(source: $exception, context: ['demo' => true]);

// is equal to
$logger = new NullLogger();
$logger->error(
    message: 'Demo message',
    context: [
                 'demo'      => true,
                 'exception' => [
                     'class' => 'DemoException',
                     'trace' => 'getTraceAsString() output',
                     'some'  => 'context',
                 ],
             ],
);
```