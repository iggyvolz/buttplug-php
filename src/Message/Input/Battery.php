<?php

namespace iggyvolz\buttplug\Message\Input;

final readonly class Battery
{
    public function __construct(
        public int $value,
    )
    {
    }
}