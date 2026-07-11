<?php

namespace iggyvolz\buttplug\Message;

use iggyvolz\buttplug\Message\Output\Constrict;
use iggyvolz\buttplug\Message\Output\HwPositionWithDuration;
use iggyvolz\buttplug\Message\Output\Led;
use iggyvolz\buttplug\Message\Output\Oscillate;
use iggyvolz\buttplug\Message\Output\Position;
use iggyvolz\buttplug\Message\Output\Rotate;
use iggyvolz\buttplug\Message\Output\RotationWithDirection;
use iggyvolz\buttplug\Message\Output\Spray;
use iggyvolz\buttplug\Message\Output\Temperature;
use iggyvolz\buttplug\Message\Output\Vibrate;

final readonly class OutputCommand
{
    public function __construct(
        public ?Vibrate $vibrate = null,
        public ?Rotate $rotate = null,
        public ?RotationWithDirection $rotationWithDirection = null,
        public ?Oscillate $oscillate = null,
        public ?Constrict $constrict = null,
        public ?Spray $spray = null,
        public ?Temperature $temperature = null,
        public ?Led $led = null,
        public ?Position $position = null,
        public ?HwPositionWithDuration $hwPositionWithDuration = null,
    )
    {
    }

    public function get(): Vibrate|Rotate|RotationWithDirection|Oscillate|Constrict|Spray|Temperature|Led|Position|HwPositionWithDuration
    {
        return $this->vibrate ?? $this->rotate ?? $this->rotationWithDirection ?? $this->oscillate ?? $this->constrict ?? $this->spray ?? $this->temperature ?? $this->led ?? $this->position ?? $this->hwPositionWithDuration ?? throw new \LogicException();
    }

    public static function of(Spray|Oscillate|Vibrate|Position|Temperature|Led|Constrict|RotationWithDirection|Rotate|HwPositionWithDuration $command): self
    {
        return match(get_class($command)) {
            Spray::class => new self(spray: $command),
            Oscillate::class => new self(oscillate: $command),
            Vibrate::class => new self(vibrate: $command),
            Position::class => new self(position: $command),
            Temperature::class => new self(temperature: $command),
            Led::class => new self(led: $command),
            Constrict::class => new self(constrict: $command),
            RotationWithDirection::class => new self(rotationWithDirection: $command),
            Rotate::class => new self(rotate: $command),
            HwPositionWithDuration::class => new self(hwPositionWithDuration: $command),
        };
    }
}