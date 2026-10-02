<?php

class Timer
{
    private float $startTime;
    private float $endTime;

    public function start(): void
    {
        $this->startTime = microtime(true);
    }

    public function stop(): void
    {
        $this->endTime = microtime(true);
    }

    public function getElapsedTime(): float
    {
        return $this->endTime - $this->startTime;
    }
}