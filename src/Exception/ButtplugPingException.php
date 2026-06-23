<?php

namespace iggyvolz\buttplug\Exception;

use Exception;
use iggyvolz\buttplug\Message\Error;
use iggyvolz\buttplug\Message\ErrorCode;

final class ButtplugPingException extends ButtplugException
{
    public function __construct(public Error $error)
    {
        assert($error->errorCode === ErrorCode::Ping);
        parent::__construct($error);
    }
}