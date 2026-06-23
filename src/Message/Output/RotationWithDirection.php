<?php

namespace iggyvolz\buttplug\Message\Output;

final readonly class RotationWithDirection
{
    public function __construct(
        public int $value,
        public bool $clockwise,
    )
    {
    }
}