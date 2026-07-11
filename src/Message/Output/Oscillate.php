<?php

namespace iggyvolz\buttplug\Message\Output;

final readonly class Oscillate
{
    public function __construct(
        public int $value,
    )
    {
    }
}