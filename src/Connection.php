<?php

namespace iggyvolz\buttplug;

use Amp\Cancellation;
use Amp\DeferredFuture;
use Amp\Future;
use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\Client\WebsocketHandshake;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use iggyvolz\buttplug\Message\ClientMessage;
use iggyvolz\buttplug\Message\DeviceList;
use iggyvolz\buttplug\Message\Error;
use iggyvolz\buttplug\Message\Ok;
use iggyvolz\buttplug\Message\Ping;
use iggyvolz\buttplug\Message\RequestDeviceList;
use iggyvolz\buttplug\Message\RequestServerInfo;
use iggyvolz\buttplug\Message\ScalarCmd;
use iggyvolz\buttplug\Message\ServerInfo;
use iggyvolz\buttplug\Message\ServerMessage;
use iggyvolz\buttplug\Message\StartScanning;
use iggyvolz\buttplug\Message\StopAllDevices;
use iggyvolz\buttplug\Message\StopDeviceCmd;
use iggyvolz\buttplug\Message\StopScanning;
use Psr\Http\Message\UriInterface as PsrUri;
use function Amp\async;
use function Amp\delay;
use function Amp\Websocket\Client\connect;

class Connection
{
    private TreeMapper $mapper;
    private int $messageId = 69;
    /**
     * @var array<int,DeferredFuture>
     */
    private array $futures = [];

    private function __construct(private readonly WebsocketConnection $websocketConnection)
    {
        $this->mapper = (new MapperBuilder())->allowSuperfluousKeys()->allowPermissiveTypes()->mapper();
    }

    public static function connect(WebsocketHandshake|PsrUri|string $ip): self
    {
        $self = new self(connect($ip));
        $self->run();
        return $self;
    }

    private function sendMessages(ClientMessage ...$messages): void
    {
//        echo json_encode($messages) . PHP_EOL;
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
     */
    private function sendMessage(ClientMessage $message, ?Cancellation $cancellation = null): ServerMessage
    {
        return $this->sendMessageAsync($message)->await($cancellation);
    }

    /**
     * @return list<ServerMessage>
     */
    private function receiveMessages(): array
    {
        $messagesJson = json_decode($this->websocketConnection->receive()->read(), associative: true, flags: JSON_THROW_ON_ERROR);
//        echo json_encode($messagesJson) . PHP_EOL;
        $messages = array_map(fn(array $obj): ServerMessage => $this->mapper->map("iggyvolz\\buttplug\\Message\\" . array_key_first($obj), Source::array($obj[array_key_first($obj)])->camelCaseKeys()), $messagesJson);
        foreach($messages as $message) {
            if(array_key_exists($message->id, $this->futures)) {
                $future = $this->futures[$message->id];
                if($message instanceof Error) {
                    $future->error(new ButtplugException($message));
                } else {
                    $this->futures[$message->id]->complete($message);
                }
                unset($this->futures[$message->id]);
            }
        }
        return $messages;
    }

    public function run(): void
    {
        async(function(){
            while(true) $this->receiveMessages();
        });
    }

    public function ping(): void
    {
        $this->sendMessage(new Ping($this->messageId++));
    }

    private const MESSAGE_VERSION = 3;
    public function requestServerInfo(string $clientName): ServerInfo
    {
        /** @var ServerInfo $serverInfo */
        $serverInfo = $this->sendMessage(new RequestServerInfo($this->messageId++, $clientName, self::MESSAGE_VERSION));
        if($serverInfo->maxPingTime > 0) {
            async(function() use ($serverInfo) {
                while(true) {
                    delay($serverInfo->maxPingTime);
                    $this->ping();
                }
            });
        }
        return $serverInfo;
    }

    public function startScanning(): void
    {
        $this->sendMessage(new StartScanning($this->messageId++));
    }

    public function stopScanning(): void
    {
        $this->sendMessage(new StopScanning($this->messageId++));
    }

    public function requestDeviceList(): DeviceList
    {
        /**
         * @var DeviceList $deviceList
         */
        $deviceList = $this->sendMessage(new RequestDeviceList($this->messageId++));
        return $deviceList;
    }

    public function stopDeviceCmd(int $deviceIndex): void
    {
        $this->sendMessage(new StopDeviceCmd($this->messageId++, $deviceIndex));
    }

    public function stopAllDevices(): void
    {
        $this->sendMessage(new StopAllDevices($this->messageId++));
    }

    public function scalarCmd(int $deviceIndex, ScalarValue ...$scalars): void
    {
        $this->sendMessage(new ScalarCmd($this->messageId++, $deviceIndex, $scalars));
    }
}