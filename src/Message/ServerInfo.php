<?php

namespace iggyvolz\buttplug\Message;

final readonly class ServerInfo extends ServerMessage
{

    public int $protocolVersionMajor;
    public int $protocolVersionMinor;
    /** @var list<Extension> */
    public array $extensions;

    /**
     * @param list<Extension>|null $extensions
     */
    public function __construct(
        int $id,
        public string $serverName,
        public int $maxPingTime,
        ?int $protocolVersionMajor = null,
        ?int $protocolVersionMinor = null,
        ?int $messageVersion = null,
        ?array $extensions = null,
    )
    {
        parent::__construct($id);
        $this->protocolVersionMajor = $protocolVersionMajor ?? $messageVersion ?? 0;
        $this->protocolVersionMinor = $protocolVersionMinor ?? 0;
        $this->extensions = $extensions ?? [];
    }
}