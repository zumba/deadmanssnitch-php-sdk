<?php

namespace Zumba\Deadmanssnitch\Test;

use PHPUnit\Framework\TestCase;
use Zumba\Deadmanssnitch\Client;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;
use Zumba\Deadmanssnitch\Entity\Snitch;
use Zumba\Deadmanssnitch\Entity\Interval;
use Zumba\Deadmanssnitch\ResponseError;

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
        $container = [];
        $guzzle = $this->getHistoryMockedHttpClient($mock, $container);
        $client = new Client('1234', $guzzle);
        $client->createSnitch($snitch);
        $this->assertEquals('anewtoken', (string)$snitch);
        $this->assertCount(1, $container);
        $this->assertEquals('POST', $container[0]['request']->getMethod());
        $this->assertEquals('/v1/snitches', $container[0]['request']->getUri());
        $expectedBody = [
            'name' => 'Some name',
            'interval' => Interval::I_DAILY
        ];
        $this->assertEquals($expectedBody, json_decode($container[0]['request']->getBody(), true));
    }

    public function testEditSnitch()
    {
        $snitch = new Snitch('Some name', new Interval(Interval::I_DAILY), [
            'token' => '23456',
            'tags' => ['some tag']
        ]);
        $snitch->interval = new Interval(Interval::I_WEEKLY);
        $snitch->notes = 'Some cool notes.';
        $container = [];
        $guzzle = $this->getHistoryMockedHttpClient(new MockHandler([
            new Response(200)
        ]), $container);
        $client = new Client('1234', $guzzle);
        $client->editSnitch($snitch);
        $this->assertCount(1, $container);
        $this->assertEquals('PATCH', $container[0]['request']->getMethod());
        $this->assertEquals('/v1/snitches/23456', $container[0]['request']->getUri());
        $expectedBody = [
            'interval' => Interval::I_WEEKLY,
            'notes' => 'Some cool notes.'
        ];
        $this->assertEquals($expectedBody, json_decode($container[0]['request']->getBody(), true));
    }

    public function testAppendTags()
    {
        $container = [];
        $guzzle = $this->getHistoryMockedHttpClient(new MockHandler([
            new Response(200, [], json_encode(['some tag', 'new tag']))
        ]), $container);
        $client = new Client('1234', $guzzle);
        $client->appendTags('23456', ['new tag']);
        $this->assertCount(1, $container);
        $this->assertEquals('POST', $container[0]['request']->getMethod());
        $this->assertEquals('/v1/snitches/23456/tags', $container[0]['request']->getUri());
        $this->assertEquals(['new tag'], json_decode($container[0]['request']->getBody(), true));
    }

    public function testDeleteTag()
    {
        $container = [];
        $guzzle = $this->getHistoryMockedHttpClient(new MockHandler([
            new Response(200, [], json_encode(['some tag']))
        ]), $container);
        $client = new Client('1234', $guzzle);
        $client->removeTag('23456', 'new tag');
        $this->assertCount(1, $container);
        $this->assertEquals('DELETE', $container[0]['request']->getMethod());
        $this->assertEquals('/v1/snitches/23456/tags/new%20tag', (string)$container[0]['request']->getUri());
    }

    public function testPauseSnitch()
    {
        $container = [];
        $guzzle = $this->getHistoryMockedHttpClient(new MockHandler([
            new Response(204)
        ]), $container);
        $client = new Client('1234', $guzzle);
        $client->pauseSnitch('23456');
        $this->assertCount(1, $container);
        $this->assertEquals('POST', $container[0]['request']->getMethod());
        $this->assertEquals('/v1/snitches/23456/pause', (string)$container[0]['request']->getUri());
    }

    public function testDeleteSnitch()
    {
        $container = [];
        $guzzle = $this->getHistoryMockedHttpClient(new MockHandler([
            new Response(204)
        ]), $container);
        $client = new Client('1234', $guzzle);
        $client->deleteSnitch('23456');
        $this->assertCount(1, $container);
        $this->assertEquals('DELETE', $container[0]['request']->getMethod());
        $this->assertEquals('/v1/snitches/23456', (string)$container[0]['request']->getUri());
    }

    public function testCreateSnitchError()
    {
        $this->expectException(ResponseError::class);
        $this->expectExceptionCode(400);
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

    private function getHistoryMockedHttpClient(MockHandler $mock, array &$container)
    {
        $handler = HandlerStack::create($mock);
        $history = Middleware::history($container);
        $handler->push($history);
        return new GuzzleClient(['handler' => $handler]);
    }
}
