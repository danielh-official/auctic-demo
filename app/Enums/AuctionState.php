<?php

namespace App\Enums;

enum AuctionState: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Live = 'live';
    case Closed = 'closed';
}
