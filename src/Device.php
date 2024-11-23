<?php

namespace iggyvolz\buttplug;

/** Abstraction over Connection & DeviceInfo */
final class Device
{
    public function __construct(public readonly Connection $connection, public readonly DeviceInfo $deviceInfo)
    {
    }
}