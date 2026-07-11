<?php

namespace iggyvolz\buttplug\Exception;

use Exception;
use iggyvolz\buttplug\Message\Error;

class ButtplugException extends Exception
{
    public function __construct(public Error $error)
    {
        parent::__construct($error->errorMessage, $error->errorCode->value);
    }
}