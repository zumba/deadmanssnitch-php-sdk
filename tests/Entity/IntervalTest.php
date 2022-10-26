<?php

namespace Zumba\Deadmanssnitch\Test\Entity;

use PHPUnit\Framework\TestCase;
use Zumba\Deadmanssnitch\Entity\Interval;

class IntervalTest extends TestCase
{
    public function testConstruction()
    {
        $interval = new Interval(Interval::I_DAILY);
        $this->assertEquals(Interval::I_DAILY, (string)$interval);
    }

    public function testInvalidConstruction()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Interval('some invalid value');
    }
}
