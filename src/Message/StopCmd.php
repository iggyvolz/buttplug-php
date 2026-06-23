<?php

namespace iggyvolz\buttplug\Message;

final readonly class StopCmd extends ClientMessage
{
    public function __construct(int         $id,
                                public ?int $deviceIndex = null,
                                public ?int $featureIndex = null,
                                public bool $inputs = true,
                                public bool $outputs = true
    )
    {
        parent::__construct($id);
    }
}