<?php

namespace iggyvolz\buttplug;

final readonly class MessageAttribute
{
    /**
     * @param list<array{0:int,1:int}>|null $sensorRange
     * @param list<string>|null $endpoints
     */
    public function __construct(
        public ?string $featureDescriptor = null,
        public ?int $stepCount = null,
        public ?string $actuatorType = null,
        public ?string $sensorType = null,
        public ?array $sensorRange = null,
        public ?array $endpoints = null,
    )
    {
    }
}