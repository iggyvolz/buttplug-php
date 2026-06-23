<?php

namespace iggyvolz\buttplug\Message;

enum OutputType: string
{
    case Vibrate = "Vibrate";
    case Rotate = "Rotate";
    case RotationWithDirection = "RotationWithDirection";
    case Oscillate = "Oscillate";
    case Constrict = "Constrict";
    case Spray = "Spray";
    case Temperature = "Temperature";
    case Led = "Led";
    case Position = "Position";
    case HwPositionWithDuration = "HwPositionWithDuration";
}
