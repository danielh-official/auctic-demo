<?php

namespace App\Enums;

enum SettlementStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
}
