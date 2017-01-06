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

```bash
composer require zumba/deadmanssnitch-sdk
```

## Supported APIs

Supports pinging the `nosnch.in` domain for a specific snitch. See `Notifier::pingSnitch(string $token, string $message = ''): void`.

The SDK currently supports `v1` of DMS's API.

* [Creating a snitch](https://deadmanssnitch.com/docs/api/v1#creating-a-snitch) - `Client::createSnitch(Snitch $snitch): void`
* [Listing snitches](https://deadmanssnitch.com/docs/api/v1#listing-your-snitches) - `Client::listSnitches(array $tags = []): []Snitch`
  * Includes ability to filter by tags
* [Examining snitches](https://deadmanssnitch.com/docs/api/v1#examining-a-snitch) - `Client::examineSnitch(string $token): Snitch`
* [Editing a snitch](https://deadmanssnitch.com/docs/api/v1#editing-a-snitch) - `Client::editSnitch(Snitch $snitch): void`
  * Also supports appending tags and removing a single tag (per the API).
  * Setting tags on a snitch and using edit will replace the tags with what is provided.
* [Pausing a snitch](https://deadmanssnitch.com/docs/api/v1#pausing-a-snitch) - `Client::pauseSnitch(string $token): void`
* [Deleting a snitch](https://deadmanssnitch.com/docs/api/v1#deleting-a-snitch) - `Client::removeSnitch(string $token): void`

Note, we will not support attaining an API key with username/password.

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
    'base_uri' => Client::HOST,
    'auth' => ['my api key', '']
]);

$logger = new Logger('name');
$logger->pushHandler(new StreamHandler('path/to/your.log', Logger::WARNING));

$client = new Client('my api key', $http, $logger);
```

However, please note that guzzle clients are immutable, so you will be responsible
for setting the base URI and auth parameters. Internally, `http_errors` is disabled
in order to wrap and use our own exceptions. If you do not disable this, you will
need to catch Guzzle exceptions instead of `Zumba\Deadmanssnitch\ResponseError`.
