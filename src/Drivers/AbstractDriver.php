<?php

namespace Balambasik\Throttler\Drivers;

abstract class AbstractDriver
{
    /**
     * @param string $string
     * @return string
     */
    public function getHash(string $string): string
    {
        return substr(md5($string), 0, 10);
    }
}