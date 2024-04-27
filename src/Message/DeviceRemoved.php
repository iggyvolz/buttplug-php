<?php

namespace iggyvolz\buttplug\Message;

final readonly class DeviceRemoved extends ServerMessage
{
    public function __construct(
        int $id,
        public int $deviceIndex
    )
    {
        parent::__construct($id);
    }
}