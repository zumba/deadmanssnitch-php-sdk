<?php

namespace Zumba\Deadmanssnitch\Test\Entity;

use PHPUnit\Framework\TestCase;
use Zumba\Deadmanssnitch\Entity\Snitch;
use Zumba\Deadmanssnitch\Entity\Interval;

class SnitchTest extends TestCase
{
    public function testConstructionToCreate()
    {
        $snitch = new Snitch('My process', new Interval(Interval::I_DAILY));
        $this->assertTrue($snitch->isNew());
    }

    public function testConstructionFromRead()
    {
        $snitch = new Snitch(
            'My process',
            new Interval(Interval::I_DAILY),
            [
                'token' => '12345',
                'created_at' => '2017-01-01',
                'tags' => 'sometag'
            ]
        );
        $this->assertFalse($snitch->isNew());
        $this->assertInstanceOf(Interval::class, $snitch->interval);
        $this->assertInstanceOf(\DateTime::class, $snitch->created_at);
        $this->assertCount(1, $snitch->tags);
    }

    public function testBlockTokenAfterCreate()
    {
        $snitch = new Snitch(
            'My process',
            new Interval(Interval::I_DAILY),
            [
                'token' => '12345'
            ]
        );
        $snitch->token = '23456';
        $this->assertEquals('12345', $snitch->token);
    }

    public function testBlockTokenOnSecondWrite()
    {
        $snitch = new Snitch('My process', new Interval(Interval::I_DAILY));
        $snitch->token = '12345';
        $snitch->token = '23456';
        $this->assertEquals('12345', $snitch->token);
    }

    public function testJsonBody()
    {
        $snitch = new Snitch(
            'My process',
            new Interval(Interval::I_DAILY),
            [
                'token' => '12345',
                'created_at' => '2017-01-01',
                'notes' => 'Some note.',
                'alert_type' => 'basic',
                'tags' => ['a', 'b']
            ]
        );
        $this->assertEquals(json_encode([
            'interval' => 'daily',
            'name' => 'My process',
            'notes' => 'Some note.',
            'alert_type' => 'basic',
            'tags' => ['a', 'b']
        ]), json_encode($snitch));
    }
}
