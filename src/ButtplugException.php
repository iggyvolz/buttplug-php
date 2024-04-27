<?php

namespace iggyvolz\buttplug;

use Exception;
use iggyvolz\buttplug\Message\Error;

final class ButtplugException extends Exception
{
    public function __construct(Error $error)
    {
        parent::__construct($error->errorMessage, $error->errorCode->value, null);
    }
}