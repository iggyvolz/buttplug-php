<?php

namespace iggyvolz\buttplug\Message;

use iggyvolz\buttplug\RotationValue;

final readonly class RotateCmd extends ClientMessage
{
    /**
     * @param list<RotationValue> $rotations
     */
    public function __construct(int $id, public int $deviceIndex, public array $rotations)
    {
        parent::__construct($id);
    }
}