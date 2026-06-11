<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class RemoteAccessDeniedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Remote desktop is not enabled for this customer group.');
    }
}
