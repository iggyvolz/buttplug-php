<?php

namespace iggyvolz\buttplug;

final readonly class Output
{
    /**
     * @param array{0:int,1:int} $value
     * @param array{0:int,1:int}|null $duration
     */
    public function __construct(
        public array $value,
        public ?array $duration = null,
    )
    {
    }
}