<?php

use PHPUnit\Framework\TestCase;
use Balambasik\Throttler\Drivers\InMemoryDriver;
use Balambasik\Throttler\ThrottlerFactory;

class ThrottlerTest extends TestCase
{
    public $factory;
    public $id   = "string_id";
    public $tag  = "string_tag";
    public $tag2 = "string_tag2";

    public function setUp(): void
    {
        $this->factory = new ThrottlerFactory(new InMemoryDriver());
    }

    public function testLimit()
    {
        $throttler = $this->factory->id($this->id)->waitSeconds(1)->create();
        $this->assertFalse($throttler->isLimit());
        $this->assertTrue($throttler->isLimit());
        sleep(1);
        $this->assertFalse($throttler->isLimit());
    }

    public function testLimit2Seconds()
    {
        $throttler = $this->factory->id($this->id)->waitSeconds(2)->create();

        $this->assertFalse($throttler->isLimit());
        sleep(1);
        $this->assertTrue($throttler->isLimit());
        sleep(1);
        $this->assertFalse($throttler->isLimit());
    }

    public function testTags()
    {
        $t1 = $this->factory->id($this->id)->tag($this->tag)->waitSeconds(1)->create();
        $t2 = $this->factory->id($this->id)->tag($this->tag2)->waitSeconds(1)->create();

        $this->assertTrue($t1->getTag() == $this->tag);
        $this->assertTrue($t2->getTag() == $this->tag2);
    }

    public function testDisableAutoTick()
    {
        $t1 = $this->factory->id($this->id)->waitSeconds(1)->createManualMode();
        $this->assertFalse($t1->isLimit());
        sleep(1);
        $this->assertFalse($t1->isLimit());
        $t1->hit();
        $this->assertTrue($t1->isLimit());
    }

    public function testDisableAutoTick2()
    {
        $t1 = $this->factory->id($this->id)->waitSeconds(1)->createManualMode();
        $this->assertFalse($t1->isLimit());
        $this->assertFalse($t1->isLimit());
        $t1->hit();
        $this->assertTrue($t1->isLimit());
    }

    public function testCallback()
    {
        $called = false;

        $t1 = $this->factory->id($this->id)->waitSeconds(1)->create();
        $this->assertFalse($t1->isLimit());

        $t1->isLimit(function () use (&$called) {
            $called = true;
        });

        $this->assertTrue($called);
    }

    public function testLimitMultipleInstance()
    {
        $t1 = $this->factory->id($this->id)->waitSeconds(1)->tag($this->tag)->create();
        $t2 = $this->factory->id($this->id)->waitSeconds(2)->tag($this->tag2)->create();

        $this->assertFalse($t1->isLimit());
        $this->assertFalse($t2->isLimit());

        sleep(1);

        $this->assertFalse($t1->isLimit());
        $this->assertTrue($t2->isLimit());
    }

    public function testLimitMultipleInstanceWithoutTags()
    {
        $t1 = $this->factory->id($this->id)->waitSeconds(1)->create();
        $t2 = $this->factory->id($this->id)->waitSeconds(2)->create();

        $this->assertFalse($t1->isLimit());
        $this->assertTrue($t2->isLimit());
    }
}