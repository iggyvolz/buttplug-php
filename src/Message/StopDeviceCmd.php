<?php

namespace iggyvolz\buttplug\Message;

final readonly class StopDeviceCmd extends ClientMessage
{
    public function __construct(int $id, public int $deviceIndex)
    {
        parent::__construct($id);
    }
}