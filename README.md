# Deadmansnitch API PHP SDK

This library allows for easy access to [Deadmanssnitch](https://deadmanssnitch.com/)'s API.

You can use it to manage snitches through the API or to notify a snitch when a process completes.

## Example Notifier use

```php
<?php

use Zumba\Deadmanssnitch\Notifier;

$notifier = new Notifier();
$notifier->pingSnitch('snitch id', 'just checking in.');
```

## Example API Use

```php
<?php

use Zumba\Deadmanssnitch\Client;
use Zumba\Deadmanssnitch\Snitch;
use Zumba\Deadmanssnitch\Interval;
use Zumba\Deadmanssnitch\ResponseError;

$client = new Client('your api key here');

// creating a snitch
$snitch = new Snitch('My cool process', new Interval(Interval::I_DAILY), [
    'tags' => ['production', 'critical']
]);
try {
    $client->createSnitch($snitch);
} catch (ResponseError $e) {
    // Failed to create the snitch
    echo $e->getMessage();
}

echo $snitch;
// 12312412
```

## Installation

* With Composer

```bash
composer require zumba/deadmanssnitch-sdk
composer update
```

## Supported APIs

The SDK currently supports `v1` of DMS's API.

* [Creating a snitch](https://deadmanssnitch.com/docs/api/v1#creating-a-snitch) - `Client::createSnitch(Snitch $snitch): void`
* Listing snitches (Not implemented yet)
* Examining snitches (Not implemented yet)
* Editing a snitch (Not implemented yet)
* Pausing a snitch (Not implemented yet)
* Deleting a snitch (Not implemented yet)

Note, we will never support attaining an API key with username/password.

## Advanced usage

You can provide your own http client and logger to use provided they satisfy the
`GuzzleHttp\ClientInterface` and `Psr\Log\LoggerInterface` respectively:

```php
<?php

use Zumba\Deadmanssnitch\Client;
use GuzzleHttp\Client as GuzzleClient;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$http = new GuzzleClient([
    'base_uri' => static::HOST,
    'auth' => ['my api key', '']
]);

$logger = new Logger('name');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));

$client = new Client('my api key', $http, $logger);
```

However, please note that guzzle clients are immutable, so you will be responsible
for setting the base URI and auth parameters.
