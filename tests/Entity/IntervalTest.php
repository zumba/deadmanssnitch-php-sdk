<?php

use PHPUnit\Framework\TestCase;
use Zumba\Deadmanssnitch\Entity\Interval;

class IntervalTest extends TestCase
{
    public function testConstruction()
    {
        $interval = new Interval(Interval::I_DAILY);
        $this->assertEquals(Interval::I_DAILY, (string)$interval);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidConstruction()
    {
        new Interval('some invalid value');
    }
}
