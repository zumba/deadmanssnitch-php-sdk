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

class Client implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const HOST = 'https://api.deadmanssnitch.com';

    /**
     * API Key for this client.
     *
     * @var string
     */
    private $apiKey;

    /**
     * Request client instance.
     *
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * Constructor.
     *
     * @param string $apiKey
     * @param \GuzzleHttp\ClientInterface $client
     * @param \Psr\Log\LoggerInterface $logger
     * @throws \RuntimeException
     */
    public function __construct(
        $apiKey,
        ClientInterface $client = null,
        LoggerInterface $logger = null
    ) {
        if (empty($apiKey)) {
            throw new \RuntimeException('API Key required.');
        }
        $this->apiKey = $apiKey;
        $this->client = $client ?: new GuzzleClient([
            'base_uri' => static::HOST,
            'http_errors' => false,
            'auth' => [$this->apiKey, '']
        ]);
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Create a snitch.
     *
     * @param \Zumba\Deadmanssnitch\Entity\Snitch $snitch
     * @return void
     * @throws \Zumba\Deadmanssnitch\ResponseError
     */
    public function createSnitch(Snitch $snitch)
    {
        $snitchBody = $snitch->data();
        $this->logger->debug('Creating snitch.', $snitchBody);
        $response = $this->client->request('POST', '/v1/snitches', [
            'json' => $snitchBody
        ]);
        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Unable to create snitch.', [
                'snitch' => $snitchBody
            ]);
            $this->handleError($response);
        }
        $body = json_decode($response->getBody(), true);
        $snitch->setToken($body['token']);
        $snitch->setHref($body['href']);
        $snitch->setStatus($body['status']);
    }

    /**
     * Handle API error responses.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return void
     * @throws \Zumba\Deadmanssnitch\ResponseError
     */
    private function handleError(ResponseInterface $response)
    {
        $body = json_decode($response->getBody(), true);
        $errorType = isset($body['type']) ? $body['type'] : $response->getStatusCode();
        $e = new ResponseError(
            sprintf('%s: %s', $errorType, $body['error']),
            $response->getStatusCode()
        );
        $this->logger->error($e->getMessage());
        throw $e;
    }
}
