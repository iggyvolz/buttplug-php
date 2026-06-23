<?php

namespace iggyvolz\buttplug;

use iggyvolz\buttplug\Message\CommandType;

final readonly class Input
{
    /**
     * @param list<CommandType> $command
     * @param list<array{0:int,1:int}> $value
     */
    public function __construct(
        public array $command,
        public array $value,
    )
    {
    }
}