<?php

namespace iggyvolz\buttplug\Client;

use iggyvolz\buttplug\Message\DeviceList;
use iggyvolz\buttplug\Message\Input\Battery;
use iggyvolz\buttplug\Message\Input\Button;
use iggyvolz\buttplug\Message\Input\Pressure;
use iggyvolz\buttplug\Message\Input\RSSI;
use iggyvolz\buttplug\Message\InputType;
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
use iggyvolz\buttplug\Message\ServerInfo;
use Psr\Log\LoggerInterface;

interface IConnection
{
    public function run(): void;

    public function ping(): void;

    public function startScanning(): void;

    public function stopScanning(): void;

    public function requestDeviceList(): DeviceList;

    public function stopDeviceCmd(int $deviceIndex): void;

    public function stopAllDevices(): void;

    public function output(int $deviceIndex, int $featureIndex, Constrict|HwPositionWithDuration|Led|Oscillate|Position|Rotate|RotationWithDirection|Spray|Temperature|Vibrate $command): void;

    public function input(int $deviceIndex, int $featureIndex, InputType $type): Battery|Button|Pressure|RSSI|null;

    public function subscribe(int $deviceIndex, int $featureIndex, InputType $type): void;

    public function unsubscribe(int $deviceIndex, int $featureIndex, InputType $type): void;

    public ServerInfo $serverInfo {get;}
    public DeviceList $deviceList {get;}
    /**
     * @var array<string, Device>
     */
    public array $devices {get;}
    public LoggerInterface $logger {get;}
}