<?php

namespace iggyvolz\buttplug\Message;

use iggyvolz\buttplug\Connection;
use iggyvolz\buttplug\DeviceInfo;

/** Pseudo-message to note that the server connected */
final readonly class ConnectedEvent
{
    public function __construct(public readonly Connection $connection)
    {
    }
}