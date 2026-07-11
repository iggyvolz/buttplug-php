<?php

namespace iggyvolz\buttplug\Message\Output;

final readonly class Rotate
{
    public function __construct(
        public int $value,
    )
    {
    }
}