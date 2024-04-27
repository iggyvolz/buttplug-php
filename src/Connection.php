<?php

namespace iggyvolz\buttplug;

use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\Client\WebsocketHandshake;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use iggyvolz\buttplug\Message\ClientMessage;
use iggyvolz\buttplug\Message\ServerMessage;
use Psr\Http\Message\UriInterface as PsrUri;
use function Amp\Websocket\Client\connect;

class Connection
{
    private TreeMapper $mapper;

    private function __construct(private readonly WebsocketConnection $websocketConnection)
    {
        $this->mapper = (new MapperBuilder())->allowSuperfluousKeys()->allowPermissiveTypes()->mapper();
    }

    public static function connect(WebsocketHandshake|PsrUri|string $ip): self
    {
        return new self(connect($ip));
    }

    public function sendMessages(ClientMessage ...$messages): void
    {
        $this->websocketConnection->sendText(json_encode($messages));
    }

    /**
     * @return list<ServerMessage>
     */
    public function receiveMessages(): array
    {
        $messagesJson = json_decode($this->websocketConnection->receive()->read(), associative: true, flags: JSON_THROW_ON_ERROR);
        return array_map(fn(array $obj): ServerMessage => $this->mapper->map("iggyvolz\\buttplug\\Message\\" . array_key_first($obj), Source::array($obj[array_key_first($obj)])->camelCaseKeys()), $messagesJson);
    }
}