<?php

namespace iggyvolz\buttplug\Message;

enum CommandType: string
{
    case Read = "Read";
    case Subscribe = "Subscribe";
    case Unsubscribe = "Unsubscribe";
}