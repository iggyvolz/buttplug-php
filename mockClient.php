<?php

use iggyvolz\buttplug\Client\Connection;
use iggyvolz\buttplug\Input;
use iggyvolz\buttplug\Message\DeviceList;
use iggyvolz\buttplug\Message\Input\Battery;
use iggyvolz\buttplug\Message\CommandType;
use iggyvolz\buttplug\Message\InputData;
use iggyvolz\buttplug\Message\InputType;
use iggyvolz\buttplug\Output;
use Iggyvolz\SimpleAttributeReflection\AttributeReflection;
use League\Event\EventDispatcher;
use League\Event\ListenerRegistry;
use League\Event\ListenerSubscriber;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Revolt\EventLoop;
use function Amp\async;

require_once __DIR__ . "/vendor/autoload.php";
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class EventListener
{
}
class Listeners implements ListenerSubscriber {
    public function __construct(private readonly Logger $logger)
    {
    }

    #[EventListener]
    public function handleDeviceList(DeviceList $devices): void
    {
        $this->logger->info("Found " . count($devices->devices) . " devices:");
        foreach($devices->devices as $deviceInfo) {
            $this->logger->info("  Device \"$deviceInfo->deviceName\":");
            foreach ($deviceInfo->deviceFeatures as $feature) {
                $this->logger->info("    Feature \"$feature->featureDescription\":");
                foreach ($feature->input ?? [] as $i => $input) {
                    $this->logger->info("      Input \"$i\":");
                    $this->logger->info("        Supported Commands: " . implode(", ", array_map(fn(CommandType $t) => $t->name, $input->command)));
                    foreach($input->value as $value) {
                        $this->logger->info("        Minimum: " . $value[0] . "");
                        $this->logger->info("        Maximum: " . $value[1] . "");
                    }
                }
                foreach ($feature->output ?? [] as $i => $output) {
                    $this->logger->info("      Output \"$i\":");
                    $this->logger->info("        Minimum: " . $output->value[0] . "");
                    $this->logger->info("        Maximum: " . $output->value[1] . "");
                    if ($output->duration !== null) {
                        $this->logger->info("        Minimum Duration: " . $output->duration[0] . "");
                        $this->logger->info("        Maximum Duration: " . $output->duration[1] . "");
                    }
                }
            }
        }
    }
    public function subscribeListeners(ListenerRegistry $acceptor): void
    {
        foreach (new ReflectionClass(self::class)->getMethods() as $method) {
            if(AttributeReflection::getAttribute($method, EventListener::class) !== null) {
                $this->logger->debug("Registering listener for " . $method->getName() . ": " . $method->getParameters()[0]->getType()->getName() . "");
                $acceptor->subscribeTo($method->getParameters()[0]->getType()->getName(), function(object $e) use($method) {
                    async(function() use($method, $e){
                        $this->{$method->getName()}($e);
                    });
                });
            }
        }
    }
}
$logger = new Logger("buttplug-php-demo");
$handler = new StreamHandler("php://stdout", Level::Debug);
$handler->setFormatter($normalizer = new LineFormatter());
$normalizer->setMaxNormalizeDepth(100);
$logger->pushHandler($handler);
async(function() use ($logger) {
    $eventHandler = new EventDispatcher();
    $eventHandler->subscribeListenersFrom(new Listeners($logger));
    $logger->debug("Attempting to connect...");
    $conn = Connection::connect("ws://127.0.0.1:12345", "buttplug-php", $eventHandler, $logger);
    $logger->debug("Connected to server");
});
EventLoop::run();
