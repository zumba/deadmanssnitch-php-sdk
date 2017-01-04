<?php

namespace Zumba\Deadmanssnitch\Test;

use PHPUnit\Framework\TestCase;
use Zumba\Deadmanssnitch\Notifier;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;
use Psr\Log\NullLogger;

class NotifierTest extends TestCase
{
    public function testConstruction()
    {
        $notifier = new Notifier();
        $this->assertInstanceOf(Notifier::class, $notifier);
    }

    public function testConstructionWithProvidedClient()
    {
        $mock = new MockHandler();
        $handler = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $handler]);
        $notifier = new Notifier($guzzle);
        $this->assertInstanceOf(Notifier::class, $notifier);
    }

    public function testPingSnitch()
    {
        $mock = new MockHandler([
            new Response(202, [], 'Thanks for checking in!')
        ]);
        $container = [];
        $history = Middleware::history($container);
        $handler = HandlerStack::create($mock);
        $handler->push($history);
        $guzzle = new Client(['handler' => $handler]);
        $mockLogger = $this->createMock(NullLogger::class, ['error']);
        $mockLogger->expects($this->never())->method('error');
        $notifier = new Notifier($guzzle, $mockLogger);
        $notifier->pingSnitch('12345');
        $this->assertCount(1, $container);
        $this->assertEquals('/12345', (string)$container[0]['request']->getUri());
    }

    public function testPingSnitchWithMessage()
    {
        $mock = new MockHandler([
            new Response(202, [], 'Thanks for checking in!')
        ]);
        $container = [];
        $history = Middleware::history($container);
        $handler = HandlerStack::create($mock);
        $handler->push($history);
        $guzzle = new Client(['handler' => $handler]);
        $notifier = new Notifier($guzzle);
        $notifier->pingSnitch('12345', 'Just checking.');
        $this->assertCount(1, $container);
        $this->assertEquals('/12345?m=Just%20checking.', (string)$container[0]['request']->getUri());
    }

    public function testPingSnitchError()
    {
        $mock = new MockHandler([
            new Response(500)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle = new Client(['handler' => $handler, 'http_errors' => false]);
        $mockLogger = $this->createMock(NullLogger::class, ['error']);
        $mockLogger->expects($this->once())->method('error')
            ->with('Failed to ping snitch.');
        $notifier = new Notifier($guzzle, $mockLogger);
        $notifier->pingSnitch('12345', 'Just checking.');
    }
}
