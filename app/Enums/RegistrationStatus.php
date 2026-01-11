<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case Invited = 'invited';
    case Approved = 'approved';
    case Banned = 'banned';
}
