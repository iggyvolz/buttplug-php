<?php

use iggyvolz\buttplug\Message\RequestDeviceList;
use iggyvolz\buttplug\Message\RequestServerInfo;
use iggyvolz\buttplug\Message\StartScanning;
use function Amp\async;

require_once __DIR__ . "/vendor/autoload.php";
async(function(){
    $conn = \iggyvolz\buttplug\Connection::connect("ws://192.168.2.171:12345");
    $conn->run();
    $serverInfo = $conn->requestServerInfo("hello");
    echo "Hello from $serverInfo->serverName!\n";
    $conn->startScanning();
    \Amp\delay(10); // i'm lazy :\
    echo "Devices:\n";
    foreach($conn->requestDeviceList()->devices as $device) {
        echo "- $device->deviceName\n";
    }
});
\Revolt\EventLoop::run();

//$conn->sendMessages(new RequestServerInfo(__LINE__, "hello", 3));
//var_dump($conn->receiveMessages());
//$conn->sendMessages(new StartScanning(__LINE__));
//$conn->sendMessages(new RequestDeviceList(__LINE__));
//while (true) {
//    var_dump($conn->receiveMessages());
//}