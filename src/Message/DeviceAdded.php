<?php

namespace iggyvolz\buttplug\Message;

use iggyvolz\buttplug\DeviceInfo;

final readonly class DeviceAdded extends ServerMessage
{
    public DeviceInfo $device;

    /**
     * @param array<string,mixed> $deviceMessages
     */
    public function __construct(
        int $id,
        string $deviceName,
        int $deviceIndex,
        array $deviceMessages,
        ?int $deviceMessageTimingGap = 0,
        ?string $deviceDisplayName = null,
    )
    {
        parent::__construct($id);
        $this->device = new DeviceInfo($deviceName, $deviceIndex, $deviceMessages, $deviceMessageTimingGap, $deviceDisplayName);
    }
}