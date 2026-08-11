<?php

namespace App\TG;

use App\Models\User;
use App\TG\Contracts\BusinessProvisionerInterface;
use Timegridio\Concierge\Models\Business;

class BusinessService
{
    public function __construct(
        private readonly BusinessProvisionerInterface $provisioner,
    ) {
    }

    public function register(User $user, array $data, int $category): Business
    {
        return $this->provisioner->provision($user, $data, $category);
    }

    public function getExisting(User $user, string $slug): Business|false
    {
        $business = $this->provisioner->restore($user, $slug);

        return $business ?? false;
    }

    public function deactivate(Business $business): ?bool
    {
        return $this->provisioner->deactivate($business);
    }

    public function update(Business $business, array $data): Business
    {
        $business->where(['id' => $business->id])->update($data);

        return $business;
    }

    public function setCategory(Business $business, int $category): Business
    {
        return $this->provisioner->setCategory($business, $category);
    }

    public function setup(Business $business): void
    {
        $this->provisioner->setup($business);
    }
}
