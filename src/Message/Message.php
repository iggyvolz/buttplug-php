<?php

namespace iggyvolz\buttplug\Message;

use JsonSerializable;

abstract readonly class Message implements JsonSerializable
{
    public function __construct(public int $id)
    {
    }

    public function jsonSerialize(): array
    {
        $arr = (array)$this;
        $keys = array_map(ucfirst(...), array_keys($arr));
        return [substr(static::class, strlen("iggyvolz\\buttplug\\Message\\")) => array_combine($keys, $arr)];
    }
}