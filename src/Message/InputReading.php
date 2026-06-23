<?php

namespace iggyvolz\buttplug\Message;

final readonly class InputReading extends ServerMessage
{
    public function __construct(
        int $id,
        public int $deviceIndex,
        public int $featureIndex,
        public InputData $reading,
    )
    {
        parent::__construct($id);
    }
}