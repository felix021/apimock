<?php

class Timer
{
    protected $name     = null;
    protected $ts_begin = null;

    public function __construct($name)
    {
        $this->name     = $name;
        $this->ts_begin = microtime(true);
    }

    public function log($extra = '')
    {
        $now = microtime(true);
        Log::info('[timer.elapsed] %s: %.6f %s', $this->name, $now - $this->ts_begin, $extra);
    }

    public function getElapsed()
    {
        return microtime(true) - $this->ts_begin;
    }
}
