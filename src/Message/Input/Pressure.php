<?php

namespace iggyvolz\buttplug\Message\Input;

final readonly class Pressure
{
    public function __construct(
        public int $value,
    )
    {
    }
}