<?php

namespace App\Enums;

enum LotStatus: string
{
    case Pending = 'pending';
    case Open = 'open';
    case Sold = 'sold';
    case Passed = 'passed';
}
