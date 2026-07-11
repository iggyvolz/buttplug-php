<?php

namespace iggyvolz\buttplug\Message;

final readonly class Disconnect extends ClientMessage
{
    public function __construct(int $id)
    {
        parent::__construct($id);
    }
}