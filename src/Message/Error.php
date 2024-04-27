<?php

namespace iggyvolz\buttplug\Message;

final readonly class Error extends ServerMessage
{
    public function __construct(int $id, public string $errorMessage, public ErrorCode $errorCode)
    {
        parent::__construct($id);
    }
}