<?php

namespace iggyvolz\buttplug\Message;

final readonly class InputCmd extends ClientMessage
{
    public function __construct(
        int                $id,
        public int         $deviceIndex,
        public int         $featureIndex,
        public InputType   $type,
        public CommandType $command,
    )
    {
        parent::__construct($id);
    }
}