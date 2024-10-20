<?php

namespace App\Enums;

enum EventType: string
{
    case STREAMDAY = "streamday";
    case MCC = "mcc";
    case TWITCH_RIVALS = "twitch-rivals";
    case CHARITY_FUNDRAISING = "charity-fundraising";
    case OTHER = "other";
}
