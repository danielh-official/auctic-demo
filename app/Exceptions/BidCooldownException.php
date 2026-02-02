<?php

namespace App\Exceptions;

use Exception;

class BidCooldownException extends Exception
{
    public function __construct(
        string $message,
        public string $bidStartDate,
    ) {
        parent::__construct($message);
    }
}
