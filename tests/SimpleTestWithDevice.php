<?php

use iggyvolz\buttplug\Client\Device;
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
    return new DeviceList($message->id, [
        new \iggyvolz\buttplug\DeviceInfo("My Test Device", 0, []),
    ]);
});
TestConnection::expectMessage(function(StartScanning $message): void {
});
$connection = TestConnection::connect("Test Connection");
Assert::same(1, count($connection->devices));
Assert::same("My Test Device", $connection->devices[0]->name);
$connection->close();
Assert::true($requestedServerInfo);
