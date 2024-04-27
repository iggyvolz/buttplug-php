<?php

namespace iggyvolz\buttplug\Message;

final readonly class RequestServerInfo extends ClientMessage
{
    public function __construct(int $id, public string $clientName, public int $messageVersion)
    {
        parent::__construct($id);
    }
}