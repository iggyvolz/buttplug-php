<?php

namespace iggyvolz\buttplug\Client;

use iggyvolz\buttplug\DeviceFeature;
use iggyvolz\buttplug\DeviceInfo;

/** Abstraction over Connection & DeviceInfo */
final class Device
{
    /**
     * @var list<Feature>
     */
    private(set) array $features;

    public function __construct(public readonly IConnection $connection, public readonly DeviceInfo $deviceInfo)
    {
        $this->features = array_values(array_map(fn(DeviceFeature $info) => new Feature($this, $info), $this->deviceInfo->deviceFeatures));
    }

    public string $name { get => $this->deviceInfo->deviceName; }
    public ?string $displayName { get => $this->deviceInfo->deviceDisplayName; }
}