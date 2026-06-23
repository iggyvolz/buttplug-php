<?php

namespace iggyvolz\buttplug\Exception;

use iggyvolz\buttplug\Message\Error;
use iggyvolz\buttplug\Message\ErrorCode;

final class ButtplugInitException extends ButtplugException
{
    public function __construct(public Error $error)
    {
        assert($error->errorCode === ErrorCode::Init);
        parent::__construct($error);
    }
}