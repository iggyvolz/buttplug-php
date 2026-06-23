<?php

namespace iggyvolz\buttplug\Message\Output;

final readonly class Led
{
    public function __construct(
        public int $value,
    )
    {
    }
}