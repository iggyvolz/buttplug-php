<?php

namespace iggyvolz\buttplug\Client;

use Amp\Cancellation;
use Amp\DeferredFuture;
use Amp\Future;
use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\Client\WebsocketHandshake;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use iggyvolz\buttplug\DeviceInfo;
use iggyvolz\buttplug\Message\ClientMessage;
use iggyvolz\buttplug\Message\DeviceList;
use iggyvolz\buttplug\Message\Error;
use iggyvolz\buttplug\Message\Extension;
use iggyvolz\buttplug\Message\Input\Battery;
use iggyvolz\buttplug\Message\Input\Button;
use iggyvolz\buttplug\Message\Input\Pressure;
use iggyvolz\buttplug\Message\Input\RSSI;
use iggyvolz\buttplug\Message\InputCmd;
use iggyvolz\buttplug\Message\CommandType;
use iggyvolz\buttplug\Message\InputReading;
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
use iggyvolz\buttplug\Message\OutputCmd;
use iggyvolz\buttplug\Message\OutputCommand;
use iggyvolz\buttplug\Message\Ping;
use iggyvolz\buttplug\Message\RequestDeviceList;
use iggyvolz\buttplug\Message\RequestServerInfo;
use iggyvolz\buttplug\Message\ServerInfo;
use iggyvolz\buttplug\Message\ServerMessage;
use iggyvolz\buttplug\Message\StartScanning;
use iggyvolz\buttplug\Message\StopCmd;
use iggyvolz\buttplug\Message\StopScanning;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\UriInterface as PsrUri;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;
use function Amp\async;
use function Amp\delay;
use function Amp\Websocket\Client\connect;

class Connection implements IConnection
{
    private TreeMapper $mapper;
    private int $nextMessageId = 0;
    /** @internal  */
    public function getMessageId(): int {
        return $this->nextMessageId++;
    }
    /**
     * @var array<int,DeferredFuture>
     */
    private array $futures = [];
    protected(set) ServerInfo $serverInfo;
    protected(set) DeviceList $deviceList;
    protected(set) array $devices;
    private(set) LoggerInterface $logger;

    private function __construct(
        private readonly WebsocketConnection $websocketConnection,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
        LoggerInterface $logger = new NullLogger(),
    )
    {
        $this->logger = $logger;
        $this->mapper = new MapperBuilder()->allowSuperfluousKeys()->allowPermissiveTypes()->mapper();
    }

    public static function connect(
        WebsocketHandshake|PsrUri|string $ip,
        string $clientName,
        ?EventDispatcherInterface $eventDispatcher = null,
        LoggerInterface $logger = new NullLogger()): IConnection
    {
        $self = new self(connect($ip), $eventDispatcher, $logger);
        $self->run();
        $logger->debug("Requesting server info");
        $serverInfo = $self->requestServerInfo($clientName);
        $self->logger->info("Connected to server {$self->serverInfo->serverName}");
        $self->requestDeviceList();
        $self->startScanning();
        return $self;
    }

    protected function sendMessages(ClientMessage ...$messages): void
    {
        $this->logger->debug("Sending messages", ["messages" => $messages]);
        $this->websocketConnection->sendText(json_encode($messages));
    }

    /**
     * @param ClientMessage $message
     * @return Future<ServerMessage>
     */
    private function sendMessageAsync(ClientMessage $message): Future
    {
        $future = new DeferredFuture();
        $this->futures[$message->id] = $future;
        $this->sendMessages($message);
        return $future->getFuture();
    }

    /**
     * @param ClientMessage $message
     * @return ServerMessage
     * @internal
     */
    public function sendMessage(ClientMessage $message, ?Cancellation $cancellation = null): ServerMessage
    {
        return $this->sendMessageAsync($message)->await($cancellation);
    }

    private function receiveMessages(): void
    {
        $messagesJson = json_decode($this->websocketConnection->receive()?->read() ?? "[]", associative: true, flags: JSON_THROW_ON_ERROR);
        $this->logger->debug("Received messages", ["messages" => $messagesJson]);
        $messages = array_map(fn(array $obj): ServerMessage => $this->mapper->map("iggyvolz\\buttplug\\Message\\" . array_key_first($obj), Source::array($obj[array_key_first($obj)])->camelCaseKeys()), $messagesJson);
        foreach($messages as $message) {
            if(array_key_exists($message->id, $this->futures)) {
                $future = $this->futures[$message->id];
                if($message instanceof Error) {
                    $future->error($message->exception());
                } else {
                    $this->futures[$message->id]->complete($message);
                }
                unset($this->futures[$message->id]);
            }

            $this->logger->debug("Dispatching a message", ["message" => $message]);
            if($message instanceof DeviceList) {
                $this->deviceList = $message;
                $this->devices = array_map(fn(DeviceInfo $info) => new Device($this, $info), $this->deviceList->devices);
            }
            if($message instanceof ServerInfo) $this->serverInfo = $message;
            $this->eventDispatcher?->dispatch($message);
        }
    }

    public function run(): void
    {
        async(function(){
            while(true) {
                try {
                    if($this->websocketConnection->isClosed()) {
                        $this->logger->error("Websocket closed");
                        return;
                    }
                    $this->receiveMessages();
                } catch (Throwable $e) {
                    $this->logger->error("Error in connection", ["exception" => $e]);
                }
            }
        });
    }

    public function ping(): void
    {
        $this->sendMessage(new Ping($this->getMessageId()));
    }

    private const int MAJOR_VERSION = 4;
    private const int MINOR_VERSION = 0;
    private function requestServerInfo(string $clientName): ServerInfo
    {
        /** @var ServerInfo $serverInfo */
        $serverInfo = $this->sendMessage(new RequestServerInfo($this->getMessageId(), $clientName, self::MAJOR_VERSION, self::MINOR_VERSION));
        if($serverInfo->maxPingTime > 0) {
            async(function() use ($serverInfo) {
                while(true) {
                    try {
                        delay($serverInfo->maxPingTime);
                        $this->ping();
                    } catch (Throwable $e) {
                        $this->logger->error("Error in connection", ["exception" => $e]);
                    }
                }
            });
        }
        return $serverInfo;
    }

    public function startScanning(): void
    {
        $this->sendMessage(new StartScanning($this->getMessageId()));
    }

    public function stopScanning(): void
    {
        $this->sendMessage(new StopScanning($this->getMessageId()));
    }

    public function requestDeviceList(): DeviceList
    {
        /**
         * @var DeviceList $deviceList
         */
        $deviceList = $this->sendMessage(new RequestDeviceList($this->getMessageId()));
        return $deviceList;
    }

    public function stopDeviceCmd(int $deviceIndex): void
    {
        $this->sendMessage(new StopCmd($this->getMessageId(), $deviceIndex));
    }

    public function stopAllDevices(): void
    {
        $this->sendMessage(new StopCmd($this->getMessageId()));
    }

    public function output(
        int $deviceIndex,
        int $featureIndex,
        Constrict|HwPositionWithDuration|Led|Oscillate|Position|Rotate|RotationWithDirection|Spray|Temperature|Vibrate $command
    ): void
    {
        $this->sendMessage(new OutputCmd($this->getMessageId(), $deviceIndex, $featureIndex, OutputCommand::of($command)));
    }

    public function input(int $deviceIndex, int $featureIndex, InputType $type): Battery|Button|Pressure|RSSI
    {
        /** @var InputReading $reading */
        $reading = $this->sendMessage(new InputCmd($this->getMessageId(), $deviceIndex, $featureIndex, $type, CommandType::Read));
        return match($type) {
            InputType::Battery => $reading->reading->battery,
            InputType::Button => $reading->reading->button,
            InputType::Pressure => $reading->reading->pressure,
            InputType::RSSI => $reading->reading->rSSI,
        };
    }

    public function subscribe(int $deviceIndex, int $featureIndex, InputType $type): void
    {
        $this->sendMessage(new InputCmd($this->getMessageId(), $deviceIndex, $featureIndex, $type, CommandType::Subscribe));
    }

    public function unsubscribe(int $deviceIndex, int $featureIndex, InputType $type): void
    {
        $this->sendMessage(new InputCmd($this->getMessageId(), $deviceIndex, $featureIndex, $type, CommandType::Unsubscribe));
    }
}