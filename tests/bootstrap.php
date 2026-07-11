<?php
require __DIR__ . '/../vendor/autoload.php';
Tester\Environment::setup();

use _PHPStan_2874a496b\Nette\NotImplementedException;
use Amp\ByteStream\ReadableStream;
use Amp\Cancellation;
use Amp\Socket\SocketAddress;
use Amp\Socket\TlsInfo;
use Amp\Websocket\WebsocketClient;
use Amp\Websocket\WebsocketCloseCode;
use Amp\Websocket\WebsocketCloseInfo;
use Amp\Websocket\WebsocketCount;
use Amp\Websocket\WebsocketMessage;
use Amp\Websocket\WebsocketTimestamp;
use CuyZ\Valinor\Mapper\Configurator\ConvertKeysToCamelCase;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use iggyvolz\buttplug\Client\Connection;
use iggyvolz\buttplug\Client\IConnection;
use iggyvolz\buttplug\Message\ClientMessage;
use iggyvolz\buttplug\Message\Ok;
use iggyvolz\buttplug\Message\ServerMessage;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Revolt\EventLoop;
use Tester\Assert;

class TestConnection implements WebsocketClient, IteratorAggregate
{
    private readonly TreeMapper $mapper;

    public function __construct()
    {
        $this->mapper = new MapperBuilder()->allowSuperfluousKeys()->allowPermissiveTypes()->configureWith(new ConvertKeysToCamelCase())->mapper();

    }

    private static ?self $instance = null;
    /**
     * Expected message handlers, returns message to append to the queue
     * @var list<class-string, Closure(ClientMessage):null|ServerMessage>
     */
    private array $expectations = [];

    /**
     * List of messages queued to return from receive()
     * @var list<ServerMessage>
     */
    private array $queue = [];

    public static function expectMessage(Closure $handler): void
    {
        $type = new ReflectionFunction($handler)->getParameters()[0]->getType()->getName();
        self::get()->expectations[$type] = $handler;
    }

    public static function get(): self
    {
        return self::$instance ??= new self();
    }

    public static function connect(
        string $clientName): IConnection
    {
        $logger = new Logger("buttplug-php-demo");
        $handler = new StreamHandler("php://stdout", Level::Emergency);
        $handler->setFormatter($normalizer = new LineFormatter());
        $normalizer->setMaxNormalizeDepth(100);
        $logger->pushHandler($handler);
        return Connection::connect(self::get(), $clientName, null, $logger, true);
    }

    public function receive(?Cancellation $cancellation = null): ?WebsocketMessage
    {
        $delay = 0;
        while(empty($this->queue)) {
            if($this->closed) {
                return null;
            }
            if($delay > 5) {
                Assert::fail("Timed out waiting for message");
            }
            Amp\delay(0.1);
            $delay+=0.1;
        }
        $queue = $this->queue;
        $this->queue = [];
        return WebsocketMessage::fromText(json_encode($queue, flags: JSON_THROW_ON_ERROR));
    }

    public function sendText(string $data): void
    {
        $messagesJson = json_decode($data, associative: true, flags: JSON_THROW_ON_ERROR);
        if(!is_array($messagesJson)) {
            throw new LogicException("Received non-array messages");
        }
        $messages = array_map(function (mixed $obj): ClientMessage {
            if(!is_array($obj)) {
                throw new LogicException("Received non-array message");
            }
            $type = array_key_first($obj) ?? throw new LogicException("Received non-typed message");
            $data = $obj[$type];
            if(!is_array($data)) {
                throw new LogicException("Received non-array data");
            }
            $clientMessage = $this->mapper->map("iggyvolz\\buttplug\\Message\\$type", Source::array($data));
            if(!($clientMessage instanceof ClientMessage)) {
                throw new LogicException("Received non-client message");
            }
            return $clientMessage;
        }, $messagesJson);
        foreach($messages as $message) {
            $expectation = $this->expectations[get_class($message)] ?? Assert::fail("Received unexpected message " . get_class($message));
            $responseMessage = $expectation($message) ?? new Ok($message->id);
            $this->queue[] = $responseMessage;
        }
    }

    public function getId(): int
    {
        throw new LogicException();
    }

    public function getLocalAddress(): SocketAddress
    {
        throw new LogicException();
    }

    public function getRemoteAddress(): SocketAddress
    {
        throw new LogicException();
    }

    public function getTlsInfo(): ?TlsInfo
    {
        throw new LogicException();
    }

    public function getCloseInfo(): WebsocketCloseInfo
    {
        throw new LogicException();
    }

    public function isCompressionEnabled(): bool
    {
        throw new LogicException();
    }

    public function sendBinary(string $data): void
    {
        throw new LogicException();
    }

    public function streamText(ReadableStream $stream): void
    {
        throw new LogicException();
    }

    public function streamBinary(ReadableStream $stream): void
    {
        throw new LogicException();
    }

    public function ping(): void
    {
        throw new LogicException();
    }

    public function getCount(WebsocketCount $type): int
    {
        throw new LogicException();
    }

    public function getTimestamp(WebsocketTimestamp $type): float
    {
        throw new LogicException();
    }

    private bool $closed = false;

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function close(int $code = WebsocketCloseCode::NORMAL_CLOSE, string $reason = ''): void
    {
        $this->closed = true;
    }

    public function onClose(\Closure $onClose): void
    {
        throw new LogicException();
    }

    public function getIterator(): Traversable
    {
        throw new LogicException();
    }
}

register_shutdown_function(static fn () => EventLoop::run());

// Close TestConnection on dirty shutdown
set_exception_handler(static function (Throwable $e)  {
    try {
        TestConnection::get()->close();
    } catch (Throwable) {
        // swallow, we're already throwing
    }
    throw $e;
});