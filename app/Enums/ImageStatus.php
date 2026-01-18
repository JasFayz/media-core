<?php

namespace App\Enums;

enum ImageStatus: string
{
    case PENDING = 'pending';
    case READY = 'ready';

    case FAILED = 'failed';
}
