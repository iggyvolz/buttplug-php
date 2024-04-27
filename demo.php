<?php

use iggyvolz\buttplug\Message\RequestDeviceList;
use iggyvolz\buttplug\Message\RequestServerInfo;
use iggyvolz\buttplug\Message\StartScanning;

require_once __DIR__ . "/vendor/autoload.php";

$conn = \iggyvolz\buttplug\Connection::connect("ws://192.168.2.171:12345");
$conn->sendMessages(new RequestServerInfo(__LINE__, "hello", 3));
var_dump($conn->receiveMessages());
$conn->sendMessages(new StartScanning(__LINE__));
$conn->sendMessages(new RequestDeviceList(__LINE__));
while (true) {
    var_dump($conn->receiveMessages());
}