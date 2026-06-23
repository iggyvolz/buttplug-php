<?php

namespace iggyvolz\buttplug\Message;

use iggyvolz\buttplug\Exception\ButtplugDeviceException;
use iggyvolz\buttplug\Exception\ButtplugException;
use iggyvolz\buttplug\Exception\ButtplugInitException;
use iggyvolz\buttplug\Exception\ButtplugMsgException;
use iggyvolz\buttplug\Exception\ButtplugPingException;

final readonly class Error extends ServerMessage
{
    public function __construct(int $id, public string $errorMessage, public ErrorCode $errorCode)
    {
        parent::__construct($id);
    }
    public function exception(): ButtplugException
    {
        return match ($this->errorCode) {
            ErrorCode::Init => new ButtplugInitException($this),
            ErrorCode::Device => new ButtplugDeviceException($this),
            ErrorCode::Msg => new ButtplugMsgException($this),
            ErrorCode::Ping => new ButtplugPingException($this),
            default => new ButtplugException($this),
        };
    }
}