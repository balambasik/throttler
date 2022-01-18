<?php

namespace Balambasik\Throttler\Drivers;

use Balambasik\Throttler\DriverInterface;

class InMemoryDriver extends AbstractDriver implements DriverInterface
{
    /**
     * @var array
     */
    protected $hits = [];

    /**
     * @param int $timestamp
     * @param array $tags
     * @return void
     */
    public function clearHitsLessThan(int $timestamp, array $tags = []): void
    {
        foreach ($this->hits as &$hit) {
            if ($tags) {
                if (in_array($hit["tag"], $tags) && $hit["timestamp"] < $timestamp) {
                    unset($hit);
                }
            } else {
                if ($hit["timestamp"] < $timestamp) {
                    unset($hit);
                }
            }
        }
    }

    /**
     * @param string $identifier
     * @param string $tag
     * @param int $timestamp
     * @return void
     */
    public function setHit(string $identifier, string $tag, int $timestamp): void
    {
        $this->hits[] = [
            "hash"      => $this->getHash($identifier . $tag),
            "timestamp" => $timestamp,
            "tag"       => $tag
        ];
    }

    /**
     * @param string $identifier
     * @param string $tag
     * @return int
     */
    public function getLastHitTimestamp(string $identifier, string $tag): int
    {
        foreach ($this->hits as $hit) {
            if ($hit["hash"] === $this->getHash($identifier . $tag)) {
                return $hit["timestamp"];
            }
        }

        return 0;
    }

    /**
     * @return DriverInterface
     */
    public function clear(): DriverInterface
    {
        $this->hits = [];
        return $this;
    }
}