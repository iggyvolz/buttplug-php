<?php

use iggyvolz\buttplug\Connection;
use iggyvolz\buttplug\DeviceInfo;
use iggyvolz\buttplug\Message\ConnectedEvent;
use iggyvolz\buttplug\Message\DeviceAdded;
use iggyvolz\buttplug\Message\DeviceRemoved;
use iggyvolz\buttplug\Message\ServerInfo;
use Iggyvolz\SimpleAttributeReflection\AttributeReflection;
use League\Event\EventDispatcher;
use League\Event\ListenerRegistry;
use League\Event\ListenerSubscriber;
use Revolt\EventLoop;
use function Amp\async;
use function Amp\delay;

require_once __DIR__ . "/vendor/autoload.php";
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class EventListener
{
}
class Listeners implements ListenerSubscriber {
    public function __construct(private readonly string $clientName)
    {
    }

    private ?Connection $connection = null;
    private ?ServerInfo $serverInfo = null;
    /**
     * @var array<int,DeviceInfo>
     */
    private array $deviceInfos = [];
    #[EventListener]
    public function onConnected(ConnectedEvent $connectedEvent): void
    {
        echo "Connected!\n";
        $this->connection = $connectedEvent->connection;
        $this->serverInfo = $this->connection->requestServerInfo($this->clientName);
        echo "Hello from " . $this->serverInfo->serverName . "!\n";
        foreach($this->connection->requestDeviceList()->devices as $deviceInfo) {
            echo "Existing device added: $deviceInfo->deviceName\n";
            $this->deviceInfos[$deviceInfo->deviceIndex] = $deviceInfo;
            $this->handleDeviceAdd($deviceInfo);
        }
        echo "Now scanning for devices...\n";
        $this->connection->startScanning();
    }
    #[EventListener]
    public function onDeviceAdded(DeviceAdded $event): void
    {
        $deviceInfo = $event->device;
        echo "Device added: $deviceInfo->deviceName\n";
        $this->deviceInfos[$deviceInfo->deviceIndex] = $deviceInfo;
        $this->handleDeviceAdd($deviceInfo);
    }

    private function handleDeviceAdd(DeviceInfo $deviceInfo): void
    {
        echo "$deviceInfo->deviceName added!\nMessages:\n";
        foreach($deviceInfo->deviceMessages as $messageType => $messages) {
            echo "  $messageType:\n";
            foreach($messages as $message) {
                echo "    Feature Descriptor: " . json_encode($message->featureDescriptor) . "\n";
                echo "    Step Count: " . json_encode($message->stepCount) . "\n";
                echo "    Actuator Type: " . json_encode($message->actuatorType) . "\n";
                echo "    Sensor Type: " . json_encode($message->sensorType) . "\n";
                echo "    Sensor Range: " . json_encode($message->sensorRange) . "\n";
                echo "    Endpoints: " . json_encode($message->endpoints) . "\n";
            }
        }
    }
    #[EventListener]
    public function onDeviceRemoved(DeviceRemoved $event): void
    {
        $device = $this->deviceInfos[$event->deviceIndex];
        echo "Device removed: {$device->deviceName}\n";
        unset($this->deviceInfos[$event->deviceIndex]);
    }
    public function subscribeListeners(ListenerRegistry $acceptor): void
    {
        foreach ((new ReflectionClass(self::class))->getMethods() as $method) {
            if(AttributeReflection::getAttribute($method, EventListener::class) !== null) {
                $acceptor->subscribeOnceTo($method->getParameters()[0]->getType()->getName(), function(object $e) use($method) {
                    async(function() use($method, $e){
                        $this->{$method->getName()}($e);
                    });
                });
            }
        }
    }
}
async(function(){
    $eventHandler = new EventDispatcher();
    $eventHandler->subscribeListenersFrom(new Listeners("buttplug-php"));
    Connection::connect("ws://127.0.0.1:12345", $eventHandler);
});
EventLoop::run();
