<?php

namespace App\Enums;

enum BidStatus: string
{
    case Accepted = 'accepted';
    case Outbid = 'outbid';
}
