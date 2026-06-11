<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class RemoteLimitExceededException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Monthly remote desktop minutes limit reached.');
    }
}
