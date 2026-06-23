<?php

namespace iggyvolz\buttplug\Message\Input;

final readonly class RSSI
{
    public function __construct(
        public int $value,
    )
    {
    }
}