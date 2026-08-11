<?php

namespace App\TG\Contracts;

use App\Models\User;

interface UserRegistrarInterface
{
    public function register(array $data): User;

    public function findOrCreateFromOAuth(object $providerUser): User;

    public function linkExisting(string $email): ?User;
}
