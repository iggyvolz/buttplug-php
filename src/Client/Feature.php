<?php

namespace iggyvolz\buttplug\Client;

use iggyvolz\buttplug\DeviceFeature;

final class Feature
{

    public function __construct(public readonly Device $param, public readonly DeviceFeature $info)
    {
    }

    public string $description { get => $this->info->featureDescription; }
}