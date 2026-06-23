<?php

namespace iggyvolz\buttplug\Message\Output;

final readonly class Vibrate
{
    public function __construct(
        public int $value,
    )
    {
    }
}