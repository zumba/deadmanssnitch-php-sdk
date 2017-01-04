<?php

namespace Zumba\Deadmanssnitch;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client as GuzzleClient;
use Zumba\Deadmanssnitch\Entity\Snitch;
use Zumba\Deadmanssnitch\Entity\Interval;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Psr\Http\Message\ResponseInterface;

class Notifier implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const HOST = 'https://nosnch.in';

    /**
     * Request client instance.
     *
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * Constructor.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ClientInterface $client = null,
        LoggerInterface $logger = null
    ) {
        $this->client = $client ?: new GuzzleClient([
            'base_uri' => static::HOST,
            'http_errors' => false
        ]);
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Ping a snitch
     *
     * @param string $token Snitch token to ping
     * @param string $message Optional message to provide.
     * @return void
     */
    public function pingSnitch($token, $message = '')
    {
        $this->logger->debug('Pinging snitch.', compact('token'));
        $options = empty($message) ? [] : [
            'query' => [
                'm' => $message
            ]
        ];
        $response = $this->client->request('GET', "/$token", $options);
        if ($response->getStatusCode() !== 202) {
            $this->logger->error('Failed to ping snitch.');
        }
    }
}
