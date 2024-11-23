<?php

namespace iggyvolz\buttplug;

use JsonSerializable;

final readonly class LinearValue implements JsonSerializable
{
    public function __construct(
        public int $index,
        public int $duration,
        public float $position,
    )
    {
    }

    public function jsonSerialize(): array
    {
        $arr = (array)$this;
        $keys = array_map(ucfirst(...), array_keys($arr));
        return array_combine($keys, $arr);
    }
}