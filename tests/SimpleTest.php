<?php

use iggyvolz\buttplug\Message\DeviceList;
use iggyvolz\buttplug\Message\RequestDeviceList;
use iggyvolz\buttplug\Message\RequestServerInfo;
use iggyvolz\buttplug\Message\ServerInfo;
use iggyvolz\buttplug\Message\StartScanning;
use Tester\Assert;

require_once __DIR__ . '/bootstrap.php';
$requestedServerInfo = false;
TestConnection::expectMessage(function(RequestServerInfo $message) use(&$requestedServerInfo): ServerInfo {
    $requestedServerInfo = true;
    return new ServerInfo($message->id, "Test Server", 0);
});
TestConnection::expectMessage(function(RequestDeviceList $message): DeviceList {
    return new DeviceList($message->id, []);
});
TestConnection::expectMessage(function(StartScanning $message): void {
});
$connection = TestConnection::connect("Test Connection");
$connection->close();
Assert::true($requestedServerInfo);
