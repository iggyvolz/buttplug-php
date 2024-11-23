<?php

namespace iggyvolz\buttplug\Message;

final readonly class SensorReading extends ServerMessage
{
    /**
     * @param list<int> $data
     */
    public function __construct(int $id, public int $deviceIndex, public int $sensorIndex, public string $sensorType, public array $data)
    {
        parent::__construct($id);
    }
}