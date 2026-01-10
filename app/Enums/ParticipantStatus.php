<?php

namespace App\Enums;

enum ParticipantStatus: string
{
    case Invited = 'invited';
    case Approved = 'approved';
    case Banned = 'banned';
}
