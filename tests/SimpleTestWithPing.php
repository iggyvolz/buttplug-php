<?php

use iggyvolz\buttplug\Message\DeviceList;
use iggyvolz\buttplug\Message\Ping;
use iggyvolz\buttplug\Message\RequestDeviceList;
use iggyvolz\buttplug\Message\RequestServerInfo;
use iggyvolz\buttplug\Message\ServerInfo;
use iggyvolz\buttplug\Message\StartScanning;
use Tester\Assert;
use function Amp\delay;

require_once __DIR__ . '/bootstrap.php';
$requestedServerInfo = false;
TestConnection::expectMessage(function(RequestServerInfo $message) use(&$requestedServerInfo): ServerInfo {
    $requestedServerInfo = true;
    return new ServerInfo($message->id, "Test Server", 1);
});
TestConnection::expectMessage(function(RequestDeviceList $message): DeviceList {
    return new DeviceList($message->id, []);
});
TestConnection::expectMessage(function(StartScanning $message): void {
});
TestConnection::expectMessage(function(Ping $message) use(&$didPing): void {
    $didPing = true;
});
$connection = TestConnection::connect("Test Connection");
delay(5);
$connection->close();
Assert::true($requestedServerInfo);
Assert::true($didPing);