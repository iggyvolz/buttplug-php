<?php

namespace iggyvolz\buttplug;

use iggyvolz\buttplug\Client\IConnection;
use Psr\EventDispatcher\EventDispatcherInterface;

interface EventDispatcherFactory
{
    public function getEventDispatcher(IConnection $connection): ?EventDispatcherInterface;
}