<?php

namespace iggyvolz\buttplug\Message\Input;

final readonly class Button
{
    public function __construct(
        public int $value,
    )
    {
    }
}