<?php

namespace iggyvolz\buttplug\Message;

final readonly class SensorReadCmd extends ClientMessage
{
    public function __construct(int $id, public int $deviceIndex, public int $sensorIndex, public string $sensorType)
    {
        parent::__construct($id);
    }
}