<?php

namespace iggyvolz\buttplug;

use Psr\EventDispatcher\EventDispatcherInterface;

interface EventDispatcherFactory
{
    public function getEventDispatcher(Connection $connection): ?EventDispatcherInterface;
}