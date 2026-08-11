<?php

namespace App\TG;

use App\Exceptions\BusinessAlreadyRegistered;
use App\Models\User;
use App\TG\Business\Setup\SetupStaff;
use App\TG\Contracts\BusinessProvisionerInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Timegridio\Concierge\Models\Business;
use Timegridio\Concierge\Models\Category;

class BusinessProvisioner implements BusinessProvisionerInterface
{
    public function __construct(
        private readonly SetupStaff $setupStaff = new SetupStaff(),
    ) {
    }

    public function provision(User $user, array $data, int $categoryId): Business
    {
        $slug = Str::slug($data['name']);

        $existing = $this->findExisting($user, $slug);
        if ($existing !== null) {
            return $existing;
        }

        $business = new Business($data);

        $category = Category::find($categoryId);
        $business->strategy = $category->strategy;
        $business->category()->associate($category);

        $business->save();

        $user->businesses()->attach($business);

        Log::info('BusinessProvisioner: business created', [
            'user_id'     => $user->id,
            'business_id' => $business->id,
            'slug'        => $slug,
        ]);

        return $business;
    }

    public function setCategory(Business $business, int $categoryId): Business
    {
        $category = Category::find($categoryId);
        $business->category()->associate($category);
        $business->save();

        return $business;
    }

    public function deactivate(Business $business): ?bool
    {
        Log::info('BusinessProvisioner: deactivating business', [
            'business_id' => $business->id,
        ]);

        return $business->delete();
    }

    public function restore(User $user, string $slug): ?Business
    {
        $business = Business::withTrashed()->where('slug', $slug)->first();

        if ($business === null) {
            return null;
        }

        if (!$user->isOwnerOf($business->id)) {
            throw new BusinessAlreadyRegistered();
        }

        $business->restore();

        Log::info('BusinessProvisioner: business restored', [
            'user_id'     => $user->id,
            'business_id' => $business->id,
        ]);

        return $business;
    }

    public function setup(Business $business): void
    {
        $this->setupStaff->createStaffMember($business);
    }

    private function findExisting(User $user, string $slug): ?Business
    {
        $business = Business::withTrashed()->where('slug', $slug)->first();

        if ($business === null) {
            return null;
        }

        if (!$user->isOwnerOf($business->id)) {
            throw new BusinessAlreadyRegistered();
        }

        $business->restore();

        Log::info('BusinessProvisioner: restored existing business', [
            'user_id'     => $user->id,
            'business_id' => $business->id,
        ]);

        return $business;
    }
}
