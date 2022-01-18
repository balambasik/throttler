<?php

namespace Balambasik\Throttler;

interface DriverInterface
{
    /**
     * @param string $identifier
     * @param string $tag
     * @param int $timestamp
     * @return void
     */
    public function setHit(string $identifier, string $tag, int $timestamp): void;

    /**
     * @param int $timestamp
     * @param array $tags
     * @return void
     */
    public function clearHitsLessThan(int $timestamp, array $tags = []): void;

    /**
     * @param string $identifier
     * @param string $tag
     * @return int
     */
    public function getLastHitTimestamp(string $identifier, string $tag): int;

    /**
     * @return DriverInterface
     */
    public function clear(): DriverInterface;
}