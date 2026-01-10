<?php

namespace App\Enums;

enum PaymentIntentStatus: string
{
    case Initiated = 'initiated';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Canceled = 'canceled';
}
