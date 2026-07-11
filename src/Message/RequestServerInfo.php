<?php

namespace iggyvolz\buttplug\Message;

final readonly class RequestServerInfo extends ClientMessage
{
    public int $protocolVersionMajor;
    public int $protocolVersionMinor;
    public function __construct(int $id, public string $clientName, ?int $protocolVersionMajor = null, ?int $protocolVersionMinor = null, ?int $messageVersion = null)
    {
        parent::__construct($id);
        $this->protocolVersionMajor = $protocolVersionMajor ?? $messageVersion ?? 0;
        $this->protocolVersionMinor = $protocolVersionMinor ?? 0;
    }
}