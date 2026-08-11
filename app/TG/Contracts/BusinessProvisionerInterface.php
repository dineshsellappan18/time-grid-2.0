<?php

namespace App\TG\Contracts;

use App\Models\User;
use Timegridio\Concierge\Models\Business;

interface BusinessProvisionerInterface
{
    public function provision(User $user, array $data, int $categoryId): Business;

    public function setCategory(Business $business, int $categoryId): Business;

    public function deactivate(Business $business): ?bool;

    public function restore(User $user, string $slug): ?Business;

    public function setup(Business $business): void;
}
