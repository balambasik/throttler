<?php

use PHPUnit\Framework\TestCase;
use Balambasik\Throttler\Drivers\InMemoryDriver;

class InMemoryDriverTest extends TestCase
{
    public $driver;
    public $id;
    public $tag;
    public $timestamp;

    public function setUp(): void
    {
        $this->driver = new InMemoryDriver();
        $this->id     = "identifier";
        $this->tag    = "tag";
        $this->timestamp  = time();
    }

    public function tearDown(): void
    {
        $this->driver->clear();
    }

    public function testSetGetTimestamp()
    {
        $this->driver->setHit($this->id, $this->tag, $this->timestamp);
        $this->assertTrue($this->driver->getLastHitTimestamp($this->id, $this->tag) === $this->timestamp);
    }

    public function testSetGetAnotherTag()
    {
        $this->driver->setHit($this->id, "another_tag", $this->timestamp);
        $this->assertFalse($this->driver->getLastHitTimestamp($this->id, $this->tag) === $this->timestamp);
        $this->assertTrue($this->driver->getLastHitTimestamp($this->id, "another_tag") === $this->timestamp);
    }

    public function testSetGetAnotherIdentifier()
    {
        $this->driver->setHit("another_identifier", $this->tag, $this->timestamp);
        $this->assertFalse($this->driver->getLastHitTimestamp($this->id, $this->tag) === $this->timestamp);
        $this->assertTrue($this->driver->getLastHitTimestamp("another_identifier", $this->tag) === $this->timestamp);
    }

    public function testSetGetMultiple()
    {
        $items = [
            [
                "identifier" => "id_1",
                "tag"        => "tag_1",
                "timestamp"  => 10
            ], [
                "identifier" => "id_2",
                "tag"        => "tag_2",
                "timestamp"  => 20
            ]
        ];

        foreach ($items as $item) {
            $this->driver->setHit($item["identifier"], $item["tag"], $item["timestamp"]);
        }

        foreach ($items as $item) {
            $result = $this->driver->getLastHitTimestamp($item["identifier"], $item["tag"]);
            $this->assertTrue($result === $item["timestamp"]);
        }

    }

    public function testClear()
    {
        $this->driver->setHit($this->id, $this->tag, $this->timestamp);
        $this->assertTrue($this->driver->getLastHitTimestamp($this->id, $this->tag) === $this->timestamp);
        $this->driver->clear();
        $this->assertTrue($this->driver->getLastHitTimestamp($this->id, $this->tag) === 0);
    }
}