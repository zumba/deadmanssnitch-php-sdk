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
     * Get a list of all snitches.
     *
     * @param array $tags Optional filter by tags.
     * @return array []\Zumba\Deadmanssnitch\Entity\Snitch
     * @throws \Zumba\Deadmanssnitch\ResponseError
     */
    public function listSnitches(array $tags = [])
    {
        $response = $this->client->request('GET', '/v1/snitches', [
            'query' => !empty($tags) ? compact('tags') : []
        ]);
        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Unable to list snitches.', compact('tags'));
            $this->handleError($response);
        }
        $snitches = [];
        foreach (json_decode($response->getBody(), true) as $entry) {
            $snitches[] = new Snitch($entry['name'], new Interval($entry['interval']), $entry);
        }
        return $snitches;
    }

    /**
     * Get a detailed view of a specific snitch by token.
     *
     * @param string $token
     * @return \Zumba\Deadmanssnitch\Entity\Snitch
     */
    public function examineSnitch($token)
    {
        $response = $this->client->request('GET', "/v1/snitches/$token");
        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Unable to find snitch.', compact('token'));
            $this->handleError($response);
        }
        $snitch = json_decode($response->getBody(), true);
        return new Snitch($entry['name'], new Interval($entry['interval']), $entry);
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
        $snitchBody = $snitch->toArray();
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
        $snitch->token = $body['token'];
    }

    /**
     * Edit a snitch.
     *
     * Example usage:
     *   $snitch = $client->examineSnitch($token);
     *   $snitch->notes = 'Some informative notes.';
     *   $client->editSnitch($snitch);
     *
     * Please note, that this will overwrite any value you modify.
     * If you want to append/remove tags, use Client::appendTags()/removeTag() respective.
     *
     * If the snitch has not been previously created (ie doesn't have a token),
     * it will be created.
     *
     * @param \Zumba\Deadmanssnitch\Entity\Snitch
     * @return void
     * @throws \Zumba\Deadmanssnitch\ResponseError
     */
    public function editSnitch(Snitch $snitch)
    {
        if ($snitch->isNew()) {
            $this->createSnitch($snitch);
            return;
        }
        $candidateValues = $snitch->extract($snitch->getVisible(), true);
        if (isset($candidateValues['interval'])) {
            $candidateValues['interval'] = (string)$candidateValues['interval'];
        }
        $response = $this->client->request('PATCH', "/v1/snitches/$snitch", [
            'json' => $candidateValues
        ]);
        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Unable to edit snitch.', compact('candidateValues'));
            $this->handleError($response);
        }
    }

    /**
     * Append tags to a snitch.
     *
     * @param string $token
     * @param array $tags
     * @return void
     * @throws \Zumba\Deadmanssnitch\ResponseError
     */
    public function appendTags($token, array $tags)
    {
        $response = $this->client->request('POST', "/v1/snitches/$token/tags", [
            'json' => $tags
        ]);
        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Unable to append tags to snitch.', compact('tags'));
            $this->handleError($response);
        }
    }

    /**
     * Remove a tag from a snitch.
     *
     * @param string $token
     * @param string $tag
     * @return void
     * @throws \Zumba\Deadmanssnitch\ResponseError
     */
    public function removeTag($token, $tag)
    {
        $response = $this->client->request('DELETE', "/v1/snitches/$token/tags/$tag");
        if ($response->getStatusCode() !== 200) {
            $this->logger->error('Unable to append tags to snitch.', compact('tags'));
            $this->handleError($response);
        }
    }

    /**
     * Pause snitch.
     *
     * @param string $token
     * @return void
     * @throws \Zumba\Deadmanssnitch\ResponseError
     */
    public function pauseSnitch($token)
    {
        $response = $this->client->request('POST', "/v1/snitches/$token/pause");
        if ($response->getStatusCode() !== 204) {
            $this->logger->error('Unable to pause snitch.', compact('token'));
            $this->handleError($response);
        }
    }

    /**
     * Delete a snitch.
     *
     * @param string $token
     * @return void
     * @throws \Zumba\Deadmanssnitch\ResponseError
     */
    public function deleteSnitch($token)
    {
        $response = $this->client->request('DELETE', "/v1/snitches/$token");
        if ($response->getStatusCode() !== 204) {
            $this->logger->error('Unable to delete snitch.', compact('token'));
            $this->handleError($response);
        }
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
