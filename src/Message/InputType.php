<?php

namespace iggyvolz\buttplug\Message;

enum InputType: string
{
    case Battery = "Battery";
    case RSSI = "RSSI";
    case Pressure = "Pressure";
    case Button = "Button";

}