<?php

namespace App\Exceptions;

use RuntimeException;

class RegistrationFailedException extends RuntimeException
{
    public static function emailTaken(string $email): self
    {
        return new self("A user with this email already exists.");
    }

    public static function invalidData(string $reason): self
    {
        return new self("Registration failed: {$reason}");
    }
}
