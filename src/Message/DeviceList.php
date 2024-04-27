<?php

namespace iggyvolz\buttplug\Message;

use iggyvolz\buttplug\DeviceInfo;

final readonly class DeviceList extends ServerMessage
{
    /**
     * @param list<DeviceInfo> $devices
     */
    public function __construct(int $id, public array $devices)
    {
        parent::__construct($id);
    }
}