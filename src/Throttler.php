<?php

namespace Balambasik\Throttler;

class Throttler
{
    /**
     * @var DriverInterface
     */
    protected $storage;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    public $tag;

    /**
     * @var int
     */
    protected $wait;

    /**
     * @var int
     */
    protected $hitsTTL;

    /**
     * @var bool
     */
    protected $autoHitMode;

    /**
     * Clear old hits chance 1/100, 3/100...
     * @var int[]
     */
    protected $lottery = [1, 100];

    /**
     * @param DriverInterface $storage
     * @param string $id
     * @param string $tag
     * @param int $wait
     * @param int $hitsTTL
     * @param bool $autoHitMode
     */
    public function __construct(
        DriverInterface $storage,
        string          $id,
        string          $tag,
        int             $wait,
        int             $hitsTTL,
        bool            $autoHitMode
    )
    {
        $this->storage     = $storage;
        $this->id          = $id;
        $this->tag         = $tag;
        $this->wait        = $wait;
        $this->hitsTTL     = $hitsTTL;
        $this->autoHitMode = $autoHitMode;
    }

    /**
     * @param int|null $debugNow
     * @return void
     */
    public function hit(int $debugNow = null): void
    {
        $now = $debugNow ?? time();
        $this->storage->setHit($this->id, $this->tag, $now);

        if (rand(0, $this->lottery[1]) <= $this->lottery[0]) {
            $this->storage->clearHitsLessThan($now - $this->hitsTTL, [$this->tag]);
        }
    }

    /**
     * @param \Closure|null $callback
     * @return bool
     */
    public function isLimit(\Closure $callback = null): bool
    {
        $now       = $debugNow ?? time();
        $timestamp = $this->storage->getLastHitTimestamp($this->id, $this->tag);
        $result    = ($timestamp + $this->wait) > $now;

        if ($this->autoHitMode && !$result) {
            $this->hit();
        }

        if ($result && ($callback instanceof \Closure)) {
            $callback();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag;
    }
}