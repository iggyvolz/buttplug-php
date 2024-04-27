<?php

namespace iggyvolz\buttplug\Message;

use JsonSerializable;

abstract readonly class Message implements JsonSerializable
{
    public function __construct(public int $id)
    {
        var_dump($this);
    }

    public function jsonSerialize(): array
    {
        $arr = (array)$this;
        var_dump($arr);
        $keys = array_map(ucfirst(...), array_keys($arr));
        return [substr(static::class, strlen("iggyvolz\\buttplug\\Message\\")) => array_combine($keys, $arr)];
    }
}