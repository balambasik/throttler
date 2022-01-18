<?php

namespace Balambasik\Throttler;

class ThrottlerFactory
{
    const AUTO_HIT_MODE = true;
    const MANUAL_HIT_MODE = false;

    /**
     * @var DriverInterface
     */
    protected $storage;

    /**
     * @var string
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $tag = null;

    /**
     * @var int
     */
    protected $wait = null;

    /**
     * @var int
     */
    protected $hitsTTL = 86400;

    /**
     * @param DriverInterface $storage
     */
    public function __construct(DriverInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param DriverInterface $storage
     * @return $this
     */
    public function driver(DriverInterface $storage): ThrottlerFactory
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @param string $tag
     * @return $this
     */
    public function tag(string $tag): ThrottlerFactory
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function id(string $id): ThrottlerFactory
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param int $wait
     * @return $this
     */
    public function waitSeconds(int $wait): ThrottlerFactory
    {
        $this->wait = $wait;
        return $this;
    }

    /**
     * @param int $wait
     * @return $this
     */
    public function waitMinutes(int $wait): ThrottlerFactory
    {
        $this->wait = $wait * 60;
        return $this;
    }

    /**
     * @param int $wait
     * @return $this
     */
    public function waitHours(int $wait): ThrottlerFactory
    {
        $this->wait = $wait * 3600;
        return $this;
    }

    /**
     * @param int $hitsTTL
     * @return $this
     */
    public function setHitsTTL(int $hitsTTL): ThrottlerFactory
    {
        $this->hitsTTL = $hitsTTL;
        return $this;
    }

    /**
     * @return int
     */
    public function getHitsTTL(): int
    {
        return $this->hitsTTL;
    }

    /**
     * @return Throttler
     * @throws ThrottlerException
     */
    public function create(): Throttler
    {
        return $this->createInstance(ThrottlerFactory::AUTO_HIT_MODE);
    }

    /**
     * @return Throttler
     * @throws ThrottlerException
     */
    public function createManualMode(): Throttler
    {
        return $this->createInstance(ThrottlerFactory::MANUAL_HIT_MODE);
    }

    /**
     * @param bool $autoHit
     * @return Throttler
     * @throws ThrottlerException
     */
    protected function createInstance(bool $autoHit): Throttler
    {
        if (!$this->wait) {
            throw new ThrottlerException('Not setted: "wait", use waitSeconds() or wait*() methods');
        }

        if (!$this->id) {
            throw new ThrottlerException('Not setted: "id", use id() method');
        }

        $instance = new Throttler(
            $this->storage,
            $this->id,
            $this->tag ?? "default_tag",
            $this->wait,
            $this->hitsTTL,
            $autoHit
        );

        $this->reset();

        return $instance;
    }

    /**
     * @return void
     */
    protected function reset(): void
    {
        $this->tag  = null;
        $this->id   = null;
        $this->wait = null;
    }
}