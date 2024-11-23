<?php

namespace iggyvolz\buttplug\Message;

use iggyvolz\buttplug\LinearValue;

final readonly class LinearCmd extends ClientMessage
{
    /**
     * @param list<LinearValue> $vectors
     */
    public function __construct(int $id, public int $deviceIndex, public array $vectors)
    {
        parent::__construct($id);
    }
}