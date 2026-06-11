<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class TicketLimitExceededException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Monthly ticket limit reached for this tenant.');
    }
}
