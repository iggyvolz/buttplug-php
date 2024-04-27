<?php

namespace iggyvolz\buttplug\Message;

enum ErrorCode: int implements \JsonSerializable
{
    case Unknown = 0;
    case Init = 1;
    case Ping = 2;
    case Msg = 3;
    case Device = 4;

    public function jsonSerialize(): int
    {
        return $this->value;
    }
}