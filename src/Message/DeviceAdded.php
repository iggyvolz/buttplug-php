<?php

namespace iggyvolz\buttplug\Message;

use iggyvolz\buttplug\DeviceInfo;
use iggyvolz\buttplug\MessageAttribute;

final readonly class DeviceAdded extends ServerMessage
{
    public DeviceInfo $device;

    /**
     * @param array<string,list<MessageAttribute>> $deviceMessages
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