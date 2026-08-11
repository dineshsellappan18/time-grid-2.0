<?php

namespace App\TG\Repositories;

use App\Models\User;
use App\TG\Contracts\UserRegistrarInterface;

class UserRepository
{
    public function __construct(
        private readonly UserRegistrarInterface $registrar,
    ) {
    }

    public function findOrCreate(object $userData): User
    {
        return $this->registrar->findOrCreateFromOAuth($userData);
    }
}
