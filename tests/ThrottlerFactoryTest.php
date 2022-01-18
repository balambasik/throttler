<?php

use PHPUnit\Framework\TestCase;
use Balambasik\Throttler\Throttler;
use Balambasik\Throttler\Drivers\InMemoryDriver;
use Balambasik\Throttler\ThrottlerFactory;

class ThrottlerFactoryTest extends TestCase
{
    public function testCreateThrottler()
    {
        $factory   = new ThrottlerFactory(new InMemoryDriver());
        $throttler = $factory->id("id")->waitSeconds(1)->create();
        $this->assertInstanceOf(Throttler::class, $throttler);
    }
}