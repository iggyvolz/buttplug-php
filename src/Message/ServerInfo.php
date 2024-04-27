<?php

namespace iggyvolz\buttplug\Message;

final readonly class ServerInfo extends ServerMessage
{
    public function __construct(int $id, public string $serverName, public int $messageVersion, public int $maxPingTime)
    {
        parent::__construct($id);
    }
}