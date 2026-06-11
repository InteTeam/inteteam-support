<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class ChatAccessDeniedException extends RuntimeException
{
    public function __construct(string $message = 'Chat is not enabled for your account.')
    {
        parent::__construct($message);
    }
}
