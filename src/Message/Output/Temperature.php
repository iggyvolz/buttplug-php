<?php

namespace iggyvolz\buttplug\Message\Output;

final readonly class Temperature
{
    public function __construct(
        public int $value,
    )
    {
    }
}