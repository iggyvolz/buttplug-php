<?php

namespace iggyvolz\buttplug;

use JsonSerializable;

final readonly class ScalarValue implements JsonSerializable
{
    public function __construct(
        public int $index,
        public float $scalar,
        public string $actuatorType,
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