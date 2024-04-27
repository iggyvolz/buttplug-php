<?php

namespace iggyvolz\buttplug\Message;

use iggyvolz\buttplug\ScalarValue;

final readonly class ScalarCmd extends ClientMessage
{
    /**
     * @param list<ScalarValue> $scalars
     */
    public function __construct(int $id, public int $deviceIndex, public array $scalars)
    {
        parent::__construct($id);
    }
}