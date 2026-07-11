<?php

namespace iggyvolz\buttplug\Message\Output;

final readonly class HwPositionWithDuration
{
    public function __construct(
        public int $value,
        public int $duration,
    )
    {
    }
}