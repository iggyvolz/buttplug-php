<?php

namespace iggyvolz\buttplug;

final readonly class DeviceFeature
{
    /**
     * @param array<string,Output>|null $output (keyed by OutputType)
     * @param array<string,Input>|null $input (keyed by InputType)
     */
    public function __construct(
        public string $featureDescription,
        public int $featureIndex,
        public ?array $output = null,
        public ?array $input = null,
    )
    {
    }
}