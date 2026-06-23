<?php

namespace iggyvolz\buttplug\Exception;

use iggyvolz\buttplug\Message\Error;
use iggyvolz\buttplug\Message\ErrorCode;

final class ButtplugMsgException extends ButtplugException
{
    public function __construct(public Error $error)
    {
        assert($error->errorCode === ErrorCode::Msg);
        parent::__construct($error);
    }
}