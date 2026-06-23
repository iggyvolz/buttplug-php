<?php

namespace iggyvolz\buttplug\Message\Output;

final readonly class Position
{
    public function __construct(
        public int $value,
    )
    {
    }
}