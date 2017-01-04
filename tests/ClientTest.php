<?php

use PHPUnit\Framework\TestCase;
use Zumba\Deadmanssnitch\Client;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Zumba\Deadmanssnitch\Entity\Snitch;
use Zumba\Deadmanssnitch\Entity\Interval;

class ClientTest extends TestCase
{
    public function testConstruction()
    {
        $client = new Client('1234');
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testConstructionWithProvidedClient()
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar']),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle = new GuzzleClient(['handler' => $handler]);
        $client = new Client('1234', $guzzle);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testCreateSnitch()
    {
        $snitch = new Snitch('Some name', new Interval(Interval::I_DAILY));
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'token' => 'anewtoken',
                'href' => 'v1/snitches/anewtoken',
                'status' => 'pending'
            ]))
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle = new GuzzleClient(['handler' => $handler]);
        $client = new Client('1234', $guzzle);
        $client->createSnitch($snitch);
        $this->assertEquals('anewtoken', (string)$snitch);
    }

    /**
     * @expectedException \Zumba\Deadmanssnitch\ResponseError
     * @expectedExceptionMessage error_type: Some error
     * @expectedExceptionCode 400
     */
    public function testCreateSnitchError()
    {
        $snitch = new Snitch('Some name', new Interval(Interval::I_DAILY));
        $mock = new MockHandler([
            new Response(400, [], json_encode([
                'error' => 'Some error',
                'type' => 'error_type'
            ]))
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle = new GuzzleClient(['handler' => $handler, 'http_errors' => false]);
        $client = new Client('1234', $guzzle);
        $client->createSnitch($snitch);
    }
}
