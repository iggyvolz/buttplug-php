<?php

namespace iggyvolz\buttplug;

final readonly class DeviceInfo
{
    /**
     * @param array<string,DeviceFeature> $deviceFeatures
     */
    public function __construct(
        public string $deviceName,
        public int $deviceIndex,
        public array $deviceFeatures,
        public int $deviceMessageTimingGap = 0,
        public ?string $deviceDisplayName = null,
    )
    {
    }
}