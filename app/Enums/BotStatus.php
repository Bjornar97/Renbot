<?php

namespace App\Enums;

enum BotStatus: string
{
    case RUNNING = "running";
    case STOPPED = "stopped";
    case FAILED = "failed";
    case ERROR = "error";
    case UNKNOWN = "unknown";
}
