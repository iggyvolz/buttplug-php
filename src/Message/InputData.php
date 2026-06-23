<?php

namespace iggyvolz\buttplug\Message;

use iggyvolz\buttplug\Message\Input\Battery;
use iggyvolz\buttplug\Message\Input\Button;
use iggyvolz\buttplug\Message\Input\Pressure;
use iggyvolz\buttplug\Message\Input\RSSI;

final readonly class InputData
{
    public function __construct(
        public ?Battery $battery = null,
        public ?RSSI $rSSI = null,
        public ?Pressure $pressure = null,
        public ?Button $button = null
    )
    {
    }
    public function get(): Battery|RSSI|Pressure|Button
    {
        return $this->battery ?? $this->rSSI ?? $this->pressure ?? $this->button;
    }
    public static function of(Battery|RSSI|Pressure|Button $data): self
    {
        return match(get_class($data)) {
            Battery::class => new self(battery: $data),
            RSSI::class => new self(rSSI: $data),
            Pressure::class => new self(pressure: $data),
            Button::class => new self(button: $data),
        };
    }
    public static function from(InputType|string $type, int $value): self
    {
        if(is_string($type)) {
            $type = InputType::from($type);
        }
        return match($type) {
            InputType::Battery => new self(battery: new Battery($value)),
            InputType::RSSI => new self(rSSI: new RSSI($value)),
            InputType::Pressure => new self(pressure: new Pressure($value)),
            InputType::Button => new self(button: new Button($value)),
        };
    }
}