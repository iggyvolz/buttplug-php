<?php

namespace iggyvolz\buttplug;

final readonly class DeviceInfo
{
    /**
     * @param array<string,list<MessageAttribute>> $deviceMessages
     */
    public function __construct(
        public string $deviceName,
        public int $deviceIndex,
        public array $deviceMessages,
        public ?int $deviceMessageTimingGap = 0,
        public ?string $deviceDisplayName = null,
    )
    {
    }
}