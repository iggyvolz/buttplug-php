<?php

namespace iggyvolz\buttplug\Client;

use Amp\Cancellation;
use Amp\DeferredFuture;
use Amp\Future;
use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\Client\WebsocketHandshake;
use Amp\Websocket\WebsocketClient;
use CuyZ\Valinor\Mapper\Configurator\ConvertKeysToCamelCase;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use iggyvolz\buttplug\DeviceInfo;
use iggyvolz\buttplug\Message\ClientMessage;
use iggyvolz\buttplug\Message\DeviceList;
use iggyvolz\buttplug\Message\Error;
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
use LogicException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\UriInterface as PsrUri;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Revolt\EventLoop;
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
     * @var array<int,DeferredFuture<ServerMessage>>
     */
    private array $futures = [];
    protected(set) ServerInfo $serverInfo;
    protected(set) DeviceList $deviceList;
    /**
     * @var array<string, Device>
     */
    protected(set) array $devices;
    private(set) LoggerInterface $logger;

    private function __construct(
        private readonly WebsocketClient           $ws,
        private readonly ?EventDispatcherInterface $eventDispatcher = null,
        LoggerInterface                            $logger = new NullLogger(),
        private readonly bool                      $debug = false
    )
    {
        $this->logger = $logger;
        $this->mapper = new MapperBuilder()->allowSuperfluousKeys()->allowPermissiveTypes()->configureWith(new ConvertKeysToCamelCase())->mapper();
    }

    public static function connect(
        WebsocketHandshake|PsrUri|string|WebsocketClient $connectionOrIp,
        string $clientName,
        ?EventDispatcherInterface $eventDispatcher = null,
        LoggerInterface $logger = new NullLogger(),
        bool $debug = false): IConnection
    {
        if(!$connectionOrIp instanceof WebsocketClient) {
            $connectionOrIp = connect($connectionOrIp);
        }
        $self = new self($connectionOrIp, $eventDispatcher, $logger, $debug);
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
        $this->ws->sendText(json_encode($messages, flags: JSON_THROW_ON_ERROR));
    }

    /**
     * @param ClientMessage $message
     * @return Future<ServerMessage>
     */
    private function sendMessageAsync(ClientMessage $message): Future
    {
        /**
         * @var DeferredFuture<ServerMessage> $future
         */
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
        $messagesJson = json_decode($this->ws->receive()?->read() ?? "[]", associative: true, flags: JSON_THROW_ON_ERROR);
        if(!is_array($messagesJson)) {
            throw new LogicException("Received non-array messages");
        }
        $this->logger->debug("Received messages", ["messages" => $messagesJson]);
        $messages = array_map(function (mixed $obj): ServerMessage {
            if(!is_array($obj)) {
                throw new LogicException("Received non-array message");
            }
            $type = array_key_first($obj) ?? throw new LogicException("Received non-typed message");
            $data = $obj[$type];
            if(!is_array($data)) {
                throw new LogicException("Received non-array data");
            }
            $serverMessage = $this->mapper->map("iggyvolz\\buttplug\\Message\\$type", Source::array($data));
            if(!($serverMessage instanceof ServerMessage)) {
                throw new LogicException("Received non-server message");
            }
            return $serverMessage;
        }, $messagesJson);
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
            while(!$this->ws->isClosed()) {
                try {
                    $this->receiveMessages();
                } catch (Throwable $e) {
                    $this->logger->error("Error in connection", ["exception" => $e]);
                    if($this->debug) {
                        // Only throw errors in debug mode (i.e. tests) - otherwise log and swallow
                        EventLoop::queue(static fn () => throw $e);
                        return;
                    }
                }
            }
            $this->logger->error("Websocket closed");
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
                while(!$this->ws->isClosed()) {
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

    public function input(int $deviceIndex, int $featureIndex, InputType $type): Battery|Button|Pressure|RSSI|null
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

    public function close(): void
    {
        $this->ws->close();
    }
}