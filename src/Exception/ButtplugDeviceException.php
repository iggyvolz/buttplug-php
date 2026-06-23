<?php

namespace iggyvolz\buttplug\Exception;

use Exception;
use iggyvolz\buttplug\Message\Error;
use iggyvolz\buttplug\Message\ErrorCode;

final class ButtplugDeviceException extends ButtplugException
{
    public function __construct(public Error $error)
    {
        assert($error->errorCode === ErrorCode::Device);
        parent::__construct($error);
    }
}