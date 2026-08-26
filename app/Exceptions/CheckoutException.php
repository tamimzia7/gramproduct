<?php

namespace App\Exceptions;

use RuntimeException;

class CheckoutException extends RuntimeException
{
    /**
     * @param  array<int, array{type: string, message: string}>  $issues
     */
    public function __construct(
        string $message,
        public readonly array $issues = [],
    ) {
        parent::__construct($message);
    }
}
