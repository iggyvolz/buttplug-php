<?php

namespace iggyvolz\buttplug\Message;

final readonly class OutputCmd extends ClientMessage
{
    public function __construct(
        int $id,
        public int $deviceIndex,
        public int $featureIndex,
        public OutputCommand $command,
    )
    {
        parent::__construct($id);
    }
}