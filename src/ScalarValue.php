<?php

namespace iggyvolz\buttplug;

final readonly class ScalarValue
{
    public function __construct(
        public int $index,
        public float $scalar,
        public string $actuatorType,
    )
    {
    }
}