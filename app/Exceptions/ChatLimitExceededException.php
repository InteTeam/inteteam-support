<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class ChatLimitExceededException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Monthly chat session limit reached for this tenant.');
    }
}
